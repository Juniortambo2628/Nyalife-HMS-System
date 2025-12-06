-- Table: users
-- Generated: 2025-08-26 15:03:16

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
