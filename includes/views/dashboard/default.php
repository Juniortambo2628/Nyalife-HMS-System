<div class="container-fluid px-4 py-5">
    <h1 class="h3 mb-4">Dashboard</h1>
    
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <img src="<?= $baseUrl ?>/assets/img/illustrations/welcome.png" alt="Welcome" class="img-fluid mb-4" style="max-width: 250px;">
                    <h2>Welcome to Nyalife Hospital Management System!</h2>
                    <p class="lead text-muted">
                        You're logged in as <strong><?= htmlspecialchars($currentUser['firstName'] . ' ' . $currentUser['lastName']) ?></strong>.
                    </p>
                    <p>
                        Please contact the system administrator to set up your proper role and permissions.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Links</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= $baseUrl ?>/profile" class="btn btn-primary">
                            <i class="fas fa-user-edit me-2"></i> Edit Your Profile
                        </a>
                        <a href="<?= $baseUrl ?>/help" class="btn btn-info">
                            <i class="fas fa-question-circle me-2"></i> Help & Documentation
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="<?= $baseUrl ?>/assets/img/logo.png" alt="Nyalife HMS Logo" class="img-fluid mb-3" style="max-width: 150px;">
                        <h5>Nyalife Hospital Management System</h5>
                        <p class="text-muted small">Version 1.0.0</p>
                    </div>
                    
                    <div class="text-center mt-4">
                        <p class="small text-muted mb-0">
                            &copy; <?= date('Y') ?> Nyalife Hospital. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
