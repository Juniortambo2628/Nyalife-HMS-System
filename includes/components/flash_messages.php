<?php
/**
 * Flash Messages Component
 *
 * Displays success, error, warning, and info messages from session or controller variables
 */

// Check for session flash messages (from SessionManager)
$flashMessages = $_SESSION['flash_messages'] ?? [];

// Check for controller variables as fallback
if (empty($flashMessages)) {
    if (!empty($successMessage)) {
        $flashMessages[] = ['type' => 'success', 'message' => $successMessage];
    }
    if (!empty($errorMessage)) {
        $flashMessages[] = ['type' => 'danger', 'message' => $errorMessage];
    }
    if (!empty($warningMessage)) {
        $flashMessages[] = ['type' => 'warning', 'message' => $warningMessage];
    }
    if (!empty($infoMessage)) {
        $flashMessages[] = ['type' => 'info', 'message' => $infoMessage];
    }
}

// Display flash messages if any exist
if (!empty($flashMessages)): ?>
    <?php foreach ($flashMessages as $flash): ?>
        <div class="alert alert-<?= htmlspecialchars((string) $flash['type']) ?> alert-dismissible fade show" role="alert">
            <?php
            // Add appropriate icon based on message type
            $icon = match($flash['type']) {
                'success' => 'fas fa-check-circle',
                'danger', 'error' => 'fas fa-exclamation-circle',
                'warning' => 'fas fa-exclamation-triangle',
                'info' => 'fas fa-info-circle',
                default => 'fas fa-bell'
            };
        ?>
            <i class="<?= $icon ?> me-2"></i>
            <?= htmlspecialchars((string) $flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>
<?php endif;

// Clear session flash messages after displaying
if (isset($_SESSION['flash_messages'])) {
    unset($_SESSION['flash_messages']);
}
?>
