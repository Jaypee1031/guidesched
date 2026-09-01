-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 27, 2026 at 09:19 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `guidesched`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `counselor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `concern` text NOT NULL,
  `status` enum('pending','approved','declined','rescheduled','completed','cancelled','no_show') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `student_id`, `counselor_id`, `appointment_date`, `start_time`, `end_time`, `concern`, `status`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 5, 2, '2026-08-21', '13:57:00', '14:58:00', 'sgagdaggsgaasg', 'completed', NULL, '2026-08-03 05:58:44', '2026-08-03 05:59:23'),
(2, 3, 2, '2026-08-28', '10:00:00', '11:00:00', '[Face-to-face] Academic stress: Need guidance for midterms', 'pending', NULL, '2026-08-24 00:56:15', '2026-08-24 00:56:15'),
(3, 6, 2, '2026-08-25', '09:00:00', '10:00:00', '[Face-to-face] Academic stress', 'pending', NULL, '2026-08-24 00:56:30', '2026-08-24 00:56:30');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_history`
--

CREATE TABLE `appointment_history` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `old_status` varchar(20) DEFAULT NULL,
  `new_status` varchar(20) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointment_history`
--

INSERT INTO `appointment_history` (`id`, `appointment_id`, `action`, `old_status`, `new_status`, `changed_by`, `created_at`) VALUES
(1, 1, 'status_change', 'pending', 'approved', 1, '2026-08-03 05:59:05'),
(2, 1, 'status_change', 'approved', 'completed', 1, '2026-08-03 05:59:23');

-- --------------------------------------------------------

--
-- Table structure for table `availability`
--

CREATE TABLE `availability` (
  `id` int(11) NOT NULL,
  `counselor_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('available','booked','blocked') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `availability`
--

INSERT INTO `availability` (`id`, `counselor_id`, `date`, `start_time`, `end_time`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, '2026-08-21', '13:57:00', '14:58:00', 'booked', '2026-08-03 05:57:56', '2026-08-03 05:58:44'),
(2, 2, '2026-08-24', '09:00:00', '10:00:00', 'available', '2026-08-24 00:53:07', '2026-08-24 00:53:07'),
(3, 2, '2026-08-28', '10:00:00', '11:00:00', 'booked', '2026-08-24 00:56:15', '2026-08-24 00:56:15'),
(4, 2, '2026-08-25', '09:00:00', '10:00:00', 'booked', '2026-08-24 00:56:30', '2026-08-24 00:56:30');

-- --------------------------------------------------------

--
-- Table structure for table `counselor_profiles`
--

CREATE TABLE `counselor_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `counselor_profiles`
--

INSERT INTO `counselor_profiles` (`id`, `user_id`, `specialization`, `contact_number`, `profile_picture`, `created_at`, `updated_at`) VALUES
(1, 2, 'Academic Counseling', '+63 912 345 6789', NULL, '2026-07-30 00:44:48', '2026-07-30 00:44:48');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `type` enum('approved','declined','rescheduled','reminder','info') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `appointment_id`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 2, 1, 'New appointment request from student for August 21, 2026 at 1:57 PM', 'info', 0, '2026-08-03 05:58:44'),
(2, 5, 1, 'Your appointment request has been submitted for August 21, 2026 at 1:57 PM', 'info', 0, '2026-08-03 05:58:44'),
(3, 5, 1, 'Your appointment on August 21, 2026 at 1:57 PM has been approved.', 'approved', 0, '2026-08-03 05:59:05'),
(4, 5, 1, 'Your appointment on August 21, 2026 at 1:57 PM has been completed.', '', 0, '2026-08-03 05:59:23'),
(5, 2, 2, 'New appointment request from student for August 28, 2026 at 10:00 AM', 'info', 0, '2026-08-24 00:56:15'),
(6, 3, 2, 'Your appointment request has been submitted for August 28, 2026 at 10:00 AM', 'info', 0, '2026-08-24 00:56:15'),
(7, 2, 3, 'New appointment request from student for August 25, 2026 at 9:00 AM', 'info', 0, '2026-08-24 00:56:30'),
(8, 6, 3, 'Your appointment request has been submitted for August 25, 2026 at 9:00 AM', 'info', 0, '2026-08-24 00:56:30');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `generated_by` int(11) NOT NULL,
  `parameters` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `course` varchar(100) NOT NULL,
  `year_level` int(11) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_profiles`
--

INSERT INTO `student_profiles` (`id`, `user_id`, `student_number`, `course`, `year_level`, `contact_number`, `profile_picture`, `created_at`, `updated_at`) VALUES
(1, 3, '1234567', 'Bachelor of Secondary Education major in Filipino, Science, Math, English (BSED)', 2, '+639634032919', NULL, '2026-08-03 01:05:10', '2026-08-03 01:05:10'),
(2, 5, '123456789', 'Bachelor of Secondary Education major in Filipino, Science, Math, English (BSED)', 1, '+639352640530', NULL, '2026-08-03 02:16:56', '2026-08-03 02:16:56'),
(3, 6, '12345678', 'Bachelor of Secondary Education major in Filipino, Science, Math, English (BSED)', 3, '+639634032919', NULL, '2026-08-24 00:43:41', '2026-08-24 00:43:41'),
(4, 7, 'dfdsfgsdfg', 'dsfgdgdf', 3, '+639634032919', NULL, '2026-08-27 07:17:13', '2026-08-27 07:17:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `role` enum('student','admin','counselor') NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `role`, `name`, `email`, `password`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ADMIN001', 'admin', 'System Administrator', 'admin@guidesched.com', '$2y$10$PxRDlEEigvI2HXWrX/FjMu9aJGhrJZ1AOJ9j1jrHDZbWNsZfl/IPu', 'active', '2026-07-30 00:44:48', '2026-08-03 01:31:00'),
(2, 'COUNSELOR001', 'counselor', 'Dr. Maria Santos', 'maria.santos@guidesched.com', '$2y$10$O8o3OowgoVFdpW.7SJIdCeflba.sL5LcQraoCcltv9wbgYZug.G3y', 'active', '2026-07-30 00:44:48', '2026-08-24 00:52:13'),
(3, 'STU758065', 'student', 'jp', 'supremejp1111@gmail.com', '$2y$10$BRQOUmAYVMoLRucc7AgJV.C9jxgwB6OKE.yUbBqpkbRJ1AoRSVL4O', 'active', '2026-08-03 01:05:10', '2026-08-03 01:05:10'),
(5, 'STU797983', 'student', 'migrate_requirement_remark_images.php', 'Jaypeepumihic11@gmail.com', '$2y$10$FjdtNF32m5c7wGq0dxORTeJgd.DQz80EpgfjNbQanRKbI3xLU3EW2', 'active', '2026-08-03 02:16:56', '2026-08-03 02:16:56'),
(6, 'STU080833', 'student', 'aira delos santos', 'aira@gmail.com', '$2y$10$1sr8DNBcMfNPoDDjrYvCge79WbcjhvBsbZCPKNFJZwQiA6xxcjJpa', 'active', '2026-08-24 00:43:41', '2026-08-24 00:43:41'),
(7, 'STU429372', 'student', 'sdgsdfgsdfgs', 'supremejp111@gmail.com', '$2y$10$aRSP8COOweZfYg1N.ku46.qKgKgxys0ruiLoKLayNqZ4lLUtfg5BW', 'active', '2026-08-27 07:17:13', '2026-08-27 07:17:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_counselor_id` (`counselor_id`),
  ADD KEY `idx_appointment_date` (`appointment_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date_status` (`appointment_date`,`status`);

--
-- Indexes for table `appointment_history`
--
ALTER TABLE `appointment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `idx_appointment_id` (`appointment_id`),
  ADD KEY `idx_action` (`action`);

--
-- Indexes for table `availability`
--
ALTER TABLE `availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_slot` (`counselor_id`,`date`,`start_time`,`end_time`),
  ADD KEY `idx_counselor_id` (`counselor_id`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date_counselor` (`date`,`counselor_id`);

--
-- Indexes for table `counselor_profiles`
--
ALTER TABLE `counselor_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_report_type` (`report_type`),
  ADD KEY `idx_generated_by` (`generated_by`);

--
-- Indexes for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD KEY `idx_student_number` (`student_number`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `appointment_history`
--
ALTER TABLE `appointment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `availability`
--
ALTER TABLE `availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `counselor_profiles`
--
ALTER TABLE `counselor_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_profiles`
--
ALTER TABLE `student_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`counselor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `appointment_history`
--
ALTER TABLE `appointment_history`
  ADD CONSTRAINT `appointment_history_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointment_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `availability`
--
ALTER TABLE `availability`
  ADD CONSTRAINT `availability_ibfk_1` FOREIGN KEY (`counselor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `counselor_profiles`
--
ALTER TABLE `counselor_profiles`
  ADD CONSTRAINT `counselor_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `student_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
