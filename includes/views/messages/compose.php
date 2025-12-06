<?php
/**
 * Compose Message View - With Reply/Forward Support
 */

$users = $users ?? [];
$mode = $mode ?? 'compose';
$replyMessage = $replyMessage ?? null;
$forwardMessage = $forwardMessage ?? null;

// Prepare form defaults based on mode
$defaultRecipient = '';
$defaultSubject = '';
$defaultMessage = '';
$defaultPriority = 'normal';

if ($mode === 'reply' && $replyMessage) {
    $defaultRecipient = $replyMessage['sender_id'];
    $defaultSubject = (strpos($replyMessage['subject'], 'Re:') === 0)
        ? $replyMessage['subject']
        : 'Re: ' . $replyMessage['subject'];
    $senderName = trim($replyMessage['sender_first_name'] . ' ' . $replyMessage['sender_last_name']);
    $defaultMessage = "\n\n--- Original Message ---\n"
        . "From: " . $senderName . "\n"
        . "Date: " . date('Y-m-d H:i', strtotime($replyMessage['created_at'])) . "\n"
        . "Subject: " . $replyMessage['subject'] . "\n\n"
        . $replyMessage['message'];
    $defaultPriority = $replyMessage['priority'] ?? 'normal';
} elseif ($mode === 'forward' && $forwardMessage) {
    $defaultSubject = (strpos($forwardMessage['subject'], 'Fwd:') === 0)
        ? $forwardMessage['subject']
        : 'Fwd: ' . $forwardMessage['subject'];
    $senderName = trim($forwardMessage['sender_first_name'] . ' ' . $forwardMessage['sender_last_name']);
    $recipientName = trim($forwardMessage['recipient_first_name'] . ' ' . $forwardMessage['recipient_last_name']);
    $defaultMessage = "\n\n--- Forwarded Message ---\n"
        . "From: " . $senderName . "\n"
        . "To: " . $recipientName . "\n"
        . "Date: " . date('Y-m-d H:i', strtotime($forwardMessage['created_at'])) . "\n"
        . "Subject: " . $forwardMessage['subject'] . "\n\n"
        . $forwardMessage['message'];
    $defaultPriority = $forwardMessage['priority'] ?? 'normal';
}
?>

<div class="compose-message-container">
    <!-- Header -->
    <div class="compose-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="page-title">
                    <i class="fas fa-<?= $mode === 'reply' ? 'reply' : ($mode === 'forward' ? 'share' : 'edit') ?>"></i>
                    <?= ucfirst($mode) ?> Message
                </h2>
                <p class="page-subtitle">
                    <?php if ($mode === 'reply'): ?>
                        Reply to: "<?= htmlspecialchars($replyMessage['subject']) ?>"
                    <?php elseif ($mode === 'forward'): ?>
                        Forward: "<?= htmlspecialchars($forwardMessage['subject']) ?>"
                    <?php else: ?>
                        Send a message to another user
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-6 text-end">
                <a href="<?= $baseUrl ?>/messages" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Messages
                </a>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="<?= $baseUrl ?>/messages/send" method="POST" id="composeForm">
                        <!-- Recipient Selection -->
                        <div class="mb-3">
                            <label for="recipient_id" class="form-label">
                                To <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="recipient_id" name="recipient_id" required <?= $mode === 'reply' ? 'readonly' : '' ?>>
                                <option value="">Select recipient...</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['user_id'] ?>"
                                            <?= ($defaultRecipient == $user['user_id']) ? 'selected' : '' ?>
                                            data-email="<?= htmlspecialchars($user['email']) ?>"
                                            data-role="<?= htmlspecialchars($user['role']) ?>">
                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                        (<?= htmlspecialchars($user['role']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Select the recipient for your message</div>
                        </div>

                        <!-- Priority Selection -->
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-control" id="priority" name="priority">
                                <option value="low" <?= $defaultPriority === 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="normal" <?= $defaultPriority === 'normal' ? 'selected' : '' ?>>Normal</option>
                                <option value="high" <?= $defaultPriority === 'high' ? 'selected' : '' ?>>High</option>
                            </select>
                        </div>

                        <!-- Subject -->
                        <div class="mb-3">
                            <label for="subject" class="form-label">
                                Subject <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   value="<?= htmlspecialchars($defaultSubject) ?>"
                                   placeholder="Enter message subject" required maxlength="255">
                        </div>

                        <!-- Message Content -->
                        <div class="mb-4">
                            <label for="message" class="form-label">
                                Message <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="message" name="message" rows="10" 
                                      placeholder="Type your message here..." required><?= htmlspecialchars($defaultMessage) ?></textarea>
                            <div class="form-text">
                                <span id="charCount">0</span> characters
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearForm()">
                                    <i class="fas fa-eraser"></i> Clear
                                </button>
                            </div>
                            <div>
                                <a href="<?= $baseUrl ?>/messages" class="btn btn-secondary me-2">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Quick Tips -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-lightbulb"></i> Quick Tips
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Use descriptive subject lines to help recipients prioritize messages
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Set priority to "High" only for urgent matters
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Keep messages clear and professional
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Recipients will receive notifications for new messages
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS Styles -->
<style>
.compose-message-container {
    padding: 20px 0;
}

.compose-header {
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

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.form-control:focus {
    border-color: var(--primary-color, #20c997);
    box-shadow: 0 0 0 0.2rem rgba(32, 201, 151, 0.25);
}

#message {
    min-height: 200px;
    resize: vertical;
}

.btn-primary {
    background-color: var(--primary-color, #20c997);
    border-color: var(--primary-color, #20c997);
}

.btn-primary:hover {
    background-color: var(--primary-hover, #1ba085);
    border-color: var(--primary-hover, #1ba085);
}

.text-danger {
    color: #dc3545 !important;
}

#charCount {
    font-weight: 600;
}

.list-unstyled li {
    display: flex;
    align-items: flex-start;
}

.card-title {
    color: var(--primary-color, #20c997);
}
</style>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const messageTextarea = document.getElementById('message');
    const charCountSpan = document.getElementById('charCount');
    const form = document.getElementById('composeForm');
    const submitButton = form.querySelector('button[type="submit"]');
    
    // Character counter
    function updateCharCount() {
        const count = messageTextarea.value.length;
        charCountSpan.textContent = count.toLocaleString();
        
        // Color coding based on length
        if (count > 2000) {
            charCountSpan.className = 'text-warning';
        } else if (count > 3000) {
            charCountSpan.className = 'text-danger';
        } else {
            charCountSpan.className = 'text-muted';
        }
    }
    
    messageTextarea.addEventListener('input', updateCharCount);
    updateCharCount(); // Initial count
    
    // Form validation and submission
    form.addEventListener('submit', function(e) {
        const recipient = document.getElementById('recipient_id').value;
        const subject = document.getElementById('subject').value.trim();
        const message = messageTextarea.value.trim();
        
        if (!recipient || !subject || !message) {
            e.preventDefault();
            alert('Please fill in all required fields');
            return;
        }
        
        // Disable submit button to prevent double submission
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    });
    
    // Auto-resize textarea
    function autoResize() {
        messageTextarea.style.height = 'auto';
        messageTextarea.style.height = Math.max(200, messageTextarea.scrollHeight) + 'px';
    }
    
    messageTextarea.addEventListener('input', autoResize);
    
    // Enhanced recipient selection with search
    const recipientSelect = document.getElementById('recipient_id');
    
    // Add search functionality to select (if using Select2 or similar library)
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $(recipientSelect).select2({
            placeholder: 'Select recipient...',
            allowClear: true,
            templateResult: function(user) {
                if (!user.id) return user.text;
                
                const $user = $(
                    '<span>' + 
                        '<strong>' + user.text.split(' (')[0] + '</strong>' +
                        '<br><small class="text-muted">' + user.text.split(' (')[1] + '</small>' +
                    '</span>'
                );
                return $user;
            }
        });
    }
});

// Clear form function
function clearForm() {
    if (confirm('Are you sure you want to clear the form? All unsaved changes will be lost.')) {
        document.getElementById('composeForm').reset();
        document.getElementById('charCount').textContent = '0';
        
        // Reset select2 if active
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('#recipient_id').val(null).trigger('change');
        }
        
        // Reset textarea height
        const messageTextarea = document.getElementById('message');
        messageTextarea.style.height = '200px';
    }
}

// Auto-save draft functionality (optional enhancement)
let autoSaveTimeout;
function autoSaveDraft() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(function() {
        const formData = new FormData(document.getElementById('composeForm'));
        const draftData = {
            recipient_id: formData.get('recipient_id'),
            subject: formData.get('subject'),
            message: formData.get('message'),
            priority: formData.get('priority')
        };
        
        // Save to localStorage as draft
        if (draftData.recipient_id || draftData.subject || draftData.message) {
            localStorage.setItem('messageDraft', JSON.stringify(draftData));
            console.log('Draft saved');
        }
    }, 3000); // Save after 3 seconds of inactivity
}

// Load draft on page load
window.addEventListener('load', function() {
    const savedDraft = localStorage.getItem('messageDraft');
    if (savedDraft) {
        try {
            const draftData = JSON.parse(savedDraft);
            if (draftData.recipient_id) document.getElementById('recipient_id').value = draftData.recipient_id;
            if (draftData.subject) document.getElementById('subject').value = draftData.subject;
            if (draftData.message) document.getElementById('message').value = draftData.message;
            if (draftData.priority) document.getElementById('priority').value = draftData.priority;
            
            // Update character count
            document.getElementById('charCount').textContent = draftData.message ? draftData.message.length.toLocaleString() : '0';
        } catch (e) {
            console.error('Error loading draft:', e);
        }
    }
});

// Clear draft when form is successfully submitted
document.getElementById('composeForm').addEventListener('submit', function() {
    localStorage.removeItem('messageDraft');
});

// Add auto-save listeners
document.getElementById('recipient_id').addEventListener('change', autoSaveDraft);
document.getElementById('subject').addEventListener('input', autoSaveDraft);
document.getElementById('message').addEventListener('input', autoSaveDraft);
document.getElementById('priority').addEventListener('change', autoSaveDraft);
</script>
