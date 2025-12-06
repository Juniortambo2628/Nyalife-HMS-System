# Next Steps Guide - Database Alignment Completion

## Current Status

✅ **Views Implementation:** 100% Complete (110/110 views implemented)
⚠️ **Database Alignment:** 95.2% Complete (1 table missing)

## Missing Table: `doctor_schedules`

The `doctor_schedules` table is referenced by `DoctorScheduleModel` but does not exist in the database.

### Table Structure Required

Based on `DoctorScheduleModel.php`, the table needs:

```sql
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
```

## Methods to Create the Table

### Method 1: Using phpMyAdmin (Recommended)

1. Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
2. Select database: `nyalifew_hms_prod`
3. Click on "SQL" tab
4. Copy and paste the SQL from `create_doctor_schedules_table.sql`
5. Click "Go" to execute

### Method 2: Using MySQL Command Line

```bash
# Connect to MySQL
mysql -u root -p

# Select database
USE nyalifew_hms_prod;

# Run the SQL file
SOURCE create_doctor_schedules_table.sql;
```

### Method 3: Using PHP Script (When Database is Accessible)

```bash
# Ensure MySQL/WAMP is running
php create_doctor_schedules_table.php
```

### Method 4: Using Phinx Migration

```bash
# Ensure MySQL/WAMP is running and configured in phinx.php
vendor/bin/phinx migrate -e development
```

## Verification

After creating the table, run:

```bash
php verify_database_alignment.php
```

Expected output:
- ✅ All 21 model tables found
- ✅ 0 tables missing
- ✅ 61 foreign key relationships (60 + 1 new)

## Files Created

1. **`create_doctor_schedules_table.php`** - PHP script to create table
2. **`create_doctor_schedules_table.sql`** - SQL file for manual execution
3. **`migrations/create_doctor_schedules_table.php`** - Phinx migration
4. **`NEXT_STEPS_GUIDE.md`** - This guide

## Alternative: Update Model (If Table Not Needed)

If the `doctor_schedules` table is not actually needed, you can:

1. Remove or comment out `DoctorScheduleModel` usage
2. Update the model to reference an existing table
3. Mark the model as deprecated

However, based on the model code, it appears to be actively used for managing doctor availability schedules, so creating the table is recommended.

## Summary

**Current Status:**
- ✅ Views: 100% complete
- ⚠️ Database: 95.2% complete (1 table missing)

**Action Required:**
- Create `doctor_schedules` table using one of the methods above

**After Completion:**
- Run `php verify_database_alignment.php` to confirm
- All verifications will pass ✅

