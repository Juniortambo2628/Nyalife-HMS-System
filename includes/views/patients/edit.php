<?php
/**
 * Nyalife HMS - Edit Patient View
 */

$pageTitle = 'Edit Patient - Nyalife HMS';
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Edit Patient</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= $baseUrl ?>/patients/view/<?= $patient['patient_id'] ?>" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to Patient
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="<?= $baseUrl ?>/patients/update/<?= $patient['patient_id'] ?>" method="post" id="patientForm">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <ul class="nav nav-tabs" id="patientTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                            Personal Information
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                            Contact Information
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="medical-tab" data-bs-toggle="tab" data-bs-target="#medical" type="button" role="tab">
                            Medical Information
                        </button>
                    </li>
                </ul>

                <div class="tab-content p-3 border border-top-0 rounded-bottom" id="patientTabsContent">
                    <!-- Personal Information Tab -->
                    <div class="tab-pane fade show active" id="personal" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required 
                                       value="<?= htmlspecialchars($patient['first_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required
                                       value="<?= htmlspecialchars($patient['last_name'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required
                                       value="<?= htmlspecialchars($patient['date_of_birth'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" <?= (isset($patient['gender']) && $patient['gender'] === 'male') ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= (isset($patient['gender']) && $patient['gender'] === 'female') ? 'selected' : '' ?>>Female</option>
                                    <option value="other" <?= (isset($patient['gender']) && $patient['gender'] === 'other') ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="blood_group" class="form-label">Blood Group</label>
                                <select class="form-select" id="blood_group" name="blood_group">
                                    <option value="">Select Blood Group</option>
                                    <option value="A+" <?= (isset($patient['blood_group']) && $patient['blood_group'] === 'A+') ? 'selected' : '' ?>>A+</option>
                                    <option value="A-" <?= (isset($patient['blood_group']) && $patient['blood_group'] === 'A-') ? 'selected' : '' ?>>A-</option>
                                    <option value="B+" <?= (isset($patient['blood_group']) && $patient['blood_group'] === 'B+') ? 'selected' : '' ?>>B+</option>
                                    <option value="B-" <?= (isset($patient['blood_group']) && $patient['blood_group'] === 'B-') ? 'selected' : '' ?>>B-</option>
                                    <option value="AB+" <?= (isset($patient['blood_group']) && $patient['blood_group'] === 'AB+') ? 'selected' : '' ?>>AB+</option>
                                    <option value="AB-" <?= (isset($patient['blood_group']) && $patient['blood_group'] === 'AB-') ? 'selected' : '' ?>>AB-</option>
                                    <option value="O+" <?= (isset($patient['blood_group']) && $patient['blood_group'] === 'O+') ? 'selected' : '' ?>>O+</option>
                                    <option value="O-" <?= (isset($patient['blood_group']) && $patient['blood_group'] === 'O-') ? 'selected' : '' ?>>O-</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="patient_number" class="form-label">Patient Number</label>
                                <input type="text" class="form-control" id="patient_number" name="patient_number" readonly
                                       value="<?= htmlspecialchars($patient['patient_number'] ?? '') ?>">
                                <small class="form-text text-muted">Patient number cannot be changed</small>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Tab -->
                    <div class="tab-pane fade" id="contact" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?= htmlspecialchars($patient['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" required
                                       value="<?= htmlspecialchars($patient['phone'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($patient['address'] ?? '') ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city"
                                       value="<?= htmlspecialchars($patient['city'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="state" name="state"
                                       value="<?= htmlspecialchars($patient['state'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code"
                                       value="<?= htmlspecialchars($patient['postal_code'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country"
                                   value="<?= htmlspecialchars($patient['country'] ?? '') ?>">
                        </div>
                        
                        <div class="border p-3 rounded mb-3">
                            <h5>Emergency Contact</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="emergency_contact_name" class="form-label">Contact Name</label>
                                    <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name"
                                           value="<?= htmlspecialchars($patient['emergency_contact_name'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="emergency_contact_phone" class="form-label">Contact Phone</label>
                                    <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone"
                                           value="<?= htmlspecialchars($patient['emergency_contact_phone'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="emergency_contact_relation" class="form-label">Relationship</label>
                                <input type="text" class="form-control" id="emergency_contact_relation" name="emergency_contact_relation"
                                       value="<?= htmlspecialchars($patient['emergency_contact_relation'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Medical Information Tab -->
                    <div class="tab-pane fade" id="medical" role="tabpanel">
                        <div class="mb-3">
                            <label for="medical_history" class="form-label">Medical History</label>
                            <textarea class="form-control" id="medical_history" name="medical_history" rows="3"><?= htmlspecialchars($patient['medical_history'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="allergies" class="form-label">Allergies</label>
                            <textarea class="form-control" id="allergies" name="allergies" rows="2"><?= htmlspecialchars($patient['allergies'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="current_medications" class="form-label">Current Medications</label>
                            <textarea class="form-control" id="current_medications" name="current_medications" rows="2"><?= htmlspecialchars($patient['current_medications'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="insurance_provider" class="form-label">Insurance Provider</label>
                            <input type="text" class="form-control" id="insurance_provider" name="insurance_provider"
                                   value="<?= htmlspecialchars($patient['insurance_provider'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="insurance_policy_number" class="form-label">Insurance Policy Number</label>
                            <input type="text" class="form-control" id="insurance_policy_number" name="insurance_policy_number"
                                   value="<?= htmlspecialchars($patient['insurance_policy_number'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-secondary" id="prevBtn" onclick="nextPrev(-1)">Previous</button>
                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextPrev(1)">Next</button>
                    <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">Update Patient</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Tab navigation for the form
let currentTab = 0;
showTab(currentTab);

function showTab(n) {
    const tabs = document.getElementsByClassName('tab-pane');
    const buttons = document.querySelectorAll('.nav-link');
    
    if (n === 0) {
        document.getElementById('prevBtn').style.display = 'none';
    } else {
        document.getElementById('prevBtn').style.display = 'inline-block';
    }
    
    if (n === (tabs.length - 1)) {
        document.getElementById('nextBtn').style.display = 'none';
        document.getElementById('submitBtn').style.display = 'inline-block';
    } else {
        document.getElementById('nextBtn').style.display = 'inline-block';
        document.getElementById('submitBtn').style.display = 'none';
    }
    
    // Activate the current tab
    for (let i = 0; i < buttons.length; i++) {
        if (i === n) {
            buttons[i].classList.add('active');
            tabs[i].classList.add('show', 'active');
        } else {
            buttons[i].classList.remove('active');
            tabs[i].classList.remove('show', 'active');
        }
    }
}

function nextPrev(n) {
    const tabs = document.getElementsByClassName('tab-pane');
    
    // Exit if any required fields in the current tab are not filled
    if (n === 1 && !validateForm()) return false;
    
    // Update current tab
    currentTab = currentTab + n;
    
    // If reached the end, submit the form
    if (currentTab >= tabs.length) {
        document.getElementById("patientForm").submit();
        return false;
    }
    
    // Otherwise, display the correct tab
    showTab(currentTab);
}

function validateForm() {
    let valid = true;
    const tabs = document.getElementsByClassName('tab-pane');
    const currentPane = tabs[currentTab];
    
    // Get all inputs in the current tab with the required attribute
    const requiredInputs = currentPane.querySelectorAll('[required]');
    
    // Check each required input
    requiredInputs.forEach(input => {
        if (input.value.trim() === '') {
            // Add an error indicator
            input.classList.add('is-invalid');
            valid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return valid;
}

// Add input event listeners to all required fields to remove validation error as soon as they type
document.querySelectorAll('[required]').forEach(input => {
    input.addEventListener('input', function() {
        if (this.value.trim() !== '') {
            this.classList.remove('is-invalid');
        }
    });
});

// Add event listeners to tab buttons
document.querySelectorAll('.nav-link').forEach((button, index) => {
    button.addEventListener('click', function() {
        // Validate current tab before allowing to switch
        if (index > currentTab && !validateForm()) {
            event.preventDefault();
            event.stopPropagation();
        } else {
            currentTab = index;
            showTab(currentTab);
        }
    });
});
</script> 