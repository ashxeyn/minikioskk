-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 19, 2024 at 07:40 PM
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
-- Database: `minikiosk1`
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
(8, 'Leo1', 'A', '', '00:00:00', '00:00:00', 'open', '2024-12-13 01:04:15'),
(10, 'Load', 'A', '', '00:00:00', '00:00:00', 'open', '2024-12-13 01:09:09'),
(11, 'Buns', 'A', '', '00:00:00', '00:00:00', 'open', '2024-12-13 13:29:07'),
(12, 'tintin', 'tin', '', '00:00:00', '00:00:00', 'open', '2024-12-13 13:48:35'),
(13, 'Myak', 'asdd', '', '00:00:00', '00:00:00', 'open', '2024-12-13 13:59:13'),
(14, 'meow', 'Science Complex', '', '00:00:00', '00:00:00', 'open', '2024-12-13 21:34:17'),
(15, 'asdf', 'asdfs', '', '00:00:00', '00:00:00', 'open', '2024-12-14 01:52:56'),
(16, 'Marco', 'askkl', NULL, NULL, NULL, 'open', '2024-12-17 12:54:09'),
(17, 'Perdo Eatery', 'Main Campus', '', '00:00:00', '00:00:00', 'open', '2024-12-17 14:52:05'),
(18, 'Prio', 'Main Campus', '', '00:00:00', '00:00:00', 'open', '2024-12-19 18:16:58'),
(19, 'prio', 'Main Campus', '', '00:00:00', '00:00:00', 'open', '2024-12-19 18:23:42'),
(20, 'prio', 'Main Campus', '', '00:00:00', '00:00:00', 'open', '2024-12-19 18:33:46');

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
(4, 'College of Science and Mathematics', 'CSM', 'Science and Mathematics Programs'),
(5, 'College of Nursing', 'CN', 'Nursing'),
(6, 'College of Asian and Islamic Studies', 'CAIS', 'CAISs'),
(7, 'College of Home Economics', 'CHE', 'CHE'),
(8, 'College of Nursing', 'CN', 'kasjdlk');

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
(3, 2, 'Civil Engineering', 'CE Departments'),
(4, 2, 'Mechanical Engineering', 'ME Department'),
(5, 3, 'English Department', 'English Studies'),
(6, 4, 'Mathematics Department', 'Mathematics Studies'),
(7, 5, 'Nursing', 'Nursing');

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
  `manager_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `canteen_id` int(11) NOT NULL,
  `start_date` date NOT NULL DEFAULT curdate(),
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rejection_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `managers`
--

INSERT INTO `managers` (`manager_id`, `user_id`, `canteen_id`, `start_date`, `status`, `created_at`, `rejection_reason`) VALUES
(1, 8, 6, '0000-00-00', 'accepted', '2024-12-13 00:47:23', NULL),
(2, 20, 8, '0000-00-00', 'accepted', '2024-12-13 01:04:15', NULL),
(4, 24, 10, '0000-00-00', 'accepted', '2024-12-13 01:09:10', NULL),
(5, 27, 11, '2024-12-13', 'accepted', '2024-12-13 13:29:07', NULL),
(6, 28, 12, '2024-12-13', 'accepted', '2024-12-13 13:48:35', NULL),
(7, 31, 13, '2024-12-13', 'accepted', '2024-12-13 13:59:13', NULL),
(9, 23, 2, '2024-12-11', 'pending', '2024-12-14 01:50:47', NULL),
(10, 44, 15, '2024-12-14', 'accepted', '2024-12-14 01:52:56', NULL),
(11, 64, 17, '2024-12-17', 'accepted', '2024-12-17 14:52:05', NULL),
(12, 65, 13, '2024-12-17', 'accepted', '2024-12-17 15:30:12', NULL),
(15, 70, 20, '2024-12-19', 'accepted', '2024-12-19 18:33:46', 'botlog');

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
(8, 25, 1, 1500.00, 'placed', 'unpaid', 'cash', '2024-12-13 01:25:46', '2024-12-13 01:25:46'),
(9, 26, 10, 118.00, 'placed', 'unpaid', 'cash', '2024-12-13 13:13:03', '2024-12-13 13:13:03'),
(10, 26, 10, 177.00, 'placed', 'unpaid', 'cash', '2024-12-13 13:13:34', '2024-12-13 13:13:34'),
(11, 25, 10, 118.00, 'completed', 'unpaid', 'cash', '2024-12-13 13:14:29', '2024-12-13 13:15:07'),
(12, 25, 1, 80.00, 'placed', 'unpaid', 'cash', '2024-12-13 13:20:04', '2024-12-13 13:20:04'),
(13, 25, 1, 80.00, 'placed', 'unpaid', 'cash', '2024-12-13 13:20:27', '2024-12-13 13:20:27'),
(14, 25, 1, 80.00, 'placed', 'unpaid', 'cash', '2024-12-13 13:23:24', '2024-12-13 13:23:24'),
(15, 30, 1, 80.00, 'completed', 'unpaid', 'cash', '2024-12-13 15:12:59', '2024-12-13 18:10:00'),
(16, 30, 10, 59.00, 'placed', 'unpaid', 'cash', '2024-12-13 18:10:57', '2024-12-13 18:10:57'),
(17, 30, 1, 25.00, 'placed', 'unpaid', 'cash', '2024-12-13 18:11:23', '2024-12-13 18:11:23'),
(18, 30, 1, 25.00, 'placed', 'unpaid', 'cash', '2024-12-13 18:22:00', '2024-12-13 18:22:00'),
(19, 30, 2, 45.00, 'completed', 'unpaid', 'cash', '2024-12-13 18:22:31', '2024-12-13 18:32:03'),
(20, 30, 2, 90.00, 'placed', 'unpaid', 'cash', '2024-12-13 19:28:39', '2024-12-13 19:28:39'),
(21, 30, 1, 80.00, 'placed', 'unpaid', 'cash', '2024-12-13 19:51:52', '2024-12-13 19:51:52'),
(32, 45, 2, 90.00, 'placed', 'unpaid', 'cash', '2024-12-14 03:57:28', '2024-12-14 03:57:28'),
(38, 45, 10, 354.00, 'completed', 'unpaid', 'cash', '2024-12-14 04:35:59', '2024-12-14 04:36:30'),
(39, 30, 1, 80.00, 'placed', 'unpaid', 'cash', '2024-12-15 15:14:04', '2024-12-15 15:14:04'),
(40, 30, 13, 55.00, 'cancelled', 'unpaid', 'cash', '2024-12-15 18:54:46', '2024-12-17 07:26:53'),
(41, 30, 13, 30.00, 'ready', 'unpaid', 'cash', '2024-12-15 18:56:47', '2024-12-17 07:27:24'),
(42, 30, 13, 30.00, 'cancelled', 'unpaid', 'cash', '2024-12-15 18:57:23', '2024-12-17 07:26:56'),
(43, 30, 13, 30.00, 'ready', 'unpaid', 'cash', '2024-12-15 18:58:20', '2024-12-17 07:27:31'),
(44, 30, 13, 30.00, 'ready', 'unpaid', 'cash', '2024-12-15 18:58:52', '2024-12-15 19:04:47'),
(45, 30, 1, 25.00, 'placed', 'unpaid', 'cash', '2024-12-15 19:00:12', '2024-12-15 19:00:12'),
(46, 30, 2, 45.00, 'placed', 'unpaid', 'cash', '2024-12-15 22:08:06', '2024-12-15 22:08:06'),
(47, 30, 13, 30.00, 'ready', 'unpaid', 'cash', '2024-12-16 16:15:34', '2024-12-16 19:43:58'),
(48, 30, 13, 50.00, 'completed', 'unpaid', 'cash', '2024-12-17 07:28:21', '2024-12-17 12:57:12'),
(49, 30, 13, 50.00, 'placed', 'unpaid', 'cash', '2024-12-17 15:35:06', '2024-12-17 15:35:06'),
(50, 30, 13, 50.00, 'cancelled', 'unpaid', 'cash', '2024-12-17 15:35:41', '2024-12-17 15:36:03'),
(51, 30, 1, 25.00, 'placed', 'unpaid', 'cash', '2024-12-17 15:36:54', '2024-12-17 15:36:54'),
(52, 30, 13, 50.00, 'placed', 'unpaid', 'cash', '2024-12-17 15:49:39', '2024-12-17 15:49:39'),
(53, 67, 13, 50.00, 'cancelled', 'unpaid', 'cash', '2024-12-17 17:14:33', '2024-12-17 17:14:47'),
(54, 67, 13, 50.00, 'completed', 'unpaid', 'cash', '2024-12-17 17:15:06', '2024-12-17 17:16:55'),
(55, 67, 13, 50.00, 'completed', 'unpaid', 'cash', '2024-12-17 17:17:22', '2024-12-17 17:18:11'),
(56, 30, 13, 50.00, 'placed', 'unpaid', 'cash', '2024-12-17 19:11:55', '2024-12-17 19:11:55'),
(57, 71, 13, 50.00, 'placed', 'unpaid', 'cash', '2024-12-19 18:40:31', '2024-12-19 18:40:31');

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
(2, 2, 3, 5, 25.00, 125.00, '2024-12-12 20:28:40'),
(8, 6, 3, 1, 25.00, 25.00, '2024-12-12 20:59:25'),
(9, 6, 4, 1, 45.00, 45.00, '2024-12-12 20:59:25'),
(13, 9, 7, 2, 59.00, 118.00, '2024-12-13 13:13:03'),
(14, 10, 7, 3, 59.00, 177.00, '2024-12-13 13:13:34'),
(15, 11, 7, 2, 59.00, 118.00, '2024-12-13 13:14:29'),
(20, 16, 7, 1, 59.00, 59.00, '2024-12-13 18:10:57'),
(21, 17, 3, 1, 25.00, 25.00, '2024-12-13 18:11:23'),
(22, 18, 3, 1, 25.00, 25.00, '2024-12-13 18:22:00'),
(23, 19, 4, 1, 45.00, 45.00, '2024-12-13 18:22:31'),
(24, 20, 4, 2, 45.00, 90.00, '2024-12-13 19:28:39'),
(36, 32, 4, 2, 45.00, 90.00, '2024-12-14 03:57:28'),
(42, 38, 7, 6, 59.00, 354.00, '2024-12-14 04:35:59'),
(45, 40, 3, 1, 25.00, 25.00, '2024-12-15 18:54:46'),
(50, 45, 3, 1, 25.00, 25.00, '2024-12-15 19:00:13'),
(51, 46, 4, 1, 45.00, 45.00, '2024-12-15 22:08:06'),
(53, 48, 17, 1, 50.00, 50.00, '2024-12-17 07:28:21'),
(54, 49, 17, 1, 50.00, 50.00, '2024-12-17 15:35:06'),
(55, 50, 17, 1, 50.00, 50.00, '2024-12-17 15:35:41'),
(56, 51, 3, 1, 25.00, 25.00, '2024-12-17 15:36:54'),
(57, 52, 17, 1, 50.00, 50.00, '2024-12-17 15:49:39'),
(58, 53, 17, 1, 50.00, 50.00, '2024-12-17 17:14:33'),
(59, 54, 17, 1, 50.00, 50.00, '2024-12-17 17:15:06'),
(60, 55, 17, 1, 50.00, 50.00, '2024-12-17 17:17:22'),
(61, 56, 17, 1, 50.00, 50.00, '2024-12-17 19:11:55'),
(62, 57, 17, 1, 50.00, 50.00, '2024-12-19 18:40:31');

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
(3, 1, 3, 'Iced Tea', 'Refreshing cold teas', 25.00, NULL, 'available', '2024-12-12 18:13:17'),
(4, 2, 2, 'Sandwich', 'Fresh vegetable sandwich', 45.00, NULL, 'available', '2024-12-12 18:13:17'),
(7, 10, 1, 'Corned Beef Tapa', 'Freshly Made tinapas', 59.00, NULL, 'available', '2024-12-13 01:23:50'),
(17, 13, 2, 'Kangkong cheaps', 'kangkong sarap', 50.00, NULL, 'available', '2024-12-17 07:26:19');

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
(1, 7, 'Bachelor of Science in Nursing', 'mm'),
(3, 2, 'Bachelor of Science in Information Technology', 'mm');

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
(3, 3, 98, '2024-12-12 18:13:17', '2024-12-17 15:36:54'),
(4, 4, 48, '2024-12-15 21:40:23', '2024-12-15 22:08:06'),
(3, 3, 98, '2024-12-12 18:13:17', '2024-12-17 15:36:54'),
(4, 4, 48, '2024-12-15 21:40:23', '2024-12-15 22:08:06'),
(0, 7, 6, NULL, '2024-12-14 04:35:59'),
(0, 4, 48, '2024-12-15 21:40:23', '2024-12-15 22:08:06'),
(0, 17, 93, '2024-12-17 07:26:19', '2024-12-19 18:40:31');

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
  `canteen_id` int(11) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `username`, `password`, `role`, `status`, `last_name`, `given_name`, `middle_name`, `created_at`, `program_id`, `department_id`, `canteen_id`, `rejection_reason`) VALUES
(1, 'admin@wmsu.edu.ph', 'admin', '$2y$10$8WxhJz0q.Y9HYVPgCB8Z8.1P.P8QC8Z3s5GxCPd0ZJz0SCn2.ZkxK', 'admin', 'approved', 'Adminz', 'System', 'A', '2024-12-12 18:13:16', NULL, NULL, NULL, NULL),
(2, 'manager@wmsu.edu.ph', 'manager', '$2y$10$8WxhJz0q.Y9HYVPgCB8Z8.1P.P8QC8Z3s5GxCPd0ZJz0SCn2.ZkxK', 'manager', 'approved', 'Manager', 'Canteen', 'M', '2024-12-12 18:13:16', NULL, NULL, 1, NULL),
(3, 'rei@wmsu.edu.ph', 'Rei@wmsu.edu.ph', '$2y$10$appaWBqQ.Xyn7fB/1uaJQ.QRF3Ah1Udp.MUQBxnTkyKmn8l7NiDzy', 'admin', 'pending', 'Rei', 'Rei', 'O', '2024-12-12 18:14:16', 2, NULL, NULL, NULL),
(6, 'johnmagno332@wmsu.edu.ph', 'Rei', '$2y$10$scjVpjsaeWkP/8czzjBvzebSfkDGYQOtTzRER0bixW0TYNWfhxMJq', 'student', 'pending', 'Rei', 'Rei', 'O', '2024-12-13 00:33:16', 2, NULL, NULL, NULL),
(8, 'Levi@wmsu.edu.ph', 'Rieie', '$2y$10$CF8njcjfchBb71SwPUZ4Ge4kFs4kL.HimWEwhT/bssGW2yuNcXXwC', 'manager', 'approved', 'ReiRie', 'Rieei', 'O', '2024-12-13 00:47:23', NULL, NULL, NULL, NULL),
(11, 'Reign@wmsu.edu.ph', 'Rei152', '$2y$10$uLde9aKUXHvIO63/MiSW4ubEhWKh645dJf/3aN5qZ3DOxLcx.VnTS', 'admin', 'approved', 'Reiei', 'Reieiei', 'o', '2024-12-13 00:51:12', NULL, NULL, NULL, NULL),
(13, 'johnmagno3322@wmsu.edu.ph', 'john', '$2y$10$rJa9CHS9vmTgbRwPKSVJLuBHQq9H.x62eV4gz/xTJAwWL7rG3qaOy', 'admin', 'pending', 'Rei', 'Rei', 'O', '2024-12-13 00:53:14', NULL, NULL, NULL, NULL),
(14, 'cj@wmsu.edu.ph', 'CJ', '$2y$10$uiZSTB42k4Ek9sXqwhg9Qedvm94dGDUFrXdarfppUHFC2k1GAfc1K', 'student', 'pending', 'rEI', 'rEIEI', 'O', '2024-12-13 00:55:23', 2, NULL, NULL, NULL),
(15, 'Leo@wmsu.edu.ph', 'Leo', '$2y$10$qjn81FCcpLytR3wO9F74PeMFOvG8xTweOOQ9KI7.t94MZj0vDMBxW', 'manager', 'pending', 'Leo', 'Leo', 'O', '2024-12-13 01:00:40', 3, NULL, NULL, NULL),
(17, NULL, 'Guest4740', '$2y$10$2/KlVfIp532Zz0Tn4o6byOlr5q4cFrHx7rqz4EDa7hRq6.G9clG7i', 'guest', 'approved', '', '', '', '2024-12-13 01:02:58', NULL, NULL, NULL, NULL),
(18, NULL, 'Guest1905', '$2y$10$q5NYpEFLKvDp04sROb08D.m1MCxnLuMbxqInlswR.YYFUCLRQ1Ywi', 'guest', 'approved', '', '', '', '2024-12-13 01:03:02', NULL, NULL, NULL, NULL),
(20, 'Leo1@wmsu.edu.ph', 'Leo1', '$2y$10$YHv5y4hUFsKFg6QS2Cb0FOxAz9yygQ8Hzzmro0LFPmepv0r9JS1p.', 'manager', 'approved', 'Leo1', 'Leo1', 'l', '2024-12-13 01:04:15', NULL, NULL, NULL, NULL),
(21, NULL, 'Guest7607', '$2y$10$PoDpzlTIOSRBW2CSW9mzU.bPpU49naPPpakIjZ1u4/IZO195908uq', 'guest', 'approved', '', '', '', '2024-12-13 01:06:20', NULL, NULL, NULL, NULL),
(22, 'Save@wmsu.edu.ph', 'Save', '$2y$10$02pMGPkUQus0/jUoJvGukuKGjws8Vrp2m2gml8EhiIqcNx6CBr4LK', 'manager', 'approved', 'Save', 'Save', 'O', '2024-12-13 01:06:48', NULL, NULL, NULL, NULL),
(23, NULL, 'Guest5228', '$2y$10$.f2ZHWqnEUscR9lKQyx1JuXmv9th3ccYwEtNsFU4PtWRkJpc6Lr9a', 'guest', 'approved', '', '', '', '2024-12-13 01:08:31', NULL, NULL, NULL, NULL),
(24, 'Load@wmsu.edu.ph', 'Load', '$2y$10$wDrx6IefyezC68q2syj0oe.39UVeRNeLUFnWjDcOQLeE1cXDJ4YUu', 'manager', 'approved', 'Load', 'Load', 'O', '2024-12-13 01:09:10', NULL, NULL, NULL, NULL),
(25, 'Mustard@wmsu.edu.ph', 'Mustard', '$2y$10$0kgoZaNIXIpCxblmhCftIuV8jjmNbrBhYiSlcNd.vzuDE3vj/uDJm', 'student', 'pending', 'Mustard', 'Mustard', 'O', '2024-12-13 01:12:44', 2, NULL, NULL, NULL),
(26, 'Lux@wmsu.edu.ph', 'Lux', '$2y$10$dFWutFM29llzG.kAIhqhx.RZNOmsLDPjhN8TJAOn6ODIz/22ZJ7de', 'student', 'pending', 'Lux', 'Lux', 'O', '2024-12-13 13:12:03', 2, NULL, NULL, NULL),
(27, 'bun@wmsu.edu.ph', 'Bun', '$2y$10$WBLOflY2Y0mxzW6N0Y.lgeJDrviGZQAdnV16zIJJseiyobNAJBHk2', 'manager', 'approved', 'Bun', 'Bun', 'O', '2024-12-13 13:29:07', NULL, NULL, NULL, NULL),
(28, 'tin@gmail.com', 'tin123', '$2y$10$Ptwhph5HczUfXa5/Le1ZTuXxkb4tCvc65IEJfv2PIUFMBWxAXDIT6', 'manager', 'approved', 'Tin', 'Tin', 'O', '2024-12-13 13:48:35', NULL, NULL, NULL, NULL),
(30, 'meow11@gmail.com', 'meow', '$2y$10$4Fzif/yz.cII9DCbz6nIa.OZB73OeCRArqqe5zANsK2A1hMj/.uoC', 'guest', 'approved', 'asdf', 'asdf', 'asdf', '2024-12-13 13:54:06', NULL, NULL, NULL, NULL),
(31, 'mayk@gmail.com', 'mayk', '$2y$10$5SNVd4BZAs/5lImMW/JQyumvmLeZH/nHZuiCfxh8jRs4uCO.j2N7y', 'manager', 'approved', 'Mike', 'rey', 'admin1', '2024-12-13 13:59:13', NULL, NULL, NULL, NULL),
(39, 'faminianochristianjude@gmail.com', 'christian12345', '$2y$10$1HpPD4.z00jaLwXg5aBa4u9SrGYxG4n2pdIeXj1E7r8woaDbULGUS', 'employee', '', 'Cath', 'Mortiss', 'Meow', '2024-12-13 23:05:54', NULL, NULL, NULL, NULL),
(42, 'faminianochkjhristianjude@gmail.com', 'christian12345kjkohj', '$2y$10$l5Pv0/GNNlM4zh3lNkBzr.NExzOpE5lM5dNLwnPToW..gxQdS6RYa', 'employee', '', 'Cath', 'Mortiss', 'Meow', '2024-12-13 23:06:14', NULL, NULL, NULL, NULL),
(44, 'mosdaa@gmail.com', 'asdfsd', '$2y$10$KynNi5TZq0QslqvQoLEMaunzi35CHjK7/3/HvHN4Wf2Gfr/gsM2kG', 'manager', 'approved', 'l;wer', 'qwer', 'wer', '2024-12-14 01:52:56', NULL, NULL, NULL, NULL),
(45, 'roroo@gmail.com', 'ashxeynx', '$2y$10$ZKduRd3xvLAoiaOjG20PMOjGLs.oInnvhsdl1WgesJcMXWpT7YH82', 'guest', 'approved', 'asdf', 'asdg', 'fassdf', '2024-12-14 03:21:19', NULL, NULL, NULL, NULL),
(46, 'admin@example.com', 'admin2', '$2y$10$gpejOKqd4Craszci6Bosc.j8eQVxMDVNCd3HlNGr3uqhqaUcWeVPa', 'admin', '', 'admins', 'admin', 'admin', '2024-12-17 09:00:41', NULL, NULL, NULL, NULL),
(63, 'pertivo@gmail.com', 'pertivo', '$2y$10$HDNafUrGCEWr7AoI7Z.JR.NEKepOpzFuxQ.vnwkEqL91tAH.IfHKC', 'guest', 'pending', 'Man', 'asd', 'sss', '2024-12-17 09:14:46', NULL, NULL, NULL, NULL),
(64, 'perdo@wmsu.edu.ph', 'perdo', '$2y$10$PNVL2dQBobzHulZELOq0VuGne4iO0cqpqDmOQS869EbQHlKu2qi0C', 'manager', 'approved', 'perdo', 'penduko', 'Persis', '2024-12-17 14:52:05', NULL, NULL, NULL, NULL),
(65, 'mkaykor@gmail.com', 'mike123', '$2y$10$pcQyehWi7R6XrhgBhq8rUeTKz5Ake4zI3M7k4ripDAprvqa.vuyD2', 'manager', 'approved', 'mike', 'mike', 'ike', '2024-12-17 15:30:12', NULL, NULL, NULL, NULL),
(66, 'eh202201151@wmsu.edu.ph', 'arlie', '$2y$10$4FxTTBEipTGFYjjqDs.TneD1eWFh8.8tHmouiWRC81FKf5k3ECL5m', 'student', 'pending', 'lukson', 'arlie', 'mae', '2024-12-17 15:47:05', 3, NULL, NULL, NULL),
(67, 'meowko@wmsu.edu.ph', 'meowko', '$2y$10$saxd4Qv99zTeg/MuxsYrjeT/Y2R4tK8qIth1ePB.VCvTZPq.N9opu', 'student', 'pending', 'meow', 'm', 'sdf', '2024-12-17 17:13:58', 1, NULL, NULL, NULL),
(70, 'prio@wmsu.edu.ph', 'prio', '$2y$10$wGZpg2IvZAGfEfjhydGzI.8yK0FOw0vr.zEHlDR6iNEAxTjAa7p46', 'manager', 'approved', 'prio', 'prio', 'prio', '2024-12-19 18:33:46', NULL, NULL, NULL, 'botlog'),
(71, 'porki@wmsu.edu.ph', 'porki', '$2y$10$4DLjr3wcziLMdsmy1VKKgurEbfGzU0EjV9TNJ0Df4f9JBzjKWV53S', 'student', 'pending', 'porki', 'porki', 'porki', '2024-12-19 18:40:17', 1, NULL, NULL, NULL);

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
  ADD KEY `canteen_id` (`canteen_id`),
  ADD KEY `user_id` (`user_id`);

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
  ADD KEY `programs_ibfk_1` (`department_id`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD KEY `stocks_ibfk_1` (`product_id`);

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
  MODIFY `canteen_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `colleges`
--
ALTER TABLE `colleges`
  MODIFY `college_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  MODIFY `manager_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `product_types`
--
ALTER TABLE `product_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

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
  ADD CONSTRAINT `managers_ibfk_2` FOREIGN KEY (`canteen_id`) REFERENCES `canteens` (`canteen_id`) ON DELETE CASCADE;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
