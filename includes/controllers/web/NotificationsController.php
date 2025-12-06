<?php

/**
 * Nyalife HMS - Notifications Controller (Web)
 *
 * Web controller for managing and displaying notifications.
 */

require_once __DIR__ . '/WebController.php';
require_once __DIR__ . '/../../models/NotificationModel.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../services/NotificationService.php';
require_once __DIR__ . '/../../core/SessionManager.php';

class NotificationsController extends WebController
{
    protected \NotificationModel $notificationModel;
    
    protected \UserModel $userModel;
    
    protected \NotificationService $notificationService;

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
        $this->notificationModel = new NotificationModel();
        $this->userModel = new UserModel();
        $this->notificationService = new NotificationService();
        $this->pageTitle = 'Notifications - Nyalife HMS';
    }

    /**
     * Display all notifications for the current user
     */
    public function index(): void
    {
        try {
            // Get current user ID
            $userId = SessionManager::get('user_id');
            if (!$userId) {
                $this->redirect('/login?redirect=' . urlencode('/notifications'));
                return;
            }

            // Get current user details
            $currentUser = $this->userModel->find($userId);
            if (!$currentUser) {
                $this->redirect('/login');
                return;
            }

            $page = intval($_GET['page'] ?? 1);
            $perPage = 20; // Show 20 notifications per page
            $offset = ($page - 1) * $perPage;

            // Get notifications for current user with pagination
            $notifications = $this->notificationModel->getUserNotifications($userId, $perPage, $offset);

            // Get total count for pagination
            $totalCount = $this->notificationModel->getUserNotificationCount($userId);
            $totalPages = ceil($totalCount / $perPage);

            $pageData = [
                'notifications' => $notifications,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalCount' => $totalCount,
                'perPage' => $perPage,
                'hasNextPage' => $page < $totalPages,
                'hasPrevPage' => $page > 1,
                'user' => $currentUser
            ];

            $this->renderView('notifications/index', $pageData);
        } catch (Exception $e) {
            error_log("NotificationsController::index() - Error: " . $e->getMessage());
            $this->setFlashMessage('error', 'An error occurred while loading notifications.');
            $this->renderView('notifications/index', [
                'notifications' => [],
                'currentPage' => 1,
                'totalPages' => 0,
                'totalCount' => 0,
                'perPage' => 20,
                'hasNextPage' => false,
                'hasPrevPage' => false,
                'user' => null
            ]);
        }
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(): void
    {
        try {
            $notificationId = $_POST['notification_id'] ?? null;
            $userId = SessionManager::get('user_id');

            if (!$userId || !$notificationId) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid request']);
                return;
            }

            $success = $this->notificationModel->markAsRead($notificationId, $userId);

            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Notification marked as read']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to mark notification as read']);
            }
        } catch (Exception $e) {
            error_log("NotificationsController::markAsRead() - Error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred']);
        }
    }

    /**
     * Mark all notifications as read for current user
     */
    public function markAllAsRead(): void
    {
        try {
            $userId = SessionManager::get('user_id');

            if (!$userId) {
                $this->jsonResponse(['success' => false, 'message' => 'Unauthorized']);
                return;
            }

            $success = $this->notificationModel->markAllAsRead($userId);

            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'All notifications marked as read']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to mark notifications as read']);
            }
        } catch (Exception $e) {
            error_log("NotificationsController::markAllAsRead() - Error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred']);
        }
    }
}
