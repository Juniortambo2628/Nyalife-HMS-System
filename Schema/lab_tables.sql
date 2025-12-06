-- Create or update the lab_test_types table
CREATE TABLE IF NOT EXISTS `lab_test_types` (
  `test_type_id` INT(11) NOT NULL AUTO_INCREMENT,
  `test_name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `category` VARCHAR(50) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `normal_range` VARCHAR(100) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `instructions_file` VARCHAR(255) NULL,
  `created_by` INT(11) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`test_type_id`),
  KEY `fk_test_types_created_by_idx` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create or update the lab_test_requests table
CREATE TABLE IF NOT EXISTS `lab_test_requests` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `requested_by` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `test_id` int(11) NOT NULL,
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
  KEY `test_id` (`test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create or update the lab_samples table
CREATE TABLE IF NOT EXISTS `lab_samples` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sample_id` varchar(20) NOT NULL COMMENT 'Unique sample identifier',
  `patient_id` int(11) NOT NULL,
  `test_type_id` int(11) NOT NULL,
  `sample_type` varchar(50) NOT NULL COMMENT 'Type of sample (blood, urine, etc.)',
  `collected_date` date NOT NULL,
  `collected_by` int(11) NOT NULL COMMENT 'User ID of collector',
  `collected_at` datetime NOT NULL,
  `status` enum('registered','in_progress','pending_results','completed','cancelled') NOT NULL DEFAULT 'registered',
  `completed_by` int(11) NULL COMMENT 'User ID who completed the test',
  `completed_at` datetime NULL,
  `notes` text NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create or update the lab_results table
CREATE TABLE IF NOT EXISTS `lab_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sample_id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL,
  `result_value` varchar(255) NOT NULL,
  `is_abnormal` tinyint(1) NOT NULL DEFAULT 0,
  `recorded_by` int(11) NOT NULL,
  `recorded_at` datetime NOT NULL,
  `notes` text NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_sample_parameter` (`sample_id`, `parameter_id`),
  KEY `sample_id` (`sample_id`),
  KEY `parameter_id` (`parameter_id`),
  KEY `recorded_by` (`recorded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create the lab_test_parameters table if it doesn't exist
CREATE TABLE IF NOT EXISTS `lab_test_parameters` (
  `parameter_id` INT(11) NOT NULL AUTO_INCREMENT,
  `test_id` INT(11) NOT NULL,
  `parameter_name` VARCHAR(100) NOT NULL,
  `unit` VARCHAR(50) NULL,
  `reference_range` VARCHAR(100) NULL,
  `sequence` INT(11) NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` INT(11) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`parameter_id`),
  KEY `fk_test_parameters_test_idx` (`test_id`),
  KEY `fk_test_parameters_created_by_idx` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 