<?php

/**
 * Nyalife HMS - Error Controller
 *
 * Controller for handling error pages.
 */

require_once __DIR__ . '/WebController.php';

class ErrorController extends WebController
{
    // Override to allow public access without login
    /** @var bool */
    protected $requiresLogin = false;

    /** @var string */
    protected $layout = 'layouts/main';

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show unauthorized page
     */
    public function unauthorized(): void
    {
        http_response_code(403);
        $this->pageTitle = 'Unauthorized - Nyalife HMS';
        $this->renderView('error/unauthorized');
    }
}
