<?php
/**
 * Nyalife HMS - Lab Test Details View
 */

$pageTitle = 'Lab Test Details - Nyalife HMS';
?>
<div class="container-fluid">
 <div class="row mb-4">
        <div class="col-md-6">
            <h1>Test Type Details</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= $baseUrl ?? '' ?>/lab/tests" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <a href="<?= $baseUrl ?? '' ?>/lab/tests/edit/<?= htmlspecialchars($test['test_type_id'] ?? '') ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Test Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Test Name:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($test['test_name'] ?? '') ?></dd>
                                
                                <dt class="col-sm-4">Category:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($test['category'] ?? 'General') ?></dd>
                                
                                <dt class="col-sm-4">Price:</dt>
                                <dd class="col-sm-8">$<?= htmlspecialchars(number_format($test['price'] ?? 0, 2)) ?></dd>
                                
                                <dt class="col-sm-4">Status:</dt>
                                <dd class="col-sm-8">
                                    <?php if (($test['is_active'] ?? 1) == 1): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Normal Range:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($test['normal_range'] ?? 'Not specified') ?></dd>
                                
                                <dt class="col-sm-4">Units:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($test['units'] ?? 'Not specified') ?></dd>
                                
                                <dt class="col-sm-4">Created:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars(date('M d, Y', strtotime($test['created_at'] ?? 'now'))) ?></dd>
                                
                                <dt class="col-sm-4">Updated:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars(date('M d, Y', strtotime($test['updated_at'] ?? 'now'))) ?></dd>
                            </dl>
                        </div>
                    </div>
                    
                    <?php if (!empty($test['description'])): ?>
                    <div class="mt-3">
                        <h6>Description:</h6>
                        <p class="text-muted"><?= htmlspecialchars($test['description']) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($test['instructions_file'])): ?>
                    <div class="mt-3">
                        <h6>Instructions File:</h6>
                        <a href="<?= $baseUrl ?? '' ?>/uploads/lab_tests/<?= htmlspecialchars($test['instructions_file']) ?>" 
                           class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="fas fa-download"></i> Download Instructions
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= $baseUrl ?? '' ?>/lab-tests/register-sample" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Register Sample
                        </a>
                        <a href="<?= $baseUrl ?? '' ?>/lab-tests/manage" class="btn btn-outline-primary">
                            <i class="fas fa-flask"></i> Manage Samples
                        </a>
                        <a href="<?= $baseUrl ?? '' ?>/lab-tests/completed" class="btn btn-outline-success">
                            <i class="fas fa-check-circle"></i> View Completed
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($parameters)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Test Parameters</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Parameter Name</th>
                                    <th>Unit</th>
                                    <th>Reference Range</th>
                                    <th>Sequence</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($parameters as $parameter): ?>
                                <tr>
                                    <td><?= htmlspecialchars($parameter['parameter_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($parameter['unit'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($parameter['reference_range'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($parameter['sequence'] ?? '1') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>