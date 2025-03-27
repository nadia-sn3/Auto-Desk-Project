-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 27, 2025 at 12:36 PM
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
-- Table structure for table `Bucket_File`
--

CREATE TABLE `Bucket_File` (
  `bucket_file_id` int(11) NOT NULL,
  `project_file_id` int(11) NOT NULL,
  `file_version` int(11) NOT NULL,
  `object_id` varchar(255) NOT NULL,
  `object_key` varchar(255) NOT NULL,
  `first_added_at_version` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commits`
--

CREATE TABLE `commits` (
  `commit_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `commit_message` text NOT NULL,
  `committed_by` int(11) NOT NULL,
  `committed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `parent_commit_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Commit_File`
--

CREATE TABLE `Commit_File` (
  `commit_id` int(11) NOT NULL,
  `bucket_file_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commit_files`
--

CREATE TABLE `commit_files` (
  `commit_file_id` int(11) NOT NULL,
  `commit_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `action` enum('create','update','delete') NOT NULL,
  `previous_version` int(11) DEFAULT NULL
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
-- Table structure for table `Project`
--

CREATE TABLE `Project` (
  `project_id` int(11) NOT NULL,
  `org_id` int(11) DEFAULT NULL,
  `project_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `latest_version` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Table structure for table `Project_Commit`
--

CREATE TABLE `Project_Commit` (
  `commit_id` int(11) NOT NULL,
  `commit_message` text NOT NULL,
  `project_id` int(11) NOT NULL,
  `project_version` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Project_File`
--

CREATE TABLE `Project_File` (
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
(2, 'Project Editor', '[\"project.edit\",\"model.upload\",\"model.update\"]'),
(3, 'Project Viewer', '[\"project.view\",\"model.view\"]'),
(4, 'Project Contractor', '[\"limited.access\"]');

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
  `system_role_id` int(11) NOT NULL DEFAULT 2,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `system_role_id`, `email`, `password_hash`, `first_name`, `last_name`, `created_at`, `last_login`, `is_active`) VALUES
(13, 1, 'admin@autodesk.com', '$2y$10$RVe.sS0fXXDj08sv.dACu..9WD/gflJkgj2gBSzXkF..rrh4fdgLW', 'admin', 'admin', '2025-03-26 09:37:24', NULL, 1),
(14, 2, 'user@autodesk.com', '$2y$10$iSEPB3LxPJIuK6hsiCJUTer/SQOrI/gLJq3a2D60Ymxl9iAj9O6A2', 'user', 'user', '2025-03-26 09:39:20', NULL, 1),
(16, 2, 'test@test.com', '$2y$10$i4zdE//JOIHPJHam.XrhHecOorEg8HczO0QQQ1yz5W1VUVd5/TiMu', 'Testing', 'testing', '2025-03-27 10:50:06', NULL, 1),
(17, 2, 't@t.com', '$2y$10$NT5/yua/tRNzUmx7TI7/ae7Q7LsSE8bmc323MQjjWN2jSXX5sa7Cm', 'test', 'test', '2025-03-27 11:15:40', NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Bucket_File`
--
ALTER TABLE `Bucket_File`
  ADD PRIMARY KEY (`bucket_file_id`),
  ADD KEY `project_file_id` (`project_file_id`);

--
-- Indexes for table `commits`
--
ALTER TABLE `commits`
  ADD PRIMARY KEY (`commit_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `committed_by` (`committed_by`),
  ADD KEY `parent_commit_id` (`parent_commit_id`);

--
-- Indexes for table `Commit_File`
--
ALTER TABLE `Commit_File`
  ADD PRIMARY KEY (`commit_id`,`bucket_file_id`),
  ADD KEY `bucket_file_id` (`bucket_file_id`);

--
-- Indexes for table `commit_files`
--
ALTER TABLE `commit_files`
  ADD PRIMARY KEY (`commit_file_id`),
  ADD KEY `commit_id` (`commit_id`),
  ADD KEY `model_id` (`model_id`);

--
-- Indexes for table `invitations`
--
ALTER TABLE `invitations`
  ADD PRIMARY KEY (`invitation_id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `org_id` (`org_id`),
  ADD KEY `org_role_id` (`org_role_id`),
  ADD KEY `invited_by` (`invited_by`);

--
-- Indexes for table `models`
--
ALTER TABLE `models`
  ADD PRIMARY KEY (`model_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

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
  ADD UNIQUE KEY `org_user_unique` (`org_id`,`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `org_role_id` (`org_role_id`),
  ADD KEY `invited_by` (`invited_by`);

--
-- Indexes for table `organisation_roles`
--
ALTER TABLE `organisation_roles`
  ADD PRIMARY KEY (`org_role_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`token_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Project`
--
ALTER TABLE `Project`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `fk_project_org` (`org_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `org_id` (`org_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `Project_Commit`
--
ALTER TABLE `Project_Commit`
  ADD PRIMARY KEY (`commit_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `Project_File`
--
ALTER TABLE `Project_File`
  ADD PRIMARY KEY (`project_file_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `project_members`
--
ALTER TABLE `project_members`
  ADD PRIMARY KEY (`project_member_id`),
  ADD UNIQUE KEY `project_user_unique` (`project_id`,`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `project_role_id` (`project_role_id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `project_roles`
--
ALTER TABLE `project_roles`
  ADD PRIMARY KEY (`project_role_id`);

--
-- Indexes for table `system_roles`
--
ALTER TABLE `system_roles`
  ADD PRIMARY KEY (`system_role_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `system_role_id` (`system_role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Bucket_File`
--
ALTER TABLE `Bucket_File`
  MODIFY `bucket_file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commits`
--
ALTER TABLE `commits`
  MODIFY `commit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commit_files`
--
ALTER TABLE `commit_files`
  MODIFY `commit_file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invitations`
--
ALTER TABLE `invitations`
  MODIFY `invitation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `models`
--
ALTER TABLE `models`
  MODIFY `model_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organisations`
--
ALTER TABLE `organisations`
  MODIFY `org_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `organisation_members`
--
ALTER TABLE `organisation_members`
  MODIFY `org_member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `organisation_roles`
--
ALTER TABLE `organisation_roles`
  MODIFY `org_role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Project`
--
ALTER TABLE `Project`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Project_Commit`
--
ALTER TABLE `Project_Commit`
  MODIFY `commit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Project_File`
--
ALTER TABLE `Project_File`
  MODIFY `project_file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_members`
--
ALTER TABLE `project_members`
  MODIFY `project_member_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_roles`
--
ALTER TABLE `project_roles`
  MODIFY `project_role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `system_roles`
--
ALTER TABLE `system_roles`
  MODIFY `system_role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Bucket_File`
--
ALTER TABLE `Bucket_File`
  ADD CONSTRAINT `bucket_file_ibfk_1` FOREIGN KEY (`project_file_id`) REFERENCES `Project_File` (`project_file_id`) ON DELETE CASCADE;

--
-- Constraints for table `commits`
--
ALTER TABLE `commits`
  ADD CONSTRAINT `commits_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commits_ibfk_2` FOREIGN KEY (`committed_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `commits_ibfk_3` FOREIGN KEY (`parent_commit_id`) REFERENCES `commits` (`commit_id`) ON DELETE SET NULL;

--
-- Constraints for table `Commit_File`
--
ALTER TABLE `Commit_File`
  ADD CONSTRAINT `commit_file_ibfk_1` FOREIGN KEY (`commit_id`) REFERENCES `Project_Commit` (`commit_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commit_file_ibfk_2` FOREIGN KEY (`bucket_file_id`) REFERENCES `Bucket_File` (`bucket_file_id`) ON DELETE CASCADE;

--
-- Constraints for table `commit_files`
--
ALTER TABLE `commit_files`
  ADD CONSTRAINT `commit_files_ibfk_1` FOREIGN KEY (`commit_id`) REFERENCES `commits` (`commit_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commit_files_ibfk_2` FOREIGN KEY (`model_id`) REFERENCES `models` (`model_id`) ON DELETE CASCADE;

--
-- Constraints for table `invitations`
--
ALTER TABLE `invitations`
  ADD CONSTRAINT `invitations_ibfk_1` FOREIGN KEY (`org_id`) REFERENCES `organisations` (`org_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invitations_ibfk_2` FOREIGN KEY (`org_role_id`) REFERENCES `organisation_roles` (`org_role_id`),
  ADD CONSTRAINT `invitations_ibfk_3` FOREIGN KEY (`invited_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `models`
--
ALTER TABLE `models`
  ADD CONSTRAINT `models_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `models_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `organisation_members`
--
ALTER TABLE `organisation_members`
  ADD CONSTRAINT `organisation_members_ibfk_1` FOREIGN KEY (`org_id`) REFERENCES `organisations` (`org_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `organisation_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `organisation_members_ibfk_3` FOREIGN KEY (`org_role_id`) REFERENCES `organisation_roles` (`org_role_id`),
  ADD CONSTRAINT `organisation_members_ibfk_4` FOREIGN KEY (`invited_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `Project`
--
ALTER TABLE `Project`
  ADD CONSTRAINT `fk_project_org` FOREIGN KEY (`org_id`) REFERENCES `organisations` (`org_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `project_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`org_id`) REFERENCES `organisations` (`org_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `Project_Commit`
--
ALTER TABLE `Project_Commit`
  ADD CONSTRAINT `project_commit_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `Project` (`project_id`) ON DELETE CASCADE;

--
-- Constraints for table `Project_File`
--
ALTER TABLE `Project_File`
  ADD CONSTRAINT `project_file_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `Project` (`project_id`) ON DELETE CASCADE;

--
-- Constraints for table `project_members`
--
ALTER TABLE `project_members`
  ADD CONSTRAINT `project_members_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_members_ibfk_3` FOREIGN KEY (`project_role_id`) REFERENCES `project_roles` (`project_role_id`),
  ADD CONSTRAINT `project_members_ibfk_4` FOREIGN KEY (`added_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`system_role_id`) REFERENCES `system_roles` (`system_role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
