<?php
/**
 * Nyalife HMS - Dashboard Controller
 * 
 * Controller for the dashboard pages.
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/PatientModel.php';
require_once __DIR__ . '/../../models/AppointmentModel.php';
require_once __DIR__ . '/../../models/LabTestModel.php';
require_once __DIR__ . '/../../models/PrescriptionModel.php';

class DashboardController extends WebController {
    protected $userModel;
    protected $patientModel;
    protected $appointmentModel;
    protected $labTestModel;
    protected $prescriptionModel;
    
    /**
     * Initialize the controller
     */
    public function __construct() {
        parent::__construct();
        $this->pageTitle = 'Dashboard - Nyalife HMS';
        $this->userModel = new UserModel();
        $this->patientModel = new PatientModel();
        $this->appointmentModel = new AppointmentModel();
        $this->labTestModel = new LabTestModel();
        $this->prescriptionModel = new PrescriptionModel();
    }
    
    /**
     * Display the appropriate dashboard based on user role
     * 
     * @param string|null $role User role from URL parameter
     * @return void
     */
    public function index($role = null) {
        // Use the URL role if provided, otherwise use the session role
        if (empty($role) || $role == 'dashboard') {
            $role = SessionManager::get('role');
        }
        $userId = SessionManager::get('user_id');
        
        // Get base URL
        $baseUrl = getBaseUrl();
        
        // Get user information for all dashboards
        $user = $this->userModel->getUserById($userId);
        $currentUser = [
            'firstName' => $user['first_name'],
            'lastName' => $user['last_name'],
            'email' => $user['email'],
            'phone' => $user['phone']
        ];

        
        // Set today's date for appointment filtering
        $today = date('Y-m-d');
        
        switch ($role) {
            case 'admin':
                // Get total counts
                $userCount = $this->userModel->getActiveUserCount();
                $patientCount = count($this->patientModel->getAllPatients());
                $appointmentCount = $this->appointmentModel->getTotalAppointmentsCount();
                
                // Get recent users
                $recentUsers = $this->userModel->getRecentUsers(5);
            
            // Get count of all pending appointments
            $pendingAppointments = $this->appointmentModel->countAppointmentsByStatus('pending');
            
            // Render the admin dashboard view
            $this->renderView('dashboard/admin', [
                'userCount' => $userCount,
                'patientCount' => $patientCount,
                'appointmentCount' => $appointmentCount,
                'recentUsers' => $recentUsers,
                'pendingAppointments' => $pendingAppointments,
                'currentUser' => $currentUser,
                'baseUrl' => $baseUrl
            ]);
            break;
                
            case 'doctor':
                // Get doctor-specific data
                $doctorId = $this->userModel->getDoctorIdByUserId($userId);
                
                // Today's appointments
                $todayAppointments = $this->appointmentModel->getDoctorAppointments($doctorId, $today);
                
                // Upcoming appointments (next 7 days, excluding today)
                $upcomingAppointments = $this->appointmentModel->getUpcomingDoctorAppointments($doctorId, $today, 7);
                
                // Recent patients
                $recentPatients = $this->patientModel->getRecentPatientsByDoctor($doctorId, 5);
                
                // Patient count for this doctor
                $patientCount = $this->patientModel->getPatientCountByDoctor($doctorId);
                
                // Total appointments count
                $appointmentCount = $this->appointmentModel->getDoctorAppointmentCount($doctorId);
                
                // Pending appointments
                $pendingAppointments = $this->appointmentModel->getDoctorAppointmentsByStatus($doctorId, 'pending');
                
                // Ensure currentUser has role set
                if (!isset($currentUser['role'])) {
                    $currentUser['role'] = 'doctor'; // Default role
                }
                
                // Render the doctor dashboard view
                $this->renderView('dashboard/doctor', [
                    'todayAppointments' => $todayAppointments,
                    'upcomingAppointments' => $upcomingAppointments,
                    'recentPatients' => $recentPatients,
                    'patientCount' => $patientCount,
                    'appointmentCount' => $appointmentCount,
                    'pendingAppointments' => count($pendingAppointments),
                    'currentUser' => $currentUser,
                    'baseUrl' => $baseUrl
                ]);
                break;
                
            case 'nurse':
                // Get all patients for the counter
                $patientCount = count($this->patientModel->getAllPatients());
                
                // Get today's appointments for the hospital
                $todayAppointments = $this->appointmentModel->getAllAppointmentsByDate($today);
                
                // Upcoming appointments (next 3 days, excluding today)
                $upcomingAppointments = $this->appointmentModel->getUpcomingAppointmentsByDateRange($today, 3);
                
                // Render the nurse dashboard view
                $this->renderView('dashboard/nurse', [
                    'patientCount' => $patientCount,
                    'todayAppointments' => $todayAppointments, 
                    'upcomingAppointments' => $upcomingAppointments,
                    'currentUser' => $currentUser,
                    'baseUrl' => $baseUrl
                ]);
                break;
                
            case 'lab_technician':
                // Get lab test data using the LabTestModel
                $pendingLabTests = $this->labTestModel->getPendingTests();
                $completedLabTests = $this->labTestModel->getCompletedTests(10); // Show 10 most recent completed tests
                
                // Render the lab technician dashboard view
                $this->renderView('dashboard/lab_technician', [
                    'pendingLabTests' => $pendingLabTests,
                    'completedLabTests' => $completedLabTests,
                    'currentUser' => $currentUser,
                    'baseUrl' => $baseUrl
                ]);
                break;
                
            case 'pharmacist':
                // Get prescription data using the PrescriptionModel
                $pendingPrescriptions = $this->prescriptionModel->getPendingPrescriptions();
                $completedPrescriptions = $this->prescriptionModel->getCompletedPrescriptions(10); // Show 10 most recent completed prescriptions
                
                // Render the pharmacist dashboard view
                $this->renderView('dashboard/pharmacist', [
                    'pendingPrescriptions' => $pendingPrescriptions,
                    'completedPrescriptions' => $completedPrescriptions,
                    'currentUser' => $currentUser,
                    'baseUrl' => $baseUrl
                ]);
                break;
                
            case 'patient':
                // Get patient-specific data
                $patientId = $this->patientModel->getPatientIdByUserId($userId);
                if ($patientId) {
                    $patientDetails = $this->patientModel->getById($patientId);
                    $upcomingAppointments = $this->appointmentModel->getPatientAppointments($patientId, null, 'scheduled');
                    $pastAppointments = $this->appointmentModel->getPastPatientAppointments($patientId, 5);
                } else {
                    // If no patient record exists yet, show empty data
                    $patientDetails = [];
                    $upcomingAppointments = [];
                    $pastAppointments = [];
                }
                
                // Render the patient dashboard view
                $this->renderView('dashboard/patient', [
                    'patientDetails' => $patientDetails,
                    'upcomingAppointments' => $upcomingAppointments,
                    'pastAppointments' => $pastAppointments,
                    'currentUser' => $currentUser,
                    'baseUrl' => $baseUrl
                ]);
                break;
                
            default:
                // Default view for other roles or if role is not set
                $this->renderView('dashboard/default', [
                    'currentUser' => $currentUser,
                    'baseUrl' => $baseUrl
                ]);
                break;
        }
    }
}
