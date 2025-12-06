<?php
/**
 * Nyalife HMS - Pending Prescriptions View
 */

$pageTitle = 'Pending Prescriptions - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="row mb-2 mt-2">
        <div class="col-md-6">
            <h1>Pending Prescriptions</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= $baseUrl ?? '' ?>/prescriptions" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to All Prescriptions
            </a>
        </div>
    </div>

    <div class="card mb-2 mt-2">
        <div class="card-header">
            <h5 class="mb-0">Pending Prescriptions</h5>
        </div>
        <div class="card-body">
            <?php if (isset($prescriptions) && !empty($prescriptions)): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Prescription ID</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prescriptions as $prescription): ?>
                                <tr>
                                    <td>#<?= htmlspecialchars($prescription['prescription_id'] ?? '') ?></td>
                                    <td><?= htmlspecialchars(($prescription['patient_name'] ?? $prescription['first_name'] ?? '') . ' ' . ($prescription['last_name'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($prescription['doctor_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($prescription['prescription_date'] ?? date('Y-m-d')) ?></td>
                                    <td>
                                        <span class="badge bg-warning">Pending</span>
                                    </td>
                                    <td>
                                        <a href="<?= $baseUrl ?? '' ?>/prescriptions/view/<?= $prescription['prescription_id'] ?? '' ?>" class="btn btn-sm btn-info">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle me-2"></i>No pending prescriptions found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

