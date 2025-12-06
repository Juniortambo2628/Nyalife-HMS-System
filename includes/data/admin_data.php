<?php
/**
 * Nyalife HMS - Admin Data Functions
 * 
 * Contains functions for retrieving and manipulating admin-related data.
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Get system statistics
 * 
 * @return array Array of system statistics
 */
function getSystemStatistics() {
    global $conn;
    
    $stats = [];
    
    // Get total patients
    $query = "SELECT COUNT(*) as count FROM patients";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $stats['total_patients'] = $row['count'];
    
    // Get total staff
    $query = "SELECT COUNT(*) as count FROM staff";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $stats['total_staff'] = $row['count'];
    
    // Get total doctors
    $query = "SELECT COUNT(*) as count FROM staff WHERE role = 'doctor'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $stats['total_doctors'] = $row['count'];
    
    // Get total nurses
    $query = "SELECT COUNT(*) as count FROM staff WHERE role = 'nurse'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $stats['total_nurses'] = $row['count'];
    
    // Get total lab technicians
    $query = "SELECT COUNT(*) as count FROM staff WHERE role = 'lab_technician'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $stats['total_lab_technicians'] = $row['count'];
    
    // Get total pharmacists
    $query = "SELECT COUNT(*) as count FROM staff WHERE role = 'pharmacist'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $stats['total_pharmacists'] = $row['count'];
    
    // Get total appointments today
    $today = date('Y-m-d');
    $query = "SELECT COUNT(*) as count FROM appointments WHERE appointment_date = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['today_appointments'] = $row['count'];
    
    // Get total consultations today
    $query = "SELECT COUNT(*) as count FROM consultations WHERE consultation_date = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['today_consultations'] = $row['count'];
    
    // Get total lab tests pending
    $query = "SELECT COUNT(*) as count FROM lab_test_items WHERE status = 'pending'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $stats['pending_lab_tests'] = $row['count'];
    
    // Get total prescriptions pending
    $query = "SELECT COUNT(*) as count FROM prescriptions WHERE status = 'pending'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $stats['pending_prescriptions'] = $row['count'];
    
    // Get total medications
    $query = "SELECT COUNT(*) as count FROM medications";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $stats['total_medications'] = $row['count'];
    
    // Get total departments
    $query = "SELECT COUNT(*) as count FROM departments";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $stats['total_departments'] = $row['count'];
    
    // Get total users
    $query = "SELECT COUNT(*) as count FROM users";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $stats['total_users'] = $row['count'];
    
    return $stats;
}

/**
 * Get recent users
 * 
 * @param int $limit Maximum number of users to return
 * @return array Array of recent users
 */
function getRecentUsers($limit = 5) {
    global $conn;
    
    $query = "SELECT u.*, CASE 
                WHEN p.patient_id IS NOT NULL THEN 'patient'
                WHEN s.staff_id IS NOT NULL THEN s.role
                ELSE 'unknown'
                END as role_name
                FROM users u
                LEFT JOIN patients p ON u.user_id = p.user_id
                LEFT JOIN staff s ON u.user_id = s.user_id
                ORDER BY u.created_at DESC
                LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get recent activities
 * 
 * @param int $limit Maximum number of activities to return
 * @return array Array of recent activities
 */
function getRecentActivities($limit = 10) {
    global $conn;
    
    $query = "SELECT a.*, u.first_name, u.last_name, u.profile_image
              FROM activity_logs a
              JOIN users u ON a.user_id = u.user_id
              ORDER BY a.created_at DESC
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get staff list
 * 
 * @param string $role Optional role filter
 * @param string $department Optional department filter
 * @param int $limit Maximum number of staff to return
 * @return array Array of staff
 */
function getStaffList($role = null, $department = null, $limit = 50) {
    global $conn;
    
    $query = "SELECT s.*, u.first_name, u.last_name, u.email, u.phone_number, 
              u.gender, u.date_of_birth, u.profile_image, d.name as department_name
              FROM staff s
              JOIN users u ON s.user_id = u.user_id
              LEFT JOIN departments d ON s.department_id = d.department_id
              WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if ($role) {
        $query .= " AND s.role = ?";
        $params[] = $role;
        $types .= "s";
    }
    
    if ($department) {
        $query .= " AND s.department_id = ?";
        $params[] = $department;
        $types .= "i";
    }
    
    $query .= " ORDER BY u.last_name, u.first_name ASC LIMIT ?";
    $params[] = $limit;
    $types .= "i";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get patient list
 * 
 * @param string $search Optional search term
 * @param int $limit Maximum number of patients to return
 * @return array Array of patients
 */
function getPatientList($search = null, $limit = 50) {
    global $conn;
    
    $query = "SELECT p.*, u.first_name, u.last_name, u.email, u.phone_number, 
              u.gender, u.date_of_birth, u.profile_image
              FROM patients p
              JOIN users u ON p.user_id = u.user_id
              WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if ($search) {
        $searchTerm = "%$search%";
        $query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR p.patient_number LIKE ? OR u.email LIKE ?)";
        $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        $types = "ssss";
    }
    
    $query .= " ORDER BY u.last_name, u.first_name ASC LIMIT ?";
    $params[] = $limit;
    $types .= "i";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get department list
 * 
 * @return array Array of departments
 */
function getDepartmentList() {
    global $conn;
    
    $query = "SELECT d.*, COUNT(s.staff_id) as staff_count
              FROM departments d
              LEFT JOIN staff s ON d.department_id = s.department_id
              GROUP BY d.department_id
              ORDER BY d.name ASC";
    
    $result = $conn->query($query);
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get notification count for admin
 * 
 * @return int Number of unread notifications
 */
function getAdminNotificationCount() {
    global $conn;
    
    $query = "SELECT COUNT(*) as count FROM notifications 
              WHERE recipient_type = 'admin' AND is_read = 0";
    
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    
    return $row['count'];
}

/**
 * Get admin notifications
 * 
 * @param int $limit Maximum number of notifications to return
 * @return array Array of notifications
 */
function getAdminNotifications($limit = 10) {
    global $conn;
    
    $query = "SELECT * FROM notifications 
              WHERE recipient_type = 'admin'
              ORDER BY created_at DESC
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Mark notification as read
 * 
 * @param int $notificationId Notification ID
 * @return bool Success flag
 */
function markNotificationRead($notificationId) {
    global $conn;
    
    $query = "UPDATE notifications SET is_read = 1 WHERE notification_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $notificationId);
    
    return $stmt->execute() && $stmt->affected_rows > 0;
}

/**
 * Create a new department
 * 
 * @param array $data Department data
 * @return int|false New department ID or false on failure
 */
function createDepartment($data) {
    global $conn;
    
    if (!isset($data['name']) || empty($data['name'])) {
        return false;
    }
    
    $query = "INSERT INTO departments (name, description) VALUES (?, ?)";
    $description = isset($data['description']) ? $data['description'] : '';
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $data['name'], $description);
    
    if ($stmt->execute()) {
        return $stmt->insert_id;
    }
    
    return false;
}

/**
 * Get appointment statistics for admin dashboard
 * 
 * @param string $period Period (today, week, month, year)
 * @return array Statistics data
 */
function getAdminAppointmentStatistics($period = 'today') {
    global $conn;
    
    // Define date range based on period
    $startDate = '';
    $endDate = date('Y-m-d');
    
    switch ($period) {
        case 'today':
            $startDate = date('Y-m-d');
            break;
        case 'week':
            $startDate = date('Y-m-d', strtotime('-1 week'));
            break;
        case 'month':
            $startDate = date('Y-m-d', strtotime('-1 month'));
            break;
        case 'year':
            $startDate = date('Y-m-d', strtotime('-1 year'));
            break;
        default:
            $startDate = date('Y-m-d');
    }
    
    $query = "SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) AS scheduled,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
                SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) AS no_show
            FROM appointments
            WHERE appointment_date BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return [
        'total' => 0,
        'scheduled' => 0,
        'confirmed' => 0,
        'completed' => 0,
        'cancelled' => 0,
        'no_show' => 0
    ];
}

/**
 * Check system notifications for the admin
 * 
 * @return array Array of system notifications
 */
function checkSystemNotifications() {
    global $conn;
    
    $notifications = [];
    
    // Check for low inventory items
    $query = "SELECT COUNT(*) as count FROM inventory WHERE quantity <= reorder_level";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $notifications[] = [
            'type' => 'inventory',
            'message' => $row['count'] . ' inventory items are low in stock and need to be reordered.',
            'count' => $row['count']
        ];
    }
    
    // Check for pending lab results over 48 hours
    $twoDaysAgo = date('Y-m-d H:i:s', strtotime('-2 days'));
    $query = "SELECT COUNT(*) as count FROM lab_test_items 
              WHERE status = 'pending' AND created_at < ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $twoDaysAgo);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $notifications[] = [
            'type' => 'lab',
            'message' => $row['count'] . ' lab tests have been pending for more than 48 hours.',
            'count' => $row['count']
        ];
    }
    
    // Check for unclaimed prescriptions over 7 days
    $sevenDaysAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
    $query = "SELECT COUNT(*) as count FROM prescriptions 
              WHERE status = 'pending' AND created_at < ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $sevenDaysAgo);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $notifications[] = [
            'type' => 'prescription',
            'message' => $row['count'] . ' prescriptions have been unclaimed for more than 7 days.',
            'count' => $row['count']
        ];
    }
    
    return $notifications;
} 