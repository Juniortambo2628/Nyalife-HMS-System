<?php
/**
 * Nyalife HMS - Create Consultation View
 */

$pageTitle = 'Create Consultation - Nyalife HMS';

// Check if there's any form data to repopulate
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Clear the form data after using it

// Define status options
$statusOptions = [
    'scheduled' => 'Scheduled',
    'in_progress' => 'In Progress',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled'
];

// Debug - Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Form submitted: " . json_encode($_POST));
}
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

        <!-- Flash Messages -->
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($errorMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Consultation Form -->
        <div class="card">
            <div class="card-body">
                <form method="post" action="<?= $baseUrl ?>/consultations/create" id="consultation-form">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" id="patient_id" class="form-select select2" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?= $patient['patient_id'] ?>" <?= (($formData['patient_id'] ?? $selectedPatientId ?? '') == $patient['patient_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?> (<?= htmlspecialchars($patient['patient_id']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="doctor_id" class="form-label">Doctor <span class="text-danger">*</span></label>
                            <select name="doctor_id" id="doctor_id" class="form-select select2" required>
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?= $doctor['user_id'] ?>" <?= (($formData['doctor_id'] ?? $selectedDoctorId ?? '') == $doctor['user_id']) ? 'selected' : '' ?>>
                                        Dr. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="consultation_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control datepicker" id="consultation_date" name="consultation_date" 
                                   value="<?= htmlspecialchars($formData['consultation_date'] ?? date('Y-m-d')) ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="consultation_time" class="form-label">Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="consultation_time" name="consultation_time" 
                                   value="<?= htmlspecialchars($formData['consultation_time'] ?? date('H:i')) ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select" required>
                                <?php foreach ($statusOptions as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= ($formData['status'] ?? 'scheduled') === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="is_walk_in" name="is_walk_in" value="1" 
                                       <?= isset($formData['is_walk_in']) && $formData['is_walk_in'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_walk_in">
                                    Walk-in Consultation
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="chief_complaint" class="form-label">Chief Complaint <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="chief_complaint" name="chief_complaint" rows="3" required><?= htmlspecialchars($formData['chief_complaint'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Initial Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($formData['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="reset" class="btn btn-outline-secondary me-md-2">Clear</button>
                        <button type="submit" class="btn btn-primary">Create Consultation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
// Initialize components when the page is loaded or reloaded via AJAX
document.addEventListener('DOMContentLoaded', initCreateConsultationPage);
document.addEventListener('page:loaded', initCreateConsultationPage);

function initCreateConsultationPage() {
    // Initialize Select2 dropdowns if available
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Select an option'
        });
    }
    
    // Initialize datepickers if available
    if (typeof $.fn.datepicker !== 'undefined') {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
    }
    
    // Handle form submission with AJAX
    const consultationForm = document.getElementById('consultation-form');
    if (consultationForm && typeof Components !== 'undefined') {
        consultationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (!consultationForm.checkValidity()) {
                e.stopPropagation();
                consultationForm.classList.add('was-validated');
                return;
            }
            
            // Submit form via AJAX
            const formData = new FormData(consultationForm);
            
            fetch(consultationForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to the view page or consultations list
                    if (data.redirect) {
                        Components.loadPage(data.redirect);
                    } else {
                        Components.loadPage('<?= $baseUrl ?>/consultations');
                    }
                } else {
                    // Display error message
                    alert(data.message || 'An error occurred while creating the consultation.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected error occurred. Please try again.');
            });
        });
    }
}
</script>
