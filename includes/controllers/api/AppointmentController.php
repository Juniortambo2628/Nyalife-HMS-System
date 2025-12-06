<?php

/**
 * Nyalife HMS - Appointment API Controller
 *
 * This controller handles all appointment-related API requests.
 */

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../../data/appointment_data.php';

class AppointmentController extends ApiController
{
    protected \AppointmentModel $appointmentModel;

    protected \AuditLogger $auditLogger;

    protected \StaffModel $staffModel;

    protected \PatientModel $patientModel;

    // Status constants
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NOSHOW = 'no_show';

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();

        // Initialize models
        require_once __DIR__ . '/../../models/AppointmentModel.php';
        require_once __DIR__ . '/../../models/StaffModel.php';
        require_once __DIR__ . '/../../models/PatientModel.php';
        require_once __DIR__ . '/../../helpers/AuditLogger.php';

        $this->appointmentModel = new AppointmentModel();
        $this->staffModel = new StaffModel();
        $this->patientModel = new PatientModel();
        $this->auditLogger = new AuditLogger($this->db);
    }

    // Validation rules
    private array $validationRules = [
        'create' => [
            'patient_id' => 'required|integer',
            'doctor_id' => 'required|integer',
            'appointment_date' => 'required|date_format:Y-m-d',
            'appointment_time' => 'required|date_format:H:i:s',
            'appointment_type' => 'required|string|max:50',
            'notes' => 'string|max:500'
        ],
        'update' => [
            'patient_id' => 'integer',
            'doctor_id' => 'integer',
            'appointment_date' => 'date_format:Y-m-d',
            'appointment_time' => 'date_format:H:i:s',
            'appointment_type' => 'string|max:50',
            'status' => 'string|in:scheduled,confirmed,completed,cancelled,no_show',
            'notes' => 'string|max:500'
        ]
    ];

    /**
     * Get query parameters from request
     *
     * @return array Query parameters
     */
    private function getQueryParams(): array
    {
        return [
            'page' => $this->getIntParam('page', 'GET', 1),
            'limit' => $this->getIntParam('limit', 'GET', 20),
            'status' => $this->getStringParam('status', 'GET'),
            'doctor_id' => $this->getIntParam('doctor_id', 'GET'),
            'patient_id' => $this->getIntParam('patient_id', 'GET'),
            'date_from' => $this->getStringParam('date_from', 'GET'),
            'date_to' => $this->getStringParam('date_to', 'GET')
        ];
    }

    /**
     * Get all appointments
     */
    public function index(): void
    {
        try {
            $params = $this->getQueryParams();
            $appointments = $this->appointmentModel->getAppointmentsFiltered($params);

            $this->sendResponse([
                'data' => $appointments,
                'meta' => [
                    'total' => count($appointments),
                    'page' => $params['page'] ?? 1,
                    'limit' => $params['limit'] ?? 20
                ]
            ]);
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve appointments: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get appointment by ID
     *
     * @param int $id Appointment ID
     */
    public function get($id): void
    {
        try {
            $appointment = $this->appointmentModel->getAppointmentDetails($id);

            if (!$appointment) {
                $this->sendError('Appointment not found', 404);
            }

            // Check permissions
            if (!$this->hasAccessToAppointment($appointment)) {
                $this->sendError('Unauthorized access', 403);
            }

            $this->sendResponse(['data' => $appointment]);
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve appointment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new appointment
     */
    public function create(): void
    {
        try {
            $data = $this->getRequestData();

            // Validate input
            $validation = $this->validate($data, $this->validationRules['create']);
            if (!$validation['success']) {
                $this->sendError('Validation failed', 422, $validation['errors']);
                return;
            }

            // Check for conflicts
            if (
                $this->hasSchedulingConflict(
                    $data['doctor_id'],
                    $data['appointment_date'],
                    $data['appointment_time']
                )
            ) {
                $this->sendError('Scheduling conflict: The selected time slot is not available', 409);
                return;
            }

            // Create appointment
            $appointmentId = $this->appointmentModel->createAppointment([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'appointment_type' => $data['appointment_type'],
                'notes' => $data['notes'] ?? null,
                'status' => self::STATUS_SCHEDULED,
                'created_by' => $this->getCurrentUserId()
            ]);

            // Log the action
            $this->auditLogger->log([
                'user_id' => $this->getCurrentUserId(),
                'action' => 'create',
                'entity_type' => 'appointment',
                'entity_id' => $appointmentId,
                'details' => 'Created new appointment'
            ]);

            $this->sendResponse([
                'message' => 'Appointment created successfully',
                'appointment_id' => $appointmentId
            ], 201);
        } catch (Exception $e) {
            $this->sendError('Failed to create appointment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update an appointment
     *
     * @param int $id Appointment ID
     */
    public function update($id): void
    {
        try {
            $data = $this->getRequestData();

            // Get existing appointment
            $appointment = $this->appointmentModel->getAppointmentDetails($id);
            if (!$appointment) {
                $this->sendError('Appointment not found', 404);
            }

            // Check permissions
            if (!$this->hasAccessToAppointment($appointment)) {
                $this->sendError('Unauthorized access', 403);
            }

            // Validate input
            $validation = $this->validate($data, $this->validationRules['update']);
            if (!$validation['success']) {
                $this->sendError('Validation failed', 422, $validation['errors']);
            }

            // Check for conflicts if time/date is being updated
            if (isset($data['appointment_date']) || isset($data['appointment_time'])) {
                $date = $data['appointment_date'] ?? $appointment['appointment_date'];
                $time = $data['appointment_time'] ?? $appointment['appointment_time'];

                if (
                    $this->hasSchedulingConflict(
                        $data['doctor_id'] ?? $appointment['doctor_id'],
                        $date,
                        $time,
                        $id
                    )
                ) {
                    $this->sendError('Scheduling conflict: The selected time slot is not available', 409);
                }
            }

            // Update appointment
            $updated = $this->appointmentModel->updateAppointment($id, $data);

            if ($updated) {
                // Log the action
                $this->auditLogger->log([
                    'user_id' => $this->getCurrentUserId(),
                    'action' => 'update',
                    'entity_type' => 'appointment',
                    'entity_id' => $id,
                    'details' => 'Updated appointment details'
                ]);

                $this->sendResponse(['message' => 'Appointment updated successfully']);
            }

            $this->sendError('Failed to update appointment', 500);
        } catch (Exception $e) {
            $this->sendError('Failed to update appointment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cancel an appointment
     *
     * @param int $id Appointment ID
     */
    public function cancel($id): void
    {
        try {
            // Get existing appointment
            $appointment = $this->appointmentModel->getAppointmentDetails($id);
            if (!$appointment) {
                $this->sendError('Appointment not found', 404);
            }

            // Check permissions
            if (!$this->hasAccessToAppointment($appointment)) {
                $this->sendError('Unauthorized access', 403);
            }

            // Check if already cancelled
            if ($appointment['status'] === self::STATUS_CANCELLED) {
                $this->sendError('Appointment is already cancelled', 400);
            }

            // Cancel appointment
            $data = [
                'status' => self::STATUS_CANCELLED,
                'cancelled_by' => $this->getCurrentUserId(),
                'cancelled_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->appointmentModel->updateAppointment($id, $data);

            if ($updated) {
                // Log the action
                $this->auditLogger->log([
                    'user_id' => $this->getCurrentUserId(),
                    'action' => 'cancel',
                    'entity_type' => 'appointment',
                    'entity_id' => $id,
                    'details' => 'Cancelled appointment'
                ]);

                $this->sendResponse(['message' => 'Appointment cancelled successfully']);
            }

            $this->sendError('Failed to cancel appointment', 500);
        } catch (Exception $e) {
            $this->sendError('Failed to cancel appointment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get available time slots
     */
    public function getAvailableSlots(): void
    {
        try {
            $doctorId = $this->getIntParam('doctor_id');
            $date = $this->getStringParam('date');

            if ($doctorId === 0 || ($date === '' || $date === '0')) {
                $this->sendError('Doctor ID and date are required', 400);
            }

            $slots = $this->appointmentModel->getAvailableTimeSlots($doctorId, $date);

            $this->sendResponse([
                'data' => $slots,
                'meta' => [
                    'doctor_id' => $doctorId,
                    'date' => $date,
                    'total_slots' => count($slots)
                ]
            ]);
        } catch (Exception $e) {
            $this->sendError('Failed to get available slots: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get pending appointments count
     */
    public function pendingCount(): void
    {
        try {
            $userId = $this->getCurrentUserId();
            $role = $this->getCurrentUserRole();

            $count = 0;

            if ($role === 'admin') {
                // Admin sees all pending appointments
                $sql = "SELECT COUNT(*) as count FROM appointments WHERE status IN ('scheduled', 'pending')";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $count = $row['count'] ?? 0;
            } elseif ($role === 'doctor') {
                // Doctors see their own pending appointments
                $doctorId = $this->staffModel->getStaffIdByUserId($userId);
                if ($doctorId) {
                    $sql = "SELECT COUNT(*) as count FROM appointments 
                            WHERE doctor_id = ? AND status IN ('scheduled', 'pending')";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bind_param('i', $doctorId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $count = $row['count'] ?? 0;
                }
            } elseif ($role === 'patient') {
                // Patients see their own pending appointments
                $patientId = $this->patientModel->getPatientIdByUserId($userId);
                if ($patientId) {
                    $sql = "SELECT COUNT(*) as count FROM appointments 
                            WHERE patient_id = ? AND status IN ('scheduled', 'pending')";
                    $stmt = $this->db->prepare($sql);
                    $stmt->bind_param('i', $patientId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $count = $row['count'] ?? 0;
                }
            }

            $this->sendResponse(['count' => (int)$count]);
        } catch (Exception $e) {
            $this->sendError('Failed to get pending appointments count: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get appointment statistics
     */
    public function stats(): void
    {
        try {
            $startDate = $this->getStringParam('start_date', date('Y-m-01'));
            $endDate = $this->getStringParam('end_date', date('Y-m-t'));
            $doctorId = $this->getIntParam('doctor_id');

            $stats = $this->getAppointmentStats($startDate, $endDate, $doctorId);

            $this->sendResponse([
                'data' => $stats,
                'meta' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'doctor_id' => $doctorId
                ]
            ]);
        } catch (Exception $e) {
            $this->sendError('Failed to get appointment statistics: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get appointment statistics
     *
     * @param int|null $doctorId
     * @return array
     */
    private function getAppointmentStats(string $startDate, string $endDate, $doctorId = null)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                        SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show
                    FROM appointments 
                    WHERE appointment_date BETWEEN ? AND ?";

            $params = [$startDate, $endDate];
            $types = 'ss';

            if ($doctorId) {
                $sql .= " AND doctor_id = ?";
                $params[] = $doctorId;
                $types .= 'i';
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats = $result->fetch_assoc();
            $stmt->close();

            return $stats;
        } catch (Exception $e) {
            error_log("Error getting appointment stats: " . $e->getMessage());
            return [
                'total' => 0,
                'scheduled' => 0,
                'confirmed' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'no_show' => 0
            ];
        }
    }

    /**
     * Check if user has access to the appointment
     *
     * @param array $appointment Appointment data
     * @return bool
     */
    private function hasAccessToAppointment(array $appointment)
    {
        $userId = $this->getCurrentUserId();
        $role = $this->getCurrentUserRole();

        // Admin has access to all appointments
        if ($role === 'admin') {
            return true;
        }

        // Doctors can access their own appointments
        if ($role === 'doctor') {
            $doctorId = $this->staffModel->getStaffIdByUserId($userId);
            return $appointment['doctor_id'] == $doctorId;
        }

        // Patients can access their own appointments
        if ($role === 'patient') {
            $patientId = $this->patientModel->getPatientIdByUserId($userId);
            return $appointment['patient_id'] == $patientId;
        }

        // Other roles have no access by default
        return false;
    }

    /**
     * Check for scheduling conflicts
     *
     * @param int $doctorId
     * @param string $date
     * @param string $time
     * @param int $excludeAppointmentId
     * @return bool
     */
    private function hasSchedulingConflict($doctorId, $date, $time, $excludeAppointmentId = null)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM appointments 
                    WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? 
                    AND status NOT IN ('cancelled', 'no_show')";

            $params = [$doctorId, $date, $time];
            $types = 'iss';

            if ($excludeAppointmentId) {
                $sql .= " AND appointment_id != ?";
                $params[] = $excludeAppointmentId;
                $types .= 'i';
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            return $row['count'] > 0;
        } catch (Exception $e) {
            error_log("Error checking scheduling conflict: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate input data
     *
     * @param array $rules
     */
    private function validate(array $data, $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $rules = explode('|', (string) $rule);

            foreach ($rules as $r) {
                $params = explode(':', $r);
                $ruleName = array_shift($params);

                switch ($ruleName) {
                    case 'required':
                        if (!isset($data[$field]) || $data[$field] === '') {
                            $errors[$field][] = 'The ' . str_replace('_', ' ', $field) . ' field is required';
                        }
                        break;

                    case 'integer':
                        if (isset($data[$field]) && !is_numeric($data[$field])) {
                            $errors[$field][] = 'The ' . str_replace('_', ' ', $field) . ' must be an integer';
                        }
                        break;

                    case 'date_format':
                        $format = $params[0] ?? 'Y-m-d';
                        $date = \DateTime::createFromFormat($format, $data[$field] ?? '');
                        if (isset($data[$field]) && !($date && $date->format($format) === $data[$field])) {
                            $errors[$field][] = 'The ' . str_replace('_', ' ', $field) . ' must be in format ' . $format;
                        }
                        break;

                    case 'string':
                        if (isset($data[$field]) && !is_string($data[$field])) {
                            $errors[$field][] = 'The ' . str_replace('_', ' ', $field) . ' must be a string';
                        }
                        break;

                    case 'max':
                        $max = (int)($params[0] ?? 0);
                        if (isset($data[$field]) && strlen((string) $data[$field]) > $max) {
                            $errors[$field][] = 'The ' . str_replace('_', ' ', $field) . ' may not be greater than ' . $max . ' characters';
                        }
                        break;

                    case 'in':
                        $allowed = explode(',', $params[0] ?? '');
                        if (isset($data[$field]) && !in_array($data[$field], $allowed)) {
                            $errors[$field][] = 'The selected ' . str_replace('_', ' ', $field) . ' is invalid';
                        }
                        break;
                }
            }
        }

        return [
            'success' => $errors === [],
            'errors' => $errors
        ];
    }
}
