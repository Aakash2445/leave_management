-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 07, 2026 at 03:06 PM
-- Server version: 5.7.44
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `emp_leave_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
CREATE TABLE IF NOT EXISTS `audit_log` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `description` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `action`, `module`, `description`, `ip_address`, `created_at`) VALUES
(1, 1, 'CREATE_USER', 'Admin', 'Created user EMP01 (Admin)', '::1', '2026-06-07 14:17:35'),
(5, 1, 'LOGIN', 'Auth', 'User logged in', '::1', '2026-06-07 14:20:46'),
(6, 1, 'CREATE_USER', 'Admin', 'Created user EMP02 (John)', '::1', '2026-06-07 14:22:01'),
(7, 1, 'CREATE_USER', 'Admin', 'Created user EMP03 (Rohit)', '::1', '2026-06-07 14:22:37'),
(8, 1, 'CREATE_USER', 'Admin', 'Created user EMP04 (Kumar)', '::1', '2026-06-07 14:23:02'),
(9, 1, 'CREATE_USER', 'Admin', 'Created user EMP05 (Karthi)', '::1', '2026-06-07 14:23:36'),
(10, 1, 'LOGOUT', 'Auth', 'User logged out', '::1', '2026-06-07 14:24:00'),
(11, 3, 'LOGIN', 'Auth', 'User logged in', '::1', '2026-06-07 14:24:05'),
(12, 3, 'APPLY_LEAVE', 'Leave', 'Applied LR-202606-0001 for 2 day(s)', '::1', '2026-06-07 14:26:46'),
(13, 3, 'LOGOUT', 'Auth', 'User logged out', '::1', '2026-06-07 14:26:59'),
(14, 2, 'LOGIN', 'Auth', 'User logged in', '::1', '2026-06-07 14:27:05'),
(15, 2, 'LOGOUT', 'Auth', 'User logged out', '::1', '2026-06-07 14:27:16'),
(16, 5, 'LOGIN', 'Auth', 'User logged in', '::1', '2026-06-07 14:27:24'),
(17, 5, 'APPLY_LEAVE', 'Leave', 'Applied LR-202606-0002 for 7 day(s)', '::1', '2026-06-07 14:29:04'),
(18, 5, 'LOGOUT', 'Auth', 'User logged out', '::1', '2026-06-07 14:29:08'),
(19, 4, 'LOGIN', 'Auth', 'User logged in', '::1', '2026-06-07 14:29:19'),
(20, 4, 'APPLY_LEAVE', 'Leave', 'Applied LR-202606-0003 for 3 day(s)', '::1', '2026-06-07 14:29:55'),
(21, 4, 'LOGOUT', 'Auth', 'User logged out', '::1', '2026-06-07 14:30:02'),
(22, 2, 'LOGIN', 'Auth', 'User logged in', '::1', '2026-06-07 14:30:08'),
(23, 2, 'APPROVE_LEAVE', 'Leave', 'Approved leave request ID 2', '::1', '2026-06-07 14:30:19'),
(24, 2, 'APPROVE_LEAVE', 'Leave', 'Approved leave request ID 1', '::1', '2026-06-07 14:30:23'),
(25, 2, 'REJECT_LEAVE', 'Leave', 'Rejectd leave request ID 3', '::1', '2026-06-07 14:30:48'),
(26, 2, 'LOGOUT', 'Auth', 'User logged out', '::1', '2026-06-07 14:30:54'),
(27, 4, 'LOGIN', 'Auth', 'User logged in', '::1', '2026-06-07 14:30:59'),
(28, 4, 'APPLY_LEAVE', 'Leave', 'Applied LR-202606-0004 for 2 day(s)', '::1', '2026-06-07 14:31:27'),
(29, 1, 'ADD_LEAVE_TYPE', 'Admin', 'Added leave type: Materinity Leave (60 days)', '::1', '2026-06-07 14:35:03'),
(30, 1, 'UPDATE_LEAVE_TYPE', 'Admin', 'Updated leave type ID 5: Materinity Leave (90 days)', '::1', '2026-06-07 14:35:10'),
(31, 4, 'LOGOUT', 'Auth', 'User logged out', '::1', '2026-06-07 14:41:20'),
(32, 3, 'LOGIN', 'Auth', 'User logged in', '::1', '2026-06-07 14:41:40'),
(33, 1, 'LOGOUT', 'Auth', 'User logged out', '::1', '2026-06-07 14:42:02'),
(34, 2, 'LOGIN', 'Auth', 'User logged in', '::1', '2026-06-07 14:42:07'),
(35, 2, 'LOGOUT', 'Auth', 'User logged out', '::1', '2026-06-07 14:42:25');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
CREATE TABLE IF NOT EXISTS `employees` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `department` varchar(100) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `user_id`, `employee_id`, `full_name`, `email`, `mobile`, `department`, `designation`, `created_at`, `updated_at`) VALUES
(1, 1, 'EMP01', 'Admin', 'admin@gmail.com', '98384389343', 'IT', 'Administrator', '2026-06-07 14:17:35', '2026-06-07 14:17:35'),
(2, 2, 'EMP02', 'John', 'john@gmail.com', '8923783478382', 'IT', 'Team Lead', '2026-06-07 14:22:01', '2026-06-07 14:22:01'),
(3, 3, 'EMP03', 'Rohit', 'rohit@gmail.com', '892389348932', 'IT', 'Software Developer', '2026-06-07 14:22:37', '2026-06-07 14:22:37'),
(4, 4, 'EMP04', 'Kumar', 'kumar@gmail.com', '8932893893', 'IT', 'Tester', '2026-06-07 14:23:02', '2026-06-07 14:23:02'),
(5, 5, 'EMP05', 'Karthi', 'karthi@gmail.com', '893289439843', 'IT', 'Software Engineer', '2026-06-07 14:23:36', '2026-06-07 14:23:36');

-- --------------------------------------------------------

--
-- Table structure for table `leave_balance`
--

DROP TABLE IF EXISTS `leave_balance`;
CREATE TABLE IF NOT EXISTS `leave_balance` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `leave_type_id` int(10) UNSIGNED NOT NULL,
  `year` year(4) NOT NULL,
  `total_days` int(11) NOT NULL DEFAULT '0',
  `used_days` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_balance` (`user_id`,`leave_type_id`,`year`),
  KEY `leave_type_id` (`leave_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `leave_balance`
--

INSERT INTO `leave_balance` (`id`, `user_id`, `leave_type_id`, `year`, `total_days`, `used_days`) VALUES
(1, 1, 1, '2026', 12, 0),
(2, 1, 2, '2026', 10, 0),
(3, 1, 3, '2026', 15, 0),
(4, 2, 1, '2026', 12, 0),
(5, 2, 2, '2026', 10, 0),
(6, 2, 3, '2026', 15, 0),
(7, 3, 1, '2026', 12, 2),
(8, 3, 2, '2026', 10, 0),
(9, 3, 3, '2026', 15, 0),
(10, 4, 1, '2026', 12, 0),
(11, 4, 2, '2026', 10, 0),
(12, 4, 3, '2026', 15, 0),
(13, 5, 1, '2026', 12, 0),
(14, 5, 2, '2026', 10, 7),
(15, 5, 3, '2026', 15, 0);

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

DROP TABLE IF EXISTS `leave_requests`;
CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_no` varchar(20) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `leave_type_id` int(10) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `manager_id` int(10) UNSIGNED DEFAULT NULL,
  `manager_remarks` text,
  `action_date` timestamp NULL DEFAULT NULL,
  `applied_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `request_no` (`request_no`),
  KEY `user_id` (`user_id`),
  KEY `leave_type_id` (`leave_type_id`),
  KEY `manager_id` (`manager_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `request_no`, `user_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `manager_id`, `manager_remarks`, `action_date`, `applied_date`, `updated_at`) VALUES
(1, 'LR-202606-0001', 3, 1, '2026-06-15', '2026-06-16', 2, 'There is a festival in my hometown so I need to request two days of leave.', 'approved', 2, NULL, '2026-06-07 14:30:23', '2026-06-07 14:26:46', '2026-06-07 14:30:23'),
(2, 'LR-202606-0002', 5, 2, '2026-06-15', '2026-06-21', 7, 'I have dengue fever, so I need one week of medical leave.\"', 'approved', 2, NULL, '2026-06-07 14:30:19', '2026-06-07 14:29:04', '2026-06-07 14:30:19'),
(3, 'LR-202606-0003', 4, 3, '2026-06-24', '2026-06-26', 3, 'Need three days leave', 'rejected', 2, 'Leave request was rejected due to project deadline', '2026-06-07 14:30:48', '2026-06-07 14:29:55', '2026-06-07 14:30:48'),
(4, 'LR-202606-0004', 4, 1, '2026-07-09', '2026-07-10', 2, 'Need a causal leave for 2 days', 'pending', NULL, NULL, NULL, '2026-06-07 14:31:27', '2026-06-07 14:31:27');

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

DROP TABLE IF EXISTS `leave_types`;
CREATE TABLE IF NOT EXISTS `leave_types` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `default_days` int(11) NOT NULL DEFAULT '0',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `name`, `default_days`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Casual Leave', 12, 'active', '2026-06-06 04:14:23', '2026-06-07 12:59:15'),
(2, 'Sick Leave', 10, 'active', '2026-06-06 04:14:23', '2026-06-06 04:14:23'),
(3, 'Earned Leave', 15, 'active', '2026-06-06 04:14:23', '2026-06-06 04:14:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `ref_password` varchar(255) DEFAULT NULL,
  `role` enum('admin','manager','employee') NOT NULL DEFAULT 'employee',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `employee_id`, `email`, `password`, `ref_password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'EMP01', 'admin@gmail.com', '$2y$12$tulbOSU5F9d.j7bihXeSqujWT418A7CSebN26gASqyIdswxu5pN7e', 'VXNlcjEyMzQ=', 'admin', 'active', '2026-06-07 14:17:35', '2026-06-07 14:17:35'),
(2, 'EMP02', 'john@gmail.com', '$2y$12$OdpJbsuDHbH1fYPPp1qPr.kSLhOk1vzcqDW7LzuN90l/lq39UTTga', 'VXNlcjEyMzQ=', 'manager', 'active', '2026-06-07 14:22:01', '2026-06-07 14:22:01'),
(3, 'EMP03', 'rohit@gmail.com', '$2y$12$9VRjBNcnu6wMBihSnL9ftewIyh.ance1xu5/iyYQXtRs.qnpzFzta', 'VXNlcjEyMzQ=', 'employee', 'active', '2026-06-07 14:22:37', '2026-06-07 14:22:37'),
(4, 'EMP04', 'kumar@gmail.com', '$2y$12$BLZQqec8XKBSrwFF/gMjEeLYnveFow0lFDVd/j6iBjZAk4WVsWRmi', 'VXNlcjEyMzQ=', 'employee', 'active', '2026-06-07 14:23:02', '2026-06-07 14:23:02'),
(5, 'EMP05', 'karthi@gmail.com', '$2y$12$TGtzw2ZrjyJlI/p0mzm0zeWRqe6f/tJpSMI2xU7IEoTXnMR842GSe', 'VXNlcjEyMzQ=', 'employee', 'active', '2026-06-07 14:23:36', '2026-06-07 14:23:36');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_balance`
--
ALTER TABLE `leave_balance`
  ADD CONSTRAINT `leave_balance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_balance_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_requests_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`),
  ADD CONSTRAINT `leave_requests_ibfk_3` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
