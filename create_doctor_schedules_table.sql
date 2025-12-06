-- Create doctor_schedules table
-- This table stores doctor availability schedules by day of week

CREATE TABLE IF NOT EXISTS `doctor_schedules` (
  `schedule_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `doctor_id` INT UNSIGNED NOT NULL COMMENT 'References staff.staff_id',
  `day_of_week` TINYINT UNSIGNED NOT NULL COMMENT '0=Sunday, 1=Monday, 2=Tuesday, 3=Wednesday, 4=Thursday, 5=Friday, 6=Saturday',
  `start_time` TIME NOT NULL COMMENT 'Start time for this day',
  `end_time` TIME NOT NULL COMMENT 'End time for this day',
  `appointment_duration` SMALLINT UNSIGNED NOT NULL DEFAULT 30 COMMENT 'Duration in minutes',
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`schedule_id`),
  INDEX `idx_doctor_id` (`doctor_id`),
  INDEX `idx_day_of_week` (`day_of_week`),
  INDEX `idx_is_active` (`is_active`),
  INDEX `idx_doctor_day_active` (`doctor_id`, `day_of_week`, `is_active`),
  CONSTRAINT `fk_doctor_schedules_staff` 
    FOREIGN KEY (`doctor_id`) 
    REFERENCES `staff` (`staff_id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

