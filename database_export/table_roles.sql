-- Table: roles
-- Generated: 2025-08-26 15:03:17

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

-- Data for table `roles`
INSERT INTO `roles` VALUES ('1', 'admin', 'System Administrator with full access', '2025-05-11 23:20:42', '2025-05-11 23:20:42');
INSERT INTO `roles` VALUES ('2', 'doctor', 'Medical doctor with patient consultation access', '2025-05-11 23:20:42', '2025-05-11 23:20:42');
INSERT INTO `roles` VALUES ('3', 'nurse', 'Nursing staff with limited clinical access', '2025-05-11 23:20:42', '2025-05-11 23:20:42');
INSERT INTO `roles` VALUES ('4', 'lab_technician', 'Laboratory staff with test management access', '2025-05-11 23:20:42', '2025-05-11 23:20:42');
INSERT INTO `roles` VALUES ('5', 'pharmacist', 'Pharmacy staff with medication management access', '2025-05-11 23:20:42', '2025-05-11 23:20:42');
INSERT INTO `roles` VALUES ('6', 'patient', 'Patient with limited personal data access', '2025-05-11 23:20:42', '2025-05-11 23:20:42');
