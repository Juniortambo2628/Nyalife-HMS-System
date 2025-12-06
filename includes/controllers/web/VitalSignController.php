<?php

/**
 * Nyalife HMS - Vital Sign Controller
 *
 * Handles all vital signs related web requests
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/VitalSignModel.php';
require_once __DIR__ . '/../../models/PatientModel.php';
require_once __DIR__ . '/../../models/UserModel.php';

class VitalSignController extends WebController
{
    private readonly \VitalSignModel $vitalSignModel;

    private readonly \PatientModel $patientModel;

    /** @var bool */
    protected $requiresLogin = true;

    /** @var array */
    protected $allowedRoles = ['admin', 'doctor', 'nurse'];

    public function __construct()
    {
        parent::__construct();
        $this->vitalSignModel = new VitalSignModel();
        $this->patientModel = new PatientModel();
        $this->pageTitle = 'Vital Signs';
    }

    /**
     * Show create vital sign form
     *
     * @param int $patientId Optional patient ID
     */
    public function create($patientId = null): void
    {
        try {
            $patient = null;

            // Support query parameter ?patient_id=...
            if (!$patientId && isset($_GET['patient_id']) && is_numeric($_GET['patient_id'])) {
                $patientId = (int)$_GET['patient_id'];
            }

            if ($patientId) {
                $patient = $this->patientModel->getById($patientId);

                if (!$patient) {
                    $this->redirectWithError('Patient not found', '/patients');
                    return;
                }
            }

            // Optional context
            $appointmentId = isset($_GET['appointment_id']) && is_numeric($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : null;
            $returnUrl = isset($_GET['return']) ? (string)$_GET['return'] : '';

            $this->renderView('vitals/create', [
                'patient' => $patient,
                'patients' => $patient ? [] : $this->patientModel->getAllPatients(),
                'appointmentId' => $appointmentId,
                'returnUrl' => $returnUrl
            ]);
        } catch (Exception $e) {
            $this->handleError('Error loading vital sign form', $e);
        }
    }

    /**
     * Store a new vital sign record
     */
    public function store(): void
    {
        try {
            // Validate input
            $required = ['patient_id'];
            $missing = [];

            foreach ($required as $field) {
                if (!isset($_POST[$field]) || trim((string) $_POST[$field]) === '') {
                    $missing[] = $field;
                }
            }

            if ($missing !== []) {
                // Build errors array keyed by field
                $errors = [];
                foreach ($missing as $m) {
                    $errors[$m] = match ($m) {
                        'patient_id' => 'Please select a patient',
                        default => 'This field is required',
                    };
                }

                // If AJAX request, return JSON with field errors
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
                    return;
                }

                $this->redirectWithError('Missing required fields: ' . implode(', ', $missing), '/vitals/create');
                return;
            }

            // Prepare data
            $data = [
                'patient_id' => $_POST['patient_id'],
                'blood_pressure' => $_POST['blood_pressure'] ?? null,
                'heart_rate' => $_POST['pulse'] ?? null,
                'temperature' => $_POST['temperature'] ?? null,
                'respiratory_rate' => $_POST['respiratory_rate'] ?? null,
                'oxygen_saturation' => $_POST['oxygen_saturation'] ?? null,
                'height' => $_POST['height'] ?? null,
                'weight' => $_POST['weight'] ?? null,
                'bmi' => $_POST['bmi'] ?? null,
                'pain_level' => $_POST['pain_level'] ?? null,
                'notes' => $_POST['notes'] ?? null,
                'recorded_by' => $this->userId,
                'measured_at' => date('Y-m-d H:i:s')
            ];

            // Calculate BMI if height and weight are provided but BMI is not
            if (empty($data['bmi']) && !empty($data['height']) && !empty($data['weight'])) {
                // Height in meters, weight in kg
                $heightInMeters = $data['height'] / 100; // Convert cm to m
                $data['bmi'] = round($data['weight'] / ($heightInMeters * $heightInMeters), 2);
            }

            // Create vital sign record
            $vitalSignId = $this->vitalSignModel->createVitalSign($data);

            if (!$vitalSignId) {
                $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Failed to create vital sign record']);
                    return;
                }
                $this->redirectWithError('Failed to create vital sign record', '/vitals/create');
                return;
            }

            // Log the action
            $this->logAction('vital_sign_created', [
                'vital_sign_id' => $vitalSignId,
                'patient_id' => $data['patient_id']
            ]);

            // If a return URL was provided, prefer redirecting back
            $returnUrl = isset($_POST['return']) ? (string)$_POST['return'] : '';
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            if ($isAjax) {
                // Build formatted vitals for UI
                $formatted = [];
                $formatted['measured_at'] = date('M d, Y h:i A', strtotime($data['measured_at']));
                $formatted['blood_pressure'] = $data['blood_pressure'] ? htmlspecialchars((string) $data['blood_pressure']) . ' mmHg' : '<span class="text-muted">Not recorded</span>';
                $formatted['pulse'] = $data['heart_rate'] ? htmlspecialchars((string) $data['heart_rate']) . ' bpm' : '<span class="text-muted">Not recorded</span>';
                $formatted['temperature'] = $data['temperature'] ? htmlspecialchars((string) $data['temperature']) . ' °C' : '<span class="text-muted">Not recorded</span>';
                $formatted['respiratory_rate'] = $data['respiratory_rate'] ? htmlspecialchars((string) $data['respiratory_rate']) . ' br/min' : '<span class="text-muted">Not recorded</span>';
                $formatted['oxygen_saturation'] = $data['oxygen_saturation'] ? htmlspecialchars((string) $data['oxygen_saturation']) . '%' : '<span class="text-muted">Not recorded</span>';
                // Resolve user display name
                $formatted['recorded_by'] = 'System';
                try {
                    $um = new UserModel();
                    $uid = $this->userId ?? ($this->auth->getUserId() ?? null);
                    if ($uid) {
                        // UserModel provides getUserById
                        $u = $um->getUserById($uid);
                        if ($u) {
                            $formatted['recorded_by'] = in_array(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')), ['', '0'], true) ? 'System' : trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                        }
                    }
                } catch (Throwable) {
                    // ignore, keep default
                }

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Vital sign record created successfully', 'vital_id' => $vitalSignId, 'vitals' => $formatted]);
                return;
            }

            if ($returnUrl !== '' && $returnUrl !== '0') {
                $this->redirectWithSuccess('Vital sign record created successfully', $returnUrl);
                return;
            }

            $this->redirectWithSuccess('Vital sign record created successfully', '/vitals/view/' . $vitalSignId);
        } catch (Exception $e) {
            $this->handleError('Error creating vital sign record', $e);
        }
    }

    /**
     * View a vital sign record
     *
     * @param int $id Vital sign ID
     */
    public function view($id): void
    {
        try {
            $vitalSign = $this->vitalSignModel->getVitalSignById($id);

            if (!$vitalSign) {
                $this->redirectWithError('Vital sign record not found', '/patients');
                return;
            }

            $patient = $this->patientModel->getWithUserData($vitalSign['patient_id']);

            $this->renderView('vitals/view', [
                'vitalSign' => $vitalSign,
                'patient' => $patient
            ]);
        } catch (Exception $e) {
            $this->handleError('Error loading vital sign record', $e);
        }
    }

    /**
     * Show vital sign history for a patient
     *
     * @param int $patientId Patient ID
     */
    public function history($patientId): void
    {
        try {
            $patient = $this->patientModel->getWithUserData($patientId);

            if (!$patient) {
                $this->redirectWithError('Patient not found', '/patients');
                return;
            }

            $vitalSigns = $this->vitalSignModel->getVitalSignsByPatient($patientId);

            $this->renderView('vitals/history', [
                'patient' => $patient,
                'vitalSigns' => $vitalSigns
            ]);
        } catch (Exception $e) {
            $this->handleError('Error loading vital sign history', $e);
        }
    }

    /**
     * Log an action related to vital signs
     *
     * @param string $action Action name
     * @param array $details Action details
     */
    private function logAction(string $action, array $details): void
    {
        if (function_exists('logAction')) {
            logAction(
                $this->userId,
                $action,
                'vital_sign',
                $details['vital_sign_id'] ?? null,
                json_encode($details)
            );
        }
    }

    /**
     * Handle errors for vital signs operations
     *
     * @param string $message Error message
     * @param Exception|null $e Exception if available
     */
    protected function handleError($message, ?Exception $e = null): void
    {
        if ($e instanceof \Exception) {
            ErrorHandler::logSystemError($e, __METHOD__);
        }

        $this->setFlashMessage('error', $message);
        $this->redirect('/patients');
    }

    /**
     * Redirect with error message
     *
     * @param string $message Error message
     * @param string $url URL to redirect to
     */
    protected function redirectWithError($message, $url): void
    {
        $this->setFlashMessage('error', $message);
        $this->redirect($url);
    }

    /**
     * Redirect with success message
     *
     * @param string $message Success message
     * @param string $url URL to redirect to
     */
    protected function redirectWithSuccess($message, $url): void
    {
        $this->setFlashMessage('success', $message);
        $this->redirect($url);
    }
}
