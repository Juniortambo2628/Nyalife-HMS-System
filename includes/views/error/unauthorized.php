<?php
// The layout is now defined in the ErrorController class
?>

<!-- Duplicate wrapper removed -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center p-5">
                    <div class="display-1 text-danger mb-3">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h1 class="h2 mb-3">Access Denied</h1>
                    <p class="lead mb-4">You do not have permission to access this page.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="<?= $baseUrl ?>/dashboard" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i> Go to Dashboard
                        </a>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Go Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- end duplicate wrapper removed -->