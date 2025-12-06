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

class AppointmentController extends WebController {
    protected $appointmentModel;
    protected $userModel;
    protected $patientModel;
    protected $staffModel;
    protected $auditLogger;
    
    /**
     * Initialize the controller
     */
    public function __construct() {
        parent::__construct();
        // Initialize models first
        $this->appointmentModel = new AppointmentModel();
        $this->userModel = new UserModel();
        $this->patientModel = new PatientModel();
        $this->staffModel = new StaffModel();
        
        // Now initialize audit logger with a database connection
        $this->auditLogger = new AuditLogger($this->appointmentModel->getDbConnection());
        
        $this->pageTitle = 'Appointments - Nyalife HMS';
        
        // Load necessary helpers (Framework might autoload or use a different mechanism)
        // $this->load->helper('url');
        // $this->load->library('session');
        // $this->load->helper('form');
        // $this->load->helper('security');
    }
    
    /**
     * Display all appointments
     * 
     * @return void
     */
    public function index() {
        // Check if user is logged in
        if (!SessionManager::get('user_id')) {
            redirect('auth/login');
            return;
        }
        
        $userId = SessionManager::get('user_id');
        $role = SessionManager::get('role');
        
        // Get filter parameters
        $filterDate = $_GET['filter_date'] ?? null;
        $filterDoctor = $_GET['filter_doctor'] ?? null;
        $filterPatient = $_GET['filter_patient'] ?? null;
        $filterStatus = $_GET['filter_status'] ?? null;
        
        // Get appointments based on user role and filters
        $params = [
            'date' => $filterDate,
            'doctor_id' => $filterDoctor,
            'patient_id' => $filterPatient,
            'status' => $filterStatus
        ];
        
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

        if ($role === 'admin' || $role === 'receptionist') { // Assuming receptionist also sees filtered list
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
        
        // Get doctors for filter dropdown
        $doctors = $this->userModel->getAllDoctors();
        
        // Get patients for filter dropdown (only for admin and receptionist)
        $patients = [];
        if (in_array($role, ['admin', 'receptionist'])) {
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
     * 
     * @return void
     */
    public function create() {
        // Check if this is an AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        // Check if user is logged in
        if (!SessionManager::get('user_id')) {
            if ($isAjax) {
                $this->output->set_status_header(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            } else {
                $this->redirect('/auth/login');
                return;
            }
        }
        
        $userId = SessionManager::get('user_id');
        $role = SessionManager::get('role');
        
        // Handle form submission (POST request)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle AJAX submission
            if ($isAjax) {
                // Validate input
                $this->load->library('form_validation');
                $this->form_validation->set_rules('patient_id', 'Patient', 'required|numeric');
                $this->form_validation->set_rules('doctor_id', 'Doctor', 'required|numeric');
                $this->form_validation->set_rules('appointment_date', 'Date', 'required');
                $this->form_validation->set_rules('appointment_time', 'Time', 'required');
                $this->form_validation->set_rules('appointment_type', 'Appointment Type', 'required');
                
                if ($this->form_validation->run() === FALSE) {
                    $this->output->set_status_header(400);
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Validation failed',
                        'errors' => $this->form_validation->error_array()
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
                    
                    // TODO: Send notifications to patient and doctor
                    
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
                    if (isset($_POST[$field]) && !empty(trim($_POST[$field]))) {
                        $formData[$field] = trim($_POST[$field]); // Trim whitespace
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
        
        $this->renderView('appointments/create', [
            'doctors' => $doctors,
            'patients' => $patients,
            'selectedPatientId' => $selectedPatientId,
            'userRole' => $role
        ]);
    }
    
    /**
     * Display appointment details
     * 
     * @param int $id Appointment ID
     * @return void
     */
    public function view($id) {
        // Get appointment details
        $appointment = $this->appointmentModel->getAppointmentWithDetails($id);
        
        if (!$appointment) {
            $this->setFlashMessage('error', 'Appointment not found');
            $this->redirect('/appointments');
            return;
        }
        
        // Get medical history for this appointment
        $medicalHistory = $this->appointmentModel->getMedicalHistory($id);
        
        // Get prescriptions for this appointment
        $prescriptions = $this->appointmentModel->getPrescriptions($id);
        
        // Get lab tests for this appointment
        $labTests = $this->appointmentModel->getLabTests($id);
        
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
     * @return void
     */
    public function edit($id) {
        // Get appointment details
        $appointment = $this->appointmentModel->getAppointmentDetails($id);
        
        if (!$appointment) {
            $this->setFlashMessage('error', 'Appointment not found');
            $this->redirect('/appointments');
            return;
        }
        
        // Get all doctors for selection
        $doctors = $this->userModel->getAllDoctors();
        
        // Get patient details
        $patient = $this->patientModel->getWithUserData($appointment['patient_id']);
        
        $this->renderView('appointments/edit', [
            'appointment' => $appointment,
            'doctors' => $doctors,
            'patient' => $patient,
            'userRole' => SessionManager::get('role')
        ]);
    }
    
    /**
     * Update an appointment
     * 
     * @param int $id Appointment ID
     * @return void
     */
    public function update($id) {
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
            $formData['appointment_date'] = trim($_POST['appointment_date']);
        }
        if (isset($_POST['appointment_time']) && $_POST['appointment_time'] !== '') {
            $formData['appointment_time'] = trim($_POST['appointment_time']);
        }
        if (isset($_POST['status']) && $_POST['status'] !== '') {
            $formData['status'] = trim($_POST['status']);
        }
        
        if (isset($_POST['reason']) && $_POST['reason'] !== '') {
            $formData['notes'] = trim($_POST['reason']);
        } elseif (isset($_POST['notes']) && $_POST['notes'] !== '') {
             $formData['notes'] = trim($_POST['notes']);
        }

        if (empty($formData)) {
            $this->setFlashMessage('info', 'No changes submitted for update.');
            $this->redirect('/appointments/edit/' . $id);
            return;
        }
        
        $userId = SessionManager::get('user_id');
        if ($userId) {
            $formData['updated_by'] = $userId; 
        }

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
     * @return void
     */
    public function cancel($id) {
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
            'updated_by' => $userId, // Also set updated_by for consistency with updateAppointment model method
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
     * @return void
     */
    public function addMedicalHistory($id) {
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
            if (isset($_POST[$field]) && !empty(trim($_POST[$field]))) {
                $historyData[$field] = trim(htmlspecialchars($_POST[$field])); // Basic sanitization
            } else {
                $missingFields[] = ucfirst($field);
            }
        }

        if (!empty($missingFields)) {
            $this->setFlashMessage('error', 'Missing required medical history fields: ' . implode(', ', $missingFields) . '.');
            $this->redirect('/appointments/view/' . $id); // Or back to a specific form page if one exists
            return;
        }

        // Optional notes field
        if (isset($_POST['notes'])) {
            $historyData['notes'] = trim(htmlspecialchars($_POST['notes'])); // Basic sanitization
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
            $historyId = $this->appointmentModel->addMedicalHistory($historyData);
            
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
                    'updated_by' => $userId,
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
     * 
     * @return void
     */
    public function calendar() {
        $userId = SessionManager::get('user_id');
        $role = SessionManager::get('role');
        $filters = [];
        $appointments = []; // Initialize $appointments

        if ($role === 'admin') {
            // Admins see all appointments (or could be filtered by date range in future)
            $filters = []; 
        } elseif ($role === 'doctor') {
            $staffId = $this->staffModel->getStaffIdByUserId($userId);
            if ($staffId) {
                $filters = ['doctor_id' => $staffId];
            } else {
                $this->setFlashMessage('warning', 'Could not retrieve your staff details to show appointments.');
                $filters = ['doctor_id' => 0]; // Ensure no appointments are fetched if staff record not found
            }
        } elseif ($role === 'patient') {
            // Attempt to get patient_id using the user_id
            // This relies on PatientModel::getPatientByUserId() which needs to be implemented
            $patient = $this->patientModel->getByUserId($userId); 
            if ($patient && isset($patient['patient_id'])) {
                $filters = ['patient_id' => $patient['patient_id']];
            } else {
                // Patient record not found for this user_id, or patient_id missing
                $this->setFlashMessage('warning', 'Could not retrieve your patient details to show appointments.');
                // $appointments is already initialized to [], so no need to set it again
                $filters = ['patient_id' => 0]; // A non-existent patient_id to ensure no results if we proceed to fetch
            }
        } else {
            // Default: Other roles or if specific ID not found
            $this->setFlashMessage('info', 'Calendar view is not configured for your role or specific data is missing.');
            // $appointments is already initialized to []
            $filters = ['status' => 'non_existent_status_to_fetch_nothing']; // Ensure no results if we proceed to fetch
        }

        // Only fetch if we intend to (i.e., filters are not set to explicitly fetch nothing)
        // Admin with empty filters means fetch all.
        // For other roles, if filters were set to 'patient_id' => 0 or 'status' => 'non_existent...',
        // we effectively want an empty $appointments array, which is already initialized.
        // So, we fetch if ($role === 'admin' && empty($filters)) OR if filters are set meaningfully.
        
        $fetchAppointments = false;
        if ($role === 'admin' && empty($filters)) { // Admin wants all
            $fetchAppointments = true;
        } elseif (isset($filters['doctor_id']) || (isset($filters['patient_id']) && $filters['patient_id'] !== 0)) {
            // Doctor or Patient with valid ID
            $fetchAppointments = true;
        }
        // If $filters contains 'status' => 'non_existent_status_to_fetch_nothing' or 'patient_id' => 0, 
        // $fetchAppointments remains false, and $appointments remains [].

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
        
        $this->renderView('appointments/calendar', [
            'calendarEvents' => json_encode($calendarEvents),
            'userRole' => $role
        ]);
    }
    public function roleBasedRedirect() {
        if (!$this->auth->isLoggedIn()) {
            // Redirect to login if not logged in
            $this->setFlashMessage('warning', 'You must be logged in to view appointments');
            $this->redirect('/login');
            return;
        }
        
        $role = SessionManager::get('role');
        
        // Redirect based on user role
        switch ($role) {
            case 'doctor':
                $this->redirect('/appointments?filter=doctor');
                break;
            case 'patient':
                $this->redirect('/appointments?filter=patient');
                break;
            case 'admin':
            case 'nurse':
            case 'lab_technician':
            case 'pharmacist':
            default:
                // For other roles, redirect to the main appointments page
                $this->redirect('/appointments');
                break;
        }
    }
    
    /**
     * Get appointment details by ID (AJAX)
     * 
     * @param int $id Appointment ID
     * @return void
     */
    public function get($id) {
        // Check if user is logged in
        if (!SessionManager::get('user_id')) {
            $this->output->set_status_header(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $userId = SessionManager::get('user_id');
        $role = SessionManager::get('role');
        
        // Get appointment
        $appointment = $this->appointmentModel->getAppointmentById($id);
        
        if (!$appointment) {
            $this->output->set_status_header(404);
            echo json_encode(['success' => false, 'message' => 'Appointment not found']);
            return;
        }
        
        // Check permissions
        if ($role === 'patient') {
            $patientId = $this->patientModel->getPatientIdByUserId($userId);
            if ($appointment['patient_id'] != $patientId) {
                $this->output->set_status_header(403);
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                return;
            }
        } elseif ($role === 'doctor') {
            $staffId = $this->staffModel->getStaffIdByUserId($userId);
            if ($appointment['doctor_id'] != $staffId) {
                $this->output->set_status_header(403);
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
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent()
        ]);
        
        // Return appointment data
        $this->output->set_content_type('application/json');
        echo json_encode(['success' => true, 'data' => $appointment]);
    }
    
    /**
     * Create a new appointment
     * 
     * @return void
     */

    
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
     * @return bool True on success, false on failure
     */
    public function markAsCompleted($id) {
        // Check if user is logged in
        if (!SessionManager::get('user_id')) {
            http_response_code(401);
            return false;
        }
        
        // Update appointment status
        $result = $this->appointmentModel->updateAppointment($id, [
            'status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            // Log the action
            $this->auditLogger->log(
                SessionManager::get('user_id'),
                'appointment',
                'update',
                "Marked appointment #{$id} as completed"
            );
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Cancel an appointment
     * 
     * @param int $id Appointment ID
     * @return void
     */

    
    /**
     * Get available time slots for a doctor on a specific date
     * 
     * @return void
     */
    public function getAvailableSlots() {
        // Check if user is logged in
        if (!SessionManager::get('user_id')) {
            $this->output->set_status_header(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        // Get input parameters
        $doctorId = $this->input->get('doctor_id');
        $date = $this->input->get('date');
        
        if (empty($doctorId) || empty($date)) {
            $this->output->set_status_header(400);
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
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent()
        ]);
        
        echo json_encode([
            'success' => true, 
            'data' => $slots
        ]);
    }
    
    /**
     * Get appointment statistics
     * 
     * @return void
     */
    public function stats() {
        // Check if user is logged in and is admin/staff
        if (!SessionManager::get('user_id') || !in_array(SessionManager::get('role'), ['admin', 'doctor', 'receptionist'])) {
            $this->output->set_status_header(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        $userId = SessionManager::get('user_id');
        $role = SessionManager::get('role');
        
        // Get filter parameters
        $startDate = $this->input->get('start_date') ?: date('Y-m-01'); // First day of current month
        $endDate = $this->input->get('end_date') ?: date('Y-m-t'); // Last day of current month
        $doctorId = $this->input->get('doctor_id');
        
        // If user is a doctor, only show their stats
        if ($role === 'doctor') {
            $doctorId = $this->staffModel->getStaffIdByUserId($userId);
        }
        
        // Get statistics
        $stats = $this->appointmentModel->getAppointmentStats($startDate, $endDate, $doctorId);
        
        // Log the action
        $this->auditLogger->log([
            'user_id' => $userId,
            'action' => 'view',
            'entity_type' => 'appointment_stats',
            'entity_id' => null,
            'details' => 'Viewed appointment statistics',
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent()
        ]);
        
        echo json_encode([
            'success' => true, 
            'data' => $stats
        ]);
    }
}
