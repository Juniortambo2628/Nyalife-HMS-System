<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">My Profile</h1>
        <a href="<?= $baseUrl ?>/dashboard" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="row">
        <!-- Profile Information -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Profile Information</h6>
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
                                       id="phone" name="phone" value="<?= $formData['phone'] ?? $user['phone'] ?>" required>
                                <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="role" class="form-label">Role</label>
                                <input type="text" class="form-control" id="role" value="<?= htmlspecialchars($user['role_name']) ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Change Password -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Change Password</h6>
                </div>
                <div class="card-body">
                    <form action="<?= $baseUrl ?>/profile/update" method="post" id="changePasswordForm">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>" 
                                   id="current_password" name="current_password">
                            <?php if (isset($errors['current_password'])): ?>
                            <div class="invalid-feedback"><?= $errors['current_password'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                   id="new_password" name="new_password">
                            <div class="form-text">Password must be at least 8 characters and include uppercase, lowercase, and numbers.</div>
                            <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= $errors['password'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                   id="confirm_new_password" name="confirm_new_password">
                            <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card shadow-sm mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Account Security</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Last Login</label>
                        <p class="mb-0"><?= isset($user['last_login']) ? date('M d, Y, h:i A', strtotime($user['last_login'])) : 'N/A' ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Account Created</label>
                        <p class="mb-0"><?= date('M d, Y', strtotime($user['created_at'])) ?></p>
                    </div>
                    
                    <div class="mb-0">
                        <label class="form-label">Account Status</label>
                        <p class="mb-0">
                            <span class="badge <?= $user['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                                <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password change validation
    const changePasswordForm = document.getElementById('changePasswordForm');
    changePasswordForm.addEventListener('submit', function(e) {
        const currentPassword = document.getElementById('current_password').value;
        const newPassword = document.getElementById('new_password').value;
        const confirmNewPassword = document.getElementById('confirm_new_password').value;
        
        // If the form is being submitted with password fields (at least the current password is filled)
        if (currentPassword) {
            // Validate that new password is provided
            if (!newPassword) {
                e.preventDefault();
                alert('Please enter a new password');
                return;
            }
            
            // Validate that passwords match
            if (newPassword !== confirmNewPassword) {
                e.preventDefault();
                alert('New passwords do not match');
                return;
            }
            
            // Validate password strength (at least 8 characters with uppercase, lowercase, and numbers)
            if (newPassword.length < 8 || 
                !/[A-Z]/.test(newPassword) || 
                !/[a-z]/.test(newPassword) || 
                !/[0-9]/.test(newPassword)) {
                e.preventDefault();
                alert('Password must be at least 8 characters and include uppercase, lowercase, and numbers');
                return;
            }
        } else if (newPassword || confirmNewPassword) {
            // If new password or confirm password is filled but not current password
            e.preventDefault();
            alert('Please enter your current password');
            return;
        }
        
        // If no password fields are filled, remove password fields from submission
        // so the profile update doesn't attempt to change the password
        if (!currentPassword && !newPassword && !confirmNewPassword) {
            document.getElementById('current_password').disabled = true;
            document.getElementById('new_password').disabled = true;
            document.getElementById('confirm_new_password').disabled = true;
        }
    });
});
</script>
