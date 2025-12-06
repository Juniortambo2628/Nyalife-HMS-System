<?php

/**
 * Consultation Controller
 * Handles all consultation-related functionality
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/ConsultationModel.php';
require_once __DIR__ . '/../../models/AppointmentModel.php';
require_once __DIR__ . '/../../models/PatientModel.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/LabTestModel.php';
require_once __DIR__ . '/../../helpers/AuditLogger.php';
require_once __DIR__ . '/../../core/SessionManager.php';

class ConsultationController extends WebController
{
    protected \ConsultationModel $consultationModel;

    protected \AppointmentModel $appointmentModel;

    protected \PatientModel $patientModel;

    protected \LabTestModel $labTestModel;

    protected \UserModel $userModel;

    protected \AuditLogger $auditLogger;

    /** @var bool */
    protected $requiresLogin = true;

    /** @var array */
    protected $allowedRoles = ['doctor', 'nurse', 'admin'];

    public function __construct()
    {
        parent::__construct();
        $this->consultationModel = new ConsultationModel();
        $this->appointmentModel = new AppointmentModel();
        $this->patientModel = new PatientModel();
        $this->labTestModel = new LabTestModel();
        $this->userModel = new UserModel();

        // Initialize AuditLogger with the database connection from ConsultationModel
        $this->auditLogger = new AuditLogger($this->consultationModel->getDbConnection());

        $this->pageTitle = 'Consultations - Nyalife HMS';
    }

    /**
     * Display a list of consultations
     */
    /**
     * Display a list of consultations with filters
     */
    public function index(): void
    {
        try {
            // Check if user has permission
            $userRole = $this->auth->getUserRole();
            if (!in_array($userRole, ['doctor', 'nurse', 'admin'])) {
                throw new Exception('You do not have permission to view consultations');
            }

            // Get filter parameters
            $filters = [
                'patient_id' => $_GET['patient_id'] ?? null,
                // Map doctor user_id to staff_id for filtering
                'doctor_id' => null,
                'status' => $_GET['status'] ?? null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null,
            ];

            if ($userRole === 'doctor') {
                $doctorStaffId = $this->userModel->getDoctorIdByUserId($this->auth->getUserId());
                $filters['doctor_id'] = $doctorStaffId ?: -1;
            } elseif (!empty($_GET['doctor_id'])) {
                // If admin provided a doctor user_id in filter, convert to staff_id
                $provided = (int)$_GET['doctor_id'];
                $doctorStaffId = $this->userModel->getDoctorIdByUserId($provided);
                $filters['doctor_id'] = $doctorStaffId ?: -1;
            }

            // Validate date range if both dates are provided
            if (!empty($filters['date_from']) && !empty($filters['date_to']) && $filters['date_from'] > $filters['date_to']) {
                throw new Exception('End date cannot be before start date');
            }

            // Get consultations
            $consultations = $this->consultationModel->getConsultations($filters);

            // Get doctors for filter (only for admins)
            $doctors = [];
            if ($userRole === 'admin') {
                $userModel = new UserModel();
                $doctors = $userModel->getUsersByRole('doctor');
            }

            // Get patients for filter (for admins and nurses)
            $patients = [];
            if ($userRole === 'admin' || $userRole === 'nurse') {
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
                'userRole' => $userRole,
                'baseUrl' => $this->getBaseUrl(),
                'flashMessages' => $this->getFlashMessages()
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
     * @param int|null $appointmentId Optional appointment ID to pre-fill consultation
     */
    public function create($appointmentId = null): void
    {
        try {
            // Check if user has permission
            $userRole = $this->auth->getUserRole();
            if (!in_array($userRole, ['doctor', 'nurse', 'admin'])) {
                throw new Exception('You do not have permission to create consultations');
            }

            $appointment = null;
            $selectedPatientId = null;
            $selectedDoctorId = null;

            // Handle query parameters for pre-filling
            if (isset($_GET['patient_id'])) {
                $selectedPatientId = (int)$_GET['patient_id'];
            }

            if (isset($_GET['appointment_id'])) {
                $appointmentId = (int)$_GET['appointment_id'];
            }

            $appointment = null;
            if ($appointmentId) {
                $appointment = $this->appointmentModel->getAppointmentDetails($appointmentId);
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

            // Get patients for dropdown – allow all roles that can create consultations to view all patients
            // This change ensures doctors can select any patient, not just those assigned to them.
            $patients = $this->patientModel->getAllPatients();

            // Get available appointments for dropdown (only scheduled appointments without consultations)
            $appointments = $this->appointmentModel->getAppointmentsWithoutConsultation();

            // Pre-select current doctor if user is a doctor
            $currentUserRole = SessionManager::get('role');
            $currentUserId = SessionManager::get('user_id');
            if ($currentUserRole === 'doctor') {
                $selectedDoctorId = $currentUserId;
            }

            $this->renderView('consultations/create', [
                'appointment' => $appointment,
                'doctors' => $doctors,
                'patients' => $patients,
                'appointments' => $appointments,
                'selectedPatientId' => $selectedPatientId,
                'selectedDoctorId' => $selectedDoctorId,
                'pageTitle' => 'New Consultation',
                'baseUrl' => $this->getBaseUrl()
            ]);
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirectToRoute('consultations.index');
        }
    }


    public function store(): void
    {
        try {
            // Debug - Log the POST data
            error_log("ConsultationController::store - POST data received: " . json_encode($_POST));

            // Check if user has permission
            $userRole = $this->auth->getUserRole();
            if (!in_array($userRole, ['doctor', 'nurse', 'admin'])) {
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

            if ($missingFields !== []) {
                throw new Exception('Required fields are missing: ' . implode(', ', $missingFields));
            }

            // Check if this is a walk-in consultation
            $isWalkIn = isset($_POST['is_walk_in']) && $_POST['is_walk_in'] == '1';

            // Combine date and time into datetime format
            $consultationDateTime = $_POST['consultation_date'] . ' ' . $_POST['consultation_time'];

            // Prepare consultation data
            $consultationData = [
                'appointment_id' => $isWalkIn ? null : ($_POST['appointment_id'] ?? null),
                'is_walk_in' => $isWalkIn ? 1 : 0,
                'patient_id' => $_POST['patient_id'],
                // Map posted doctor user_id to staff.staff_id for storage
                'doctor_id' => (function ($userId) {
                    $um = new UserModel();
                    $sid = $um->getDoctorIdByUserId((int)$userId);
                    return $sid ?: null;
                })($_POST['doctor_id']),
                'consultation_date' => $consultationDateTime,
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
                    'pulse' => empty($_POST['pulse']) ? null : (int)$_POST['pulse'],
                    'temperature' => empty($_POST['temperature']) ? null : (float)$_POST['temperature'],
                    'respiratory_rate' => empty($_POST['respiratory_rate']) ? null : (int)$_POST['respiratory_rate'],
                    'oxygen_saturation' => empty($_POST['oxygen_saturation']) ? null : (int)$_POST['oxygen_saturation'],
                    'height' => empty($_POST['height']) ? null : (float)$_POST['height'],
                    'weight' => empty($_POST['weight']) ? null : (float)$_POST['weight'],
                    'bmi' => empty($_POST['bmi']) ? null : (float)$_POST['bmi'],
                    'pain_level' => empty($_POST['pain_level']) ? null : (int)$_POST['pain_level']
                ],
                'diagnosis' => $_POST['diagnosis'] ?? null,
                'treatment_plan' => $_POST['treatment_plan'] ?? null,
                'follow_up_instructions' => $_POST['follow_up_instructions'] ?? null,
                'notes' => $_POST['notes'] ?? null,
                'consultation_status' => $_POST['consultation_status'] ?? 'open',
                'created_by' => $this->auth->getUserId()
            ];

            // Calculate BMI if height and weight are provided
            if ($consultationData['vital_signs']['height'] && !empty($consultationData['vital_signs']['weight'])) {
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

                // Persist vital signs into vital_signs table for patient history
                try {
                    // vital_signs is always in consultationData array, check if it has meaningful values
                    $v = $consultationData['vital_signs'];
                    // $v is always an array, check if it contains any non-null values
                    $hasVitals = (
                        !empty($v['blood_pressure']) || isset($v['pulse']) && $v['pulse'] !== 0 ||
                        !empty($v['temperature']) || isset($v['respiratory_rate']) && $v['respiratory_rate'] !== 0 ||
                        isset($v['oxygen_saturation']) && $v['oxygen_saturation'] !== 0 || !empty($v['height']) ||
                        !empty($v['weight']) || isset($v['pain_level']) && $v['pain_level'] !== 0
                    );
                    if ($hasVitals && !empty($consultationData['patient_id'])) {
                        $vsModel = new VitalSignModel();
                        $vsModel->createVitalSign([
                            'patient_id' => (int)$consultationData['patient_id'],
                            'blood_pressure' => $v['blood_pressure'] ?? null,
                            'heart_rate' => $v['pulse'] ?? null,
                            'temperature' => $v['temperature'] ?? null,
                            'respiratory_rate' => $v['respiratory_rate'] ?? null,
                            'oxygen_saturation' => $v['oxygen_saturation'] ?? null,
                            'height' => $v['height'] ?? null,
                            'weight' => $v['weight'] ?? null,
                            'bmi' => $v['bmi'] ?? null,
                            'measured_at' => $consultationData['consultation_date'], // consultation_date is always a non-falsy string
                            'recorded_by' => $this->auth->getUserId(),
                        ]);
                    }
                } catch (Exception $e) {
                    // Non-fatal, log and continue
                    ErrorHandler::logSystemError($e, __METHOD__);
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
                $this->auditLogger->log([
                    'user_id' => $this->auth->getUserId(),
                    'action' => 'consultation_created',
                    'entity_type' => 'consultation',
                    'entity_id' => $consultationId,
                    'details' => [
                        'consultation_id' => $consultationId,
                        'is_walk_in' => $isWalkIn,
                        'appointment_id' => $consultationData['appointment_id'] ?? null
                    ]
                ]);

                // Commit transaction
                $this->db->commit();

                $response = [
                    'success' => true,
                    'message' => 'Consultation created successfully',
                    'consultation_id' => $consultationId,
                    'redirect' => $this->getBaseUrl() . '/consultations/view/' . $consultationId
                ];

                // Check if this is an AJAX request
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                          strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

                if ($isAjax) {
                    $this->jsonResponse($response);
                } else {
                    $this->setFlashMessage('success', $response['message']);
                    $this->redirectToRoute('consultations.view', ['id' => $consultationId]);
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

            // Check if this is an AJAX request
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                      strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

            if ($isAjax) {
                $this->jsonResponse($response, 500);
            } else {
                $this->setFlashMessage('error', $response['message']);

                // Store form data in session to repopulate form
                if (session_status() === PHP_SESSION_ACTIVE) {
                    $_SESSION['form_data'] = $_POST;
                }

                // Redirect back to create form
                if (!empty($_POST['appointment_id'])) {
                    $this->redirectToRoute('consultations.create', ['appointment_id' => $_POST['appointment_id']]);
                } else {
                    $this->redirectToRoute('consultations.create.new');
                }
            }
        }
    }

    /**
     * Show the form for editing the specified consultation
     */
    public function edit(int $id): void
    {
        // Check if user has permission
        if (!in_array($this->auth->getUserRole(), ['doctor', 'nurse', 'admin'])) {
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
        if ($this->auth->getUserRole() === 'doctor' && $consultation['doctor_user_id'] != $this->auth->getUserId()) {
            $this->setFlashMessage('error', 'You can only edit your own consultations');
            $this->redirect('/consultations/view/' . $id);
            return;
        }

        // Get patient details
        $patient = $this->patientModel->getWithUserData($consultation['patient_id']);

        // Get doctors list
        $userModel = new UserModel();
        $doctors = $userModel->getUsersByRole('doctor');

        // Get all patients for the form
        $patients = $this->patientModel->getAllPatients();

        // Load the comprehensive edit view
        $this->renderView('consultations/edit_full', [
            'consultation' => $consultation,
            'patient' => $patient,
            'patients' => $patients,
            'doctors' => $doctors,
            'pageTitle' => 'Edit Consultation'
        ]);
    }

    /**
     * Update the specified consultation
     */
    public function update(string $id): void
    {
        // Check if user has permission
        if (!in_array($this->auth->getUserRole(), ['doctor', 'nurse', 'admin'])) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'You do not have permission to update consultations'
            ], 403);
            return;
        }

        // Check if this is an AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        try {
            // Get existing consultation
            $existingConsultation = $this->consultationModel->getConsultationById($id);
            if (!$existingConsultation) {
                throw new Exception('Consultation not found');
            }

            // Check if user has permission to update this consultation
            if ($this->auth->getUserRole() === 'doctor' && $existingConsultation['doctor_user_id'] != $this->auth->getUserId()) {
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

            if ($missingFields !== []) {
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
                    'pulse' => empty($_POST['pulse']) ? null : (int)$_POST['pulse'],
                    'temperature' => empty($_POST['temperature']) ? null : (float)$_POST['temperature'],
                    'respiratory_rate' => empty($_POST['respiratory_rate']) ? null : (int)$_POST['respiratory_rate'],
                    'oxygen_saturation' => empty($_POST['oxygen_saturation']) ? null : (int)$_POST['oxygen_saturation'],
                    'height' => empty($_POST['height']) ? null : (float)$_POST['height'],
                    'weight' => empty($_POST['weight']) ? null : (float)$_POST['weight'],
                    'bmi' => empty($_POST['bmi']) ? null : (float)$_POST['bmi'],
                    'pain_level' => empty($_POST['pain_level']) ? null : (int)$_POST['pain_level']
                ],
                'diagnosis' => $_POST['diagnosis'] ?? null,
                'treatment_plan' => $_POST['treatment_plan'] ?? null,
                'follow_up_instructions' => $_POST['follow_up_instructions'] ?? null,
                'notes' => $_POST['notes'] ?? null,
                'consultation_status' => $_POST['consultation_status'] ?? 'open'
            ];

            // Calculate BMI if height and weight are provided
            if ($consultationData['vital_signs']['height'] && !empty($consultationData['vital_signs']['weight'])) {
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

                // Upsert latest vitals record for this patient so Vitals tab reflects edits
                try {
                    // vital_signs is always in consultationData array, check if it has meaningful values
                    $v = $consultationData['vital_signs'];
                    // $v is always an array, check if it contains any non-null values
                    $hasVitals = (
                        !empty($v['blood_pressure']) || isset($v['pulse']) && $v['pulse'] !== 0 ||
                        !empty($v['temperature']) || isset($v['respiratory_rate']) && $v['respiratory_rate'] !== 0 ||
                        isset($v['oxygen_saturation']) && $v['oxygen_saturation'] !== 0 || !empty($v['height']) ||
                        !empty($v['weight']) || isset($v['pain_level']) && $v['pain_level'] !== 0
                    );
                    if ($hasVitals && !empty($consultationData['patient_id'])) {
                        $vsModel = new VitalSignModel();
                        $latest = $vsModel->getLatestVitalSignByPatient((int)$consultationData['patient_id']);
                        $payload = [
                            'blood_pressure' => $v['blood_pressure'] ?? null,
                            'heart_rate' => $v['pulse'] ?? null,
                            'temperature' => $v['temperature'] ?? null,
                            'respiratory_rate' => $v['respiratory_rate'] ?? null,
                            'oxygen_saturation' => $v['oxygen_saturation'] ?? null,
                            'height' => $v['height'] ?? null,
                            'weight' => $v['weight'] ?? null,
                            'bmi' => $v['bmi'] ?? null,
                            'measured_at' => $consultationData['consultation_date'], // consultation_date is always a non-falsy string
                            'recorded_by' => $this->auth->getUserId(),
                        ];
                        if ($latest && !empty($latest['vital_id'])) {
                            $vsModel->updateVitalSign((int)$latest['vital_id'], $payload);
                        } else {
                            $payload['patient_id'] = (int)$consultationData['patient_id'];
                            $vsModel->createVitalSign($payload);
                        }
                    }
                } catch (Exception $e) {
                    ErrorHandler::logSystemError($e, __METHOD__);
                }

                // Log the action
                $this->auditLogger->log([
                    'user_id' => $this->auth->getUserId(),
                    'action' => 'consultation_updated',
                    'entity_type' => 'consultation',
                    'entity_id' => $id,
                    'details' => [
                        'consultation_id' => $id
                    ]
                ]);

                // Commit transaction
                $this->db->commit();

                $response = [
                    'success' => true,
                    'message' => 'Consultation updated successfully',
                    'redirect' => $this->getBaseUrl() . '/consultations/view/' . $id
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

            // Check if this is an AJAX request
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                      strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

            if ($isAjax) {
                $this->jsonResponse($response, 500);
            } else {
                $this->setFlashMessage('error', $response['message']);

                // Store form data in session to repopulate form
                if (session_status() === PHP_SESSION_ACTIVE) {
                    $_SESSION['form_data'] = $_POST;
                }

                // Redirect back to edit form
                $this->redirect($this->getBaseUrl() . '/consultations/edit/' . $id);
            }
        }
    }

    /**
     * View consultation details
     */
    public function view($id): void
    {
        // Get consultation details
        $consultation = $this->consultationModel->getConsultationById($id);

        if (!$consultation) {
            $this->setFlashMessage('error', 'Consultation not found');
            $this->redirect('/appointments');
            return;
        }

        // Check if user has permission
        $canView = $this->auth->getUserRole() === 'admin' ||
                  $this->auth->getUserId() == $consultation['doctor_user_id'] ||
                  $this->auth->getUserId() == $consultation['patient_user_id'];

        if (!$canView) {
            $this->setFlashMessage('error', 'You do not have permission to view this consultation');
            $this->redirect('/dashboard');
            return;
        }

        // Get patient details
        $patient = $this->patientModel->getWithUserData($consultation['patient_id']);

        // Get lab tests for this consultation
        $labTests = $this->labTestModel->getTestsByConsultation($id);

        // Parse vital signs JSON if it's a string
        $vitalSigns = [];
        if (!empty($consultation['vital_signs'])) {
            if (is_string($consultation['vital_signs'])) {
                $vitalSigns = json_decode($consultation['vital_signs'], true);
            } elseif (is_array($consultation['vital_signs'])) {
                $vitalSigns = $consultation['vital_signs'];
            }
        }

        // Set status class and label
        $statusClass = 'bg-primary';
        $statusLabel = 'Scheduled';

        if (!empty($consultation['status'])) {
            switch ($consultation['status']) {
                case 'in_progress':
                    $statusClass = 'bg-warning text-dark';
                    $statusLabel = 'In Progress';
                    break;
                case 'completed':
                    $statusClass = 'bg-success';
                    $statusLabel = 'Completed';
                    break;
                case 'cancelled':
                    $statusClass = 'bg-danger';
                    $statusLabel = 'Cancelled';
                    break;
            }
        }

        // Get current user role
        $userRole = $this->auth->getUserRole();

        $this->renderView('consultations/view', [
            'consultation' => $consultation,
            'patient' => $patient,
            'labTests' => $labTests,
            'vitalSigns' => $vitalSigns,
            'statusClass' => $statusClass,
            'statusLabel' => $statusLabel,
            'userRole' => $userRole,
            'pageTitle' => 'Consultation Details'
        ]);
    }

    /**
     * Print consultation details
     */
    public function print(int $consultationId): void
    {
        // Similar to view but with print layout
        $consultation = $this->consultationModel->getConsultationById($consultationId);

        if (!$consultation) {
            $this->setFlashMessage('error', 'Consultation not found');
            $this->redirect('/appointments');
            return;
        }

        // Check permissions
        $canView = $this->auth->getUserRole() === 'admin' ||
                  $this->auth->getUserId() == $consultation['doctor_user_id'] ||
                  $this->auth->getUserId() == $consultation['patient_user_id'];

        if (!$canView) {
            $this->setFlashMessage('error', 'You do not have permission to view this consultation');
            $this->redirect('/dashboard');
            return;
        }

        $patient = $this->patientModel->getWithUserData($consultation['patient_id']);
        $vitalSigns = empty($consultation['vital_signs']) ? [] : json_decode((string) $consultation['vital_signs'], true);

        $this->renderView('consultations/print', [
            'consultation' => $consultation,
            'patient' => $patient,
            'vitalSigns' => $vitalSigns,
            'pageTitle' => 'Consultation Report',
            'print' => true
        ], 'print');
    }

    /**
     * Update a specific field in a consultation
     *
     * @param int $consultationId Consultation ID
     */
    public function updateField($consultationId): void
    {
        try {
            // Check permissions
            $userRole = $this->auth->getUserRole();
            if (!in_array($userRole, ['doctor', 'admin'])) {
                throw new Exception('You do not have permission to update consultations');
            }
            // Validate input
            if (!isset($_POST['field']) || !isset($_POST['value'])) {
                throw new Exception('Field and value are required');
            }

            $field = $_POST['field'];
            $value = $_POST['value'];

            // Extended allowed fields - keep in sync with model
            $allowedFields = [
                'chief_complaint', 'history_present_illness', 'past_medical_history',
                'family_history', 'social_history', 'obstetric_history', 'gynecological_history',
                'menstrual_history', 'contraceptive_history', 'sexual_history',
                'review_of_systems', 'physical_examination', 'general_examination',
                'systems_examination', 'diagnosis', 'clinical_summary',
                'treatment_plan', 'follow_up_instructions', 'notes',
                'parity', 'current_pregnancy', 'past_obstetric',
                'diagnosis_confidence', 'differential_diagnosis', 'diagnostic_plan',
                'surgical_history'
            ];

            if (!in_array($field, $allowedFields)) {
                throw new Exception('Invalid field');
            }

            // If the submitted form includes additional related fields (e.g. diagnosis edit includes confidence,
            // differential, diagnostic_plan), perform a composite update to persist all related values at once.
            $compositeKeys = ['diagnosis_confidence', 'differential_diagnosis', 'diagnostic_plan', 'general_examination', 'systems_examination', 'physical_examination', 'clinical_summary'];

            $hasComposite = false;
            $updateData = [];

            // Always include the primary field/value
            $updateData[$field] = $value;

            foreach ($compositeKeys as $k) {
                if (isset($_POST[$k])) {
                    $hasComposite = true;
                    $updateData[$k] = $_POST[$k];
                }
            }

            if ($hasComposite) {
                // Use updateConsultation to handle multiple fields and timestamping
                $success = $this->consultationModel->updateConsultation($consultationId, $updateData);
            } else {
                // Single field update
                $success = $this->consultationModel->updateField($consultationId, $field, $value);
            }

            if ($success) {
                // If AJAX request, return JSON
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                          strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

                if ($isAjax) {
                    // Return formatted values for UI insertion
                    $formatted = $this->formatUpdatedFields($hasComposite ? $updateData : [$field => $value]);
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Field updated successfully',
                        'updated' => $formatted
                    ]);
                    return;
                }

                $this->setFlashMessage('success', 'Field updated successfully');
            } else {
                throw new Exception('Failed to update field');
            }
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
        }

        $this->redirect("/consultations/view/$consultationId");
    }

    /**
     * Update vital signs in a consultation
     *
     * @param int $consultationId Consultation ID
     */
    public function updateVitals(int $consultationId): void
    {
        try {
            // Check permissions
            $userRole = $this->auth->getUserRole();
            if (!in_array($userRole, ['doctor', 'admin'])) {
                throw new Exception('You do not have permission to update consultations');
            }

            // Prepare vital signs data
            $vitalSigns = [
                'blood_pressure' => $_POST['blood_pressure'] ?? null,
                'pulse' => empty($_POST['pulse']) ? null : (int)$_POST['pulse'],
                'temperature' => empty($_POST['temperature']) ? null : (float)$_POST['temperature'],
                'respiratory_rate' => empty($_POST['respiratory_rate']) ? null : (int)$_POST['respiratory_rate'],
                'oxygen_saturation' => empty($_POST['oxygen_saturation']) ? null : (int)$_POST['oxygen_saturation'],
                'height' => empty($_POST['height']) ? null : (float)$_POST['height'],
                'weight' => empty($_POST['weight']) ? null : (float)$_POST['weight'],
                'bmi' => empty($_POST['bmi']) ? null : (float)$_POST['bmi'],
                'pain_level' => empty($_POST['pain_level']) ? null : (int)$_POST['pain_level']
            ];

            // Update vital signs
            $success = $this->consultationModel->updateVitalSigns($consultationId, $vitalSigns);

            if ($success) {
                // If AJAX request, return JSON with updated vitals
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                          strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

                if ($isAjax) {
                    // Fetch latest vitals from model
                    $consult = $this->consultationModel->getConsultationById($consultationId);
                    $vitals = [];
                    if (!empty($consult['vital_signs'])) {
                        $vitals = is_string($consult['vital_signs']) ? json_decode($consult['vital_signs'], true) : $consult['vital_signs'];
                    }
                    // Return both raw and formatted vitals
                    $formattedVitals = $this->formatVitalsForJson($vitals);
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Vital signs updated successfully',
                        'vitals' => $formattedVitals,
                        'vitals_raw' => $vitals
                    ]);
                    return;
                }

                $this->setFlashMessage('success', 'Vital signs updated successfully');
            } else {
                throw new Exception('Failed to update vital signs');
            }
        } catch (Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
        }

        $this->redirect("/consultations/view/$consultationId");
    }

    /**
     * Send a JSON response
     *
     * @param array $data Data to send as JSON
     * @param int $statusCode HTTP status code
     */
    protected function jsonResponse($data, $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Format updated fields for JSON responses (include units, badges, etc.)
     */
    protected function formatUpdatedFields(array $fields): array
    {
        $formatted = [];
        foreach ($fields as $k => $v) {
            switch ($k) {
                case 'temperature':
                    $formatted[$k] = $v !== null && $v !== '' ? htmlspecialchars((string) $v) . ' °C' : '<span class="text-muted">Not recorded</span>';
                    break;
                case 'pulse':
                case 'heart_rate':
                    $formatted['pulse'] = $v !== null && $v !== '' ? htmlspecialchars((string) $v) . ' bpm' : '<span class="text-muted">Not recorded</span>';
                    break;
                case 'blood_pressure':
                    $formatted[$k] = $v !== null && $v !== '' ? htmlspecialchars((string) $v) . ' mmHg' : '<span class="text-muted">Not recorded</span>';
                    break;
                case 'oxygen_saturation':
                    $formatted[$k] = $v !== null && $v !== '' ? htmlspecialchars((string) $v) . '%' : '<span class="text-muted">Not recorded</span>';
                    break;
                case 'pain_level':
                    $formatted[$k] = $v !== null && $v !== '' ? htmlspecialchars((string) $v) . '/10' : '<span class="text-muted">Not recorded</span>';
                    break;
                case 'prescription_status':
                    // map to semantic badge classes (styling in CSS)
                    $status = $v ?: 'pending';
                    switch ($status) {
                        case 'pending':
                            $label = 'Pending';
                            $statusClass = 'badge-status-pending';
                            break;
                        case 'dispensed':
                            $label = 'Dispensed';
                            $statusClass = 'badge-status-dispensed';
                            break;
                        case 'partially_dispensed':
                            $label = 'Partially Dispensed';
                            $statusClass = 'badge-status-partial';
                            break;
                        case 'cancelled':
                            $label = 'Cancelled';
                            $statusClass = 'badge-status-cancelled';
                            break;
                        default:
                            $label = htmlspecialchars((string) $status);
                            $statusClass = 'badge-status-default';
                            break;
                    }
                    $formatted[$k] = '<span class="badge ' . $statusClass . '">' . $label . '</span>';
                    break;
                default:
                    $formatted[$k] = htmlspecialchars((string)$v);
                    break;
            }
        }
        return $formatted;
    }

    /**
     * Create formatted vitals array mapping keys to display-ready strings
     */
    protected function formatVitalsForJson(array $vitals): array
    {
        $out = [];
        if (isset($vitals['blood_pressure'])) {
            $out['blood_pressure'] = $vitals['blood_pressure'] ? htmlspecialchars((string) $vitals['blood_pressure']) . ' mmHg' : '<span class="text-muted">Not recorded</span>';
        }
        if (isset($vitals['pulse'])) {
            $out['pulse'] = $vitals['pulse'] ? htmlspecialchars((string) $vitals['pulse']) . ' bpm' : '<span class="text-muted">Not recorded</span>';
        }
        if (isset($vitals['temperature'])) {
            $out['temperature'] = $vitals['temperature'] ? htmlspecialchars((string) $vitals['temperature']) . ' °C' : '<span class="text-muted">Not recorded</span>';
        }
        if (isset($vitals['respiratory_rate'])) {
            $out['respiratory_rate'] = $vitals['respiratory_rate'] ? htmlspecialchars((string) $vitals['respiratory_rate']) . ' br/min' : '<span class="text-muted">Not recorded</span>';
        }
        if (isset($vitals['oxygen_saturation'])) {
            $out['oxygen_saturation'] = $vitals['oxygen_saturation'] ? htmlspecialchars((string) $vitals['oxygen_saturation']) . '%' : '<span class="text-muted">Not recorded</span>';
        }
        if (isset($vitals['pain_level']) && $vitals['pain_level'] !== null) {
            $out['pain_level'] = htmlspecialchars((string) $vitals['pain_level']) . '/10';
        } else {
            $out['pain_level'] = '<span class="text-muted">Not recorded</span>';
        }
        if (isset($vitals['height'])) {
            $out['height'] = $vitals['height'] ? htmlspecialchars((string) $vitals['height']) . ' cm' : '<span class="text-muted">Not recorded</span>';
        }
        if (isset($vitals['weight'])) {
            $out['weight'] = $vitals['weight'] ? htmlspecialchars((string) $vitals['weight']) . ' kg' : '<span class="text-muted">Not recorded</span>';
        }
        if (isset($vitals['bmi'])) {
            $out['bmi'] = $vitals['bmi'] ? htmlspecialchars((string) $vitals['bmi']) : '<span class="text-muted">Not recorded</span>';
        }
        return $out;
    }
}
