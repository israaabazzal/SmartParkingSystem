-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 28, 2026 at 12:25 PM
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
-- Database: `smart_parking`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(60) NOT NULL,
  `password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `password_hash`) VALUES
(1, 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `billing_rates`
--

CREATE TABLE `billing_rates` (
  `rate_id` int(11) NOT NULL,
  `rate_per_min` decimal(6,4) NOT NULL DEFAULT 0.0500,
  `violation_multiplier` decimal(4,2) NOT NULL DEFAULT 2.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing_rates`
--

INSERT INTO `billing_rates` (`rate_id`, `rate_per_min`, `violation_multiplier`) VALUES
(1, 0.0500, 2.00);

-- --------------------------------------------------------

--
-- Table structure for table `gates`
--

CREATE TABLE `gates` (
  `gate_id` int(11) NOT NULL,
  `gate_type` enum('entry','exit') NOT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'closed',
  `last_trigger_source` varchar(30) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gates`
--

INSERT INTO `gates` (`gate_id`, `gate_type`, `status`, `last_trigger_source`, `updated_by`) VALUES
(1, 'entry', 'open', 'admin', 1),
(2, 'exit', 'open', 'system', 1);

-- --------------------------------------------------------

--
-- Table structure for table `gate_logs`
--

CREATE TABLE `gate_logs` (
  `log_id` int(11) NOT NULL,
  `gate_id` int(11) NOT NULL,
  `action` enum('open','close') NOT NULL,
  `source` varchar(30) NOT NULL DEFAULT 'admin',
  `admin_id` int(11) DEFAULT NULL,
  `logged_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gate_logs`
--

INSERT INTO `gate_logs` (`log_id`, `gate_id`, `action`, `source`, `admin_id`, `logged_at`) VALUES
(1, 1, 'open', 'admin', 1, '2026-05-22 12:46:07'),
(2, 1, 'close', 'admin', 1, '2026-05-22 12:49:10'),
(3, 2, 'open', 'admin', 1, '2026-05-22 12:49:14'),
(4, 2, 'open', 'system', 1, '2026-05-22 12:54:17'),
(5, 1, 'open', 'admin', 1, '2026-05-22 12:58:04'),
(6, 2, 'open', 'system', 1, '2026-05-22 13:00:39'),
(7, 1, 'open', 'admin', 1, '2026-05-22 13:01:09'),
(8, 1, 'open', 'admin', 1, '2026-05-22 13:04:08'),
(9, 1, 'close', 'admin', 1, '2026-05-22 13:04:17'),
(10, 1, 'open', 'admin', 1, '2026-05-22 17:34:58'),
(11, 1, 'close', 'admin', 1, '2026-05-22 18:01:56'),
(12, 2, 'close', 'admin', 1, '2026-05-22 18:02:04'),
(13, 2, 'open', 'admin', 1, '2026-05-22 18:02:05'),
(14, 1, 'open', 'admin', 1, '2026-06-12 11:27:48'),
(15, 2, 'open', 'system', 1, '2026-06-12 11:32:36'),
(16, 2, 'close', 'admin', 1, '2026-06-12 11:32:49'),
(17, 1, 'close', 'admin', 1, '2026-06-12 11:32:52'),
(18, 2, 'open', 'admin', 1, '2026-06-12 19:16:32'),
(19, 2, 'close', 'admin', 1, '2026-06-12 19:16:33'),
(20, 1, 'open', 'admin', 1, '2026-06-12 19:16:33'),
(21, 1, 'close', 'admin', 1, '2026-06-12 19:16:34'),
(22, 1, 'open', 'admin', 1, '2026-06-13 09:31:35'),
(23, 1, 'open', 'admin', 1, '2026-06-13 09:31:43'),
(24, 2, 'open', 'system', 1, '2026-06-13 09:38:24');

-- --------------------------------------------------------

--
-- Table structure for table `parking_levels`
--

CREATE TABLE `parking_levels` (
  `level_id` int(11) NOT NULL,
  `level_name` varchar(50) NOT NULL,
  `status` enum('open','full') NOT NULL DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_levels`
--

INSERT INTO `parking_levels` (`level_id`, `level_name`, `status`) VALUES
(1, 'Level 1', 'open'),
(2, 'Level 2', 'open'),
(3, 'Level 3', 'open');

-- --------------------------------------------------------

--
-- Table structure for table `parking_sessions`
--

CREATE TABLE `parking_sessions` (
  `session_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `slot_id` int(11) NOT NULL,
  `time_start` datetime DEFAULT NULL,
  `time_end` datetime DEFAULT NULL,
  `duration_seconds` int(11) NOT NULL DEFAULT 0,
  `base_cost` decimal(8,2) NOT NULL DEFAULT 0.00,
  `violation_charge` decimal(8,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(8,2) NOT NULL DEFAULT 0.00,
  `is_double_billing` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','awaiting_payment','completed') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_sessions`
--

INSERT INTO `parking_sessions` (`session_id`, `vehicle_id`, `slot_id`, `time_start`, `time_end`, `duration_seconds`, `base_cost`, `violation_charge`, `total_cost`, `is_double_billing`, `status`) VALUES
(1, 1, 1, '2026-05-22 12:46:59', '2026-05-22 12:48:08', 69, 0.06, 0.00, 0.06, 1, 'completed'),
(2, 1, 2, '2026-05-22 13:00:05', '2026-05-22 13:00:14', 0, 0.00, 0.00, 0.00, 0, 'completed'),
(3, 1, 1, '2026-05-22 13:04:13', '2026-05-22 13:05:28', 75, 0.06, 0.00, 0.06, 1, 'awaiting_payment'),
(4, 4, 2, '2026-05-22 13:04:14', '2026-05-22 13:05:35', 81, 0.07, 0.00, 0.07, 0, 'awaiting_payment'),
(5, 5, 9, '2026-05-22 17:35:09', '2026-05-22 17:35:30', 21, 0.04, 0.00, 0.04, 0, 'awaiting_payment'),
(6, 6, 10, '2026-06-12 11:31:15', '2026-06-12 11:31:20', 4, 0.00, 0.00, 0.00, 0, 'completed'),
(7, 7, 6, '2026-06-13 09:32:01', '2026-06-13 09:37:07', 306, 0.51, 0.00, 0.51, 1, 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `parking_slots`
--

CREATE TABLE `parking_slots` (
  `slot_id` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `slot_code` varchar(20) NOT NULL,
  `vehicle_type` varchar(30) NOT NULL,
  `status` enum('available','occupied') NOT NULL DEFAULT 'available',
  `is_double_billing` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_slots`
--

INSERT INTO `parking_slots` (`slot_id`, `level_id`, `slot_code`, `vehicle_type`, `status`, `is_double_billing`) VALUES
(1, 1, 'L1-S1', 'car', 'occupied', 1),
(2, 1, 'L1-S2', 'car', 'occupied', 0),
(3, 1, 'L1-S3', 'car', 'available', 0),
(4, 1, 'L1-S4', 'car', 'available', 0),
(5, 2, 'L2-S1', 'car', 'available', 0),
(6, 2, 'L2-S2', 'car', 'available', 1),
(7, 2, 'L2-S3', 'car', 'available', 0),
(8, 2, 'L2-S4', 'car', 'available', 0),
(9, 3, 'L3-S1', 'car', 'occupied', 0),
(10, 3, 'L3-S2', 'car', 'available', 0),
(11, 3, 'L3-S3', 'car', 'available', 0),
(12, 3, 'L3-S4', 'car', 'available', 0);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `payment_method` enum('credit_card','mobile_pay','cash','qr_code') NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `status` enum('pending','paid') NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `session_id`, `payment_method`, `amount`, `status`, `paid_at`) VALUES
(1, 1, 'cash', 0.06, 'paid', '2026-05-22 12:54:17'),
(2, 2, 'cash', 0.00, 'paid', '2026-05-22 13:00:39'),
(3, 6, 'cash', 0.00, 'paid', '2026-06-12 11:32:36'),
(4, 3, 'cash', 0.06, 'pending', NULL),
(5, 7, 'cash', 0.51, 'paid', '2026-06-13 09:38:24');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `vehicle_id` int(11) NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `vehicle_type` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `plate_number`, `vehicle_type`) VALUES
(1, 'A1234', 'disabled'),
(4, 'A890', 'suv'),
(5, 'A20', 'suv'),
(6, 'Z1234', 'suv'),
(7, 'A123', 'motorcycle');

-- --------------------------------------------------------

--
-- Table structure for table `violations`
--

CREATE TABLE `violations` (
  `violation_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `status` enum('active','resolved') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `billing_rates`
--
ALTER TABLE `billing_rates`
  ADD PRIMARY KEY (`rate_id`);

--
-- Indexes for table `gates`
--
ALTER TABLE `gates`
  ADD PRIMARY KEY (`gate_id`),
  ADD UNIQUE KEY `gate_type` (`gate_type`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `gate_logs`
--
ALTER TABLE `gate_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `gate_id` (`gate_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `parking_levels`
--
ALTER TABLE `parking_levels`
  ADD PRIMARY KEY (`level_id`);

--
-- Indexes for table `parking_sessions`
--
ALTER TABLE `parking_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `slot_id` (`slot_id`);

--
-- Indexes for table `parking_slots`
--
ALTER TABLE `parking_slots`
  ADD PRIMARY KEY (`slot_id`),
  ADD UNIQUE KEY `slot_code` (`slot_code`),
  ADD KEY `level_id` (`level_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`vehicle_id`),
  ADD UNIQUE KEY `plate_number` (`plate_number`);

--
-- Indexes for table `violations`
--
ALTER TABLE `violations`
  ADD PRIMARY KEY (`violation_id`),
  ADD KEY `session_id` (`session_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `billing_rates`
--
ALTER TABLE `billing_rates`
  MODIFY `rate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `gates`
--
ALTER TABLE `gates`
  MODIFY `gate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `gate_logs`
--
ALTER TABLE `gate_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `parking_levels`
--
ALTER TABLE `parking_levels`
  MODIFY `level_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `parking_sessions`
--
ALTER TABLE `parking_sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `parking_slots`
--
ALTER TABLE `parking_slots`
  MODIFY `slot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `violations`
--
ALTER TABLE `violations`
  MODIFY `violation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `gates`
--
ALTER TABLE `gates`
  ADD CONSTRAINT `gates_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`admin_id`);

--
-- Constraints for table `gate_logs`
--
ALTER TABLE `gate_logs`
  ADD CONSTRAINT `gate_logs_ibfk_1` FOREIGN KEY (`gate_id`) REFERENCES `gates` (`gate_id`),
  ADD CONSTRAINT `gate_logs_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`);

--
-- Constraints for table `parking_sessions`
--
ALTER TABLE `parking_sessions`
  ADD CONSTRAINT `parking_sessions_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`),
  ADD CONSTRAINT `parking_sessions_ibfk_2` FOREIGN KEY (`slot_id`) REFERENCES `parking_slots` (`slot_id`);

--
-- Constraints for table `parking_slots`
--
ALTER TABLE `parking_slots`
  ADD CONSTRAINT `parking_slots_ibfk_1` FOREIGN KEY (`level_id`) REFERENCES `parking_levels` (`level_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `parking_sessions` (`session_id`);

--
-- Constraints for table `violations`
--
ALTER TABLE `violations`
  ADD CONSTRAINT `violations_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `parking_sessions` (`session_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
