<?php
/**
 * Nyalife HMS - Report Utilities
 * 
 * This file provides standardized functions for generating reports.
 */

require_once __DIR__ . '/db_utils.php';
require_once __DIR__ . '/date_utils.php';

/**
 * Generate date range filter for reports
 * 
 * @param string $start_date Start date (defaults to first day of current month)
 * @param string $end_date End date (defaults to last day of current month)
 * @return array Date range parameters
 */
function getReportDateRange($start_date = null, $end_date = null) {
    if (!$start_date) {
        $start_date = date('Y-m-01'); // First day of current month
    }
    if (!$end_date) {
        $end_date = date('Y-m-t'); // Last day of current month
    }
    
    return [
        'start_date' => $start_date,
        'end_date' => $end_date
    ];
}

/**
 * Generate patient statistics report
 * 
 * @param array $filters Report filters
 * @return array Report data
 */
function generatePatientStatisticsReport($filters = []) {
    $dateRange = getReportDateRange(
        $filters['start_date'] ?? null,
        $filters['end_date'] ?? null
    );
    
    $result = [
        'date_range' => $dateRange,
        'total_patients' => dbSelectValue(
            "SELECT COUNT(*) FROM patients"
        ),
        'new_patients' => dbSelectValue(
            "SELECT COUNT(*) FROM patients WHERE DATE(registration_date) BETWEEN ? AND ?",
            [$dateRange['start_date'], $dateRange['end_date']]
        ),
        'active_patients' => dbSelectValue(
            "SELECT COUNT(DISTINCT p.patient_id) 
             FROM patients p
             JOIN appointments a ON p.patient_id = a.patient_id
             WHERE DATE(a.appointment_date) BETWEEN ? AND ?",
            [$dateRange['start_date'], $dateRange['end_date']]
        ),
        'demographics' => dbSelect(
            "SELECT 
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 18 THEN 'Under 18'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 18 AND 30 THEN '18-30'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 31 AND 45 THEN '31-45'
                    WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 46 AND 60 THEN '46-60'
                    ELSE 'Over 60'
                END as age_group,
                COUNT(*) as count
             FROM patients
             GROUP BY age_group
             ORDER BY 
                CASE age_group
                    WHEN 'Under 18' THEN 1
                    WHEN '18-30' THEN 2
                    WHEN '31-45' THEN 3
                    WHEN '46-60' THEN 4
                    WHEN 'Over 60' THEN 5
                END"
        ),
        'gender_distribution' => dbSelect(
            "SELECT gender, COUNT(*) as count
             FROM patients
             GROUP BY gender"
        )
    ];
    
    return $result;
}

/**
 * Generate appointment statistics report
 * 
 * @param array $filters Report filters
 * @return array Report data
 */
function generateAppointmentStatisticsReport($filters = []) {
    $dateRange = getReportDateRange(
        $filters['start_date'] ?? null,
        $filters['end_date'] ?? null
    );
    
    $result = [
        'date_range' => $dateRange,
        'total_appointments' => dbSelectValue(
            "SELECT COUNT(*) 
             FROM appointments 
             WHERE DATE(appointment_date) BETWEEN ? AND ?",
            [$dateRange['start_date'], $dateRange['end_date']]
        ),
        'status_breakdown' => dbSelect(
            "SELECT status, COUNT(*) as count
             FROM appointments
             WHERE DATE(appointment_date) BETWEEN ? AND ?
             GROUP BY status",
            [$dateRange['start_date'], $dateRange['end_date']]
        ),
        'daily_appointments' => dbSelect(
            "SELECT DATE(appointment_date) as date, COUNT(*) as count
             FROM appointments
             WHERE DATE(appointment_date) BETWEEN ? AND ?
             GROUP BY DATE(appointment_date)
             ORDER BY DATE(appointment_date)",
            [$dateRange['start_date'], $dateRange['end_date']]
        ),
        'department_breakdown' => dbSelect(
            "SELECT d.department_name, COUNT(*) as count
             FROM appointments a
             JOIN doctors doc ON a.doctor_id = doc.doctor_id
             JOIN departments d ON doc.department_id = d.department_id
             WHERE DATE(a.appointment_date) BETWEEN ? AND ?
             GROUP BY d.department_id
             ORDER BY count DESC",
            [$dateRange['start_date'], $dateRange['end_date']]
        )
    ];
    
    return $result;
}

/**
 * Generate doctor performance report
 * 
 * @param array $filters Report filters
 * @return array Report data
 */
function generateDoctorPerformanceReport($filters = []) {
    $dateRange = getReportDateRange(
        $filters['start_date'] ?? null,
        $filters['end_date'] ?? null
    );
    
    $doctorId = $filters['doctor_id'] ?? null;
    
    $params = [$dateRange['start_date'], $dateRange['end_date']];
    $doctorCondition = '';
    
    if ($doctorId) {
        $doctorCondition = "AND d.doctor_id = ?";
        $params[] = $doctorId;
    }
    
    $result = [
        'date_range' => $dateRange,
        'doctors' => dbSelect(
            "SELECT 
                d.doctor_id,
                u.first_name,
                u.last_name,
                dep.department_name,
                COUNT(a.appointment_id) as total_appointments,
                SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) as completed_appointments,
                SUM(CASE WHEN a.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_appointments,
                AVG(TIMESTAMPDIFF(MINUTE, a.appointment_time, c.created_at)) as avg_consultation_time
             FROM doctors d
             JOIN users u ON d.user_id = u.user_id
             JOIN departments dep ON d.department_id = dep.department_id
             LEFT JOIN appointments a ON d.doctor_id = a.doctor_id AND DATE(a.appointment_date) BETWEEN ? AND ?
             LEFT JOIN consultations c ON a.appointment_id = c.appointment_id
             WHERE 1=1 $doctorCondition
             GROUP BY d.doctor_id, u.first_name, u.last_name, dep.department_name
             ORDER BY total_appointments DESC",
            $params
        )
    ];
    
    return $result;
}

/**
 * Generate pharmacy inventory report
 * 
 * @param array $filters Report filters
 * @return array Report data
 */
function generatePharmacyInventoryReport($filters = []) {
    $result = [
        'medications' => dbSelect(
            "SELECT 
                m.medication_id,
                m.medication_name,
                m.generic_name,
                m.category,
                m.stock_quantity,
                m.reorder_level,
                m.unit_price,
                CASE 
                    WHEN m.stock_quantity <= m.reorder_level THEN 'low'
                    WHEN m.stock_quantity > m.reorder_level * 2 THEN 'high'
                    ELSE 'normal'
                END as stock_status
             FROM medications m
             ORDER BY 
                CASE 
                    WHEN m.stock_quantity <= m.reorder_level THEN 1
                    ELSE 2
                END,
                m.category,
                m.medication_name"
        ),
        'low_stock_count' => dbSelectValue(
            "SELECT COUNT(*) 
             FROM medications 
             WHERE stock_quantity <= reorder_level"
        ),
        'expired_count' => dbSelectValue(
            "SELECT COUNT(*) 
             FROM medications 
             WHERE expiry_date <= CURDATE()"
        ),
        'total_value' => dbSelectValue(
            "SELECT SUM(stock_quantity * unit_price) 
             FROM medications"
        ),
        'category_breakdown' => dbSelect(
            "SELECT 
                category, 
                COUNT(*) as count,
                SUM(stock_quantity) as total_units,
                SUM(stock_quantity * unit_price) as total_value
             FROM medications
             GROUP BY category
             ORDER BY total_value DESC"
        )
    ];
    
    return $result;
}

/**
 * Generate financial report
 * 
 * @param array $filters Report filters
 * @return array Report data
 */
function generateFinancialReport($filters = []) {
    $dateRange = getReportDateRange(
        $filters['start_date'] ?? null,
        $filters['end_date'] ?? null
    );
    
    $result = [
        'date_range' => $dateRange,
        'total_revenue' => dbSelectValue(
            "SELECT SUM(amount) 
             FROM payments 
             WHERE payment_status = 'completed' 
             AND DATE(payment_date) BETWEEN ? AND ?",
            [$dateRange['start_date'], $dateRange['end_date']]
        ),
        'payment_methods' => dbSelect(
            "SELECT 
                payment_method, 
                COUNT(*) as count,
                SUM(amount) as total_amount
             FROM payments
             WHERE payment_status = 'completed' 
             AND DATE(payment_date) BETWEEN ? AND ?
             GROUP BY payment_method",
            [$dateRange['start_date'], $dateRange['end_date']]
        ),
        'service_categories' => dbSelect(
            "SELECT 
                service_category, 
                COUNT(*) as count,
                SUM(amount) as total_amount
             FROM payments
             WHERE payment_status = 'completed' 
             AND DATE(payment_date) BETWEEN ? AND ?
             GROUP BY service_category",
            [$dateRange['start_date'], $dateRange['end_date']]
        ),
        'daily_revenue' => dbSelect(
            "SELECT 
                DATE(payment_date) as date, 
                SUM(amount) as total_amount
             FROM payments
             WHERE payment_status = 'completed' 
             AND DATE(payment_date) BETWEEN ? AND ?
             GROUP BY DATE(payment_date)
             ORDER BY DATE(payment_date)",
            [$dateRange['start_date'], $dateRange['end_date']]
        ),
        'outstanding_payments' => dbSelect(
            "SELECT 
                SUM(amount) as total_amount,
                COUNT(*) as count
             FROM payments
             WHERE payment_status = 'pending' 
             AND DATE(payment_date) BETWEEN ? AND ?",
            [$dateRange['start_date'], $dateRange['end_date']]
        )[0] ?? ['total_amount' => 0, 'count' => 0]
    ];
    
    return $result;
}

/**
 * Generate system activity report
 * 
 * @param array $filters Report filters
 * @return array Report data
 */
function generateSystemActivityReport($filters = []) {
    $dateRange = getReportDateRange(
        $filters['start_date'] ?? null,
        $filters['end_date'] ?? null
    );
    
    $result = [
        'date_range' => $dateRange,
        'user_logins' => dbSelect(
            "SELECT 
                u.username,
                r.role_name,
                COUNT(*) as login_count
             FROM audit_logs al
             JOIN users u ON al.user_id = u.user_id
             JOIN roles r ON u.role_id = r.role_id
             WHERE al.action = 'login' 
             AND DATE(al.created_at) BETWEEN ? AND ?
             GROUP BY u.user_id, u.username, r.role_name
             ORDER BY login_count DESC
             LIMIT 20",
            [$dateRange['start_date'], $dateRange['end_date']]
        ),
        'activity_by_module' => dbSelect(
            "SELECT 
                entity_type as module,
                COUNT(*) as action_count
             FROM audit_logs
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY entity_type
             ORDER BY action_count DESC",
            [$dateRange['start_date'], $dateRange['end_date']]
        ),
        'activity_by_action' => dbSelect(
            "SELECT 
                action,
                COUNT(*) as count
             FROM audit_logs
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY action
             ORDER BY count DESC",
            [$dateRange['start_date'], $dateRange['end_date']]
        ),
        'daily_activity' => dbSelect(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as action_count
             FROM audit_logs
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY DATE(created_at)",
            [$dateRange['start_date'], $dateRange['end_date']]
        )
    ];
    
    return $result;
}

/**
 * Format report data as CSV
 * 
 * @param array $data Report data to format
 * @param array $headers CSV headers
 * @return string CSV formatted data
 */
function formatReportAsCsv($data, $headers) {
    $output = fopen('php://temp', 'r+');
    
    // Add headers
    fputcsv($output, $headers);
    
    // Add data rows
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    
    return $csv;
}

/**
 * Export report data as CSV file
 * 
 * @param array $data Report data to export
 * @param array $headers CSV headers
 * @param string $filename Filename for the CSV
 * @return void
 */
function exportReportAsCsv($data, $headers, $filename) {
    $csv = formatReportAsCsv($data, $headers);
    
    // Set headers for download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Output CSV
    echo $csv;
    exit;
}
?> 