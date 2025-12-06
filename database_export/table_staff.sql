-- Table: staff
-- Generated: 2025-08-26 15:03:18

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
