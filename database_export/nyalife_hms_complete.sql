-- Nyalife HMS Database Export
-- Generated: 2025-08-26 15:03:14
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

-- Data for table `appointments`
INSERT INTO `appointments` VALUES ('1', '1', '12', '2025-07-26', '11:30:00', '12:00:00', 'scheduled', 'consultation', 'reason', NULL, '12', '2025-07-26 03:31:31', '2025-07-26 03:31:31');
INSERT INTO `appointments` VALUES ('2', '2', '12', '2025-07-26', '12:30:00', '13:00:00', 'scheduled', 'follow_up', 'test 1', NULL, '12', '2025-07-26 03:50:01', '2025-07-26 03:50:01');

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

-- Data for table `audit_logs`
INSERT INTO `audit_logs` VALUES ('1', '7', 'register', 'user', '7', 'User registered', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 02:31:11');
INSERT INTO `audit_logs` VALUES ('2', '7', 'logout', 'user', '7', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 03:01:12');
INSERT INTO `audit_logs` VALUES ('3', '2', 'logout', 'user', '2', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 03:04:52');
INSERT INTO `audit_logs` VALUES ('4', '1', 'logout', 'user', '1', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 03:06:21');
INSERT INTO `audit_logs` VALUES ('5', '5', 'logout', 'user', '5', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 03:07:51');
INSERT INTO `audit_logs` VALUES ('6', '3', 'logout', 'user', '3', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 03:09:01');
INSERT INTO `audit_logs` VALUES ('7', '4', 'logout', 'user', '4', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 03:10:03');
INSERT INTO `audit_logs` VALUES ('8', '2', 'logout', 'user', '2', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 05:14:45');
INSERT INTO `audit_logs` VALUES ('9', '3', 'logout', 'user', '3', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 05:15:36');
INSERT INTO `audit_logs` VALUES ('10', '4', 'logout', 'user', '4', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 05:16:16');
INSERT INTO `audit_logs` VALUES ('11', '5', 'logout', 'user', '5', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 05:17:02');
INSERT INTO `audit_logs` VALUES ('12', '6', 'logout', 'user', '6', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 05:33:05');
INSERT INTO `audit_logs` VALUES ('13', '2', 'logout', 'user', '2', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 07:51:36');
INSERT INTO `audit_logs` VALUES ('14', '2', 'logout', 'user', '2', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-12 09:36:20');
INSERT INTO `audit_logs` VALUES ('15', '6', 'login', 'user', '6', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 09:51:35');
INSERT INTO `audit_logs` VALUES ('16', '6', 'logout', 'user', '6', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 09:52:03');
INSERT INTO `audit_logs` VALUES ('17', '2', 'login', 'user', '2', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 09:52:33');
INSERT INTO `audit_logs` VALUES ('18', '2', 'logout', 'user', '2', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 09:54:37');
INSERT INTO `audit_logs` VALUES ('19', '6', 'login', 'user', '6', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 10:35:14');
INSERT INTO `audit_logs` VALUES ('20', '6', 'logout', 'user', '6', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 10:36:03');
INSERT INTO `audit_logs` VALUES ('21', '2', 'login', 'user', '2', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 10:36:33');
INSERT INTO `audit_logs` VALUES ('22', '2', 'logout', 'user', '2', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 10:36:55');
INSERT INTO `audit_logs` VALUES ('23', '2', 'login', 'user', '2', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 10:57:06');
INSERT INTO `audit_logs` VALUES ('24', '2', 'logout', 'user', '2', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 11:06:00');
INSERT INTO `audit_logs` VALUES ('25', '5', 'login', 'user', '5', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 11:06:27');
INSERT INTO `audit_logs` VALUES ('26', '5', 'logout', 'user', '5', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 11:06:42');
INSERT INTO `audit_logs` VALUES ('27', '2', 'login', 'user', '2', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 11:32:49');
INSERT INTO `audit_logs` VALUES ('28', '2', 'logout', 'user', '2', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 11:33:30');
INSERT INTO `audit_logs` VALUES ('29', '6', 'login', 'user', '6', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 11:34:58');
INSERT INTO `audit_logs` VALUES ('30', '6', 'logout', 'user', '6', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 11:35:38');
INSERT INTO `audit_logs` VALUES ('31', '3', 'login', 'user', '3', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 11:35:53');
INSERT INTO `audit_logs` VALUES ('32', '3', 'logout', 'user', '3', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 11:36:20');
INSERT INTO `audit_logs` VALUES ('33', '5', 'login', 'user', '5', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 11:38:15');
INSERT INTO `audit_logs` VALUES ('34', '5', 'logout', 'user', '5', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 11:38:43');
INSERT INTO `audit_logs` VALUES ('35', '1', 'login', 'user', '1', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 11:39:40');
INSERT INTO `audit_logs` VALUES ('36', '1', 'logout', 'user', '1', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 11:46:06');
INSERT INTO `audit_logs` VALUES ('37', '2', 'login', 'user', '2', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 12:04:56');
INSERT INTO `audit_logs` VALUES ('38', '2', 'logout', 'user', '2', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 12:10:45');
INSERT INTO `audit_logs` VALUES ('39', '2', 'login', 'user', '2', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 12:10:57');
INSERT INTO `audit_logs` VALUES ('40', '2', 'logout', 'user', '2', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-13 12:11:08');
INSERT INTO `audit_logs` VALUES ('41', '2', 'login', 'user', '2', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 12:25:53');
INSERT INTO `audit_logs` VALUES ('42', '2', 'logout', 'user', '2', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-13 12:26:15');
INSERT INTO `audit_logs` VALUES ('43', '2', 'login', 'user', '2', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 13:31:10');
INSERT INTO `audit_logs` VALUES ('44', '2', 'logout', 'user', '2', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 13:46:51');
INSERT INTO `audit_logs` VALUES ('45', '2', 'login', 'user', '2', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 13:47:40');
INSERT INTO `audit_logs` VALUES ('46', '2', 'logout', 'user', '2', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 14:27:53');
INSERT INTO `audit_logs` VALUES ('47', '2', 'login', 'user', '2', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 14:28:04');
INSERT INTO `audit_logs` VALUES ('48', '2', 'login', 'user', '2', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-14 15:36:36');
INSERT INTO `audit_logs` VALUES ('49', '2', 'logout', 'user', '2', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-14 15:37:11');
INSERT INTO `audit_logs` VALUES ('50', '4', 'login', 'user', '4', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-14 15:37:24');
INSERT INTO `audit_logs` VALUES ('51', '4', 'login', 'user', '4', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 16:36:21');
INSERT INTO `audit_logs` VALUES ('52', '4', 'login', 'user', '4', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 17:22:21');
INSERT INTO `audit_logs` VALUES ('53', '4', 'logout', 'user', '4', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 17:57:56');
INSERT INTO `audit_logs` VALUES ('54', '4', 'login', 'user', '4', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 17:58:28');
INSERT INTO `audit_logs` VALUES ('55', '4', 'login', 'user', '4', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-14 18:08:56');
INSERT INTO `audit_logs` VALUES ('56', '1', 'logout', 'user', '1', 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-18 14:34:20');
INSERT INTO `audit_logs` VALUES ('57', '4', 'login', 'user', '4', 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-18 14:34:36');
INSERT INTO `audit_logs` VALUES ('58', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-13 00:48:30');
INSERT INTO `audit_logs` VALUES ('59', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-13 00:48:36');
INSERT INTO `audit_logs` VALUES ('60', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-13 00:55:45');
INSERT INTO `audit_logs` VALUES ('61', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-13 00:55:49');
INSERT INTO `audit_logs` VALUES ('62', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-13 00:56:12');
INSERT INTO `audit_logs` VALUES ('63', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-13 05:24:40');
INSERT INTO `audit_logs` VALUES ('64', NULL, '', '', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 08:31:04');
INSERT INTO `audit_logs` VALUES ('65', NULL, '', '', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 08:37:05');
INSERT INTO `audit_logs` VALUES ('66', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-13 08:43:41');
INSERT INTO `audit_logs` VALUES ('67', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 10:45:36');
INSERT INTO `audit_logs` VALUES ('68', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 12:28:46');
INSERT INTO `audit_logs` VALUES ('69', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 13:33:39');
INSERT INTO `audit_logs` VALUES ('70', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 13:34:12');
INSERT INTO `audit_logs` VALUES ('71', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 13:34:18');
INSERT INTO `audit_logs` VALUES ('72', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 14:56:27');
INSERT INTO `audit_logs` VALUES ('73', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 14:59:37');
INSERT INTO `audit_logs` VALUES ('74', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-13 16:21:21');
INSERT INTO `audit_logs` VALUES ('75', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-13 16:21:33');
INSERT INTO `audit_logs` VALUES ('76', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-13 16:21:38');
INSERT INTO `audit_logs` VALUES ('77', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-13 16:21:57');
INSERT INTO `audit_logs` VALUES ('78', NULL, '', '', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 17:32:57');
INSERT INTO `audit_logs` VALUES ('79', NULL, '', '', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 17:54:47');
INSERT INTO `audit_logs` VALUES ('80', NULL, '', '', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 17:56:42');
INSERT INTO `audit_logs` VALUES ('81', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 18:11:18');
INSERT INTO `audit_logs` VALUES ('82', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 18:12:30');
INSERT INTO `audit_logs` VALUES ('83', NULL, '', '', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 18:13:18');
INSERT INTO `audit_logs` VALUES ('84', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 18:32:18');
INSERT INTO `audit_logs` VALUES ('85', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 18:32:43');
INSERT INTO `audit_logs` VALUES ('86', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 18:33:07');
INSERT INTO `audit_logs` VALUES ('87', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-13 18:33:46');
INSERT INTO `audit_logs` VALUES ('88', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-13 19:03:12');
INSERT INTO `audit_logs` VALUES ('89', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-13 19:03:52');
INSERT INTO `audit_logs` VALUES ('90', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-13 19:05:40');
INSERT INTO `audit_logs` VALUES ('91', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-13 19:10:48');
INSERT INTO `audit_logs` VALUES ('92', NULL, '', '', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-13 19:14:55');
INSERT INTO `audit_logs` VALUES ('93', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0', '2025-06-13 19:27:44');
INSERT INTO `audit_logs` VALUES ('94', '3', 'view', 'appointments', NULL, '', NULL, NULL, '102.0.123.41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:139.0) Gecko/20100101 Firefox/139.0', '2025-06-17 12:29:09');
INSERT INTO `audit_logs` VALUES ('95', '3', 'view', 'appointments', NULL, '', NULL, NULL, '102.0.123.41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:139.0) Gecko/20100101 Firefox/139.0', '2025-06-17 12:31:31');
INSERT INTO `audit_logs` VALUES ('96', '3', 'view', 'appointments', NULL, '', NULL, NULL, '102.0.123.41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:139.0) Gecko/20100101 Firefox/139.0', '2025-06-17 12:35:36');
INSERT INTO `audit_logs` VALUES ('97', '3', 'view', 'appointments', NULL, '', NULL, NULL, '102.0.123.41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:139.0) Gecko/20100101 Firefox/139.0', '2025-06-17 12:37:46');
INSERT INTO `audit_logs` VALUES ('98', '3', 'view', 'appointments', NULL, '', NULL, NULL, '102.0.123.41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:139.0) Gecko/20100101 Firefox/139.0', '2025-06-17 12:39:49');
INSERT INTO `audit_logs` VALUES ('99', '3', 'view', 'appointments', NULL, '', NULL, NULL, '102.0.123.41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:139.0) Gecko/20100101 Firefox/139.0', '2025-06-17 12:40:07');
INSERT INTO `audit_logs` VALUES ('100', '3', 'view', 'appointments', NULL, '', NULL, NULL, '102.0.123.41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:139.0) Gecko/20100101 Firefox/139.0', '2025-06-17 12:40:25');
INSERT INTO `audit_logs` VALUES ('101', '3', 'view', 'appointments', NULL, '', NULL, NULL, '102.0.123.41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:139.0) Gecko/20100101 Firefox/139.0', '2025-06-17 12:40:57');
INSERT INTO `audit_logs` VALUES ('102', '8', 'view', 'appointments', NULL, '', NULL, NULL, '102.213.179.127', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-17 12:55:32');
INSERT INTO `audit_logs` VALUES ('103', '2', 'view', 'appointments', NULL, '', NULL, NULL, '41.90.172.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-17 12:59:38');
INSERT INTO `audit_logs` VALUES ('104', '8', 'view', 'appointments', NULL, '', NULL, NULL, '102.213.179.127', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-17 13:03:59');
INSERT INTO `audit_logs` VALUES ('105', '2', 'view', 'appointments', NULL, '', NULL, NULL, '41.90.172.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-17 13:04:34');
INSERT INTO `audit_logs` VALUES ('106', '3', 'view', 'appointments', NULL, '', NULL, NULL, '102.0.123.41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:139.0) Gecko/20100101 Firefox/139.0', '2025-06-17 13:08:23');
INSERT INTO `audit_logs` VALUES ('107', '8', 'view', 'appointments', NULL, '', NULL, NULL, '102.213.179.127', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-17 13:14:51');
INSERT INTO `audit_logs` VALUES ('108', '3', 'view', 'appointments', NULL, '', NULL, NULL, '41.90.172.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-17 13:44:49');
INSERT INTO `audit_logs` VALUES ('109', '2', 'view', 'appointments', NULL, '', NULL, NULL, '41.90.172.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-17 14:02:57');
INSERT INTO `audit_logs` VALUES ('110', '2', 'view', 'appointments', NULL, '', NULL, NULL, '41.90.172.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-17 14:05:18');
INSERT INTO `audit_logs` VALUES ('111', '8', 'view', 'appointments', NULL, '', NULL, NULL, '102.213.179.127', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-27 18:52:30');
INSERT INTO `audit_logs` VALUES ('112', '2', 'view', 'appointments', NULL, '', NULL, NULL, '105.160.97.186', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-27 20:47:48');
INSERT INTO `audit_logs` VALUES ('113', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-05 16:27:49');
INSERT INTO `audit_logs` VALUES ('114', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-05 16:27:59');
INSERT INTO `audit_logs` VALUES ('115', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-05 16:45:50');
INSERT INTO `audit_logs` VALUES ('116', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-05 17:05:43');
INSERT INTO `audit_logs` VALUES ('117', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-05 17:53:16');
INSERT INTO `audit_logs` VALUES ('118', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-05 20:56:48');
INSERT INTO `audit_logs` VALUES ('119', NULL, '', '', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-05 20:57:40');
INSERT INTO `audit_logs` VALUES ('120', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-05 22:51:21');
INSERT INTO `audit_logs` VALUES ('121', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-06 04:56:06');
INSERT INTO `audit_logs` VALUES ('122', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-06 06:45:45');
INSERT INTO `audit_logs` VALUES ('123', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-25 20:04:12');
INSERT INTO `audit_logs` VALUES ('124', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-25 20:09:20');
INSERT INTO `audit_logs` VALUES ('125', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-25 20:10:20');
INSERT INTO `audit_logs` VALUES ('126', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-25 20:34:03');
INSERT INTO `audit_logs` VALUES ('127', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-25 20:43:06');
INSERT INTO `audit_logs` VALUES ('128', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-25 20:55:54');
INSERT INTO `audit_logs` VALUES ('129', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-25 21:47:37');
INSERT INTO `audit_logs` VALUES ('130', '1', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-25 22:04:16');
INSERT INTO `audit_logs` VALUES ('131', '3', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-25 22:35:39');
INSERT INTO `audit_logs` VALUES ('132', '3', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-25 22:51:20');
INSERT INTO `audit_logs` VALUES ('133', '4', 'lab_sample_registered', 'lab_test', '0', '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 00:16:15');
INSERT INTO `audit_logs` VALUES ('134', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 01:47:43');
INSERT INTO `audit_logs` VALUES ('135', '2', 'consultation_updated', 'consultation', '5', '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 01:48:25');
INSERT INTO `audit_logs` VALUES ('136', '2', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 02:29:54');
INSERT INTO `audit_logs` VALUES ('137', '12', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 03:18:31');
INSERT INTO `audit_logs` VALUES ('138', '12', 'create', 'appointment', '1', '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 03:31:31');
INSERT INTO `audit_logs` VALUES ('139', '12', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 03:44:36');
INSERT INTO `audit_logs` VALUES ('140', '12', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 03:44:55');
INSERT INTO `audit_logs` VALUES ('141', '12', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 03:45:09');
INSERT INTO `audit_logs` VALUES ('142', '12', 'create', 'appointment', '2', '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 03:50:01');
INSERT INTO `audit_logs` VALUES ('143', '12', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 03:50:39');
INSERT INTO `audit_logs` VALUES ('144', '12', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 04:08:58');
INSERT INTO `audit_logs` VALUES ('145', '12', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 04:09:22');
INSERT INTO `audit_logs` VALUES ('146', '12', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 04:16:12');
INSERT INTO `audit_logs` VALUES ('147', '12', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 04:17:10');
INSERT INTO `audit_logs` VALUES ('148', '3', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 04:25:36');
INSERT INTO `audit_logs` VALUES ('149', '3', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 04:26:14');
INSERT INTO `audit_logs` VALUES ('150', '12', 'vital_sign_created', 'vital_sign', '1', '{\"vital_sign_id\":1,\"patient_id\":\"2\"}', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-26 04:34:53');
INSERT INTO `audit_logs` VALUES ('151', '12', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-27 14:04:27');
INSERT INTO `audit_logs` VALUES ('152', '1', 'view', 'appointments', NULL, '', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-24 21:57:53');

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

-- Data for table `consultations`
INSERT INTO `consultations` VALUES ('3', NULL, '1', '1', '2', '2025-06-13 00:00:00', 'test', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"blood_pressure\":null,\"pulse\":null,\"temperature\":null,\"respiratory_rate\":null,\"oxygen_saturation\":null,\"height\":null,\"weight\":null,\"bmi\":null,\"pain_level\":null}', '', NULL, NULL, 'test', '', '2', '2025-06-13 17:32:57', '2025-06-13 18:13:18');
INSERT INTO `consultations` VALUES ('4', NULL, '1', '1', '2', '2025-06-13 00:00:00', 'test 2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"blood_pressure\":null,\"pulse\":null,\"temperature\":null,\"respiratory_rate\":null,\"oxygen_saturation\":null,\"height\":null,\"weight\":null,\"bmi\":null,\"pain_level\":null}', '', NULL, NULL, 'test 2', 'open', '2', '2025-06-13 17:56:42', '2025-06-13 17:56:42');
INSERT INTO `consultations` VALUES ('5', NULL, '1', '2', '2', '2025-06-13 00:00:00', 'demo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"blood_pressure\":\"\",\"pulse\":null,\"temperature\":null,\"respiratory_rate\":null,\"oxygen_saturation\":null,\"height\":null,\"weight\":null,\"bmi\":null,\"pain_level\":null}', '', NULL, NULL, 'demo', 'open', '2', '2025-06-13 19:14:55', '2025-07-26 01:48:25');
INSERT INTO `consultations` VALUES ('8', NULL, '1', '1', '2', '2025-07-05 00:00:00', 'abc', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"blood_pressure\":null,\"pulse\":null,\"temperature\":null,\"respiratory_rate\":null,\"oxygen_saturation\":null,\"height\":null,\"weight\":null,\"bmi\":null,\"pain_level\":null}', '', NULL, NULL, 'abc', 'open', '2', '2025-07-05 20:57:40', '2025-07-05 20:57:40');

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

-- Data for table `departments`
INSERT INTO `departments` VALUES ('1', 'Obstetrics', 'Deals with pregnancy, childbirth, and the postpartum period', '1', '2025-06-17 13:14:36', '2025-08-23 08:27:42', 'OBSTE', 'clinical', NULL, NULL, NULL);
INSERT INTO `departments` VALUES ('2', 'Gynecology', 'Deals with the health of the female reproductive system', '1', '2025-06-17 13:14:36', '2025-08-23 08:27:42', 'GYNEC', 'clinical', NULL, NULL, NULL);
INSERT INTO `departments` VALUES ('3', 'Laboratory', 'Handles medical testing and analysis', '1', '2025-06-17 13:14:36', '2025-08-23 08:27:42', 'LAB', 'support', NULL, NULL, NULL);
INSERT INTO `departments` VALUES ('4', 'Pharmacy', 'Handles medication dispensing and management', '1', '2025-06-17 13:14:36', '2025-08-23 08:27:42', 'PHARM', 'support', NULL, NULL, NULL);
INSERT INTO `departments` VALUES ('5', 'Administration', 'Handles hospital administration', '1', '2025-06-17 13:14:36', '2025-08-23 08:27:42', 'ADMIN', 'administrative', NULL, NULL, NULL);

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

-- Data for table `lab_samples`
INSERT INTO `lab_samples` VALUES ('1', 'LTS-20250726-15B2', '1', '4', 'blood', '2025-07-26', '4', '2025-07-26 00:16:15', 'registered', NULL, NULL, '0', '1', '2025-07-26 00:16:15', '2025-07-26 00:16:15');

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

-- Data for table `lab_test_types`
INSERT INTO `lab_test_types` VALUES ('1', 'Complete Blood Count', 'Full blood cell count analysis', 'Hematology', '1500.00', 'Varies by parameter', 'Various', '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `lab_test_types` VALUES ('2', 'Blood Glucose', 'Blood sugar level test', 'Chemistry', '500.00', '70-99', 'mg/dL', '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `lab_test_types` VALUES ('3', 'Pregnancy Test', 'hCG detection test', 'Reproductive', '800.00', 'Negative/Positive', NULL, '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `lab_test_types` VALUES ('4', 'HIV Test', 'HIV antibody test', 'Serology', '1000.00', 'Negative/Positive', NULL, '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `lab_test_types` VALUES ('5', 'Liver Function Test', 'Liver enzyme panel', 'Chemistry', '2000.00', 'Varies by parameter', 'Various', '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `lab_test_types` VALUES ('6', 'Urinalysis', 'Urine analysis', 'Microbiology', '800.00', 'Varies by parameter', 'Various', '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `lab_test_types` VALUES ('7', 'Lipid Profile', 'Cholesterol and lipid panel', 'Chemistry', '1800.00', 'Varies by parameter', 'Various', '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `lab_test_types` VALUES ('8', 'High Vaginal Swab', 'Microscopy, Culture, and Sensitivity of high vaginal swab', 'Microbiology', '2500.00', NULL, NULL, '1', '2025-05-14 16:37:34', '2025-05-14 16:37:34');
INSERT INTO `lab_test_types` VALUES ('9', 'Pap Smear Cytology', 'Cytology examination of cervical sample', 'Pathology', '3500.00', NULL, NULL, '1', '2025-05-14 16:37:34', '2025-05-14 16:37:34');

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

-- Data for table `medication_categories`
INSERT INTO `medication_categories` VALUES ('1', 'Antibiotics', 'Medications used to treat bacterial infections', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');
INSERT INTO `medication_categories` VALUES ('2', 'Analgesics', 'Pain relievers', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');
INSERT INTO `medication_categories` VALUES ('3', 'Anti-inflammatory', 'Medications to reduce inflammation', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');
INSERT INTO `medication_categories` VALUES ('4', 'Antihypertensives', 'Medications to treat high blood pressure', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');
INSERT INTO `medication_categories` VALUES ('5', 'Antidiabetics', 'Medications to treat diabetes', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');
INSERT INTO `medication_categories` VALUES ('6', 'Antihistamines', 'Medications to treat allergies', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');
INSERT INTO `medication_categories` VALUES ('7', 'Antidepressants', 'Medications to treat depression', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');
INSERT INTO `medication_categories` VALUES ('8', 'Antifungals', 'Medications to treat fungal infections', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');
INSERT INTO `medication_categories` VALUES ('9', 'Antivirals', 'Medications to treat viral infections', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');
INSERT INTO `medication_categories` VALUES ('10', 'Vaccines', 'Preparations used to stimulate the immune response', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');
INSERT INTO `medication_categories` VALUES ('11', 'Vitamins & Supplements', 'Nutritional supplements', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');
INSERT INTO `medication_categories` VALUES ('12', 'Other', 'Other medications', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');

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

-- Data for table `medications`
INSERT INTO `medications` VALUES ('1', 'Folic Acid', 'Folic Acid', 'Supplement', 'Tablet', '5', 'mg', NULL, '10.00', '1000', NULL, NULL, NULL, '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `medications` VALUES ('2', 'Iron Supplement', 'Ferrous Sulfate', 'Supplement', 'Tablet', '65', 'mg', NULL, '15.00', '1000', NULL, NULL, NULL, '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `medications` VALUES ('3', 'Paracetamol', 'Acetaminophen', 'Analgesic', 'Tablet', '500', 'mg', NULL, '5.00', '2000', NULL, NULL, NULL, '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `medications` VALUES ('4', 'Amoxicillin', 'Amoxicillin', 'Antibiotic', 'Capsule', '500', 'mg', NULL, '25.00', '1000', NULL, NULL, NULL, '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `medications` VALUES ('5', 'Metronidazole', 'Metronidazole', 'Antibiotic', 'Tablet', '400', 'mg', NULL, '20.00', '800', NULL, NULL, NULL, '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `medications` VALUES ('6', 'Birth Control Pills', 'Ethinylestradiol/Levonorgestrel', 'Contraceptive', 'Tablet', '0.03/0.15', 'mg', NULL, '350.00', '500', NULL, NULL, NULL, '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `medications` VALUES ('7', 'Mefenamic Acid', 'Mefenamic Acid', 'NSAID', 'Capsule', '500', 'mg', NULL, '15.00', '1000', NULL, NULL, NULL, '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');

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

-- Data for table `messages`
INSERT INTO `messages` VALUES ('1', '12', '3', 'Patient 000131', 'Their vitals are worrying. send them in right away.', 'high', '1', '0', '0', '2025-08-22 08:08:02', '2025-08-23 01:56:53', NULL, NULL);
INSERT INTO `messages` VALUES ('2', '12', '3', 'Patient 000131', 'Their vitals are worrying. send them in right away.', 'high', '1', '0', '0', '2025-08-22 08:18:26', '2025-08-23 01:56:27', NULL, NULL);
INSERT INTO `messages` VALUES ('3', '1', '3', 'Test Message for Dashboard', 'This is a test message to verify the dashboard messages card is working correctly.', 'normal', '1', '0', '0', '2025-08-22 11:37:40', '2025-08-22 16:46:21', NULL, NULL);
INSERT INTO `messages` VALUES ('4', '1', '3', 'Welcome to Nyalife HMS', 'Welcome to the Nyalife Hospital Management System. Please check your messages regularly for important updates.', 'high', '1', '0', '0', '2025-08-22 11:37:41', '2025-08-22 16:23:55', NULL, NULL);
INSERT INTO `messages` VALUES ('5', '12', '3', 'TEST MESSAGE', 'Test', 'low', '1', '0', '0', '2025-08-22 16:48:42', '2025-08-22 19:06:52', NULL, NULL);
INSERT INTO `messages` VALUES ('6', '3', '12', 'Re: TEST MESSAGE', 'test confirmed.\r\n\r\nRegards \r\nKevin\r\n\r\n--- Original Message ---\r\nFrom: doctor nyalife\r\nDate: 2025-08-22 16:48\r\nSubject: TEST MESSAGE\r\n\r\nTest', 'low', '1', '1', '0', '2025-08-23 01:56:05', '2025-08-23 04:17:08', '2025-08-23 02:41:47', NULL);
INSERT INTO `messages` VALUES ('7', '3', '12', 'Fwd: Patient 000131', '--- Forwarded Message ---\r\nFrom: doctor nyalife\r\nTo: Nyalife  Nurse\r\nDate: 2025-08-22 08:08\r\nSubject: Patient 000131\r\n\r\nTheir vitals are worrying. send them in right away.', 'high', '1', '0', '1', '2025-08-23 01:57:24', '2025-08-23 02:41:18', NULL, '2025-08-23 02:41:18');
INSERT INTO `messages` VALUES ('8', '3', '12', 'Re: TEST MESSAGE', 'TEST MESSAGE 2\r\n\r\n\r\n--- Original Message ---\r\nFrom: doctor nyalife\r\nDate: 2025-08-22 16:48\r\nSubject: TEST MESSAGE\r\n\r\nTest', 'low', '1', '0', '0', '2025-08-23 03:55:33', '2025-08-23 04:16:17', NULL, NULL);
INSERT INTO `messages` VALUES ('9', '1', '12', 'WELCOME USER', 'Welcome to Nyalfe HMS.', 'high', '1', '0', '0', '2025-08-23 03:59:28', '2025-08-23 04:16:33', NULL, NULL);
INSERT INTO `messages` VALUES ('10', '6', '1', 'APPOINTMENT QUERY', 'Query', 'normal', '1', '0', '0', '2025-08-23 04:10:52', '2025-08-23 04:47:40', NULL, NULL);
INSERT INTO `messages` VALUES ('11', '6', '12', 'APPOINTMENT QUERY - 2', 'Patient test', 'normal', '1', '0', '0', '2025-08-23 04:11:50', '2025-08-23 04:16:39', NULL, NULL);

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

-- Data for table `notifications`
INSERT INTO `notifications` VALUES ('1', '3', 'Appointment Reminder', 'You have an appointment scheduled for tomorrow at 10:00 AM', 'appointment_reminder', NULL, NULL, '1', '2025-08-22 15:00:57', '2025-08-22 15:00:58');
INSERT INTO `notifications` VALUES ('2', '3', 'New Message from Dr. Smith', 'Please review the lab results when you have a moment', 'message_received', NULL, NULL, '0', '2025-08-22 15:00:57', '2025-08-22 15:00:57');
INSERT INTO `notifications` VALUES ('3', '3', 'System Maintenance', 'System maintenance scheduled for tonight at 2:00 AM', 'system_alert', NULL, NULL, '0', '2025-08-22 15:00:57', '2025-08-22 15:00:57');

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

-- Data for table `patients`
INSERT INTO `patients` VALUES ('1', '6', 'NYA-PAT-001', 'O+', '165.00', '60.50', NULL, NULL, '+2547600000', 'John Jones', 'Husband', NULL, 'married', NULL, NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `patients` VALUES ('2', '7', 'NYA-PAT-002', 'A+', '183.00', '93.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-12 02:31:11', '2025-05-12 02:31:11');
INSERT INTO `patients` VALUES ('3', '14', 'PT2025000001', NULL, NULL, NULL, 'None', NULL, '0705883227', 'Test', NULL, NULL, NULL, NULL, NULL, '2025-08-22 08:28:58', '2025-08-22 08:28:58');

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

-- Data for table `roles`
INSERT INTO `roles` VALUES ('1', 'admin', 'System Administrator with full access', '2025-05-11 23:20:42', '2025-05-11 23:20:42');
INSERT INTO `roles` VALUES ('2', 'doctor', 'Medical doctor with patient consultation access', '2025-05-11 23:20:42', '2025-05-11 23:20:42');
INSERT INTO `roles` VALUES ('3', 'nurse', 'Nursing staff with limited clinical access', '2025-05-11 23:20:42', '2025-05-11 23:20:42');
INSERT INTO `roles` VALUES ('4', 'lab_technician', 'Laboratory staff with test management access', '2025-05-11 23:20:42', '2025-05-11 23:20:42');
INSERT INTO `roles` VALUES ('5', 'pharmacist', 'Pharmacy staff with medication management access', '2025-05-11 23:20:42', '2025-05-11 23:20:42');
INSERT INTO `roles` VALUES ('6', 'patient', 'Patient with limited personal data access', '2025-05-11 23:20:42', '2025-05-11 23:20:42');

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

-- Data for table `services`
INSERT INTO `services` VALUES ('1', 'Initial Consultation', 'Consultation', 'First-time patient consultation', '2500.00', '45', '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `services` VALUES ('2', 'Follow-up Consultation', 'Consultation', 'Follow-up appointment', '1500.00', '30', '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `services` VALUES ('3', 'Prenatal Check-up', 'Obstetrics', 'Regular pregnancy check-up', '1800.00', '30', '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `services` VALUES ('4', 'Ultrasound Scan', 'Diagnostics', 'Abdominal or transvaginal ultrasound', '3000.00', '30', '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `services` VALUES ('5', 'Pap Smear', 'Gynecology', 'Cervical cancer screening', '2000.00', '15', '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `services` VALUES ('6', 'IUD Insertion', 'Procedure', 'Intrauterine device insertion', '3500.00', '30', '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `services` VALUES ('7', 'STI Testing', 'Laboratory', 'Sexually transmitted infection testing', '2500.00', '15', '1', '2025-05-11 23:20:43', '2025-05-11 23:20:43');

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

-- Data for table `settings`
INSERT INTO `settings` VALUES ('1', 'hospital_name', 'Nyalife HMS', 'The name of the hospital', '1', NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `settings` VALUES ('2', 'hospital_address', '123 Health Avenue, Nairobi', 'The physical address of the hospital', '1', NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `settings` VALUES ('3', 'hospital_phone', '+254700000000', 'The contact phone number', '1', NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `settings` VALUES ('4', 'hospital_email', 'info@nyalife.com', 'The contact email address', '1', NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `settings` VALUES ('5', 'appointment_interval', '30', 'Default appointment interval in minutes', '0', NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `settings` VALUES ('6', 'primary_color', '#058b7c', 'Primary theme color', '1', NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `settings` VALUES ('7', 'secondary_color', '#d41559', 'Secondary theme color', '1', NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `settings` VALUES ('8', 'currency', 'KES', 'Default currency', '1', NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `settings` VALUES ('9', 'tax_rate', '16', 'Default tax rate percentage', '0', NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `settings` VALUES ('10', 'logo_path', 'assets/img/logo.png', 'Path to the hospital logo', '1', NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `settings` VALUES ('11', 'working_hours_start', '08:00:00', 'Start of working hours', '1', NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `settings` VALUES ('12', 'working_hours_end', '17:00:00', 'End of working hours', '1', NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43');
INSERT INTO `settings` VALUES ('13', 'working_days', 'Monday,Tuesday,Wednesday,Thursday,Friday', 'Working days', '1', NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43');

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

-- Data for table `specializations`
INSERT INTO `specializations` VALUES ('1', 'General Obstetrics', 'General pregnancy and childbirth care', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');
INSERT INTO `specializations` VALUES ('2', 'Maternal-Fetal Medicine', 'High-risk pregnancy care', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');
INSERT INTO `specializations` VALUES ('3', 'General Gynecology', 'General female reproductive health', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');
INSERT INTO `specializations` VALUES ('4', 'Reproductive Endocrinology', 'Hormonal disorders and fertility issues', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');
INSERT INTO `specializations` VALUES ('5', 'Gynecologic Oncology', 'Female reproductive cancers', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');
INSERT INTO `specializations` VALUES ('6', 'Urogynecology', 'Female pelvic medicine', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');
INSERT INTO `specializations` VALUES ('7', 'Family Planning', 'Contraception and pregnancy planning', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');
INSERT INTO `specializations` VALUES ('8', 'General Practice', 'General medical care', '1', '2025-06-17 13:14:36', '2025-06-17 13:14:36');

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

-- Data for table `staff`
INSERT INTO `staff` VALUES ('1', '2', 'NYA-DOC-001', 'Medical', 'Gynecologist', 'Obstetrics and Gynecology', 'MBCHB, MMED OBGYN', '2023-01-01', NULL, NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43', NULL);
INSERT INTO `staff` VALUES ('2', '3', 'NYA-NUR-001', 'Nursing', 'Registered Nurse', 'Maternal Care', 'BSN, RN', '2023-02-01', NULL, NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43', NULL);
INSERT INTO `staff` VALUES ('3', '4', 'NYA-LAB-001', 'Laboratory', 'Lab Technician', 'Medical Laboratory', 'BSc Medical Laboratory', '2023-02-15', NULL, NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43', NULL);
INSERT INTO `staff` VALUES ('4', '5', 'NYA-PHM-001', 'Pharmacy', 'Pharmacist', 'Clinical Pharmacy', 'BPharm', '2023-03-01', NULL, NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43', NULL);
INSERT INTO `staff` VALUES ('8', '8', 'NYA-DOC-008', 'Medical', 'Gynecologist', 'Obstetrics and Gynecology', 'MBCHB, MMED OBGYN', '2023-01-01', NULL, NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43', NULL);
INSERT INTO `staff` VALUES ('12', '12', 'NYA-DOC-005', 'Medical', 'Gynecologist', 'Obstetrics and Gynecology', 'MBCHB, MMED OBGYN', '2025-01-01', NULL, NULL, '2025-05-11 23:20:43', '2025-05-11 23:20:43', NULL);

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

-- Data for table `user_tokens`
INSERT INTO `user_tokens` VALUES ('1', '6', '$2y$10$udxLW79LR2ZZBurKi5YGqu6qVeC5tpOPZflOyz599piGEnreJ7Kc.', '2025-06-11 17:09:51', '2025-05-12 17:09:51');

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

-- Data for table `users`
INSERT INTO `users` VALUES ('1', '1', 'admin', '$2y$10$1gotGmyBvpCDrrUTgU1dau2gPoElIRxKhwkrR3.t56ZxuseY8P53S', 'admin@nyalife.com', '+2547000000', 'System', 'Administrator', NULL, 'male', NULL, NULL, NULL, NULL, NULL, '1', '2025-08-26 14:02:17', NULL, '2025-05-11 23:20:43', '2025-08-26 14:02:17');
INSERT INTO `users` VALUES ('2', '2', 'doctor', '$2y$10$1gotGmyBvpCDrrUTgU1dau2gPoElIRxKhwkrR3.t56ZxuseY8P53S', 'doctor@nyalife.com', '+2547100000', 'Nyalife', 'Doctor', NULL, 'female', NULL, NULL, NULL, NULL, NULL, '1', '2025-08-22 07:36:32', NULL, '2025-05-11 23:20:43', '2025-08-22 07:36:32');
INSERT INTO `users` VALUES ('3', '3', 'nurse', '$2y$10$1gotGmyBvpCDrrUTgU1dau2gPoElIRxKhwkrR3.t56ZxuseY8P53S', 'nurse@nyalife.com', '+2547200000', 'Nyalife ', 'Nurse', NULL, 'female', NULL, NULL, NULL, NULL, NULL, '1', '2025-08-23 03:54:18', NULL, '2025-05-11 23:20:43', '2025-08-23 03:54:18');
INSERT INTO `users` VALUES ('4', '4', 'lab', '$2y$10$1gotGmyBvpCDrrUTgU1dau2gPoElIRxKhwkrR3.t56ZxuseY8P53S', 'lab@nyalife.com', '+2547300000', 'Nyalife', 'Lab', NULL, 'male', NULL, NULL, NULL, NULL, NULL, '1', '2025-08-26 13:18:44', NULL, '2025-05-11 23:20:43', '2025-08-26 13:18:44');
INSERT INTO `users` VALUES ('5', '5', 'pharm', '$2y$10$1gotGmyBvpCDrrUTgU1dau2gPoElIRxKhwkrR3.t56ZxuseY8P53S', 'pharm@nyalife.com', '+2547400000', 'Nyalife', 'Pharmacy', NULL, 'female', NULL, NULL, NULL, NULL, NULL, '1', '2025-06-13 19:41:21', NULL, '2025-05-11 23:20:43', '2025-06-17 12:58:13');
INSERT INTO `users` VALUES ('6', '6', 'patient', '$2y$10$1gotGmyBvpCDrrUTgU1dau2gPoElIRxKhwkrR3.t56ZxuseY8P53S', 'patient@example.com', '+2547500000', 'Patient', 'Test', '1990-05-15', 'female', '123 Main St', 'Nairobi', NULL, NULL, NULL, '1', '2025-08-23 08:55:38', NULL, '2025-05-11 23:20:43', '2025-08-23 08:55:38');
INSERT INTO `users` VALUES ('7', '6', 'kt12345', '$2y$10$MrrJg6qBG..4Wrk8OGebX.Gfgqy3nUrQ87joblx2FXOhGf8u3WLCe', 'test@test.com', '071247921', 'Kevin', 'Test', '1997-02-28', 'male', 'Langata', NULL, NULL, NULL, NULL, '1', '2025-05-12 02:52:55', NULL, '2025-05-12 02:31:11', '2025-05-12 02:52:55');
INSERT INTO `users` VALUES ('8', '2', 'schola_airo', '$2y$10$aRb21pUvxBAYF9U8S0wLqerEjZu0bjYtFCRWuubiSOBpbak4mUUfm', 'scolanick08@gmail.com', '+254 721 783027', 'Scholastica', 'Airo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '2025-07-02 20:11:53', NULL, '2025-06-17 12:01:37', '2025-07-02 20:11:53');
INSERT INTO `users` VALUES ('9', '3', 'Nurse-001', '$2y$10$04DT8CwY87Kt2DDw7eLVQe/3nMDh/j4iRmojKsmmgf9UvBSLTUTqa', 'nurse@nyalife.net', '0705123123', 'Nyalife ', 'Nurse-001', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, NULL, '2025-07-06 06:01:32', '2025-07-06 06:01:32');
INSERT INTO `users` VALUES ('12', '2', 'Doctor_2', '$2y$10$y66QHFAnEdXBbag0OYblpOLpns9tpDTAdirU.UP5xxdBvJyc9FeI6', 'doctor_2@nyalife.com', '0712742831', 'doctor', 'nyalife', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '2025-08-23 04:12:34', NULL, '2025-07-26 02:49:34', '2025-08-23 04:12:34');
INSERT INTO `users` VALUES ('14', '6', 'kairuwan@mail.com', '$2y$10$NLpBbdzefMn9r76..lRa..ZTUTYbh54RCarOrLx3zze2H2lIuavym', 'kairuwan@mail.com', '0717215425', 'Wanja', 'Kairu', '1996-09-19', 'female', 'Test Address', NULL, NULL, NULL, NULL, '1', NULL, NULL, '2025-08-22 08:28:58', '2025-08-22 08:28:58');

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

-- Data for table `vital_signs`
INSERT INTO `vital_signs` VALUES ('1', '2', NULL, '120/80', '70', '15', '35.0', '90.00', '183.00', '26.87', '7', '90', 'note', '2025-07-26 04:34:53', '12', '2025-07-26 04:34:53', '2025-07-26 04:34:53');

SET FOREIGN_KEY_CHECKS = 1;
