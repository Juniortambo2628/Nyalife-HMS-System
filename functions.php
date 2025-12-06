<?php
/**
 * Nyalife HMS - Common Functions
 * 
 * This file contains reusable functions for the Nyalife HMS system.
 * 
 * Primary Color: #058b7c
 * Secondary Color: #d41559
 */

// Include required files
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/validation_functions.php';
require_once __DIR__ . '/id_generator.php';
require_once __DIR__ . '/modal_functions.php';
require_once __DIR__ . '/includes/db_utils.php';

// Global variable to store body classes
$GLOBALS['body_classes'] = [];

/**
 * Add one or more classes to the body element
 * 
 * @param string $classes Space-separated list of classes to add
 * @return void
 */
function add_body_class($classes) {
    if (!isset($GLOBALS['body_classes'])) {
        $GLOBALS['body_classes'] = [];
    }
    
    $class_array = explode(' ', $classes);
    foreach ($class_array as $class) {
        if (!empty($class) && !in_array($class, $GLOBALS['body_classes'])) {
            $GLOBALS['body_classes'][] = $class;
        }
    }
}

/**
 * Get all body classes as a space-separated string
 * 
 * @return string Space-separated list of body classes
 */
function get_body_classes() {
    return isset($GLOBALS['body_classes']) ? implode(' ', $GLOBALS['body_classes']) : '';
}

/**
 * Force hide the page loader
 * 
 * @return string JavaScript to hide the loader
 */
function dashboard_force_hide_loader() {
    return '<script>
        document.addEventListener("DOMContentLoaded", function() {
            if (window.NyalifeLoader && typeof NyalifeLoader.hide === "function") {
                NyalifeLoader.hide(true); // true = immediate hide
            }
        });
    </script>';
}

// Start session if not already started
function ensureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function isLoggedIn() {
    $auth = Auth::getInstance();
    return $auth->isLoggedIn();
}

// Check if user has a specific role
function hasRole($role) {
    $auth = Auth::getInstance();
    return $auth->hasRole($role);
}

// Check if user has any of the specified roles
function hasAnyRole($roles) {
    $auth = Auth::getInstance();
    return $auth->hasAnyRole($roles);
}

// Redirect user based on role
function redirectByRole() {
    $auth = Auth::getInstance();
    $auth->redirectByRole();
}

// Get role name from role ID
function getRoleName($roleId) {
    $sql = "SELECT role_name FROM roles WHERE role_id = ?";
    $result = selectSingle($sql, [$roleId]);
    
    return $result ? $result['role_name'] : null;
}

// Get user data by ID
function getUserById($userId) {
    $sql = "SELECT * FROM users WHERE user_id = ?";
    return selectSingle($sql, [$userId]);
}

// Get user data by username
function getUserByUsername($username) {
    $sql = "SELECT * FROM users WHERE username = ?";
    return selectSingle($sql, [$username]);
}

// Get user data by email
function getUserByEmail($email) {
    $sql = "SELECT * FROM users WHERE email = ?";
    return selectSingle($sql, [$email]);
}

// Generate ID functions are moved to id_generator.php

// Log user action to audit log
function logAction($userId, $action, $entityType, $entityId, $description, $oldValues = null, $newValues = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $sql = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, description, old_values, new_values, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $params = [$userId, $action, $entityType, $entityId, $description, $oldValues, $newValues, $ip, $userAgent];
    
    return executeQuery($sql, $params);
}

// Create response array for AJAX requests
function createResponse($success, $message, $data = null) {
    return [
        'success' => $success,
        'message' => $message,
        'data' => $data
    ];
}

// Send JSON response for AJAX requests
function sendJsonResponse($response) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

/**
 * Get the base URL for the application
 * 
 * @return string The base URL
 */
if (!function_exists('getBaseUrl')) {
    function getBaseUrl() {
    // Check if running from command line
    $isCli = php_sapi_name() === 'cli';
    
    if ($isCli) {
        // For CLI, provide a default base URL
        return 'http://localhost' . (defined('APP_PATH') ? APP_PATH : '/Nyalife-HMS-System');
    }

    // Get the protocol (http or https)
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
                (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? 
                'https://' : 'http://';
    
    // Get the domain and port
    $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    
    // Check if APP_PATH constant is defined
    if (defined('APP_PATH')) {
        $baseUrl = $protocol . $domain . APP_PATH;
    } else {
        // Fallback if APP_PATH is not defined: extract from script path
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $appPath = ($scriptDir == '/' || $scriptDir == '\\') ? '' : $scriptDir;
        $baseUrl = $protocol . $domain . $appPath;
    }
    
    // Debug information
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        error_log("Base URL calculation:");
        error_log("  Protocol: {$protocol}");
        error_log("  Domain: {$domain}");
        error_log("  APP_PATH: " . (defined('APP_PATH') ? APP_PATH : 'Not defined'));
        error_log("  Script Dir: " . dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        error_log("  Final Base URL: {$baseUrl}");
    }
    
    return $baseUrl;
    }
}

// Get dashboard URL based on role
function getDashboardUrl() {
    $baseUrl = getBaseUrl();
    $role = $_SESSION['role'] ?? '';
    
    // Use the dashboard route with role parameter instead of direct file paths
    return $baseUrl . '/dashboard/' . $role;
}

// Check if current page is active
function isActivePage($page) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    return $currentPage === $page;
}

// Get page-specific scripts
function getPageScripts($page) {
    $scripts = [];
    
    // No common scripts - we'll load nyalife.js directly in the footer
    
    // Page-specific scripts
    switch ($page) {
        case 'appointments.php':
            $scripts[] = getBaseUrl() . '/assets/js/pages/appointments.js';
            break;
        case 'consultations.php':
            $scripts[] = getBaseUrl() . '/assets/js/pages/consultations.js';
            break;
        case 'lab-requests.php':
            $scripts[] = getBaseUrl() . '/assets/js/pages/lab-requests.js';
            break;
        case 'prescriptions.php':
            $scripts[] = getBaseUrl() . '/assets/js/pages/prescriptions.js';
            break;
    }
    
    return $scripts;
}

/**
 * Hero Section Animation Functions
 * Used to control animations and interaction in the hero section
 */
function initHeroAnimations() {
    // Output the initialization script for hero animations
    ob_start();
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Make sure Nyalife object exists and hero module is available
        if (typeof Nyalife !== 'undefined' && Nyalife.hero) {
            console.log('Initializing Nyalife hero animations');
            Nyalife.hero.init();
            
            // Add an event listener for slide changes to ensure column highlights
            document.querySelectorAll('.hero-dot').forEach((dot, index) => {
                dot.addEventListener('click', function() {
                    setTimeout(() => {
                        console.log('Manual dot navigation, updating column:', index);
                        if (typeof window.updateActiveColumn === 'function') {
                            window.updateActiveColumn(index);
                        }
                    }, 100);
                });
            });
            
            // Also handle arrow navigation
            document.getElementById('prev-slide')?.addEventListener('click', function() {
                setTimeout(() => {
                    const activeSlide = document.querySelector('.hero-slide.active');
                    const slideIndex = Array.from(document.querySelectorAll('.hero-slide')).indexOf(activeSlide);
                    console.log('Previous button clicked, now on slide:', slideIndex);
                    if (typeof window.updateActiveColumn === 'function') {
                        window.updateActiveColumn(slideIndex);
                    }
                }, 100);
            });
            
            document.getElementById('next-slide')?.addEventListener('click', function() {
                setTimeout(() => {
                    const activeSlide = document.querySelector('.hero-slide.active');
                    const slideIndex = Array.from(document.querySelectorAll('.hero-slide')).indexOf(activeSlide);
                    console.log('Next button clicked, now on slide:', slideIndex);
                    if (typeof window.updateActiveColumn === 'function') {
                        window.updateActiveColumn(slideIndex);
                    }
                }, 100);
            });
        } else {
            console.error('Nyalife.hero module not available. Check that nyalife.js is loaded correctly.');
        }
        
        // Ensure modals have higher z-index than hero overlay
        const fixModals = function() {
            const modalElements = document.querySelectorAll('.modal');
            modalElements.forEach(modal => {
                modal.style.zIndex = '1050';
            });
            
            const modalDialogs = document.querySelectorAll('.modal-dialog');
            modalDialogs.forEach(dialog => {
                dialog.style.zIndex = '1060'; 
            });
            
            const modalContents = document.querySelectorAll('.modal-content');
            modalContents.forEach(content => {
                content.style.background = 'rgba(255, 255, 255, 0.98)';
                content.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.3)';
            });
            
            const modalBackdrops = document.querySelectorAll('.modal-backdrop');
            modalBackdrops.forEach(backdrop => {
                backdrop.style.zIndex = '1040';
                backdrop.style.opacity = '0.7';
            });
        };
        
        // Call initially
        fixModals();
        
        // Ensure modal backdrop has proper z-index
        document.addEventListener('show.bs.modal', fixModals);
        document.addEventListener('shown.bs.modal', fixModals);
        
        // Handle service box tooltips display
        document.querySelectorAll('.why-join-item').forEach((box, index) => {
            box.addEventListener('click', function(e) {
                console.log('Service box clicked:', index);
                
                // Fix any existing modal backdrop issues
                fixModals();
                
                // Ensure we're showing the right tooltip in the modal
                const tooltipContent = this.querySelector('.join-tooltip').innerHTML;
                const modalContent = document.getElementById('modalServiceContent');
                if (modalContent) {
                    modalContent.innerHTML = tooltipContent;
                }
                
                // Set the correct service modal background image
                const modal = document.querySelector('.service-modal-bg');
                if (modal) {
                    // Default background image path
                    let bgImagePath = 'assets/img/gallery/';
                    
                    // Select appropriate background image based on service type
                    switch (index) {
                        case 0: // Obstetrics Care
                            bgImagePath += 'Obstetrics-care.jpg';
                            break;
                        case 1: // Gynecology Services
                            bgImagePath += 'Gynecology-services.jpg';
                            break;
                        case 2: // Lab Services
                            bgImagePath += 'Laboratory-services.JPG';
                            break;
                        case 3: // Pharmacy
                            bgImagePath += 'Pharmacy.jpg';
                            break;
                        default:
                            bgImagePath += 'Obstetrics-care.jpg';
                    }
                    
                    // Set the background image
                    modal.style.backgroundImage = `url('${bgImagePath}')`;
                    console.log('Setting modal background:', bgImagePath);
                }
            });
        });
        
        // Handle modal close to ensure backdrop is removed
        document.addEventListener('hidden.bs.modal', function() {
            // Remove any lingering backdrops
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => {
                backdrop.remove();
            });
            
            // Remove modal-open class from body if no modals are open
            const openModals = document.querySelectorAll('.modal.show');
            if (openModals.length === 0) {
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

/**
 * Dashboard Statistics Functions
 */

// Get patient count
function getPatientCount() {
    $sql = "SELECT COUNT(*) as count FROM patients";
    $result = selectSingle($sql);
    return $result ? $result['count'] : 0;
}

// Get staff count
function getStaffCount() {
    $sql = "SELECT COUNT(*) as count FROM staff";
    $result = selectSingle($sql);
    return $result ? $result['count'] : 0;
}

// Get appointment count
function getAppointmentCount($status = null, $date = null) {
    $sql = "SELECT COUNT(*) as count FROM appointments WHERE 1=1";
    $params = [];
    
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    
    if ($date) {
        $sql .= " AND DATE(appointment_date) = ?";
        $params[] = $date;
    }
    
    $result = selectSingle($sql, $params);
    return $result ? $result['count'] : 0;
}

// Get doctor appointments
function getDoctorAppointments($doctorId, $status = null, $date = null, $returnCount = false) {
    $sql = "SELECT a.*, p.first_name, p.last_name, p.patient_number 
            FROM appointments a 
            JOIN patients p ON a.patient_id = p.patient_id 
            WHERE a.doctor_id = ?";
    $params = [$doctorId];
    
    if ($status) {
        $sql .= " AND a.status = ?";
        $params[] = $status;
    }
    
    if ($date) {
        $sql .= " AND DATE(a.appointment_date) = ?";
        $params[] = $date;
    }
    
    $sql .= " ORDER BY a.appointment_date ASC";
    
    if ($returnCount) {
        // If we just need the count, modify the query
        $countSql = "SELECT COUNT(*) as count FROM (" . $sql . ") as subquery";
        $result = selectSingle($countSql, $params);
        return $result ? $result['count'] : 0;
    }
    
    return selectQuery($sql, $params);
}

// Get lab request count
function getLabRequestCount($status = null) {
    $sql = "SELECT COUNT(*) as count FROM lab_test_requests WHERE 1=1";
    $params = [];
    
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    
    $result = selectSingle($sql, $params);
    return $result ? $result['count'] : 0;
}

// Get prescription count
function getPrescriptionCount($status = null) {
    $sql = "SELECT COUNT(*) as count FROM prescriptions WHERE 1=1";
    $params = [];
    
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    
    $result = selectSingle($sql, $params);
    return $result ? $result['count'] : 0;
}

// Get consultation count
function getConsultationCount($status = null, $doctorId = null) {
    $sql = "SELECT COUNT(*) as count FROM consultations WHERE 1=1";
    $params = [];
    
    if ($status) {
        $sql .= " AND consultation_status = ?";
        $params[] = $status;
    }
    
    if ($doctorId) {
        $sql .= " AND doctor_id = ?";
        $params[] = $doctorId;
    }
    
    $result = selectSingle($sql, $params);
    return $result ? $result['count'] : 0;
}

// Get user notifications
function getUserNotifications($userId, $limit = 5) {
    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
    return selectQuery($sql, [$userId, $limit]);
}

// Get patient appointments
function getPatientAppointments($patientId, $status = null) {
    $sql = "SELECT a.*, d.first_name as doctor_first_name, d.last_name as doctor_last_name 
            FROM appointments a 
            JOIN staff s ON a.doctor_id = s.staff_id 
            JOIN users d ON s.user_id = d.user_id 
            WHERE a.patient_id = ?";
    $params = [$patientId];
    
    if ($status) {
        $sql .= " AND a.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY a.appointment_date DESC";
    
    return selectQuery($sql, $params);
}

// Get recent appointments
function getRecentAppointments($limit = 5) {
    $sql = "SELECT a.*, p.first_name as patient_first_name, p.last_name as patient_last_name,
            d.first_name as doctor_first_name, d.last_name as doctor_last_name 
            FROM appointments a 
            JOIN patients pat ON a.patient_id = pat.patient_id 
            JOIN users p ON pat.user_id = p.user_id 
            JOIN staff s ON a.doctor_id = s.staff_id 
            JOIN users d ON s.user_id = d.user_id 
            ORDER BY a.created_at DESC LIMIT ?";
    
    return selectQuery($sql, [$limit]);
}

// Get recent lab requests
function getRecentLabRequests($limit = 5) {
    $sql = "SELECT lr.*, p.first_name as patient_first_name, p.last_name as patient_last_name 
            FROM lab_test_requests lr 
            JOIN patients pat ON lr.patient_id = pat.patient_id 
            JOIN users p ON pat.user_id = p.user_id 
            ORDER BY lr.created_at DESC LIMIT ?";
    
    return selectQuery($sql, [$limit]);
}

// Get recent prescriptions
function getRecentPrescriptions($limit = 5) {
    $sql = "SELECT pr.*, p.first_name as patient_first_name, p.last_name as patient_last_name 
            FROM prescriptions pr 
            JOIN patients pat ON pr.patient_id = pat.patient_id 
            JOIN users p ON pat.user_id = p.user_id 
            ORDER BY pr.created_at DESC LIMIT ?";
    
    return selectQuery($sql, [$limit]);
}

// Get staff ID from user ID
function getStaffIdFromUserId($userId) {
    $sql = "SELECT staff_id FROM staff WHERE user_id = ?";
    $result = selectSingle($sql, [$userId]);
    return $result ? $result['staff_id'] : null;
}

// Get patient ID from user ID
function getPatientIdFromUserId($userId) {
    $sql = "SELECT patient_id FROM patients WHERE user_id = ?";
    $result = selectSingle($sql, [$userId]);
    return $result ? $result['patient_id'] : null;
}

// Format date for display
function formatDate($dateString) {
    return date(DISPLAY_DATE_FORMAT, strtotime($dateString));
}

// Format time for display
function formatTime($timeString) {
    return date(DISPLAY_TIME_FORMAT, strtotime($timeString));
}

// Modal and loader functions moved to modal_functions.php

// Doctor Dashboard Functions

// Get today's appointments for a doctor (with $date param)
function getDoctorAppointmentsForDashboard($doctor_id, $date = null) {
    if (!$date) {
        $date = date('Y-m-d');
    }
    return selectQuery(
        "SELECT a.*, 
         p.patient_number,
         CONCAT(u.first_name, ' ', u.last_name) as patient_name,
         u.gender, u.date_of_birth,
         CONCAT(u.phone) as patient_phone
         FROM appointments a
         JOIN patients p ON a.patient_id = p.patient_id
         JOIN users u ON p.user_id = u.user_id
         WHERE a.doctor_id = ? 
         AND DATE(a.appointment_date) = ?
         ORDER BY a.appointment_time ASC",
        [$doctor_id, $date]
    );
}

// Get pending consultations for a doctor
function getPendingConsultations($doctor_id) {
    return selectQuery(
        "SELECT c.*, 
         p.patient_number,
         CONCAT(u.first_name, ' ', u.last_name) as patient_name,
         u.gender, u.date_of_birth,
         CONCAT(u.phone) as patient_phone,
         a.appointment_type, a.reason as appointment_reason
         FROM consultations c
         JOIN patients p ON c.patient_id = p.patient_id
         JOIN users u ON p.user_id = u.user_id
         LEFT JOIN appointments a ON c.appointment_id = a.appointment_id
         WHERE c.doctor_id = ? 
         AND c.consultation_status = 'open'
         ORDER BY c.consultation_date DESC",
        [$doctor_id]
    );
}

// Get recent consultations for a doctor
function getRecentConsultations($doctor_id, $limit = 5) {
    return selectQuery(
        "SELECT c.*, 
         p.patient_number,
         CONCAT(u.first_name, ' ', u.last_name) as patient_name,
         u.gender, u.date_of_birth,
         CONCAT(u.phone) as patient_phone,
         a.appointment_type
         FROM consultations c
         JOIN patients p ON c.patient_id = p.patient_id
         JOIN users u ON p.user_id = u.user_id
         LEFT JOIN appointments a ON c.appointment_id = a.appointment_id
         WHERE c.doctor_id = ? 
         ORDER BY c.consultation_date DESC
         LIMIT ?",
        [$doctor_id, $limit]
    );
}

// Get statistics for the doctor dashboard
function getDoctorStatistics($doctor_id) {
    $today = date('Y-m-d');
    $month_start = date('Y-m-01');
    $month_end = date('Y-m-t');
    // Today's appointments
    $today_appointments = selectSingle(
        "SELECT COUNT(*) as count FROM appointments 
         WHERE doctor_id = ? AND DATE(appointment_date) = ?",
        [$doctor_id, $today]
    );
    // Monthly consultations
    $monthly_consultations = selectSingle(
        "SELECT COUNT(*) as count FROM consultations 
         WHERE doctor_id = ? 
         AND consultation_date BETWEEN ? AND ?",
        [$doctor_id, $month_start, $month_end]
    );
    // Pending lab results
    $pending_lab_results = selectSingle(
        "SELECT COUNT(*) as count 
         FROM lab_test_requests r
         JOIN lab_test_items i ON r.request_id = i.request_id
         WHERE r.requested_by = ? 
         AND i.status = 'pending'",
        [$doctor_id]
    );
    // Pending prescriptions
    $pending_prescriptions = selectSingle(
        "SELECT COUNT(*) as count 
         FROM prescriptions p
         JOIN prescription_items i ON p.prescription_id = i.prescription_id
         WHERE p.prescribed_by = ? 
         AND i.status = 'pending'",
        [$doctor_id]
    );
    return [
        'today_appointments' => $today_appointments['count'] ?? 0,
        'monthly_consultations' => $monthly_consultations['count'] ?? 0,
        'pending_lab_results' => $pending_lab_results['count'] ?? 0,
        'pending_prescriptions' => $pending_prescriptions['count'] ?? 0
    ];
}

// Get upcoming follow-ups for a doctor
function getUpcomingFollowUps($doctor_id, $limit = 5) {
    return selectQuery(
        "SELECT f.*, 
         p.patient_number,
         CONCAT(u.first_name, ' ', u.last_name) as patient_name,
         u.gender, u.date_of_birth,
         CONCAT(u.phone) as patient_phone,
         c.diagnosis
         FROM follow_ups f
         JOIN consultations c ON f.consultation_id = c.consultation_id
         JOIN patients p ON f.patient_id = p.patient_id
         JOIN users u ON p.user_id = u.user_id
         WHERE c.doctor_id = ? 
         AND f.follow_up_date >= CURDATE()
         AND f.status = 'scheduled'
         ORDER BY f.follow_up_date ASC
         LIMIT ?",
        [$doctor_id, $limit]
    );
}

// Nurse Dashboard Functions

function getNurseAppointments($nurse_id) {
    $today = date('Y-m-d');
    return selectQuery(
        "SELECT a.*, p.patient_number, u.first_name, u.last_name, 
                s.staff_id, su.first_name as doctor_first_name, su.last_name as doctor_last_name,
                v.vital_id
         FROM appointments a
         JOIN patients p ON a.patient_id = p.patient_id
         JOIN users u ON p.user_id = u.user_id
         JOIN staff s ON a.doctor_id = s.staff_id
         JOIN users su ON s.user_id = su.user_id
         LEFT JOIN vital_signs v ON a.patient_id = v.patient_id AND DATE(v.measured_at) = a.appointment_date
         WHERE a.appointment_date = ? 
         ORDER BY a.appointment_time ASC",
        [$today]
    );
}

function getPendingVitalSigns($nurse_id) {
    $today = date('Y-m-d');
    return selectQuery(
        "SELECT a.*, p.patient_number, u.first_name, u.last_name, 
                s.staff_id, su.first_name as doctor_first_name, su.last_name as doctor_last_name
         FROM appointments a
         JOIN patients p ON a.patient_id = p.patient_id
         JOIN users u ON p.user_id = u.user_id
         JOIN staff s ON a.doctor_id = s.staff_id
         JOIN users su ON s.user_id = su.user_id
         LEFT JOIN vital_signs v ON a.patient_id = v.patient_id AND DATE(v.measured_at) = a.appointment_date
         WHERE a.appointment_date = ? 
         AND a.status = 'scheduled' 
         AND v.vital_id IS NULL
         ORDER BY a.appointment_time ASC",
        [$today]
    );
}

function getRecentVitalSigns($nurse_id) {
    return selectQuery(
        "SELECT v.*, p.patient_number, u.first_name, u.last_name
         FROM vital_signs v
         JOIN patients p ON v.patient_id = p.patient_id
         JOIN users u ON p.user_id = u.user_id
         WHERE v.recorded_by = ?
         ORDER BY v.measured_at DESC
         LIMIT 10",
        [$nurse_id]
    );
}

function getNurseStatistics($nurse_id) {
    $today = date('Y-m-d');
    // Get today's appointments count
    $today_appointments = selectSingle(
        "SELECT COUNT(*) as count 
         FROM appointments 
         WHERE appointment_date = ?",
        [$today]
    )['count'];
    // Get pending vital signs count
    $pending_vitals = selectSingle(
        "SELECT COUNT(*) as count 
         FROM appointments a 
         LEFT JOIN vital_signs v ON a.patient_id = v.patient_id AND DATE(v.measured_at) = a.appointment_date
         WHERE a.appointment_date = ? AND a.status = 'scheduled' AND v.vital_id IS NULL",
        [$today]
    )['count'];
    // Get monthly vital signs recorded
    $monthly_vitals = selectSingle(
        "SELECT COUNT(*) as count 
         FROM vital_signs 
         WHERE recorded_by = ? 
         AND MONTH(measured_at) = MONTH(CURRENT_DATE())
         AND YEAR(measured_at) = YEAR(CURRENT_DATE())",
        [$nurse_id]
    )['count'];
    // Get patient check-ins today
    $check_ins = selectSingle(
        "SELECT COUNT(*) as count 
         FROM appointments 
         WHERE appointment_date = ? 
         AND status != 'no_show'",
        [$today]
    )['count'];
    return [
        'today_appointments' => $today_appointments,
        'pending_vitals' => $pending_vitals,
        'monthly_vitals' => $monthly_vitals,
        'check_ins' => $check_ins
    ];
}

function getPatientDetailsForVitals($patient_id) {
    return selectSingle(
        "SELECT p.*, u.first_name, u.last_name, u.date_of_birth, u.gender
         FROM patients p
         JOIN users u ON p.user_id = u.user_id
         WHERE p.patient_id = ?",
        [$patient_id]
    );
}

function saveVitalSigns($data) {
    try {
        $conn = beginTransaction();
        // Insert vital signs
        $vital_id = executeQuery(
            "INSERT INTO vital_signs (
                patient_id, consultation_id, blood_pressure, heart_rate,
                respiratory_rate, temperature, weight, height, bmi,
                pain_level, oxygen_saturation, notes, measured_at, recorded_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)",
            [
                $data['patient_id'],
                $data['consultation_id'] ?? null,
                $data['blood_pressure'],
                $data['heart_rate'],
                $data['respiratory_rate'],
                $data['temperature'],
                $data['weight'],
                $data['height'],
                $data['bmi'],
                $data['pain_level'],
                $data['oxygen_saturation'],
                $data['notes'],
                $data['recorded_by']
            ]
        );
        // Update appointment status if needed
        if (isset($data['appointment_id'])) {
            executeQuery(
                "UPDATE appointments SET status = 'in_progress' WHERE appointment_id = ?",
                [$data['appointment_id']]
            );
        }
        commitTransaction($conn);
        return ['success' => true, 'vital_id' => $vital_id];
    } catch (Exception $e) {
        rollbackTransaction($conn);
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Patient Dashboard Functions

function getPatientId($user_id) {
    $result = selectSingle("SELECT patient_id FROM patients WHERE user_id = ?", [$user_id]);
    return $result ? $result['patient_id'] : null;
}

function getPatientDetails($patient_id) {
    return selectSingle(
        "SELECT p.*, u.email, u.phone
         FROM patients p
         JOIN users u ON p.user_id = u.user_id
         WHERE p.patient_id = ?",
        [$patient_id]
    );
}

function getUpcomingAppointments($patient_id) {
    return selectQuery(
        "SELECT a.*, 
                CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                s.specialization_name
         FROM appointments a
         JOIN staff d ON a.doctor_id = d.staff_id
         LEFT JOIN specializations s ON d.specialization_id = s.specialization_id
         WHERE a.patient_id = ? 
         AND a.appointment_date >= CURDATE()
         AND a.status != 'cancelled'
         ORDER BY a.appointment_date ASC, a.appointment_time ASC
         LIMIT 5",
        [$patient_id]
    );
}

function getRecentConsultationsForPatient($patient_id) {
    return selectQuery(
        "SELECT c.*, 
                CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
                s.specialization_name
         FROM consultations c
         JOIN staff d ON c.doctor_id = d.staff_id
         LEFT JOIN specializations s ON d.specialization_id = s.specialization_id
         WHERE c.patient_id = ?
         ORDER BY c.consultation_date DESC
         LIMIT 5",
        [$patient_id]
    );
}

function getActivePrescriptions($patient_id) {
    return selectQuery(
        "SELECT p.*, 
                m.medication_name,
                m.generic_name,
                m.form,
                m.strength,
                CONCAT(d.first_name, ' ', d.last_name) as doctor_name
         FROM prescriptions p
         JOIN medications m ON p.medication_id = m.medication_id
         JOIN staff d ON p.doctor_id = d.staff_id
         WHERE p.patient_id = ?
         AND p.status != 'completed'
         AND p.status != 'cancelled'
         ORDER BY p.prescribed_date DESC
         LIMIT 5",
        [$patient_id]
    );
}

function getPendingLabTests($patient_id) {
    return selectQuery(
        "SELECT lt.*, 
                t.test_name,
                CONCAT(d.first_name, ' ', d.last_name) as doctor_name
         FROM lab_test_items lt
         JOIN lab_tests t ON lt.test_id = t.test_id
         JOIN staff d ON lt.doctor_id = d.staff_id
         WHERE lt.patient_id = ?
         AND lt.status = 'pending'
         ORDER BY lt.request_date DESC
         LIMIT 5",
        [$patient_id]
    );
}

function getRecentLabResults($patient_id) {
    return selectQuery(
        "SELECT lt.*, 
                t.test_name,
                CONCAT(d.first_name, ' ', d.last_name) as doctor_name
         FROM lab_test_items lt
         JOIN lab_tests t ON lt.test_id = t.test_id
         JOIN staff d ON lt.doctor_id = d.staff_id
         WHERE lt.patient_id = ?
         AND lt.status = 'completed'
         ORDER BY lt.result_date DESC
         LIMIT 5",
        [$patient_id]
    );
}

// Pharmacist Dashboard Functions

function getPendingPrescriptions($pharmacist_id) {
    return selectQuery(
        "SELECT p.*, 
         pt.patient_number,
         CONCAT(u.first_name, ' ', u.last_name) as patient_name,
         CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
         s.specialization_name
         FROM prescriptions p
         JOIN patients pt ON p.patient_id = pt.patient_id
         JOIN users u ON pt.user_id = u.user_id
         JOIN staff s ON p.doctor_id = s.staff_id
         JOIN users d ON s.user_id = d.user_id
         WHERE p.pharmacist_id = ? 
         AND p.status = 'pending'
         ORDER BY p.prescribed_date ASC",
        [$pharmacist_id]
    );
}

function getRecentDispensedMedications($pharmacist_id, $limit = 5) {
    return selectQuery(
        "SELECT d.*, 
         p.patient_number,
         CONCAT(u.first_name, ' ', u.last_name) as patient_name,
         m.medication_name,
         m.generic_name
         FROM dispensed_medications d
         JOIN prescriptions p ON d.prescription_id = p.prescription_id
         JOIN patients pt ON p.patient_id = pt.patient_id
         JOIN users u ON pt.user_id = u.user_id
         JOIN medications m ON d.medication_id = m.medication_id
         WHERE d.dispensed_by = ?
         ORDER BY d.dispensed_at DESC
         LIMIT ?",
        [$pharmacist_id, $limit]
    );
}

function getMedicationInventory() {
    return selectQuery(
        "SELECT m.*, 
         COALESCE(SUM(d.quantity), 0) as dispensed_quantity,
         COALESCE(SUM(o.quantity), 0) as ordered_quantity
         FROM medications m
         LEFT JOIN dispensed_medications d ON m.medication_id = d.medication_id
         LEFT JOIN medication_orders o ON m.medication_id = o.medication_id
         WHERE m.is_active = 1
         GROUP BY m.medication_id
         ORDER BY m.medication_name ASC"
    );
}

function getPharmacistStatistics($pharmacist_id) {
    $today = date('Y-m-d');
    $month_start = date('Y-m-01');
    $month_end = date('Y-m-t');
    
    // Today's dispensed medications
    $today_dispensed = selectSingle(
        "SELECT COUNT(*) as count 
         FROM dispensed_medications 
         WHERE dispensed_by = ? 
         AND DATE(dispensed_at) = ?",
        [$pharmacist_id, $today]
    )['count'];
    
    // Pending prescriptions
    $pending_prescriptions = selectSingle(
        "SELECT COUNT(*) as count 
         FROM prescriptions 
         WHERE pharmacist_id = ? 
         AND status = 'pending'",
        [$pharmacist_id]
    )['count'];
    
    // Low stock items
    $low_stock = selectSingle(
        "SELECT COUNT(*) as count 
         FROM medications 
         WHERE stock_quantity < 10 
         AND is_active = 1"
    )['count'];
    
    // Monthly dispensed medications
    $monthly_dispensed = selectSingle(
        "SELECT COUNT(*) as count 
         FROM dispensed_medications 
         WHERE dispensed_by = ? 
         AND dispensed_at BETWEEN ? AND ?",
        [$pharmacist_id, $month_start, $month_end]
    )['count'];
    
    return [
        'today_dispensed' => $today_dispensed,
        'pending_prescriptions' => $pending_prescriptions,
        'low_stock' => $low_stock,
        'monthly_dispensed' => $monthly_dispensed
    ];
}

function getPrescriptionDetails($prescription_id) {
    return selectSingle(
        "SELECT p.*, 
         pt.patient_number,
         CONCAT(u.first_name, ' ', u.last_name) as patient_name,
         CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
         s.specialization_name
         FROM prescriptions p
         JOIN patients pt ON p.patient_id = pt.patient_id
         JOIN users u ON pt.user_id = u.user_id
         JOIN staff s ON p.doctor_id = s.staff_id
         JOIN users d ON s.user_id = d.user_id
         WHERE p.prescription_id = ?",
        [$prescription_id]
    );
}

function getPrescriptionItems($prescription_id) {
    return selectQuery(
        "SELECT pi.*, 
         m.medication_name,
         m.generic_name,
         m.stock_quantity
         FROM prescription_items pi
         JOIN medications m ON pi.medication_id = m.medication_id
         WHERE pi.prescription_id = ?",
        [$prescription_id]
    );
}

function dispenseMedication($data) {
    try {
        $conn = beginTransaction();
        
        // Insert dispensed medication record
        $dispensed_id = executeQuery(
            "INSERT INTO dispensed_medications (
                prescription_id, medication_id, quantity, 
                dispensed_by, dispensed_at, notes
            ) VALUES (?, ?, ?, ?, NOW(), ?)",
            [
                $data['prescription_id'],
                $data['medication_id'],
                $data['quantity'],
                $data['dispensed_by'],
                $data['notes'] ?? null
            ]
        );
        
        // Update medication stock
        executeQuery(
            "UPDATE medications 
             SET stock_quantity = stock_quantity - ? 
             WHERE medication_id = ?",
            [$data['quantity'], $data['medication_id']]
        );
        
        // Update prescription item status
        executeQuery(
            "UPDATE prescription_items 
             SET status = 'dispensed' 
             WHERE prescription_id = ? 
             AND medication_id = ?",
            [$data['prescription_id'], $data['medication_id']]
        );
        
        // Check if all items are dispensed
        $pending_items = selectSingle(
            "SELECT COUNT(*) as count 
             FROM prescription_items 
             WHERE prescription_id = ? 
             AND status != 'dispensed'",
            [$data['prescription_id']]
        )['count'];
        
        if ($pending_items === 0) {
            // Update prescription status to completed
            executeQuery(
                "UPDATE prescriptions 
                 SET status = 'completed' 
                 WHERE prescription_id = ?",
                [$data['prescription_id']]
            );
        }
        
        commitTransaction($conn);
        return ['success' => true, 'dispensed_id' => $dispensed_id];
    } catch (Exception $e) {
        rollbackTransaction($conn);
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Check if user has required role
 * 
 * @param string|array $requiredRole Role or array of roles to check
 * @return bool Whether user has required role
 */
function checkRole($requiredRole) {
    $auth = Auth::getInstance();
    if (!$auth->isLoggedIn()) {
        return false;
    }
    
    if (is_array($requiredRole)) {
        return $auth->hasAnyRole($requiredRole);
    }
    
    return $auth->hasRole($requiredRole);
}