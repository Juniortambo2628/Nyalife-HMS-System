<?php
/**
 * Nyalife HMS - Create Patient View
 */

$pageTitle = 'Create Patient - Nyalife HMS';
?>
<div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Register New Patient</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="<?= $baseUrl ?>/patients" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Patients
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

        <!-- Patient Registration Form -->
        <div class="card">
            <div class="card-body">
                <form method="post" action="<?= $baseUrl ?>/patients/store" id="patient-form">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?= htmlspecialchars($formData['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?= htmlspecialchars($formData['last_name'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male" <?= ($formData['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= ($formData['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= ($formData['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" class="form-control datepicker" id="date_of_birth" name="date_of_birth" 
                                   value="<?= htmlspecialchars($formData['date_of_birth'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="blood_type" class="form-label">Blood Type</label>
                            <select class="form-select" id="blood_type" name="blood_type">
                                <option value="">Unknown</option>
                                <option value="A+" <?= ($formData['blood_type'] ?? '') === 'A+' ? 'selected' : '' ?>>A+</option>
                                <option value="A-" <?= ($formData['blood_type'] ?? '') === 'A-' ? 'selected' : '' ?>>A-</option>
                                <option value="B+" <?= ($formData['blood_type'] ?? '') === 'B+' ? 'selected' : '' ?>>B+</option>
                                <option value="B-" <?= ($formData['blood_type'] ?? '') === 'B-' ? 'selected' : '' ?>>B-</option>
                                <option value="AB+" <?= ($formData['blood_type'] ?? '') === 'AB+' ? 'selected' : '' ?>>AB+</option>
                                <option value="AB-" <?= ($formData['blood_type'] ?? '') === 'AB-' ? 'selected' : '' ?>>AB-</option>
                                <option value="O+" <?= ($formData['blood_type'] ?? '') === 'O+' ? 'selected' : '' ?>>O+</option>
                                <option value="O-" <?= ($formData['blood_type'] ?? '') === 'O-' ? 'selected' : '' ?>>O-</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($formData['phone'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($formData['email'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" rows="2" required><?= htmlspecialchars($formData['address'] ?? '') ?></textarea>
                    </div>

                    <hr class="my-4">
                    <h5>Emergency Contact Information</h5>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                            <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" 
                                   value="<?= htmlspecialchars($formData['emergency_contact_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
                            <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" 
                                   value="<?= htmlspecialchars($formData['emergency_contact_phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="emergency_contact_relationship" class="form-label">Relationship to Patient</label>
                        <input type="text" class="form-control" id="emergency_contact_relationship" name="emergency_contact_relationship" 
                               value="<?= htmlspecialchars($formData['emergency_contact_relationship'] ?? '') ?>">
                    </div>

                    <hr class="my-4">
                    <h5>Medical History</h5>

                    <div class="mb-3">
                        <label for="allergies" class="form-label">Known Allergies</label>
                        <textarea class="form-control" id="allergies" name="allergies" rows="2"><?= htmlspecialchars($formData['allergies'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="chronic_conditions" class="form-label">Chronic Conditions</label>
                        <textarea class="form-control" id="chronic_conditions" name="chronic_conditions" rows="2"><?= htmlspecialchars($formData['chronic_conditions'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="current_medications" class="form-label">Current Medications</label>
                        <textarea class="form-control" id="current_medications" name="current_medications" rows="2"><?= htmlspecialchars($formData['current_medications'] ?? '') ?></textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="reset" class="btn btn-outline-secondary me-md-2">Clear</button>
                        <button type="submit" class="btn btn-primary">Register Patient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
// Initialize components when the page is loaded or reloaded via AJAX
document.addEventListener('DOMContentLoaded', initCreatePatientPage);
document.addEventListener('page:loaded', initCreatePatientPage);

function initCreatePatientPage() {
    // Initialize datepickers if available
    if (typeof $.fn.datepicker !== 'undefined') {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
    }
    
    // Handle form submission with AJAX
    const patientForm = document.getElementById('patient-form');
    if (patientForm && typeof Components !== 'undefined') {
        patientForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (!patientForm.checkValidity()) {
                e.stopPropagation();
                patientForm.classList.add('was-validated');
                return;
            }
            
            // Submit form via AJAX
            const formData = new FormData(patientForm);
            
            fetch(patientForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to the view page or patients list
                    if (data.redirect) {
                        Components.loadPage(data.redirect);
                    } else {
                        Components.loadPage('<?= $baseUrl ?>/patients');
                    }
                } else {
                    // Display error message
                    alert(data.message || 'An error occurred while registering the patient.');
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