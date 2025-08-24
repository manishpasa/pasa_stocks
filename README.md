-- sql for the code 
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 18, 2025 at 05:00 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `pasa_stocks`
--
CREATE DATABASE IF NOT EXISTS `pasa_stocks` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `pasa_stocks`;

-- --------------------------------------------------------

--
-- Table structure for table `company`
--
CREATE TABLE `company` (
  `company_id` int(11) NOT NULL AUTO_INCREMENT,
  `company_code` varchar(50) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `total_employees` int(11) DEFAULT NULL,
  `has_live` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`company_id`),
  UNIQUE KEY `company_code` (`company_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--
CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `cust_name` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  PRIMARY KEY (`customer_id`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--
CREATE TABLE `employee` (
  `emp_id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `DOB` date DEFAULT NULL,
  `company_code` varchar(50) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `join_date` date DEFAULT NULL,
  `issolo` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`emp_id`),
  UNIQUE KEY `email` (`email`),
  KEY `company_code` (`company_code`),
  CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`company_code`) REFERENCES `company` (`company_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--
CREATE TABLE `bills` (
  `bill_id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `bill_date` date DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `islive` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`bill_id`),
  KEY `emp_id` (`emp_id`),
  KEY `customer_id` (`customer_id`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `bills_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employee` (`emp_id`),
  CONSTRAINT `bills_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`),
  CONSTRAINT `bills_ibfk_3` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_otp`
--
CREATE TABLE `email_otp` (
  `otp_code` varchar(10) NOT NULL,
  `emp_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`otp_code`),
  KEY `emp_id` (`emp_id`),
  CONSTRAINT `email_otp_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employee` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--
CREATE TABLE `inventory` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `Quantity_sold` int(11) DEFAULT 0,
  PRIMARY KEY (`item_id`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `live_inventory`
--
CREATE TABLE `live_inventory` (
  `live_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) NOT NULL,
  `company_id` int(11) NOT NULL,
  `cost_per_unit` decimal(10,2) NOT NULL,
  `sell_price` decimal(10,2) NOT NULL,
  `total_bought` int(11) DEFAULT 0,
  `total_sold` int(11) DEFAULT 0,
  `total_cost` decimal(12,2) DEFAULT 0.00,
  `category` varchar(50) DEFAULT NULL,
  `added_date` date DEFAULT curdate(),
  PRIMARY KEY (`live_id`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `live_inventory_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `live_inventory_sales`
--
CREATE TABLE `live_inventory_sales` (
  `sale_id` int(11) NOT NULL AUTO_INCREMENT,
  `live_id` int(11) NOT NULL,
  `quantity_sold` int(11) NOT NULL,
  `sold_price_per_unit` decimal(10,2) NOT NULL,
  `cost_price_per_unit` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(12,2) GENERATED ALWAYS AS (`quantity_sold` * `sold_price_per_unit`) STORED,
  `sale_date` datetime DEFAULT current_timestamp(),
  `emp_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `bill_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`sale_id`),
  KEY `live_id` (`live_id`),
  KEY `emp_id` (`emp_id`),
  KEY `company_id` (`company_id`),
  KEY `bill_id` (`bill_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `live_inventory_sales_ibfk_1` FOREIGN KEY (`live_id`) REFERENCES `live_inventory` (`live_id`),
  CONSTRAINT `live_inventory_sales_ibfk_2` FOREIGN KEY (`emp_id`) REFERENCES `employee` (`emp_id`),
  CONSTRAINT `live_inventory_sales_ibfk_3` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`),
  CONSTRAINT `live_inventory_sales_ibfk_4` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`bill_id`),
  CONSTRAINT `live_inventory_sales_ibfk_5` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_list`
--
CREATE TABLE `purchase_list` (
  `item_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `purchase_date` date NOT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_id`, `purchase_date`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `purchase_list_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`item_id`),
  CONSTRAINT `purchase_list_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `returned_list`
--
CREATE TABLE `returned_list` (
  `return_id` int(11) NOT NULL AUTO_INCREMENT,
  `bill_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `return_date` datetime DEFAULT current_timestamp(),
  `emp_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`return_id`),
  KEY `bill_id` (`bill_id`),
  KEY `item_id` (`item_id`),
  KEY `emp_id` (`emp_id`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `returned_list_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`bill_id`),
  CONSTRAINT `returned_list_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`item_id`),
  CONSTRAINT `returned_list_ibfk_3` FOREIGN KEY (`emp_id`) REFERENCES `employee` (`emp_id`),
  CONSTRAINT `returned_list_ibfk_4` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sold_list`
--
CREATE TABLE `sold_list` (
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `bill_id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `emp_id` int(11) DEFAULT NULL,
  `item_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`bill_id`, `item_id`),
  KEY `company_id` (`company_id`),
  KEY `emp_id` (`emp_id`),
  KEY `item_id` (`item_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `sold_list_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`bill_id`),
  CONSTRAINT `sold_list_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`),
  CONSTRAINT `sold_list_ibfk_3` FOREIGN KEY (`emp_id`) REFERENCES `employee` (`emp_id`),
  CONSTRAINT `sold_list_ibfk_4` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`item_id`),
  CONSTRAINT `sold_list_ibfk_5` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_returns`
--
CREATE TABLE `supplier_returns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `return_date` datetime DEFAULT current_timestamp(),
  `emp_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `emp_id` (`emp_id`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `supplier_returns_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`item_id`),
  CONSTRAINT `supplier_returns_ibfk_2` FOREIGN KEY (`emp_id`) REFERENCES `employee` (`emp_id`),
  CONSTRAINT `supplier_returns_ibfk_3` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;