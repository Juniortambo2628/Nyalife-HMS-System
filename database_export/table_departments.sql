-- Table: departments
-- Generated: 2025-08-26 15:03:19

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
