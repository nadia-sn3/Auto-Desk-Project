-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 03, 2025 at 12:47 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `autodesk`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `audit_log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `project_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`audit_log_id`, `user_id`, `action`, `created_at`, `project_id`) VALUES
(26, 11, 'User logged in', '2025-03-28 09:17:10', NULL),
(27, 11, 'User logged in', '2025-03-28 09:19:45', NULL),
(28, 8, 'User logged in', '2025-03-28 09:31:51', NULL),
(29, NULL, 'Failed login attempt for email: test2@test.com', '2025-03-28 09:57:38', NULL),
(30, 10, 'User logged in', '2025-03-28 09:57:45', NULL),
(31, 8, 'User logged in', '2025-03-28 10:43:00', NULL),
(32, 8, 'User logged in', '2025-04-01 22:00:27', NULL),
(33, NULL, 'Failed login attempt', '2025-04-01 22:03:10', NULL),
(34, 14, 'User logged in', '2025-04-01 22:03:32', NULL),
(35, 14, 'User logged in', '2025-04-01 23:05:36', NULL),
(36, 14, 'User logged in', '2025-04-01 23:26:05', NULL),
(37, 14, 'User logged in', '2025-04-02 12:12:10', NULL),
(38, 10, 'User logged in', '2025-04-02 17:29:01', NULL),
(39, NULL, 'Failed login attempt', '2025-04-02 17:30:09', NULL),
(40, NULL, 'Failed login attempt', '2025-04-02 21:25:42', NULL),
(41, 18, 'User logged in', '2025-04-02 21:25:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bucket_file`
--

CREATE TABLE `bucket_file` (
  `bucket_file_id` int(11) NOT NULL,
  `project_file_id` int(11) NOT NULL,
  `file_version` int(11) NOT NULL,
  `object_id` varchar(255) NOT NULL,
  `object_key` varchar(255) NOT NULL,
  `first_added_at_version` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commit_file`
--

CREATE TABLE `commit_file` (
  `commit_id` int(11) NOT NULL,
  `bucket_file_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invitations`
--

CREATE TABLE `invitations` (
  `invitation_id` int(11) NOT NULL,
  `org_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `org_role_id` int(11) NOT NULL,
  `invited_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT (current_timestamp() + interval 7 day),
  `status` enum('pending','accepted','expired') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `issues`
--

CREATE TABLE `issues` (
  `issue_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `commit_id` int(11) NOT NULL,
  `file_id` int(11) DEFAULT NULL,
  `reporter_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('open','in_progress','resolved') NOT NULL DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `issue_comments`
--

CREATE TABLE `issue_comments` (
  `comment_id` int(11) NOT NULL,
  `issue_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `models`
--

CREATE TABLE `models` (
  `model_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `model_name` varchar(200) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `version` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organisations`
--

CREATE TABLE `organisations` (
  `org_id` int(11) NOT NULL,
  `org_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organisation_members`
--

CREATE TABLE `organisation_members` (
  `org_member_id` int(11) NOT NULL,
  `org_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `org_role_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `invited_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organisation_roles`
--

CREATE TABLE `organisation_roles` (
  `org_role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `permissions` text DEFAULT NULL COMMENT 'JSON array of permissions'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organisation_roles`
--

INSERT INTO `organisation_roles` (`org_role_id`, `role_name`, `permissions`) VALUES
(1, 'Organisation Owner', '[\"org.manage\",\"org.members.invite\",\"org.members.remove\",\"org.projects.create\",\"org.projects.manage\"]'),
(2, 'Organisation Admin', '[\"org.members.invite\",\"org.projects.create\",\"org.projects.manage\"]'),
(3, 'Organisation Member', '[\"org.projects.view\"]');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `token_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE `project` (
  `project_id` int(11) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `latest_version` int(11) NOT NULL,
  `org_id` int(11) DEFAULT NULL,
  `thumbnail_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_commit`
--

CREATE TABLE `project_commit` (
  `commit_id` int(11) NOT NULL,
  `commit_message` text NOT NULL,
  `project_id` int(11) NOT NULL,
  `project_version` int(11) NOT NULL,
  `commit_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_file`
--

CREATE TABLE `project_file` (
  `project_file_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `latest_version` int(11) NOT NULL,
  `first_added_at_version` int(11) NOT NULL,
  `file_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_members`
--

CREATE TABLE `project_members` (
  `project_member_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `project_role_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `added_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_members`
--

INSERT INTO `project_members` (`project_member_id`, `project_id`, `user_id`, `project_role_id`, `added_at`, `added_by`) VALUES
(27, 32, 18, 1, '2025-04-02 17:52:56', 18),
(28, 33, 18, 1, '2025-04-02 17:55:21', 18),
(29, 34, 18, 1, '2025-04-02 17:57:01', 18),
(30, 35, 18, 1, '2025-04-02 20:24:19', 18),
(31, 36, 18, 1, '2025-04-02 20:40:27', 18),
(32, 37, 18, 1, '2025-04-02 21:14:33', 18),
(33, 38, 18, 1, '2025-04-02 21:25:56', 18),
(34, 39, 18, 1, '2025-04-02 21:45:03', 18);

-- --------------------------------------------------------

--
-- Table structure for table `project_roles`
--

CREATE TABLE `project_roles` (
  `project_role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `permissions` text DEFAULT NULL COMMENT 'JSON array of permissions'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_roles`
--

INSERT INTO `project_roles` (`project_role_id`, `role_name`, `permissions`) VALUES
(1, 'Project Admin', '[\"project.manage\",\"project.members.invite\",\"project.members.remove\"]'),
(3, 'Project Editor', '[\"project.edit\",\"model.upload\",\"model.update\"]'),
(4, 'Project Viewer', '[\"project.view\",\"model.view\"]'),
(5, 'Project Contractor', '[\"limited.access\"]'),
(2, 'Project Manager', '[\"project.manage\",\"project.edit\",\"project.view\",\"model.upload\",\"model.update\",\"model.view\",\"project.members.invite\"]');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `reported_type` enum('user','project') NOT NULL,
  `reported_id` int(11) NOT NULL,
  `report_reason` varchar(255) NOT NULL,
  `report_details` text DEFAULT NULL,
  `status` enum('pending','resolved') NOT NULL DEFAULT 'pending',
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`report_id`, `reporter_id`, `reported_type`, `reported_id`, `report_reason`, `report_details`, `status`, `resolved_by`, `resolved_at`, `created_at`) VALUES
(1, 2, 'user', 3, 'Inappropriate content', 'This user posted offensive material in their profile', 'pending', NULL, NULL, '2025-03-28 09:15:22'),
(2, 3, 'project', 1, 'Copyright violation', 'This project contains copyrighted material without permission', 'pending', NULL, NULL, '2025-03-28 10:30:45'),
(3, 1, 'user', 4, 'Harassment', 'This user sent me abusive messages', 'resolved', NULL, NULL, '2025-03-27 14:20:33'),
(4, 4, 'project', 2, 'Spam', 'This project appears to be advertising unrelated products', 'pending', NULL, NULL, '2025-03-28 11:45:12');

-- --------------------------------------------------------

--
-- Table structure for table `system_roles`
--

CREATE TABLE `system_roles` (
  `system_role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `permissions` text DEFAULT NULL COMMENT 'JSON array of permissions'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_roles`
--

INSERT INTO `system_roles` (`system_role_id`, `role_name`, `permissions`) VALUES
(1, 'System Admin', '[\"*\"]'),
(2, 'Regular User', '[\"basic.access\"]');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `system_role_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `system_role_id`, `email`, `password_hash`, `first_name`, `last_name`, `created_at`, `last_login`, `is_active`) VALUES
(1, 1, 'admin@admin.com', '$2y$10$1fxcfuF.u.q8Q7tXBk6Gs.Tna0ptbeYerCl8CBvZAfX95fKOfAp9y', 'admin', 'admin', '2025-03-28 04:37:02', NULL, NULL),
(18, 2, 'test@test.com', '$2y$10$UBqgSTBxfG7.oqQvW2q75uGBKeWfQZrmlmIH/QWvyRuTseywhWh.2', 'Test', 'test', '2025-04-02 17:30:29', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`audit_log_id`),
  ADD KEY `fk_project_id` (`project_id`);

--
-- Indexes for table `bucket_file`
--
ALTER TABLE `bucket_file`
  ADD PRIMARY KEY (`bucket_file_id`),
  ADD KEY `project_file_id` (`project_file_id`);

--
-- Indexes for table `commit_file`
--
ALTER TABLE `commit_file`
  ADD PRIMARY KEY (`commit_id`,`bucket_file_id`),
  ADD KEY `bucket_file_id` (`bucket_file_id`);

--
-- Indexes for table `invitations`
--
ALTER TABLE `invitations`
  ADD PRIMARY KEY (`invitation_id`),
  ADD KEY `org_id` (`org_id`);

--
-- Indexes for table `issues`
--
ALTER TABLE `issues`
  ADD PRIMARY KEY (`issue_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `commit_id` (`commit_id`),
  ADD KEY `file_id` (`file_id`),
  ADD KEY `reporter_id` (`reporter_id`);

--
-- Indexes for table `issue_comments`
--
ALTER TABLE `issue_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `issue_id` (`issue_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `organisations`
--
ALTER TABLE `organisations`
  ADD PRIMARY KEY (`org_id`);

--
-- Indexes for table `organisation_members`
--
ALTER TABLE `organisation_members`
  ADD PRIMARY KEY (`org_member_id`),
  ADD KEY `org_id` (`org_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `org_role_id` (`org_role_id`);

--
-- Indexes for table `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `fk_project_org` (`org_id`);

--
-- Indexes for table `project_commit`
--
ALTER TABLE `project_commit`
  ADD PRIMARY KEY (`commit_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `project_file`
--
ALTER TABLE `project_file`
  ADD PRIMARY KEY (`project_file_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `project_members`
--
ALTER TABLE `project_members`
  ADD PRIMARY KEY (`project_member_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `reporter_id` (`reporter_id`),
  ADD KEY `reported_id` (`reported_id`),
  ADD KEY `fk_reports_resolved_by` (`resolved_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `audit_log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `bucket_file`
--
ALTER TABLE `bucket_file`
  MODIFY `bucket_file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `invitations`
--
ALTER TABLE `invitations`
  MODIFY `invitation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issues`
--
ALTER TABLE `issues`
  MODIFY `issue_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `issue_comments`
--
ALTER TABLE `issue_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organisations`
--
ALTER TABLE `organisations`
  MODIFY `org_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `organisation_members`
--
ALTER TABLE `organisation_members`
  MODIFY `org_member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `project`
--
ALTER TABLE `project`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `project_commit`
--
ALTER TABLE `project_commit`
  MODIFY `commit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `project_file`
--
ALTER TABLE `project_file`
  MODIFY `project_file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `project_members`
--
ALTER TABLE `project_members`
  MODIFY `project_member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_project_id` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`);

--
-- Constraints for table `bucket_file`
--
ALTER TABLE `bucket_file`
  ADD CONSTRAINT `bucket_file_ibfk_1` FOREIGN KEY (`project_file_id`) REFERENCES `project_file` (`project_file_id`) ON DELETE CASCADE;

--
-- Constraints for table `commit_file`
--
ALTER TABLE `commit_file`
  ADD CONSTRAINT `commit_file_ibfk_1` FOREIGN KEY (`commit_id`) REFERENCES `project_commit` (`commit_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commit_file_ibfk_2` FOREIGN KEY (`bucket_file_id`) REFERENCES `bucket_file` (`bucket_file_id`) ON DELETE CASCADE;

--
-- Constraints for table `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `fk_project_org` FOREIGN KEY (`org_id`) REFERENCES `organisations` (`org_id`);

--
-- Constraints for table `project_commit`
--
ALTER TABLE `project_commit`
  ADD CONSTRAINT `project_commit_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`) ON DELETE CASCADE;

--
-- Constraints for table `project_file`
--
ALTER TABLE `project_file`
  ADD CONSTRAINT `project_file_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `fk_reports_resolved_by` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
