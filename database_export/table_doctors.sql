-- Table: doctors
-- Generated: 2025-08-26 15:03:18

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

