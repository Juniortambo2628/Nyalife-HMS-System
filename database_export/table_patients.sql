-- Table: patients
-- Generated: 2025-08-26 15:03:19

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

-- Data for table `patients`
INSERT INTO `patients` VALUES ('1', '6', 'NYA-PAT-001', 'O+', '165.00', '60.50', NULL, NULL, '+2547600000', 'John Jones', 'Husband', NULL, 'married', NULL, NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `patients` VALUES ('2', '7', 'NYA-PAT-002', 'A+', '183.00', '93.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-12 02:31:11', '2025-05-12 02:31:11');
INSERT INTO `patients` VALUES ('3', '14', 'PT2025000001', NULL, NULL, NULL, 'None', NULL, '0705883227', 'Test', NULL, NULL, NULL, NULL, NULL, '2025-08-22 08:28:58', '2025-08-22 08:28:58');
