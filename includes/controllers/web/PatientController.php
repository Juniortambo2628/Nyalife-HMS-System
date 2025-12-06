<?php
/**
 * Nyalife HMS - Patient Web Controller
 * 
 * Controller for patient-related web pages.
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/PatientModel.php';
require_once __DIR__ . '/../../models/AppointmentModel.php';

class PatientController extends WebController {
    protected $patientModel;
    protected $appointmentModel;
    protected $allowedRoles = ['admin', 'doctor', 'nurse'];
    
    /**
     * Initialize the controller
     */
    public function __construct() {
        parent::__construct();
        $this->patientModel = new PatientModel();
        $this->appointmentModel = new AppointmentModel();
        $this->pageTitle = 'Patient Management';
    }
    
    /**
     * List patients
     * 
     * @return void
     */
    public function index() {
        $searchTerm = $this->getParam('search', '');
        $page = max(1, (int)$this->getParam('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $patients = [];
        
        if (!empty($searchTerm)) {
            $patients = $this->patientModel->searchPatients($searchTerm, $limit, $offset);
        } else {
            $patients = $this->patientModel->findAll([], 'patient_id DESC', $limit, $offset);
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
     * @return void
     */
    public function view($id) {
        $patient = $this->patientModel->getWithUserData($id);
        
        if (!$patient) {
            $this->setFlashMessage('error', 'Patient not found');
            $this->redirectToRoute('patients');
            return;
        }
        
        // Get patient's medical history
        $medicalHistory = $this->patientModel->getMedicalHistory($id);
        
        // Get patient's appointments
        $appointments = $this->appointmentModel->getPatientAppointments($id);
        
        $this->renderView('patients/view', [
            'patient' => $patient,
            'medicalHistory' => $medicalHistory,
            'appointments' => $appointments,
            'pageTitle' => 'Patient: ' . $patient['first_name'] . ' ' . $patient['last_name']
        ]);
    }
    
    /**
     * Show create patient form
     * 
     * @return void
     */
    public function create() {
        $this->renderView('patients/create', [
            'pageTitle' => 'Register New Patient'
        ]);
    }
    
    /**
     * Process patient creation
     * 
     * @return void
     */
    public function store() {
        // Validate form data
        $formData = $this->processFormData([
            'first_name', 'last_name', 'email', 'gender', 'date_of_birth', 'phone'
        ]);
        
        if (!$formData) {
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
            'blood_group' => $formData['blood_group'] ?? null,
            'allergies' => $formData['allergies'] ?? null,
            'medical_conditions' => $formData['medical_conditions'] ?? null,
            'emergency_contact_name' => $formData['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $formData['emergency_contact_phone'] ?? null,
            'registration_date' => date('Y-m-d')
        ];
        
        // Create patient
        $patientId = $this->patientModel->createPatient($userData, $patientData);
        
        if (!$patientId) {
            $this->setFlashMessage('error', 'Failed to create patient. Please try again.');
            $this->redirectToRoute('patients/create');
            return;
        }
        
        $this->setFlashMessage('success', 'Patient registered successfully!');
        $this->redirectToRoute('patients/view/' . $patientId);
    }
    
    /**
     * Show edit patient form
     * 
     * @param int $id Patient ID
     * @return void
     */
    public function edit($id) {
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
     * @return void
     */
    public function update($id) {
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
        
        if (!$formData) {
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
            'blood_group' => $formData['blood_group'] ?? null,
            'allergies' => $formData['allergies'] ?? null,
            'medical_conditions' => $formData['medical_conditions'] ?? null,
            'emergency_contact_name' => $formData['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $formData['emergency_contact_phone'] ?? null
        ];
        
        // Update patient
        $success = $this->patientModel->updatePatient($id, $userData, $patientData);
        
        if (!$success) {
            $this->setFlashMessage('error', 'Failed to update patient. Please try again.');
            $this->redirectToRoute('patients/edit/' . $id);
            return;
        }
        
        $this->setFlashMessage('success', 'Patient updated successfully!');
        $this->redirectToRoute('patients/view/' . $id);
    }
    
    /**
     * Add medical history record
     * 
     * @param int $id Patient ID
     * @return void
     */
    public function addMedicalHistory($id) {
        $patient = $this->patientModel->find($id);
        
        if (!$patient) {
            $this->setFlashMessage('error', 'Patient not found');
            $this->redirectToRoute('patients');
            return;
        }
        
        // Validate form data
        $formData = $this->processFormData(['description']);
        
        if (!$formData) {
            $this->redirectToRoute('patients/view/' . $id);
            return;
        }
        
        $data = [
            'patient_id' => $id,
            'description' => $formData['description'],
            'diagnosis' => $formData['diagnosis'] ?? null,
            'treatment' => $formData['treatment'] ?? null,
            'notes' => $formData['notes'] ?? null,
            'date_recorded' => date('Y-m-d H:i:s'),
            'created_by' => $this->auth->getUserId()
        ];
        
        // Add medical history
        $success = $this->patientModel->addMedicalHistory($data);
        
        if (!$success) {
            $this->setFlashMessage('error', 'Failed to add medical history. Please try again.');
            $this->redirectToRoute('patients/view/' . $id);
            return;
        }
        
        $this->setFlashMessage('success', 'Medical history added successfully!');
        $this->redirectToRoute('patients/view/' . $id);
    }
}
