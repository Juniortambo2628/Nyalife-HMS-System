<?php
/**
 * Search Messages View
 * Display search results
 */

$messages = $messages ?? [];
$stats = $stats ?? [];
$query = $query ?? '';
$type = $type ?? 'inbox';
?>

<div class="message-search-container">
    <!-- Header -->
    <div class="search-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="page-title">
                    <i class="fas fa-search"></i>
                    Search Results
                </h2>
                <p class="page-subtitle">
                    <?= count($messages) ?> results found for 
                    <strong>"<?= htmlspecialchars($query) ?>"</strong>
                    in <?= ucfirst($type) ?>
                </p>
            </div>
            <div class="col-md-6 text-end">
                <a href="<?= $baseUrl ?>/messages" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Messages
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-3">Refine Search</h6>
                    
                    <!-- Search Form -->
                    <form action="<?= $baseUrl ?>/messages/search" method="GET" class="mb-4">
                        <div class="mb-3">
                            <label for="q" class="form-label">Search Query</label>
                            <input type="text" class="form-control" id="q" name="q" 
                                   value="<?= htmlspecialchars($query) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="type" class="form-label">Search In</label>
                            <select class="form-control" id="type" name="type">
                                <option value="inbox" <?= $type === 'inbox' ? 'selected' : '' ?>>Inbox</option>
                                <option value="sent" <?= $type === 'sent' ? 'selected' : '' ?>>Sent</option>
                                <option value="archived" <?= $type === 'archived' ? 'selected' : '' ?>>Archived</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                    
                    <!-- Quick Links -->
                    <h6 class="card-title mb-3">Quick Links</h6>
                    <div class="list-group list-group-flush">
                        <a href="<?= $baseUrl ?>/messages?type=inbox" class="list-group-item list-group-item-action">
                            <i class="fas fa-inbox me-2"></i> Inbox
                            <?php if (isset($stats['unread']) && $stats['unread'] > 0): ?>
                                <span class="badge bg-primary float-end"><?= $stats['unread'] ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= $baseUrl ?>/messages?type=sent" class="list-group-item list-group-item-action">
                            <i class="fas fa-paper-plane me-2"></i> Sent
                        </a>
                        <a href="<?= $baseUrl ?>/messages?type=archived" class="list-group-item list-group-item-action">
                            <i class="fas fa-archive me-2"></i> Archived
                        </a>
                        <a href="<?= $baseUrl ?>/messages/compose" class="list-group-item list-group-item-action">
                            <i class="fas fa-plus me-2"></i> Compose
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Results -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    
                    <?php if (empty($messages)): ?>
                        <!-- No Results -->
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No messages found</h5>
                            <p class="text-muted">
                                No messages match your search query 
                                <strong>"<?= htmlspecialchars($query) ?>"</strong> 
                                in <?= ucfirst($type) ?>.
                            </p>
                            <div class="mt-4">
                                <a href="<?= $baseUrl ?>/messages" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-arrow-left"></i> View All Messages
                                </a>
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('q').focus()">
                                    <i class="fas fa-search"></i> Try Another Search
                                </button>
                            </div>
                        </div>
                        
                    <?php else: ?>
                        <!-- Results Header -->
                        <div class="search-results-header mb-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-0">
                                        Showing <?= count($messages) ?> results
                                        <?php if (count($messages) >= 50): ?>
                                            <small class="text-muted">(limited to first 50 results)</small>
                                        <?php endif; ?>
                                    </h6>
                                </div>
                                <div class="col-md-4 text-end">
                                    <small class="text-muted">Searching in: <?= ucfirst($type) ?></small>
                                </div>
                            </div>
                        </div>

                        <!-- Search Results List -->
                        <div class="search-results-list">
                            <?php foreach ($messages as $message): ?>
                                <div class="search-result-item <?= $message['is_read'] ? 'read' : 'unread' ?>" 
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
                                                    <?php if ($type === 'sent'): ?>
                                                        <span class="participant-name">
                                                            To: <?= htmlspecialchars($message['first_name'] . ' ' . $message['last_name']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="participant-name">
                                                            From: <?= htmlspecialchars($message['first_name'] . ' ' . $message['last_name']) ?>
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
                                                            <?php if ($type !== 'archived'): ?>
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
                                                    <?= highlightSearchTerm(htmlspecialchars($message['subject']), $query) ?>
                                                </div>
                                                <div class="message-preview">
                                                    <?= highlightSearchTerm(htmlspecialchars(substr(strip_tags($message['message']), 0, 200)), $query) ?>
                                                    <?php if (strlen($message['message']) > 200): ?>...<?php endif; ?>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Highlight search terms in text
 */
function highlightSearchTerm($text, $term)
{
    if (empty($term)) {
        return $text;
    }

    $highlightedText = preg_replace(
        '/(' . preg_quote($term, '/') . ')/i',
        '<mark class="search-highlight">$1</mark>',
        $text
    );

    return $highlightedText;
}
?>

<!-- CSS Styles -->
<style>
.message-search-container {
    padding: 20px 0;
}

.search-header {
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

.search-result-item {
    border-bottom: 1px solid #dee2e6;
    padding: 15px;
    transition: background-color 0.2s;
}

.search-result-item:hover {
    background-color: #f8f9fa;
}

.search-result-item.unread {
    background-color: #fff3cd;
    font-weight: 600;
}

.search-result-item:last-child {
    border-bottom: none;
}

.message-meta {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.participant-name {
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

.search-result-item:hover .message-actions {
    opacity: 1;
}

.search-highlight {
    background-color: #ffeb3b;
    padding: 1px 2px;
    border-radius: 2px;
    font-weight: 600;
}

.search-results-header {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 15px;
}

.card-title {
    color: var(--primary-color, #20c997);
}

.btn-primary {
    background-color: var(--primary-color, #20c997);
    border-color: var(--primary-color, #20c997);
}

.btn-primary:hover {
    background-color: var(--primary-hover, #1ba085);
    border-color: var(--primary-hover, #1ba085);
}

.list-group-item-action:hover {
    background-color: var(--bs-list-group-hover-bg);
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
                        // Remove the message from search results
                        const messageItem = document.querySelector(`.search-result-item[data-message-id="${messageId}"]`);
                        if (messageItem) {
                            messageItem.style.transition = 'opacity 0.3s';
                            messageItem.style.opacity = '0';
                            setTimeout(() => messageItem.remove(), 300);
                        }
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
                        // Remove the message from search results
                        const messageItem = document.querySelector(`.search-result-item[data-message-id="${messageId}"]`);
                        if (messageItem) {
                            messageItem.style.transition = 'opacity 0.3s';
                            messageItem.style.opacity = '0';
                            setTimeout(() => messageItem.remove(), 300);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the message');
                });
            }
        });
    });
    
    // Auto-focus search input when page loads
    document.getElementById('q').focus();
    
    // Handle form submission
    const searchForm = document.querySelector('form[action*="/messages/search"]');
    searchForm.addEventListener('submit', function(e) {
        const query = document.getElementById('q').value.trim();
        if (!query) {
            e.preventDefault();
            alert('Please enter a search query');
            document.getElementById('q').focus();
        }
    });
});
</script>
