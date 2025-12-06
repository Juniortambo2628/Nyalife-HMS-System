-- Table: appointments
-- Generated: 2025-08-26 15:03:20

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
