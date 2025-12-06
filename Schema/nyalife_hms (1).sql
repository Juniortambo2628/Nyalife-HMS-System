-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 07, 2025 at 04:48 AM
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
-- Database: `nyalife_hms`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('scheduled','completed','cancelled','no_show') NOT NULL DEFAULT 'scheduled',
  `appointment_type` enum('new_visit','follow_up','emergency','routine_checkup','consultation') NOT NULL,
  `reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `action`, `entity_type`, `entity_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 7, 'register', 'user', 7, 'User registered', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-11 23:31:11'),
(2, 7, 'logout', 'user', 7, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 00:01:12'),
(3, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 00:04:52'),
(4, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 00:06:21'),
(5, 5, 'logout', 'user', 5, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 00:07:51'),
(6, 3, 'logout', 'user', 3, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 00:09:01'),
(7, 4, 'logout', 'user', 4, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 00:10:03'),
(8, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 02:14:45'),
(9, 3, 'logout', 'user', 3, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 02:15:36'),
(10, 4, 'logout', 'user', 4, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 02:16:16'),
(11, 5, 'logout', 'user', 5, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 02:17:02'),
(12, 6, 'logout', 'user', 6, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 02:33:05'),
(13, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 04:51:36'),
(14, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 06:36:20'),
(15, 6, 'login', 'user', 6, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 06:51:35'),
(16, 6, 'logout', 'user', 6, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 06:52:03'),
(17, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 06:52:33'),
(18, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 06:54:37'),
(19, 6, 'login', 'user', 6, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 07:35:14'),
(20, 6, 'logout', 'user', 6, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 07:36:03'),
(21, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 07:36:33'),
(22, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 07:36:55'),
(23, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 07:57:06'),
(24, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 08:06:00'),
(25, 5, 'login', 'user', 5, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 08:06:27'),
(26, 5, 'logout', 'user', 5, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 08:06:42'),
(27, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 08:32:49'),
(28, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 08:33:30'),
(29, 6, 'login', 'user', 6, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 08:34:58'),
(30, 6, 'logout', 'user', 6, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 08:35:38'),
(31, 3, 'login', 'user', 3, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 08:35:53'),
(32, 3, 'logout', 'user', 3, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 08:36:20'),
(33, 5, 'login', 'user', 5, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 08:38:15'),
(34, 5, 'logout', 'user', 5, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 08:38:43'),
(35, 1, 'login', 'user', 1, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 08:39:40'),
(36, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 08:46:06'),
(37, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 09:04:56'),
(38, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 09:10:45'),
(39, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 09:10:57'),
(40, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 09:11:08'),
(41, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 09:25:53'),
(42, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 09:26:15'),
(43, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 10:31:10'),
(44, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 10:46:51'),
(45, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 10:47:40'),
(46, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 11:27:53'),
(47, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 11:28:04'),
(48, 2, 'login', 'user', 2, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-14 12:36:36'),
(49, 2, 'logout', 'user', 2, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-14 12:37:11'),
(50, 4, 'login', 'user', 4, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-14 12:37:24'),
(51, 4, 'login', 'user', 4, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 13:36:21'),
(52, 4, 'login', 'user', 4, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 14:22:21'),
(53, 4, 'logout', 'user', 4, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 14:57:56'),
(54, 4, 'login', 'user', 4, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 14:58:28'),
(55, 4, 'login', 'user', 4, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 15:08:56'),
(56, 1, 'logout', 'user', 1, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-18 11:34:20'),
(57, 4, 'login', 'user', 4, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-18 11:34:36');

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

CREATE TABLE `consultations` (
  `consultation_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `consultation_date` datetime NOT NULL,
  `chief_complaint` text DEFAULT NULL,
  `history_present_illness` text DEFAULT NULL,
  `past_medical_history` text DEFAULT NULL,
  `family_history` text DEFAULT NULL,
  `social_history` text DEFAULT NULL,
  `obstetric_history` text DEFAULT NULL,
  `gynecological_history` text DEFAULT NULL,
  `menstrual_history` text DEFAULT NULL,
  `contraceptive_history` text DEFAULT NULL,
  `sexual_history` text DEFAULT NULL,
  `review_of_systems` text DEFAULT NULL,
  `physical_examination` text DEFAULT NULL,
  `vital_signs` text DEFAULT NULL,
  `diagnosis` text NOT NULL,
  `treatment_plan` text DEFAULT NULL,
  `follow_up_instructions` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `consultation_status` enum('open','closed','pending','referred') NOT NULL DEFAULT 'open',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `follow_ups`
--

CREATE TABLE `follow_ups` (
  `follow_up_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `consultation_id` int(11) NOT NULL,
  `follow_up_date` date NOT NULL,
  `follow_up_type` varchar(50) DEFAULT NULL,
  `reason` text NOT NULL,
  `status` enum('scheduled','completed','cancelled','no_show') NOT NULL DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `invoice_number` varchar(20) NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `tax` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','paid','partially_paid','cancelled','overdue') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `insurance_claim_id` varchar(50) DEFAULT NULL,
  `insurance_coverage` decimal(10,2) DEFAULT 0.00,
  `patient_responsibility` decimal(10,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `item_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `item_type` enum('service','medication','lab_test','procedure','other') NOT NULL,
  `item_id_ref` int(11) DEFAULT NULL COMMENT 'Reference to the specific type table',
  `description` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `tax` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_parameters`
--

CREATE TABLE `lab_parameters` (
  `parameter_id` int(11) NOT NULL,
  `test_type_id` int(11) NOT NULL,
  `parameter_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `normal_range` varchar(100) DEFAULT NULL,
  `units` varchar(20) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_test_items`
--

CREATE TABLE `lab_test_items` (
  `test_item_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `test_type_id` int(11) NOT NULL,
  `parameter_id` int(11) DEFAULT NULL,
  `status` enum('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
  `result` text DEFAULT NULL,
  `result_value` varchar(100) DEFAULT NULL,
  `result_interpretation` varchar(50) DEFAULT NULL,
  `normal_range` varchar(100) DEFAULT NULL,
  `units` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `report_file` varchar(255) DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `performed_at` datetime DEFAULT NULL,
  `sample_collected_at` datetime DEFAULT NULL,
  `sample_received_at` datetime DEFAULT NULL,
  `sample_reported_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_test_requests`
--

CREATE TABLE `lab_test_requests` (
  `request_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `requested_by` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `sample_collected_by` int(11) DEFAULT NULL,
  `request_date` datetime NOT NULL,
  `completed_at` datetime DEFAULT NULL,
  `status` enum('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
  `priority` enum('routine','urgent','stat') NOT NULL DEFAULT 'routine',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_test_types`
--

CREATE TABLE `lab_test_types` (
  `test_type_id` int(11) NOT NULL,
  `test_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `normal_range` text DEFAULT NULL,
  `units` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_test_types`
--

INSERT INTO `lab_test_types` (`test_type_id`, `test_name`, `description`, `category`, `price`, `normal_range`, `units`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Complete Blood Count', 'Full blood cell count analysis', 'Hematology', 1500.00, 'Varies by parameter', 'Various', 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(2, 'Blood Glucose', 'Blood sugar level test', 'Chemistry', 500.00, '70-99', 'mg/dL', 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(3, 'Pregnancy Test', 'hCG detection test', 'Reproductive', 800.00, 'Negative/Positive', NULL, 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(4, 'HIV Test', 'HIV antibody test', 'Serology', 1000.00, 'Negative/Positive', NULL, 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(5, 'Liver Function Test', 'Liver enzyme panel', 'Chemistry', 2000.00, 'Varies by parameter', 'Various', 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(6, 'Urinalysis', 'Urine analysis', 'Microbiology', 800.00, 'Varies by parameter', 'Various', 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(7, 'Lipid Profile', 'Cholesterol and lipid panel', 'Chemistry', 1800.00, 'Varies by parameter', 'Various', 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(8, 'High Vaginal Swab', 'Microscopy, Culture, and Sensitivity of high vaginal swab', 'Microbiology', 2500.00, NULL, NULL, 1, '2025-05-14 13:37:34', '2025-05-14 13:37:34'),
(9, 'Pap Smear Cytology', 'Cytology examination of cervical sample', 'Pathology', 3500.00, NULL, NULL, 1, '2025-05-14 13:37:34', '2025-05-14 13:37:34');

-- --------------------------------------------------------

--
-- Table structure for table `medical_history`
--

CREATE TABLE `medical_history` (
  `history_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `history_type` enum('surgery','illness','injury','allergy','immunization','medication','family','pregnancy','childbirth') NOT NULL,
  `description` text NOT NULL,
  `date_occurred` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_ongoing` tinyint(1) DEFAULT 0,
  `treatment` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medications`
--

CREATE TABLE `medications` (
  `medication_id` int(11) NOT NULL,
  `medication_name` varchar(100) NOT NULL,
  `generic_name` varchar(100) DEFAULT NULL,
  `medication_type` varchar(50) DEFAULT NULL,
  `form` varchar(50) DEFAULT NULL,
  `strength` varchar(50) DEFAULT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `manufacturer` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `side_effects` text DEFAULT NULL,
  `contraindications` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medications`
--

INSERT INTO `medications` (`medication_id`, `medication_name`, `generic_name`, `medication_type`, `form`, `strength`, `unit`, `manufacturer`, `price`, `stock_quantity`, `description`, `side_effects`, `contraindications`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Folic Acid', 'Folic Acid', 'Supplement', 'Tablet', '5', 'mg', NULL, 10.00, 1000, NULL, NULL, NULL, 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(2, 'Iron Supplement', 'Ferrous Sulfate', 'Supplement', 'Tablet', '65', 'mg', NULL, 15.00, 1000, NULL, NULL, NULL, 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(3, 'Paracetamol', 'Acetaminophen', 'Analgesic', 'Tablet', '500', 'mg', NULL, 5.00, 2000, NULL, NULL, NULL, 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(4, 'Amoxicillin', 'Amoxicillin', 'Antibiotic', 'Capsule', '500', 'mg', NULL, 25.00, 1000, NULL, NULL, NULL, 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(5, 'Metronidazole', 'Metronidazole', 'Antibiotic', 'Tablet', '400', 'mg', NULL, 20.00, 800, NULL, NULL, NULL, 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(6, 'Birth Control Pills', 'Ethinylestradiol/Levonorgestrel', 'Contraceptive', 'Tablet', '0.03/0.15', 'mg', NULL, 350.00, 500, NULL, NULL, NULL, 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(7, 'Mefenamic Acid', 'Mefenamic Acid', 'NSAID', 'Capsule', '500', 'mg', NULL, 15.00, 1000, NULL, NULL, NULL, 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `obstetric_history`
--

CREATE TABLE `obstetric_history` (
  `obstetric_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `gravida` int(11) DEFAULT NULL,
  `para` int(11) DEFAULT NULL,
  `abortions` int(11) DEFAULT NULL,
  `living_children` int(11) DEFAULT NULL,
  `last_menstrual_period` date DEFAULT NULL,
  `estimated_delivery_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `patient_number` varchar(20) NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `chronic_diseases` text DEFAULT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL,
  `emergency_name` varchar(100) DEFAULT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `marital_status` enum('single','married','divorced','widowed') DEFAULT NULL,
  `insurance_provider` varchar(100) DEFAULT NULL,
  `insurance_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `user_id`, `patient_number`, `blood_group`, `height`, `weight`, `allergies`, `chronic_diseases`, `emergency_contact`, `emergency_name`, `relationship`, `occupation`, `marital_status`, `insurance_provider`, `insurance_id`, `created_at`, `updated_at`) VALUES
(1, 6, 'NYA-PAT-001', 'O+', 165.00, 60.50, NULL, NULL, '+2547600000', 'John Jones', 'Husband', NULL, 'married', NULL, NULL, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(2, 7, 'NYA-PAT-002', 'A+', 183.00, 93.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-11 23:31:11', '2025-05-11 23:31:11');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','credit_card','debit_card','bank_transfer','check','insurance','mobile_payment','other') NOT NULL,
  `payment_date` datetime NOT NULL,
  `transaction_reference` varchar(100) DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'completed',
  `notes` text DEFAULT NULL,
  `received_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `transaction_id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `transaction_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pregnancy_details`
--

CREATE TABLE `pregnancy_details` (
  `pregnancy_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `obstetric_id` int(11) NOT NULL,
  `date_of_delivery` date DEFAULT NULL,
  `gestational_age` int(11) DEFAULT NULL,
  `delivery_type` enum('vaginal','cesarean','instrumental') DEFAULT NULL,
  `delivery_location` varchar(100) DEFAULT NULL,
  `complications` text DEFAULT NULL,
  `outcome` enum('live_birth','stillbirth','miscarriage','abortion') DEFAULT NULL,
  `birth_weight` decimal(5,2) DEFAULT NULL,
  `apgar_score` varchar(10) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `prescription_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `prescribed_by` int(11) NOT NULL,
  `prescription_date` datetime NOT NULL,
  `status` enum('pending','dispensed','partially_dispensed','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescription_items`
--

CREATE TABLE `prescription_items` (
  `item_id` int(11) NOT NULL,
  `prescription_id` int(11) NOT NULL,
  `medication_id` int(11) NOT NULL,
  `dosage` varchar(50) NOT NULL,
  `frequency` varchar(50) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL,
  `instructions` text DEFAULT NULL,
  `status` enum('pending','dispensed','cancelled') NOT NULL DEFAULT 'pending',
  `dispensed_by` int(11) DEFAULT NULL,
  `dispensed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `referral_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `consultation_id` int(11) NOT NULL,
  `referral_date` date NOT NULL,
  `referred_to_name` varchar(100) NOT NULL,
  `referred_to_specialty` varchar(100) NOT NULL,
  `referred_to_facility` varchar(100) DEFAULT NULL,
  `referred_to_contact` varchar(100) DEFAULT NULL,
  `reason` text NOT NULL,
  `priority` enum('routine','urgent','emergency') NOT NULL DEFAULT 'routine',
  `status` enum('pending','accepted','completed','cancelled','rejected') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'System Administrator with full access', '2025-05-11 20:20:42', '2025-05-11 20:20:42'),
(2, 'doctor', 'Medical doctor with patient consultation access', '2025-05-11 20:20:42', '2025-05-11 20:20:42'),
(3, 'nurse', 'Nursing staff with limited clinical access', '2025-05-11 20:20:42', '2025-05-11 20:20:42'),
(4, 'lab_technician', 'Laboratory staff with test management access', '2025-05-11 20:20:42', '2025-05-11 20:20:42'),
(5, 'pharmacist', 'Pharmacy staff with medication management access', '2025-05-11 20:20:42', '2025-05-11 20:20:42'),
(6, 'patient', 'Patient with limited personal data access', '2025-05-11 20:20:42', '2025-05-11 20:20:42');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `service_category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `cost` decimal(10,2) NOT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'in minutes',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `service_category`, `description`, `cost`, `duration`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Initial Consultation', 'Consultation', 'First-time patient consultation', 2500.00, 45, 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(2, 'Follow-up Consultation', 'Consultation', 'Follow-up appointment', 1500.00, 30, 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(3, 'Prenatal Check-up', 'Obstetrics', 'Regular pregnancy check-up', 1800.00, 30, 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(4, 'Ultrasound Scan', 'Diagnostics', 'Abdominal or transvaginal ultrasound', 3000.00, 30, 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(5, 'Pap Smear', 'Gynecology', 'Cervical cancer screening', 2000.00, 15, 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(6, 'IUD Insertion', 'Procedure', 'Intrauterine device insertion', 3500.00, 30, 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(7, 'STI Testing', 'Laboratory', 'Sexually transmitted infection testing', 2500.00, 15, 1, '2025-05-11 20:20:43', '2025-05-11 20:20:43');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_id`, `setting_key`, `setting_value`, `description`, `is_public`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'hospital_name', 'Nyalife HMS', 'The name of the hospital', 1, NULL, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(2, 'hospital_address', '123 Health Avenue, Nairobi', 'The physical address of the hospital', 1, NULL, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(3, 'hospital_phone', '+254700000000', 'The contact phone number', 1, NULL, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(4, 'hospital_email', 'info@nyalife.com', 'The contact email address', 1, NULL, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(5, 'appointment_interval', '30', 'Default appointment interval in minutes', 0, NULL, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(6, 'primary_color', '#058b7c', 'Primary theme color', 1, NULL, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(7, 'secondary_color', '#d41559', 'Secondary theme color', 1, NULL, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(8, 'currency', 'KES', 'Default currency', 1, NULL, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(9, 'tax_rate', '16', 'Default tax rate percentage', 0, NULL, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(10, 'logo_path', 'assets/img/logo.png', 'Path to the hospital logo', 1, NULL, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(11, 'working_hours_start', '08:00:00', 'Start of working hours', 1, NULL, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(12, 'working_hours_end', '17:00:00', 'End of working hours', 1, NULL, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(13, 'working_days', 'Monday,Tuesday,Wednesday,Thursday,Friday', 'Working days', 1, NULL, '2025-05-11 20:20:43', '2025-05-11 20:20:43');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `qualification` text DEFAULT NULL,
  `join_date` date NOT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL,
  `emergency_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `user_id`, `employee_id`, `department`, `position`, `specialization`, `qualification`, `join_date`, `emergency_contact`, `emergency_name`, `created_at`, `updated_at`) VALUES
(1, 2, 'NYA-DOC-001', 'Medical', 'Gynecologist', 'Obstetrics and Gynecology', 'MBCHB, MMED OBGYN', '2023-01-01', NULL, NULL, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(2, 3, 'NYA-NUR-001', 'Nursing', 'Registered Nurse', 'Maternal Care', 'BSN, RN', '2023-02-01', NULL, NULL, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(3, 4, 'NYA-LAB-001', 'Laboratory', 'Lab Technician', 'Medical Laboratory', 'BSc Medical Laboratory', '2023-02-15', NULL, NULL, '2025-05-11 20:20:43', '2025-05-11 20:20:43'),
(4, 5, 'NYA-PHM-001', 'Pharmacy', 'Pharmacist', 'Clinical Pharmacy', 'BPharm', '2023-03-01', NULL, NULL, '2025-05-11 20:20:43', '2025-05-11 20:20:43');

-- --------------------------------------------------------

--
-- Table structure for table `system_notifications`
--

CREATE TABLE `system_notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `role_id`, `username`, `password`, `email`, `phone`, `first_name`, `last_name`, `date_of_birth`, `gender`, `address`, `city`, `state`, `country`, `postal_code`, `is_active`, `last_login`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 1, 'admin', '$2y$10$1gotGmyBvpCDrrUTgU1dau2gPoElIRxKhwkrR3.t56ZxuseY8P53S', 'admin@nyalife.com', '+2547000000', 'System', 'Administrator', NULL, 'male', NULL, NULL, NULL, NULL, NULL, 1, '2025-06-07 04:09:56', NULL, '2025-05-11 20:20:43', '2025-06-07 01:09:56'),
(2, 2, 'doctor', '$2y$10$1gotGmyBvpCDrrUTgU1dau2gPoElIRxKhwkrR3.t56ZxuseY8P53S', 'doctor@nyalife.com', '+2547100000', 'Jane', 'Smith', NULL, 'female', NULL, NULL, NULL, NULL, NULL, 1, '2025-06-07 05:02:56', NULL, '2025-05-11 20:20:43', '2025-06-07 02:02:56'),
(3, 3, 'nurse', '$2y$10$1gotGmyBvpCDrrUTgU1dau2gPoElIRxKhwkrR3.t56ZxuseY8P53S', 'nurse@nyalife.com', '+2547200000', 'Mary', 'Johnson', NULL, 'female', NULL, NULL, NULL, NULL, NULL, 1, '2025-05-20 16:36:52', NULL, '2025-05-11 20:20:43', '2025-05-20 13:36:52'),
(4, 4, 'lab', '$2y$10$1gotGmyBvpCDrrUTgU1dau2gPoElIRxKhwkrR3.t56ZxuseY8P53S', 'lab@nyalife.com', '+2547300000', 'James', 'Brown', NULL, 'male', NULL, NULL, NULL, NULL, NULL, 1, '2025-05-20 16:42:39', NULL, '2025-05-11 20:20:43', '2025-05-20 13:42:39'),
(5, 5, 'pharm', '$2y$10$1gotGmyBvpCDrrUTgU1dau2gPoElIRxKhwkrR3.t56ZxuseY8P53S', 'pharm@nyalife.com', '+2547400000', 'Sarah', 'Williams', NULL, 'female', NULL, NULL, NULL, NULL, NULL, 1, '2025-05-20 16:55:59', NULL, '2025-05-11 20:20:43', '2025-05-20 13:55:59'),
(6, 6, 'patient', '$2y$10$1gotGmyBvpCDrrUTgU1dau2gPoElIRxKhwkrR3.t56ZxuseY8P53S', 'patient@example.com', '+2547500000', 'Elizabeth', 'Jones', '1990-05-15', 'female', '123 Main St', 'Nairobi', NULL, NULL, NULL, 1, '2025-05-22 09:52:36', NULL, '2025-05-11 20:20:43', '2025-05-22 06:52:36'),
(7, 6, 'kt12345', '$2y$10$MrrJg6qBG..4Wrk8OGebX.Gfgqy3nUrQ87joblx2FXOhGf8u3WLCe', 'test@test.com', '071247921', 'Kevin', 'Test', '1997-02-28', 'male', 'Langata', NULL, NULL, NULL, NULL, 1, '2025-05-12 02:52:55', NULL, '2025-05-11 23:31:11', '2025-05-11 23:52:55');

-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

CREATE TABLE `user_tokens` (
  `token_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_tokens`
--

INSERT INTO `user_tokens` (`token_id`, `user_id`, `token_hash`, `expires_at`, `created_at`) VALUES
(1, 6, '$2y$10$udxLW79LR2ZZBurKi5YGqu6qVeC5tpOPZflOyz599piGEnreJ7Kc.', '2025-06-11 17:09:51', '2025-05-12 14:09:51');

-- --------------------------------------------------------

--
-- Table structure for table `vital_signs`
--

CREATE TABLE `vital_signs` (
  `vital_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `blood_pressure` varchar(20) DEFAULT NULL,
  `heart_rate` int(11) DEFAULT NULL,
  `respiratory_rate` int(11) DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `bmi` decimal(5,2) DEFAULT NULL,
  `pain_level` int(11) DEFAULT NULL,
  `oxygen_saturation` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `measured_at` datetime NOT NULL,
  `recorded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `consultations`
--
ALTER TABLE `consultations`
  ADD PRIMARY KEY (`consultation_id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `follow_ups`
--
ALTER TABLE `follow_ups`
  ADD PRIMARY KEY (`follow_up_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `consultation_id` (`consultation_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `consultation_id` (`consultation_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `lab_parameters`
--
ALTER TABLE `lab_parameters`
  ADD PRIMARY KEY (`parameter_id`),
  ADD KEY `test_type_id` (`test_type_id`);

--
-- Indexes for table `lab_test_items`
--
ALTER TABLE `lab_test_items`
  ADD PRIMARY KEY (`test_item_id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `test_type_id` (`test_type_id`),
  ADD KEY `performed_by` (`performed_by`),
  ADD KEY `verified_by` (`verified_by`),
  ADD KEY `parameter_id` (`parameter_id`);

--
-- Indexes for table `lab_test_requests`
--
ALTER TABLE `lab_test_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `consultation_id` (`consultation_id`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `sample_collected_by` (`sample_collected_by`);

--
-- Indexes for table `lab_test_types`
--
ALTER TABLE `lab_test_types`
  ADD PRIMARY KEY (`test_type_id`);

--
-- Indexes for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`medication_id`),
  ADD UNIQUE KEY `medication_name` (`medication_name`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `obstetric_history`
--
ALTER TABLE `obstetric_history`
  ADD PRIMARY KEY (`obstetric_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `patient_number` (`patient_number`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `received_by` (`received_by`);

--
-- Indexes for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `pregnancy_details`
--
ALTER TABLE `pregnancy_details`
  ADD PRIMARY KEY (`pregnancy_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `obstetric_id` (`obstetric_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`prescription_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `consultation_id` (`consultation_id`),
  ADD KEY `prescribed_by` (`prescribed_by`);

--
-- Indexes for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `prescription_id` (`prescription_id`),
  ADD KEY `medication_id` (`medication_id`),
  ADD KEY `dispensed_by` (`dispensed_by`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`referral_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `consultation_id` (`consultation_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `system_notifications`
--
ALTER TABLE `system_notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD PRIMARY KEY (`token_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `vital_signs`
--
ALTER TABLE `vital_signs`
  ADD PRIMARY KEY (`vital_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `consultation_id` (`consultation_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `consultation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `follow_ups`
--
ALTER TABLE `follow_ups`
  MODIFY `follow_up_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lab_parameters`
--
ALTER TABLE `lab_parameters`
  MODIFY `parameter_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lab_test_items`
--
ALTER TABLE `lab_test_items`
  MODIFY `test_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lab_test_requests`
--
ALTER TABLE `lab_test_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lab_test_types`
--
ALTER TABLE `lab_test_types`
  MODIFY `test_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `medical_history`
--
ALTER TABLE `medical_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medications`
--
ALTER TABLE `medications`
  MODIFY `medication_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `obstetric_history`
--
ALTER TABLE `obstetric_history`
  MODIFY `obstetric_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pregnancy_details`
--
ALTER TABLE `pregnancy_details`
  MODIFY `pregnancy_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `prescription_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescription_items`
--
ALTER TABLE `prescription_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `referral_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `system_notifications`
--
ALTER TABLE `system_notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_tokens`
--
ALTER TABLE `user_tokens`
  MODIFY `token_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vital_signs`
--
ALTER TABLE `vital_signs`
  MODIFY `vital_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `consultations`
--
ALTER TABLE `consultations`
  ADD CONSTRAINT `consultations_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`),
  ADD CONSTRAINT `consultations_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `consultations_ibfk_3` FOREIGN KEY (`doctor_id`) REFERENCES `staff` (`staff_id`),
  ADD CONSTRAINT `consultations_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `follow_ups`
--
ALTER TABLE `follow_ups`
  ADD CONSTRAINT `follow_ups_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `follow_ups_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`),
  ADD CONSTRAINT `follow_ups_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoices_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE;

--
-- Constraints for table `lab_parameters`
--
ALTER TABLE `lab_parameters`
  ADD CONSTRAINT `lab_parameters_ibfk_1` FOREIGN KEY (`test_type_id`) REFERENCES `lab_test_types` (`test_type_id`);

--
-- Constraints for table `lab_test_items`
--
ALTER TABLE `lab_test_items`
  ADD CONSTRAINT `lab_test_items_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `lab_test_requests` (`request_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lab_test_items_ibfk_2` FOREIGN KEY (`test_type_id`) REFERENCES `lab_test_types` (`test_type_id`),
  ADD CONSTRAINT `lab_test_items_ibfk_3` FOREIGN KEY (`performed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lab_test_items_ibfk_4` FOREIGN KEY (`verified_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lab_test_items_ibfk_5` FOREIGN KEY (`parameter_id`) REFERENCES `lab_parameters` (`parameter_id`);

--
-- Constraints for table `lab_test_requests`
--
ALTER TABLE `lab_test_requests`
  ADD CONSTRAINT `lab_test_requests_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `lab_test_requests_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lab_test_requests_ibfk_3` FOREIGN KEY (`requested_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `lab_test_requests_ibfk_4` FOREIGN KEY (`assigned_to`) REFERENCES `staff` (`staff_id`),
  ADD CONSTRAINT `lab_test_requests_ibfk_5` FOREIGN KEY (`sample_collected_by`) REFERENCES `staff` (`staff_id`);

--
-- Constraints for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD CONSTRAINT `medical_history_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_history_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `obstetric_history`
--
ALTER TABLE `obstetric_history`
  ADD CONSTRAINT `obstetric_history_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `obstetric_history_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`received_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `payment_transactions_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`);

--
-- Constraints for table `pregnancy_details`
--
ALTER TABLE `pregnancy_details`
  ADD CONSTRAINT `pregnancy_details_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pregnancy_details_ibfk_2` FOREIGN KEY (`obstetric_id`) REFERENCES `obstetric_history` (`obstetric_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pregnancy_details_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `prescriptions_ibfk_3` FOREIGN KEY (`prescribed_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD CONSTRAINT `prescription_items_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`prescription_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescription_items_ibfk_2` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`medication_id`),
  ADD CONSTRAINT `prescription_items_ibfk_3` FOREIGN KEY (`dispensed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `referrals_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`),
  ADD CONSTRAINT `referrals_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `system_notifications`
--
ALTER TABLE `system_notifications`
  ADD CONSTRAINT `system_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);

--
-- Constraints for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `vital_signs`
--
ALTER TABLE `vital_signs`
  ADD CONSTRAINT `vital_signs_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `vital_signs_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `vital_signs_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
