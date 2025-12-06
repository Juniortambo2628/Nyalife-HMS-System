<?php
/**
 * Nyalife HMS - Dashboard Helper
 *
 * Contains helper functions for dashboard functionality and statistics
 */

class DashboardHelper
{
    /**
     * Get dashboard statistics based on user role
     *
     * @param string $userRole User's role
     * @param int $userId User ID
     * @return array Dashboard statistics
     */
    public static function getDashboardStats($userRole, $userId = null)
    {
        return match ($userRole) {
            'admin' => self::getAdminDashboardStats(),
            'doctor' => self::getDoctorDashboardStats($userId),
            'nurse' => self::getNurseDashboardStats($userId),
            'lab_technician' => self::getLabTechnicianDashboardStats($userId),
            'pharmacist' => self::getPharmacistDashboardStats($userId),
            'patient' => self::getPatientDashboardStats($userId),
            default => self::getDefaultDashboardStats(),
        };
    }

    /**
     * Get admin dashboard statistics
     *
     * @return array Admin dashboard stats
     */
    private static function getAdminDashboardStats(): array
    {
        global $conn;

        $stats = [];

        // System overview
        $stats['total_patients'] = self::getCount('patients');
        $stats['total_doctors'] = self::getCount('staff', "role = 'doctor'");
        $stats['total_nurses'] = self::getCount('staff', "role = 'nurse'");
        $stats['total_departments'] = self::getCount('departments');

        // Today's activities
        $today = date('Y-m-d');
        $stats['today_appointments'] = self::getCount('appointments', "appointment_date = '$today'");
        $stats['today_consultations'] = self::getCount('consultations', "consultation_date = '$today'");
        $stats['today_follow_ups'] = self::getCount('follow_ups', "follow_up_date = '$today'");

        // Financial overview
        $stats['pending_invoices'] = self::getCount('invoices', "status IN ('pending', 'partially_paid')");
        $stats['total_revenue'] = self::getSum('payments', 'amount', "status = 'completed'");
        $stats['monthly_revenue'] = self::getSum('payments', 'amount', "status = 'completed' AND MONTH(created_at) = MONTH(CURDATE())");

        // Lab and pharmacy
        $stats['pending_lab_tests'] = self::getCount('lab_test_items', "status = 'pending'");
        $stats['pending_prescriptions'] = self::getCount('prescriptions', "status = 'pending'");

        return $stats;
    }

    /**
     * Get doctor dashboard statistics
     *
     * @param int $userId Doctor's user ID
     * @return array Doctor dashboard stats
     */
    private static function getDoctorDashboardStats($userId): array
    {
        global $conn;

        $stats = [];

        // Get doctor's staff ID
        $doctorId = self::getStaffIdByUserId($userId);

        if ($doctorId) {
            $today = date('Y-m-d');

            // Doctor-specific stats
            $stats['today_appointments'] = self::getCount('appointments', "doctor_id = $doctorId AND appointment_date = '$today'");
            $stats['pending_consultations'] = self::getCount('consultations', "doctor_id = $doctorId AND status = 'pending'");
            $stats['upcoming_follow_ups'] = self::getCount('follow_ups', "doctor_id = $doctorId AND follow_up_date >= '$today'");
            $stats['total_patients'] = self::getCount('appointments', "doctor_id = $doctorId", 'DISTINCT patient_id');

            // Monthly stats
            $monthStart = date('Y-m-01');
            $monthEnd = date('Y-m-t');
            $stats['monthly_consultations'] = self::getCount('consultations', "doctor_id = $doctorId AND consultation_date BETWEEN '$monthStart' AND '$monthEnd'");
            $stats['monthly_appointments'] = self::getCount('appointments', "doctor_id = $doctorId AND appointment_date BETWEEN '$monthStart' AND '$monthEnd'");
        }

        return $stats;
    }

    /**
     * Get nurse dashboard statistics
     *
     * @return array Nurse dashboard stats
     */
    private static function getNurseDashboardStats(): array
    {
        global $conn;
        $stats = [];
        $today = date('Y-m-d');
        // Nurse-specific stats
        $stats['today_appointments'] = self::getCount('appointments', "appointment_date = '$today'");
        $stats['pending_vitals'] = self::getCount('appointments', "appointment_date = '$today' AND status = 'scheduled'");
        $stats['upcoming_follow_ups'] = self::getCount('follow_ups', "follow_up_date >= '$today'");
        $stats['total_patients'] = self::getCount('patients');
        // Monthly stats
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $stats['monthly_vitals'] = self::getCount('vital_signs', "measured_at BETWEEN '$monthStart' AND '$monthEnd'");
        return $stats;
    }

    /**
     * Get lab technician dashboard statistics
     *
     * @return array Lab technician dashboard stats
     */
    private static function getLabTechnicianDashboardStats(): array
    {
        global $conn;
        $stats = [];
        $today = date('Y-m-d');
        // Lab-specific stats
        $stats['pending_tests'] = self::getCount('lab_test_items', "status = 'pending'");
        $stats['processing_tests'] = self::getCount('lab_test_items', "status = 'processing'");
        $stats['completed_today'] = self::getCount('lab_test_items', "status = 'completed' AND DATE(performed_at) = '$today'");
        $stats['urgent_pending'] = self::getCount('lab_test_items', "status = 'pending' AND priority = 'urgent'");
        // Weekly stats
        $weekStart = date('Y-m-d', strtotime('-1 week'));
        $stats['completed_week'] = self::getCount('lab_test_items', "status = 'completed' AND performed_at >= '$weekStart'");
        return $stats;
    }

    /**
     * Get pharmacist dashboard statistics
     *
     * @return array Pharmacist dashboard stats
     */
    private static function getPharmacistDashboardStats(): array
    {
        global $conn;
        $stats = [];
        $today = date('Y-m-d');
        // Pharmacy-specific stats
        $stats['pending_prescriptions'] = self::getCount('prescriptions', "status = 'pending'");
        $stats['completed_today'] = self::getCount('prescriptions', "status = 'completed' AND DATE(dispensed_at) = '$today'");
        $stats['low_stock_medicines'] = self::getCount('medications', "quantity <= reorder_level");
        $stats['expiring_medicines'] = self::getCount('medication_batches', "expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
        // Monthly stats
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $stats['monthly_dispensed'] = self::getCount('prescriptions', "status = 'completed' AND dispensed_at BETWEEN '$monthStart' AND '$monthEnd'");
        return $stats;
    }

    /**
     * Get patient dashboard statistics
     *
     * @param int $userId Patient's user ID
     * @return array Patient dashboard stats
     */
    private static function getPatientDashboardStats($userId): array
    {
        global $conn;

        $stats = [];

        // Get patient ID
        $patientId = self::getPatientIdByUserId($userId);

        if ($patientId) {
            $today = date('Y-m-d');

            // Patient-specific stats
            $stats['upcoming_appointments'] = self::getCount('appointments', "patient_id = $patientId AND appointment_date >= '$today'");
            $stats['total_consultations'] = self::getCount('consultations', "patient_id = $patientId");
            $stats['active_prescriptions'] = self::getCount('prescriptions', "patient_id = $patientId AND status = 'active'");
            $stats['pending_follow_ups'] = self::getCount('follow_ups', "patient_id = $patientId AND status = 'pending'");
        }

        return $stats;
    }

    /**
     * Get default dashboard statistics
     *
     * @return array Default dashboard stats
     */
    private static function getDefaultDashboardStats(): array
    {
        return [
            // Basic system stats
            'total_patients' => self::getCount('patients'),
            'today_appointments' => self::getCount('appointments', "appointment_date = '" . date('Y-m-d') . "'"),
        ];
    }

    /**
     * Get count from database table
     *
     * @param string $table Table name
     * @param string $where WHERE clause (optional)
     * @param string $column Column to count (default: *)
     * @return int Count
     */
    private static function getCount(string $table, string $where = '', string $column = '*'): int
    {
        global $conn;

        $sql = "SELECT COUNT($column) as count FROM $table";
        if ($where !== '' && $where !== '0') {
            $sql .= " WHERE $where";
        }

        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (int)$row['count'];
        }

        return 0;
    }

    /**
     * Get sum from database table
     *
     * @param string $table Table name
     * @param string $column Column to sum
     * @param string $where WHERE clause (optional)
     * @return float Sum
     */
    private static function getSum(string $table, string $column, string $where = ''): float
    {
        global $conn;

        $sql = "SELECT SUM($column) as total FROM $table";
        if ($where !== '' && $where !== '0') {
            $sql .= " WHERE $where";
        }

        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (float)$row['total'];
        }

        return 0.0;
    }

    /**
     * Get staff ID by user ID
     *
     * @param int $userId User ID
     * @return int|null Staff ID or null
     */
    private static function getStaffIdByUserId($userId): ?int
    {
        global $conn;

        $sql = "SELECT staff_id FROM staff WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (int)$row['staff_id'];
        }

        return null;
    }

    /**
     * Get patient ID by user ID
     *
     * @param int $userId User ID
     * @return int|null Patient ID or null
     */
    private static function getPatientIdByUserId($userId): ?int
    {
        global $conn;

        $sql = "SELECT patient_id FROM patients WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (int)$row['patient_id'];
        }

        return null;
    }

    /**
     * Get recent activities for dashboard
     *
     * @param string $userRole User's role
     * @param int $userId User ID
     * @param int $limit Number of activities to return
     * @return array Recent activities
     */
    public static function getRecentActivities($userRole, $userId = null, $limit = 10)
    {
        global $conn;

        $activities = [];

        return match ($userRole) {
            'admin' => self::getAdminRecentActivities($limit),
            'doctor' => self::getDoctorRecentActivities($userId, $limit),
            'nurse' => self::getNurseRecentActivities($userId, $limit),
            'patient' => self::getPatientRecentActivities($userId, $limit),
            default => $activities,
        };
    }

    /**
     * Get admin recent activities
     *
     * @param int $limit Number of activities
     * @return array Activities
     */
    private static function getAdminRecentActivities($limit): array
    {
        global $conn;

        $activities = [];

        // Get recent appointments
        $sql = "SELECT 'appointment' as type, a.appointment_id as id, 
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                a.appointment_date, a.status, a.created_at
                FROM appointments a
                JOIN patients pa ON a.patient_id = pa.patient_id
                JOIN users p ON pa.user_id = p.user_id
                ORDER BY a.created_at DESC
                LIMIT ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }

        return $activities;
    }

    /**
     * Get doctor recent activities
     *
     * @param int $userId User ID
     * @param int $limit Number of activities
     * @return array Activities
     */
    private static function getDoctorRecentActivities($userId, $limit): array
    {
        global $conn;

        $activities = [];
        $doctorId = self::getStaffIdByUserId($userId);

        if ($doctorId) {
            // Get recent consultations
            $sql = "SELECT 'consultation' as type, c.consultation_id as id,
                    CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                    c.consultation_date, c.status, c.created_at
                    FROM consultations c
                    JOIN patients pa ON c.patient_id = pa.patient_id
                    JOIN users p ON pa.user_id = p.user_id
                    WHERE c.doctor_id = ?
                    ORDER BY c.created_at DESC
                    LIMIT ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $doctorId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $activities[] = $row;
            }
        }

        return $activities;
    }

    /**
     * Get nurse recent activities
     *
     * @param int $limit Number of activities
     * @return array Activities
     */
    private static function getNurseRecentActivities($limit): array
    {
        global $conn;

        $activities = [];

        // Get recent vital signs
        $sql = "SELECT 'vital_signs' as type, v.vital_sign_id as id,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                v.measured_at, v.created_at
                FROM vital_signs v
                JOIN patients pa ON v.patient_id = pa.patient_id
                JOIN users p ON pa.user_id = p.user_id
                ORDER BY v.created_at DESC
                LIMIT ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }

        return $activities;
    }

    /**
     * Get patient recent activities
     *
     * @param int $userId User ID
     * @param int $limit Number of activities
     * @return array Activities
     */
    private static function getPatientRecentActivities($userId, $limit): array
    {
        global $conn;

        $activities = [];
        $patientId = self::getPatientIdByUserId($userId);

        if ($patientId) {
            // Get recent appointments
            $sql = "SELECT 'appointment' as type, a.appointment_id as id,
                    CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                    a.appointment_date, a.status, a.created_at
                    FROM appointments a
                    JOIN staff s ON a.doctor_id = s.staff_id
                    JOIN users d ON s.user_id = d.user_id
                    WHERE a.patient_id = ?
                    ORDER BY a.created_at DESC
                    LIMIT ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $patientId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $activities[] = $row;
            }
        }

        return $activities;
    }
}
