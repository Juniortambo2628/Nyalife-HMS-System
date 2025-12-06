<?php
/**
 * Nyalife HMS - Follow-up Details View
 *
 * View for displaying follow-up details.
 */

$pageTitle = 'Follow-up Details - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-check fa-fw"></i> Follow-up Details
        </h1>
        <div>
            <a href="<?= $baseUrl ?>/follow-ups/edit/<?= $followUp['follow_up_id'] ?>" class="btn btn-warning btn-sm">
                <i class="fas fa-edit fa-fw"></i> Edit Follow-up
            </a>
            <a href="<?= $baseUrl ?>/follow-ups" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left fa-fw"></i> Back to Follow-ups
            </a>
        </div>
    </div>

    <!-- Follow-up Details -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Follow-up Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Patient:</strong></td>
                                    <td><?= htmlspecialchars($followUp['patient_name']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Doctor:</strong></td>
                                    <td><?= htmlspecialchars($followUp['doctor_name']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Follow-up Date:</strong></td>
                                    <td><?= date('F j, Y', strtotime($followUp['follow_up_date'])) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Follow-up Time:</strong></td>
                                    <td><?= date('g:i A', strtotime($followUp['follow_up_time'] ?? '00:00:00')) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td>
                                        <span class="badge badge-info"><?= htmlspecialchars(ucfirst($followUp['follow_up_type'])) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Priority:</strong></td>
                                    <td>
                                        <span class="badge badge-<?= $followUp['priority'] === 'urgent' ? 'danger' : ($followUp['priority'] === 'high' ? 'warning' : 'info') ?>">
                                            <?= htmlspecialchars(ucfirst($followUp['priority'])) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge badge-<?= $followUp['status'] === 'completed' ? 'success' : ($followUp['status'] === 'cancelled' ? 'danger' : 'warning') ?>">
                                            <?= htmlspecialchars(ucfirst($followUp['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td><?= date('F j, Y g:i A', strtotime($followUp['created_at'])) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <h6><strong>Reason for Follow-up:</strong></h6>
                            <p><?= nl2br(htmlspecialchars($followUp['reason'])) ?></p>
                        </div>
                    </div>

                    <?php if (!empty($followUp['notes'])): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6><strong>Additional Notes:</strong></h6>
                                <p><?= nl2br(htmlspecialchars($followUp['notes'])) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Related Consultation -->
            <?php if (!empty($followUp['consultation_date'])): ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Related Consultation</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Consultation Date:</strong> <?= date('F j, Y', strtotime($followUp['consultation_date'])) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Diagnosis:</strong> <?= htmlspecialchars($followUp['diagnosis']) ?></p>
                            </div>
                        </div>
                        <?php if (!empty($followUp['treatment_plan'])): ?>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <p><strong>Treatment Plan:</strong></p>
                                    <p><?= nl2br(htmlspecialchars($followUp['treatment_plan'])) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Actions Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= $baseUrl ?>/follow-ups/edit/<?= $followUp['follow_up_id'] ?>" class="btn btn-warning">
                            <i class="fas fa-edit fa-fw"></i> Edit Follow-up
                        </a>
                        
                        <?php if ($followUp['status'] !== 'completed'): ?>
                            <button type="button" class="btn btn-success" onclick="updateStatus(<?= $followUp['follow_up_id'] ?>, 'completed')">
                                <i class="fas fa-check fa-fw"></i> Mark Complete
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($followUp['status'] === 'scheduled'): ?>
                            <button type="button" class="btn btn-info" onclick="updateStatus(<?= $followUp['follow_up_id'] ?>, 'in_progress')">
                                <i class="fas fa-play fa-fw"></i> Start Follow-up
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($followUp['status'] !== 'cancelled'): ?>
                            <button type="button" class="btn btn-danger" onclick="updateStatus(<?= $followUp['follow_up_id'] ?>, 'cancelled')">
                                <i class="fas fa-times fa-fw"></i> Cancel Follow-up
                            </button>
                        <?php endif; ?>
                        
                        <a href="<?= $baseUrl ?>/follow-ups" class="btn btn-secondary">
                            <i class="fas fa-arrow-left fa-fw"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Patient Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Patient Information</h6>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?= htmlspecialchars($followUp['patient_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($followUp['patient_email']) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($followUp['patient_phone']) ?></p>
                    <a href="<?= $baseUrl ?>/patients/show/<?= $followUp['patient_id'] ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-user fa-fw"></i> View Patient
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateStatus(followUpId, status) {
    const statusText = status === 'completed' ? 'complete' : 
                      status === 'cancelled' ? 'cancel' : 
                      status === 'in_progress' ? 'start' : status;
    
    if (confirm(`Are you sure you want to ${statusText} this follow-up?`)) {
        fetch('<?= $baseUrl ?>/follow-ups/update-status/' + followUpId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update follow-up status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected error occurred.');
        });
    }
}
</script>