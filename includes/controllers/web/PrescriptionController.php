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

class PrescriptionController extends WebController
{
    private readonly \PrescriptionModel $prescriptionModel;

    private readonly \PatientModel $patientModel;

    private readonly \MedicationModel $medicationModel;

    /** @var bool */
    protected $requiresLogin = true;

    /** @var array */
    protected $allowedRoles = ['doctor', 'pharmacist', 'admin'];

    public function __construct()
    {
        parent::__construct();
        $this->prescriptionModel = new PrescriptionModel();
        $this->patientModel = new PatientModel();
        $this->medicationModel = new MedicationModel();
        $this->pageTitle = 'Prescriptions';
    }

    /**
     * List all prescriptions
     */
    public function index(): void
    {
        try {
            $status = $_GET['status'] ?? 'active';
            $patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = 15;

            // Get prescriptions based on user role
            $prescriptions = [];
            $total = 0;
            $userRole = SessionManager::get('role');
            $userId = SessionManager::get('user_id');

            if ($userRole === 'admin') {
                // Admins can see all prescriptions
                $filters = ['status' => $status];
                if ($patientId !== 0) {
                    $filters['patient_id'] = $patientId;
                }
                $prescriptions = $this->prescriptionModel->getAllPrescriptions($filters);
                $total = $this->prescriptionModel->countPrescriptions($status, $patientId);
            } elseif ($userRole === 'doctor') {
                // Doctors can see prescriptions they've created
                $prescriptions = $this->prescriptionModel->getPrescriptionsByDoctor($userId, $status, $patientId, $page, $perPage);
                $total = $this->prescriptionModel->countPrescriptionsByDoctor($userId, $status, $patientId);
            } elseif ($userRole === 'pharmacist') {
                // Pharmacists can see all active prescriptions
                $prescriptions = $this->prescriptionModel->getPrescriptionsByStatus('active', $patientId, $page, $perPage);
                $total = $this->prescriptionModel->countPrescriptions('active', $patientId);
            } elseif ($userRole === 'patient') {
                // Patients can see their own prescriptions
                $patientRecord = $this->patientModel->getPatientIdByUserId($userId);
                if ($patientRecord) {
                    $prescriptions = $this->prescriptionModel->getPatientPrescriptions($patientRecord);
                    // Get total count for pagination (simplified for now)
                    $total = count($prescriptions);
                } else {
                    $prescriptions = [];
                    $total = 0;
                }
            }

            // Get patient details if filtered by patient
            $patient = null;
            if ($patientId !== 0) {
                $patient = $this->patientModel->getWithUserData($patientId);
            }

            $this->renderView('prescriptions/index', [
                'prescriptions' => $prescriptions,
                'patient' => $patient,
                'status' => $status,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'perPage' => $perPage,
                    'url' => "/prescriptions?status=$status" . ($patientId !== 0 ? "&patient_id=$patientId" : '') . "&page="
                ]
            ]);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Show create prescription form
     */
    public function create(): void
    {
        try {
            $patientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
            $appointmentId = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;

            // Get patient details if provided
            $patient = null;
            if ($patientId !== 0) {
                $patient = $this->patientModel->getWithUserData($patientId);
                if (!$patient) {
                    $this->setFlashMessage('error', 'Patient not found');
                    $this->redirect('/prescriptions');
                    return;
                }
            }

            // Get medications for the dropdown
            $medications = $this->medicationModel->getAllMedications();

            // Get common medications for quick add
            $commonMedications = $this->medicationModel->getCommonMedications();

            $this->renderView('prescriptions/form', [
                'patient' => $patient,
                'appointmentId' => $appointmentId,
                'medications' => $medications,
                'commonMedications' => $commonMedications,
                'frequencies' => $this->getFrequencies(),
                'durations' => $this->getDurations()
            ]);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Store a new prescription
     */
    public function store(): void
    {
        try {
            $userRole = SessionManager::get('role');
            $userId = SessionManager::get('user_id');

            // Only doctors can create prescriptions
            if ($userRole !== 'doctor' && $userRole !== 'admin') {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'You do not have permission to create prescriptions']);
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

            if ($missing !== []) {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Missing required fields: ' . implode(', ', $missing)
                ]);
                return;
            }

            // Get patient details
            $patient = $this->patientModel->getWithUserData($data['patient_id']);
            if (!$patient) {
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Patient not found']);
                return;
            }

            // Prepare prescription data
            $prescriptionData = [
                'patient_id' => $data['patient_id'],
                'prescribed_by' => $userId,
                'appointment_id' => empty($_POST['appointment_id']) ? null : (int)$_POST['appointment_id'],
                'prescription_date' => date('Y-m-d H:i:s'),
                'notes' => $_POST['notes'] ?? '',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Prepare prescription items
            $items = json_decode((string) $data['items'], true);
            if (empty($items) || !is_array($items)) {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No medication items provided']);
                return;
            }

            $prescriptionData['items'] = [];

            foreach ($items as $item) {
                if (empty($item['medication_id'])) {
                    continue;
                }
                if (empty($item['dosage'])) {
                    continue;
                }
                if (empty($item['frequency'])) {
                    continue;
                }
                if (empty($item['duration'])) {
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
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No valid medication items provided']);
                return;
            }

            // Create prescription
            $prescriptionId = $this->prescriptionModel->createPrescription($prescriptionData);

            if (!$prescriptionId) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create prescription']);
                return;
            }

            // Return success
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Prescription created successfully',
                'prescription_id' => $prescriptionId,
                'redirect_url' => '/prescriptions/view/' . $prescriptionId
            ]);
        } catch (Exception $e) {
            ErrorHandler::logSystemError($e, __METHOD__);
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * View a prescription
     *
     * @param int $prescriptionId Prescription ID
     */
    public function view($prescriptionId): void
    {
        try {
            // Get prescription details with items
            $prescription = $this->prescriptionModel->getPrescriptionWithItems($prescriptionId);

            if (!$prescription) {
                $this->setFlashMessage('error', 'Prescription not found');
                $this->redirect('/prescriptions');
                return;
            }

            // Check if user has permission to view this prescription
            if (!$this->canViewPrescription($prescription)) {
                $this->showError('You do not have permission to view this prescription', 403);
                return;
            }

            // Extract items from prescription data
            $items = $prescription['items'] ?? [];

            // Get patient details
            $patient = $this->patientModel->getWithUserData($prescription['patient_id']);

            $this->renderView('prescriptions/view', [
                'prescription' => $prescription,
                'items' => $items,
                'patient' => $patient
            ]);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Print a prescription
     *
     * @param int $prescriptionId Prescription ID
     */
    public function print($prescriptionId): void
    {
        try {
            // Get prescription details with items
            $prescription = $this->prescriptionModel->getPrescriptionWithItems($prescriptionId);

            if (!$prescription) {
                $this->setFlashMessage('error', 'Prescription not found');
                $this->redirect('/prescriptions');
                return;
            }

            // Check if user has permission to view this prescription
            if (!$this->canViewPrescription($prescription)) {
                $this->showError('You do not have permission to view this prescription', 403);
                return;
            }

            // Extract items from prescription data
            $items = $prescription['items'] ?? [];

            // Get patient details
            $patient = $this->patientModel->getWithUserData($prescription['patient_id']);

            // Render print view with no layout
            $this->renderView('prescriptions/print', [
                'prescription' => $prescription,
                'items' => $items,
                'patient' => $patient
            ], 'print');
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Show pending prescriptions
     */
    public function pending(): void
    {
        try {
            $userRole = SessionManager::get('role');
            $userId = SessionManager::get('user_id');

            $prescriptions = [];

            if ($userRole === 'pharmacist' || $userRole === 'admin') {
                // Pharmacists and admins can see all pending prescriptions
                $prescriptions = $this->prescriptionModel->getPrescriptionsByStatus('pending');
            } elseif ($userRole === 'doctor') {
                // Doctors can see their own pending prescriptions
                $prescriptions = $this->prescriptionModel->getPrescriptionsByDoctor($userId, 'pending');
            }

            $this->renderView('prescriptions/pending', [
                'prescriptions' => $prescriptions,
                'userRole' => $userRole
            ]);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Dispense a prescription
     *
     * @param int $id Prescription ID
     */
    public function dispense($id): void
    {
        try {
            $userRole = SessionManager::get('role');
            $userId = SessionManager::get('user_id');

            // Only pharmacists can dispense prescriptions
            if ($userRole !== 'pharmacist' && $userRole !== 'admin') {
                $this->setFlashMessage('error', 'Only pharmacists can dispense prescriptions');
                $this->redirect('/prescriptions');
                return;
            }

            // Get prescription details
            $prescription = $this->prescriptionModel->getPrescriptionWithItems($id);

            if (!$prescription) {
                $this->setFlashMessage('error', 'Prescription not found');
                $this->redirect('/prescriptions');
                return;
            }

            // Check if prescription is in a dispensable state
            if ($prescription['status'] !== 'pending' && $prescription['status'] !== 'processing') {
                $this->setFlashMessage('error', 'Prescription is not ready for dispensing');
                $this->redirect('/prescriptions');
                return;
            }

            // Process the dispensing
            $result = $this->prescriptionModel->dispensePrescription($id, $userId);

            if ($result) {
                $this->setFlashMessage('success', 'Prescription dispensed successfully');
                $this->redirect('/prescriptions/view/' . $id);
            } else {
                $this->setFlashMessage('error', 'Failed to dispense prescription');
                $this->redirect('/prescriptions');
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Cancel a prescription
     *
     * @param int $id Prescription ID
     */
    public function cancel($id): void
    {
        try {
            $userRole = SessionManager::get('role');
            $userId = SessionManager::get('user_id');

            // Get prescription details
            $prescription = $this->prescriptionModel->getPrescriptionWithItems($id);

            if (!$prescription) {
                $this->setFlashMessage('error', 'Prescription not found');
                $this->redirect('/prescriptions');
                return;
            }

            // Check permissions
            if (!$this->canCancelPrescription($prescription, $userRole, $userId)) {
                $this->setFlashMessage('error', 'You do not have permission to cancel this prescription');
                $this->redirect('/prescriptions');
                return;
            }

            // Cancel the prescription
            $result = $this->prescriptionModel->cancelPrescription($id, 'Cancelled by user', $userId);

            if ($result) {
                $this->setFlashMessage('success', 'Prescription cancelled successfully');
                $this->redirect('/prescriptions');
            } else {
                $this->setFlashMessage('error', 'Failed to cancel prescription');
                $this->redirect('/prescriptions');
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Helper method to check if user can view a prescription
     *
     * @param array $prescription Prescription data
     * @return bool True if user can view
     */
    private function canViewPrescription(array $prescription)
    {
        $userRole = SessionManager::get('role');
        $userId = SessionManager::get('user_id');

        // Admins can view all prescriptions
        if ($userRole === 'admin') {
            return true;
        }

        // Doctors can view prescriptions they created
        if ($userRole === 'doctor' && $prescription['created_by'] == $userId) {
            return true;
        }

        // Pharmacists can view active prescriptions
        if ($userRole === 'pharmacist' && $prescription['status'] === 'active') {
            return true;
        }
        // Patients can view their own prescriptions
        return $userRole === 'patient' && $prescription['patient_id'] == $userId;
    }

    /**
     * Check if user can cancel a prescription
     *
     * @param array $prescription Prescription data
     * @param string $userRole User role
     * @param int $userId User ID
     * @return bool
     */
    private function canCancelPrescription(array $prescription, $userRole, $userId)
    {
        // Admins can cancel any prescription
        if ($userRole === 'admin') {
            return true;
        }

        // Doctors can cancel their own prescriptions if they're still pending
        if ($userRole === 'doctor' && $prescription['doctor_id'] == $userId && $prescription['status'] === 'pending') {
            return true;
        }
        // Pharmacists can cancel prescriptions they're processing
        return $userRole === 'pharmacist' && $prescription['pharmacist_id'] == $userId && $prescription['status'] === 'processing';
    }

    /**
     * Get common frequencies for medications
     *
     * @return array List of frequencies
     */
    private function getFrequencies(): array
    {
        return [
            'once_daily' => 'Once daily',
            'twice_daily' => 'Twice daily (BID)',
            'three_times_daily' => 'Three times daily (TID)',
            'four_times_daily' => 'Four times daily (QID)',
            'every_morning' => 'Every morning (QAM)',
            'every_night' => 'Every night (QHS)',
            'every_6_hours' => 'Every 6 hours (q6h)',
            'every_8_hours' => 'Every 8 hours (q8h)',
            'every_12_hours' => 'Every 12 hours (q12h)',
            'as_needed' => 'As needed (PRN)',
            'with_meals' => 'With meals',
            'before_meals' => 'Before meals',
            'after_meals' => 'After meals',
            'other' => 'Other (specify in instructions)'
        ];
    }

    /**
     * Get common durations for medications
     *
     * @return array List of durations
     */
    private function getDurations(): array
    {
        return [
            '3_days' => '3 days',
            '5_days' => '5 days',
            '7_days' => '7 days',
            '10_days' => '10 days',
            '14_days' => '14 days',
            '21_days' => '21 days',
            '30_days' => '30 days',
            '60_days' => '60 days',
            '90_days' => '90 days',
            'indefinite' => 'Indefinite/Chronic',
            'other' => 'Other (specify in instructions)'
        ];
    }
}
