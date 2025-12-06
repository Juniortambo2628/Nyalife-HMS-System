<?php
/**
 * Nyalife HMS - Home Controller
 * 
 * Controller for the landing page.
 */

require_once __DIR__ . '/WebController.php';

class HomeController extends WebController {
    // Override to allow public access without login
    protected $requiresLogin = false;
    
    /**
     * Initialize the controller
     */
    public function __construct() {
        parent::__construct();
        $this->pageTitle = 'Welcome to Nyalife Hospital Management System';
    }
    
    /**
     * Display the landing page
     * 
     * @return void
     */
    public function index() {
        // Process any session messages
        $message = '';
        $messageType = '';

        if (SessionManager::has('auth_message')) {
            $message = SessionManager::get('auth_message');
            $messageType = SessionManager::get('auth_message_type', 'info');
            
            // Clear the message from session
            SessionManager::remove('auth_message');
            SessionManager::remove('auth_message_type');
        }
        
        // Render the landing page view
        $this->renderView('home/index', [
            'message' => $message,
            'messageType' => $messageType
        ], 'landing'); // Use a special 'landing' layout
    }
}
