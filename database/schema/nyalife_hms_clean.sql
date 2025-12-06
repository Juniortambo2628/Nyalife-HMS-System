-- Nyalife HMS - Clean Schema for Production
-- Compatible with phpMyAdmin import on shared hosting

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

-- Drop existing tables (order matters for FKs)
DROP TABLE IF EXISTS email_queue;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS patients;
DROP TABLE IF EXISTS staff;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

-- Roles
CREATE TABLE roles (
  role_id INT AUTO_INCREMENT PRIMARY KEY,
  role_name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO roles (role_name) VALUES 
('admin'), ('doctor'), ('nurse'), ('lab_technician'), ('pharmacist'), ('patient'), ('receptionist');

-- Users
CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role_id INT NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  phone VARCHAR(30) NULL,
  date_of_birth DATE NULL,
  gender ENUM('male','female','other') NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Staff (Doctors and other staff linked to user)
CREATE TABLE staff (
  staff_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  department VARCHAR(120) NULL,
  specialization VARCHAR(120) NULL,
  join_date DATE NULL,
  employee_id VARCHAR(50) NULL,
  CONSTRAINT fk_staff_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Patients
CREATE TABLE patients (
  patient_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  patient_number VARCHAR(50) NOT NULL UNIQUE,
  registration_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  chronic_diseases TEXT NULL,
  emergency_name VARCHAR(120) NULL,
  emergency_contact VARCHAR(50) NULL,
  CONSTRAINT fk_patients_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Appointments
CREATE TABLE appointments (
  appointment_id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  doctor_id INT NOT NULL, -- references staff.staff_id
  appointment_date DATE NOT NULL,
  appointment_time TIME NOT NULL,
  end_time TIME NOT NULL,
  status ENUM('scheduled','confirmed','completed','cancelled','pending') NOT NULL DEFAULT 'scheduled',
  appointment_type VARCHAR(80) NOT NULL,
  reason TEXT NULL,
  notes TEXT NULL,
  created_by INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_appt_patient FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
  CONSTRAINT fk_appt_doctor FOREIGN KEY (doctor_id) REFERENCES staff(staff_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Email Queue (non-blocking email processing)
CREATE TABLE email_queue (
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

SET FOREIGN_KEY_CHECKS=1;

-- Seed admin (optional; change password after import)
-- INSERT INTO users (username,email,password,role_id,first_name,last_name,phone,status)
-- VALUES ('admin','admin@your-domain.com', '$2y$10$examplehashedpassword', 1, 'System','Admin', '+0000000000','active');


