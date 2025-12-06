<?php

/**
 * Nyalife HMS - Patient Web Controller
 *
 * Controller for patient-related web pages.
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/PatientModel.php';
require_once __DIR__ . '/../../models/AppointmentModel.php';
require_once __DIR__ . '/../../models/ConsultationModel.php';
require_once __DIR__ . '/../../models/VitalSignModel.php';
require_once __DIR__ . '/../../models/PrescriptionModel.php';

class PatientController extends WebController
{
    protected \PatientModel $patientModel;

    protected \AppointmentModel $appointmentModel;

    /** @var array */
    protected $allowedRoles = ['admin', 'doctor', 'nurse'];

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->patientModel = new PatientModel();
        $this->appointmentModel = new AppointmentModel();
        $this->pageTitle = 'Patient Management';
    }

    /**
     * List patients
     */
    public function index(): void
    {
        $searchTerm = $this->getParam('search', '');
        $page = max(1, (int)$this->getParam('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $patients = [];

        if (!empty($searchTerm)) {
            $patients = $this->patientModel->searchPatients($searchTerm, $limit, $offset);
        } else {
            $patients = $this->patientModel->getAllPatientsWithUserData($limit, $offset);
        }

        $totalPatients = $this->patientModel->count();
        $totalPages = ceil($totalPatients / $limit);

        $this->renderView('patients/index', [
            'patients' => $patients,
            'searchTerm' => $searchTerm,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalPatients' => $totalPatients
        ]);
    }

    /**
     * View patient details
     *
     * @param int $id Patient ID
     */
    public function view($id): void
    {
        try {
            $patient = $this->patientModel->getWithUserData($id);

            if (!$patient) {
                $this->setFlashMessage('error', 'Patient not found');
                $this->redirectToRoute('patients');
                return;
            }

            // Get patient's medical history
            $medicalHistoryModel = new MedicalHistoryModel();
            $medicalHistory = $medicalHistoryModel->getPatientMedicalHistory($id);

            // Get patient's appointments
            $appointments = $this->appointmentModel->getPatientAppointments($id);

            // Get patient's consultations
            $consultationModel = new ConsultationModel();
            $consultations = $consultationModel->getConsultationsByPatient($id);

            // Format consultation data for display
            foreach ($consultations as &$consultation) {
                $consultation['formatted_date'] = date('M j, Y', strtotime((string) $consultation['consultation_date']));
                $consultation['doctor_name'] = $consultation['doctor_first_name'] . ' ' . $consultation['doctor_last_name'];
                $consultation['status_class'] = $this->getStatusClass($consultation['consultation_status'] ?? 'completed');
                $consultation['status_label'] = ucfirst(str_replace('_', ' ', $consultation['consultation_status'] ?? 'completed'));
            }

            // Get patient's vitals
            $vitalSignModel = new VitalSignModel();
            $vitals = $vitalSignModel->getVitalSignsByPatient($id);

            // Format vitals data for display
            foreach ($vitals as &$vital) {
                $vital['formatted_date'] = date('M j, Y', strtotime((string) $vital['measured_at']));
                $vital['recorded_by'] = $vital['recorded_by_name'] ?? 'Unknown';
            }

            // Get patient's prescriptions
            $prescriptionModel = new PrescriptionModel();
            $prescriptions = $prescriptionModel->getPatientPrescriptions($id);

            // Get prescription items for each prescription to calculate actual medication count
            foreach ($prescriptions as &$prescription) {
                $prescription['formatted_date'] = date('M j, Y', strtotime((string) $prescription['prescription_date']));
                // Use the concatenated doctor_name field from PrescriptionModel
                $prescription['doctor_name'] ??= 'Unknown Doctor';
                $prescription['status_class'] = $this->getStatusClass($prescription['status']);
                $prescription['status_label'] = ucfirst(str_replace('_', ' ', $prescription['status']));

                // Get actual medication count from prescription items
                $items = $prescriptionModel->getPrescriptionItems($prescription['prescription_id']);
                $prescription['medication_count'] = count($items);
                $prescription['items'] = $items;
            }

            // Get patient's lab results
            $labResults = $this->getPatientLabResults($id);

            $this->renderView('patients/view', [
                'patient' => $patient,
                'medicalHistory' => $medicalHistory,
                'appointments' => $appointments,
                'consultations' => $consultations,
                'vitals' => $vitals,
                'prescriptions' => $prescriptions,
                'labResults' => $labResults,
                'pageTitle' => 'Patient: ' . $patient['first_name'] . ' ' . $patient['last_name']
            ]);
        } catch (Exception $e) {
            error_log("Error in PatientController::view(): " . $e->getMessage());
            $this->setFlashMessage('error', 'An error occurred while loading patient details');
            $this->redirectToRoute('patients');
        }
    }

    /**
     * Show create patient form
     */
    public function create(): void
    {
        $this->renderView('patients/create', [
            'pageTitle' => 'Register New Patient'
        ]);
    }

    /**
     * Process patient creation
     */
    public function store(): void
    {
        // Validate form data
        $formData = $this->processFormData([
            'first_name', 'last_name', 'email', 'gender', 'date_of_birth', 'phone'
        ]);

        if ($formData === [] || $formData === false) {
            $this->redirectToRoute('patients/create');
            return;
        }

        // Split data into user and patient parts
        $userData = [
            'first_name' => $formData['first_name'],
            'last_name' => $formData['last_name'],
            'email' => $formData['email'],
            'username' => $formData['email'], // Use email as username
            'password' => password_hash(uniqid(), PASSWORD_DEFAULT), // Generate random password
            'gender' => $formData['gender'],
            'date_of_birth' => $formData['date_of_birth'],
            'phone' => $formData['phone'],
            'address' => $formData['address'] ?? '',
            'role_id' => 6, // Assuming 6 is patient role ID
            'is_active' => 1
                ];

        $patientData = [
            'blood_group' => $formData['blood_type'] ?? null, // Fixed: blood_type from form
            'allergies' => $formData['allergies'] ?? null,
            'chronic_diseases' => $formData['chronic_conditions'] ?? null, // Fixed: chronic_conditions from form
            'emergency_name' => $formData['emergency_contact_name'] ?? null,
            'emergency_contact' => $formData['emergency_contact_phone'] ?? null,
            'relationship' => $formData['emergency_contact_relationship'] ?? null, // Fixed: correct field name
            'occupation' => $formData['occupation'] ?? null,
            'marital_status' => $formData['marital_status'] ?? null,
            'insurance_provider' => $formData['insurance_provider'] ?? null,
            'insurance_id' => $formData['insurance_id'] ?? null
        ];

        // Create patient
        $patientId = $this->patientModel->createPatient($userData, $patientData);

        if (!$patientId) {
            $this->setFlashMessage('error', 'Failed to create patient. Please try again.');
            $this->redirectToRoute('patients/create');
            return;
        }

        // Add current medications as medical history if provided
        if (!empty($formData['current_medications'])) {
            try {
                require_once __DIR__ . '/../../models/MedicalHistoryModel.php';
                $medicalHistoryModel = new MedicalHistoryModel();

                $medicationHistory = [
                    'patient_id' => $patientId,
                    'history_type' => 'medication',
                    'description' => 'Current medications: ' . $formData['current_medications'],
                    'date_occurred' => date('Y-m-d'),
                    'is_ongoing' => 1,
                    'recorded_by' => $this->userId
                ];

                $medicalHistoryModel->addMedicalHistory($medicationHistory);
            } catch (Exception $e) {
                error_log("Failed to add medication history: " . $e->getMessage());
                // Don't fail patient creation if medication history fails
            }
        }

        $this->setFlashMessage('success', 'Patient registered successfully!');
        $this->redirect("/patients/view/$patientId");
    }

    /**
     * Show edit patient form
     *
     * @param int $id Patient ID
     */
    public function edit($id): void
    {
        $patient = $this->patientModel->getWithUserData($id);

        if (!$patient) {
            $this->setFlashMessage('error', 'Patient not found');
            $this->redirectToRoute('patients');
            return;
        }

        $this->renderView('patients/edit', [
            'patient' => $patient,
            'pageTitle' => 'Edit Patient: ' . $patient['first_name'] . ' ' . $patient['last_name']
        ]);
    }

    /**
     * Process patient update
     *
     * @param int $id Patient ID
     */
    public function update($id): void
    {
        $patient = $this->patientModel->find($id);

        if (!$patient) {
            $this->setFlashMessage('error', 'Patient not found');
            $this->redirectToRoute('patients');
            return;
        }

        // Validate form data
        $formData = $this->processFormData([
            'first_name', 'last_name', 'email', 'gender', 'date_of_birth', 'phone'
        ]);

        if ($formData === [] || $formData === false) {
            $this->redirectToRoute('patients/edit/' . $id);
            return;
        }

        // Split data into user and patient parts
        $userData = [
            'first_name' => $formData['first_name'],
            'last_name' => $formData['last_name'],
            'email' => $formData['email'],
            'gender' => $formData['gender'],
            'date_of_birth' => $formData['date_of_birth'],
            'phone' => $formData['phone'],
            'address' => $formData['address'] ?? ''
        ];

        $patientData = [
            'blood_group' => $formData['blood_type'] ?? null, // Fixed: blood_type from form
            'allergies' => $formData['allergies'] ?? null,
            'chronic_diseases' => $formData['chronic_conditions'] ?? null, // Fixed: chronic_conditions from form
            'emergency_name' => $formData['emergency_contact_name'] ?? null,
            'emergency_contact' => $formData['emergency_contact_phone'] ?? null,
            'relationship' => $formData['emergency_contact_relationship'] ?? null, // Fixed: correct field name
            'occupation' => $formData['occupation'] ?? null,
            'marital_status' => $formData['marital_status'] ?? null,
            'insurance_provider' => $formData['insurance_provider'] ?? null,
            'insurance_id' => $formData['insurance_id'] ?? null
        ];

        // Update patient
        $success = $this->patientModel->updatePatient($id, $userData, $patientData);

        if (!$success) {
            $this->setFlashMessage('error', 'Failed to update patient. Please try again.');
            $this->redirectToRoute('patients/edit/' . $id);
            return;
        }

        $this->setFlashMessage('success', 'Patient updated successfully!');
        $this->redirect("/patients/view/$id");
    }

    /**
     * Add medical history record
     *
     * @param int $id Patient ID
     */
    public function addMedicalHistory($id): void
    {
        $patient = $this->patientModel->find($id);

        if (!$patient) {
            $this->setFlashMessage('error', 'Patient not found');
            $this->redirectToRoute('patients');
            return;
        }

        // Validate form data
        $formData = $this->processFormData(['description']);

        if ($formData === [] || $formData === false) {
            $this->redirectToRoute('patients/view/' . $id);
            return;
        }

        $data = [
            'patient_id' => $id,
            'history_type' => $formData['history_type'] ?? 'illness',
            'description' => $formData['description'],
            'treatment' => $formData['treatment'] ?? null,
            'notes' => $formData['notes'] ?? null,
            'date_occurred' => date('Y-m-d'),
            'recorded_by' => SessionManager::get('user_id')
        ];

        // Add medical history using the new model
        $medicalHistoryModel = new MedicalHistoryModel();
        $success = $medicalHistoryModel->addMedicalHistory($data);

        if (!$success) {
            $this->setFlashMessage('error', 'Failed to add medical history. Please try again.');
            $this->redirectToRoute('patients/view/' . $id);
            return;
        }

        $this->setFlashMessage('success', 'Medical history added successfully!');
        $this->redirectToRoute('patients/view/' . $id);
    }

    /**
     * Get status class for styling
     */
    private function getStatusClass($status): string
    {
        return match ($status) {
            'completed', 'active' => 'badge bg-success',
            'in_progress', 'pending' => 'badge bg-warning',
            'cancelled' => 'badge bg-danger',
            'scheduled' => 'badge bg-info',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Get patient lab results
     */
    private function getPatientLabResults($patientId)
    {
        try {
            // Align with current schema: patient_id lives on lab_test_requests;
            // lab_test_items references request_id and test_type_id; date fields are performed_at / sample_reported_at
            $sql = "SELECT 
                        ti.*, 
                        lt.test_name,
                        -- Prefer verified_by name, fallback to performed_by
                        COALESCE(CONCAT(ud.first_name, ' ', ud.last_name), CONCAT(up.first_name, ' ', up.last_name)) as doctor_name,
                        COALESCE(ti.sample_reported_at, ti.performed_at, lr.completed_at, lr.request_date) as result_dt,
                        DATE_FORMAT(COALESCE(ti.sample_reported_at, ti.performed_at, lr.completed_at, lr.request_date), '%M %e, %Y') as formatted_date
                    FROM lab_test_items ti
                    JOIN lab_test_requests lr ON ti.request_id = lr.request_id
                    LEFT JOIN lab_test_types lt ON ti.test_type_id = lt.test_type_id
                    LEFT JOIN users ud ON ti.verified_by = ud.user_id
                    LEFT JOIN users up ON ti.performed_by = up.user_id
                    WHERE lr.patient_id = ? AND ti.status = 'completed'
                    ORDER BY result_dt DESC";

            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }

            $stmt->bind_param('i', $patientId);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return [];
            }

            $result = $stmt->get_result();

            $labResults = [];
            while ($row = $result->fetch_assoc()) {
                $labResults[] = $row;
            }

            return $labResults;
        } catch (Exception $e) {
            error_log("Error getting lab results: " . $e->getMessage());
            return [];
        }
    }
}
