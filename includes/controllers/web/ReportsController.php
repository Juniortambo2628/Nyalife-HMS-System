<?php

/**
 * Nyalife HMS - Reports Controller
 *
 * Controller for system reports pages.
 */

require_once __DIR__ . '/WebController.php';

class ReportsController extends WebController
{
    /** @var array */
    protected $allowedRoles = ['admin'];

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Reports - Nyalife HMS';
    }

    /**
     * Display the reports page
     */
    public function index(): void
    {
        // Get comprehensive system statistics
        $stats = $this->getSystemStats();

        $this->renderView('reports/index', [
            'pageTitle' => $this->pageTitle,
            'activeMenu' => 'admin_reports',
            'stats' => $stats
        ]);
    }

    /**
     * Display appointment reports
     */
    public function appointments(): void
    {
        $this->renderView('reports/appointments', [
            'pageTitle' => 'Appointment Reports - Nyalife HMS',
            'activeMenu' => 'admin_reports'
        ]);
    }

    /**
     * Display patient reports
     */
    public function patients(): void
    {
        $this->renderView('reports/patients', [
            'pageTitle' => 'Patient Reports - Nyalife HMS',
            'activeMenu' => 'admin_reports'
        ]);
    }

    /**
     * Display financial reports
     */
    public function financial(): void
    {
        $this->renderView('reports/financial', [
            'pageTitle' => 'Financial Reports - Nyalife HMS',
            'activeMenu' => 'admin_reports'
        ]);
    }

    /**
     * Display laboratory reports
     */
    public function laboratory(): void
    {
        $this->renderView('reports/laboratory', [
            'pageTitle' => 'Laboratory Reports - Nyalife HMS',
            'activeMenu' => 'admin_reports'
        ]);
    }

    /**
     * Display pharmacy reports
     */
    public function pharmacy(): void
    {
        $this->renderView('reports/pharmacy', [
            'pageTitle' => 'Pharmacy Reports - Nyalife HMS',
            'activeMenu' => 'admin_reports'
        ]);
    }

    /**
     * Get comprehensive system statistics for reports
     */
    private function getSystemStats(): array
    {
        try {
            // $this->db is inherited from BaseController and always initialized
            $db = $this->db;

            $stats = [];

            // Get user counts
            $result = $this->db->query("SELECT COUNT(*) as count FROM users");
            $stats['totalUsers'] = $result ? $result->fetch_assoc()['count'] : 0;

            // Get patient count
            $result = $this->db->query("SELECT COUNT(*) as count FROM patients");
            $stats['totalPatients'] = $result ? $result->fetch_assoc()['count'] : 0;

            // Get doctor count (staff with doctor role)
            $result = $this->db->query("SELECT COUNT(*) as count 
                                       FROM staff s 
                                       JOIN users u ON s.user_id = u.user_id 
                                       JOIN roles r ON u.role_id = r.role_id 
                                       WHERE r.role_name = 'doctor'");
            $stats['totalDoctors'] = $result ? $result->fetch_assoc()['count'] : 0;

            // Get nurse count (staff with nurse role)
            $result = $this->db->query("SELECT COUNT(*) as count 
                                       FROM staff s 
                                       JOIN users u ON s.user_id = u.user_id 
                                       JOIN roles r ON u.role_id = r.role_id 
                                       WHERE r.role_name = 'nurse'");
            $stats['totalNurses'] = $result ? $result->fetch_assoc()['count'] : 0;

            // Get appointment counts
            $result = $this->db->query("SELECT COUNT(*) as count FROM appointments");
            $stats['totalAppointments'] = $result ? $result->fetch_assoc()['count'] : 0;

            $result = $this->db->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'pending'");
            $stats['pendingAppointments'] = $result ? $result->fetch_assoc()['count'] : 0;

            $result = $this->db->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'completed'");
            $stats['completedAppointments'] = $result ? $result->fetch_assoc()['count'] : 0;

            // Get consultation count
            $result = $this->db->query("SELECT COUNT(*) as count FROM consultations");
            $stats['totalConsultations'] = $result ? $result->fetch_assoc()['count'] : 0;

            // Get lab test count
            $result = $this->db->query("SELECT COUNT(*) as count FROM lab_tests");
            $stats['totalLabTests'] = $result ? $result->fetch_assoc()['count'] : 0;

            // Get invoice and revenue data
            $result = $this->db->query("SELECT COUNT(*) as count FROM invoices");
            $stats['totalInvoices'] = $result ? $result->fetch_assoc()['count'] : 0;

            $result = $this->db->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE payment_status = 'completed'");
            $stats['totalRevenue'] = $result ? $result->fetch_assoc()['total'] : 0;

            return $stats;
        } catch (Exception $e) {
            error_log("Error getting system stats: " . $e->getMessage());
            return [
                'totalUsers' => 0,
                'totalPatients' => 0,
                'totalDoctors' => 0,
                'totalNurses' => 0,
                'totalAppointments' => 0,
                'pendingAppointments' => 0,
                'completedAppointments' => 0,
                'totalConsultations' => 0,
                'totalLabTests' => 0,
                'totalInvoices' => 0,
                'totalRevenue' => 0
            ];
        }
    }

    /**
     * Generate daily report
     */
    public function daily(): void
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $this->renderView('reports/daily', [
            'pageTitle' => 'Daily Report - Nyalife HMS',
            'activeMenu' => 'admin_reports',
            'reportDate' => $date
        ]);
    }

    /**
     * Generate weekly report
     */
    public function weekly(): void
    {
        $startDate = $_GET['start'] ?? date('Y-m-d', strtotime('monday this week'));
        $endDate = $_GET['end'] ?? date('Y-m-d', strtotime('sunday this week'));
        
        $this->renderView('reports/weekly', [
            'pageTitle' => 'Weekly Report - Nyalife HMS',
            'activeMenu' => 'admin_reports',
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    /**
     * Generate monthly report
     */
    public function monthly(): void
    {
        $month = $_GET['month'] ?? date('Y-m');
        
        $this->renderView('reports/monthly', [
            'pageTitle' => 'Monthly Report - Nyalife HMS',
            'activeMenu' => 'admin_reports',
            'reportMonth' => $month
        ]);
    }

    /**
     * Export all data
     */
    public function exportAll(): void
    {
        // Set appropriate headers for file download
        header('Content-Type: application/json');
        
        $exportData = [
            'status' => 'success',
            'message' => 'Export feature in progress',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode($exportData);
        exit;
    }
}
