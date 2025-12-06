<?php
/**
 * Nyalife HMS - Medicine Details View
 *
 * View for displaying detailed medication information.
 */

$pageTitle = 'Medicine Details - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-pills fa-fw"></i> Medicine Details
        </h1>
        <div>
            <a href="<?= $baseUrl ?>/pharmacy/medicines/edit/<?= $medication['medication_id'] ?>" 
               class="btn btn-warning btn-sm">
                <i class="fas fa-edit fa-fw"></i> Edit Medicine
            </a>
            <a href="<?= $baseUrl ?>/pharmacy/medicines" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left fa-fw"></i> Back to Medicines
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php include __DIR__ . '/../../components/flash_messages.php'; ?>

    <!-- Medicine Details -->
    <div class="row">
        <!-- Medicine Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Medicine Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Medicine Name:</strong></td>
                                    <td><?= htmlspecialchars($medication['medication_name']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Generic Name:</strong></td>
                                    <td><?= htmlspecialchars($medication['generic_name'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td>
                                        <?php if (!empty($medication['medication_type'])): ?>
                                            <span class="badge badge-info"><?= htmlspecialchars($medication['medication_type']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Form:</strong></td>
                                    <td><?= htmlspecialchars($medication['form']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Strength:</strong></td>
                                    <td><?= htmlspecialchars($medication['strength']) ?> <?= htmlspecialchars($medication['unit']) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Manufacturer:</strong></td>
                                    <td><?= htmlspecialchars($medication['manufacturer'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Price:</strong></td>
                                    <td><?= number_format($medication['price'], 2) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Stock Quantity:</strong></td>
                                    <td>
                                        <?php
                                        $stockQuantity = $medication['stock_quantity'] ?? 0;
$stockClass = $stockQuantity <= 0 ? 'text-danger' : ($stockQuantity <= 10 ? 'text-warning' : 'text-success');
?>
                                        <span class="<?= $stockClass ?>">
                                            <strong><?= number_format($stockQuantity) ?></strong>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <?php if ($medication['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td><?= date('F j, Y', strtotime($medication['created_at'])) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <?php if (!empty($medication['description'])): ?>
                        <div class="mt-3">
                            <h6><strong>Description:</strong></h6>
                            <p><?= nl2br(htmlspecialchars($medication['description'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Medical Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Medical Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if (!empty($medication['side_effects'])): ?>
                            <div class="col-md-6">
                                <h6><strong>Side Effects:</strong></h6>
                                <p><?= nl2br(htmlspecialchars($medication['side_effects'])) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($medication['contraindications'])): ?>
                            <div class="col-md-6">
                                <h6><strong>Contraindications:</strong></h6>
                                <p><?= nl2br(htmlspecialchars($medication['contraindications'])) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Information -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Stock Information</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($stock)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Batch</th>
                                        <th>Quantity</th>
                                        <th>Expiry</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stock as $batch): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($batch['batch_number']) ?></td>
                                            <td><?= number_format($batch['quantity']) ?></td>
                                            <td><?= date('M j, Y', strtotime($batch['expiry_date'])) ?></td>
                                            <td>
                                                <?php
        $expiryDate = new DateTime($batch['expiry_date']);
                                        $today = new DateTime();
                                        $daysUntilExpiry = $today->diff($expiryDate)->days;

                                        if ($expiryDate < $today): ?>
                                                    <span class="badge badge-danger">Expired</span>
                                                <?php elseif ($daysUntilExpiry <= 30): ?>
                                                    <span class="badge badge-warning">Expires Soon</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">Good</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No stock information available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= $baseUrl ?>/pharmacy/medicines/edit/<?= $medication['medication_id'] ?>" 
                           class="btn btn-warning btn-sm">
                            <i class="fas fa-edit fa-fw"></i> Edit Medicine
                        </a>
                        
                        <button type="button" class="btn btn-<?= $medication['is_active'] ? 'secondary' : 'success' ?> btn-sm"
                                onclick="toggleStatus(<?= $medication['medication_id'] ?>, <?= $medication['is_active'] ? 'false' : 'true' ?>)">
                            <i class="fas fa-<?= $medication['is_active'] ? 'times' : 'check' ?> fa-fw"></i>
                            <?= $medication['is_active'] ? 'Deactivate' : 'Activate' ?>
                        </button>
                        
                        <a href="<?= $baseUrl ?>/pharmacy/inventory/add-stock?medication_id=<?= $medication['medication_id'] ?>" 
                           class="btn btn-info btn-sm">
                            <i class="fas fa-plus fa-fw"></i> Add Stock
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleStatus(medicationId, isActive) {
    const action = isActive ? 'activate' : 'deactivate';
    if (confirm(`Are you sure you want to ${action} this medicine?`)) {
        fetch(`${baseUrl}/pharmacy/medicines/toggle-status/${medicationId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                is_active: isActive
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update medicine status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected error occurred.');
        });
    }
}
</script> 