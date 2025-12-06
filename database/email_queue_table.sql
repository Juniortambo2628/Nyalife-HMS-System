-- Email Queue Table for Background Processing
-- This table stores emails that need to be sent to avoid blocking the appointment submission process

CREATE TABLE IF NOT EXISTS email_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    type ENUM('appointment_confirmation', 'appointment_reminder', 'password_reset', 'welcome') NOT NULL,
    reference_id INT NULL COMMENT 'ID of related record (appointment_id, user_id, etc.)',
    subject VARCHAR(500) NULL,
    body TEXT NULL,
    template VARCHAR(100) NULL COMMENT 'Email template to use',
    status ENUM('pending', 'processing', 'sent', 'failed') DEFAULT 'pending',
    priority TINYINT DEFAULT 5 COMMENT '1=highest, 10=lowest priority',
    attempts INT DEFAULT 0 COMMENT 'Number of send attempts',
    max_attempts INT DEFAULT 3 COMMENT 'Maximum number of attempts',
    error_message TEXT NULL COMMENT 'Last error message if failed',
    scheduled_at DATETIME NULL COMMENT 'When to send (for delayed emails)',
    processed_at DATETIME NULL COMMENT 'When email was processed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_scheduled (scheduled_at),
    INDEX idx_priority (priority),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial test data (optional)
-- INSERT INTO email_queue (email, type, reference_id, subject, status) 
-- VALUES ('test@example.com', 'appointment_confirmation', 1, 'Appointment Confirmation', 'pending');
