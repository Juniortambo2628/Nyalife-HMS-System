<?php

/**
 * Nyalife HMS - Guest Appointment Controller
 *
 * Controller for handling guest appointment bookings.
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/AppointmentModel.php';
require_once __DIR__ . '/../../models/PatientModel.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../services/NotificationService.php';

class GuestAppointmentController extends WebController
{
    protected \AppointmentModel $appointmentModel;

    protected \PatientModel $patientModel;

    protected \UserModel $userModel;

    protected \NotificationService $notificationService;

    /** @var bool */
    protected $requiresLogin = false; // Allow guest access without login

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Guest Appointment - Nyalife HMS';
        $this->appointmentModel = new AppointmentModel();
        $this->patientModel = new PatientModel();
        $this->userModel = new UserModel();
        $this->notificationService = new NotificationService();
    }

    /**
     * Show the guest appointment booking form
     */
    public function showBookingForm(): void
    {
        // Get available doctors
        $doctors = $this->userModel->getDoctors();

        // Get appointment types (hardcoded for now since method doesn't exist)
        $appointmentTypes = [
            'new_visit' => 'New Patient Consultation',
            'follow_up' => 'Follow-up',
            'routine_checkup' => 'Routine Check-up',
            'consultation' => 'General Consultation',
            'gynecology' => 'Gynecology Services',
            'obstetrics' => 'Obstetrics Care',
            'laboratory' => 'Laboratory Tests'
        ];

        // Get services
        $services = $this->getServices();

        $this->renderView('guest-appointments/booking-form', [
            'doctors' => $doctors,
            'appointmentTypes' => $appointmentTypes,
            'services' => $services
        ]);
    }

    /**
     * Process guest appointment booking
     */
    public function bookAppointment(): void
    {
        try {
            // Validate request method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            // Validate required fields
            $requiredFields = [
                'first_name', 'last_name', 'email', 'phone',
                'date_of_birth', 'gender', 'appointment_date',
                'appointment_time', 'appointment_type', 'reason'
            ];

            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }

            // Validate email
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address');
            }

            // Validate date within opening hours
            $appointmentDate = $_POST['appointment_date'];
            $appointmentTime = $_POST['appointment_time'];
            $appointmentDateTime = $appointmentDate . ' ' . $appointmentTime;
            $startDt = DateTime::createFromFormat('Y-m-d H:i', $appointmentDateTime);
            if (!$startDt) {
                throw new Exception('Invalid appointment date/time');
            }
            if ($startDt <= new DateTime()) {
                throw new Exception('Appointment date and time must be in the future');
            }
            if (!Utilities::isWithinOpeningHours($startDt)) {
                $next = Utilities::nextValidStart(clone $startDt);
                $suggest = $next instanceof \DateTime ? $next->format('M d, Y h:i A') : null;
                throw new Exception('Selected time is outside clinic hours.' . ($suggest ? ' Next available: ' . $suggest : ''));
            }

            // Start database transaction for atomic operations
            $this->db = DatabaseManager::getInstance()->getConnection();
            $this->db->begin_transaction();

            try {
                // Check if email already exists (optimized query)
                $emailCheckStmt = $this->db->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
                $emailCheckStmt->bind_param("s", $_POST['email']);
                $emailCheckStmt->execute();
                $result = $emailCheckStmt->get_result();

                if ($result->num_rows > 0) {
                    throw new Exception('Email address is already registered. Please use a different email or login to your existing account.');
                }

                // Get a default doctor efficiently (single query)
                $doctorId = $_POST['doctor_id'] ?? null;
                if ($doctorId) {
                    // Map provided doctor user_id to staff.staff_id
                    $mapStmt = $this->db->prepare("SELECT s.staff_id FROM staff s WHERE s.user_id = ? LIMIT 1");
                    $mapStmt->bind_param("i", $doctorId);
                    $mapStmt->execute();
                    $mapRes = $mapStmt->get_result();
                    if ($mapRow = $mapRes->fetch_assoc()) {
                        $doctorId = (int)$mapRow['staff_id'];
                    } else {
                        $doctorId = null; // fallback below
                    }
                }
                if (!$doctorId) {
                    $doctorStmt = $this->db->prepare("
                        SELECT s.staff_id 
                        FROM staff s 
                        JOIN users u ON s.user_id = u.user_id 
                        WHERE u.role_id = 2 
                        LIMIT 1
                    ");
                    $doctorStmt->execute();
                    $doctorResult = $doctorStmt->get_result();

                    if ($doctorResult->num_rows === 0) {
                        throw new Exception('No doctors available for appointment booking');
                    }

                    $doctorRow = $doctorResult->fetch_assoc();
                    $doctorId = $doctorRow['staff_id'];
                }

                // Create user and patient in optimized way
                $patientInfo = $this->createPatientOptimized($_POST);

                if ($patientInfo === [] || empty($patientInfo['patient_id']) || empty($patientInfo['user_id'])) {
                    throw new Exception('Failed to create patient record');
                }
                $patientId = (int)$patientInfo['patient_id'];
                $createdByUserId = (int)$patientInfo['user_id'];

                // Calculate end time (default 30 minutes) – already validated window boundaries
                $startDateTime = $appointmentDate . ' ' . $appointmentTime;
                $endDateTime = date('Y-m-d H:i:s', strtotime($startDateTime . ' +30 minutes'));
                $endTime = date('H:i:s', strtotime($endDateTime));

                // Create appointment with optimized single query
                $appointmentId = $this->createAppointmentOptimized([
                    'patient_id' => $patientId,
                    'doctor_id' => $doctorId,
                    'appointment_date' => $appointmentDate,
                    'appointment_time' => $appointmentTime,
                    'end_time' => $endTime,
                    'appointment_type' => $_POST['appointment_type'],
                    'reason' => $_POST['reason'],
                    'status' => 'scheduled',
                    'created_by' => $createdByUserId
                ]);

                if ($appointmentId === 0 || $appointmentId === false) {
                    throw new Exception('Failed to create appointment');
                }

                // Commit transaction
                $this->db->commit();

                // Send notifications for guest appointment
                $this->notificationService->sendAppointmentCreatedNotification($appointmentId);

                // Queue email for background processing (non-blocking)
                $this->queueConfirmationEmail($_POST['email'], $appointmentId);
            } catch (Exception $e) {
                // Rollback on any error
                $this->db->rollback();
                throw $e;
            }

            // Return success response
            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Appointment request submitted successfully. We will contact you to confirm.',
                    'appointment_id' => $appointmentId,
                    'redirect_url' => rtrim($this->getBaseUrl(), '/') . '/guest-appointments/confirmation'
                ]);
            } else {
                $this->setFlashMessage('success', 'Appointment request submitted successfully. We will contact you to confirm.');
                $this->redirect($this->getBaseUrl() . '/guest-appointments/confirmation');
            }
        } catch (Exception $e) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            } else {
                $this->setFlashMessage('error', $e->getMessage());
                $this->redirect($this->getBaseUrl() . '/guest-appointments');
            }
        }
    }

    /**
     * Show confirmation page
     */
    public function confirmation(): void
    {
        $this->renderView('guest-appointments/confirmation');
    }

    /**
     * Get available services
     */
    private function getServices(): array
    {
        // This should come from a services table
        return [
            ['id' => 'new_visit', 'name' => 'New Patient Consultation'],
            ['id' => 'follow_up', 'name' => 'Follow-up'],
            ['id' => 'routine_checkup', 'name' => 'Routine Check-up'],
            ['id' => 'consultation', 'name' => 'General Consultation'],
            ['id' => 'gynecology', 'name' => 'Gynecology Services'],
            ['id' => 'obstetrics', 'name' => 'Obstetrics Care'],
            ['id' => 'laboratory', 'name' => 'Laboratory Tests']
        ];
    }

    /**
     * Create patient with optimized single transaction
     */
    private function createPatientOptimized(array $data): array
    {
        // Generate unique username and patient number
        $username = 'guest_' . time() . '_' . random_int(1000, 9999);
        $patientNumber = $this->generatePatientNumber();
        $hashedPassword = password_hash('guest_' . time(), PASSWORD_DEFAULT);

        // Single query to create user (avoid referencing non-existent 'status' column)
        $userStmt = $this->db->prepare("
            INSERT INTO users (username, email, password, role_id, first_name, last_name, phone, date_of_birth, gender, created_at) 
            VALUES (?, ?, ?, 6, ?, ?, ?, ?, ?, NOW())
        ");
        $userStmt->bind_param(
            "ssssssss",
            $username,
            $data['email'],
            $hashedPassword,
            $data['first_name'],
            $data['last_name'],
            $data['phone'],
            $data['date_of_birth'],
            $data['gender']
        );

        if (!$userStmt->execute()) {
            throw new Exception('Failed to create user record');
        }

        $userId = $this->db->insert_id;

        // Single query to create patient (align with schema that uses created_at default)
        $patientStmt = $this->db->prepare("
            INSERT INTO patients (user_id, patient_number) 
            VALUES (?, ?)
        ");
        $patientStmt->bind_param("is", $userId, $patientNumber);

        if (!$patientStmt->execute()) {
            throw new Exception('Failed to create patient record');
        }

        $patientId = $this->db->insert_id;

        return ['patient_id' => $patientId, 'user_id' => $userId];
    }

    /**
     * Create appointment with optimized single query
     * @return int|false
     */
    private function createAppointmentOptimized(array $data): int|false
    {
        $stmt = $this->db->prepare("
            INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, end_time, appointment_type, reason, status, created_by, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param(
            "iissssssi",
            $data['patient_id'],
            $data['doctor_id'],
            $data['appointment_date'],
            $data['appointment_time'],
            $data['end_time'],
            $data['appointment_type'],
            $data['reason'],
            $data['status'],
            $data['created_by']
        );

        if (!$stmt->execute()) {
            return false;
        }

        return $this->db->insert_id;
    }

    /**
     * Generate unique patient number
     */
    private function generatePatientNumber(): string
    {
        $prefix = 'NYA';
        $year = date('Y');

        // Get next sequential number for this year
        $stmt = $this->db->prepare("
            SELECT MAX(CAST(SUBSTRING(patient_number, 8) AS UNSIGNED)) as max_num 
            FROM patients 
            WHERE patient_number LIKE CONCAT(?, ?, '%')
        ");
        $stmt->bind_param("ss", $prefix, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $nextNum = ($row['max_num'] ?? 0) + 1;
        return $prefix . $year . str_pad((string) $nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Queue confirmation email for background processing
     */
    private function queueConfirmationEmail(string $email, int $appointmentId): void
    {
        try {
            // Check if email_queue table exists
            $tableCheck = $this->db->query("SHOW TABLES LIKE 'email_queue'");

            if ($tableCheck && $tableCheck->num_rows > 0) {
                // Write to email queue table for background processing
                $stmt = $this->db->prepare("
                    INSERT INTO email_queue (email, type, reference_id, status, created_at) 
                    VALUES (?, 'appointment_confirmation', ?, 'pending', NOW())
                ");

                if ($stmt) {
                    $stmt->bind_param("si", $email, $appointmentId);
                    $stmt->execute();
                    $stmt->close();
                }
            } else {
                // Table doesn't exist - just log (non-blocking)
                error_log("Warning: email_queue table does not exist. Email queuing skipped for: $email (Appointment ID: $appointmentId)");
            }

            // Log for immediate feedback (non-blocking)
            error_log("Confirmation email queued for: $email (Appointment ID: $appointmentId)");
        } catch (Exception $e) {
            // Don't throw - email queue failure should not block appointment creation
            error_log("Error queueing confirmation email: " . $e->getMessage() . " for: $email (Appointment ID: $appointmentId)");
        }
    }

    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
