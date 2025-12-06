<?php
/**
 * Nyalife HMS - Reset Password View
 */

$pageTitle = 'Reset Password - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-lock me-2"></i>Reset Password
                    </h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Enter your new password below.
                    </p>
                    
                    <form method="post" action="<?= $baseUrl ?>/reset-password/<?= $token ?>" id="reset-password-form">
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Password must be at least 6 characters long</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?= $baseUrl ?>/login" class="btn btn-outline-secondary me-md-2">Back to Login</a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Reset Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reset-password-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Basic client-side validation
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return;
            }
        });
    }
});
</script> 