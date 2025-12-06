<?php

/**
 * Nyalife HMS - Doctor Schedule Model
 *
 * Model for handling doctor schedule data.
 */

require_once __DIR__ . '/BaseModel.php';

class DoctorScheduleModel extends BaseModel
{
    protected $table = 'doctor_schedules';
    protected $primaryKey = 'schedule_id';

    /**
     * Get doctor's schedule for a specific day
     *
     * @param int $doctorId Doctor's staff ID
     * @param int $dayOfWeek Day of week (0=Sunday, 1=Monday, etc.)
     * @return array|bool Schedule data or false if not found
     */
    public function getDoctorSchedule($doctorId, $dayOfWeek)
    {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE doctor_id = ? AND day_of_week = ? AND is_active = 1";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('ii', $doctorId, $dayOfWeek);
            $stmt->execute();
            $result = $stmt->get_result();
            $schedule = $result->fetch_assoc();
            $stmt->close();

            return $schedule;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get all schedules for a doctor
     *
     * @param int $doctorId Doctor's staff ID
     * @return array Array of schedules
     */
    public function getDoctorSchedules($doctorId)
    {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE doctor_id = ? AND is_active = 1 
                    ORDER BY day_of_week ASC";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->bind_param('i', $doctorId);
            $stmt->execute();
            $result = $stmt->get_result();
            $schedules = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $schedules;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Create or update doctor schedule
     *
     * @param array $data Schedule data
     * @return int|bool Last insert ID or false on failure
     */
    public function saveDoctorSchedule(array $data)
    {
        try {
            // Check if schedule already exists for this doctor and day
            $existing = $this->getDoctorSchedule($data['doctor_id'], $data['day_of_week']);

            if ($existing) {
                // Update existing schedule
                return $this->update($existing['schedule_id'], $data);
            }
            // Create new schedule
            return $this->create($data);
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Delete doctor schedule
     *
     * @param int $scheduleId Schedule ID
     * @return bool Success status
     */
    public function deleteDoctorSchedule($scheduleId)
    {
        try {
            return $this->delete($scheduleId);
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return false;
        }
    }

    /**
     * Get available time slots for a doctor on a specific date
     *
     * @param int $doctorId Doctor's staff ID
     * @param string $date Date (Y-m-d format)
     * @return array Array of available time slots
     */
    public function getAvailableTimeSlots($doctorId, $date)
    {
        try {
            // date('w') returns string '0'-'6', but getDoctorSchedule expects int
            $dayOfWeek = (int)date('w', strtotime($date));
            $schedule = $this->getDoctorSchedule($doctorId, $dayOfWeek);

            if (!$schedule) {
                return []; // Doctor doesn't work on this day
            }

            // Get existing appointments for that doctor on that date
            $appointmentModel = new AppointmentModel();
            $existingAppointments = $appointmentModel->getDoctorAppointments($doctorId, $date);

            $bookedTimes = [];
            foreach ($existingAppointments as $appointment) {
                $bookedTimes[] = $appointment['appointment_time'];
            }

            // Generate available time slots
            $startTime = strtotime((string) $schedule['start_time']);
            $endTime = strtotime((string) $schedule['end_time']);
            $duration = $schedule['appointment_duration'] * 60; // Convert to seconds

            $availableSlots = [];
            for ($time = $startTime; $time < $endTime; $time += $duration) {
                $formattedTime = date('H:i:s', $time);
                if (!in_array($formattedTime, $bookedTimes)) {
                    $availableSlots[] = $formattedTime;
                }
            }

            return $availableSlots;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get all doctors with their schedules
     *
     * @return array Array of doctors with their schedules
     */
    public function getAllDoctorsWithSchedules()
    {
        try {
            $sql = "SELECT s.staff_id, s.user_id, s.department, s.specialization,
                           CONCAT(u.first_name, ' ', u.last_name) as doctor_name,
                           ds.day_of_week, ds.start_time, ds.end_time, ds.appointment_duration
                    FROM staff s
                    JOIN users u ON s.user_id = u.user_id
                    LEFT JOIN {$this->table} ds ON s.staff_id = ds.doctor_id AND ds.is_active = 1
                    WHERE s.position LIKE '%doctor%' OR s.position LIKE '%physician%' OR s.position LIKE '%gynecologist%'
                    ORDER BY s.staff_id, ds.day_of_week";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $doctors = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $doctors;
        } catch (Exception $e) {
            ErrorHandler::logDatabaseError($e, __METHOD__);
            return [];
        }
    }

    /**
     * Get day names
     *
     * @return array Array of day names
     */
    public function getDayNames(): array
    {
        return [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday'
        ];
    }
}
