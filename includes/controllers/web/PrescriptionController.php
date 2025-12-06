<?php
/**
 * Nyalife HMS - Prescription Web Controller
 * 
 * Handles all prescription related web requests
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/PrescriptionModel.php';
require_once __DIR__ . '/../../models/PatientModel.php';
require_once __DIR__ . '/../../models/MedicationModel.php';

class PrescriptionController extends WebController {
    private $prescriptionModel;
    private $patientModel;
    private $medicationModel;
    
    public function __construct() {
        parent::__construct();
        $this->prescriptionModel = new PrescriptionModel();
        $this->patientModel = new PatientModel();
        $this->medicationModel = new MedicationModel();
        
        // Check if user is logged in
        $this->requireLogin();
        
        // Only doctors, pharmacists, and admins can access these functions
        if (!in_array($this->userRole, ['doctor', 'pharmacist', 'admin'])) {
            $this->redirectWithError('You do not have permission to access this section', '/dashboard');
            exit;
        }
    }
    
    /**
     * List all prescriptions
     */
    public function index() {
        try {
            $status = $_GET['status'] ?? 'active';
            $patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = 15;
            
            // Get prescriptions based on user role
            $prescriptions = [];
            $total = 0;
            
            if ($this->userRole === 'admin') {
                // Admins can see all prescriptions
                $prescriptions = $this->prescriptionModel->getAllPrescriptions($status, $patientId, $page, $perPage);
                $total = $this->prescriptionModel->countPrescriptions($status, $patientId);
            } elseif ($this->userRole === 'doctor') {
                // Doctors can see prescriptions they've created
                $prescriptions = $this->prescriptionModel->getPrescriptionsByDoctor($this->userId, $status, $patientId, $page, $perPage);
                $total = $this->prescriptionModel->countPrescriptionsByDoctor($this->userId, $status, $patientId);
            } elseif ($this->userRole === 'pharmacist') {
                // Pharmacists can see all active prescriptions
                $prescriptions = $this->prescriptionModel->getPrescriptionsByStatus('active', $patientId, $page, $perPage);
                $total = $this->prescriptionModel->countPrescriptions('active', $patientId);
            }
            
            // Get patient details if filtered by patient
            $patient = null;
            if ($patientId) {
                $patient = $this->patientModel->getWithUserData($patientId);
            }
            
            $this->render('prescriptions/index', [
                'prescriptions' => $prescriptions,
                'patient' => $patient,
                'status' => $status,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'perPage' => $perPage,
                    'url' => "/prescriptions?status=$status" . ($patientId ? "&patient_id=$patientId" : '') . "&page="
                ]
            ]);
            
        } catch (Exception $e) {
            $this->handleError('Error loading prescriptions', $e);
        }
    }
    
    /**
     * Show create prescription form
     */
    public function create() {
        try {
            $patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
            $appointmentId = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;
            
            // Get patient details if provided
            $patient = null;
            if ($patientId) {
                $patient = $this->patientModel->getWithUserData($patientId);
                if (!$patient) {
                    $this->redirectWithError('Patient not found', '/prescriptions');
                    return;
                }
            }
            
            // Get medications for the dropdown
            $medications = $this->medicationModel->getAllMedications();
            
            // Get common medications for quick add
            $commonMedications = $this->medicationModel->getCommonMedications();
            
            $this->render('prescriptions/form', [
                'patient' => $patient,
                'appointmentId' => $appointmentId,
                'medications' => $medications,
                'commonMedications' => $commonMedications,
                'frequencies' => $this->getFrequencies(),
                'durations' => $this->getDurations()
            ]);
            
        } catch (Exception $e) {
            $this->handleError('Error loading prescription form', $e);
        }
    }
    
    /**
     * Store a new prescription
     */
    public function store() {
        try {
            // Only doctors can create prescriptions
            if ($this->userRole !== 'doctor' && $this->userRole !== 'admin') {
                $this->jsonResponse(['success' => false, 'message' => 'You do not have permission to create prescriptions'], 403);
                return;
            }
            
            // Validate input
            $required = ['patient_id', 'items'];
            $missing = [];
            $data = [];
            
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    $missing[] = $field;
                } else {
                    $data[$field] = $_POST[$field];
                }
            }
            
            if (!empty($missing)) {
                $this->jsonResponse([
                    'success' => false, 
                    'message' => 'Missing required fields: ' . implode(', ', $missing)
                ], 400);
                return;
            }
            
            // Get patient details
            $patient = $this->patientModel->getWithUserData($data['patient_id']);
            if (!$patient) {
                $this->jsonResponse(['success' => false, 'message' => 'Patient not found'], 404);
                return;
            }
            
            // Prepare prescription data
            $prescriptionData = [
                'patient_id' => $data['patient_id'],
                'doctor_id' => $this->userId,
                'appointment_id' => !empty($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : null,
                'prescription_date' => date('Y-m-d H:i:s'),
                'notes' => $_POST['notes'] ?? '',
                'status' => 'active',
                'created_by' => $this->userId,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Prepare prescription items
            $items = json_decode($data['items'], true);
            if (empty($items) || !is_array($items)) {
                $this->jsonResponse(['success' => false, 'message' => 'No medication items provided'], 400);
                return;
            }
            
            $prescriptionData['items'] = [];
            
            foreach ($items as $item) {
                if (empty($item['medication_id']) || empty($item['dosage']) || empty($item['frequency']) || empty($item['duration'])) {
                    continue;
                }
                
                $prescriptionData['items'][] = [
                    'medication_id' => (int)$item['medication_id'],
                    'dosage' => $item['dosage'],
                    'frequency' => $item['frequency'],
                    'duration' => $item['duration'],
                    'instructions' => $item['instructions'] ?? '',
                    'quantity' => $item['quantity'] ?? null
                ];
            }
            
            if (empty($prescriptionData['items'])) {
                $this->jsonResponse(['success' => false, 'message' => 'No valid medication items provided'], 400);
                return;
            }
            
            // Create the prescription
            $prescriptionId = $this->prescriptionModel->createPrescription($prescriptionData);
            
            if ($prescriptionId) {
                // Log the action
                $this->logAction('prescription_created', [
                    'prescription_id' => $prescriptionId,
                    'patient_id' => $data['patient_id'],
                    'item_count' => count($prescriptionData['items'])
                ]);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Prescription created successfully',
                    'redirect' => '/prescriptions/view/' . $prescriptionId
                ]);
            } else {
                throw new Exception('Failed to create prescription');
            }
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error creating prescription: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * View a prescription
     */
    public function view($prescriptionId) {
        try {
            $prescription = $this->prescriptionModel->getPrescriptionById($prescriptionId);
            
            if (!$prescription) {
                $this->redirectWithError('Prescription not found', '/prescriptions');
                return;
            }
            
            // Check permissions
            if (!$this->canViewPrescription($prescription)) {
                $this->redirectWithError('You do not have permission to view this prescription', '/prescriptions');
                return;
            }
            
            // Get patient details
            $patient = $this->patientModel->getWithUserData($prescription['patient_id']);
            
            // Get doctor details
            $doctor = $this->userModel->getUserById($prescription['doctor_id']);
            
            // Get prescription items
            $items = $this->prescriptionModel->getPrescriptionItems($prescriptionId);
            
            $this->render('prescriptions/view', [
                'prescription' => $prescription,
                'patient' => $patient,
                'doctor' => $doctor,
                'items' => $items,
                'canEdit' => $this->canEditPrescription($prescription),
                'canDispense' => $this->canDispensePrescription($prescription),
                'canPrint' => true
            ]);
            
        } catch (Exception $e) {
            $this->handleError('Error viewing prescription', $e);
        }
    }
    
    /**
     * Print prescription
     */
    public function print($prescriptionId) {
        try {
            $prescription = $this->prescriptionModel->getPrescriptionById($prescriptionId);
            
            if (!$prescription) {
                $this->redirectWithError('Prescription not found', '/prescriptions');
                return;
            }
            
            // Check permissions
            if (!$this->canViewPrescription($prescription)) {
                $this->redirectWithError('You do not have permission to view this prescription', '/prescriptions');
                return;
            }
            
            // Get patient details
            $patient = $this->patientModel->getWithUserData($prescription['patient_id']);
            
            // Get doctor details
            $doctor = $this->userModel->getUserById($prescription['doctor_id']);
            
            // Get prescription items
            $items = $this->prescriptionModel->getPrescriptionItems($prescriptionId);
            
            $this->render('prescriptions/print', [
                'prescription' => $prescription,
                'patient' => $patient,
                'doctor' => $doctor,
                'items' => $items,
                'print' => true
            ], 'print');
            
        } catch (Exception $e) {
            $this->handleError('Error printing prescription', $e);
        }
    }
    
    /**
     * Dispense a prescription
     */
    public function dispense($prescriptionId) {
        try {
            // Only pharmacists can dispense prescriptions
            if ($this->userRole !== 'pharmacist' && $this->userRole !== 'admin') {
                $this->jsonResponse(['success' => false, 'message' => 'You do not have permission to dispense prescriptions'], 403);
                return;
            }
            
            $prescription = $this->prescriptionModel->getPrescriptionById($prescriptionId);
            
            if (!$prescription) {
                $this->jsonResponse(['success' => false, 'message' => 'Prescription not found'], 404);
                return;
            }
            
            // Check if already dispensed
            if ($prescription['status'] === 'dispensed') {
                $this->jsonResponse([
                    'success' => false, 
                    'message' => 'This prescription has already been dispensed'
                ], 400);
                return;
            }
            
            // Check if expired
            if (strtotime($prescription['valid_until']) < time()) {
                $this->jsonResponse([
                    'success' => false, 
                    'message' => 'This prescription has expired and cannot be dispensed'
                ], 400);
                return;
            }
            
            // Update prescription status
            $success = $this->prescriptionModel->updatePrescription($prescriptionId, [
                'status' => 'dispensed',
                'dispensed_by' => $this->userId,
                'dispensed_at' => date('Y-m-d H:i:s'),
                'updated_by' => $this->userId,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($success) {
                // Update medication stock levels
                $items = $this->prescriptionModel->getPrescriptionItems($prescriptionId);
                foreach ($items as $item) {
                    if ($item['quantity'] > 0) {
                        $this->medicationModel->decrementStock($item['medication_id'], $item['quantity']);
                    }
                }
                
                // Log the action
                $this->logAction('prescription_dispensed', [
                    'prescription_id' => $prescriptionId,
                    'patient_id' => $prescription['patient_id'],
                    'dispensed_by' => $this->userId
                ]);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Prescription dispensed successfully',
                    'redirect' => '/prescriptions/view/' . $prescriptionId
                ]);
            } else {
                throw new Exception('Failed to dispense prescription');
            }
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error dispensing prescription: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Cancel a prescription
     */
    public function cancel($prescriptionId) {
        try {
            // Only doctors and admins can cancel prescriptions
            if ($this->userRole !== 'doctor' && $this->userRole !== 'admin') {
                $this->jsonResponse(['success' => false, 'message' => 'You do not have permission to cancel prescriptions'], 403);
                return;
            }
            
            $prescription = $this->prescriptionModel->getPrescriptionById($prescriptionId);
            
            if (!$prescription) {
                $this->jsonResponse(['success' => false, 'message' => 'Prescription not found'], 404);
                return;
            }
            
            // Check if already cancelled or dispensed
            if ($prescription['status'] === 'cancelled') {
                $this->jsonResponse([
                    'success' => false, 
                    'message' => 'This prescription is already cancelled'
                ], 400);
                return;
            }
            
            if ($prescription['status'] === 'dispensed') {
                $this->jsonResponse([
                    'success' => false, 
                    'message' => 'Cannot cancel a dispensed prescription'
                ], 400);
                return;
            }
            
            // Update prescription status
            $success = $this->prescriptionModel->updatePrescription($prescriptionId, [
                'status' => 'cancelled',
                'cancelled_by' => $this->userId,
                'cancelled_at' => date('Y-m-d H:i:s'),
                'cancellation_reason' => $_POST['reason'] ?? 'No reason provided',
                'updated_by' => $this->userId,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($success) {
                // Log the action
                $this->logAction('prescription_cancelled', [
                    'prescription_id' => $prescriptionId,
                    'patient_id' => $prescription['patient_id'],
                    'cancelled_by' => $this->userId,
                    'reason' => $_POST['reason'] ?? 'No reason provided'
                ]);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Prescription cancelled successfully',
                    'redirect' => '/prescriptions/view/' . $prescriptionId
                ]);
            } else {
                throw new Exception('Failed to cancel prescription');
            }
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error cancelling prescription: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check if current user can view a prescription
     */
    private function canViewPrescription($prescription) {
        // Admins can view all prescriptions
        if ($this->userRole === 'admin') {
            return true;
        }
        
        // Doctors can view prescriptions they created
        if ($this->userRole === 'doctor' && $prescription['doctor_id'] == $this->userId) {
            return true;
        }
        
        // Pharmacists can view active prescriptions
        if ($this->userRole === 'pharmacist' && $prescription['status'] === 'active') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if current user can edit a prescription
     */
    private function canEditPrescription($prescription) {
        // Only active prescriptions can be edited
        if ($prescription['status'] !== 'active') {
            return false;
        }
        
        // Only the prescribing doctor or admin can edit
        return ($this->userRole === 'admin' || 
               ($this->userRole === 'doctor' && $prescription['doctor_id'] == $this->userId));
    }
    
    /**
     * Check if current user can dispense a prescription
     */
    private function canDispensePrescription($prescription) {
        // Only active prescriptions can be dispensed
        if ($prescription['status'] !== 'active') {
            return false;
        }
        
        // Only pharmacists and admins can dispense
        return ($this->userRole === 'pharmacist' || $this->userRole === 'admin');
    }
    
    /**
     * Get frequency options for prescriptions
     */
    private function getFrequencies() {
        return [
            'OD' => 'Once daily',
            'BD' => 'Twice daily',
            'TDS' => 'Three times daily',
            'QID' => 'Four times daily',
            'Q4H' => 'Every 4 hours',
            'Q6H' => 'Every 6 hours',
            'Q8H' => 'Every 8 hours',
            'Q12H' => 'Every 12 hours',
            'QOD' => 'Every other day',
            'QW' => 'Once weekly',
            'PRN' => 'As needed',
            'STAT' => 'Immediately, then as directed'
        ];
    }
    
    /**
     * Get duration options for prescriptions
     */
    private function getDurations() {
        return [
            '1 day' => '1 day',
            '3 days' => '3 days',
            '5 days' => '5 days',
            '1 week' => '1 week',
            '2 weeks' => '2 weeks',
            '1 month' => '1 month',
            '3 months' => '3 months',
            '6 months' => '6 months',
            '12 months' => '12 months',
            'Ongoing' => 'Ongoing',
            'Until finished' => 'Until finished',
            'PRN' => 'As needed'
        ];
    }
}
