<?php
/**
 * Nyalife HMS - Change Password View
 */

$pageTitle = 'Change Password - Nyalife HMS';
?>
<div class="container-fluid py-5 px-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Change Password</h1>
        <a href="<?= $baseUrl ?>/profile" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Change Your Password</h6>
                </div>
                <div class="card-body">
                    <form action="<?= $baseUrl ?>/profile/change-password" method="post" id="changePasswordForm">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <div class="form-text">Enter your current password to verify your identity</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <div class="form-text">
                                Password must be at least 8 characters long and contain:
                                <ul class="mt-1 mb-0">
                                    <li>At least one uppercase letter</li>
                                    <li>At least one lowercase letter</li>
                                    <li>At least one number</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
                            <div class="form-text">Re-enter your new password to confirm</div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?= $baseUrl ?>/profile" class="btn btn-outline-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('changePasswordForm');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_new_password');
    
    // Real-time password confirmation validation
    function validatePasswordMatch() {
        if (newPassword.value && confirmPassword.value) {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
    }
    
    newPassword.addEventListener('input', validatePasswordMatch);
    confirmPassword.addEventListener('input', validatePasswordMatch);
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        const password = newPassword.value;
        
        // Check password requirements
        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long');
            return;
        }
        
        if (!/[A-Z]/.test(password)) {
            e.preventDefault();
            alert('Password must contain at least one uppercase letter');
            return;
        }
        
        if (!/[a-z]/.test(password)) {
            e.preventDefault();
            alert('Password must contain at least one lowercase letter');
            return;
        }
        
        if (!/[0-9]/.test(password)) {
            e.preventDefault();
            alert('Password must contain at least one number');
            return;
        }
        
        if (newPassword.value !== confirmPassword.value) {
            e.preventDefault();
            alert('Passwords do not match');
            return;
        }
    });
});
</script> 