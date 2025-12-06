<?php
/**
 * Nyalife HMS - Register Lab Sample View
 */

$pageTitle = 'Register Lab Sample - Nyalife HMS';
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Register Lab Sample</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= $baseUrl ?? '' ?>/lab-tests/manage" class="btn btn-secondary">
                <i class="fas fa-list"></i> Manage Samples
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="<?= $baseUrl ?? '' ?>/lab-tests/register-sample" method="post" id="register-sample-form">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="patient_id" class="form-label">Patient *</label>
                            <select name="patient_id" id="patient_id" class="form-select" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $patient): ?>
                                <option value="<?= htmlspecialchars($patient['patient_id']) ?>">
                                    <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?> 
                                    (<?= htmlspecialchars($patient['patient_number']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="test_type_id" class="form-label">Test Type *</label>
                            <select name="test_type_id" id="test_type_id" class="form-select" required>
                                <option value="">Select Test Type</option>
                                <?php foreach ($testTypes as $test): ?>
                                <option value="<?= htmlspecialchars($test['test_type_id']) ?>">
                                    <?= htmlspecialchars($test['test_name']) ?> 
                                    (<?= htmlspecialchars($test['category'] ?? 'General') ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sample_type" class="form-label">Sample Type *</label>
                            <select name="sample_type" id="sample_type" class="form-select" required>
                                <option value="">Select Sample Type</option>
                                <option value="blood">Blood</option>
                                <option value="urine">Urine</option>
                                <option value="stool">Stool</option>
                                <option value="sputum">Sputum</option>
                                <option value="csf">Cerebrospinal Fluid (CSF)</option>
                                <option value="swab">Swab</option>
                                <option value="tissue">Tissue</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="collected_date" class="form-label">Collection Date *</label>
                            <input type="date" name="collected_date" id="collected_date" class="form-control" 
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="urgent" name="urgent" value="1">
                        <label class="form-check-label" for="urgent">
                            Mark as Urgent
                        </label>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                    <button type="submit" class="btn btn-primary">Register Sample</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug: Show form action and baseUrl
    console.log('Base URL:', '<?= $baseUrl ?? "undefined" ?>');
    console.log('Form action:', document.getElementById('register-sample-form').action);
    
    // Initialize select2 for better dropdown experience
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#patient_id, #test_type_id, #sample_type').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }
    
    // Form validation
    const form = document.getElementById('register-sample-form');
    form.addEventListener('submit', function(event) {
        console.log('Form submission started');
        
        let isValid = true;
        
        // Check required fields
        const requiredFields = ['patient_id', 'test_type_id', 'sample_type', 'collected_date'];
        requiredFields.forEach(function(field) {
            const input = document.getElementById(field);
            console.log('Checking field:', field, 'Value:', input.value);
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            event.preventDefault();
            alert('Please fill in all required fields');
            console.log('Form validation failed');
        } else {
            console.log('Form validation passed, submitting...');
        }
    });
});
</script> 