-- phpMyAdmin SQL Dump
-- version 5.2.1deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 04, 2025 at 05:48 AM
-- Server version: 10.11.6-MariaDB-0+deb12u1
-- PHP Version: 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `needle_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `dept_id` int(11) NOT NULL,
  `dept_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`dept_id`, `dept_name`) VALUES
(1, 'IT'),
(4, 'PT EMB'),
(5, 'PT Sewing'),
(2, 'Sample'),
(3, 'Stores'),
(6, 'TM EMB'),
(7, 'TM Sewing 1'),
(8, 'TM Sewing 2');

-- --------------------------------------------------------

--
-- Table structure for table `machines`
--

CREATE TABLE `machines` (
  `model_id` int(11) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `model_type` enum('Sewing','Embroidery','Other') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `machines`
--

INSERT INTO `machines` (`model_id`, `model_name`, `serial_number`, `model_type`) VALUES
(1, 'test', 'test1', 'Sewing'),
(2, 'test', 'test2', 'Embroidery');

-- --------------------------------------------------------

--
-- Table structure for table `machine_needle_compatibility`
--

CREATE TABLE `machine_needle_compatibility` (
  `map_id` int(11) NOT NULL,
  `machine_id_fk` int(11) NOT NULL,
  `needle_type_id_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `machine_needle_compatibility`
--

INSERT INTO `machine_needle_compatibility` (`map_id`, `machine_id_fk`, `needle_type_id_fk`) VALUES
(1, 1, 1),
(2, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `needle_inventory`
--

CREATE TABLE `needle_inventory` (
  `inventory_id` int(11) NOT NULL,
  `needle_type_id_fk` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `location` varchar(100) NOT NULL DEFAULT 'Main Store',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `needle_inventory`
--

INSERT INTO `needle_inventory` (`inventory_id`, `needle_type_id_fk`, `quantity`, `location`, `last_updated`) VALUES
(1, 1, 600, 'Main Store', '2025-07-31 11:33:07'),
(2, 2, 300, 'Main Store', '2025-08-04 05:24:46');

-- --------------------------------------------------------

--
-- Table structure for table `needle_requests`
--

CREATE TABLE `needle_requests` (
  `request_id` int(11) NOT NULL,
  `requesting_user_id_fk` int(11) NOT NULL,
  `machine_model_id_fk` int(11) NOT NULL,
  `needle_type_id_fk` int(11) NOT NULL,
  `quantity_requested` int(11) NOT NULL DEFAULT 1,
  `change_reason` varchar(50) NOT NULL COMMENT 'e.g., Broken, Blunted',
  `change_reason_type` varchar(50) DEFAULT NULL,
  `broken_needle_image_path` varchar(255) DEFAULT NULL,
  `request_status` varchar(50) NOT NULL DEFAULT 'Pending Approval',
  `manager_approver_id_fk` int(11) DEFAULT NULL,
  `stores_issuer_id_fk` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `needle_requests`
--

INSERT INTO `needle_requests` (`request_id`, `requesting_user_id_fk`, `machine_model_id_fk`, `needle_type_id_fk`, `quantity_requested`, `change_reason`, `change_reason_type`, `broken_needle_image_path`, `request_status`, `manager_approver_id_fk`, `stores_issuer_id_fk`, `created_at`, `approved_at`, `issued_at`) VALUES
(1, 1, 1, 1, 1, 'fdsz', NULL, 'uploads/6889f31b90e525.45660568.png', '', 1, NULL, '2025-07-30 10:25:31', '2025-07-31 04:19:25', NULL),
(2, 1, 1, 1, 1, 'fs', 'Blunted', 'uploads/6890437a177240.85527289.jpg', 'Issued', 1, 1, '2025-08-04 05:22:02', '2025-08-04 05:23:46', '2025-08-04 05:23:57'),
(3, 1, 1, 1, 1, '', 'Blunted', 'uploads/689043baeeaf76.86910869.jpg', 'Pending Approval', NULL, NULL, '2025-08-04 05:23:06', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `needle_types`
--

CREATE TABLE `needle_types` (
  `needle_type_id` int(11) NOT NULL,
  `needle_sku` varchar(100) NOT NULL,
  `needle_size` varchar(50) NOT NULL,
  `manufacturer` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `needle_types`
--

INSERT INTO `needle_types` (`needle_type_id`, `needle_sku`, `needle_size`, `manufacturer`) VALUES
(1, '44x34ggh', '11./055', NULL),
(2, '6689fx08ff', 'gfdg48', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `order_id` int(11) NOT NULL,
  `purchase_request_id_fk` int(11) DEFAULT NULL,
  `needle_type_id_fk` int(11) NOT NULL,
  `quantity_ordered` int(11) NOT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `order_notes` text DEFAULT NULL,
  `order_status` varchar(50) NOT NULL DEFAULT 'Placed',
  `placing_user_id_fk` int(11) NOT NULL,
  `placed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `received_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`order_id`, `purchase_request_id_fk`, `needle_type_id_fk`, `quantity_ordered`, `supplier_name`, `order_notes`, `order_status`, `placing_user_id_fk`, `placed_at`, `received_at`) VALUES
(1, NULL, 1, 500, 'test', 'sef', 'Delivered', 1, '2025-07-31 06:45:02', '2025-07-31 11:20:35'),
(2, NULL, 2, 200, NULL, NULL, 'Delivered', 1, '2025-08-04 05:24:15', '2025-08-04 05:24:46');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `prequest_id` int(11) NOT NULL,
  `needle_type_id_fk` int(11) NOT NULL,
  `quantity_requested` int(11) NOT NULL,
  `requesting_user_id_fk` int(11) NOT NULL,
  `request_notes` text DEFAULT NULL,
  `request_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(2, 'Admin'),
(3, 'Manager'),
(7, 'Observer'),
(4, 'Operator'),
(6, 'Purchasing'),
(5, 'Stores'),
(1, 'Super Admin');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role_id_fk` int(11) NOT NULL,
  `dept_id_fk` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `full_name`, `password_hash`, `role_id_fk`, `dept_id_fk`, `is_active`, `created_at`) VALUES
(1, 'nirmal', 'Nirmal Himansha', '$2y$10$ByWx71grscozkhhQkAzK6eSkB4s5UkT34dKFulJk1SKYGoOWJyB.6', 1, NULL, 1, '2025-07-30 07:57:14'),
(2, 'madushanka', 'Madushanka', '$2y$10$r1oo1yrg1UOJIWcaQpoR0uDWMDpLCU3P3Lq6gonFC1cJF10Qu.aqm', 2, NULL, 1, '2025-07-30 09:12:28'),
(3, 'lorena', 'Lorena', '$2y$10$MhuQ/lhiTODy7Nybd1EjjuvQy2.Mu4a/wQbH1WNWrFWjsl6mjKTru', 4, NULL, 1, '2025-07-30 09:12:56'),
(4, 'nalin', 'Nalin', '$2y$10$hH.OZFCcgz4ZJWYIsdyaieJj2v9va7KIhoS5p0Gbn9hp.Ruf0jzg.', 3, NULL, 1, '2025-07-30 09:14:12'),
(5, 'thushara', 'Thushara', '$2y$10$j8VUZXyz2.bcYVzsUjegwuFI.mqvJOFNWH9p91U1VfRtCyX8rJ0eq', 5, NULL, 1, '2025-07-30 09:14:31'),
(6, 'udeni', 'Udeni', '$2y$10$HFKfFitFKl3YsUhz.CdR9eDxDLn8S45MO/cc9WpqJs./KoPTf81wW', 6, NULL, 1, '2025-07-30 09:15:18'),
(7, 'prasangi', 'Prasangi', '$2y$10$KTkePBpL0/DunnsIKx16E.iSsbchOMtMppv4B2LbJlAk8IHyC96VK', 7, NULL, 1, '2025-07-30 09:15:46'),
(8, 'sadeepa', 'Sadeepa Gayashan', '$2y$10$TFwZb..wZNx570ebkXPW2OOqKaQVG3u80ZMeswIJBZTglp1O2kdVu', 2, 1, 1, '2025-08-01 09:06:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`dept_id`),
  ADD UNIQUE KEY `dept_name` (`dept_name`);

--
-- Indexes for table `machines`
--
ALTER TABLE `machines`
  ADD PRIMARY KEY (`model_id`);

--
-- Indexes for table `machine_needle_compatibility`
--
ALTER TABLE `machine_needle_compatibility`
  ADD PRIMARY KEY (`map_id`),
  ADD UNIQUE KEY `unique_mapping` (`machine_id_fk`,`needle_type_id_fk`),
  ADD KEY `needle_type_id_fk` (`needle_type_id_fk`);

--
-- Indexes for table `needle_inventory`
--
ALTER TABLE `needle_inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD UNIQUE KEY `needle_type_id_fk` (`needle_type_id_fk`);

--
-- Indexes for table `needle_requests`
--
ALTER TABLE `needle_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `requesting_user_id_fk` (`requesting_user_id_fk`),
  ADD KEY `machine_model_id_fk` (`machine_model_id_fk`),
  ADD KEY `needle_type_id_fk` (`needle_type_id_fk`),
  ADD KEY `manager_approver_id_fk` (`manager_approver_id_fk`),
  ADD KEY `stores_issuer_id_fk` (`stores_issuer_id_fk`);

--
-- Indexes for table `needle_types`
--
ALTER TABLE `needle_types`
  ADD PRIMARY KEY (`needle_type_id`),
  ADD UNIQUE KEY `needle_sku` (`needle_sku`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `needle_type_id_fk` (`needle_type_id_fk`),
  ADD KEY `placing_user_id_fk` (`placing_user_id_fk`),
  ADD KEY `purchase_request_id_fk` (`purchase_request_id_fk`);

--
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`prequest_id`),
  ADD KEY `needle_type_id_fk` (`needle_type_id_fk`),
  ADD KEY `requesting_user_id_fk` (`requesting_user_id_fk`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id_fk` (`role_id_fk`),
  ADD KEY `dept_id_fk` (`dept_id_fk`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `dept_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `machines`
--
ALTER TABLE `machines`
  MODIFY `model_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `machine_needle_compatibility`
--
ALTER TABLE `machine_needle_compatibility`
  MODIFY `map_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `needle_inventory`
--
ALTER TABLE `needle_inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `needle_requests`
--
ALTER TABLE `needle_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `needle_types`
--
ALTER TABLE `needle_types`
  MODIFY `needle_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  MODIFY `prequest_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `machine_needle_compatibility`
--
ALTER TABLE `machine_needle_compatibility`
  ADD CONSTRAINT `machine_needle_compatibility_ibfk_1` FOREIGN KEY (`machine_id_fk`) REFERENCES `machines` (`model_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `machine_needle_compatibility_ibfk_2` FOREIGN KEY (`needle_type_id_fk`) REFERENCES `needle_types` (`needle_type_id`) ON DELETE CASCADE;

--
-- Constraints for table `needle_inventory`
--
ALTER TABLE `needle_inventory`
  ADD CONSTRAINT `needle_inventory_ibfk_1` FOREIGN KEY (`needle_type_id_fk`) REFERENCES `needle_types` (`needle_type_id`);

--
-- Constraints for table `needle_requests`
--
ALTER TABLE `needle_requests`
  ADD CONSTRAINT `needle_requests_ibfk_1` FOREIGN KEY (`requesting_user_id_fk`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `needle_requests_ibfk_2` FOREIGN KEY (`machine_model_id_fk`) REFERENCES `machines` (`model_id`),
  ADD CONSTRAINT `needle_requests_ibfk_3` FOREIGN KEY (`needle_type_id_fk`) REFERENCES `needle_types` (`needle_type_id`),
  ADD CONSTRAINT `needle_requests_ibfk_4` FOREIGN KEY (`manager_approver_id_fk`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `needle_requests_ibfk_5` FOREIGN KEY (`stores_issuer_id_fk`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`needle_type_id_fk`) REFERENCES `needle_types` (`needle_type_id`),
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`placing_user_id_fk`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `purchase_orders_ibfk_3` FOREIGN KEY (`purchase_request_id_fk`) REFERENCES `purchase_requests` (`prequest_id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD CONSTRAINT `purchase_requests_ibfk_1` FOREIGN KEY (`needle_type_id_fk`) REFERENCES `needle_types` (`needle_type_id`),
  ADD CONSTRAINT `purchase_requests_ibfk_2` FOREIGN KEY (`requesting_user_id_fk`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id_fk`) REFERENCES `roles` (`role_id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`dept_id_fk`) REFERENCES `departments` (`dept_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
