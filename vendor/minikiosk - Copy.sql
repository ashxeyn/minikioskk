-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 13, 2024 at 02:44 AM
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
  `campus_location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `opening_time` time DEFAULT NULL,
  `closing_time` time DEFAULT NULL,
  `status` enum('open','closed','maintenance') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `canteens`
--

INSERT INTO `canteens` (`canteen_id`, `name`, `campus_location`, `description`, `opening_time`, `closing_time`, `status`, `created_at`) VALUES
(1, 'Main Canteen', 'Main Campus', 'Primary campus canteen', '07:00:00', '20:00:00', 'open', '2024-12-12 18:13:16'),
(2, 'CCS Canteen', 'CCS Building', 'Computing Studies canteen', '08:00:00', '17:00:00', 'open', '2024-12-12 18:13:16'),
(3, 'Engineering Cafe', 'Engineering Building', 'Engineering building cafe', '07:30:00', '18:00:00', 'open', '2024-12-12 18:13:16'),
(4, 'Science Hub', 'Science Complex', 'Science building food court', '08:00:00', '19:00:00', 'open', '2024-12-12 18:13:16'),
(5, 'Ribshack', 'Campus A', '', '00:00:00', '00:00:00', 'open', '2024-12-13 00:45:43'),
(6, 'Ribshack', 'A', '', '00:00:00', '00:00:00', 'open', '2024-12-13 00:47:23'),
(7, 'Leo', 'A', '', '00:00:00', '00:00:00', 'open', '2024-12-13 01:03:34'),
(8, 'Leo1', 'A', '', '00:00:00', '00:00:00', 'open', '2024-12-13 01:04:15'),
(9, 'Save', 'A', '', '00:00:00', '00:00:00', 'open', '2024-12-13 01:06:48'),
(10, 'Load', 'A', '', '00:00:00', '00:00:00', 'open', '2024-12-13 01:09:09');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','completed','abandoned') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`cart_id`, `user_id`, `created_at`, `updated_at`, `status`) VALUES
(33, 25, '2024-12-13 01:28:05', '2024-12-13 01:28:05', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `product_id`, `quantity`, `unit_price`, `subtotal`, `created_at`, `updated_at`) VALUES
(66, 33, 7, 1, 59.00, 59.00, '2024-12-13 01:28:05', '2024-12-13 01:28:05');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`) VALUES
(1, 'Food', 'All food items'),
(2, 'Beverages', 'All types of drinks'),
(3, 'Utensils', 'Eating and serving utensils'),
(4, 'Toiletries', 'Personal care and hygiene products'),
(5, 'Others', 'Miscellaneous items');

-- --------------------------------------------------------

--
-- Table structure for table `colleges`
--

CREATE TABLE `colleges` (
  `college_id` int(11) NOT NULL,
  `college_name` varchar(255) NOT NULL,
  `abbreviation` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `colleges`
--

INSERT INTO `colleges` (`college_id`, `college_name`, `abbreviation`, `description`) VALUES
(1, 'College of Computing Studies', 'CCS', 'Computing and IT Programs'),
(2, 'College of Engineering', 'COE', 'Engineering Programs'),
(3, 'College of Liberal Arts', 'CLA', 'Liberal Arts Programs'),
(4, 'College of Science and Mathematics', 'CSM', 'Science and Mathematics Programs');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `college_id` int(11) NOT NULL,
  `department_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `college_id`, `department_name`, `description`) VALUES
(1, 1, 'Information Technology', 'IT Department'),
(2, 1, 'Computer Science', 'CS Department'),
(3, 2, 'Civil Engineering', 'CE Department'),
(4, 2, 'Mechanical Engineering', 'ME Department'),
(5, 3, 'English Department', 'English Studies'),
(6, 4, 'Mathematics Department', 'Mathematics Studies');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `employee_number` varchar(50) NOT NULL,
  `position` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guests`
--

CREATE TABLE `guests` (
  `guest_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `purpose` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `managers`
--

CREATE TABLE `managers` (
  `manager_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `canteen_id` int(11) NOT NULL,
  `start_date` date NOT NULL DEFAULT CURRENT_DATE,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`manager_id`),
  KEY `canteen_id` (`canteen_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `managers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `managers_ibfk_2` FOREIGN KEY (`canteen_id`) REFERENCES `canteens` (`canteen_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `managers`
--

INSERT INTO `managers` (`manager_id`, `user_id`, `canteen_id`, `start_date`, `status`, `created_at`) VALUES
(1, 8, 6, '0000-00-00', 'accepted', '2024-12-13 00:47:23'),
(2, 20, 8, '0000-00-00', 'accepted', '2024-12-13 01:04:15'),
(3, 22, 9, '0000-00-00', 'pending', '2024-12-13 01:06:48'),
(4, 24, 10, '0000-00-00', 'accepted', '2024-12-13 01:09:10');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `canteen_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('placed','accepted','preparing','ready','completed','cancelled') DEFAULT 'placed',
  `payment_status` enum('unpaid','paid') DEFAULT 'unpaid',
  `payment_method` enum('cash','e-wallet','card') DEFAULT 'cash',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `canteen_id`, `total_amount`, `status`, `payment_status`, `payment_method`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 150.00, 'completed', 'paid', 'cash', '2024-12-12 18:13:17', '2024-12-12 18:13:17'),
(2, 3, 1, 445.00, 'placed', 'unpaid', 'cash', '2024-12-12 20:28:40', '2024-12-12 20:28:40'),
(3, 3, 1, 75.00, 'placed', 'unpaid', 'cash', '2024-12-12 20:29:24', '2024-12-12 20:29:24'),
(4, 3, 1, 80.00, 'placed', 'unpaid', 'cash', '2024-12-12 20:30:07', '2024-12-12 20:30:07'),
(5, 3, 1, 1500.00, 'placed', 'unpaid', 'cash', '2024-12-12 20:38:36', '2024-12-12 20:38:36'),
(6, 3, 1, 1725.00, 'placed', 'unpaid', 'cash', '2024-12-12 20:59:25', '2024-12-12 20:59:25'),
(7, 3, 1, 75.00, 'placed', 'unpaid', 'cash', '2024-12-12 21:01:57', '2024-12-12 21:01:57'),
(8, 25, 1, 1500.00, 'placed', 'unpaid', 'cash', '2024-12-13 01:25:46', '2024-12-13 01:25:46');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`, `created_at`) VALUES
(1, 1, 1, 2, 75.00, 150.00, '2024-12-12 18:13:17'),
(2, 2, 3, 5, 25.00, 125.00, '2024-12-12 20:28:40'),
(3, 2, 2, 4, 80.00, 320.00, '2024-12-12 20:28:40'),
(4, 3, 1, 1, 75.00, 75.00, '2024-12-12 20:29:24'),
(5, 4, 2, 1, 80.00, 80.00, '2024-12-12 20:30:07'),
(6, 5, 1, 20, 75.00, 1500.00, '2024-12-12 20:38:36'),
(7, 6, 2, 1, 80.00, 80.00, '2024-12-12 20:59:25'),
(8, 6, 3, 1, 25.00, 25.00, '2024-12-12 20:59:25'),
(9, 6, 4, 1, 45.00, 45.00, '2024-12-12 20:59:25'),
(10, 6, 1, 21, 75.00, 1575.00, '2024-12-12 20:59:25'),
(11, 7, 1, 1, 75.00, 75.00, '2024-12-12 21:01:57'),
(12, 8, 1, 20, 75.00, 1500.00, '2024-12-13 01:25:46');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `canteen_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('available','unavailable') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `canteen_id`, `type_id`, `name`, `description`, `price`, `image_url`, `status`, `created_at`) VALUES
(1, 1, 1, 'Chicken Adobo', 'Classic Filipino adobo with rice', 75.00, NULL, 'available', '2024-12-12 18:13:17'),
(2, 1, 1, 'Sinigang', 'Sour soup with pork and vegetables', 80.00, NULL, 'available', '2024-12-12 18:13:17'),
(3, 1, 3, 'Iced Tea', 'Refreshing cold tea', 25.00, NULL, 'available', '2024-12-12 18:13:17'),
(4, 2, 2, 'Sandwich', 'Fresh vegetable sandwich', 45.00, NULL, 'available', '2024-12-12 18:13:17'),
(5, 10, 1, 'Reo', 'asdas', 2.00, NULL, 'available', '2024-12-13 01:10:24'),
(6, 9, 8, 'Reo', 'asdas', 199.00, NULL, 'available', '2024-12-13 01:14:03'),
(7, 10, 1, 'Corned Beef Tapa', 'Freshly Made tinapa', 59.00, NULL, 'available', '2024-12-13 01:23:50');

-- --------------------------------------------------------

--
-- Table structure for table `product_types`
--

CREATE TABLE `product_types` (
  `type_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_types`
--

INSERT INTO `product_types` (`type_id`, `category_id`, `name`, `type`, `description`) VALUES
(1, 1, 'Hot Meals', 'food', 'Freshly cooked main dishes'),
(2, 1, 'Snacks', 'food', 'Light meals and finger foods'),
(3, 2, 'Cold Beverages', 'beverage', 'Refreshing drinks and smoothies'),
(4, 2, 'Hot Beverages', 'beverage', 'Coffee, tea, and other hot drinks'),
(5, 3, 'Disposable Utensils', 'utensil', 'Single-use plates, cups, and cutlery'),
(6, 3, 'Reusable Utensils', 'utensil', 'Washable plates and utensils'),
(7, 4, 'Personal Care', 'toiletry', 'Basic hygiene products'),
(8, 4, 'Cleaning Supplies', 'toiletry', 'Sanitizers and cleaning materials');

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `program_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`program_id`, `department_id`, `program_name`, `description`) VALUES
(1, 1, 'Bachelor of Science in Information Technology', 'BSIT Program'),
(2, 2, 'Bachelor of Science in Computer Science', 'BSCS Program'),
(3, 3, 'Bachelor of Science in Civil Engineering', 'BSCE Program'),
(4, 4, 'Bachelor of Science in Mechanical Engineering', 'BSME Program');

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `stock_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `last_restock` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stocks`
--

INSERT INTO `stocks` (`stock_id`, `product_id`, `quantity`, `last_restock`, `updated_at`) VALUES
(1, 1, 29, '2024-12-12 18:13:17', '2024-12-13 01:25:46'),
(2, 2, 34, '2024-12-12 18:13:17', '2024-12-12 20:59:25'),
(3, 3, 104, '2024-12-12 18:13:17', '2024-12-12 20:59:25'),
(4, 4, 39, '2024-12-12 18:13:17', '2024-12-12 20:59:25');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `student_number` varchar(50) NOT NULL,
  `year_level` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','employee','student','guest') NOT NULL DEFAULT 'student',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `last_name` varchar(255) NOT NULL,
  `given_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `program_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `canteen_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `username`, `password`, `role`, `status`, `last_name`, `given_name`, `middle_name`, `created_at`, `program_id`, `department_id`, `canteen_id`) VALUES
(1, 'admin@wmsu.edu.ph', 'admin', '$2y$10$8WxhJz0q.Y9HYVPgCB8Z8.1P.P8QC8Z3s5GxCPd0ZJz0SCn2.ZkxK', 'admin', 'approved', 'Admin', 'System', 'A', '2024-12-12 18:13:16', NULL, NULL, NULL),
(2, 'manager@wmsu.edu.ph', 'manager', '$2y$10$8WxhJz0q.Y9HYVPgCB8Z8.1P.P8QC8Z3s5GxCPd0ZJz0SCn2.ZkxK', 'manager', 'approved', 'Manager', 'Canteen', 'M', '2024-12-12 18:13:16', NULL, NULL, 1),
(3, 'rei@wmsu.edu.ph', 'Rei@wmsu.edu.ph', '$2y$10$appaWBqQ.Xyn7fB/1uaJQ.QRF3Ah1Udp.MUQBxnTkyKmn8l7NiDzy', 'admin', 'pending', 'Rei', 'Rei', 'O', '2024-12-12 18:14:16', 2, NULL, NULL),
(4, NULL, 'Guest', '$2y$10$mvcYSaYqNxtkxMbelxXlfedK6EKnTVYq3d.1VCXx3Oy2048NczJNa', 'guest', 'approved', '', '', '', '2024-12-12 21:04:39', NULL, NULL, NULL),
(6, 'johnmagno332@wmsu.edu.ph', 'Rei', '$2y$10$scjVpjsaeWkP/8czzjBvzebSfkDGYQOtTzRER0bixW0TYNWfhxMJq', 'student', 'pending', 'Rei', 'Rei', 'O', '2024-12-13 00:33:16', 2, NULL, NULL),
(7, NULL, 'Guest5109', '$2y$10$dnECfxkoFsMYkzHOA9Xf5eGZxSCaNni0eEAet428sa7sIsY1B/1Km', 'guest', 'approved', '', '', '', '2024-12-13 00:44:59', NULL, NULL, NULL),
(8, 'Levi@wmsu.edu.ph', 'Rieie', '$2y$10$CF8njcjfchBb71SwPUZ4Ge4kFs4kL.HimWEwhT/bssGW2yuNcXXwC', 'manager', 'approved', 'ReiRie', 'Rieei', 'O', '2024-12-13 00:47:23', NULL, NULL, NULL),
(11, 'Reign@wmsu.edu.ph', 'Rei152', '$2y$10$uLde9aKUXHvIO63/MiSW4ubEhWKh645dJf/3aN5qZ3DOxLcx.VnTS', 'admin', 'approved', 'Reiei', 'Reieiei', 'o', '2024-12-13 00:51:12', NULL, NULL, NULL),
(13, 'johnmagno3322@wmsu.edu.ph', 'john', '$2y$10$rJa9CHS9vmTgbRwPKSVJLuBHQq9H.x62eV4gz/xTJAwWL7rG3qaOy', 'admin', 'pending', 'Rei', 'Rei', 'O', '2024-12-13 00:53:14', NULL, NULL, NULL),
(14, 'cj@wmsu.edu.ph', 'CJ', '$2y$10$uiZSTB42k4Ek9sXqwhg9Qedvm94dGDUFrXdarfppUHFC2k1GAfc1K', 'student', 'pending', 'rEI', 'rEIEI', 'O', '2024-12-13 00:55:23', 2, NULL, NULL),
(15, 'Leo@wmsu.edu.ph', 'Leo', '$2y$10$qjn81FCcpLytR3wO9F74PeMFOvG8xTweOOQ9KI7.t94MZj0vDMBxW', 'manager', 'pending', 'Leo', 'Leo', 'O', '2024-12-13 01:00:40', 3, NULL, NULL),
(16, NULL, 'Guest7206', '$2y$10$MDSyCEVIBWpfu/D0oef0fORUrg5tjRms0CIzIoDuceZTt.tZVbtze', 'guest', 'approved', '', '', '', '2024-12-13 01:01:40', NULL, NULL, NULL),
(17, NULL, 'Guest4740', '$2y$10$2/KlVfIp532Zz0Tn4o6byOlr5q4cFrHx7rqz4EDa7hRq6.G9clG7i', 'guest', 'approved', '', '', '', '2024-12-13 01:02:58', NULL, NULL, NULL),
(18, NULL, 'Guest1905', '$2y$10$q5NYpEFLKvDp04sROb08D.m1MCxnLuMbxqInlswR.YYFUCLRQ1Ywi', 'guest', 'approved', '', '', '', '2024-12-13 01:03:02', NULL, NULL, NULL),
(20, 'Leo1@wmsu.edu.ph', 'Leo1', '$2y$10$YHv5y4hUFsKFg6QS2Cb0FOxAz9yygQ8Hzzmro0LFPmepv0r9JS1p.', 'manager', 'approved', 'Leo1', 'Leo1', 'l', '2024-12-13 01:04:15', NULL, NULL, NULL),
(21, NULL, 'Guest7607', '$2y$10$PoDpzlTIOSRBW2CSW9mzU.bPpU49naPPpakIjZ1u4/IZO195908uq', 'guest', 'approved', '', '', '', '2024-12-13 01:06:20', NULL, NULL, NULL),
(22, 'Save@wmsu.edu.ph', 'Save', '$2y$10$02pMGPkUQus0/jUoJvGukuKGjws8Vrp2m2gml8EhiIqcNx6CBr4LK', 'manager', 'pending', 'Save', 'Save', 'O', '2024-12-13 01:06:48', NULL, NULL, NULL),
(23, NULL, 'Guest5228', '$2y$10$.f2ZHWqnEUscR9lKQyx1JuXmv9th3ccYwEtNsFU4PtWRkJpc6Lr9a', 'guest', 'approved', '', '', '', '2024-12-13 01:08:31', NULL, NULL, NULL),
(24, 'Load@wmsu.edu.ph', 'Load', '$2y$10$wDrx6IefyezC68q2syj0oe.39UVeRNeLUFnWjDcOQLeE1cXDJ4YUu', 'manager', 'approved', 'Load', 'Load', 'O', '2024-12-13 01:09:10', NULL, NULL, NULL),
(25, 'Mustard@wmsu.edu.ph', 'Mustard', '$2y$10$0kgoZaNIXIpCxblmhCftIuV8jjmNbrBhYiSlcNd.vzuDE3vj/uDJm', 'student', 'pending', 'Mustard', 'Mustard', 'O', '2024-12-13 01:12:44', 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

--
-- Indexes for table `canteens`
--
ALTER TABLE `canteens`
  ADD PRIMARY KEY (`canteen_id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `colleges`
--
ALTER TABLE `colleges`
  ADD PRIMARY KEY (`college_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD KEY `college_id` (`college_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `employee_number` (`employee_number`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `guests`
--
ALTER TABLE `guests`
  ADD PRIMARY KEY (`guest_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `managers`
--
ALTER TABLE `managers`
  ADD PRIMARY KEY (`manager_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `canteen_id` (`canteen_id`);

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
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `canteen_id` (`canteen_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `product_types`
--
ALTER TABLE `product_types`
  ADD PRIMARY KEY (`type_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`program_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`stock_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `program_id` (`program_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `program_id` (`program_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `canteen_id` (`canteen_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `canteens`
--
ALTER TABLE `canteens`
  MODIFY `canteen_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `colleges`
--
ALTER TABLE `colleges`
  MODIFY `college_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `guest_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `managers`
--
ALTER TABLE `managers`
  MODIFY `manager_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `product_types`
--
ALTER TABLE `product_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `stock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`cart_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`college_id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `guests`
--
ALTER TABLE `guests`
  ADD CONSTRAINT `guests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `managers`
--
ALTER TABLE `managers`
  ADD CONSTRAINT `managers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `managers_ibfk_2` FOREIGN KEY (`canteen_id`) REFERENCES `canteens` (`canteen_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`canteen_id`) REFERENCES `canteens` (`canteen_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`canteen_id`) REFERENCES `canteens` (`canteen_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `product_types` (`type_id`);

--
-- Constraints for table `product_types`
--
ALTER TABLE `product_types`
  ADD CONSTRAINT `product_types_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `programs_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE CASCADE;

--
-- Constraints for table `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `stocks_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `users_ibfk_3` FOREIGN KEY (`canteen_id`) REFERENCES `canteens` (`canteen_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
