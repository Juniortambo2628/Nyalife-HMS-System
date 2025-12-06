<?php
/**
 * Nyalife HMS - Edit User View
 */

$pageTitle = 'Edit User - Nyalife HMS';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Edit User</h1>
            <div>
                <a href="<?= $baseUrl ?>/users" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i> Back to Users
                </a>
                <a href="<?= $baseUrl ?>/users/view/<?= $user['user_id'] ?>" class="btn btn-outline-primary">
                    <i class="fas fa-eye me-1"></i> View User
                </a>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
            </div>
            <div class="card-body">
                <?php
                $errors = SessionManager::get('form_errors', []);
$formData = SessionManager::get('form_data', []);

// Clear the session data
SessionManager::remove('form_errors');
SessionManager::remove('form_data');

if (!empty($errors) && isset($errors['general'])):
    ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $errors['general'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <form action="<?= $baseUrl ?>/users/update/<?= $user['user_id'] ?>" method="post" id="updateUserForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                                   id="first_name" name="first_name" 
                                   value="<?= isset($formData['first_name']) ? $formData['first_name'] : $user['first_name'] ?>" required>
                            <?php if (isset($errors['first_name'])): ?>
                            <div class="invalid-feedback"><?= $errors['first_name'] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                                   id="last_name" name="last_name" 
                                   value="<?= isset($formData['last_name']) ? $formData['last_name'] : $user['last_name'] ?>" required>
                            <?php if (isset($errors['last_name'])): ?>
                            <div class="invalid-feedback"><?= $errors['last_name'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                   id="email" name="email" 
                                   value="<?= isset($formData['email']) ? $formData['email'] : $user['email'] ?>" required>
                            <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= $errors['email'] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                   id="phone" name="phone" 
                                   value="<?= isset($formData['phone']) ? $formData['phone'] : $user['phone'] ?>" required>
                            <?php if (isset($errors['phone'])): ?>
                            <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                   id="username" name="username" 
                                   value="<?= isset($formData['username']) ? $formData['username'] : $user['username'] ?>" <?= $currentUser['id'] == $user['user_id'] ? 'readonly' : '' ?>>
                            <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?= $errors['username'] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label for="role_id" class="form-label">Role</label>
                            <select class="form-select <?= isset($errors['role_id']) ? 'is-invalid' : '' ?>" id="role_id" name="role_id" <?= $currentUser['id'] == $user['user_id'] ? 'disabled' : '' ?>>
                                <option value="">Select Role</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['role_id'] ?>" 
                                        <?= (isset($formData['role_id']) && $formData['role_id'] == $role['role_id']) ||
                                (!isset($formData['role_id']) && $user['role_id'] == $role['role_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['role_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['role_id'])): ?>
                            <div class="invalid-feedback"><?= $errors['role_id'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Doctor-specific fields (only shown when role is doctor) -->
                    <div id="doctor-fields" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="specialization" class="form-label">Specialization</label>
                                <input type="text" class="form-control" id="specialization" name="specialization" 
                                       value="<?= isset($formData['specialization']) ? $formData['specialization'] : ($doctorDetails['specialization'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="license_number" class="form-label">License Number</label>
                                <input type="text" class="form-control" id="license_number" name="license_number" 
                                       value="<?= isset($formData['license_number']) ? $formData['license_number'] : ($doctorDetails['license_number'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Patient-specific fields (only shown when role is patient) -->
                    <div id="patient-fields" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="blood_group" class="form-label">Blood Group</label>
                                <select class="form-select" id="blood_group" name="blood_group">
                                    <option value="">Select Blood Group</option>
                                    <?php
                        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
foreach ($bloodGroups as $bg):
    ?>
                                    <option value="<?= $bg ?>" 
                                        <?= (isset($formData['blood_group']) && $formData['blood_group'] == $bg) ||
            (!isset($formData['blood_group']) && isset($patientDetails['blood_group']) && $patientDetails['blood_group'] == $bg) ? 'selected' : '' ?>>
                                        <?= $bg ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="patient_number" class="form-label">Patient Number</label>
                                <input type="text" class="form-control" id="patient_number" name="patient_number" 
                                       value="<?= isset($formData['patient_number']) ? $formData['patient_number'] : ($patientDetails['patient_number'] ?? '') ?>" readonly>
                                <div class="form-text">Patient number is generated automatically.</div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="emergency_name" class="form-label">Emergency Contact Name</label>
                                <input type="text" class="form-control" id="emergency_name" name="emergency_name" 
                                       value="<?= isset($formData['emergency_name']) ? $formData['emergency_name'] : ($patientDetails['emergency_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="emergency_contact" class="form-label">Emergency Contact Phone</label>
                                <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" 
                                       value="<?= isset($formData['emergency_contact']) ? $formData['emergency_contact'] : ($patientDetails['emergency_contact'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="relationship" class="form-label">Relationship</label>
                                <input type="text" class="form-control" id="relationship" name="relationship" 
                                       value="<?= isset($formData['relationship']) ? $formData['relationship'] : ($patientDetails['relationship'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Account Status</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_active" id="status_active" value="1" 
                                           <?= (isset($formData['is_active']) && $formData['is_active'] == 1) ||
               (!isset($formData['is_active']) && $user['is_active'] == 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="status_active">Active</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_active" id="status_inactive" value="0" 
                                           <?= (isset($formData['is_active']) && $formData['is_active'] == 0) ||
               (!isset($formData['is_active']) && $user['is_active'] == 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="status_inactive">Inactive</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="change_password" name="change_password">
                                <label class="form-check-label" for="change_password">
                                    Change Password
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div id="password-fields" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                       id="password" name="password">
                                <div class="form-text">Password must be at least 8 characters and include uppercase, lowercase, and numbers.</div>
                                <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback"><?= $errors['password'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                       id="confirm_password" name="confirm_password">
                                <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
<script>
// Initialize components when the page is loaded or reloaded via AJAX
document.addEventListener('DOMContentLoaded', initEditUserPage);
document.addEventListener('page:loaded', initEditUserPage);

function initEditUserPage() {
    const roleSelect = document.getElementById('role_id');
    const doctorFields = document.getElementById('doctor-fields');
    const patientFields = document.getElementById('patient-fields');
    const changePasswordCheckbox = document.getElementById('change_password');
    const passwordFields = document.getElementById('password-fields');
    
    // Show/hide role-specific fields based on role selection
    function handleRoleChange() {
        if (!roleSelect) return;
        
        const selectedOption = roleSelect.options[roleSelect.selectedIndex];
        const roleName = selectedOption.textContent.trim().toLowerCase();
        
        // Hide all role-specific fields first
        if (doctorFields) doctorFields.style.display = 'none';
        if (patientFields) patientFields.style.display = 'none';
        
        // Show specific fields based on role
        if (roleName === 'doctor' && doctorFields) {
            doctorFields.style.display = 'block';
        } else if (roleName === 'patient' && patientFields) {
            patientFields.style.display = 'block';
        }
    }
    
    // Toggle password fields based on checkbox
    function togglePasswordFields() {
        if (changePasswordCheckbox && passwordFields) {
            passwordFields.style.display = changePasswordCheckbox.checked ? 'block' : 'none';
            
            // Reset password fields when unchecked
            if (!changePasswordCheckbox.checked) {
                const passwordInput = document.getElementById('password');
                const confirmPasswordInput = document.getElementById('confirm_password');
                
                if (passwordInput) passwordInput.value = '';
                if (confirmPasswordInput) confirmPasswordInput.value = '';
            }
        }
    }
    
    // Initialize event listeners
    if (roleSelect) {
        roleSelect.addEventListener('change', handleRoleChange);
    }
    
    if (changePasswordCheckbox) {
        changePasswordCheckbox.addEventListener('change', togglePasswordFields);
    }
    
    // Initialize form submission with AJAX
    const updateUserForm = document.getElementById('updateUserForm');
    if (updateUserForm && typeof Components !== 'undefined') {
        updateUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (changePasswordCheckbox && changePasswordCheckbox.checked) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (password !== confirmPassword) {
                    alert('Passwords do not match');
                    return;
                }
            }
            
            // Submit form via AJAX
            const formData = new FormData(updateUserForm);
            
            fetch(updateUserForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to the user view page
                    if (data.redirect) {
                        Components.loadPage(data.redirect);
                    } else {
                        Components.loadPage('<?= $baseUrl ?>/users/view/<?= $user['user_id'] ?>');
                    }
                } else {
                    // Display error message
                    if (data.errors) {
                        // Handle field-specific errors
                        Object.keys(data.errors).forEach(field => {
                            const input = document.getElementById(field);
                            if (input) {
                                input.classList.add('is-invalid');
                                
                                // Create or update feedback div
                                let feedback = input.nextElementSibling;
                                if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                                    feedback = document.createElement('div');
                                    feedback.className = 'invalid-feedback';
                                    input.parentNode.insertBefore(feedback, input.nextSibling);
                                }
                                feedback.textContent = data.errors[field];
                            }
                        });
                    } else {
                        // General error
                        alert(data.message || 'An error occurred while updating the user.');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected error occurred. Please try again.');
            });
        });
    }
    
    // Initialize Select2 for dropdowns if available
    if (typeof $.fn.select2 !== 'undefined') {
        if (roleSelect && !roleSelect.disabled) {
            $(roleSelect).select2({
                placeholder: 'Select Role',
                width: '100%'
            });
        }
        
        const bloodGroupSelect = document.getElementById('blood_group');
        if (bloodGroupSelect) {
            $(bloodGroupSelect).select2({
                placeholder: 'Select Blood Group',
                width: '100%'
            });
        }
    }
    
    // Initial setup
    handleRoleChange();
    togglePasswordFields();
}
</script>
