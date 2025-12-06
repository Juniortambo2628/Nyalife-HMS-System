<?php
/**
 * Nyalife HMS - Appointment API Controller
 * 
 * This controller handles all appointment-related API requests.
 */

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../../../includes/data/appointment_data.php';

class AppointmentController extends ApiController {
    // Status constants
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NOSHOW = 'no_show';
    
    // Validation rules
    private $validationRules = [
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
     * Get all appointments
     * 
     * @return void
     */
    public function index() {
        try {
            $params = $this->getQueryParams();
            $appointments = $this->appointmentModel->getAppointments($params);
            
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
     * @return void
     */
    public function get($id) {
        try {
            $appointment = $this->appointmentModel->getAppointmentById($id);
            
            if (!$appointment) {
                return $this->sendError('Appointment not found', 404);
            }
            
            // Check permissions
            if (!$this->hasAccessToAppointment($appointment)) {
                return $this->sendError('Unauthorized access', 403);
            }
            
            $this->sendResponse(['data' => $appointment]);
        } catch (Exception $e) {
            $this->sendError('Failed to retrieve appointment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new appointment
     * 
     * @return void
     */
    public function create() {
        try {
            $data = $this->getRequestData();
            
            // Validate input
            $validation = $this->validate($data, $this->validationRules['create']);
            if (!$validation['success']) {
                return $this->sendError('Validation failed', 422, $validation['errors']);
            }
            
            // Check for conflicts
            if ($this->hasSchedulingConflict(
                $data['doctor_id'],
                $data['appointment_date'],
                $data['appointment_time']
            )) {
                return $this->sendError('Scheduling conflict: The selected time slot is not available', 409);
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
     * @return void
     */
    public function update($id) {
        try {
            $data = $this->getRequestData();
            
            // Get existing appointment
            $appointment = $this->appointmentModel->getAppointmentById($id);
            if (!$appointment) {
                return $this->sendError('Appointment not found', 404);
            }
            
            // Check permissions
            if (!$this->hasAccessToAppointment($appointment)) {
                return $this->sendError('Unauthorized access', 403);
            }
            
            // Validate input
            $validation = $this->validate($data, $this->validationRules['update']);
            if (!$validation['success']) {
                return $this->sendError('Validation failed', 422, $validation['errors']);
            }
            
            // Check for conflicts if time/date is being updated
            if (isset($data['appointment_date']) || isset($data['appointment_time'])) {
                $date = $data['appointment_date'] ?? $appointment['appointment_date'];
                $time = $data['appointment_time'] ?? $appointment['appointment_time'];
                
                if ($this->hasSchedulingConflict(
                    $data['doctor_id'] ?? $appointment['doctor_id'],
                    $date,
                    $time,
                    $id
                )) {
                    return $this->sendError('Scheduling conflict: The selected time slot is not available', 409);
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
                
                return $this->sendResponse(['message' => 'Appointment updated successfully']);
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
     * @return void
     */
    public function cancel($id) {
        try {
            // Get existing appointment
            $appointment = $this->appointmentModel->getAppointmentById($id);
            if (!$appointment) {
                return $this->sendError('Appointment not found', 404);
            }
            
            // Check permissions
            if (!$this->hasAccessToAppointment($appointment)) {
                return $this->sendError('Unauthorized access', 403);
            }
            
            // Check if already cancelled
            if ($appointment['status'] === self::STATUS_CANCELLED) {
                return $this->sendError('Appointment is already cancelled', 400);
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
                
                return $this->sendResponse(['message' => 'Appointment cancelled successfully']);
            }
            
            $this->sendError('Failed to cancel appointment', 500);
            
        } catch (Exception $e) {
            $this->sendError('Failed to cancel appointment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get available time slots
     * 
     * @return void
     */
    public function getAvailableSlots() {
        try {
            $doctorId = $this->getIntParam('doctor_id');
            $date = $this->getStringParam('date');
            
            if (!$doctorId || !$date) {
                return $this->sendError('Doctor ID and date are required', 400);
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
     * Get appointment statistics
     * 
     * @return void
     */
    public function stats() {
        try {
            $startDate = $this->getStringParam('start_date', date('Y-m-01'));
            $endDate = $this->getStringParam('end_date', date('Y-m-t'));
            $doctorId = $this->getIntParam('doctor_id');
            
            $stats = $this->appointmentModel->getAppointmentStats($startDate, $endDate, $doctorId);
            
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
     * Check if user has access to the appointment
     * 
     * @param array $appointment Appointment data
     * @return bool
     */
    private function hasAccessToAppointment($appointment) {
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
    private function hasSchedulingConflict($doctorId, $date, $time, $excludeAppointmentId = null) {
        return $this->appointmentModel->hasSchedulingConflict(
            $doctorId,
            $date,
            $time,
            $excludeAppointmentId
        );
    }
    
    /**
     * Validate input data
     * 
     * @param array $data
     * @param array $rules
     * @return array
     */
    private function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $rules = explode('|', $rule);
            
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
                        if (isset($data[$field]) && strlen($data[$field]) > $max) {
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
            'success' => empty($errors),
            'errors' => $errors
        ];
    }
}