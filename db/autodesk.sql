-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 27, 2025 at 01:32 PM
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

--
-- Dumping data for table `Bucket_File`
--

INSERT INTO `Bucket_File` (`bucket_file_id`, `project_file_id`, `file_version`, `object_id`, `object_key`, `first_added_at_version`) VALUES
(1, 1, 1, 'dXJuOmFkc2sub2JqZWN0czpvcy5vYmplY3Q6bXlidWNrZXRfMjAyNS9vbmUub2Jq', 'one.obj', 2),
(2, 2, 2, 'dXJuOmFkc2sub2JqZWN0czpvcy5vYmplY3Q6bXlidWNrZXRfMjAyNS9vbmUub2Jq', 'one.obj', 2),
(3, 2, 2, 'dXJuOmFkc2sub2JqZWN0czpvcy5vYmplY3Q6bXlidWNrZXRfMjAyNS9vbmUub2Jq', 'one.obj', 2),
(4, 2, 3, 'dXJuOmFkc2sub2JqZWN0czpvcy5vYmplY3Q6bXlidWNrZXRfMjAyNS9vbmUub2Jq', 'one.obj', 4),
(5, 2, 4, 'dXJuOmFkc2sub2JqZWN0czpvcy5vYmplY3Q6bXlidWNrZXRfMjAyNS9vbmUub2Jq', 'one.obj', 5),
(6, 2, 5, 'dXJuOmFkc2sub2JqZWN0czpvcy5vYmplY3Q6bXlidWNrZXRfMjAyNS9vbmUub2Jq', 'one.obj', 6),
(7, 3, 1, 'dXJuOmFkc2sub2JqZWN0czpvcy5vYmplY3Q6bXlidWNrZXRfMjAyNS9vbmUub2Jq', 'one.obj', 2);

-- --------------------------------------------------------

--
-- Table structure for table `Commit_File`
--

CREATE TABLE `Commit_File` (
  `commit_id` int(11) NOT NULL,
  `bucket_file_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Commit_File`
--

INSERT INTO `Commit_File` (`commit_id`, `bucket_file_id`) VALUES
(1, 1),
(2, 2),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 7);

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
  `project_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `latest_version` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Project`
--

INSERT INTO `Project` (`project_id`, `project_name`, `description`, `created_by`, `latest_version`) VALUES
(1, 'susu', 'susu', 1, 2),
(2, 'susu', 'susu', 1, 6),
(3, 'susu', 'susu', 1, 2);

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

--
-- Dumping data for table `Project_Commit`
--

INSERT INTO `Project_Commit` (`commit_id`, `commit_message`, `project_id`, `project_version`) VALUES
(1, 'first', 1, 2),
(2, 'gtfg', 2, 2),
(3, 'gtfg', 2, 3),
(4, 'jk', 2, 4),
(5, 'gu', 2, 5),
(6, 'gu', 2, 6),
(7, 'enhufaj', 3, 2);

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

--
-- Dumping data for table `Project_File`
--

INSERT INTO `Project_File` (`project_file_id`, `project_id`, `file_name`, `latest_version`, `first_added_at_version`, `file_type`) VALUES
(1, 1, 'one.obj', 1, 2, 'obj'),
(2, 2, 'one.obj', 5, 2, 'obj'),
(3, 3, 'one.obj', 1, 2, 'obj');

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
(1, 2, 'su@gmail.com', '$2y$10$OxAIfun39pby6PBZ2/AlHe.pSoyl8UvBsonheeMUdGczNvySLRl4.', 'susu', 'susu', '2025-03-27 11:52:41', NULL, NULL),
(2, 2, 'su1@gmail.com', '$2y$10$lbB3k2xNY5lU8gQR8UHl5.0saoHebr8.3jthkuOhNTWnhnHBIwh/i', 'sususu', 'susu', '2025-03-27 11:55:15', NULL, NULL),
(3, 2, 's@gmail.com', '$2y$10$nreCM2W56S3WZQGP31jA2uQe1X9Mg1xtV6.KNADXRZLuLibxIld1O', 'sussu', 'susu', '2025-03-27 12:13:36', NULL, NULL),
(4, 2, 't@t.com', '$2y$10$g3tRLJmPzaaSNSxv85puaOtQJVZWBD/iZwJzBAez0m/iZ0V.UNNhu', 't', 't', '2025-03-27 12:26:53', NULL, NULL);

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
-- Indexes for table `Commit_File`
--
ALTER TABLE `Commit_File`
  ADD PRIMARY KEY (`commit_id`,`bucket_file_id`),
  ADD KEY `bucket_file_id` (`bucket_file_id`);

--
-- Indexes for table `Project`
--
ALTER TABLE `Project`
  ADD PRIMARY KEY (`project_id`);

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
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Bucket_File`
--
ALTER TABLE `Bucket_File`
  MODIFY `bucket_file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `Project`
--
ALTER TABLE `Project`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Project_Commit`
--
ALTER TABLE `Project_Commit`
  MODIFY `commit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `Project_File`
--
ALTER TABLE `Project_File`
  MODIFY `project_file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Bucket_File`
--
ALTER TABLE `Bucket_File`
  ADD CONSTRAINT `bucket_file_ibfk_1` FOREIGN KEY (`project_file_id`) REFERENCES `Project_File` (`project_file_id`) ON DELETE CASCADE;

--
-- Constraints for table `Commit_File`
--
ALTER TABLE `Commit_File`
  ADD CONSTRAINT `commit_file_ibfk_1` FOREIGN KEY (`commit_id`) REFERENCES `Project_Commit` (`commit_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commit_file_ibfk_2` FOREIGN KEY (`bucket_file_id`) REFERENCES `Bucket_File` (`bucket_file_id`) ON DELETE CASCADE;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
