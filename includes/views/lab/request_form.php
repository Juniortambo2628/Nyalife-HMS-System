<?php
/**
 * Nyalife HMS - Lab Request Form
 */

$pageTitle = 'Lab Request Form - Nyalife HMS';
?>
<div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 text-gray-800">New Lab Request</h1>
                    <div>
                        <a href="<?= $baseUrl ?>/lab/requests" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Requests
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Request Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Lab Request Details</h6>
            </div>
            <div class="card-body">
                <form action="<?= $baseUrl ?>/lab/request/create" method="post" id="labRequestForm">
                    <!-- Patient Selection -->
                    <div class="form-group mb-4">
                        <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
                        <select class="form-control select2" id="patient_id" name="patient_id" required>
                            <option value="">Select Patient</option>
                            <?php if (!empty($patients)): ?>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?= $patient['patient_id'] ?>" <?= isset($selectedPatientId) && $selectedPatientId == $patient['patient_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?> (ID: <?= $patient['patient_number'] ?? $patient['patient_id'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Tests Selection -->
                    <div class="form-group mb-4">
                        <label class="form-label">Select Tests <span class="text-danger">*</span></label>
                        <div class="card">
                            <div class="card-body">
                                <?php if (!empty($testCategories)): ?>
                                    <?php foreach ($testCategories as $category): ?>
                                        <h6 class="mb-2"><?= htmlspecialchars($category['name']) ?></h6>
                                        <div class="row mb-3">
                                            <?php foreach ($category['tests'] as $test): ?>
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="test_type_id[]" value="<?= $test['id'] ?>" id="test_<?= $test['id'] ?>">
                                                        <label class="form-check-label" for="test_<?= $test['id'] ?>">
                                                            <?= htmlspecialchars($test['name']) ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        No lab tests available. Please contact the administrator.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="form-group mb-4">
                        <label for="clinical_notes" class="form-label">Clinical Notes</label>
                        <textarea class="form-control" id="clinical_notes" name="clinical_notes" rows="3" placeholder="Add any specific instructions or clinical information..."></textarea>
                    </div>

                    <!-- Priority -->
                    <div class="form-group mb-4">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-control" id="priority" name="priority">
                            <option value="normal">Normal</option>
                            <option value="urgent">Urgent</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
<script>
// Initialize components when the page is loaded or reloaded via AJAX
document.addEventListener('DOMContentLoaded', initLabRequestFormPage);
document.addEventListener('page:loaded', initLabRequestFormPage);

function initLabRequestFormPage() {
    // Initialize Select2 for better dropdown experience
    if (typeof $.fn.select2 !== 'undefined') {
        $('#patient_id').select2({
            placeholder: 'Select a patient',
            allowClear: true,
            width: '100%'
        });
    }
    
    // Form validation and submission
    const form = document.getElementById('labRequestForm');
    if (form && typeof Components !== 'undefined') {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            
            let isValid = true;
            
            // Validate patient selection
            const patientId = document.getElementById('patient_id').value;
            if (!patientId) {
                isValid = false;
                alert('Please select a patient.');
            }
            
            // Validate test selection
            const testCheckboxes = document.querySelectorAll('input[name="test_type_id[]"]:checked');
            if (testCheckboxes.length === 0) {
                isValid = false;
                alert('Please select at least one test.');
            }
            
            if (isValid) {
                // Submit form via AJAX
                const formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect to the lab requests list or view the new request
                        if (data.redirect) {
                            Components.loadPage(data.redirect);
                        } else {
                            Components.loadPage('<?= $baseUrl ?>/lab/requests');
                        }
                    } else {
                        // Display error message
                        alert(data.message || 'An error occurred while creating the lab request.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An unexpected error occurred. Please try again.');
                });
            }
        });
    }
}
</script> 