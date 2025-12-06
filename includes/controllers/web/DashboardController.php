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

class DashboardController extends WebController
{
    protected \UserModel $userModel;

    protected \PatientModel $patientModel;

    protected \AppointmentModel $appointmentModel;

    protected \LabTestModel $labTestModel;

    protected \PrescriptionModel $prescriptionModel;

    /**
     * Initialize the controller
     */
    public function __construct()
    {
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
     */
    public function index($role = null): void
    {
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
            'phone' => $user['phone'],
            'role' => $role // Add the role to currentUser
        ];

        // Set active menu for all dashboard views
        $activeMenu = 'dashboard';

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
                    'baseUrl' => $baseUrl,
                    'activeMenu' => $activeMenu
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
                $currentUser['role'] = $role;

                // Render the doctor dashboard view
                $this->renderView('dashboard/doctor', [
                    'todayAppointments' => $todayAppointments,
                    'upcomingAppointments' => $upcomingAppointments,
                    'recentPatients' => $recentPatients,
                    'patientCount' => $patientCount,
                    'appointmentCount' => $appointmentCount,
                    'pendingAppointments' => count($pendingAppointments),
                    'currentUser' => $currentUser,
                    'baseUrl' => $baseUrl,
                    'activeMenu' => $activeMenu
                ]);
                break;

            case 'nurse':
                // Get all patients for the counter
                $patientCount = count($this->patientModel->getAllPatients());

                // Get today's appointments for the hospital
                $todayAppointments = $this->appointmentModel->getAllAppointmentsByDate($today);

                // Upcoming appointments (next 3 days, excluding today)
                $upcomingAppointments = $this->appointmentModel->getUpcomingAppointmentsByDateRange($today, 3);

                // Get pending appointments
                $pendingAppointments = $this->appointmentModel->getAppointmentsFiltered(['status' => 'pending']);

                // Render the nurse dashboard view
                $this->renderView('dashboard/nurse', [
                    'patientCount' => $patientCount,
                    'todayAppointments' => $todayAppointments,
                    'upcomingAppointments' => $upcomingAppointments,
                    'pendingAppointments' => $pendingAppointments,
                    'currentUser' => $currentUser,
                    'baseUrl' => $baseUrl,
                    'activeMenu' => $activeMenu
                ]);
                break;

            case 'lab_technician':
                // Get lab test data using the LabTestModel
                $pendingLabTests = $this->labTestModel->getPendingTests();
                $completedLabTests = $this->labTestModel->getCompletedTests('', 1, 10); // Show 10 most recent completed tests

                // Render the lab technician dashboard view
                $this->renderView('dashboard/lab_technician', [
                    'pendingLabTests' => $pendingLabTests,
                    'completedLabTests' => $completedLabTests,
                    'currentUser' => $currentUser,
                    'baseUrl' => $baseUrl,
                    'activeMenu' => $activeMenu
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
                    'baseUrl' => $baseUrl,
                    'activeMenu' => $activeMenu
                ]);
                break;

            case 'patient':
                // Get patient-specific data
                $patientId = $this->patientModel->getPatientIdByUserId($userId);
                if ($patientId) {
                    $patientDetails = $this->patientModel->getById($patientId);
                    $upcomingAppointments = $this->appointmentModel->getPatientAppointments($patientId, null, 'scheduled');
                    $pastAppointments = $this->appointmentModel->getPastPatientAppointments($patientId, 5);
                    // Get lab results using PatientController's method
                    $labResults = $this->getPatientLabResults($patientId);
                } else {
                    // If no patient record exists yet, show empty data
                    $patientDetails = [];
                    $upcomingAppointments = [];
                    $pastAppointments = [];
                    $labResults = [];
                }

                // Render the patient dashboard view
                $this->renderView('dashboard/patient', [
                    'patientDetails' => $patientDetails,
                    'upcomingAppointments' => $upcomingAppointments,
                    'pastAppointments' => $pastAppointments,
                    'labResults' => $labResults,
                    'currentUser' => $currentUser,
                    'baseUrl' => $baseUrl,
                    'activeMenu' => $activeMenu
                ]);
                break;

            default:
                // Default view for other roles or if role is not set
                $this->renderView('dashboard/default', [
                    'currentUser' => $currentUser,
                    'baseUrl' => $baseUrl,
                    'activeMenu' => $activeMenu
                ]);
                break;
        }
    }

    /**
     * Get patient lab results (same as PatientController)
     */
    private function getPatientLabResults($patientId)
    {
        try {
            $sql = "SELECT 
                        ti.test_item_id as result_id,
                        ti.status as result_status,
                        ti.result_value,
                        COALESCE(ti.sample_reported_at, ti.performed_at, lr.completed_at, lr.request_date) as test_date,
                        lt.test_name,
                        COALESCE(CONCAT(ud.first_name, ' ', ud.last_name), CONCAT(up.first_name, ' ', up.last_name)) as doctor_name
                    FROM lab_test_items ti
                    JOIN lab_test_requests lr ON ti.request_id = lr.request_id
                    LEFT JOIN lab_test_types lt ON ti.test_type_id = lt.test_type_id
                    LEFT JOIN users ud ON ti.verified_by = ud.user_id
                    LEFT JOIN users up ON ti.performed_by = up.user_id
                    WHERE lr.patient_id = ? AND ti.status IN ('completed', 'pending', 'processing')
                    ORDER BY test_date DESC
                    LIMIT 10";

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
