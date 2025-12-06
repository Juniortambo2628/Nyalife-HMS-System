-- Nyalife HMS Database Export
-- Generated: 2025-08-26 15:03:16
-- Database: nyalifew_hms_prod

SET FOREIGN_KEY_CHECKS = 0;

-- Table structure for table `activity_logs`
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`activity_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `appointments`
DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`appointment_id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `audit_logs`
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `consultations`
DROP TABLE IF EXISTS `consultations`;
CREATE TABLE `consultations` (
  `consultation_id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) DEFAULT NULL,
  `is_walk_in` tinyint(1) NOT NULL DEFAULT 0,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`consultation_id`),
  KEY `appointment_id` (`appointment_id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `consultations_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE SET NULL,
  CONSTRAINT `consultations_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  CONSTRAINT `consultations_ibfk_3` FOREIGN KEY (`doctor_id`) REFERENCES `staff` (`staff_id`),
  CONSTRAINT `consultations_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `departments`
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL AUTO_INCREMENT,
  `department_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `code` varchar(10) DEFAULT NULL,
  `type` enum('clinical','administrative','support') DEFAULT 'clinical',
  `head_name` varchar(100) DEFAULT NULL,
  `head_position` varchar(100) DEFAULT NULL,
  `head_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`department_id`),
  UNIQUE KEY `department_name` (`department_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `doctors`
DROP TABLE IF EXISTS `doctors`;
CREATE TABLE `doctors` (
  `doctor_id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `specialization_id` int(11) NOT NULL,
  `license_number` varchar(50) NOT NULL,
  `consultation_fee` decimal(10,2) DEFAULT 0.00,
  `availability_schedule` text DEFAULT NULL,
  `biography` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`doctor_id`),
  KEY `staff_id` (`staff_id`),
  KEY `department_id` (`department_id`),
  KEY `specialization_id` (`specialization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `follow_ups`
DROP TABLE IF EXISTS `follow_ups`;
CREATE TABLE `follow_ups` (
  `follow_up_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `consultation_id` int(11) NOT NULL,
  `follow_up_date` date NOT NULL,
  `follow_up_type` varchar(50) DEFAULT NULL,
  `reason` text NOT NULL,
  `status` enum('scheduled','completed','cancelled','no_show') NOT NULL DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`follow_up_id`),
  KEY `patient_id` (`patient_id`),
  KEY `consultation_id` (`consultation_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `follow_ups_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  CONSTRAINT `follow_ups_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`),
  CONSTRAINT `follow_ups_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `invoice_items`
DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE `invoice_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`item_id`),
  KEY `invoice_id` (`invoice_id`),
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `invoices`
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `doctor_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`invoice_id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `patient_id` (`patient_id`),
  KEY `consultation_id` (`consultation_id`),
  KEY `created_by` (`created_by`),
  KEY `fk_invoice_doctor` (`doctor_id`),
  CONSTRAINT `fk_invoice_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `lab_parameters`
DROP TABLE IF EXISTS `lab_parameters`;
CREATE TABLE `lab_parameters` (
  `parameter_id` int(11) NOT NULL AUTO_INCREMENT,
  `test_type_id` int(11) NOT NULL,
  `parameter_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `normal_range` varchar(100) DEFAULT NULL,
  `units` varchar(20) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`parameter_id`),
  KEY `test_type_id` (`test_type_id`),
  CONSTRAINT `lab_parameters_ibfk_1` FOREIGN KEY (`test_type_id`) REFERENCES `lab_test_types` (`test_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `lab_requests`
DROP TABLE IF EXISTS `lab_requests`;
CREATE TABLE `lab_requests` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`request_id`),
  KEY `patient_id` (`patient_id`),
  KEY `consultation_id` (`consultation_id`),
  KEY `requested_by` (`requested_by`),
  KEY `assigned_to` (`assigned_to`),
  KEY `sample_collected_by` (`sample_collected_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `lab_results`
DROP TABLE IF EXISTS `lab_results`;
CREATE TABLE `lab_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sample_id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL,
  `result_value` varchar(255) NOT NULL,
  `is_abnormal` tinyint(1) NOT NULL DEFAULT 0,
  `recorded_by` int(11) NOT NULL,
  `recorded_at` datetime NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_sample_parameter` (`sample_id`,`parameter_id`),
  KEY `sample_id` (`sample_id`),
  KEY `parameter_id` (`parameter_id`),
  KEY `recorded_by` (`recorded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `lab_samples`
DROP TABLE IF EXISTS `lab_samples`;
CREATE TABLE `lab_samples` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sample_id` varchar(20) NOT NULL COMMENT 'Unique sample identifier',
  `patient_id` int(11) NOT NULL,
  `test_type_id` int(11) NOT NULL,
  `sample_type` varchar(50) NOT NULL COMMENT 'Type of sample (blood, urine, etc.)',
  `collected_date` date NOT NULL,
  `collected_by` int(11) NOT NULL COMMENT 'User ID of collector',
  `collected_at` datetime NOT NULL,
  `status` enum('registered','in_progress','pending_results','completed','cancelled') NOT NULL DEFAULT 'registered',
  `completed_by` int(11) DEFAULT NULL COMMENT 'User ID who completed the test',
  `completed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `urgent` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sample_id_UNIQUE` (`sample_id`),
  KEY `patient_id` (`patient_id`),
  KEY `test_type_id` (`test_type_id`),
  KEY `collected_by` (`collected_by`),
  KEY `completed_by` (`completed_by`),
  KEY `status_idx` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `lab_test_items`
DROP TABLE IF EXISTS `lab_test_items`;
CREATE TABLE `lab_test_items` (
  `test_item_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`test_item_id`),
  KEY `request_id` (`request_id`),
  KEY `test_type_id` (`test_type_id`),
  KEY `performed_by` (`performed_by`),
  KEY `verified_by` (`verified_by`),
  KEY `parameter_id` (`parameter_id`),
  CONSTRAINT `lab_test_items_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `lab_test_requests` (`request_id`) ON DELETE CASCADE,
  CONSTRAINT `lab_test_items_ibfk_2` FOREIGN KEY (`test_type_id`) REFERENCES `lab_test_types` (`test_type_id`),
  CONSTRAINT `lab_test_items_ibfk_3` FOREIGN KEY (`performed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `lab_test_items_ibfk_4` FOREIGN KEY (`verified_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `lab_test_items_ibfk_5` FOREIGN KEY (`parameter_id`) REFERENCES `lab_parameters` (`parameter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `lab_test_parameters`
DROP TABLE IF EXISTS `lab_test_parameters`;
CREATE TABLE `lab_test_parameters` (
  `parameter_id` int(11) NOT NULL AUTO_INCREMENT,
  `test_id` int(11) NOT NULL,
  `parameter_name` varchar(100) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `reference_range` varchar(100) DEFAULT NULL,
  `sequence` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`parameter_id`),
  KEY `fk_test_parameters_test_idx` (`test_id`),
  KEY `fk_test_parameters_created_by_idx` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `lab_test_requests`
DROP TABLE IF EXISTS `lab_test_requests`;
CREATE TABLE `lab_test_requests` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`request_id`),
  KEY `patient_id` (`patient_id`),
  KEY `consultation_id` (`consultation_id`),
  KEY `requested_by` (`requested_by`),
  KEY `assigned_to` (`assigned_to`),
  KEY `sample_collected_by` (`sample_collected_by`),
  CONSTRAINT `lab_test_requests_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  CONSTRAINT `lab_test_requests_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`) ON DELETE SET NULL,
  CONSTRAINT `lab_test_requests_ibfk_3` FOREIGN KEY (`requested_by`) REFERENCES `users` (`user_id`),
  CONSTRAINT `lab_test_requests_ibfk_4` FOREIGN KEY (`assigned_to`) REFERENCES `staff` (`staff_id`),
  CONSTRAINT `lab_test_requests_ibfk_5` FOREIGN KEY (`sample_collected_by`) REFERENCES `staff` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `lab_test_types`
DROP TABLE IF EXISTS `lab_test_types`;
CREATE TABLE `lab_test_types` (
  `test_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `test_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `normal_range` text DEFAULT NULL,
  `units` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`test_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `medical_history`
DROP TABLE IF EXISTS `medical_history`;
CREATE TABLE `medical_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`history_id`),
  KEY `patient_id` (`patient_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `medical_history_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  CONSTRAINT `medical_history_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `medication_batches`
DROP TABLE IF EXISTS `medication_batches`;
CREATE TABLE `medication_batches` (
  `batch_id` int(11) NOT NULL AUTO_INCREMENT,
  `medication_id` int(11) NOT NULL,
  `batch_number` varchar(50) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_cost` decimal(10,2) DEFAULT 0.00,
  `selling_price` decimal(10,2) DEFAULT 0.00,
  `manufacturing_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`batch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `medication_categories`
DROP TABLE IF EXISTS `medication_categories`;
CREATE TABLE `medication_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `medications`
DROP TABLE IF EXISTS `medications`;
CREATE TABLE `medications` (
  `medication_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`medication_id`),
  UNIQUE KEY `medication_name` (`medication_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `messages`
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `priority` enum('low','normal','high') DEFAULT 'normal',
  `is_read` tinyint(1) DEFAULT 0,
  `is_archived` tinyint(1) DEFAULT 0,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `archived_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`message_id`),
  KEY `idx_sender` (`sender_id`),
  KEY `idx_recipient` (`recipient_id`),
  KEY `idx_recipient_unread` (`recipient_id`,`is_read`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_priority` (`priority`),
  KEY `idx_archived` (`is_archived`),
  KEY `idx_deleted` (`is_deleted`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `notifications`
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `obstetric_history`
DROP TABLE IF EXISTS `obstetric_history`;
CREATE TABLE `obstetric_history` (
  `obstetric_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`obstetric_id`),
  KEY `patient_id` (`patient_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `obstetric_history_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  CONSTRAINT `obstetric_history_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `password_reset_tokens`
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  KEY `idx_password_reset_tokens_user_expires` (`user_id`,`expires_at`),
  CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `patients`
DROP TABLE IF EXISTS `patients`;
CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`patient_id`),
  UNIQUE KEY `patient_number` (`patient_number`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `payment_transactions`
DROP TABLE IF EXISTS `payment_transactions`;
CREATE TABLE `payment_transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `transaction_date` datetime DEFAULT NULL,
  PRIMARY KEY (`transaction_id`),
  KEY `invoice_id` (`invoice_id`),
  CONSTRAINT `payment_transactions_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `payments`
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','credit_card','debit_card','bank_transfer','check','insurance','mobile_payment','other') NOT NULL,
  `payment_date` datetime NOT NULL,
  `transaction_reference` varchar(100) DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'completed',
  `notes` text DEFAULT NULL,
  `received_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('pending','completed','failed') DEFAULT 'completed',
  PRIMARY KEY (`payment_id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `received_by` (`received_by`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`received_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `pregnancy_details`
DROP TABLE IF EXISTS `pregnancy_details`;
CREATE TABLE `pregnancy_details` (
  `pregnancy_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`pregnancy_id`),
  KEY `patient_id` (`patient_id`),
  KEY `obstetric_id` (`obstetric_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `pregnancy_details_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  CONSTRAINT `pregnancy_details_ibfk_2` FOREIGN KEY (`obstetric_id`) REFERENCES `obstetric_history` (`obstetric_id`) ON DELETE CASCADE,
  CONSTRAINT `pregnancy_details_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `prescription_items`
DROP TABLE IF EXISTS `prescription_items`;
CREATE TABLE `prescription_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`item_id`),
  KEY `prescription_id` (`prescription_id`),
  KEY `medication_id` (`medication_id`),
  KEY `dispensed_by` (`dispensed_by`),
  CONSTRAINT `prescription_items_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`prescription_id`) ON DELETE CASCADE,
  CONSTRAINT `prescription_items_ibfk_2` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`medication_id`),
  CONSTRAINT `prescription_items_ibfk_3` FOREIGN KEY (`dispensed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `prescriptions`
DROP TABLE IF EXISTS `prescriptions`;
CREATE TABLE `prescriptions` (
  `prescription_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `prescribed_by` int(11) NOT NULL,
  `prescription_date` datetime NOT NULL,
  `status` enum('pending','dispensed','partially_dispensed','cancelled') NOT NULL DEFAULT 'pending',
  `dispensed_by` int(11) DEFAULT NULL,
  `dispensed_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`prescription_id`),
  KEY `patient_id` (`patient_id`),
  KEY `consultation_id` (`consultation_id`),
  KEY `prescribed_by` (`prescribed_by`),
  CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`) ON DELETE SET NULL,
  CONSTRAINT `prescriptions_ibfk_3` FOREIGN KEY (`prescribed_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `referrals`
DROP TABLE IF EXISTS `referrals`;
CREATE TABLE `referrals` (
  `referral_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`referral_id`),
  KEY `patient_id` (`patient_id`),
  KEY `consultation_id` (`consultation_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  CONSTRAINT `referrals_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`),
  CONSTRAINT `referrals_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `remember_tokens`
DROP TABLE IF EXISTS `remember_tokens`;
CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `roles`
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `services`
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `service_name` varchar(100) NOT NULL,
  `service_category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `cost` decimal(10,2) NOT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'in minutes',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `settings`
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `specializations`
DROP TABLE IF EXISTS `specializations`;
CREATE TABLE `specializations` (
  `specialization_id` int(11) NOT NULL AUTO_INCREMENT,
  `specialization_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`specialization_id`),
  UNIQUE KEY `specialization_name` (`specialization_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `staff`
DROP TABLE IF EXISTS `staff`;
CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `department_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`staff_id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `system_notifications`
DROP TABLE IF EXISTS `system_notifications`;
CREATE TABLE `system_notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `system_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `user_tokens`
DROP TABLE IF EXISTS `user_tokens`;
CREATE TABLE `user_tokens` (
  `token_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`token_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `vital_signs`
DROP TABLE IF EXISTS `vital_signs`;
CREATE TABLE `vital_signs` (
  `vital_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`vital_id`),
  KEY `patient_id` (`patient_id`),
  KEY `consultation_id` (`consultation_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `vital_signs_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  CONSTRAINT `vital_signs_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`) ON DELETE SET NULL,
  CONSTRAINT `vital_signs_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;
