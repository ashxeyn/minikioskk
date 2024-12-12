-- Create the database and set character set
CREATE DATABASE IF NOT EXISTS `minikiosk` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_general_ci;

USE `minikiosk`;

-- Drop tables if they exist (in correct order due to foreign key constraints)
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `stocks`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `product_types`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `managers`;
DROP TABLE IF EXISTS `employees`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `guests`;
DROP TABLE IF EXISTS `user_profiles`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `programs`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `colleges`;
DROP TABLE IF EXISTS `canteens`;

-- Create tables in proper order (base tables first)

-- Colleges table
CREATE TABLE `colleges` (
  `college_id` int(11) NOT NULL AUTO_INCREMENT,
  `college_name` varchar(255) NOT NULL,
  `abbreviation` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`college_id`)
) ENGINE=InnoDB;

-- Departments table
CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL AUTO_INCREMENT,
  `college_id` int(11) NOT NULL,
  `department_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`department_id`),
  FOREIGN KEY (`college_id`) REFERENCES `colleges` (`college_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Programs table
CREATE TABLE `programs` (
  `program_id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`program_id`),
  FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Canteens table
CREATE TABLE `canteens` (
  `canteen_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `campus_location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `opening_time` time DEFAULT NULL,
  `closing_time` time DEFAULT NULL,
  `status` enum('open','closed','maintenance') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`canteen_id`)
) ENGINE=InnoDB;

-- Users table
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`),
  FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`),
  FOREIGN KEY (`canteen_id`) REFERENCES `canteens` (`canteen_id`)
) ENGINE=InnoDB;

-- User profiles table
CREATE TABLE `user_profiles` (
  `profile_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `given_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`profile_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- User type tables
CREATE TABLE `students` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `student_number` varchar(50) NOT NULL,
  `year_level` int(11) DEFAULT NULL,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `student_number` (`student_number`),
  UNIQUE KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`)
) ENGINE=InnoDB;

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `employee_number` varchar(50) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`employee_id`),
  UNIQUE KEY `employee_number` (`employee_number`),
  UNIQUE KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`)
) ENGINE=InnoDB;

CREATE TABLE `managers` (
  `manager_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `canteen_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`manager_id`),
  UNIQUE KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`canteen_id`) REFERENCES `canteens` (`canteen_id`)
) ENGINE=InnoDB;

CREATE TABLE `guests` (
  `guest_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `purpose` text DEFAULT NULL,
  PRIMARY KEY (`guest_id`),
  UNIQUE KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Product related tables
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB;

CREATE TABLE `product_types` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`type_id`),
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB;

-- Insert initial category data FIRST
INSERT INTO `categories` (`name`, `description`) VALUES
('Food', 'All food items'),
('Beverages', 'All types of drinks'),
('Utensils', 'Eating and serving utensils'),
('Toiletries', 'Personal care and hygiene products'),
('Others', 'Miscellaneous items');

-- THEN insert product types
INSERT INTO `product_types` (`category_id`, `name`, `type`, `description`) VALUES
((SELECT category_id FROM categories WHERE name = 'Food'), 'Hot Meals', 'food', 'Freshly cooked main dishes'),
((SELECT category_id FROM categories WHERE name = 'Food'), 'Snacks', 'food', 'Light meals and finger foods'),
((SELECT category_id FROM categories WHERE name = 'Beverages'), 'Cold Beverages', 'beverage', 'Refreshing drinks and smoothies'),
((SELECT category_id FROM categories WHERE name = 'Beverages'), 'Hot Beverages', 'beverage', 'Coffee, tea, and other hot drinks'),
((SELECT category_id FROM categories WHERE name = 'Utensils'), 'Disposable Utensils', 'utensil', 'Single-use plates, cups, and cutlery'),
((SELECT category_id FROM categories WHERE name = 'Utensils'), 'Reusable Utensils', 'utensil', 'Washable plates and utensils'),
((SELECT category_id FROM categories WHERE name = 'Toiletries'), 'Personal Care', 'toiletry', 'Basic hygiene products'),
((SELECT category_id FROM categories WHERE name = 'Toiletries'), 'Cleaning Supplies', 'toiletry', 'Sanitizers and cleaning materials');

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `canteen_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('available','unavailable') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`product_id`),
  FOREIGN KEY (`canteen_id`) REFERENCES `canteens` (`canteen_id`),
  FOREIGN KEY (`type_id`) REFERENCES `product_types` (`type_id`)
) ENGINE=InnoDB;

CREATE TABLE `stocks` (
  `stock_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `last_restock` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`stock_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- After all other table definitions but before data insertions, add:
CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `canteen_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','accepted','preparing','ready','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('unpaid','paid') DEFAULT 'unpaid',
  `payment_method` enum('cash','e-wallet','card') DEFAULT 'cash',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`order_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  FOREIGN KEY (`canteen_id`) REFERENCES `canteens` (`canteen_id`)
) ENGINE=InnoDB;

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`item_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
) ENGINE=InnoDB;

-- Insert sample colleges
INSERT INTO `colleges` (`college_name`, `abbreviation`, `description`) VALUES
('College of Computing Studies', 'CCS', 'Computing and IT Programs'),
('College of Engineering', 'COE', 'Engineering Programs'),
('College of Liberal Arts', 'CLA', 'Liberal Arts Programs'),
('College of Science and Mathematics', 'CSM', 'Science and Mathematics Programs');

-- Insert sample departments
INSERT INTO `departments` (`college_id`, `department_name`, `description`) VALUES
((SELECT college_id FROM colleges WHERE abbreviation = 'CCS'), 'Information Technology', 'IT Department'),
((SELECT college_id FROM colleges WHERE abbreviation = 'CCS'), 'Computer Science', 'CS Department'),
((SELECT college_id FROM colleges WHERE abbreviation = 'COE'), 'Civil Engineering', 'CE Department'),
((SELECT college_id FROM colleges WHERE abbreviation = 'COE'), 'Mechanical Engineering', 'ME Department'),
((SELECT college_id FROM colleges WHERE abbreviation = 'CLA'), 'English Department', 'English Studies'),
((SELECT college_id FROM colleges WHERE abbreviation = 'CSM'), 'Mathematics Department', 'Mathematics Studies');

-- Insert sample programs
INSERT INTO `programs` (`department_id`, `program_name`, `description`) VALUES
((SELECT department_id FROM departments WHERE department_name = 'Information Technology'), 'Bachelor of Science in Information Technology', 'BSIT Program'),
((SELECT department_id FROM departments WHERE department_name = 'Computer Science'), 'Bachelor of Science in Computer Science', 'BSCS Program'),
((SELECT department_id FROM departments WHERE department_name = 'Civil Engineering'), 'Bachelor of Science in Civil Engineering', 'BSCE Program'),
((SELECT department_id FROM departments WHERE department_name = 'Mechanical Engineering'), 'Bachelor of Science in Mechanical Engineering', 'BSME Program');

-- Insert sample canteens
INSERT INTO `canteens` (`name`, `campus_location`, `description`, `opening_time`, `closing_time`, `status`) VALUES
('Main Canteen', 'Main Campus', 'Primary campus canteen', '07:00:00', '20:00:00', 'open'),
('CCS Canteen', 'CCS Building', 'Computing Studies canteen', '08:00:00', '17:00:00', 'open'),
('Engineering Cafe', 'Engineering Building', 'Engineering building cafe', '07:30:00', '18:00:00', 'open'),
('Science Hub', 'Science Complex', 'Science building food court', '08:00:00', '19:00:00', 'open');

-- Insert sample admin user
INSERT INTO `users` (
    `email`, `username`, `password`, `role`, `status`, 
    `last_name`, `given_name`, `middle_name`
) VALUES (
    'admin@wmsu.edu.ph', 
    'admin', 
    '$2y$10$8WxhJz0q.Y9HYVPgCB8Z8.1P.P8QC8Z3s5GxCPd0ZJz0SCn2.ZkxK', -- password: admin123
    'admin',
    'approved',
    'Admin',
    'System',
    'A'
);

-- Insert sample manager
INSERT INTO `users` (
    `email`, `username`, `password`, `role`, `status`, 
    `last_name`, `given_name`, `middle_name`, `canteen_id`
) VALUES (
    'manager@wmsu.edu.ph',
    'manager',
    '$2y$10$8WxhJz0q.Y9HYVPgCB8Z8.1P.P8QC8Z3s5GxCPd0ZJz0SCn2.ZkxK', -- password: admin123
    'manager',
    'approved',
    'Manager',
    'Canteen',
    'M',
    (SELECT canteen_id FROM canteens WHERE name = 'Main Canteen')
);

-- Insert sample products
INSERT INTO `products` (`canteen_id`, `type_id`, `name`, `description`, `price`, `status`) VALUES
((SELECT canteen_id FROM canteens WHERE name = 'Main Canteen'),
 (SELECT type_id FROM product_types WHERE name = 'Hot Meals'),
 'Chicken Adobo', 'Classic Filipino adobo with rice', 75.00, 'available'),
 
((SELECT canteen_id FROM canteens WHERE name = 'Main Canteen'),
 (SELECT type_id FROM product_types WHERE name = 'Hot Meals'),
 'Sinigang', 'Sour soup with pork and vegetables', 80.00, 'available'),
 
((SELECT canteen_id FROM canteens WHERE name = 'Main Canteen'),
 (SELECT type_id FROM product_types WHERE name = 'Cold Beverages'),
 'Iced Tea', 'Refreshing cold tea', 25.00, 'available'),
 
((SELECT canteen_id FROM canteens WHERE name = 'CCS Canteen'),
 (SELECT type_id FROM product_types WHERE name = 'Snacks'),
 'Sandwich', 'Fresh vegetable sandwich', 45.00, 'available');

-- Insert initial stocks
INSERT INTO `stocks` (`product_id`, `quantity`, `last_restock`) VALUES
((SELECT product_id FROM products WHERE name = 'Chicken Adobo'), 50, CURRENT_TIMESTAMP),
((SELECT product_id FROM products WHERE name = 'Sinigang'), 30, CURRENT_TIMESTAMP),
((SELECT product_id FROM products WHERE name = 'Iced Tea'), 100, CURRENT_TIMESTAMP),
((SELECT product_id FROM products WHERE name = 'Sandwich'), 40, CURRENT_TIMESTAMP);

-- Move the sample order data insertion to after ALL other data insertions
-- (after users, canteens, and products are inserted)
-- At the very end of the file, add:

-- Insert sample orders
INSERT INTO `orders` (`user_id`, `canteen_id`, `total_amount`, `status`, `payment_status`) 
SELECT 
    u.user_id,
    c.canteen_id,
    150.00,
    'completed',
    'paid'
FROM 
    users u,
    canteens c
WHERE 
    u.username = 'admin'
    AND c.name = 'Main Canteen'
LIMIT 1;

-- Insert sample order items
INSERT INTO `order_items` (`order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`)
SELECT 
    (SELECT MAX(order_id) FROM orders),
    p.product_id,
    2,
    75.00,
    150.00
FROM 
    products p
WHERE 
    p.name = 'Chicken Adobo'
LIMIT 1;
