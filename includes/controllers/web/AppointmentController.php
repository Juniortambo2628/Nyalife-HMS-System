<?php

/**
 * Nyalife HMS - Appointment Controller
 *
 * Controller for managing appointments.
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/AppointmentModel.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/PatientModel.php';
require_once __DIR__ . '/../../models/StaffModel.php';
require_once __DIR__ . '/../../helpers/AuditLogger.php';
require_once __DIR__ . '/../../services/NotificationService.php';

class AppointmentController extends WebController
{
    protected \AppointmentModel $appointmentModel;

    protected \UserModel $userModel;

    protected \PatientModel $patientModel;

    protected \StaffModel $staffModel;

    protected \AuditLogger $auditLogger;

    protected \NotificationService $notificationService;

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
        // Initialize models first
        $this->appointmentModel = new AppointmentModel();
        $this->userModel = new UserModel();
        $this->patientModel = new PatientModel();
        $this->staffModel = new StaffModel();

        // Now initialize audit logger with a database connection
        $this->auditLogger = new AuditLogger($this->appointmentModel->getDbConnection());

        // Initialize notification service
        $this->notificationService = new NotificationService();

        $this->pageTitle = 'Appointments - Nyalife HMS';

        // Load necessary helpers (Framework might autoload or use a different mechanism)
        // $this->load->helper('url');
        // $this->load->library('session');
        // $this->load->helper('form');
        // $this->load->helper('security');
    }

    /**
     * Display all appointments
     */
    public function index(): void
    {
        // Check if user is logged in
        if (!SessionManager::get('user_id')) {
            $this->redirect('/login');
            return;
        }

        $userId = SessionManager::get('user_id');
        $role = SessionManager::get('role');

        // Debug logging
        error_log("AppointmentController::index - User ID: $userId, Role: $role");

        // Get filter parameters
        $filterDate = $_GET['filter_date'] ?? null;
        $filterDoctor = $_GET['filter_doctor'] ?? null;
        $filterPatient = $_GET['filter_patient'] ?? null;
        $filterStatus = $_GET['filter_status'] ?? null;

        $filters = [];
        if (!empty($filterDate)) {
            $filters['appointment_date'] = $filterDate;
        }
        if (!empty($filterDoctor)) {
            $filters['doctor_id'] = $filterDoctor;
        }
        if (!empty($filterPatient)) {
            $filters['patient_id'] = $filterPatient;
        }
        if (!empty($filterStatus)) {
            $filters['status'] = $filterStatus;
        }

        if (in_array($role, ['admin', 'receptionist', 'nurse'])) { // Allow nurses to see all/filtered appointments
            // Admins/Receptionists can see all/filtered appointments based on dropdowns
            // If a specific doctor/patient is selected in filter, it's already in $filters
            $appointments = $this->appointmentModel->getAppointmentsFiltered($filters);
        } elseif ($role === 'doctor') {
            $doctorStaffId = $this->userModel->getDoctorIdByUserId($userId);
            if ($doctorStaffId) {
                // If a doctor is filtering by patient/date/status, those filters are in $filters.
                // We must ensure this doctor only sees their own appointments.
                $filters['doctor_id'] = $doctorStaffId;
            } else {
                // Doctor not found or not linked to staff, show no appointments
                $filters['doctor_id'] = -1; // Force no results if doctor_id is not found
            }
            $appointments = $this->appointmentModel->getAppointmentsFiltered($filters);
        } elseif ($role === 'patient') {
            $patientId = $this->patientModel->getPatientIdByUserId($userId);
            if ($patientId) {
                // If a patient is filtering by doctor/date/status, those filters are in $filters.
                // We must ensure this patient only sees their own appointments.
                $filters['patient_id'] = $patientId;
            } else {
                // Patient not found, show no appointments
                $filters['patient_id'] = -1; // Force no results if patient_id is not found
            }
            $appointments = $this->appointmentModel->getAppointmentsFiltered($filters);
        } else {
            $appointments = [];
        }

        // Debug logging
        error_log("AppointmentController::index - Found " . count($appointments) . " appointments for role: $role");

        // Get doctors for filter dropdown
        $doctors = $this->userModel->getAllDoctors();

        // Get patients for filter dropdown (admin, receptionist, and nurse)
        $patients = [];
        if (in_array($role, ['admin', 'receptionist', 'nurse'])) {
            $patients = $this->patientModel->getAllPatients();
        }

        // Log the view action
        $this->auditLogger->log([
            'user_id' => $userId,
            'action' => 'view',
            'entity_type' => 'appointments',
            'entity_id' => null,
            'details' => 'Viewed appointments list',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);

        $this->renderView('appointments/index', [
            'appointments' => $appointments,
            'doctors' => $doctors,
            'patients' => $patients,
            'filterDate' => $filterDate,
            'filterDoctor' => $filterDoctor,
            'filterPatient' => $filterPatient,
            'filterStatus' => $filterStatus,
            'userRole' => $role
        ]);
    }

    /**
     * Display appointment creation form or handle form submission
     */
    public function create(): void
    {
        // Check if request is AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                 strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        // Check if user is logged in
        if (!SessionManager::isLoggedIn()) {
            if ($isAjax) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Not logged in']);
                return;
            }
            $this->redirect('/login');
            return;
        }

        $userId = SessionManager::get('user_id');
        $role = SessionManager::get('role');

        // Handle form submission (POST request)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle AJAX submission
            if ($isAjax) {
                // Validate input manually
                $errors = [];
                $requiredFields = [
                    'patient_id' => 'Patient',
                    'doctor_id' => 'Doctor',
                    'appointment_date' => 'Date',
                    'appointment_time' => 'Time',
                    'appointment_type' => 'Appointment Type'
                ];

                foreach ($requiredFields as $field => $label) {
                    if (!isset($_POST[$field]) || in_array(trim((string) $_POST[$field]), ['', '0'], true)) {
                        $errors[$field] = "$label is required";
                    }
                }

                if ($errors !== []) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $errors
                    ]);
                    return;
                }

                // Prepare appointment data
                $appointmentData = [
                    'patient_id' => $_POST['patient_id'] ?? null,
                    'doctor_id' => $_POST['doctor_id'] ?? null,
                    'appointment_date' => $_POST['appointment_date'] ?? null,
                    'appointment_time' => $_POST['appointment_time'] ?? null,
                    'appointment_type' => $_POST['appointment_type'] ?? null,
                    'reason' => $_POST['reason'] ?? null,
                    'status' => 'scheduled',
                    'created_by' => $userId,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                // Create appointment
                $appointmentId = $this->appointmentModel->createAppointment($appointmentData);

                if ($appointmentId) {
                    // Log the action
                    $this->auditLogger->log([
                        'user_id' => $userId,
                        'action' => 'create',
                        'entity_type' => 'appointment',
                        'entity_id' => $appointmentId,
                        'details' => 'Created new appointment',
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                    ]);

                    // Send notifications to patient and doctor
                    $this->notificationService->sendAppointmentCreatedNotification($appointmentId);

                    http_response_code(201);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Appointment created successfully',
                        'appointment_id' => $appointmentId
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to create appointment'
                    ]);
                }
            } else {
                // Handle regular form submission
                $formData = [];
                $requiredFields = ['patient_id', 'doctor_id', 'appointment_date', 'appointment_time', 'reason'];
                $allFieldsPresent = true;
                foreach ($requiredFields as $field) {
                    if (isset($_POST[$field]) && !in_array(trim((string) $_POST[$field]), ['', '0'], true)) {
                        $formData[$field] = trim((string) $_POST[$field]); // Trim whitespace
                    } else {
                        $this->setFlashMessage('error', "Missing or empty field: " . ucfirst(str_replace('_', ' ', $field)));
                        $allFieldsPresent = false;
                        break;
                    }
                }

                if (!$allFieldsPresent) {
                    $this->redirect('/appointments/create');
                    return;
                }

                // Add additional fields
                $formData['status'] = 'pending';
                $formData['created_by'] = $userId;

                try {
                    // Create appointment
                    $appointmentId = $this->appointmentModel->createAppointment($formData);

                    if ($appointmentId) {
                        $this->setFlashMessage('success', 'Appointment created successfully');
                        $this->redirect('/appointments');
                    } else {
                        throw new Exception('Failed to create appointment');
                    }
                } catch (Exception $e) {
                    // Log error
                    ErrorHandler::logSystemError($e, __METHOD__);

                    // Set error message
                    $this->setFlashMessage('error', 'Failed to create appointment: ' . $e->getMessage());

                    // Redirect back to create form
                    $this->redirect('/appointments/create');
                }
            }
            return;
        }

        // Display the form (GET request)
        // Get all doctors for selection
        $doctors = $this->userModel->getAllDoctors();

        // If user is a patient, pre-select the patient
        $selectedPatientId = null;
        if ($role === 'patient') {
            $selectedPatientId = $this->patientModel->getPatientIdByUserId($userId);
            $patients = [$this->patientModel->getWithUserData($selectedPatientId)];
        } else {
            // Get all patients for selection
            $patients = $this->patientModel->getAllPatientsWithUserData();
        }

        // Define appointment types and status options
        $appointmentTypes = ['consultation', 'follow_up', 'emergency', 'routine_checkup', 'vaccination', 'lab_test'];
        $statusOptions = [
            'scheduled' => 'Scheduled',
            'confirmed' => 'Confirmed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show'
        ];

        $this->renderView('appointments/create', [
            'doctors' => $doctors,
            'patients' => $patients,
            'selectedPatientId' => $selectedPatientId,
            'userRole' => $role,
            'formData' => [], // Initialize empty form data for GET requests
            'appointmentTypes' => $appointmentTypes,
            'statusOptions' => $statusOptions,
            'errorMessage' => null // Initialize error message
        ]);
    }

    /**
     * Display appointment details
     *
     * @param int $id Appointment ID
     */
    public function view($id): void
    {
        // Get appointment details
        $appointment = $this->appointmentModel->getAppointmentDetails($id);

        if (!$appointment) {
            $this->setFlashMessage('error', 'Appointment not found');
            $this->redirect('/appointments');
            return;
        }

        // Get medical history for this appointment (placeholder - implement when needed)
        $medicalHistory = [];

        // Get prescriptions for this appointment (placeholder - implement when needed)
        $prescriptions = [];

        // Get lab tests for this appointment (placeholder - implement when needed)
        $labTests = [];

        $this->renderView('appointments/view', [
            'appointment' => $appointment,
            'medicalHistory' => $medicalHistory,
            'prescriptions' => $prescriptions,
            'labTests' => $labTests,
            'userRole' => SessionManager::get('role')
        ]);
    }

    /**
     * Display appointment edit form
     *
     * @param int $id Appointment ID
     */
    public function edit($id): void
    {
        // Get appointment details
        $appointment = $this->appointmentModel->getAppointmentDetails($id);

        if (!$appointment) {
            $this->setFlashMessage('error', 'Appointment not found');
            $this->redirect('/appointments');
            return;
        }

        // Get all doctors for selection
        $doctors = $this->userModel->getAllDoctors();

        // Get all patients for selection
        $patients = $this->patientModel->getAllPatientsWithUserData();

        // Get patient details
        $patient = $this->patientModel->getWithUserData($appointment['patient_id']);

        // Define appointment types
        $appointmentTypes = [
            'new_visit' => 'New Patient Consultation',
            'follow_up' => 'Follow-up',
            'emergency' => 'Emergency',
            'routine_checkup' => 'Routine Check-up',
            'consultation' => 'General Consultation'
        ];

        // Define status options
        $statusOptions = [
            'scheduled' => 'Scheduled',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show'
        ];

        // Transform appointment data to match view expectations
        $appointmentData = [
            'id' => $appointment['appointment_id'],
            'patient_id' => $appointment['patient_id'],
            'doctor_id' => $appointment['doctor_id'],
            'date' => $appointment['appointment_date'],
            'time' => $appointment['appointment_time'],
            'appointment_type' => $appointment['appointment_type'],
            'status' => $appointment['status'],
            'reason' => $appointment['reason'] ?? $appointment['notes'] ?? '',
            'notes' => $appointment['notes'] ?? ''
        ];

        $this->renderView('appointments/edit', [
            'appointment' => $appointmentData,
            'doctors' => $doctors,
            'patients' => $patients,
            'patient' => $patient,
            'appointmentTypes' => array_keys($appointmentTypes),
            'statusOptions' => $statusOptions,
            'userRole' => SessionManager::get('role')
        ]);
    }

    /**
     * Update an appointment
     *
     * @param int $id Appointment ID
     */
    public function update($id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/appointments/edit/' . $id);
            return;
        }

        // Fetch existing appointment details to ensure it exists
        $existingAppointment = $this->appointmentModel->getAppointmentDetails($id);
        if (!$existingAppointment) {
            $this->setFlashMessage('error', 'Appointment not found or cannot be updated.');
            $this->redirect('/appointments');
            return;
        }

        // Prepare data from POST request
        $formData = [];

        if (isset($_POST['doctor_id']) && $_POST['doctor_id'] !== '') {
            $formData['doctor_id'] = filter_var($_POST['doctor_id'], FILTER_SANITIZE_NUMBER_INT);
        }
        if (isset($_POST['appointment_date']) && $_POST['appointment_date'] !== '') {
            $formData['appointment_date'] = trim((string) $_POST['appointment_date']);
        }
        if (isset($_POST['appointment_time']) && $_POST['appointment_time'] !== '') {
            $formData['appointment_time'] = trim((string) $_POST['appointment_time']);
        }
        if (isset($_POST['status']) && $_POST['status'] !== '') {
            $formData['status'] = trim((string) $_POST['status']);
        }

        if (isset($_POST['reason']) && $_POST['reason'] !== '') {
            $formData['notes'] = trim((string) $_POST['reason']);
        } elseif (isset($_POST['notes']) && $_POST['notes'] !== '') {
            $formData['notes'] = trim((string) $_POST['notes']);
        }

        if (empty($formData)) {
            $this->setFlashMessage('info', 'No changes submitted for update.');
            $this->redirect('/appointments/edit/' . $id);
            return;
        }

        $userId = SessionManager::get('user_id');

        try {
            $result = $this->appointmentModel->updateAppointment($id, $formData);

            if ($result) {
                $this->auditLogger->log([
                    'user_id' => $userId,
                    'action' => 'update',
                    'entity_type' => 'appointment',
                    'entity_id' => $id,
                    'details' => 'Appointment updated. New data: ' . json_encode($formData),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);

                // Send update notification
                $this->notificationService->sendAppointmentUpdatedNotification($id, $formData);

                $this->setFlashMessage('success', 'Appointment updated successfully');
                $this->redirect('/appointments/view/' . $id);
            } else {
                throw new Exception('Failed to update appointment in database (no rows affected or other non-exception failure).');
            }
        } catch (Exception $e) {
            ErrorHandler::logSystemError($e, __METHOD__);
            $this->auditLogger->log([
                'user_id' => $userId,
                'action' => 'update_failed',
                'entity_type' => 'appointment',
                'entity_id' => $id,
                'details' => 'Failed to update appointment. Error: ' . $e->getMessage() . '. Submitted data: ' . json_encode($formData),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            $this->setFlashMessage('error', 'Failed to update appointment: ' . $e->getMessage());
            $this->redirect('/appointments/edit/' . $id);
        }
    }

    /**
     * Cancel an appointment
     *
     * @param int $id Appointment ID
     */
    public function cancel($id): void
    {
        // Get appointment to cancel
        $appointment = $this->appointmentModel->getAppointmentDetails($id);

        if (!$appointment) {
            $this->setFlashMessage('error', 'Appointment not found');
            $this->redirect('/appointments');
            return;
        }

        // Check if appointment can be cancelled
        if ($appointment['status'] === 'completed' || $appointment['status'] === 'cancelled') {
            $this->setFlashMessage('error', 'This appointment cannot be cancelled as it is already ' . $appointment['status'] . '.');
            $this->redirect('/appointments/view/' . $id);
            return;
        }

        $userId = SessionManager::get('user_id');
        $cancellationData = [
            'status' => 'cancelled',
            'cancelled_by' => $userId,
            'cancelled_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s') // Ensure updated_at is also set
        ];

        try {
            // Cancel appointment by updating its status
            $result = $this->appointmentModel->updateAppointment($id, $cancellationData);

            if ($result) {
                $this->auditLogger->log([
                    'user_id' => $userId,
                    'action' => 'cancel',
                    'entity_type' => 'appointment',
                    'entity_id' => $id,
                    'details' => 'Appointment cancelled.',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);

                // Send cancellation notification
                $cancellationReason = $_POST['cancellation_reason'] ?? 'No reason provided';
                $this->notificationService->sendAppointmentCancelledNotification($id, $cancellationReason);

                $this->setFlashMessage('success', 'Appointment cancelled successfully');
                $this->redirect('/appointments');
            } else {
                throw new Exception('Failed to cancel appointment in database (no rows affected or other non-exception failure).');
            }
        } catch (Exception $e) {
            ErrorHandler::logSystemError($e, __METHOD__);
            $this->auditLogger->log([
                'user_id' => $userId,
                'action' => 'cancel_failed',
                'entity_type' => 'appointment',
                'entity_id' => $id,
                'details' => 'Failed to cancel appointment. Error: ' . $e->getMessage(),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            $this->setFlashMessage('error', 'Failed to cancel appointment: ' . $e->getMessage());
            $this->redirect('/appointments/view/' . $id);
        }
    }

    /**
     * Add medical history to an appointment
     *
     * @param int $id Appointment ID
     */
    public function addMedicalHistory($id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/appointments/view/' . $id);
            return;
        }

        // Get appointment details
        $appointment = $this->appointmentModel->getAppointmentDetails($id);

        if (!$appointment) {
            $this->setFlashMessage('error', 'Appointment not found');
            $this->redirect('/appointments');
            return;
        }

        // Check user role (Doctor or Admin can add medical history)
        $role = SessionManager::get('role');
        if ($role !== 'doctor' && $role !== 'admin') {
            $this->setFlashMessage('error', 'You are not authorized to add medical history.');
            $this->redirect('/appointments/view/' . $id);
            return;
        }

        // Validate that the appointment is not already completed or cancelled
        if (in_array($appointment['status'], ['completed', 'cancelled'])) {
            $this->setFlashMessage('warning', 'Medical history cannot be added to an appointment that is already ' . $appointment['status'] . '.');
            $this->redirect('/appointments/view/' . $id);
            return;
        }

        // Get form data from POST request
        $historyData = [];
        $requiredFields = ['diagnosis', 'treatment']; // Notes can be optional
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (isset($_POST[$field]) && !in_array(trim((string) $_POST[$field]), ['', '0'], true)) {
                $historyData[$field] = trim(htmlspecialchars((string) $_POST[$field])); // Basic sanitization
            } else {
                $missingFields[] = ucfirst($field);
            }
        }

        if ($missingFields !== []) {
            $this->setFlashMessage('error', 'Missing required medical history fields: ' . implode(', ', $missingFields) . '.');
            $this->redirect('/appointments/view/' . $id); // Or back to a specific form page if one exists
            return;
        }

        // Optional notes field
        if (isset($_POST['notes'])) {
            $historyData['notes'] = trim(htmlspecialchars((string) $_POST['notes'])); // Basic sanitization
        }

        // Add additional fields required by the model's addMedicalHistory method
        $historyData['appointment_id'] = (int)$id;
        $historyData['patient_id'] = (int)$appointment['patient_id'];
        $historyData['doctor_id'] = (int)$appointment['doctor_id']; // This is staff_id of the doctor

        $userId = SessionManager::get('user_id');
        $historyData['created_by'] = $userId;
        // created_at is handled by the model or DB

        $logDetails = ['submitted_data' => $historyData];

        try {
            // Add medical history
            $historyId = $this->appointmentModel->addMedicalHistory($id, $historyData);

            if ($historyId) {
                $this->auditLogger->log([
                    'user_id' => $userId,
                    'action' => 'add_medical_history',
                    'entity_type' => 'appointment',
                    'entity_id' => $id,
                    'related_entity_id' => $historyId,
                    'details' => 'Medical history added. History ID: ' . $historyId . '. Data: ' . json_encode($historyData),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);

                // Update appointment status to completed
                $updateData = [
                    'status' => 'completed',
                ];
                $this->appointmentModel->updateAppointment($id, $updateData);

                $this->auditLogger->log([ // Audit log for appointment status update
                    'user_id' => $userId,
                    'action' => 'update_status',
                    'entity_type' => 'appointment',
                    'entity_id' => $id,
                    'details' => 'Appointment status updated to completed after adding medical history.',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);

                // Send completion notification
                $this->notificationService->sendAppointmentCompletedNotification($id);

                $this->setFlashMessage('success', 'Medical history added and appointment marked as completed.');
                $this->redirect('/appointments/view/' . $id);
            } else {
                throw new Exception('Failed to add medical history to database (model returned false/null).');
            }
        } catch (Exception $e) {
            ErrorHandler::logSystemError($e, __METHOD__);
            $this->auditLogger->log([
                'user_id' => $userId,
                'action' => 'add_medical_history_failed',
                'entity_type' => 'appointment',
                'entity_id' => $id,
                'details' => 'Failed to add medical history. Error: ' . $e->getMessage() . '. ' . json_encode($logDetails),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            $this->setFlashMessage('error', 'Failed to add medical history: ' . $e->getMessage());
            $this->redirect('/appointments/view/' . $id);
        }
    }

    /**
     * Display appointment calendar
     */
    public function calendar(): void
    {
        try {
            $userId = SessionManager::get('user_id');
            $role = SessionManager::get('role');
            $filters = [];
            $appointments = []; // Initialize $appointments

            if ($role === 'admin') {
                // Admins see all appointments (or could be filtered by date range in future)
                $filters = [];
            } elseif ($role === 'doctor') {
                $doctorId = $this->userModel->getDoctorIdByUserId($userId);
                if ($doctorId) {
                    $filters = ['doctor_id' => $doctorId];
                } else {
                    $this->setFlashMessage('warning', 'Could not retrieve your staff details to show appointments.');
                    $filters = ['doctor_id' => 0]; // Ensure no appointments are fetched if staff record not found
                }
            } elseif ($role === 'patient') {
                // Attempt to get patient_id using the user_id
                $patient = $this->patientModel->getByUserId($userId);
                if ($patient && isset($patient['patient_id'])) {
                    $filters = ['patient_id' => $patient['patient_id']];
                } else {
                    // Patient record not found for this user_id, or patient_id missing
                    $this->setFlashMessage('warning', 'Could not retrieve your patient details to show appointments.');
                    $filters = ['patient_id' => 0]; // A non-existent patient_id to ensure no results if we proceed to fetch
                }
            } else {
                // Default: Other roles or if specific ID not found
                $this->setFlashMessage('info', 'Calendar view is not configured for your role or specific data is missing.');
                $filters = ['status' => 'non_existent_status_to_fetch_nothing']; // Ensure no results if we proceed to fetch
            }

            // Only fetch if we intend to (i.e., filters are not set to explicitly fetch nothing)
            $fetchAppointments = false;
            if ($role === 'admin' && $filters === []) { // Admin wants all
                $fetchAppointments = true;
            } elseif (isset($filters['doctor_id']) || (isset($filters['patient_id']) && $filters['patient_id'] !== 0)) {
                // Doctor or Patient with valid ID
                $fetchAppointments = true;
            }

            if ($fetchAppointments) {
                $appointments = $this->appointmentModel->getAppointmentsFiltered($filters);
            }

            $calendarEvents = [];
            if (!empty($appointments)) { // Check if $appointments is not empty before iterating
                foreach ($appointments as $appointment) {
                    $color = '#3788d8'; // Default color (e.g., for 'scheduled')

                    switch (strtolower($appointment['status'] ?? '')) { // Ensure case-insensitivity and handle null status
                        case 'completed':
                            $color = '#28a745'; // Green
                            break;
                        case 'cancelled':
                            $color = '#dc3545'; // Red
                            break;
                        case 'pending': // Assuming 'pending' is a valid status
                            $color = '#ffc107'; // Yellow
                            break;
                    }

                    // Ensure required fields exist to prevent errors
                    $title = $appointment['patient_name'] ?? ('Appointment #' . ($appointment['appointment_id'] ?? 'N/A'));
                    $startDateTime = ($appointment['appointment_date'] ?? date('Y-m-d')) . 'T' . ($appointment['appointment_time'] ?? '00:00:00');
                    $viewUrl = $this->getBaseUrl() . '/appointments/view/' . ($appointment['appointment_id'] ?? '');

                    $calendarEvents[] = [
                        'id' => $appointment['appointment_id'] ?? null,
                        'title' => $title,
                        'start' => $startDateTime,
                        'color' => $color,
                        'url' => $viewUrl
                    ];
                }
            }

            // Set page title
            $this->pageTitle = 'Appointments Calendar - Nyalife HMS';

            // Ensure all required variables are set
            $viewData = [
                'calendarEvents' => json_encode($calendarEvents),
                'userRole' => $role,
                'baseUrl' => $this->getBaseUrl(),
                'pageTitle' => $this->pageTitle,
                'appointments' => $appointments,
                'filters' => $filters
            ];

            $this->renderView('appointments/calendar', $viewData);
        } catch (Exception $e) {
            error_log("Error in AppointmentController::calendar(): " . $e->getMessage());
            $this->handleError('Error loading calendar: ' . $e->getMessage(), $e);
        }
    }
    public function roleBasedRedirect(): void
    {
        if (!$this->auth->isLoggedIn()) {
            // Redirect to login if not logged in
            $this->setFlashMessage('warning', 'You must be logged in to view appointments');
            $this->redirect('/login');
            return;
        }

        $role = SessionManager::get('role');

        // Redirect based on user role
        match ($role) {
            'doctor' => $this->redirect('/appointments?filter=doctor'),
            'patient' => $this->redirect('/appointments?filter=patient'),
            default => $this->redirect('/appointments'),
        };
    }

    /**
     * Get appointment details by ID (AJAX)
     *
     * @param int $id Appointment ID
     */
    public function get($id): void
    {
        // Check if user is logged in
        if (!SessionManager::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $userId = SessionManager::get('user_id');
        $role = SessionManager::get('role');

        // Get appointment
        $appointment = $this->appointmentModel->getAppointmentDetails($id);

        if (!$appointment) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Appointment not found']);
            return;
        }

        // Check permissions
        if ($role === 'patient') {
            $patientId = $this->patientModel->getPatientIdByUserId($userId);
            if ($appointment['patient_id'] != $patientId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                return;
            }
        } elseif ($role === 'doctor') {
            $staffId = $this->staffModel->getStaffIdByUserId($userId);
            if ($appointment['doctor_id'] != $staffId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                return;
            }
        }

        // Log the view action
        $this->auditLogger->log([
            'user_id' => $userId,
            'action' => 'view',
            'entity_type' => 'appointment',
            'entity_id' => $id,
            'details' => 'Viewed appointment details',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);

        // Return appointment data
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $appointment]);
    }

    /**
     * Handle appointment creation form submission
     */
    public function store(): void
    {
        // Check if user is logged in
        if (!SessionManager::isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $userId = SessionManager::get('user_id');
        $role = SessionManager::get('role');

        // Validate form data
        $formData = [];
        $requiredFields = ['patient_id', 'doctor_id', 'appointment_date', 'appointment_time', 'appointment_type', 'reason'];
        $allFieldsPresent = true;

        foreach ($requiredFields as $field) {
            if (isset($_POST[$field]) && !in_array(trim((string) $_POST[$field]), ['', '0'], true)) {
                $formData[$field] = trim((string) $_POST[$field]);
            } else {
                $this->setFlashMessage('error', "Missing or empty field: " . ucfirst(str_replace('_', ' ', $field)));
                $allFieldsPresent = false;
                break;
            }
        }

        if (!$allFieldsPresent) {
            // Store form data in session to repopulate the form
            SessionManager::set('form_data', $_POST);
            $this->redirect('/appointments/create');
            return;
        }

        // Calculate end time (assuming 30-minute appointments by default)
        $duration = isset($_POST['duration']) ? intval($_POST['duration']) : 30;
        $startDateTime = $formData['appointment_date'] . ' ' . $formData['appointment_time'];
        $endDateTime = date('Y-m-d H:i:s', strtotime($startDateTime . ' + ' . $duration . ' minutes'));
        $formData['end_time'] = date('H:i:s', strtotime($endDateTime));

        // Add additional fields
        $formData['status'] = 'scheduled';
        $formData['created_by'] = $userId;
        $formData['created_at'] = date('Y-m-d H:i:s');
        $formData['updated_at'] = date('Y-m-d H:i:s');

        try {
            // Check if the time slot is available
            $isAvailable = $this->appointmentModel->isTimeSlotAvailable(
                $formData['doctor_id'],
                $formData['appointment_date'],
                $formData['appointment_time'],
                $formData['end_time']
            );

            if (!$isAvailable) {
                $this->setFlashMessage('error', 'The selected time slot is not available. Please choose another time.');
                SessionManager::set('form_data', $_POST);
                $this->redirect('/appointments/create');
                return;
            }

            // Create appointment
            $appointmentId = $this->appointmentModel->createAppointment($formData);

            if ($appointmentId) {
                // Log the action
                $this->auditLogger->log([
                    'user_id' => $userId,
                    'action' => 'create',
                    'entity_type' => 'appointment',
                    'entity_id' => $appointmentId,
                    'details' => "Created appointment #{$appointmentId} for patient #{$formData['patient_id']} with doctor #{$formData['doctor_id']}",
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);

                // Send notifications to patient and doctor
                $this->notificationService->sendAppointmentCreatedNotification($appointmentId);

                // Set success message
                $this->setFlashMessage('success', 'Appointment created successfully');

                // Redirect to the appropriate page based on user role
                if ($role === 'admin' || $role === 'receptionist') {
                    $this->redirect('/appointments');
                } else {
                    $this->redirect('/appointments/view/' . $appointmentId);
                }
            } else {
                throw new Exception('Failed to create appointment');
            }
        } catch (Exception $e) {
            // Log error
            error_log("Error creating appointment: " . $e->getMessage());

            // Set error message
            $this->setFlashMessage('error', 'Failed to create appointment: ' . $e->getMessage());

            // Store form data in session to repopulate the form
            SessionManager::set('form_data', $_POST);

            // Redirect back to create form
            $this->redirect('/appointments/create');
        }
    }

    /**
     * Update an existing appointment
     *
     * @param int $id Appointment ID
     * @return void
     */
    /**
     * Mark an appointment as completed
     *
     * @param int $id Appointment ID
     */
    public function markAsCompleted($id): void
    {
        // Check if user is logged in
        if (!SessionManager::get('user_id')) {
            http_response_code(401);
            return;
        }

        // Update appointment status
        $result = $this->appointmentModel->updateAppointment($id, [
            'status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($result) {
            // Log the action
            $this->auditLogger->log([
                'user_id' => SessionManager::get('user_id'),
                'action' => 'update',
                'entity_type' => 'appointment',
                'entity_id' => $id,
                'details' => "Marked appointment #{$id} as completed",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        }
    }

    /**
     * Cancel an appointment
     *
     * @param int $id Appointment ID
     * @return void
     */
    /**
     * Get available time slots for a doctor on a specific date
     */
    public function getAvailableSlots(): void
    {
        // Check if user is logged in
        if (!SessionManager::get('user_id')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        // Get input parameters
        $doctorId = $_GET['doctor_id'] ?? null;
        $date = $_GET['date'] ?? null;

        if (empty($doctorId) || empty($date)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Doctor ID and date are required']);
            return;
        }

        // Get available time slots
        $slots = $this->appointmentModel->getAvailableTimeSlots($doctorId, $date);

        // Log the action
        $this->auditLogger->log([
            'user_id' => SessionManager::get('user_id'),
            'action' => 'view',
            'entity_type' => 'appointment_slots',
            'entity_id' => null,
            'details' => 'Viewed available time slots for doctor ' . $doctorId . ' on ' . $date,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);

        echo json_encode([
            'success' => true,
            'data' => $slots
        ]);
    }

    /**
     * Get appointment statistics
     */
    public function stats(): void
    {
        // Check if user is logged in and is admin/staff
        if (!SessionManager::get('user_id') || !in_array(SessionManager::get('role'), ['admin', 'doctor', 'receptionist'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $userId = SessionManager::get('user_id');
        $role = SessionManager::get('role'); // Last day of current month
        $doctorId = $_GET['doctor_id'] ?? null;

        // If user is a doctor, only show their stats
        if ($role === 'doctor') {
            $doctorId = $this->staffModel->getStaffIdByUserId($userId);
        }

        // Get statistics (placeholder - implement when needed)
        $stats = [
            'total_appointments' => 0,
            'completed_appointments' => 0,
            'pending_appointments' => 0,
            'cancelled_appointments' => 0
        ];

        // Log the action
        $this->auditLogger->log([
            'user_id' => $userId,
            'action' => 'view',
            'entity_type' => 'appointment_stats',
            'entity_id' => null,
            'details' => 'Viewed appointment statistics',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);

        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Start an appointment (for doctors)
     *
     * @param int $id Appointment ID
     */
    public function start($id): void
    {
        if (!SessionManager::get('user_id')) {
            $this->redirect('/login');
            return;
        }

        $userId = SessionManager::get('user_id');
        $role = SessionManager::get('role');

        // Only doctors can start appointments
        if ($role !== 'doctor') {
            $this->setFlashMessage('error', 'Only doctors can start appointments');
            $this->redirect('/appointments');
            return;
        }

        try {
            // Get appointment details
            $appointment = $this->appointmentModel->getAppointmentDetails($id);

            if (!$appointment) {
                $this->setFlashMessage('error', 'Appointment not found');
                $this->redirect('/appointments');
                return;
            }

            // Check if this doctor owns the appointment
            $doctorId = $this->userModel->getDoctorIdByUserId($userId);
            if ($appointment['doctor_id'] != $doctorId) {
                $this->setFlashMessage('error', 'You can only start your own appointments');
                $this->redirect('/appointments');
                return;
            }

            // Update appointment status to 'in_progress'
            $result = $this->appointmentModel->updateAppointment($id, [
                'status' => 'in_progress',
                'started_at' => date('Y-m-d H:i:s')
            ]);

            if ($result) {
                // Log the action
                $this->auditLogger->log([
                    'user_id' => SessionManager::get('user_id'),
                    'action' => 'start_appointment',
                    'entity_type' => 'appointment',
                    'entity_id' => $id,
                    'description' => 'Appointment started'
                ]);

                $this->setFlashMessage('success', 'Appointment started successfully');
                $this->redirect('/appointments/view/' . $id);
            } else {
                throw new Exception('Failed to start appointment');
            }
        } catch (Exception $e) {
            ErrorHandler::logSystemError($e, __METHOD__);
            $this->setFlashMessage('error', 'Failed to start appointment: ' . $e->getMessage());
            $this->redirect('/appointments');
        }
    }

    /**
     * Check in a patient for appointment (for nurses)
     *
     * @param int $id Appointment ID
     */
    public function checkIn($id): void
    {
        if (!SessionManager::get('user_id')) {
            $this->redirect('/login');
            return;
        }

        $userId = SessionManager::get('user_id');
        $role = SessionManager::get('role');

        // Only nurses and admins can check in patients
        if (!in_array($role, ['nurse', 'admin'])) {
            $this->setFlashMessage('error', 'Only nurses and administrators can check in patients');
            $this->redirect('/appointments');
            return;
        }

        try {
            // Get appointment details
            $appointment = $this->appointmentModel->getAppointmentDetails($id);

            if (!$appointment) {
                $this->setFlashMessage('error', 'Appointment not found');
                $this->redirect('/appointments');
                return;
            }

            // Update appointment status to 'checked_in'
            $result = $this->appointmentModel->updateAppointment($id, [
                'status' => 'checked_in',
                'checked_in_at' => date('Y-m-d H:i:s'),
                'checked_in_by' => $userId
            ]);

            if ($result) {
                // Log the action
                $this->auditLogger->log([
                    'user_id' => SessionManager::get('user_id'),
                    'action' => 'check_in_patient',
                    'entity_type' => 'appointment',
                    'entity_id' => $id,
                    'description' => 'Patient checked in for appointment'
                ]);

                $this->setFlashMessage('success', 'Patient checked in successfully');
                $this->redirect('/appointments/view/' . $id);
            } else {
                throw new Exception('Failed to check in patient');
            }
        } catch (Exception $e) {
            ErrorHandler::logSystemError($e, __METHOD__);
            $this->setFlashMessage('error', 'Failed to check in patient: ' . $e->getMessage());
            $this->redirect('/appointments');
        }
    }
}
