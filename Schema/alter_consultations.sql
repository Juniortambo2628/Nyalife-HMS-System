-- SQL script to modify the consultations table to make appointment_id nullable
-- This allows for walk-in patients who don't have appointments

-- First, drop the foreign key constraint
ALTER TABLE consultations
DROP FOREIGN KEY consultations_ibfk_1;

-- Modify the appointment_id column to allow NULL values
ALTER TABLE consultations
MODIFY appointment_id int(11) NULL;

-- Re-add the foreign key constraint but with ON DELETE SET NULL
ALTER TABLE consultations
ADD CONSTRAINT consultations_ibfk_1 
FOREIGN KEY (appointment_id) 
REFERENCES appointments (appointment_id)
ON DELETE SET NULL;

-- Add an is_walk_in column to explicitly mark walk-in consultations
ALTER TABLE consultations
ADD COLUMN is_walk_in TINYINT(1) NOT NULL DEFAULT 0 AFTER appointment_id; 