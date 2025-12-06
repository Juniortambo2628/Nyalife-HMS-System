-- =====================================================
-- NYALIFE HMS DATABASE STATUS VERIFICATION
-- =====================================================
-- Run this script to verify your database is properly configured
-- =====================================================

USE nyalifew_hms_prod;

-- 1. CHECK DATABASE INFORMATION
-- =====================================================
SELECT 
    SCHEMA_NAME as database_name,
    DEFAULT_CHARACTER_SET_NAME as charset,
    DEFAULT_COLLATION_NAME as collation
FROM information_schema.SCHEMATA 
WHERE SCHEMA_NAME = 'nyalifew_hms_prod';

-- 2. COUNT TOTAL TABLES
-- =====================================================
SELECT COUNT(*) as total_tables 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'nyalifew_hms_prod';

-- 3. LIST ALL TABLES WITH ENGINE AND CHARSET
-- =====================================================
SELECT 
    TABLE_NAME,
    ENGINE,
    TABLE_COLLATION,
    TABLE_ROWS,
    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS 'Size_MB'
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'nyalifew_hms_prod'
ORDER BY TABLE_NAME;

-- 4. COUNT FOREIGN KEY CONSTRAINTS
-- =====================================================
SELECT COUNT(*) as total_foreign_keys 
FROM information_schema.KEY_COLUMN_USAGE 
WHERE REFERENCED_TABLE_SCHEMA = 'nyalifew_hms_prod' 
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- 5. LIST FOREIGN KEY RELATIONSHIPS
-- =====================================================
SELECT 
    TABLE_NAME,
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE REFERENCED_TABLE_SCHEMA = 'nyalifew_hms_prod' 
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, CONSTRAINT_NAME;

-- 6. CHECK INDEXES
-- =====================================================
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    NON_UNIQUE,
    SEQ_IN_INDEX
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = 'nyalifew_hms_prod'
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;

-- 7. VERIFY CRITICAL TABLES HAVE DATA
-- =====================================================
SELECT 'roles' as table_name, COUNT(*) as record_count FROM roles
UNION ALL
SELECT 'users' as table_name, COUNT(*) as record_count FROM users
UNION ALL
SELECT 'departments' as table_name, COUNT(*) as record_count FROM departments
UNION ALL
SELECT 'staff' as table_name, COUNT(*) as record_count FROM staff
UNION ALL
SELECT 'patients' as table_name, COUNT(*) as record_count FROM patients
UNION ALL
SELECT 'appointments' as table_name, COUNT(*) as record_count FROM appointments
UNION ALL
SELECT 'consultations' as table_name, COUNT(*) as record_count FROM consultations;

-- 8. CHECK USER ROLES AND PERMISSIONS
-- =====================================================
SELECT 
    u.username,
    u.email,
    r.role_name,
    u.is_active,
    u.last_login
FROM users u
JOIN roles r ON u.role_id = r.role_id
ORDER BY r.role_name, u.username;

-- 9. VERIFY APPOINTMENT SYSTEM
-- =====================================================
SELECT 
    a.appointment_id,
    CONCAT(p.first_name, ' ', p.last_name) as patient_name,
    CONCAT(s.first_name, ' ', s.last_name) as doctor_name,
    a.appointment_date,
    a.appointment_time,
    a.status
FROM appointments a
JOIN patients p ON a.patient_id = p.patient_id
JOIN staff s ON a.doctor_id = s.staff_id
JOIN users u ON s.user_id = u.user_id
ORDER BY a.appointment_date DESC
LIMIT 10;

-- 10. CHECK SYSTEM SETTINGS
-- =====================================================
SELECT 
    setting_key,
    setting_value,
    description
FROM settings
ORDER BY setting_key;

-- 11. VERIFY AUDIT LOGGING
-- =====================================================
SELECT 
    COUNT(*) as total_audit_logs,
    MAX(created_at) as latest_log_entry
FROM audit_logs;

-- 12. CHECK DATABASE SIZE
-- =====================================================
SELECT 
    ROUND(SUM(DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS 'Database_Size_MB'
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'nyalifew_hms_prod';

-- =====================================================
-- VERIFICATION COMPLETE
-- =====================================================
-- Review the results above to ensure:
-- 1. All 42 tables are present
-- 2. Foreign key constraints are properly established
-- 3. Sample data is loaded
-- 4. Indexes are created
-- 5. Character sets are consistent (utf8mb4)
-- =====================================================
