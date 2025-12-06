<?php
/**
 * Messages Index View
 * Display inbox, sent, or archived messages
 */

$stats = $stats ?? [];
$messages = $messages ?? [];
$currentType = $currentType ?? 'inbox';
$currentPage = $currentPage ?? 1;

// Define type configurations
$typeConfig = [
    'inbox' => [
        'title' => 'Inbox',
        'icon' => 'fa-inbox',
        'description' => 'Messages received'
    ],
    'sent' => [
        'title' => 'Sent',
        'icon' => 'fa-paper-plane',
        'description' => 'Messages you sent'
    ],
    'archived' => [
        'title' => 'Archived',
        'icon' => 'fa-archive',
        'description' => 'Archived messages'
    ]
];

$config = $typeConfig[$currentType] ?? $typeConfig['inbox'];
?>

<div class="messages-container">
    <!-- Header -->
    <div class="messages-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="page-title">
                    <i class="fas <?= $config['icon'] ?>"></i>
                    <?= $config['title'] ?>
                </h2>
                <p class="page-subtitle"><?= $config['description'] ?></p>
            </div>
            <div class="col-md-6 text-end">
                <a href="<?= $baseUrl ?>/messages/compose" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Compose Message
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-3">Message Types</h6>
                    
                    <!-- Navigation -->
                    <div class="list-group list-group-flush">
                        <a href="<?= $baseUrl ?>/messages?type=inbox" 
                           class="list-group-item list-group-item-action <?= $currentType === 'inbox' ? 'active' : '' ?>">
                            <i class="fas fa-inbox me-2"></i>
                            Inbox
                            <?php if (isset($stats['unread']) && $stats['unread'] > 0): ?>
                                <span class="badge bg-primary float-end"><?= $stats['unread'] ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <a href="<?= $baseUrl ?>/messages?type=sent" 
                           class="list-group-item list-group-item-action <?= $currentType === 'sent' ? 'active' : '' ?>">
                            <i class="fas fa-paper-plane me-2"></i>
                            Sent
                            <?php if (isset($stats['sent'])): ?>
                                <span class="badge bg-secondary float-end"><?= $stats['sent'] ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <a href="<?= $baseUrl ?>/messages?type=archived" 
                           class="list-group-item list-group-item-action <?= $currentType === 'archived' ? 'active' : '' ?>">
                            <i class="fas fa-archive me-2"></i>
                            Archived
                            <?php if (isset($stats['archived'])): ?>
                                <span class="badge bg-secondary float-end"><?= $stats['archived'] ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    
                    <!-- Search -->
                    <div class="mt-4">
                        <h6 class="card-title mb-2">Search Messages</h6>
                        <form action="<?= $baseUrl ?>/messages/search" method="GET" class="d-flex">
                            <input type="hidden" name="type" value="<?= htmlspecialchars($currentType) ?>">
                            <input type="text" name="q" class="form-control form-control-sm me-2" 
                                   placeholder="Search..." required>
                            <button type="submit" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages List -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    
                    <?php if (empty($messages)): ?>
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <i class="fas <?= $config['icon'] ?> fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No messages found</h5>
                            <p class="text-muted">
                                <?php if ($currentType === 'inbox'): ?>
                                    You don't have any messages in your inbox.
                                <?php elseif ($currentType === 'sent'): ?>
                                    You haven't sent any messages yet.
                                <?php else: ?>
                                    You don't have any archived messages.
                                <?php endif; ?>
                            </p>
                            <?php if ($currentType === 'inbox'): ?>
                                <a href="<?= $baseUrl ?>/messages/compose" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Compose Your First Message
                                </a>
                            <?php endif; ?>
                        </div>
                        
                    <?php else: ?>
                        <!-- Messages List -->
                        <div class="messages-list">
                            <?php foreach ($messages as $message): ?>
                                <div class="message-item <?= $message['is_read'] ? 'read' : 'unread' ?>" 
                                     data-message-id="<?= $message['message_id'] ?>">
                                    <div class="row align-items-center">
                                        <!-- Priority Indicator -->
                                        <div class="col-auto">
                                            <?php
                                            $priorityClass = [
                                                'high' => 'text-danger',
                                                'normal' => 'text-secondary',
                                                'low' => 'text-muted'
                                            ];
                                $priorityIcon = [
                                    'high' => 'fa-exclamation-circle',
                                    'normal' => 'fa-circle',
                                    'low' => 'fa-minus-circle'
                                ];
                                ?>
                                            <i class="fas <?= $priorityIcon[$message['priority']] ?? $priorityIcon['normal'] ?> 
                                                      <?= $priorityClass[$message['priority']] ?? $priorityClass['normal'] ?>"></i>
                                        </div>
                                        
                                        <!-- Message Content -->
                                        <div class="col">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="message-meta">
                                                    <?php if ($currentType === 'sent'): ?>
                                                        <span class="sender-name">
                                                            To: <?= htmlspecialchars($message['recipient_first_name'] . ' ' . $message['recipient_last_name']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="sender-name">
                                                            From: <?= htmlspecialchars($message['sender_first_name'] . ' ' . $message['sender_last_name']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <span class="message-date">
                                                        <?= date('M j, Y g:i A', strtotime($message['created_at'])) ?>
                                                    </span>
                                                </div>
                                                
                                                <!-- Actions -->
                                                <div class="message-actions">
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                                type="button" data-bs-toggle="dropdown">
                                                            Actions
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a class="dropdown-item" href="<?= $baseUrl ?>/messages/<?= $message['message_id'] ?>">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                            </li>
                                                            <?php if ($currentType !== 'archived'): ?>
                                                                <li>
                                                                    <a class="dropdown-item archive-message" 
                                                                       href="#" data-message-id="<?= $message['message_id'] ?>">
                                                                        <i class="fas fa-archive"></i> Archive
                                                                    </a>
                                                                </li>
                                                            <?php endif; ?>
                                                            <li>
                                                                <a class="dropdown-item delete-message text-danger" 
                                                                   href="#" data-message-id="<?= $message['message_id'] ?>">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <a href="<?= $baseUrl ?>/messages/<?= $message['message_id'] ?>" 
                                               class="text-decoration-none">
                                                <div class="message-subject">
                                                    <?= htmlspecialchars($message['subject']) ?>
                                                </div>
                                                <div class="message-preview">
                                                    <?= htmlspecialchars(substr(strip_tags($message['message']), 0, 150)) ?>
                                                    <?php if (strlen($message['message']) > 150): ?>...<?php endif; ?>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Pagination (if needed) -->
                        <?php if (count($messages) === 20): ?>
                            <div class="d-flex justify-content-center mt-4">
                                <nav>
                                    <ul class="pagination">
                                        <?php if ($currentPage > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" 
                                                   href="<?= $baseUrl ?>/messages?type=<?= $currentType ?>&page=<?= $currentPage - 1 ?>">
                                                    Previous
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        <li class="page-item active">
                                            <span class="page-link"><?= $currentPage ?></span>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" 
                                               href="<?= $baseUrl ?>/messages?type=<?= $currentType ?>&page=<?= $currentPage + 1 ?>">
                                                Next
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS Styles -->
<style>
.messages-container {
    padding: 20px 0;
}

.messages-header {
    margin-bottom: 30px;
}

.page-title {
    margin-bottom: 5px;
    color: var(--primary-color, #20c997);
}

.page-subtitle {
    color: #6c757d;
    margin-bottom: 0;
}

.message-item {
    border-bottom: 1px solid #dee2e6;
    padding: 15px;
    transition: background-color 0.2s;
}

.message-item:hover {
    background-color: #f8f9fa;
}

.message-item.unread {
    background-color: #fff3cd;
    font-weight: 600;
}

.message-item:last-child {
    border-bottom: none;
}

.message-meta {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.sender-name {
    font-weight: 500;
    color: #495057;
}

.message-date {
    font-size: 0.875rem;
    color: #6c757d;
}

.message-subject {
    font-weight: 600;
    color: #212529;
    margin: 8px 0 5px 0;
}

.message-preview {
    color: #6c757d;
    font-size: 0.9rem;
    line-height: 1.4;
}

.message-actions {
    opacity: 0.7;
    transition: opacity 0.2s;
}

.message-item:hover .message-actions {
    opacity: 1;
}

.list-group-item.active {
    background-color: var(--primary-color, #20c997);
    border-color: var(--primary-color, #20c997);
}

.badge {
    font-size: 0.75rem;
}
</style>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Archive message
    document.querySelectorAll('.archive-message').forEach(function(link) {
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
                        location.reload();
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
    document.querySelectorAll('.delete-message').forEach(function(link) {
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
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the message');
                });
            }
        });
    });
});
</script>
