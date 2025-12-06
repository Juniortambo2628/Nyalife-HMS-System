<?php

/**
 * Nyalife HMS - Follow-up API Controller
 *
 * API controller for managing follow-up appointments.
 */

require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/../../models/FollowUpModel.php';
require_once __DIR__ . '/../../models/ConsultationModel.php';

class FollowUpController extends ApiController
{
    private readonly \FollowUpModel $followUpModel;

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->followUpModel = new FollowUpModel();
    }

    /**
     * Get all follow-ups with filters
     */
    public function index(): void
    {
        try {
            $this->requireAuth();

            $filters = [
                'status' => $_GET['status'] ?? null,
                'type' => $_GET['type'] ?? null,
                'doctor_id' => $_GET['doctor_id'] ?? null,
                'patient_id' => $_GET['patient_id'] ?? null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null
            ];

            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = intval($_GET['per_page'] ?? 20);

            $followUps = $this->followUpModel->getFollowUpsFiltered($filters, $page, $perPage);
            $totalFollowUps = $this->followUpModel->countFollowUpsFiltered($filters);

            $this->sendResponse([
                'success' => true,
                'data' => $followUps,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $totalFollowUps,
                    'total_pages' => ceil($totalFollowUps / $perPage)
                ]
            ]);
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get follow-up by ID
     */
    /**
     * Get follow-up by ID
     */
    public function show(int $id): void
    {
        try {
            $this->requireAuth();

            $followUp = $this->followUpModel->getFollowUpWithDetails($id);

            if (!$followUp) {
                $this->sendErrorResponse('Follow-up not found', 404);
                return;
            }

            $this->sendResponse([
                'success' => true,
                'data' => $followUp
            ]);
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Create new follow-up
     */
    public function store(): void
    {
        try {
            $this->requireAuth();
            $this->validateRequest(['POST']);

            $data = [
                'patient_id' => $_POST['patient_id'] ?? null,
                'consultation_id' => $_POST['consultation_id'] ?? null,
                'follow_up_date' => $_POST['follow_up_date'] ?? date('Y-m-d'),
                'follow_up_type' => $_POST['follow_up_type'] ?? 'general',
                'reason' => $_POST['reason'] ?? '',
                'status' => $_POST['status'] ?? 'scheduled',
                'notes' => $_POST['notes'] ?? '',
                'created_by' => $this->getCurrentUserId()
            ];

            // Validate required fields
            if (empty($data['patient_id'])) {
                $this->sendErrorResponse('Patient ID is required', 400);
                return;
            }

            if (empty($data['consultation_id'])) {
                $this->sendErrorResponse('Consultation ID is required', 400);
                return;
            }

            if (empty($data['reason'])) {
                $this->sendErrorResponse('Reason is required', 400);
                return;
            }

            $followUpId = $this->followUpModel->createFollowUp($data);

            if ($followUpId) {
                $followUp = $this->followUpModel->getFollowUpWithDetails($followUpId);
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Follow-up created successfully',
                    'data' => $followUp
                ], 201);
            } else {
                $this->sendErrorResponse('Failed to create follow-up', 500);
            }
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update follow-up
     */
    /**
     * Update follow-up
     */
    public function update(int $id): void
    {
        try {
            $this->requireAuth();
            $this->validateRequest(['PUT', 'POST']);

            $followUp = $this->followUpModel->find($id);

            if (!$followUp) {
                $this->sendErrorResponse('Follow-up not found', 404);
                return;
            }

            $data = [
                'follow_up_date' => $_POST['follow_up_date'] ?? $followUp['follow_up_date'],
                'follow_up_type' => $_POST['follow_up_type'] ?? $followUp['follow_up_type'],
                'reason' => $_POST['reason'] ?? $followUp['reason'],
                'status' => $_POST['status'] ?? $followUp['status'],
                'notes' => $_POST['notes'] ?? $followUp['notes']
            ];

            // Validate required fields
            if (isset($_POST['reason']) && empty($_POST['reason'])) {
                $this->sendErrorResponse('Reason is required', 400);
                return;
            }

            $success = $this->followUpModel->updateFollowUp($id, $data);

            if ($success) {
                $updatedFollowUp = $this->followUpModel->getFollowUpWithDetails($id);
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Follow-up updated successfully',
                    'data' => $updatedFollowUp
                ]);
            } else {
                $this->sendErrorResponse('Failed to update follow-up', 500);
            }
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Delete follow-up
     */
    /**
     * Delete follow-up
     */
    public function delete(int $id): void
    {
        try {
            $this->requireAuth();
            $this->validateRequest(['DELETE', 'POST']);

            $followUp = $this->followUpModel->find($id);

            if (!$followUp) {
                $this->sendErrorResponse('Follow-up not found', 404);
                return;
            }

            $success = $this->followUpModel->delete($id);

            if ($success) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Follow-up deleted successfully'
                ]);
            } else {
                $this->sendErrorResponse('Failed to delete follow-up', 500);
            }
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get follow-ups by patient
     */
    /**
     * Get follow-ups by patient
     */
    public function getByPatient(int $patientId): void
    {
        try {
            $this->requireAuth();

            $status = $_GET['status'] ?? null;
            $followUps = $this->followUpModel->getFollowUpsByPatient($patientId, $status);

            $this->sendResponse([
                'success' => true,
                'data' => $followUps
            ]);
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get follow-ups by doctor
     */
    /**
     * Get follow-ups by doctor
     */
    public function getByDoctor(int $doctorId): void
    {
        try {
            $this->requireAuth();

            $status = $_GET['status'] ?? null;
            $followUps = $this->followUpModel->getFollowUpsByDoctor($doctorId, $status);

            $this->sendResponse([
                'success' => true,
                'data' => $followUps
            ]);
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get follow-ups by consultation
     */
    /**
     * Get follow-ups by consultation
     */
    public function getByConsultation(int $consultationId): void
    {
        try {
            $this->requireAuth();

            $followUps = $this->followUpModel->getFollowUpsByConsultation($consultationId);

            $this->sendResponse([
                'success' => true,
                'data' => $followUps
            ]);
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get upcoming follow-ups
     */
    public function upcoming(): void
    {
        try {
            $this->requireAuth();

            $days = intval($_GET['days'] ?? 7);
            $doctorId = $_GET['doctor_id'] ?? null;

            $followUps = $this->followUpModel->getUpcomingFollowUps($days, $doctorId);

            $this->sendResponse([
                'success' => true,
                'data' => $followUps
            ]);
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get follow-up statistics
     */
    public function statistics(): void
    {
        try {
            $this->requireAuth();

            $period = $_GET['period'] ?? 'month';
            $statistics = $this->followUpModel->getFollowUpStatistics($period);

            $this->sendResponse([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update follow-up status
     */
    /**
     * Update follow-up status
     */
    public function updateStatus(int $id): void
    {
        try {
            $this->requireAuth();
            $this->validateRequest(['PUT', 'POST']);

            $status = $_POST['status'] ?? null;

            if (!$status) {
                $this->sendErrorResponse('Status is required', 400);
                return;
            }

            $success = $this->followUpModel->updateStatus($id, $status);

            if ($success) {
                $followUp = $this->followUpModel->getFollowUpWithDetails($id);
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Follow-up status updated successfully',
                    'data' => $followUp
                ]);
            } else {
                $this->sendErrorResponse('Failed to update follow-up status', 500);
            }
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }
}
