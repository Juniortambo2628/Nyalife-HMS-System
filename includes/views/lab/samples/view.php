<?php
/**
 * Nyalife HMS - Lab Sample Details View
 */

$pageTitle = 'Lab Sample Details - Nyalife HMS';
?>

<div class="container-fluid">
 <div class="row mb-4">
        <div class="col-md-6">
            <h1>Sample Details</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= $baseUrl ?? '' ?>/lab-tests/manage" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left"></i> Back to Manage
            </a>
            <a href="<?= $baseUrl ?? '' ?>/lab/samples/results/<?= htmlspecialchars($sample['sample_id'] ?? '') ?>" class="btn btn-primary">
                <i class="fas fa-flask"></i> View Results
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Sample Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Sample ID:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($sample['sample_id'] ?? '') ?></dd>
                                
                                <dt class="col-sm-4">Test Type:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($testType['test_name'] ?? '') ?></dd>
                                
                                <dt class="col-sm-4">Sample Type:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars(ucfirst($sample['sample_type'] ?? '')) ?></dd>
                                
                                <dt class="col-sm-4">Status:</dt>
                                <dd class="col-sm-8">
                                    <?php
                                    $statusClass = '';
$statusText = '';
switch ($sample['status'] ?? '') {
    case 'registered':
        $statusClass = 'bg-info';
        $statusText = 'Registered';
        break;
    case 'in_progress':
        $statusClass = 'bg-warning';
        $statusText = 'In Progress';
        break;
    case 'pending_results':
        $statusClass = 'bg-primary';
        $statusText = 'Pending Results';
        break;
    case 'completed':
        $statusClass = 'bg-success';
        $statusText = 'Completed';
        break;
    case 'cancelled':
        $statusClass = 'bg-danger';
        $statusText = 'Cancelled';
        break;
    default:
        $statusClass = 'bg-secondary';
        $statusText = 'Unknown';
}
?>
                                    <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Collection Date:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars(date('M d, Y', strtotime($sample['collected_date'] ?? ''))) ?></dd>
                                
                                <dt class="col-sm-4">Collected At:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars(date('M d, Y H:i', strtotime($sample['collected_at'] ?? ''))) ?></dd>
                                
                                <dt class="col-sm-4">Urgent:</dt>
                                <dd class="col-sm-8">
                                    <?php if (($sample['urgent'] ?? 0) == 1): ?>
                                        <span class="badge bg-danger">Yes</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No</span>
                                    <?php endif; ?>
                                </dd>
                                
                                <?php if (!empty($sample['completed_at'])): ?>
                                <dt class="col-sm-4">Completed At:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars(date('M d, Y H:i', strtotime($sample['completed_at']))) ?></dd>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>
                    
                    <?php if (!empty($sample['notes'])): ?>
                    <div class="mt-3">
                        <h6>Notes:</h6>
                        <p class="text-muted"><?= htmlspecialchars($sample['notes']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Patient Information</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Name:</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($patient['first_name'] ?? '') ?> <?= htmlspecialchars($patient['last_name'] ?? '') ?></dd>
                        
                        <dt class="col-sm-4">Patient #:</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($patient['patient_number'] ?? '') ?></dd>
                        
                        <dt class="col-sm-4">Age:</dt>
                        <dd class="col-sm-8">
                            <?php
                            if (!empty($patient['date_of_birth'])) {
                                $dob = new DateTime($patient['date_of_birth']);
                                $now = new DateTime();
                                $age = $now->diff($dob)->y;
                                echo $age . ' years';
                            } else {
                                echo 'Not specified';
                            }
?>
                        </dd>
                        
                        <dt class="col-sm-4">Gender:</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars(ucfirst($patient['gender'] ?? '')) ?></dd>
                    </dl>
                    
                    <div class="mt-3">
                        <a href="<?= $baseUrl ?? '' ?>/patients/view/<?= htmlspecialchars($patient['patient_id'] ?? '') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user"></i> View Patient Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>