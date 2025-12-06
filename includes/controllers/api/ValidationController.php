<?php

/**
 * Nyalife HMS - Validation API Controller
 *
 * Handles client-side validation requests to reduce server load
 */

require_once __DIR__ . '/../web/WebController.php';

class ValidationController extends WebController
{
    protected $requiresLogin = false; // Allow guest access for validation endpoints
    private readonly \UserModel $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    /**
     * Validate username availability
     */
    public function validateUsername(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['username']) || empty($input['username'])) {
                throw new Exception('Username is required');
            }

            $username = trim((string) $input['username']);
            if (strlen($username) < 3) {
                $this->jsonResponse([
                    'success' => true,
                    'available' => false,
                    'message' => 'Username too short'
                ]);
                return;
            }

            // Use userModel to check existence
            $exists = $this->userModel->usernameExists($username);

            $this->jsonResponse([
                'success' => true,
                'available' => !$exists,
                'message' => $exists ? 'Username already taken' : 'Username available'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'available' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Validate email availability
     */
    public function validateEmail(): void
    {
        try {
            // Only allow POST requests
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['email']) || empty($input['email'])) {
                throw new Exception('Email is required');
            }

            $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
            if (!$email) {
                $this->jsonResponse([
                    'success' => false,
                    'available' => false,
                    'message' => 'Invalid email format'
                ]);
                return;
            }

            // Quick database check
            $db = DatabaseManager::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            $available = $result->num_rows === 0;

            $this->jsonResponse([
                'success' => true,
                'available' => $available,
                'message' => $available ? 'Email available' : 'Email already registered'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'available' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Validate appointment date and time
     */
    public function validateAppointment(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $input = json_decode(file_get_contents('php://input'), true);

            $date = $input['date'] ?? '';
            $time = $input['time'] ?? '';
            $doctorId = $input['doctor_id'] ?? null;

            if (empty($date) || empty($time)) {
                throw new Exception('Date and time are required');
            }

            // Validate date format
            $appointmentDateTime = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
            if (!$appointmentDateTime) {
                throw new Exception('Invalid date or time format');
            }

            // Check if in the future
            if ($appointmentDateTime <= new DateTime()) {
                $this->jsonResponse([
                    'success' => false,
                    'available' => false,
                    'message' => 'Appointment must be in the future'
                ]);
                return;
            }

            // Check within defined opening windows using Utilities
            if (!Utilities::isWithinOpeningHours($appointmentDateTime)) {
                $next = Utilities::nextValidStart(clone $appointmentDateTime);
                $suggest = $next instanceof \DateTime ? $next->format('M d, Y h:i A') : null;
                $this->jsonResponse([
                    'success' => false,
                    'available' => false,
                    'message' => 'Selected time is outside clinic hours.' . ($suggest ? ' Next available: ' . $suggest : '')
                ]);
                return;
            }

            // Check for conflicts if doctor is specified
            $conflicts = false;
            if ($doctorId) {
                $db = DatabaseManager::getInstance()->getConnection();
                $stmt = $db->prepare("
                    SELECT appointment_id 
                    FROM appointments 
                    WHERE doctor_id = ? 
                    AND appointment_date = ? 
                    AND appointment_time = ? 
                    AND status IN ('scheduled', 'confirmed')
                    LIMIT 1
                ");
                $stmt->bind_param("iss", $doctorId, $date, $time);
                $stmt->execute();
                $result = $stmt->get_result();
                $conflicts = $result->num_rows > 0;
            }

            $this->jsonResponse([
                'success' => true,
                'available' => !$conflicts,
                'message' => $conflicts ? 'Time slot not available' : 'Time slot available'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'available' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get available time slots for a specific date and doctor
     */
    public function getAvailableSlots(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new Exception('Invalid request method');
            }

            $date = $_GET['date'] ?? '';
            $doctorId = $_GET['doctor_id'] ?? null;

            if (empty($date)) {
                throw new Exception('Date is required');
            }

            // Generate time slots using Utilities
            $slots = Utilities::generateTimeSlots($date, 30);

            // Remove booked slots if doctor is specified
            if ($doctorId) {
                $db = DatabaseManager::getInstance()->getConnection();
                $stmt = $db->prepare("
                    SELECT appointment_time 
                    FROM appointments 
                    WHERE doctor_id = ? 
                    AND appointment_date = ? 
                    AND status IN ('scheduled', 'confirmed')
                ");
                $stmt->bind_param("is", $doctorId, $date);
                $stmt->execute();
                $result = $stmt->get_result();

                $bookedSlots = [];
                while ($row = $result->fetch_assoc()) {
                    $bookedSlots[] = substr((string) $row['appointment_time'], 0, 5); // Remove seconds
                }

                $slots = array_diff($slots, $bookedSlots);
            }

            $this->jsonResponse([
                'success' => true,
                'slots' => array_values($slots)
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get available doctors for a specific date and time
     */
    public function getAvailableDoctors(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new Exception('Invalid request method');
            }

            $date = $_GET['date'] ?? '';
            $time = $_GET['time'] ?? '';

            if (empty($date) || empty($time)) {
                throw new Exception('Date and time are required');
            }

            // Validate date/time format
            $appointmentDateTime = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
            if (!$appointmentDateTime) {
                throw new Exception('Invalid date or time format');
            }

            // Optional: ensure within opening hours
            if (!Utilities::isWithinOpeningHours($appointmentDateTime)) {
                $this->jsonResponse([
                    'success' => true,
                    'doctors' => [],
                    'message' => 'Outside clinic hours'
                ]);
                return;
            }

            $db = DatabaseManager::getInstance()->getConnection();
            // Find doctors (staff + users role doctor) who do NOT have a conflicting appointment at that datetime
            $sql = "
                SELECT 
                    u.user_id,
                    s.staff_id,
                    u.first_name,
                    u.last_name,
                    s.specialization,
                    s.department
                FROM staff s
                JOIN users u ON s.user_id = u.user_id
                JOIN roles r ON u.role_id = r.role_id
                LEFT JOIN appointments a
                    ON a.doctor_id = s.staff_id
                    AND a.appointment_date = ?
                    AND a.appointment_time = ?
                    AND a.status IN ('scheduled','confirmed')
                WHERE r.role_name = 'doctor'
                  AND a.appointment_id IS NULL
                ORDER BY u.last_name, u.first_name
            ";

            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception('Query preparation failed');
            }
            $stmt->bind_param('ss', $date, $time);
            $stmt->execute();
            $res = $stmt->get_result();

            $doctors = [];
            while ($row = $res->fetch_assoc()) {
                $doctors[] = [
                    'user_id' => (int)$row['user_id'],
                    'staff_id' => (int)$row['staff_id'],
                    'name' => trim($row['first_name'] . ' ' . $row['last_name']),
                    'specialization' => $row['specialization'] ?? null,
                    'department' => $row['department'] ?? null
                ];
            }

            $this->jsonResponse([
                'success' => true,
                'doctors' => $doctors
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
