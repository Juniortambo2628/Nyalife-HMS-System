<?php
/**
 * Nyalife HMS - Pharmacist Dashboard
 */

$pageTitle = 'Pharmacist Dashboard - Nyalife HMS';
?>
<div class="container-fluid page-wrapper">
    <h1 class="h3 mb-4">Pharmacist Dashboard</h1>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Pending Prescriptions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($pendingPrescriptions) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-prescription fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Dispensed Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($completedPrescriptions) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Low Stock Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Medications</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pills fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Prescriptions -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Pending Prescriptions</h6>
                    <a href="<?= $baseUrl ?>/prescriptions/pending" class="btn btn-sm btn-primary">
                        <i class="fas fa-list me-1"></i> View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($pendingPrescriptions)): ?>
                        <div class="text-center p-4">
                            <img src="<?= $baseUrl ?>/assets/img/illustrations/no-appointments.svg" alt="No prescriptions" class="img-fluid mb-3 img-max-150 img-error-handler" data-error-icon="fas fa-prescription-bottle">
                            <p class="text-muted">No prescriptions are currently waiting for dispensation.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="pendingPrescriptionsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Patient</th>
                                        <th>Prescribed By</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingPrescriptions as $prescription): ?>
                                        <tr>
                                            <td><?= $prescription['prescription_id'] ?? 'N/A' ?></td>
                                            <td>
                                                <a href="<?= $baseUrl ?>/patients/view/<?= $prescription['patient_id'] ?? 0 ?>">
                                                    <?= htmlspecialchars($prescription['patient_name'] ?? 'Unknown Patient') ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($prescription['doctor_name'] ?? 'Unknown') ?></td>
                                            <td><?= isset($prescription['prescription_date']) ? date('M d, Y', strtotime($prescription['prescription_date'])) : 'N/A' ?></td>
                                            <td><?= $prescription['item_count'] ?? 0 ?></td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                        switch ($prescription['status'] ?? '') {
                                            case 'pending':
                                                $statusClass = 'bg-warning text-dark';
                                                break;
                                            case 'partially_dispensed':
                                                $statusClass = 'bg-info';
                                                break;
                                            case 'dispensed':
                                                $statusClass = 'bg-success';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'bg-danger';
                                                break;
                                            default:
                                                $statusClass = 'bg-secondary';
                                                break;
                                        }
                                        ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $prescription['status'] ?? 'pending')) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= $baseUrl ?>/prescriptions/view/<?= $prescription['prescription_id'] ?? 0 ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= $baseUrl ?>/prescriptions/dispense/<?= $prescription['prescription_id'] ?? 0 ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-prescription-bottle-alt"></i>
                                                </a>
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
        
        <!-- Quick Actions & Info -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= $baseUrl ?>/prescriptions/pending" class="btn btn-primary">
                            <i class="fas fa-prescription me-2"></i> View Pending Prescriptions
                        </a>
                        <a href="<?= $baseUrl ?>/medications/inventory" class="btn btn-info">
                            <i class="fas fa-pills me-2"></i> Manage Inventory
                        </a>
                        <a href="<?= $baseUrl ?>/medications/low-stock" class="btn btn-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> View Low Stock
                        </a>
                        <a href="<?= $baseUrl ?>/medications/add" class="btn btn-success">
                            <i class="fas fa-plus-circle me-2"></i> Add New Medication
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">My Information</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="profile-image-container">
                            <img src="<?= $baseUrl ?>/assets/img/profiles/default-pharmacist.png" class="img-profile" onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'fas fa-prescription-bottle\'></i>';">
                        </div>
                        <h5 class="mt-2"><?= htmlspecialchars($currentUser['firstName'] . ' ' . $currentUser['lastName']) ?></h5>
                        <p class="text-muted">
                            <i class="fas fa-prescription-bottle me-1"></i> Pharmacist
                        </p>
                    </div>
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-envelope me-2"></i> Email</span>
                            <span class="text-muted"><?= htmlspecialchars($currentUser['email'] ?? '-') ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-phone me-2"></i> Phone</span>
                            <span class="text-muted"><?= htmlspecialchars($currentUser['phone'] ?? '-') ?></span>
                        </li>
                    </ul>
                    
                    <div class="mt-3 text-center">
                        <a href="<?= $baseUrl ?>/profile" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user-edit me-1"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Dispensed Prescriptions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recently Dispensed Prescriptions</h6>
                    <a href="<?= $baseUrl ?>/prescriptions/completed" class="btn btn-sm btn-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($completedPrescriptions)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No recently dispensed prescriptions found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="completedPrescriptionsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Patient</th>
                                        <th>Prescribed By</th>
                                        <th>Dispensed Date</th>
                                        <th>Dispensed By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($completedPrescriptions as $prescription): ?>
                                        <tr>
                                            <td><?= $prescription['prescription_id'] ?? 'N/A' ?></td>
                                            <td>
                                                <a href="<?= $baseUrl ?>/patients/view/<?= $prescription['patient_id'] ?? 0 ?>">
                                                    <?= htmlspecialchars($prescription['patient_name'] ?? 'Unknown Patient') ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($prescription['doctor_name'] ?? 'Unknown') ?></td>
                                            <td><?= isset($prescription['dispensed_date']) ? date('M d, Y', strtotime($prescription['dispensed_date'])) : 'N/A' ?></td>
                                            <td><?= htmlspecialchars($prescription['dispensed_by_name'] ?? 'Unknown') ?></td>
                                            <td>
                                                <a href="<?= $baseUrl ?>/prescriptions/view/<?= $prescription['prescription_id'] ?? 0 ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= $baseUrl ?>/prescriptions/print/<?= $prescription['prescription_id'] ?? 0 ?>" class="btn btn-sm btn-secondary" data-no-ajax>
                                                    <i class="fas fa-print"></i>
                                                </a>
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
    </div>
</div>

    <!-- Bundled Assets -->
    <link rel="stylesheet" href="<?= AssetHelper::getCss('shared') ?>">
    <script src="<?= AssetHelper::getJs('runtime') ?>"></script>
    <script src="<?= AssetHelper::getJs('vendors') ?>"></script>
    <script src="<?= AssetHelper::getJs('shared') ?>"></script>
    <script src="<?= AssetHelper::getJs('app') ?>"></script>
    <script src="<?= AssetHelper::getJs('dashboard-pharmacist') ?>"></script>
</body>
</html>