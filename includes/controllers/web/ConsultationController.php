<?php
/**
 * Consultation Controller
 * Handles all consultation-related functionality
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/ConsultationModel.php';
require_once __DIR__ . '/../../models/AppointmentModel.php';
require_once __DIR__ . '/../../models/PatientModel.php';
require_once __DIR__ . '/../../models/LabTestModel.php';
$auditLoggerPath = realpath(__DIR__ . '/../../includes/helpers/AuditLogger.php');
if ($auditLoggerPath === false) {
    throw new \RuntimeException('AuditLogger.php not found at: ' . __DIR__ . '/../../includes/helpers/AuditLogger.php');
}
require_once $auditLoggerPath;

class ConsultationController extends WebController {
    protected $consultationModel;
    protected $appointmentModel;
    protected $patientModel;
    protected $labTestModel;
    protected $auditLogger;
    
    public function __construct() {
        parent::__construct();
        $this->consultationModel = new ConsultationModel();
        $this->appointmentModel = new AppointmentModel();
        $this->patientModel = new PatientModel();
        $this->labTestModel = new LabTestModel();
        
        // Initialize AuditLogger with the database connection from ConsultationModel
        $this->auditLogger = new AuditLogger($this->consultationModel->getDbConnection());
        
        $this->pageTitle = 'Consultations - Nyalife HMS';
        
        // Ensure user is logged in
        $this->requireLogin();
        
        // Load necessary helpers
        $this->load->helper('url');
        $this->load->library('session');
    }
    
    /**
     * Display a list of consultations
     */
    /**
     * Display a list of consultations with filters
     */
    public function index() {
        try {
            // Check if user has permission
            if (!in_array($this->userRole, ['doctor', 'nurse', 'admin'])) {
                throw new Exception('You do not have permission to view consultations');
            }
            
            // Get filter parameters
            $filters = [
                'patient_id' => $_GET['patient_id'] ?? null,
                'doctor_id' => $this->userRole === 'doctor' ? $this->userId : ($_GET['doctor_id'] ?? null),
                'status' => $_GET['status'] ?? null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null,
            ];
            
            // Validate date range if both dates are provided
            if (!empty($filters['date_from']) && !empty($filters['date_to']) && $filters['date_from'] > $filters['date_to']) {
                throw new Exception('End date cannot be before start date');
            }
            
            // Get consultations
            $consultations = $this->consultationModel->getConsultations($filters);
            
            // Add a message if no consultations found
            if (empty($consultations)) {
                $this->setFlashMessage('info', 'No consultations found matching your criteria.');
            }
            
            // Get doctors for filter (only for admins)
            $doctors = [];
            if ($this->userRole === 'admin') {
                $userModel = new UserModel();
                $doctors = $userModel->getUsersByRole('doctor');
            }
            
            // Get patients for filter (for admins and nurses)
            $patients = [];
            if ($this->userRole === 'admin' || $this->userRole === 'nurse') {
                $patients = $this->patientModel->getAllPatients();
            }
            
            // Get status options for filter
            $statusOptions = [
                'scheduled' => 'Scheduled',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled'
            ];
            
            // Render the view
            $this->renderView('consultations/index', [
                'consultations' => $consultations,
                'filters' => $filters,
                'doctors' => $doctors,
                'patients' => $patients,
                'statusOptions' => $statusOptions,
                'pageTitle' => 'Consultations',
                'userRole' => $this->userRole,
                'baseUrl' => $this->baseUrl,
                'successMessage' => $this->getFlashMessage('success'),
                'errorMessage' => $this->getFlashMessage('error'),
                'infoMessage' => $this->getFlashMessage('info')
            ]);
            
        } catch (Exception $e) {
            error_log("Error in ConsultationController::index(): " . $e->getMessage());
            $this->setFlashMessage('error', 'An error occurred while retrieving consultations: ' . $e->getMessage());
            $this->redirectToRoute('dashboard');
        }
    }
    
    /**
     * Show the form for creating a new consultation
     * 
     * @param int $appointmentId Optional appointment ID to pre-fill consultation
     */
    public function create($appointmentId = null) {
        try {
            // Check if user has permission
            if (!in_array($this->userRole, ['doctor', 'nurse', 'admin'])) {
                throw new Exception('You do not have permission to create consultations');
            }
            
            $appointment = null;
            if ($appointmentId) {
                $appointment = $this->appointmentModel->getAppointmentById($appointmentId);
                if (!$appointment) {
                    throw new Exception('Appointment not found');
                }
                
                // Check if consultation already exists for this appointment
                $existingConsultation = $this->consultationModel->getConsultationByAppointment($appointmentId);
                if ($existingConsultation) {
                    $this->redirectToRoute('consultations.view', ['id' => $existingConsultation['consultation_id']]);
                    return;
                }
            }
            
            // Get doctors for dropdown
            $userModel = new UserModel();
            $doctors = $userModel->getUsersByRole('doctor');
            
            // Get patients for dropdown (only for admins/nurses)
            $patients = [];
            if ($this->userRole === 'admin' || $this->userRole === 'nurse') {
                $patients = $this->patientModel->getAllPatients();
            }
            
            $this->renderView('consultations/create', [
                'appointment' => $appointment,
                'doctors' => $doctors,
                'patients' => $patients,
                'pageTitle' => 'New Consultation',
                'baseUrl' => $this->baseUrl
            ]);
            
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('consultations.index');
        }
    }
    

    public function store() {
        try {
            // Check if user has permission
            if (!in_array($this->userRole, ['doctor', 'nurse', 'admin'])) {
                throw new Exception('You do not have permission to create consultations');
            }
            // Validate required fields
            $requiredFields = [
                'patient_id' => 'Patient',
                'doctor_id' => 'Doctor',
                'consultation_date' => 'Consultation Date',
                'chief_complaint' => 'Chief Complaint'
            ];
            
            $missingFields = [];
            foreach ($requiredFields as $field => $label) {
                if (empty($_POST[$field])) {
                    $missingFields[] = $label;
                }
            }
            
            if (!empty($missingFields)) {
                throw new Exception('Required fields are missing: ' . implode(', ', $missingFields));
            }
            
            // Prepare consultation data
            $consultationData = [
                'appointment_id' => $_POST['appointment_id'] ?? null,
                'patient_id' => $_POST['patient_id'],
                'doctor_id' => $_POST['doctor_id'],
                'consultation_date' => $_POST['consultation_date'],
                'chief_complaint' => $_POST['chief_complaint'],
                'history_present_illness' => $_POST['history_present_illness'] ?? null,
                'past_medical_history' => $_POST['past_medical_history'] ?? null,
                'family_history' => $_POST['family_history'] ?? null,
                'social_history' => $_POST['social_history'] ?? null,
                'obstetric_history' => $_POST['obstetric_history'] ?? null,
                'gynecological_history' => $_POST['gynecological_history'] ?? null,
                'menstrual_history' => $_POST['menstrual_history'] ?? null,
                'contraceptive_history' => $_POST['contraceptive_history'] ?? null,
                'sexual_history' => $_POST['sexual_history'] ?? null,
                'review_of_systems' => $_POST['review_of_systems'] ?? null,
                'physical_examination' => $_POST['physical_examination'] ?? null,
                'vital_signs' => [
                    'blood_pressure' => $_POST['blood_pressure'] ?? null,
                    'pulse' => !empty($_POST['pulse']) ? (int)$_POST['pulse'] : null,
                    'temperature' => !empty($_POST['temperature']) ? (float)$_POST['temperature'] : null,
                    'respiratory_rate' => !empty($_POST['respiratory_rate']) ? (int)$_POST['respiratory_rate'] : null,
                    'oxygen_saturation' => !empty($_POST['oxygen_saturation']) ? (int)$_POST['oxygen_saturation'] : null,
                    'height' => !empty($_POST['height']) ? (float)$_POST['height'] : null,
                    'weight' => !empty($_POST['weight']) ? (float)$_POST['weight'] : null,
                    'bmi' => !empty($_POST['bmi']) ? (float)$_POST['bmi'] : null,
                    'pain_level' => !empty($_POST['pain_level']) ? (int)$_POST['pain_level'] : null
                ],
                'diagnosis' => $_POST['diagnosis'] ?? null,
                'treatment_plan' => $_POST['treatment_plan'] ?? null,
                'follow_up_instructions' => $_POST['follow_up_instructions'] ?? null,
                'notes' => $_POST['notes'] ?? null,
                'consultation_status' => $_POST['consultation_status'] ?? 'open',
                'created_by' => $this->userId
            ];
            
            // Calculate BMI if height and weight are provided
            if (!empty($consultationData['vital_signs']['height'] && !empty($consultationData['vital_signs']['weight']))) {
                $heightInMeters = $consultationData['vital_signs']['height'] / 100; // Convert cm to m
                $bmi = $consultationData['vital_signs']['weight'] / ($heightInMeters * $heightInMeters);
                $consultationData['vital_signs']['bmi'] = round($bmi, 1);
            }
            
            // Start transaction
            $this->db->begin_transaction();
            
            try {
                // Create consultation
                $consultationId = $this->consultationModel->createConsultation($consultationData);
                
                if (!$consultationId) {
                    throw new Exception('Failed to create consultation');
                }
                
                // Update appointment status if appointment_id is provided
                if (!empty($consultationData['appointment_id'])) {
                    $appointmentUpdated = $this->appointmentModel->updateAppointment(
                        $consultationData['appointment_id'],
                        ['status' => 'completed']
                    );
                    
                    if (!$appointmentUpdated) {
                        error_log("Failed to update appointment #{$consultationData['appointment_id']} status to completed");
                    }
                }
                
                // Log the action
                $this->auditLogger->log(
                    $this->userId,
                    'consultation',
                    'create',
                    "Created consultation #{$consultationId}" . 
                    (!empty($consultationData['appointment_id']) ? " for appointment #{$consultationData['appointment_id']}" : '')
                );
                
                // Commit transaction
                $this->db->commit();
                
                $response = [
                    'success' => true,
                    'message' => 'Consultation created successfully',
                    'consultation_id' => $consultationId,
                    'redirect' => $this->baseUrl . '/consultations/view/' . $consultationId
                ];
                
                if ($isAjax) {
                    $this->jsonResponse($response);
                } else {
                    $this->setFlashMessage('success', $response['message']);
                    $this->redirect($response['redirect']);
                }
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            $errorMessage = 'Error creating consultation: ' . $e->getMessage();
            error_log($errorMessage);
            
            $response = [
                'success' => false,
                'message' => $errorMessage
            ];
            
            if ($isAjax) {
                $this->jsonResponse($response, 500);
            } else {
                $this->setFlashMessage('error', $response['message']);
                
                // Store form data in session to repopulate form
                if (session_status() === PHP_SESSION_ACTIVE) {
                    $_SESSION['form_data'] = $_POST;
                }
                
                // Redirect back to create form
                $redirectUrl = !empty($_POST['appointment_id']) 
                    ? $this->baseUrl . '/consultations/create/' . $_POST['appointment_id']
                    : $this->baseUrl . '/consultations/create';
                    
                $this->redirect($redirectUrl);
            }
        }
    }
    
    /**
     * Show the form for editing the specified consultation
     */
    public function edit($id) {
        // Check if user has permission
        if (!in_array($this->userRole, ['doctor', 'nurse', 'admin'])) {
            $this->setFlashMessage('error', 'You do not have permission to edit consultations');
            $this->redirect('/consultations');
            return;
        }
        
        // Get consultation details
        $consultation = $this->consultationModel->getConsultationById($id);
        
        if (!$consultation) {
            $this->setFlashMessage('error', 'Consultation not found');
            $this->redirect('/consultations');
            return;
        }
        
        // Check if user has permission to edit this consultation
        if ($this->userRole === 'doctor' && $consultation['doctor_id'] != $this->userId) {
            $this->setFlashMessage('error', 'You can only edit your own consultations');
            $this->redirect('/consultations/view/' . $id);
            return;
        }
        
        // Get patient details
        $patient = $this->patientModel->getWithUserData($consultation['patient_id']);
        
        // Get doctors list
        $doctors = $this->userModel->getUsersByRole('doctor');
        
        // Load the view
        $this->renderView('consultations/edit', [
            'consultation' => $consultation,
            'patient' => $patient,
            'doctors' => $doctors,
            'pageTitle' => 'Edit Consultation'
        ]);
    }
    
    /**
     * Update the specified consultation
     */
    public function update($id) {
        // Check if user has permission
        if (!in_array($this->userRole, ['doctor', 'nurse', 'admin'])) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'You do not have permission to update consultations'
            ], 403);
            return;
        }
        
        // Check if this is an AJAX request
        $isAjax = $this->input->is_ajax_request();
        
        try {
            // Get existing consultation
            $existingConsultation = $this->consultationModel->getConsultationById($id);
            if (!$existingConsultation) {
                throw new Exception('Consultation not found');
            }
            
            // Check if user has permission to update this consultation
            if ($this->userRole === 'doctor' && $existingConsultation['doctor_id'] != $this->userId) {
                throw new Exception('You can only update your own consultations');
            }
            
            // Validate required fields
            $requiredFields = [
                'patient_id' => 'Patient',
                'doctor_id' => 'Doctor',
                'consultation_date' => 'Consultation Date',
                'chief_complaint' => 'Chief Complaint'
            ];
            
            $missingFields = [];
            foreach ($requiredFields as $field => $label) {
                if (empty($_POST[$field])) {
                    $missingFields[] = $label;
                }
            }
            
            if (!empty($missingFields)) {
                throw new Exception('Required fields are missing: ' . implode(', ', $missingFields));
            }
            
            // Prepare consultation data
            $consultationData = [
                'patient_id' => $_POST['patient_id'],
                'doctor_id' => $_POST['doctor_id'],
                'consultation_date' => $_POST['consultation_date'],
                'chief_complaint' => $_POST['chief_complaint'],
                'history_present_illness' => $_POST['history_present_illness'] ?? null,
                'past_medical_history' => $_POST['past_medical_history'] ?? null,
                'family_history' => $_POST['family_history'] ?? null,
                'social_history' => $_POST['social_history'] ?? null,
                'obstetric_history' => $_POST['obstetric_history'] ?? null,
                'gynecological_history' => $_POST['gynecological_history'] ?? null,
                'menstrual_history' => $_POST['menstrual_history'] ?? null,
                'contraceptive_history' => $_POST['contraceptive_history'] ?? null,
                'sexual_history' => $_POST['sexual_history'] ?? null,
                'review_of_systems' => $_POST['review_of_systems'] ?? null,
                'physical_examination' => $_POST['physical_examination'] ?? null,
                'vital_signs' => [
                    'blood_pressure' => $_POST['blood_pressure'] ?? null,
                    'pulse' => !empty($_POST['pulse']) ? (int)$_POST['pulse'] : null,
                    'temperature' => !empty($_POST['temperature']) ? (float)$_POST['temperature'] : null,
                    'respiratory_rate' => !empty($_POST['respiratory_rate']) ? (int)$_POST['respiratory_rate'] : null,
                    'oxygen_saturation' => !empty($_POST['oxygen_saturation']) ? (int)$_POST['oxygen_saturation'] : null,
                    'height' => !empty($_POST['height']) ? (float)$_POST['height'] : null,
                    'weight' => !empty($_POST['weight']) ? (float)$_POST['weight'] : null,
                    'bmi' => !empty($_POST['bmi']) ? (float)$_POST['bmi'] : null,
                    'pain_level' => !empty($_POST['pain_level']) ? (int)$_POST['pain_level'] : null
                ],
                'diagnosis' => $_POST['diagnosis'] ?? null,
                'treatment_plan' => $_POST['treatment_plan'] ?? null,
                'follow_up_instructions' => $_POST['follow_up_instructions'] ?? null,
                'notes' => $_POST['notes'] ?? null,
                'consultation_status' => $_POST['consultation_status'] ?? 'open'
            ];
            
            // Calculate BMI if height and weight are provided
            if (!empty($consultationData['vital_signs']['height'] && !empty($consultationData['vital_signs']['weight']))) {
                $heightInMeters = $consultationData['vital_signs']['height'] / 100; // Convert cm to m
                $bmi = $consultationData['vital_signs']['weight'] / ($heightInMeters * $heightInMeters);
                $consultationData['vital_signs']['bmi'] = round($bmi, 1);
            }
            
            // Start transaction
            $this->db->begin_transaction();
            
            try {
                // Update consultation
                $updated = $this->consultationModel->updateConsultation($id, $consultationData);
                
                if (!$updated) {
                    throw new Exception('Failed to update consultation');
                }
                
                // Log the action
                $this->auditLogger->log(
                    $this->userId,
                    'consultation',
                    'update',
                    "Updated consultation #{$id}"
                );
                
                // Commit transaction
                $this->db->commit();
                
                $response = [
                    'success' => true,
                    'message' => 'Consultation updated successfully',
                    'redirect' => $this->baseUrl . '/consultations/view/' . $id
                ];
                
                if ($isAjax) {
                    $this->jsonResponse($response);
                } else {
                    $this->setFlashMessage('success', $response['message']);
                    $this->redirect($response['redirect']);
                }
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            $errorMessage = 'Error updating consultation: ' . $e->getMessage();
            error_log($errorMessage);
            
            $response = [
                'success' => false,
                'message' => $errorMessage
            ];
            
            if ($isAjax) {
                $this->jsonResponse($response, 500);
            } else {
                $this->setFlashMessage('error', $response['message']);
                
                // Store form data in session to repopulate form
                if (session_status() === PHP_SESSION_ACTIVE) {
                    $_SESSION['form_data'] = $_POST;
                }
                
                // Redirect back to edit form
                $this->redirect($this->baseUrl . '/consultations/edit/' . $id);
            }
        }
    }
    
    /**
     * View consultation details
     */
    public function view($consultationId) {
        // Get consultation details
        $consultation = $this->consultationModel->getConsultationById($consultationId);
        
        if (!$consultation) {
            $this->setFlashMessage('error', 'Consultation not found');
            $this->redirect('/appointments');
            return;
        }
        
        // Check if user has permission
        $canView = $this->userRole === 'admin' || 
                  $this->userId == $consultation['doctor_id'] || 
                  $this->userId == $consultation['patient_id'];
        
        if (!$canView) {
            $this->setFlashMessage('error', 'You do not have permission to view this consultation');
            $this->redirect('/dashboard');
            return;
        }
        
        // Get patient details
        $patient = $this->patientModel->getWithUserData($consultation['patient_id']);
        
        // Get lab tests for this consultation
        $labTests = $this->labTestModel->getTestsByConsultation($consultationId);
        
        // Parse vital signs JSON
        $vitalSigns = [];
        if (!empty($consultation['vital_signs'])) {
            $vitalSigns = json_decode($consultation['vital_signs'], true);
        }
        
        $this->renderView('consultations/view', [
            'consultation' => $consultation,
            'patient' => $patient,
            'labTests' => $labTests,
            'vitalSigns' => $vitalSigns,
            'pageTitle' => 'Consultation Details'
        ]);
    }
    
    /**
     * Print consultation details
     */
    public function print($consultationId) {
        // Similar to view but with print layout
        $consultation = $this->consultationModel->getConsultationById($consultationId);
        
        if (!$consultation) {
            $this->setFlashMessage('error', 'Consultation not found');
            $this->redirect('/appointments');
            return;
        }
        
        // Check permissions
        $canView = $this->userRole === 'admin' || 
                  $this->userId == $consultation['doctor_id'] || 
                  $this->userId == $consultation['patient_id'];
        
        if (!$canView) {
            $this->setFlashMessage('error', 'You do not have permission to view this consultation');
            $this->redirect('/dashboard');
            return;
        }
        
        $patient = $this->patientModel->getWithUserData($consultation['patient_id']);
        $vitalSigns = !empty($consultation['vital_signs']) ? json_decode($consultation['vital_signs'], true) : [];
        
        $this->renderView('consultations/print', [
            'consultation' => $consultation,
            'patient' => $patient,
            'vitalSigns' => $vitalSigns,
            'pageTitle' => 'Consultation Report',
            'print' => true
        ], 'print');
    }
}
