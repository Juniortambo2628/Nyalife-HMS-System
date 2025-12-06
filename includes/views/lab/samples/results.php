<?php
/**
 * Nyalife HMS - Lab Test Results View
 */

$pageTitle = 'Lab Test Results - Nyalife HMS';
?>

<div class="container-fluid">
 <div class="row mb-4">
        <div class="col-md-6">
            <h1>Test Results</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= $baseUrl ?? '' ?>/lab/samples/view/<?= htmlspecialchars($sample['sample_id'] ?? '') ?>" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left"></i> Back to Sample
            </a>
            <a href="<?= $baseUrl ?? '' ?>/lab-tests/manage" class="btn btn-outline-secondary">
                <i class="fas fa-list"></i> Manage Samples
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Test Results for <?= htmlspecialchars($sample['sample_id'] ?? '') ?></h5>
                </div>
                <div class="card-body">
                    <?php if (empty($results)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-flask fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Results Available</h5>
                            <p class="text-muted">Test results have not been entered yet.</p>
                            <?php if (($sample['status'] ?? '') !== 'completed'): ?>
                                <a href="<?= $baseUrl ?? '' ?>/lab-tests/update-result/<?= htmlspecialchars($sample['id'] ?? '') ?>" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Enter Results
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Result</th>
                                        <th>Unit</th>
                                        <th>Reference Range</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $result): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($result['parameter_name'] ?? '') ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($result['result_value'] ?? '') ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($result['unit'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($result['reference_range'] ?? '') ?></td>
                                        <td>
                                            <?php
                                            $isAbnormal = ($result['is_abnormal'] ?? 0) == 1;
                                        $statusClass = $isAbnormal ? 'bg-danger' : 'bg-success';
                                        $statusText = $isAbnormal ? 'Abnormal' : 'Normal';
                                        ?>
                                            <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if (($sample['status'] ?? '') !== 'completed'): ?>
                        <div class="mt-3">
                            <a href="<?= $baseUrl ?? '' ?>/lab-tests/update-result/<?= htmlspecialchars($sample['id'] ?? '') ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Update Results
                            </a>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Sample Information</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Sample ID:</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($sample['sample_id'] ?? '') ?></dd>
                        
                        <dt class="col-sm-4">Test Type:</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($testType['test_name'] ?? '') ?></dd>
                        
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
                        
                        <dt class="col-sm-4">Collection Date:</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars(date('M d, Y', strtotime($sample['collected_date'] ?? ''))) ?></dd>
                        
                        <?php if (!empty($sample['completed_at'])): ?>
                        <dt class="col-sm-4">Completed:</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars(date('M d, Y H:i', strtotime($sample['completed_at']))) ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
            
            <?php if (!empty($sample['notes'])): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Notes</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?= htmlspecialchars($sample['notes']) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>