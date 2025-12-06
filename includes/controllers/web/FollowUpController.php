<?php

/**
 * Nyalife HMS - Follow-up Controller
 *
 * Controller for managing follow-up appointments.
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/FollowUpModel.php';
require_once __DIR__ . '/../../models/ConsultationModel.php';
require_once __DIR__ . '/../../models/PatientModel.php';
require_once __DIR__ . '/../../core/SessionManager.php';

class FollowUpController extends WebController
{
    private readonly \FollowUpModel $followUpModel;

    private readonly \ConsultationModel $consultationModel;

    private readonly \PatientModel $patientModel;

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->followUpModel = new FollowUpModel();
        $this->consultationModel = new ConsultationModel();
        $this->patientModel = new PatientModel();
        $this->pageTitle = 'Follow-ups - Nyalife HMS';
    }

    /**
     * Display follow-ups list
     */
    public function index(): void
    {
        // Check if user has permission to view follow-ups
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $filters = [
            'status' => $_GET['status'] ?? null,
            'type' => $_GET['type'] ?? null,
            'doctor_id' => $_GET['doctor_id'] ?? null,
            'patient_id' => $_GET['patient_id'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null
        ];

        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 20;

        $followUps = $this->followUpModel->getFollowUpsFiltered($filters, $page, $perPage);
        $totalFollowUps = $this->followUpModel->countFollowUpsFiltered($filters);
        $totalPages = ceil($totalFollowUps / $perPage);

        $statistics = $this->followUpModel->getFollowUpStatistics('month');

        $this->renderView('follow-ups/index', [
            'followUps' => $followUps,
            'filters' => $filters,
            'statistics' => $statistics,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'perPage' => $perPage,
                'totalItems' => $totalFollowUps
            ],
            'pageTitle' => 'Follow-ups'
        ]);
    }

    /**
     * Display follow-up details
     */
    public function show($id): void
    {
        // Check if user has permission to view follow-ups
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $followUp = $this->followUpModel->getFollowUpWithDetails($id);

        if (!$followUp) {
            $this->setFlashMessage('error', 'Follow-up not found.');
            $this->redirect('follow-ups');
            return;
        }

        $this->renderView('follow-ups/show', [
            'followUp' => $followUp,
            'pageTitle' => 'Follow-up Details'
        ]);
    }

    /**
     * Display create follow-up form
     */
    public function create(): void
    {
        // Check if user has permission to create follow-ups
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $consultationId = $_GET['consultation_id'] ?? null;
        $patientId = $_GET['patient_id'] ?? null;

        $consultation = null;
        $patient = null;

        if ($consultationId) {
            $consultation = $this->consultationModel->getConsultationById($consultationId);
        }

        if ($patientId) {
            $patient = $this->patientModel->getById($patientId);
        }

        $followUpTypes = [
            'general' => 'General Follow-up',
            'post_surgery' => 'Post-Surgery Follow-up',
            'medication_review' => 'Medication Review',
            'lab_results' => 'Lab Results Review',
            'treatment_progress' => 'Treatment Progress',
            'specialist_referral' => 'Specialist Referral',
            'emergency' => 'Emergency Follow-up'
        ];

        $this->renderView('follow-ups/create', [
            'consultation' => $consultation,
            'patient' => $patient,
            'followUpTypes' => $followUpTypes,
            'pageTitle' => 'Create Follow-up'
        ]);
    }

    /**
     * Store new follow-up
     */
    public function store(): void
    {
        // Check if user has permission to create follow-ups
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== SessionManager::get('csrf_token')) {
            $this->setFlashMessage('error', 'Invalid request.');
            $this->redirect('follow-ups/create');
            return;
        }

        $data = [
            'patient_id' => $_POST['patient_id'] ?? null,
            'consultation_id' => $_POST['consultation_id'] ?? null,
            'follow_up_date' => $_POST['follow_up_date'] ?? date('Y-m-d'),
            'follow_up_type' => $_POST['follow_up_type'] ?? 'general',
            'reason' => $_POST['reason'] ?? '',
            'status' => $_POST['status'] ?? 'scheduled',
            'notes' => $_POST['notes'] ?? '',
            'created_by' => SessionManager::get('user_id')
        ];

        // Validate required fields
        if (empty($data['patient_id'])) {
            $this->setFlashMessage('error', 'Patient is required.');
            $this->redirect('follow-ups/create');
            return;
        }

        if (empty($data['consultation_id'])) {
            $this->setFlashMessage('error', 'Consultation is required.');
            $this->redirect('follow-ups/create');
            return;
        }

        if (empty($data['reason'])) {
            $this->setFlashMessage('error', 'Reason is required.');
            $this->redirect('follow-ups/create');
            return;
        }

        $followUpId = $this->followUpModel->createFollowUp($data);

        if ($followUpId) {
            $this->setFlashMessage('success', 'Follow-up created successfully.');
            $this->redirect("follow-ups/show/{$followUpId}");
        } else {
            $this->setFlashMessage('error', 'Failed to create follow-up.');
            $this->redirect('follow-ups/create');
        }
    }

    /**
     * Display edit follow-up form
     */
    public function edit($id): void
    {
        // Check if user has permission to edit follow-ups
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $followUp = $this->followUpModel->getFollowUpWithDetails($id);

        if (!$followUp) {
            $this->setFlashMessage('error', 'Follow-up not found.');
            $this->redirect('follow-ups');
            return;
        }

        $followUpTypes = [
            'general' => 'General Follow-up',
            'post_surgery' => 'Post-Surgery Follow-up',
            'medication_review' => 'Medication Review',
            'lab_results' => 'Lab Results Review',
            'treatment_progress' => 'Treatment Progress',
            'specialist_referral' => 'Specialist Referral',
            'emergency' => 'Emergency Follow-up'
        ];

        $this->renderView('follow-ups/edit', [
            'followUp' => $followUp,
            'followUpTypes' => $followUpTypes,
            'pageTitle' => 'Edit Follow-up'
        ]);
    }

    /**
     * Update follow-up
     */
    public function update($id): void
    {
        // Check if user has permission to edit follow-ups
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== SessionManager::get('csrf_token')) {
            $this->setFlashMessage('error', 'Invalid request.');
            $this->redirect("follow-ups/edit/{$id}");
            return;
        }

        $data = [
            'follow_up_date' => $_POST['follow_up_date'] ?? date('Y-m-d'),
            'follow_up_type' => $_POST['follow_up_type'] ?? 'general',
            'reason' => $_POST['reason'] ?? '',
            'status' => $_POST['status'] ?? 'scheduled',
            'notes' => $_POST['notes'] ?? ''
        ];

        // Validate required fields
        if (empty($data['reason'])) {
            $this->setFlashMessage('error', 'Reason is required.');
            $this->redirect("follow-ups/edit/{$id}");
            return;
        }

        $success = $this->followUpModel->updateFollowUp($id, $data);

        if ($success) {
            $this->setFlashMessage('success', 'Follow-up updated successfully.');
            $this->redirect("follow-ups/show/{$id}");
        } else {
            $this->setFlashMessage('error', 'Failed to update follow-up.');
            $this->redirect("follow-ups/edit/{$id}");
        }
    }

    /**
     * Update follow-up status
     */
    public function updateStatus($id): void
    {
        // Check if user has permission to update follow-ups
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== SessionManager::get('csrf_token')) {
            $this->setFlashMessage('error', 'Invalid request.');
            $this->redirect('follow-ups');
            return;
        }

        $status = $_POST['status'] ?? '';

        if (empty($status)) {
            $this->setFlashMessage('error', 'Status is required.');
            $this->redirect('follow-ups');
            return;
        }

        $success = $this->followUpModel->updateStatus($id, $status);

        if ($success) {
            $this->setFlashMessage('success', 'Follow-up status updated successfully.');
        } else {
            $this->setFlashMessage('error', 'Failed to update follow-up status.');
        }

        $this->redirect('follow-ups');
    }

    /**
     * Delete follow-up
     */
    public function delete($id): void
    {
        // Check if user has permission to delete follow-ups
        if (!$this->auth->hasRole('admin')) {
            $this->redirect('error/unauthorized');
            return;
        }

        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== SessionManager::get('csrf_token')) {
            $this->setFlashMessage('error', 'Invalid request.');
            $this->redirect('follow-ups');
            return;
        }

        $success = $this->followUpModel->delete($id);

        if ($success) {
            $this->setFlashMessage('success', 'Follow-up deleted successfully.');
        } else {
            $this->setFlashMessage('error', 'Failed to delete follow-up.');
        }

        $this->redirect('follow-ups');
    }

    /**
     * Get upcoming follow-ups
     */
    public function upcoming(): void
    {
        // Check if user has permission to view follow-ups
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $days = intval($_GET['days'] ?? 7);
        $doctorId = $_GET['doctor_id'] ?? null;

        $followUps = $this->followUpModel->getUpcomingFollowUps($days, $doctorId);

        $this->renderView('follow-ups/upcoming', [
            'followUps' => $followUps,
            'days' => $days,
            'doctorId' => $doctorId,
            'pageTitle' => 'Upcoming Follow-ups'
        ]);
    }

    /**
     * Get follow-ups for AJAX requests
     */
    public function getFollowUps(): void
    {
        // Check if user has permission to view follow-ups
        if (!$this->auth->hasRole('admin') && !$this->auth->hasRole('doctor')) {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
            return;
        }

        $filters = [
            'status' => $_GET['status'] ?? null,
            'type' => $_GET['type'] ?? null,
            'doctor_id' => $_GET['doctor_id'] ?? null,
            'patient_id' => $_GET['patient_id'] ?? null
        ];

        $followUps = $this->followUpModel->getFollowUpsFiltered($filters, 1, 50);

        $this->jsonResponse([
            'success' => true,
            'followUps' => $followUps
        ]);
    }

    /**
     * Get follow-up statistics
     */
    public function statistics(): void
    {
        // Check if user has permission to view statistics
        if (!$this->auth->hasRole('admin')) {
            $this->redirect('error/unauthorized');
            return;
        }

        $period = $_GET['period'] ?? 'month';
        $statistics = $this->followUpModel->getFollowUpStatistics($period);

        $this->renderView('follow-ups/statistics', [
            'statistics' => $statistics,
            'period' => $period,
            'pageTitle' => 'Follow-up Statistics'
        ]);
    }
}
