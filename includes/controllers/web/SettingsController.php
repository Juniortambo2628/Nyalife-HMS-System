<?php

/**
 * Nyalife HMS - Settings Controller
 *
 * Controller for system settings pages.
 */

require_once __DIR__ . '/WebController.php';

class SettingsController extends WebController
{
    /** @var array */
    protected $allowedRoles = ['admin'];

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'System Settings - Nyalife HMS';
    }

    /**
     * Display the settings page
     */
    public function index(): void
    {
        // Get system statistics for display
        $systemStats = $this->getSystemStats();

        $this->renderView('settings/index', [
            'pageTitle' => $this->pageTitle,
            'activeMenu' => 'admin_settings',
            'systemStats' => $systemStats
        ]);
    }

    /**
     * Display user management settings
     */
    public function users(): void
    {
        $this->renderView('settings/users', [
            'pageTitle' => 'User Management Settings - Nyalife HMS',
            'activeMenu' => 'admin_settings'
        ]);
    }

    /**
     * Display system configuration settings
     */
    public function system(): void
    {
        $this->renderView('settings/system', [
            'pageTitle' => 'System Configuration - Nyalife HMS',
            'activeMenu' => 'admin_settings'
        ]);
    }

    /**
     * Display database settings
     */
    public function database(): void
    {
        $this->renderView('settings/database', [
            'pageTitle' => 'Database Settings - Nyalife HMS',
            'activeMenu' => 'admin_settings'
        ]);
    }

    /**
     * Get system statistics for settings overview
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

            // Get department count
            $result = $this->db->query("SELECT COUNT(*) as count FROM departments WHERE is_active = 1");
            $stats['totalDepartments'] = $result ? $result->fetch_assoc()['count'] : 0;

            // Get system uptime (simplified)
            $stats['systemUptime'] = '24/7';

            return $stats;
        } catch (Exception $e) {
            error_log("Error getting system stats: " . $e->getMessage());
            return [
                'totalUsers' => 0,
                'totalPatients' => 0,
                'totalDoctors' => 0,
                'totalNurses' => 0,
                'totalDepartments' => 0,
                'systemUptime' => 'Unknown'
            ];
        }
    }
}
