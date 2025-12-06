<?php
// Check if there's any form data to repopulate
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Clear the form data after using it
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>New Consultation</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= $baseUrl ?>/consultations" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Consultations
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="consultationForm" action="<?= $baseUrl ?>/consultations/store" method="POST">
                <?php if ($appointment): ?>
                    <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> This consultation is being created for an existing appointment.
                    </div>
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
                            <?php if ($appointment): ?>
                                <input type="hidden" name="patient_id" value="<?= $appointment['patient_id'] ?>">
                                <input type="text" class="form-control" value="<?= htmlspecialchars($appointment['patient_name']) ?>" readonly>
                            <?php else: ?>
                                <select class="form-select" id="patient_id" name="patient_id" required>
                                    <option value="">Select Patient</option>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?= $patient['patient_id'] ?>" 
                                            <?= ($formData['patient_id'] ?? '') == $patient['patient_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="doctor_id" class="form-label">Doctor <span class="text-danger">*</span></label>
                            <select class="form-select" id="doctor_id" name="doctor_id" required>
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?= $doctor['user_id'] ?>" 
                                        <?= (($formData['doctor_id'] ?? '') == $doctor['user_id'] || 
                                            (isset($appointment) && $appointment['doctor_id'] == $doctor['user_id'])) ? 'selected' : '' ?>>
                                        Dr. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="consultation_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="consultation_date" name="consultation_date" 
                                   value="<?= $formData['consultation_date'] ?? '' ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="scheduled" <?= ($formData['status'] ?? 'scheduled') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                <option value="in_progress" <?= ($formData['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="completed" <?= ($formData['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= ($formData['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="diagnosis" class="form-label">Diagnosis</label>
                    <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3"><?= htmlspecialchars($formData['diagnosis'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="treatment_plan" class="form-label">Treatment Plan</label>
                    <textarea class="form-control" id="treatment_plan" name="treatment_plan" rows="3"><?= htmlspecialchars($formData['treatment_plan'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($formData['notes'] ?? '') ?></textarea>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?= $baseUrl ?>/consultations" class="btn btn-secondary me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Consultation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('consultationForm');
    
    form.addEventListener('submit', function(event) {
        let isValid = true;
        
        // Reset validation
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        
        // Validate required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            }
        });
        
        if (!isValid) {
            event.preventDefault();
            event.stopPropagation();
            
            // Scroll to first invalid field
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
        
        form.classList.add('was-validated');
    });
});
</script>
