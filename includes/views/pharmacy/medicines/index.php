<?php
/**
 * Nyalife HMS - Pharmacy Medicines Index
 *
 * View for listing and managing medications.
 */

$pageTitle = 'Medicines Management - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-pills fa-fw"></i> Medicines Management
        </h1>
        <a href="<?= $baseUrl ?>/pharmacy/medicines/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus fa-fw"></i> Add Medicine
        </a>
    </div>

    <!-- Flash Messages -->
    <?php include __DIR__ . '/../../components/flash_messages.php'; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Medicines
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($statistics['total_medications']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pills fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Medicines
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($statistics['active_medications']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Low Stock
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($statistics['low_stock']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Out of Stock
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($statistics['out_of_stock']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search & Filter</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= $baseUrl ?>/pharmacy/medicines" class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?= htmlspecialchars($filters['search']) ?>" 
                               placeholder="Search medicines...">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select class="form-control" id="category" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category ?>" <?= $filters['category'] === $category ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="all" <?= $filters['status'] === 'all' ? 'selected' : '' ?>>All</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search fa-fw"></i> Search
                            </button>
                            <a href="<?= $baseUrl ?>/pharmacy/medicines" class="btn btn-secondary">
                                <i class="fas fa-times fa-fw"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Medicines List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Medicines</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" 
                     aria-labelledby="dropdownMenuLink">
                    <div class="dropdown-header">Export Options:</div>
                    <a class="dropdown-item" href="#" onclick="exportToCSV()">
                        <i class="fas fa-file-csv fa-fw"></i> Export to CSV
                    </a>
                    <a class="dropdown-item" href="#" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf fa-fw"></i> Export to PDF
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($medications)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="medicinesTable">
                        <thead>
                            <tr>
                                <th>Medicine Name</th>
                                <th>Generic Name</th>
                                <th>Category</th>
                                <th>Form & Strength</th>
                                <th>Stock</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($medications as $medication): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($medication['medication_name']) ?></strong>
                                        <?php if (!empty($medication['manufacturer'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($medication['manufacturer']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($medication['generic_name'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php if (!empty($medication['medication_type'])): ?>
                                            <span class="badge badge-info"><?= htmlspecialchars($medication['medication_type']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($medication['form']) ?> 
                                        <?= htmlspecialchars($medication['strength']) ?> 
                                        <?= htmlspecialchars($medication['unit']) ?>
                                    </td>
                                    <td>
                                        <?php
                                        $stockQuantity = $medication['stock_quantity'] ?? 0;
                                $stockClass = $stockQuantity <= 0 ? 'text-danger' : ($stockQuantity <= 10 ? 'text-warning' : 'text-success');
                                ?>
                                        <span class="<?= $stockClass ?>">
                                            <strong><?= number_format($stockQuantity) ?></strong>
                                        </span>
                                    </td>
                                    <td><?= number_format($medication['price'], 2) ?></td>
                                    <td>
                                        <?php if ($medication['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= $baseUrl ?>/pharmacy/medicines/show/<?= $medication['medication_id'] ?>"
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye fa-fw"></i>
                                            </a>
                                            <a href="<?= $baseUrl ?>/pharmacy/medicines/edit/<?= $medication['medication_id'] ?>"
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit fa-fw"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-<?= $medication['is_active'] ? 'secondary' : 'success' ?>" 
                                                    onclick="toggleStatus(<?= $medication['medication_id'] ?>, <?= $medication['is_active'] ? 'false' : 'true' ?>)"
                                                    title="<?= $medication['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                                <i class="fas fa-<?= $medication['is_active'] ? 'times' : 'check' ?> fa-fw"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total'] > 1): ?>
                    <nav aria-label="Medicines pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['current'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['current'] - 1 ?>&search=<?= urlencode($filters['search']) ?>&category=<?= urlencode($filters['category']) ?>&status=<?= urlencode($filters['status']) ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $pagination['total']; $i++): ?>
                                <li class="page-item <?= $i == $pagination['current'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($filters['search']) ?>&category=<?= urlencode($filters['category']) ?>&status=<?= urlencode($filters['status']) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagination['current'] < $pagination['total']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['current'] + 1 ?>&search=<?= urlencode($filters['search']) ?>&category=<?= urlencode($filters['category']) ?>&status=<?= urlencode($filters['status']) ?>">
                                        Next
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-pills fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">No medicines found</h5>
                    <p class="text-gray-400">Try adjusting your search criteria or add a new medicine.</p>
                    <a href="<?= $baseUrl ?>/pharmacy/medicines/create" class="btn btn-primary">
                        <i class="fas fa-plus fa-fw"></i> Add Medicine
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable if available
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#medicinesTable').DataTable({
            pageLength: 25,
            order: [[0, 'asc']],
            responsive: true
        });
    }
});

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

function exportToCSV() {
    // Implementation for CSV export
    alert('CSV export functionality will be implemented soon.');
}

function exportToPDF() {
    // Implementation for PDF export
    alert('PDF export functionality will be implemented soon.');
}
</script> 