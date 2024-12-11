-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 11, 2024 at 07:52 PM
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
-- Database: `minikiosk`
--

-- --------------------------------------------------------

--
-- Table structure for table `canteens`
--

CREATE TABLE `canteens` (
  `canteen_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `campus_location` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `canteens`
--

INSERT INTO `canteens` (`canteen_id`, `name`, `campus_location`) VALUES
(1, 'Romhels Milkteate', 'Campus A fronting open field'),
(2, 'Barcode', 'Campus A near CTE park'),
(8, 'Burger House', 'Campus A beside covered court'),
(9, 'New Canteen', 'College of Law side building'),
(10, 'Green Canteen', 'Campus A Cafeteria'),
(11, 'Beverages', 'Campus A Cafeteria'),
(12, 'Masungit na Tindera', 'Campus B'),
(13, 'Masungit na Tindera', 'Campus B'),
(14, 'Patir Place', 'Campus B beside CAIS'),
(18, 'Masungit na Tindera', 'Campus B beside Garments'),
(19, 'Basta', 'Campus A near elementary'),
(21, 'Burgeran', 'Campus A'),
(25, 'test', 'test'),
(26, 'test', 'test'),
(27, 'test', 'test'),
(28, 'itlog', 'golti'),
(29, 'itlog', 'golti'),
(30, 'basta', 'sheesh'),
(33, 'test', 'Campus A fronting open field'),
(38, 'Canteen ni Flow.G', 'Harap ng Sementeryo Near Social Work'),
(40, 'Mike ni Canteen', 'Near'),
(41, 'Mike ni Canteen', 'Near'),
(42, 'ASD', 'asd'),
(43, 'dafsdasd', 'asdgsg'),
(44, 'asdfsadghhg', 'g');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `canteen_id` int(11) DEFAULT NULL,
  `status` enum('pending','accepted','cancelled','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `queue_number` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `canteen_id`, `status`, `created_at`, `updated_at`, `queue_number`) VALUES
(47, 22, 1, 'completed', '2024-11-17 05:27:07', '2024-12-06 15:36:00', 7),
(54, 11, 1, 'pending', '2024-11-21 06:53:19', '2024-11-21 06:53:19', 8),
(65, 22, NULL, 'pending', '2024-12-11 18:51:29', '2024-12-11 18:51:29', 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(8,2) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `canteen_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `total_price`, `price`, `canteen_id`) VALUES
(51, 47, 45, 1, 70.00, 0.00, 2),
(81, 65, 57, 1, 25.00, 25.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `canteen_id` int(11) DEFAULT NULL,
  `category` enum('Drinks and Beverages','Snacks','Meals','Fruits') NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `canteen_id`, `category`, `name`, `description`, `price`) VALUES
(45, 2, 'Snacks', 'Barcode Burger', 'Juicy beef burger with fresh lettuce', 70.00),
(52, 2, 'Meals', 'Patir', 'Muslim Delicacy', 30.00),
(53, 9, 'Snacks', 'Lumpia', 'Crispy lumpia', 100.00),
(54, 18, 'Snacks', 'Patir', 'sccsc', 222.00),
(55, 1, 'Snacks', 'Empanada', 'Basta', 12.00),
(57, 38, 'Snacks', 'burgir', 'kkk;l', 25.00);

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `program_id` int(11) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `college` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`program_id`, `program_name`, `department`, `college`) VALUES
(1, 'BS in Computer Science', 'Computer Science Department', 'College of Computing Studies'),
(2, 'BS in Information Technology', 'Department of IT', 'College of Computing Studies'),
(3, 'BS in Nursing', 'Department of nursing', 'College of Nursing'),
(5, 'BS in Chemistry', 'Chem Dep', 'College of Science and Mathematics');

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stocks`
--

INSERT INTO `stocks` (`product_id`, `quantity`, `status`) VALUES
(45, 12313, 'In Stock'),
(52, 123, 'In Stock'),
(53, 43, 'In Stock'),
(55, 234, 'In Stock'),
(57, 20, 'In Stock');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `given_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `program_id` int(11) DEFAULT NULL,
  `canteen_id` int(11) DEFAULT NULL,
  `is_student` tinyint(1) DEFAULT 0,
  `is_employee` tinyint(1) DEFAULT 0,
  `is_manager` tinyint(1) DEFAULT 0,
  `is_admin` tinyint(1) DEFAULT 0,
  `is_guest` tinyint(1) DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin','manager','employee','student','guest','pending_manager') DEFAULT 'pending_manager'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `username`, `password`, `last_name`, `given_name`, `middle_name`, `program_id`, `canteen_id`, `is_student`, `is_employee`, `is_manager`, `is_admin`, `is_guest`, `status`, `created_at`, `role`) VALUES
(1, 'ashxeyn@gmail.com', 'ashxeynx', '$2y$10$mHj.aoz/eLqaREbsNiOt6.KK2UWUjLPviCLHdOEP424VCrE1pAp8W', 'Jimenez', 'Shane Hart', 'Duran', NULL, NULL, 0, 0, 0, 1, 0, 'pending', '2024-11-01 14:27:12', 'admin'),
(11, 'HZ202300259@wmsu.edu.ph', 'employee01', '$2y$10$g7lVRsgRqO/Kp6xgGwUbQOx9.yWor6oy7zyU5VxfDb8YXVNlUNcqW', 'Kulong', 'Rone Paullan', 'Gellecania', NULL, NULL, 0, 1, 0, 0, 0, 'pending', '2024-11-01 20:49:45', 'employee'),
(12, 'jazzypark456hd@gmail.com', 'manager12', '$2y$10$puaS8kbvyRyaU/ATlYou0OpDJmTgfp83ahKJGgR7IEoizmYGMmJJ.', 'Roque', 'Jazzper', 'Dimain', NULL, 1, 0, 0, 1, 0, 0, 'pending', '2024-11-01 21:09:19', 'manager'),
(13, 'margie@gmail.com', 'manager13', '$2y$10$wcKSQGn8m3WduTMiyr4C/esf7kqJmHMw7ZvZsTi.2TpZYP.2EN2hu', 'Clarion', 'Margie', 'Veracruz', NULL, 2, 0, 0, 1, 0, 0, 'pending', '2024-11-01 21:35:21', 'manager'),
(16, 'hz202300371@wmsu.edu.ph', 'ronron', '$2y$10$jp/VWPy1AZGSPzAr6H9J6Oq7a7SdcC7OLkKeDj9YyKQqn6bOQdPpC', 'Kulong', 'Rone Paullan', 'Gellecania', 1, NULL, 1, 0, 0, 0, 0, 'pending', '2024-11-01 22:08:06', 'student'),
(22, 'hz202301257@wmsu.edu.ph', 'JazzForYou', '$2y$10$7P2hyEf.y4H0blICcv4rCuFuYvw9aQq3/Vq9yEiutU1hP0j7fPUEm', 'Roque', 'Jazzper', 'Dimain', 1, NULL, 1, 0, 0, 0, 0, 'pending', '2024-11-03 08:20:34', 'student'),
(23, 'HZ202312339@wmsu.edu.ph', 'customer', '$2y$10$OIswgG9IcDnjK5yJnVMA6uCl8Fm2Dr6AqnvFPcUcR0g.r4fPPr9uO', 'Jimenez', 'John', 'Francisco', 1, NULL, 1, 0, 0, 0, 0, 'pending', '2024-11-26 05:31:44', 'student'),
(24, 'Sheesh@wmsu.edu.ph', 'sheesh', '$2y$10$1cNAe8nTcO1qlVE/dj67zuOlULkdgvlljWzk2BprdmMozbOk56JhS', 'Sheesh', 'Sheesh', 'Sheesh', NULL, NULL, 0, 1, 0, 0, 0, 'pending', '2024-12-09 04:10:26', 'employee'),
(25, '', '', '$2y$10$Fr6P3I/CovTyxD/H/WZ3iepO8cQaqVPNUXo9f5f3OXVYF9wn9wT5W', '', '', '', NULL, NULL, 0, 0, 0, 0, 1, 'pending', '2024-12-09 05:05:19', ''),
(27, 'flowg@gmail.com', 'flow.g', '$2y$10$v0m1JKDZf0h3OgaovCMcOuRDEDiHuywTNwq0MBAeLGwTWvX6oKc66', 'G', 'Flow', '.', NULL, 38, 0, 0, 1, 0, 0, 'pending', '2024-12-10 14:48:11', 'manager'),
(29, NULL, 'Guest', '$2y$10$1oG5jDaaa6BdTdE10.6G2eMXG5h5KjQHvv7Up4i2YWOZAkUFSaxSq', '', '', '', NULL, NULL, 0, 0, 0, 0, 1, 'pending', '2024-12-11 09:53:03', 'guest'),
(30, NULL, 'Guest3718', '$2y$10$mxZVgDtmoBNHCWj6vD46leqML9rL8RBwtC8vxhGt8YfptXrb5tN..', '', '', '', NULL, NULL, 0, 0, 0, 0, 1, 'pending', '2024-12-11 12:49:23', 'guest'),
(31, NULL, 'Guest7809', '$2y$10$g6abLxZZ/fGi895XNimGQOnZ6DdmF5SLCBjhDKe1TMskvRfiWsSm2', '', '', '', NULL, NULL, 0, 0, 0, 0, 1, 'pending', '2024-12-11 12:55:50', 'guest'),
(32, '2002@wmsu.edu.ph', 'mana123', '$2y$10$SOQzTQVTL/uk8/MxqD9pY.mfnspmadbfzB8821pfh7M2YDTALSRd6', 'Christian jude', 'meow', 'p', NULL, NULL, 0, 1, 0, 0, 0, 'pending', '2024-12-11 13:40:57', 'employee'),
(33, '2002@gmail.com', 'mana1234', '$2y$10$wCA4lFi0gK0HUsXvWqDul.ir3q2kpHc3wYNr5V3MtqyKnjlwRNBvi', 'Riyal', 'wow', 'p', NULL, NULL, 0, 0, 0, 0, 1, 'pending', '2024-12-11 13:58:26', 'guest'),
(34, NULL, 'Guest2957', '$2y$10$Cl5DESQ4T2TQ.saho6Frk.lTHDNLLqAnarBOEqaZgpiQ68GZBgUce', '', '', '', NULL, NULL, 0, 0, 0, 0, 1, 'pending', '2024-12-11 14:06:23', 'guest'),
(35, NULL, 'Guest1208', '$2y$10$S5LAJp4yuz/ax0N1LaBzruAze.NFQbBKeL9Qr2OjRL7mJA0Q6Z77.', '', '', '', NULL, NULL, 0, 0, 0, 0, 1, 'pending', '2024-12-11 14:08:55', 'guest'),
(36, NULL, 'Guest5182', '$2y$10$BlIgeS3KC9PRaJ2RTfxvcOV.REugT5uO69fVqQx/XUz1LQB.DzfYe', '', '', '', NULL, NULL, 0, 0, 0, 0, 1, 'pending', '2024-12-11 16:44:25', 'guest'),
(37, NULL, 'Guest1884', '$2y$10$FkajJnB.bmD44flYGipe..8Rj/SJynNBLkmI5nduKy1GCHBMAJMhS', '', '', '', NULL, NULL, 0, 0, 0, 0, 1, 'pending', '2024-12-11 16:54:14', 'guest'),
(38, NULL, 'Guest2845', '$2y$10$P.xod6We398OGwJnbxQyUOLo05meG2WtdcNNkdo9/ofwnbKcBY1na', '', '', '', NULL, NULL, 0, 0, 0, 0, 1, 'pending', '2024-12-11 17:05:35', 'guest'),
(39, NULL, 'Guest8458', '$2y$10$IhqRV6epee4BdVCYLI.BBuV7t6q/VsUpMNrGZM4vTKtxqWpUgbDKS', '', '', '', NULL, NULL, 0, 0, 0, 0, 1, 'pending', '2024-12-11 17:05:41', 'guest'),
(40, 'mike@gmail.com', 'mike123', '$2y$10$y3Zwi4rLUwbhsaQPYPW9jOwsr.llVRyqk5mar9hy/atvVKuuMUBfC', 'jordan', 'Michael', 'flow.g', NULL, 41, 0, 0, 1, 0, 0, 'pending', '2024-12-11 17:08:15', 'manager'),
(41, NULL, 'Guest1369', '$2y$10$NVDeDwiuKpEe8fu1sF2cT.wWbHbsyvz3YlOtasjeXtCd29l2JAqkK', '', '', '', NULL, NULL, 0, 0, 0, 0, 1, 'pending', '2024-12-11 17:43:47', 'guest'),
(42, 'asd@gmail.com', 'asd', '$2y$10$hT9ew3YWtRVJ2lP2vSH8WOOiaTFaTtCy7EJoMf9ICrNeE5VrCsPw2', 'asd', 'qwe', 'ashxeynx', NULL, 42, 0, 0, 0, 0, 0, 'pending', '2024-12-11 17:44:18', 'manager'),
(43, NULL, 'Guest1551', '$2y$10$fXk5VrC.TAVoqpPXP3HbbupHElUQj4mWcKaEile3/JmPIkMfDF8tm', '', '', '', NULL, NULL, 0, 0, 0, 0, 1, 'pending', '2024-12-11 17:51:09', 'guest'),
(44, NULL, 'Guest5769', '$2y$10$ukCUmx3Te2ZAB8HsV.ba0evTo.cTwDCaHqOWV2CAc/gqI8rgYM5yq', '', '', '', NULL, NULL, 0, 0, 0, 0, 1, 'pending', '2024-12-11 17:51:21', 'guest'),
(45, 'adsf@gmial.com', 'user11', '$2y$10$6BxbrrktVK6tl8BS8ggRS.rcm8ic6/HqtXW2UVJmo78ItcT47oG/i', 'asdffgdfd', 'dfgdf', 'asd', NULL, 43, 0, 0, 1, 0, 0, 'approved', '2024-12-11 17:52:22', 'manager'),
(46, NULL, 'Guest7065', '$2y$10$vx.OqdvV/GxUqKd8jLA1te8n7N5lJxlX5VU.7XrTkJeCu5gYShRru', '', '', '', NULL, NULL, 0, 0, 0, 0, 1, 'pending', '2024-12-11 17:57:30', 'guest'),
(47, 'sdf@gmial.com', 'user1234', '$2y$10$j9jhTjbPfFgpKgTbpm9Cgewq8d8YQtVY0L17f0GdYRDxkj1bcU1ai', 'wdkgj&#039;kafdg', 'asdfldf', 'manager13', NULL, 44, 0, 0, 1, 0, 0, 'approved', '2024-12-11 17:58:12', 'manager'),
(48, 'Helo@wmsu.edu.ph', 'heloworld', '$2y$10$Mqze5MlGIXEsrQb9zmReMOVXKKhg4RZ1tdwjcv/T0Y6Qp1D/djBKC', 'Hello', 'World', 'asd', NULL, NULL, 0, 1, 0, 0, 0, 'pending', '2024-12-11 18:00:44', 'employee');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `set_user_role` BEFORE INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.is_admin = 1 THEN
        SET NEW.role = 'admin';
    ELSEIF NEW.is_manager = 1 THEN
        SET NEW.role = 'manager';
    ELSEIF NEW.is_employee = 1 THEN
        SET NEW.role = 'employee';
    ELSEIF NEW.is_student = 1 THEN
        SET NEW.role = 'student';
    ELSEIF NEW.is_guest = 1 THEN
        SET NEW.role = 'guest';
    ELSE
        SET NEW.role = 'pending_manager';
    END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `canteens`
--
ALTER TABLE `canteens`
  ADD PRIMARY KEY (`canteen_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `canteen_id` (`canteen_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `canteen_id` (`canteen_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `canteen_id` (`canteen_id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`program_id`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `program_id` (`program_id`),
  ADD KEY `canteen_id` (`canteen_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `canteens`
--
ALTER TABLE `canteens`
  MODIFY `canteen_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`canteen_id`) REFERENCES `canteens` (`canteen_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`canteen_id`) REFERENCES `canteens` (`canteen_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`canteen_id`) REFERENCES `canteens` (`canteen_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`canteen_id`) REFERENCES `canteens` (`canteen_id`) ON DELETE CASCADE;

--
-- Constraints for table `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `stocks_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`canteen_id`) REFERENCES `canteens` (`canteen_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
