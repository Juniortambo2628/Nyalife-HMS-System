-- Nyalife HMS Production Database Setup
-- Run this script on your production server

-- 1. Create database
CREATE DATABASE IF NOT EXISTS `nyalife_hms_production`;
USE `nyalife_hms_production`;

-- 2. Import structure and data
-- Run: mysql -u username -p nyalife_hms_production < nyalife_hms_complete.sql

-- 3. Create production user (optional)
-- CREATE USER 'nyalife_user'@'localhost' IDENTIFIED BY 'strong_password';
-- GRANT ALL PRIVILEGES ON nyalife_hms_production.* TO 'nyalife_user'@'localhost';
-- FLUSH PRIVILEGES;

-- 4. Verify tables
SHOW TABLES;

-- 5. Check user count
SELECT COUNT(*) as total_users FROM users;
SELECT COUNT(*) as total_patients FROM patients;
SELECT COUNT(*) as total_staff FROM staff;
