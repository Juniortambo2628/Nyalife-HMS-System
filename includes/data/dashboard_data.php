<?php
/**
 * Nyalife HMS - Dashboard Data Access Layer
 *
 * This file provides standardized functions for dashboard data operations.
 */

require_once __DIR__ . '/../db_utils.php';
require_once __DIR__ . '/../date_utils.php';

/**
 * Get user statistics summary
 *
 * @param int $userId User ID
 * @return array User statistics
 */
function getUserStatisticsSummary($userId): array
{
    return [
        'last_login' => dbSelectValue(
            "SELECT login_time FROM login_logs 
             WHERE user_id = ? 
             ORDER BY login_time DESC LIMIT 1, 1", // Get second-to-last login
            [$userId]
        ),
        'total_logins' => dbSelectValue(
            "SELECT COUNT(*) FROM login_logs WHERE user_id = ?",
            [$userId]
        ),
        'notifications' => dbSelectValue(
            "SELECT COUNT(*) FROM notifications 
             WHERE user_id = ? AND is_read = 0",
            [$userId]
        )
    ];
}

/**
 * Get system statistics
 *
 * @return array System statistics
 */
function getSystemStatistics(): array
{
    return [
        'total_users' => dbSelectValue("SELECT COUNT(*) FROM users WHERE is_active = 1"),
        'total_patients' => dbSelectValue("SELECT COUNT(*) FROM patients"),
        'total_doctors' => dbSelectValue("SELECT COUNT(*) FROM doctors"),
        'total_appointments' => dbSelectValue(
            "SELECT COUNT(*) FROM appointments WHERE DATE(appointment_date) = CURDATE()"
        ),
        'pending_appointments' => dbSelectValue(
            "SELECT COUNT(*) FROM appointments 
             WHERE status = 'scheduled' AND DATE(appointment_date) = CURDATE()"
        )
    ];
}

/**
 * Get generic chart data for a time period
 *
 * @param string $tableName Table to query
 * @param string $dateColumn Date column to group by
 * @param string $groupBy Period to group by (day, week, month)
 * @param string $whereClause Optional WHERE clause
 * @param array $whereParams Optional WHERE parameters
 * @param string $startDate Start date (YYYY-MM-DD)
 * @param string $endDate End date (YYYY-MM-DD)
 * @return array Chart data with labels and values
 */
function getChartDataByPeriod(
    $tableName,
    $dateColumn,
    $groupBy = 'day',
    $whereClause = '',
    $whereParams = [],
    $startDate = null,
    $endDate = null
): array {
    if (!$startDate) {
        // Default to last 30 days
        $startDate = date('Y-m-d', strtotime('-30 days'));
    }

    if (!$endDate) {
        $endDate = date('Y-m-d');
    }

    $groupFormat = '%Y-%m-%d'; // Default to daily
    $labelFormat = 'M d'; // Default label format

    if ($groupBy === 'week') {
        $groupFormat = '%x-W%v'; // ISO year and week number
        $labelFormat = '\WW'; // Week label format
    } elseif ($groupBy === 'month') {
        $groupFormat = '%Y-%m'; // Year and month
        $labelFormat = 'M Y'; // Month label format
    }

    $sql = "SELECT 
                DATE_FORMAT($dateColumn, '$groupFormat') as period,
                COUNT(*) as count
            FROM $tableName
            WHERE $dateColumn BETWEEN ? AND ?";

    $params = [$startDate, $endDate];

    if ($whereClause) {
        $sql .= " AND $whereClause";
        $params = array_merge($params, $whereParams);
    }

    $sql .= " GROUP BY period ORDER BY period";

    $data = dbSelect($sql, $params);

    // Format for charts
    $labels = [];
    $values = [];

    foreach ($data as $row) {
        // Convert period to a date for formatting
        if ($groupBy === 'week') {
            // Parse "YYYY-WNN" format
            [$year, $week] = explode('-W', (string) $row['period']);
            $date = new DateTime();
            $date->setISODate($year, $week);
        } elseif ($groupBy === 'month') {
            // Parse "YYYY-MM" format
            $date = DateTime::createFromFormat('Y-m', $row['period']);
        } else {
            // Parse "YYYY-MM-DD" format
            $date = DateTime::createFromFormat('Y-m-d', $row['period']);
        }

        $labels[] = $date->format($labelFormat);
        $values[] = (int)$row['count'];
    }

    return [
        'labels' => $labels,
        'values' => $values
    ];
}

/**
 * Get calendar events for a user
 *
 * @param int $userId User ID
 * @param string $startDate Start date (YYYY-MM-DD)
 * @param string $endDate End date (YYYY-MM-DD)
 * @return array Calendar events
 */
function getUserCalendarEvents($userId, $startDate = null, $endDate = null): array
{
    if (!$startDate) {
        $startDate = date('Y-m-d');
    }

    if (!$endDate) {
        $endDate = date('Y-m-d', strtotime('+30 days'));
    }

    // Get user role
    $user = dbSelectOne(
        "SELECT u.*, r.role_name 
         FROM users u 
         JOIN roles r ON u.role_id = r.role_id 
         WHERE u.user_id = ?",
        [$userId]
    );

    $events = [];

    if (!$user) {
        return $events;
    }

    // Get appropriate events based on user role
    switch ($user['role_name']) {
        case 'doctor':
            $doctorId = getStaffIdFromUserId($userId);

            if ($doctorId) {
                $events = dbSelect(
                    "SELECT 
                        a.appointment_id as id,
                        CONCAT(u.first_name, ' ', u.last_name) as title,
                        a.appointment_date as start_date,
                        a.appointment_time as start_time,
                        a.duration,
                        a.status,
                        'appointment' as event_type
                     FROM appointments a
                     JOIN patients p ON a.patient_id = p.patient_id
                     JOIN users u ON p.user_id = u.user_id
                     WHERE a.doctor_id = ?
                     AND DATE(a.appointment_date) BETWEEN ? AND ?",
                    [$doctorId, $startDate, $endDate]
                );
            }
            break;

        case 'patient':
            $patientId = dbSelectValue(
                "SELECT patient_id FROM patients WHERE user_id = ?",
                [$userId]
            );

            if ($patientId) {
                $events = dbSelect(
                    "SELECT 
                        a.appointment_id as id,
                        CONCAT('Dr. ', u.first_name, ' ', u.last_name) as title,
                        a.appointment_date as start_date,
                        a.appointment_time as start_time,
                        a.duration,
                        a.status,
                        'appointment' as event_type
                     FROM appointments a
                     JOIN users u ON a.doctor_id = u.user_id
                     WHERE a.patient_id = ?
                     AND DATE(a.appointment_date) BETWEEN ? AND ?",
                    [$patientId, $startDate, $endDate]
                );
            }
            break;

        default:
            // Other staff may have different events
            $events = dbSelect(
                "SELECT 
                    e.event_id as id,
                    e.title,
                    e.event_date as start_date,
                    e.event_time as start_time,
                    e.duration,
                    e.event_type
                 FROM events e
                 WHERE e.user_id = ?
                 AND DATE(e.event_date) BETWEEN ? AND ?",
                [$userId, $startDate, $endDate]
            );
            break;
    }

    // Format events for calendar display
    $formattedEvents = [];

    foreach ($events as $event) {
        $startDateTime = $event['start_date'] . ' ' . $event['start_time'];
        $endDateTime = null;

        if (isset($event['duration']) && $event['duration']) {
            $start = new DateTime($startDateTime);
            $end = clone $start;
            $end->add(new DateInterval('PT' . (int)$event['duration'] . 'M'));
            $endDateTime = $end->format('Y-m-d H:i:s');
        }

        $eventColor = '#3788d8'; // Default color

        // Set color based on event type or status
        if (isset($event['event_type'])) {
            switch ($event['event_type']) {
                case 'appointment':
                    if (isset($event['status'])) {
                        switch ($event['status']) {
                            case 'scheduled':
                                $eventColor = '#4e73df'; // Primary blue
                                break;
                            case 'completed':
                                $eventColor = '#1cc88a'; // Success green
                                break;
                            case 'cancelled':
                                $eventColor = '#e74a3b'; // Danger red
                                break;
                            case 'confirmed':
                                $eventColor = '#f6c23e'; // Warning yellow
                                break;
                        }
                    }
                    break;
                case 'meeting':
                    $eventColor = '#6f42c1'; // Purple
                    break;
                case 'reminder':
                    $eventColor = '#36b9cc'; // Info teal
                    break;
            }
        }

        $formattedEvents[] = [
            'id' => $event['id'],
            'title' => $event['title'],
            'start' => $startDateTime,
            'end' => $endDateTime,
            'color' => $eventColor,
            'event_type' => $event['event_type'] ?? 'generic',
            'status' => $event['status'] ?? null
        ];
    }

    return $formattedEvents;
}
