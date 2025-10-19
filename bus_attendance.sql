-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2025 at 09:39 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bus_attendance`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendants`
--

CREATE TABLE `attendants` (
  `id` int(11) NOT NULL,
  `attendant_name` varchar(100) NOT NULL,
  `attendant_id_number` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `salary` decimal(10,2) DEFAULT 0.00,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendants`
--

INSERT INTO `attendants` (`id`, `attendant_name`, `attendant_id_number`, `phone`, `email`, `experience_years`, `salary`, `address`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Fulko Luchi', 'ERHGJ', '9999999999', 'sdfghj@gmail.com', 10, 20000.00, 'jhbshjbfkhsi,hsfeydegjl -54546', 'active', '2025-10-16 11:06:33', '2025-10-16 11:06:33'),
(2, 'Ranu Mandal', 'RANU69', '8558854485', 'ranuburi@gmail.com', 50, 10000000.00, 'Kolkata', 'active', '2025-10-16 11:16:08', '2025-10-16 11:16:08');

-- --------------------------------------------------------

--
-- Table structure for table `buses`
--

CREATE TABLE `buses` (
  `id` int(11) NOT NULL,
  `bus_number` varchar(50) NOT NULL,
  `bus_name` varchar(100) DEFAULT NULL,
  `capacity` int(11) NOT NULL,
  `bus_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buses`
--

INSERT INTO `buses` (`id`, `bus_number`, `bus_name`, `capacity`, `bus_type`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'wb12bh0022', 'de', 100, 'AC', 'xyz', 'active', '2025-10-10 23:13:23', '2025-10-10 23:28:51');

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int(11) NOT NULL,
  `driver_name` varchar(100) NOT NULL,
  `driver_id_number` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `license_number` varchar(50) NOT NULL,
  `license_expiry` date DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `salary` decimal(10,2) DEFAULT 0.00,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `gender` enum('male','female') DEFAULT 'male',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `driver_name`, `driver_id_number`, `phone`, `email`, `license_number`, `license_expiry`, `experience_years`, `salary`, `address`, `status`, `gender`, `created_at`, `updated_at`) VALUES
(1, 'suroit', '584605226718', '8240770108', 'surojitde6@gmail.com', 'abcdtest', '2029-02-21', 5, 15000.00, 'abcd', 'active', 'male', '2025-10-10 22:54:40', '2025-10-11 00:03:53');

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `id` int(11) NOT NULL,
  `route_name` varchar(100) NOT NULL,
  `route_code` varchar(50) NOT NULL,
  `start_location` varchar(100) NOT NULL,
  `end_location` varchar(100) NOT NULL,
  `distance` decimal(8,2) DEFAULT 0.00,
  `estimated_time` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`id`, `route_name`, `route_code`, `start_location`, `end_location`, `distance`, `estimated_time`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'airport - dakshineshwar', 'abcd', 'airport', 'dakshineshwar', 6.00, 40, '', 'active', '2025-10-11 00:10:23', '2025-10-11 00:10:50'),
(2, 'fghjk', 'RT-001', 'abc', 'xyz', 140.00, 180, 'gvjshbjhlkfkjfaf jkdbfjaebj', 'active', '2025-10-15 07:10:04', '2025-10-15 07:10:04');

-- --------------------------------------------------------

--
-- Table structure for table `route_attendant`
--

CREATE TABLE `route_attendant` (
  `id` int(11) NOT NULL,
  `way` varchar(100) DEFAULT NULL,
  `attendant` int(11) DEFAULT NULL,
  `bus` int(11) DEFAULT NULL,
  `route` int(11) DEFAULT NULL,
  `driver` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `route_attendant`
--

INSERT INTO `route_attendant` (`id`, `way`, `attendant`, `bus`, `route`, `driver`, `status`, `created_at`, `updated_at`) VALUES
(1, 'to_go', 1, 1, 1, 1, 'active', '2025-10-19 18:19:59', '2025-10-19 05:35:35');

-- --------------------------------------------------------

--
-- Table structure for table `route_sub_destinations`
--

CREATE TABLE `route_sub_destinations` (
  `id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `destination_name` varchar(100) NOT NULL,
  `distance` decimal(8,2) DEFAULT 0.00,
  `estimated_time` int(11) DEFAULT 0,
  `sequence_order` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `route_sub_destinations`
--

INSERT INTO `route_sub_destinations` (`id`, `route_id`, `destination_name`, `distance`, `estimated_time`, `sequence_order`, `created_at`) VALUES
(2, 1, 'mathkal', 3.00, 20, 1, '2025-10-11 00:10:50'),
(3, 1, '3 no', 3.00, 20, 2, '2025-10-11 00:10:50'),
(4, 2, 'er', 1.00, 15, 1, '2025-10-15 07:10:04'),
(5, 2, 'dds', 8.00, 70, 2, '2025-10-15 07:10:04');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `img_path` varchar(255) DEFAULT NULL,
  `route_id` int(11) NOT NULL,
  `sub_route_id` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `address` text NOT NULL,
  `gender` enum('male','female') DEFAULT 'male',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `student_name`, `img_path`, `route_id`, `sub_route_id`, `phone`, `guardian_phone`, `father_name`, `mother_name`, `address`, `gender`, `status`, `created_at`, `updated_at`) VALUES
(2, 'fddghjk', 'Rajesh', '68ef7f6692308_call-img-4.jpg', 1, 3, '9999999994', '9999999923', 'Rakesh', 'Rama', 'mumbai', 'male', 'active', '2025-10-15 11:03:02', '2025-10-16 07:43:39'),
(3, 'ST-4556', 'Rahul', '68f0bcf28917f_449677325_1917794958683620_346920412017773980_n.jpg', 2, 4, '9999999994', '9999999998', 'dsryetdjkghjk', 'sdfghjkll', '9999999994', 'male', 'active', '2025-10-16 09:37:54', '2025-10-16 09:37:54');

-- --------------------------------------------------------

--
-- Table structure for table `super_admins`
--

CREATE TABLE `super_admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `super_admins`
--

INSERT INTO `super_admins` (`id`, `username`, `email`, `password`, `full_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$8QK2dOJs6rn8U6gqpvxUm.0TSPD4oHpiZOxjx/QKFzeYMU354O7l2', 'সুপার অ্যাডমিন', '2025-10-10 22:28:54', '2025-10-16 11:35:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendants`
--
ALTER TABLE `attendants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendant_id_number` (`attendant_id_number`);

--
-- Indexes for table `buses`
--
ALTER TABLE `buses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bus_number` (`bus_number`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `driver_id_number` (`driver_id_number`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `route_code` (`route_code`);

--
-- Indexes for table `route_attendant`
--
ALTER TABLE `route_attendant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendant` (`attendant`),
  ADD KEY `bus` (`bus`),
  ADD KEY `route` (`route`),
  ADD KEY `driver` (`driver`);

--
-- Indexes for table `route_sub_destinations`
--
ALTER TABLE `route_sub_destinations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `route_id` (`route_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- Indexes for table `super_admins`
--
ALTER TABLE `super_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendants`
--
ALTER TABLE `attendants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `route_attendant`
--
ALTER TABLE `route_attendant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `route_sub_destinations`
--
ALTER TABLE `route_sub_destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `super_admins`
--
ALTER TABLE `super_admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `route_attendant`
--
ALTER TABLE `route_attendant`
  ADD CONSTRAINT `route_attendant_ibfk_1` FOREIGN KEY (`attendant`) REFERENCES `attendants` (`id`),
  ADD CONSTRAINT `route_attendant_ibfk_2` FOREIGN KEY (`bus`) REFERENCES `buses` (`id`),
  ADD CONSTRAINT `route_attendant_ibfk_3` FOREIGN KEY (`route`) REFERENCES `routes` (`id`),
  ADD CONSTRAINT `route_attendant_ibfk_4` FOREIGN KEY (`driver`) REFERENCES `drivers` (`id`);

--
-- Constraints for table `route_sub_destinations`
--
ALTER TABLE `route_sub_destinations`
  ADD CONSTRAINT `route_sub_destinations_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
