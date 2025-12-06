<?php
/**
 * Lab Results Index
 * List all lab results for current user
 */

$pageTitle = 'Lab Results - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-vial fa-fw"></i> Lab Results
        </h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">My Lab Test Results</h6>
        </div>
        <div class="card-body">
            <?php if (empty($labResults)): ?>
                <div class="text-center p-5">
                    <i class="fas fa-vial fa-3x text-muted mb-3"></i>
                    <p class="text-muted">You have no lab test results yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Test Name</th>
                                <th>Date</th>
                                <th>Result</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($labResults as $result): ?>
                                <tr>
                                    <td><?= htmlspecialchars($result['test_name'] ?? 'N/A') ?></td>
                                    <td><?= !empty($result['test_date']) ? date('M d, Y', strtotime($result['test_date'])) : 'N/A' ?></td>
                                    <td><?= !empty($result['result_value']) ? htmlspecialchars($result['result_value']) : ($result['result_status'] === 'completed' ? 'Completed' : 'Pending') ?></td>
                                    <td>
                                        <?php
                                        $status = $result['result_status'] ?? 'pending';
                                $statusClass = $status === 'completed' ? 'badge bg-success' : ($status === 'pending' ? 'badge bg-warning text-dark' : 'badge bg-info');
                                ?>
                                        <span class="<?= $statusClass ?>"><?= ucfirst($status) ?></span>
                                    </td>
                                    <td>
                                        <a href="<?= $baseUrl ?>/lab-results/view/<?= $result['result_id'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <?php if ($status === 'completed'): ?>
                                        <a href="<?= $baseUrl ?>/lab-results/download/<?= $result['result_id'] ?>" class="btn btn-sm btn-primary" data-no-ajax>
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

