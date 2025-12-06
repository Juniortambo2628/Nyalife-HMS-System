<?php
/**
 * Nyalife HMS - Appointment Data Functions
 *
 * Contains functions for retrieving and manipulating appointment data.
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Get appointments for a doctor
 *
 * @param int $doctorId Doctor's staff ID
 * @param string $date Optional date filter (format: YYYY-MM-DD)
 * @param string $status Optional status filter
 * @return array Array of appointments
 */
if (!function_exists('getDoctorAppointments')) {
function getDoctorAppointments($doctorId, $date = null, $status = null)
{
    global $conn;

    $query = "SELECT a.*, 
              p.patient_number,
              CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
              pu.gender, pu.date_of_birth
              FROM appointments a
              JOIN patients p ON a.patient_id = p.patient_id
              JOIN users pu ON p.user_id = pu.user_id
              WHERE a.doctor_id = ?";

    $params = [$doctorId];
    $types = "i";

    if ($date) {
        $query .= " AND a.appointment_date = ?";
        $params[] = $date;
        $types .= "s";
    }

    if ($status) {
        $query .= " AND a.status = ?";
        $params[] = $status;
        $types .= "s";
    }

    $query .= " ORDER BY a.appointment_date ASC, a.appointment_time ASC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}
} // End function_exists check

/**
 * Get appointments for a nurse
 *
 * @param int $nurseId Nurse's staff ID
 * @param string $date Optional date filter (format: YYYY-MM-DD)
 * @param string $status Optional status filter
 * @return array Array of appointments
 */
if (!function_exists('getNurseAppointments')) {
function getNurseAppointments($nurseId, $date = null, $status = null)
{
    global $conn;

    // Nurses can see all appointments
    $query = "SELECT a.*, 
              p.patient_number,
              CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
              CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
              pu.gender, pu.date_of_birth
              FROM appointments a
              JOIN patients p ON a.patient_id = p.patient_id
              JOIN users pu ON p.user_id = pu.user_id
              JOIN staff d ON a.doctor_id = d.staff_id
              JOIN users du ON d.user_id = du.user_id";

    $params = [];
    $types = "";

    if ($date) {
        $query .= " WHERE a.appointment_date = ?";
        $params[] = $date;
        $types .= "s";
    }

    if ($status) {
        if ($params === []) {
            $query .= " WHERE a.status = ?";
        } else {
            $query .= " AND a.status = ?";
        }
        $params[] = $status;
        $types .= "s";
    }

    $query .= " ORDER BY a.appointment_date ASC, a.appointment_time ASC";

    $stmt = $conn->prepare($query);

    if ($params !== []) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}
} // End function_exists check for getNurseAppointments

/**
 * Get appointments for a patient
 *
 * @param int $patientId Patient ID
 * @param string $status Optional status filter
 * @return array Array of appointments
 */
if (!function_exists('getPatientAppointments')) {
function getPatientAppointments($patientId, $status = null)
{
    global $conn;

    $query = "SELECT a.*, 
              CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
              d.specialization
              FROM appointments a
              JOIN staff d ON a.doctor_id = d.staff_id
              JOIN users du ON d.user_id = du.user_id
              WHERE a.patient_id = ?";

    $params = [$patientId];
    $types = "i";

    if ($status) {
        $query .= " AND a.status = ?";
        $params[] = $status;
        $types .= "s";
    }

    $query .= " ORDER BY a.appointment_date ASC, a.appointment_time ASC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}
} // End function_exists check for getPatientAppointments

/**
 * Get appointments for a date
 *
 * @param string $date Date in YYYY-MM-DD format
 * @param string $status Optional status filter
 * @return array Array of appointments
 */
function getAppointmentsByDate($date, $status = null)
{
    global $conn;

    $query = "SELECT a.*, 
              p.patient_number,
              CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
              CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
              d.specialization
              FROM appointments a
              JOIN patients p ON a.patient_id = p.patient_id
              JOIN users pu ON p.user_id = pu.user_id
              JOIN staff d ON a.doctor_id = d.staff_id
              JOIN users du ON d.user_id = du.user_id
              WHERE a.appointment_date = ?";

    $params = [$date];
    $types = "s";

    if ($status) {
        $query .= " AND a.status = ?";
        $params[] = $status;
        $types .= "s";
    }

    $query .= " ORDER BY a.appointment_time ASC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get appointment details by ID
 *
 * @param int $appointmentId Appointment ID
 * @return array|null Appointment details or null if not found
 */
function getAppointmentById($appointmentId)
{
    global $conn;

    $query = "SELECT a.*, 
              p.patient_number,
              CONCAT(pu.first_name, ' ', pu.last_name) as patient_name,
              pu.gender, pu.date_of_birth,
              CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
              d.specialization,
              CONCAT(cu.first_name, ' ', cu.last_name) as created_by_name
              FROM appointments a
              JOIN patients p ON a.patient_id = p.patient_id
              JOIN users pu ON p.user_id = pu.user_id
              JOIN staff d ON a.doctor_id = d.staff_id
              JOIN users du ON d.user_id = du.user_id
              JOIN users cu ON a.created_by = cu.user_id
              WHERE a.appointment_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $appointmentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}

/**
 * Get count of appointments
 *
 * @param string $status Optional status filter
 * @param string $date Optional date filter (format: YYYY-MM-DD)
 * @return int Number of appointments
 */
if (!function_exists('getAppointmentCount')) {
function getAppointmentCount($status = null, $date = null)
{
    global $conn;

    $query = "SELECT COUNT(*) as count FROM appointments WHERE 1=1";
    $params = [];
    $types = "";

    if ($status) {
        $query .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }

    if ($date) {
        $query .= " AND appointment_date = ?";
        $params[] = $date;
        $types .= "s";
    }

    $stmt = $conn->prepare($query);

    if ($params !== []) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['count'];
}
} // End function_exists check for getAppointmentCount

/**
 * Create a new appointment
 *
 * @param array $data Appointment data
 * @return int|false New appointment ID or false on failure
 */
function createAppointment($data)
{
    global $conn;

    $requiredFields = ['patient_id', 'doctor_id', 'appointment_date', 'appointment_time', 'appointment_type'];

    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            return false;
        }
    }

    $query = "INSERT INTO appointments (
                patient_id, doctor_id, appointment_date, appointment_time, 
                appointment_type, status, notes, created_by, created_at
              ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $status = 'scheduled';
    $notes = $data['notes'] ?? '';
    $createdBy = $data['created_by'] ?? 1; // Default to system admin
    $createdAt = date('Y-m-d H:i:s');

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "iissssis",
        $data['patient_id'],
        $data['doctor_id'],
        $data['appointment_date'],
        $data['appointment_time'],
        $data['appointment_type'],
        $status,
        $notes,
        $createdBy,
        $createdAt
    );

    if ($stmt->execute()) {
        return $stmt->insert_id;
    }

    return false;
}

/**
 * Update appointment status
 *
 * @param int $appointmentId Appointment ID
 * @param string $status New status
 * @return bool Success flag
 */
function updateAppointmentStatus($appointmentId, $status)
{
    global $conn;

    $validStatuses = ['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'];

    if (!in_array($status, $validStatuses)) {
        return false;
    }

    $query = "UPDATE appointments SET status = ? WHERE appointment_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $appointmentId);

    return $stmt->execute() && $stmt->affected_rows > 0;
}

/**
 * Update appointment details
 *
 * @param int $appointmentId Appointment ID
 * @param array $data Updated appointment data
 * @return bool Success flag
 */
function updateAppointment($appointmentId, $data)
{
    global $conn;

    // Build the query dynamically based on provided fields
    $query = "UPDATE appointments SET ";
    $params = [];
    $types = "";

    $allowedFields = [
        'doctor_id' => 'i',
        'appointment_date' => 's',
        'appointment_time' => 's',
        'appointment_type' => 's',
        'status' => 's',
        'notes' => 's'
    ];

    $updates = [];

    foreach ($allowedFields as $field => $type) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = $data[$field];
            $types .= $type;
        }
    }

    if ($updates === []) {
        return false; // No fields to update
    }

    $query .= implode(", ", $updates);
    $query .= " WHERE appointment_id = ?";
    $params[] = $appointmentId;
    $types .= "i";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);

    return $stmt->execute() && $stmt->affected_rows > 0;
}

/**
 * Get patient's upcoming appointments
 *
 * @param int $patientId Patient ID
 * @param int $limit Maximum number of appointments to return
 * @return array Array of upcoming appointments
 */
function getPatientUpcomingAppointments($patientId, $limit = 5)
{
    global $conn;

    $today = date('Y-m-d');

    $query = "SELECT a.*, 
              CONCAT(du.first_name, ' ', du.last_name) as doctor_name,
              d.specialization
              FROM appointments a
              JOIN staff d ON a.doctor_id = d.staff_id
              JOIN users du ON d.user_id = du.user_id
              WHERE a.patient_id = ? 
              AND (a.appointment_date > ? OR (a.appointment_date = ? AND a.status = 'scheduled'))
              ORDER BY a.appointment_date ASC, a.appointment_time ASC
              LIMIT ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("issi", $patientId, $today, $today, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get doctor's schedule
 *
 * @param int $doctorId Doctor's staff ID
 * @param string $dayOfWeek Optional day filter (e.g., 'Monday')
 * @return array Schedule data
 */
function getDoctorSchedule($doctorId, $dayOfWeek = null)
{
    global $conn;

    $query = "SELECT * FROM doctor_schedules WHERE doctor_id = ?";
    $params = [$doctorId];
    $types = "i";

    if ($dayOfWeek) {
        $query .= " AND day_of_week = ?";
        $params[] = $dayOfWeek;
        $types .= "s";
    }

    $query .= " ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($dayOfWeek) {
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Check if time slot is available for doctor
 *
 * @param int $doctorId Doctor's staff ID
 * @param string $date Appointment date
 * @param string $time Appointment time
 * @return bool True if slot is available
 */
function isTimeSlotAvailable($doctorId, $date, $time): bool
{
    global $conn;

    $query = "SELECT appointment_id FROM appointments 
              WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? 
              AND status IN ('scheduled', 'confirmed')";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $doctorId, $date, $time);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows === 0;
}

/**
 * Get appointment statistics
 *
 * @param string $startDate Start date (format: YYYY-MM-DD)
 * @param string $endDate End date (format: YYYY-MM-DD)
 * @param int $doctorId Optional doctor filter
 * @param string $type Optional appointment type filter
 * @return array Statistics data
 */
function getAppointmentStatistics($startDate, $endDate, $doctorId = null, $type = null)
{
    global $conn;

    $query = "SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) AS scheduled,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
                SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) AS no_show
              FROM appointments
              WHERE appointment_date BETWEEN ? AND ?";

    $params = [$startDate, $endDate];
    $types = "ss";

    if ($doctorId) {
        $query .= " AND doctor_id = ?";
        $params[] = $doctorId;
        $types .= "i";
    }

    if ($type) {
        $query .= " AND appointment_type = ?";
        $params[] = $type;
        $types .= "s";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

/**
 * Get doctor statistics
 *
 * @param int $doctorId Doctor's staff ID
 * @return array Statistics
 */
if (!function_exists('getDoctorStatistics')) {
function getDoctorStatistics($doctorId): array
{
    global $conn;

    $today = date('Y-m-d');
    $monthStart = date('Y-m-01');
    $monthEnd = date('Y-m-t');

    // Today's appointments
    $query = "SELECT COUNT(*) as count FROM appointments 
              WHERE doctor_id = ? AND appointment_date = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $doctorId, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $todayAppointments = $row['count'];

    // Pending consultations
    $query = "SELECT COUNT(*) as count FROM consultations 
              WHERE doctor_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $pendingConsultations = $row['count'];

    // Monthly consultations
    $query = "SELECT COUNT(*) as count FROM consultations 
              WHERE doctor_id = ? AND consultation_date BETWEEN ? AND ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $doctorId, $monthStart, $monthEnd);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $monthlyConsultations = $row['count'];

    // Total patients
    $query = "SELECT COUNT(DISTINCT patient_id) as count FROM appointments 
              WHERE doctor_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $totalPatients = $row['count'];

    // Pending lab results
    $query = "SELECT COUNT(*) as count FROM lab_test_items 
              WHERE doctor_id = ? AND status IN ('pending', 'processing')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $pendingLabResults = $row['count'];

    // Pending prescriptions
    $query = "SELECT COUNT(*) as count FROM prescriptions 
              WHERE doctor_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $pendingPrescriptions = $row['count'];

    return [
        'today_appointments' => $todayAppointments,
        'pending_consultations' => $pendingConsultations,
        'monthly_consultations' => $monthlyConsultations,
        'total_patients' => $totalPatients,
        'pending_lab_results' => $pendingLabResults,
        'pending_prescriptions' => $pendingPrescriptions
    ];
}
} // End function_exists check for getDoctorStatistics

/**
 * Get nurse statistics
 *
 * @param int $nurseId Nurse's staff ID
 * @return array Statistics
 */
if (!function_exists('getNurseStatistics')) {
function getNurseStatistics($nurseId): array
{
    global $conn;

    $today = date('Y-m-d');
    $monthStart = date('Y-m-01');
    $monthEnd = date('Y-m-t');

    // Today's appointments
    $query = "SELECT COUNT(*) as count FROM appointments 
              WHERE appointment_date = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $todayAppointments = $row['count'];

    // Pending vitals
    $query = "SELECT COUNT(*) as count FROM appointments 
              WHERE appointment_date = ? AND status = 'scheduled' 
              AND appointment_id NOT IN (SELECT appointment_id FROM vital_signs)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $pendingVitals = $row['count'];

    // Monthly vital signs
    $query = "SELECT COUNT(*) as count FROM vital_signs 
              WHERE measured_at BETWEEN ? AND ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $monthStart, $monthEnd);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $monthlyVitals = $row['count'];

    // Monthly triage
    $query = "SELECT COUNT(*) as count FROM triage 
              WHERE created_at BETWEEN ? AND ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $monthStart, $monthEnd);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $monthlyTriage = $row['count'];

    // Total patients
    $query = "SELECT COUNT(DISTINCT patient_id) as count FROM patients";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $totalPatients = $row['count'];

    // Check-ins today
    $query = "SELECT COUNT(*) as count FROM check_ins WHERE check_in_date = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $checkIns = $row['count'];

    return [
        'today_appointments' => $todayAppointments,
        'pending_vitals' => $pendingVitals,
        'monthly_vitals' => $monthlyVitals,
        'monthly_triage' => $monthlyTriage,
        'total_patients' => $totalPatients,
        'check_ins' => $checkIns
    ];
}
} // End function_exists check for getNurseStatistics
