<?php
/**
 * Nyalife HMS - Edit Consultation View
 */

$pageTitle = 'Edit Consultation - Nyalife HMS';
// Use the renderView method to include the layout
// The content of this file will be captured and inserted into the layout

// Ensure vital_signs is an array
if (isset($consultation['vital_signs']) && is_string($consultation['vital_signs'])) {
    $consultation['vital_signs'] = json_decode($consultation['vital_signs'], true) ?: [];
} elseif (!isset($consultation['vital_signs'])) {
    $consultation['vital_signs'] = [];
}
?>

    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Edit Consultation</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="<?= $baseUrl ?>/consultations/view/<?= $consultation['consultation_id'] ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Consultation
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (!empty($flashMessages)): ?>
            <?php foreach ($flashMessages as $message): ?>
                <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Include the comprehensive form -->
        <?php include __DIR__ . '/_form.php'; ?>
    </div>