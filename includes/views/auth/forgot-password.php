<?php
/**
 * Nyalife HMS - Forgot Password View
 */

$pageTitle = 'Forgot Password - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-key me-2"></i>Forgot Password
                    </h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Enter your email address and we'll send you a link to reset your password.
                    </p>
                    
                    <form method="post" action="<?= $baseUrl ?>/forgot-password" id="forgot-password-form">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?= $baseUrl ?>/login" class="btn btn-outline-secondary me-md-2">Back to Login</a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 