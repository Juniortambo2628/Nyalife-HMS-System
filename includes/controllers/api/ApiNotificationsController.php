<?php

/**
 * Nyalife HMS - Notifications API Controller
 *
 * API Controller for handling notification-related requests.
 */

require_once __DIR__ . '/../web/WebController.php';
require_once __DIR__ . '/../../services/NotificationService.php';
require_once __DIR__ . '/../../core/SessionManager.php';

class ApiNotificationsController extends WebController
{
    private readonly \NotificationService $notificationService;

    public function __construct()
    {
        parent::__construct();
        $this->notificationService = new NotificationService();
    }

    /**
     * Get user notifications
     *
     * GET /api/notifications
     */
    public function index(): void
    {
        $this->requireAuth();

        $userId = SessionManager::get('user_id');
        $limit = $_GET['limit'] ?? 10;
        $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';

        try {
            $notifications = $this->notificationService->getUserNotifications($userId, (int)$limit, $unreadOnly);

            $this->jsonResponse([
                'success' => true,
                'data' => $notifications,
                'count' => count($notifications)
            ]);
        } catch (Exception $e) {
            error_log("Notifications API Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread notification count
     *
     * GET /api/notifications/count
     */
    public function count(): void
    {
        $this->requireAuth();

        $userId = SessionManager::get('user_id');

        try {
            $count = $this->notificationService->getUnreadCount($userId);

            $this->jsonResponse([
                'success' => true,
                'count' => $count
            ]);
        } catch (Exception $e) {
            error_log("Notifications Count API Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get notification count: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark notification as read
     *
     * PUT /api/notifications/{id}/read
     */
    /**
     * Mark notification as read
     */
    public function markAsRead(int $id): void
    {
        $this->requireAuth();

        $userId = SessionManager::get('user_id');

        try {
            $result = $this->notificationService->markAsRead($id, $userId);

            if ($result) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Notification marked as read'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to mark notification as read'
                ], 400);
            }
        } catch (Exception) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update notification'
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     *
     * PUT /api/notifications/mark-all-read
     */
    public function markAllAsRead(): void
    {
        $this->requireAuth();

        $userId = SessionManager::get('user_id');

        try {
            $result = $this->notificationService->markAllAsRead($userId);

            if ($result) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'All notifications marked as read'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to mark notifications as read'
                ], 400);
            }
        } catch (Exception) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update notifications'
            ], 500);
        }
    }

    /**
     * Require authentication for API endpoints
     */
    private function requireAuth(): void
    {
        if (!SessionManager::isLoggedIn()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
            exit;
        }
    }
}
