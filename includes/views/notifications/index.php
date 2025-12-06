<?php
/**
 * Nyalife HMS - Notifications Index View
 */
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1><i class="fas fa-bell me-2 text-primary"></i>All Notifications</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= $baseUrl ?>/dashboard" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($successMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($errorMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Notifications Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">
                        <span class="badge bg-primary me-2"><?= $totalCount ?></span>
                        Total Notifications
                    </h5>
                </div>
                <div class="col-md-6 text-end">
                    <?php if ($totalCount > 0): ?>
                        <button id="mark-all-read-btn" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-check-double me-1"></i> Mark All as Read
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($notifications)): ?>
                <!-- Empty State -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-bell-slash fa-4x text-primary opacity-50"></i>
                    </div>
                    <h4 class="text-muted">No Notifications</h4>
                    <p class="text-muted mb-4">You don't have any notifications at the moment.</p>
                    <a href="<?= $baseUrl ?>/dashboard" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Back to Dashboard
                    </a>
                </div>
            <?php else: ?>
                <!-- Notifications List -->
                <div class="notifications-list">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item <?= $notification['is_read'] ? 'read' : 'unread' ?>" 
                             data-notification-id="<?= $notification['notification_id'] ?>"
                             data-type="<?= htmlspecialchars($notification['type']) ?>">
                            
                            <div class="d-flex align-items-start p-3">
                                <!-- Notification Icon -->
                                <div class="notification-icon me-3">
                                    <?php
                                    $iconMap = [
                                        'appointment_created' => 'fas fa-calendar-plus',
                                        'appointment_updated' => 'fas fa-calendar-edit',
                                        'appointment_cancelled' => 'fas fa-calendar-times',
                                        'appointment_reminder' => 'fas fa-clock',
                                        'appointment_completed' => 'fas fa-calendar-check'
                                    ];
                        $icon = $iconMap[$notification['type']] ?? 'fas fa-bell';
                        ?>
                                    <i class="<?= $icon ?>"></i>
                                </div>

                                <!-- Notification Content -->
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="notification-title mb-1">
                                            <?= htmlspecialchars($notification['title']) ?>
                                        </h6>
                                        <?php if (!$notification['is_read']): ?>
                                            <span class="notification-dot badge bg-danger rounded-pill">•</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <p class="notification-message mb-2 text-muted">
                                        <?= htmlspecialchars($notification['message']) ?>
                                    </p>
                                    
                                    <!-- Appointment Details (if available) -->
                                    <?php if (!empty($notification['appointment_date']) && !empty($notification['appointment_time'])): ?>
                                        <div class="notification-meta mb-2">
                                            <small class="text-info">
                                                <i class="fas fa-calendar-day me-1"></i>
                                                <?= htmlspecialchars(date('M d, Y', strtotime($notification['appointment_date']))) ?> at 
                                                <?= htmlspecialchars(date('g:i A', strtotime($notification['appointment_time']))) ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="notification-time text-primary">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= $notification['time_ago'] ?>
                                        </small>
                                        
                                        <div class="notification-actions">
                                            <?php if (!$notification['is_read']): ?>
                                                <button class="btn btn-sm btn-outline-primary mark-read-btn" 
                                                        data-notification-id="<?= $notification['notification_id'] ?>">
                                                    <i class="fas fa-check"></i> Mark as Read
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($notification['appointment_id'])): ?>
                                                <a href="<?= $baseUrl ?>/appointments/view/<?= $notification['appointment_id'] ?>" 
                                                   class="btn btn-sm btn-primary ms-2">
                                                    <i class="fas fa-eye"></i> View Appointment
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Notifications pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <!-- Previous Page -->
                            <?php if ($hasPrevPage): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $baseUrl ?>/notifications?page=<?= $currentPage - 1 ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php endif; ?>

                            <!-- Page Numbers -->
                            <?php
                            $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    ?>
                            
                            <?php if ($startPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $baseUrl ?>/notifications?page=1">1</a>
                                </li>
                                <?php if ($startPage > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
                                <li class="page-item <?= $page === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $baseUrl ?>/notifications?page=<?= $page ?>">
                                        <?= $page ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $baseUrl ?>/notifications?page=<?= $totalPages ?>"><?= $totalPages ?></a>
                                </li>
                            <?php endif; ?>

                            <!-- Next Page -->
                            <?php if ($hasNextPage): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $baseUrl ?>/notifications?page=<?= $currentPage + 1 ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript for AJAX functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark individual notification as read
    document.querySelectorAll('.mark-read-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const notificationId = this.dataset.notificationId;
            markNotificationAsRead(notificationId, this);
        });
    });

    // Mark all notifications as read
    const markAllBtn = document.getElementById('mark-all-read-btn');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function() {
            markAllNotificationsAsRead(this);
        });
    }

    function markNotificationAsRead(notificationId, button) {
        fetch(`${window.baseUrl}/api/notifications/${notificationId}/read`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notificationItem = button.closest('.notification-item');
                notificationItem.classList.remove('unread');
                notificationItem.classList.add('read');
                
                const dot = notificationItem.querySelector('.notification-dot');
                if (dot) dot.remove();
                
                button.remove();
                
                // Update badge count in header if it exists
                updateNotificationBadge();
            } else {
                alert('Failed to mark notification as read: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while marking the notification as read.');
        });
    }

    function markAllNotificationsAsRead(button) {
        if (!confirm('Are you sure you want to mark all notifications as read?')) {
            return;
        }

        fetch(`${window.baseUrl}/api/notifications/mark-all-read`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update all notification items
                document.querySelectorAll('.notification-item.unread').forEach(function(item) {
                    item.classList.remove('unread');
                    item.classList.add('read');
                    
                    const dot = item.querySelector('.notification-dot');
                    if (dot) dot.remove();
                    
                    const markReadBtn = item.querySelector('.mark-read-btn');
                    if (markReadBtn) markReadBtn.remove();
                });
                
                button.remove();
                updateNotificationBadge();
                
                // Show success message
                showAlert('success', 'All notifications marked as read successfully!');
            } else {
                alert('Failed to mark all notifications as read: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while marking notifications as read.');
        });
    }

    function updateNotificationBadge() {
        // Update the main notification badge if it exists
        if (window.updateNotificationBadge) {
            window.updateNotificationBadge();
        }
    }

    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle me-2"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        const container = document.querySelector('.container-fluid');
        const firstCard = container.querySelector('.card');
        container.insertBefore(alertDiv, firstCard);
        
        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            if (alertDiv && alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 3000);
    }
});
</script>
