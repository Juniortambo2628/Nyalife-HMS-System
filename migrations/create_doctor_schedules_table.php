<?php
/**
 * Phinx Migration: Create doctor_schedules table
 * 
 * Creates the doctor_schedules table for managing doctor availability schedules
 */

use Phinx\Migration\AbstractMigration;

class CreateDoctorSchedulesTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     */
    public function change(): void
    {
        $table = $this->table('doctor_schedules', [
            'id' => false,
            'primary_key' => 'schedule_id',
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci'
        ]);

        $table->addColumn('schedule_id', 'integer', [
            'identity' => true,
            'signed' => false,
            'null' => false
        ])
        ->addColumn('doctor_id', 'integer', [
            'signed' => false,
            'null' => false,
            'comment' => 'References staff.staff_id'
        ])
        ->addColumn('day_of_week', 'integer', [
            'limit' => 1,
            'signed' => false,
            'null' => false,
            'comment' => '0=Sunday, 1=Monday, ..., 6=Saturday'
        ])
        ->addColumn('start_time', 'time', [
            'null' => false,
            'comment' => 'Start time for this day'
        ])
        ->addColumn('end_time', 'time', [
            'null' => false,
            'comment' => 'End time for this day'
        ])
        ->addColumn('appointment_duration', 'integer', [
            'limit' => 3,
            'signed' => false,
            'null' => false,
            'default' => 30,
            'comment' => 'Duration in minutes'
        ])
        ->addColumn('is_active', 'boolean', [
            'default' => true,
            'null' => false
        ])
        ->addColumn('created_at', 'timestamp', [
            'default' => 'CURRENT_TIMESTAMP',
            'null' => false
        ])
        ->addColumn('updated_at', 'timestamp', [
            'default' => 'CURRENT_TIMESTAMP',
            'update' => 'CURRENT_TIMESTAMP',
            'null' => false
        ])
        ->addIndex(['doctor_id'], ['name' => 'idx_doctor_id'])
        ->addIndex(['day_of_week'], ['name' => 'idx_day_of_week'])
        ->addIndex(['is_active'], ['name' => 'idx_is_active'])
        ->addIndex(['doctor_id', 'day_of_week', 'is_active'], [
            'name' => 'idx_doctor_day_active',
            'unique' => false
        ])
        ->addForeignKey('doctor_id', 'staff', 'staff_id', [
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
            'constraint' => 'fk_doctor_schedules_staff'
        ])
        ->create();
    }
}

