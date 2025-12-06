<?php
/**
 * Nyalife HMS - Edit Profile View
 */

$pageTitle = 'Edit Profile - Nyalife HMS';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit Profile</h1>
        <a href="<?= $baseUrl ?>/profile" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    <div class="row">
        <!-- Profile Information -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Profile Information</h6>
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
                    
                    <form action="<?= $baseUrl ?>/profile/update" method="post" id="updateProfileForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                                       id="first_name" name="first_name" value="<?= $formData['first_name'] ?? $user['first_name'] ?>" required>
                                <?php if (isset($errors['first_name'])): ?>
                                <div class="invalid-feedback"><?= $errors['first_name'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                                       id="last_name" name="last_name" value="<?= $formData['last_name'] ?? $user['last_name'] ?>" required>
                                <?php if (isset($errors['last_name'])): ?>
                                <div class="invalid-feedback"><?= $errors['last_name'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                       id="email" name="email" value="<?= $formData['email'] ?? $user['email'] ?>" required>
                                <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                       id="phone" name="phone" value="<?= $formData['phone'] ?? $user['phone'] ?>">
                                <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5>Change Password</h5>
                        <p class="text-muted small">Leave blank if you don't want to change your password</p>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>" 
                                       id="current_password" name="current_password">
                                <?php if (isset($errors['current_password'])): ?>
                                <div class="invalid-feedback"><?= $errors['current_password'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" 
                                       id="new_password" name="new_password">
                                <?php if (isset($errors['new_password'])): ?>
                                <div class="invalid-feedback"><?= $errors['new_password'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control <?= isset($errors['confirm_new_password']) ? 'is-invalid' : '' ?>" 
                                       id="confirm_new_password" name="confirm_new_password">
                                <?php if (isset($errors['confirm_new_password'])): ?>
                                <div class="invalid-feedback"><?= $errors['confirm_new_password'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password validation
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_new_password');
        const currentPasswordInput = document.getElementById('current_password');
        
        // Form validation
        document.getElementById('updateProfileForm').addEventListener('submit', function(e) {
            // Check if new password is provided but not current password
            if (newPasswordInput.value && !currentPasswordInput.value) {
                e.preventDefault();
                alert('Please enter your current password to change your password');
                currentPasswordInput.focus();
                return false;
            }
            
            // Check if passwords match
            if (newPasswordInput.value && newPasswordInput.value !== confirmPasswordInput.value) {
                e.preventDefault();
                alert('New passwords do not match');
                confirmPasswordInput.focus();
                return false;
            }
            
            return true;
        });
    });
</script>