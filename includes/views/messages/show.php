<?php
/**
 * Show Message View
 * Display individual message content
 */

$message = $message ?? null;

if (!$message) {
    echo '<div class="alert alert-danger">Message not found.</div>';
    return;
}

$currentUserId = $_SESSION['user_id'] ?? 0;
$isRecipient = $message['recipient_id'] == $currentUserId;
$isSender = $message['sender_id'] == $currentUserId;
?>

<div class="message-view-container">
    <!-- Header -->
    <div class="message-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <a href="<?= $baseUrl ?>/messages" class="btn btn-outline-secondary me-3">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <div>
                        <h2 class="page-title mb-1">
                            <?= htmlspecialchars($message['subject']) ?>
                        </h2>
                        <div class="message-meta">
                            <span class="badge badge-priority priority-<?= $message['priority'] ?>">
                                <?= ucfirst($message['priority']) ?> Priority
                            </span>
                            <?php if (!$message['is_read'] && $isRecipient): ?>
                                <span class="badge bg-primary ms-2">New</span>
                            <?php endif; ?>
                            <?php if ($message['is_archived']): ?>
                                <span class="badge bg-secondary ms-2">Archived</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                            id="messageActions" data-bs-toggle="dropdown">
                        Actions
                    </button>
                    <ul class="dropdown-menu">
                        <?php if ($isRecipient): ?>
                            <li>
                                <a class="dropdown-item" href="<?= $baseUrl ?>/messages/compose?reply=<?= $message['message_id'] ?>">
                                    <i class="fas fa-reply"></i> Reply
                                </a>
                            </li>
                            <?php if (!$message['is_read']): ?>
                                <li>
                                    <a class="dropdown-item mark-read" href="#" data-message-id="<?= $message['message_id'] ?>">
                                        <i class="fas fa-check"></i> Mark as Read
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (!$message['is_archived']): ?>
                                <li>
                                    <a class="dropdown-item archive-message" href="#" data-message-id="<?= $message['message_id'] ?>">
                                        <i class="fas fa-archive"></i> Archive
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        <li>
                            <a class="dropdown-item delete-message text-danger" href="#" data-message-id="<?= $message['message_id'] ?>">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="window.print()">
                                <i class="fas fa-print"></i> Print
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Content -->
    <div class="row">
        <div class="col-md-12">
            <div class="card message-card">
                <!-- Message Header Info -->
                <div class="card-header bg-light">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="message-participant">
                                <strong>From:</strong>
                                <div class="participant-info">
                                    <?= htmlspecialchars($message['sender_first_name'] . ' ' . $message['sender_last_name']) ?>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($message['sender_email']) ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="message-participant">
                                <strong>To:</strong>
                                <div class="participant-info">
                                    <?= htmlspecialchars($message['recipient_first_name'] . ' ' . $message['recipient_last_name']) ?>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($message['recipient_email']) ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="message-timestamp">
                                <strong>Sent:</strong>
                                <?= date('F j, Y \a\t g:i A', strtotime($message['created_at'])) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="message-status">
                                <strong>Status:</strong>
                                <?php if ($message['is_read']): ?>
                                    <span class="text-success">
                                        <i class="fas fa-check-circle"></i> Read
                                    </span>
                                <?php else: ?>
                                    <span class="text-warning">
                                        <i class="fas fa-clock"></i> Unread
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message Body -->
                <div class="card-body">
                    <div class="message-content">
                        <?= nl2br(htmlspecialchars($message['message'])) ?>
                    </div>
                </div>

                <!-- Message Footer -->
                <div class="card-footer bg-light">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                Message ID: #<?= $message['message_id'] ?>
                            </small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <?php if ($message['updated_at'] !== $message['created_at']): ?>
                                    Last updated: <?= date('M j, Y g:i A', strtotime($message['updated_at'])) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <?php if ($isRecipient): ?>
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <h6 class="card-title">Quick Actions</h6>
                        <div class="btn-group" role="group">
                            <a href="<?= $baseUrl ?>/messages/compose?reply=<?= $message['message_id'] ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-reply"></i> Reply
                            </a>
                            <a href="<?= $baseUrl ?>/messages/compose?forward=<?= $message['message_id'] ?>" 
                               class="btn btn-outline-secondary">
                                <i class="fas fa-share"></i> Forward
                            </a>
                            <?php if (!$message['is_archived']): ?>
                                <button class="btn btn-outline-warning archive-message" 
                                        data-message-id="<?= $message['message_id'] ?>">
                                    <i class="fas fa-archive"></i> Archive
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- CSS Styles -->
<style>
.message-view-container {
    padding: 20px 0;
}

.message-header {
    margin-bottom: 30px;
}

.page-title {
    margin-bottom: 0;
    color: var(--primary-color, #20c997);
    font-size: 1.5rem;
}

.message-meta {
    margin-top: 8px;
}

.badge-priority {
    font-size: 0.75rem;
}

.priority-high {
    background-color: #dc3545;
}

.priority-normal {
    background-color: #6c757d;
}

.priority-low {
    background-color: #28a745;
}

.message-card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.message-participant {
    margin-bottom: 15px;
}

.participant-info {
    margin-top: 5px;
    padding-left: 10px;
}

.message-timestamp,
.message-status {
    margin-bottom: 10px;
}

.message-content {
    font-size: 1rem;
    line-height: 1.6;
    color: #495057;
    min-height: 200px;
    padding: 20px 0;
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.card-footer {
    border-top: 1px solid rgba(0, 0, 0, 0.125);
    background-color: rgba(0, 0, 0, 0.03);
}

.btn-primary {
    background-color: var(--primary-color, #20c997);
    border-color: var(--primary-color, #20c997);
}

.btn-primary:hover {
    background-color: var(--primary-hover, #1ba085);
    border-color: var(--primary-hover, #1ba085);
}

/* Print styles */
@media print {
    .message-header .btn,
    .dropdown,
    .card:last-child {
        display: none !important;
    }
    
    .message-view-container {
        padding: 0;
    }
    
    .page-title {
        color: black !important;
    }
}
</style>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Mark as read
    const markReadLink = document.querySelector('.mark-read');
    if (markReadLink) {
        markReadLink.addEventListener('click', function(e) {
            e.preventDefault();
            const messageId = this.getAttribute('data-message-id');
            
            fetch('<?= $baseUrl ?>/messages/mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'message_id=' + messageId
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Error: ' + data.error);
                } else {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while marking the message as read');
            });
        });
    }
    
    // Archive message
    const archiveLinks = document.querySelectorAll('.archive-message');
    archiveLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const messageId = this.getAttribute('data-message-id');
            
            if (confirm('Are you sure you want to archive this message?')) {
                fetch('<?= $baseUrl ?>/messages/archive', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'message_id=' + messageId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                    } else {
                        // Redirect to messages list after archiving
                        window.location.href = '<?= $baseUrl ?>/messages?type=archived';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while archiving the message');
                });
            }
        });
    });
    
    // Delete message
    const deleteLinks = document.querySelectorAll('.delete-message');
    deleteLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const messageId = this.getAttribute('data-message-id');
            
            if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
                fetch('<?= $baseUrl ?>/messages/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'message_id=' + messageId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                    } else {
                        // Redirect to messages list after deleting
                        window.location.href = '/messages?deleted=1';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the message');
                });
            }
        });
    });
    
    // Auto-mark as read for recipients after 3 seconds
    const currentUserId = <?= json_encode($currentUserId) ?>;
    const isRecipient = <?= json_encode($isRecipient) ?>;
    const isRead = <?= json_encode((bool)$message['is_read']) ?>;
    const messageId = <?= json_encode($message['message_id']) ?>;
    
    if (isRecipient && !isRead) {
        setTimeout(function() {
            fetch('<?= $baseUrl ?>/messages/mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'message_id=' + messageId
            })
            .then(response => response.json())
            .then(data => {
                if (!data.error) {
                    // Update the UI to show as read
                    const newBadge = document.querySelector('.badge.bg-primary');
                    if (newBadge) {
                        newBadge.remove();
                    }
                    
                    const statusElement = document.querySelector('.message-status span');
                    if (statusElement) {
                        statusElement.className = 'text-success';
                        statusElement.innerHTML = '<i class="fas fa-check-circle"></i> Read';
                    }
                }
            })
            .catch(error => {
                console.error('Error auto-marking as read:', error);
            });
        }, 3000);
    }
});
</script>
