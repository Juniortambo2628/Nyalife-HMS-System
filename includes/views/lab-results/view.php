<?php
/**
 * Lab Results View
 * Display detailed lab test result
 */

$pageTitle = 'Lab Test Result - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-vial fa-fw"></i> Lab Test Result
        </h1>
        <div>
            <a href="<?= $baseUrl ?>/lab-results" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left fa-fw"></i> Back to Results
            </a>
            <?php if (isset($print) && $print): ?>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="fas fa-print fa-fw"></i> Print
            </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($labResult)): ?>
        <div class="alert alert-danger">
            Lab test result not found.
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <?= htmlspecialchars($labResult['test_name'] ?? 'Lab Test Result') ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Test Name:</strong><br>
                                <?= htmlspecialchars($labResult['test_name'] ?? 'N/A') ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong><br>
                                <?php
                                $status = $labResult['status'] ?? 'pending';
        $statusClass = $status === 'completed' ? 'badge bg-success' : ($status === 'pending' ? 'badge bg-warning' : 'badge bg-info');
        ?>
                                <span class="<?= $statusClass ?>"><?= ucfirst($status) ?></span>
                            </div>
                        </div>

                        <?php if (!empty($labResult['test_description'])): ?>
                        <div class="mb-3">
                            <strong>Description:</strong><br>
                            <?= htmlspecialchars($labResult['test_description']) ?>
                        </div>
                        <?php endif; ?>

                        <?php
                        $resultValue = $labResult['result_value'] ?? $labResult['result'] ?? null;
if (!empty($resultValue)): ?>
                        <div class="mb-3">
                            <strong>Result:</strong><br>
                            <div class="alert alert-info">
                                <?= htmlspecialchars($resultValue) ?>
                                <?php if (!empty($labResult['units'])): ?>
                                    <span class="text-muted">(<?= htmlspecialchars($labResult['units']) ?>)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($labResult['normal_range'])): ?>
                        <div class="mb-3">
                            <strong>Normal Range:</strong><br>
                            <?= htmlspecialchars($labResult['normal_range']) ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($labResult['result_interpretation'])): ?>
                        <div class="mb-3">
                            <strong>Interpretation:</strong><br>
                            <span class="badge bg-<?= $labResult['result_interpretation'] === 'normal' ? 'success' : 'warning' ?>">
                                <?= ucfirst($labResult['result_interpretation']) ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($labResult['notes'])): ?>
                        <div class="mb-3">
                            <strong>Notes:</strong><br>
                            <?= nl2br(htmlspecialchars($labResult['notes'])) ?>
                        </div>
                        <?php endif; ?>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <strong>Request Date:</strong><br>
                                <?= !empty($labResult['request_date']) ? date('M d, Y', strtotime($labResult['request_date'])) : 'N/A' ?>
                            </div>
                            <?php if (!empty($labResult['completed_at'])): ?>
                            <div class="col-md-6">
                                <strong>Completed At:</strong><br>
                                <?= date('M d, Y H:i', strtotime($labResult['completed_at'])) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Patient Information</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($patient)): ?>
                            <p><strong>Name:</strong><br>
                            <?= htmlspecialchars(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')) ?></p>
                            <?php if (!empty($patient['email'])): ?>
                            <p><strong>Email:</strong><br><?= htmlspecialchars($patient['email']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($patient['phone'])): ?>
                            <p><strong>Phone:</strong><br><?= htmlspecialchars($patient['phone']) ?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted">Patient information not available</p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($allResults) && count($allResults) > 1): ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Other Tests in This Request</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <?php foreach ($allResults as $result): ?>
                                <?php if ($result['test_item_id'] != $labResult['test_item_id']): ?>
                                <li class="mb-2">
                                    <a href="<?= $baseUrl ?>/lab-results/view/<?= $result['test_item_id'] ?>">
                                        <?= htmlspecialchars($result['test_name'] ?? 'Test') ?>
                                    </a>
                                    <span class="badge bg-<?= $result['status'] === 'completed' ? 'success' : 'warning' ?> ms-2">
                                        <?= ucfirst($result['status']) ?>
                                    </span>
                                </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

