<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Consultation Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/consultations">Consultations</a></li>
                    <li class="breadcrumb-item active" aria-current="page">View</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group">
                <a href="<?= $baseUrl ?>/consultations/print/<?= $consultation['consultation_id'] ?>" 
                   class="btn btn-outline-secondary me-2" target="_blank">
                    <i class="fas fa-print"></i> Print
                </a>
                <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                    <a href="<?= $baseUrl ?>/consultations/edit/<?= $consultation['consultation_id'] ?>" 
                       class="btn btn-outline-primary me-2">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                <?php endif; ?>
                <a href="<?= $baseUrl ?>/consultations" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Consultation Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Consultation Date</h6>
                            <p><?= date('F j, Y g:i A', strtotime($consultation['consultation_date'])) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Status</h6>
                            <span class="badge bg-<?= 
                                $consultation['status'] === 'completed' ? 'success' : 
                                ($consultation['status'] === 'in_progress' ? 'info' : 
                                ($consultation['status'] === 'cancelled' ? 'danger' : 'primary')) ?>">
                                <?= ucwords(str_replace('_', ' ', $consultation['status'])) ?>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted">Diagnosis</h6>
                        <div class="border rounded p-3 bg-light">
                            <?= !empty($consultation['diagnosis']) ? nl2br(htmlspecialchars($consultation['diagnosis'])) : 
                                '<span class="text-muted">No diagnosis recorded</span>' ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted">Treatment Plan</h6>
                        <div class="border rounded p-3 bg-light">
                            <?= !empty($consultation['treatment_plan']) ? nl2br(htmlspecialchars($consultation['treatment_plan'])) : 
                                '<span class="text-muted">No treatment plan recorded</span>' ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted">Notes</h6>
                        <div class="border rounded p-3 bg-light">
                            <?= !empty($consultation['notes']) ? nl2br(htmlspecialchars($consultation['notes'])) : 
                                '<span class="text-muted">No additional notes</span>' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Patient Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar avatar-xl bg-primary bg-opacity-10 rounded-circle mb-2">
                            <i class="fas fa-user fa-2x text-primary"></i>
                        </div>
                        <h5><?= htmlspecialchars($consultation['patient_first_name'] . ' ' . $consultation['patient_last_name']) ?></h5>
                        <p class="text-muted">Patient</p>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <h6 class="text-muted mb-1">Date of Birth</h6>
                        <p><?= !empty($patient['date_of_birth']) ? date('F j, Y', strtotime($patient['date_of_birth'])) : 'N/A' ?></p>
                    </div>
                    <div class="mb-2">
                        <h6 class="text-muted mb-1">Gender</h6>
                        <p><?= !empty($patient['gender']) ? ucfirst($patient['gender']) : 'N/A' ?></p>
                    </div>
                    <div class="mb-2">
                        <h6 class="text-muted mb-1">Phone</h6>
                        <p><?= !empty($patient['phone']) ? htmlspecialchars($patient['phone']) : 'N/A' ?></p>
                    </div>
                    <div class="mb-2">
                        <h6 class="text-muted mb-1">Email</h6>
                        <p><?= !empty($patient['email']) ? htmlspecialchars($patient['email']) : 'N/A' ?></p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Doctor Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar avatar-xl bg-info bg-opacity-10 rounded-circle mb-2">
                            <i class="fas fa-user-md fa-2x text-info"></i>
                        </div>
                        <h5>Dr. <?= htmlspecialchars($consultation['doctor_first_name'] . ' ' . $consultation['doctor_last_name']) ?></h5>
                        <p class="text-muted">Doctor</p>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <h6 class="text-muted mb-1">Specialization</h6>
                        <p><?= !empty($consultation['specialization']) ? htmlspecialchars($consultation['specialization']) : 'N/A' ?></p>
                    </div>
                    <div class="mb-2">
                        <h6 class="text-muted mb-1">Department</h6>
                        <p><?= !empty($consultation['department']) ? htmlspecialchars($consultation['department']) : 'N/A' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for confirming actions -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to <span id="actionText"></span> this consultation?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAction">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle action buttons
    const actionButtons = document.querySelectorAll('.action-btn');
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    const actionText = document.getElementById('actionText');
    const confirmAction = document.getElementById('confirmAction');
    
    let currentAction = '';
    let currentUrl = '';
    
    actionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            currentAction = this.dataset.action;
            currentUrl = this.href;
            
            // Set the action text in the modal
            actionText.textContent = this.dataset.action;
            
            // Show the modal
            confirmModal.show();
        });
    });
    
    // Handle confirm button in modal
    confirmAction.addEventListener('click', function() {
        if (currentAction && currentUrl) {
            window.location.href = currentUrl;
        }
    });
});
</script>
