-- Nyalife HMS - Post Import Patch
-- Use this AFTER importing DB/nyalifew_hms_prod.sql to align with current code

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

-- 1) Ensure email_queue exists (non-blocking emails)
CREATE TABLE IF NOT EXISTS email_queue (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL,
  type ENUM('appointment_confirmation','appointment_reminder','password_reset','welcome') NOT NULL,
  reference_id INT NULL,
  subject VARCHAR(500) NULL,
  body TEXT NULL,
  template VARCHAR(100) NULL,
  status ENUM('pending','processing','sent','failed') DEFAULT 'pending',
  priority TINYINT DEFAULT 5,
  attempts INT DEFAULT 0,
  max_attempts INT DEFAULT 3,
  error_message TEXT NULL,
  scheduled_at DATETIME NULL,
  processed_at DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (status),
  INDEX idx_type (type),
  INDEX idx_scheduled (scheduled_at),
  INDEX idx_priority (priority),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Appointments status enum: add values used by code ('confirmed','pending')
-- and keep existing values if present
SET @cur := (SELECT COLUMN_TYPE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'appointments'
               AND COLUMN_NAME = 'status');

-- Only alter if needed
DO
BEGIN
  IF @cur NOT LIKE '%confirmed%' OR @cur NOT LIKE '%pending%' THEN
    ALTER TABLE appointments 
      MODIFY COLUMN status ENUM('scheduled','confirmed','completed','cancelled','pending','no_show')
      NOT NULL DEFAULT 'scheduled';
  END IF;
END;

-- 3) Ensure staff.user_id is unique (one staff record per user)
ALTER TABLE staff ADD UNIQUE KEY `unique_user_id` (user_id);

-- 4) Helpful indexes if missing
ALTER TABLE appointments 
  ADD INDEX idx_appt_datetime (appointment_date, appointment_time),
  ADD INDEX idx_appt_patient (patient_id),
  ADD INDEX idx_appt_doctor (doctor_id);

SET FOREIGN_KEY_CHECKS=1;

-- End Patch

