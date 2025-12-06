<?php
/**
 * Nyalife HMS - Department Management Index
 *
 * View for listing and managing departments.
 */

$pageTitle = 'Departments - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building fa-fw"></i> Departments
        </h1>
        <a href="<?= $baseUrl ?>/departments/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus fa-fw"></i> Create Department
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_messages'])): ?>
        <?php foreach ($_SESSION['flash_messages'] as $message): ?>
            <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
        <?php unset($_SESSION['flash_messages']); ?>
    <?php endif; ?>

    <!-- Search and Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search & Filter</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= $baseUrl ?>/departments" class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                               placeholder="Search departments...">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($_GET['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="type">Type</label>
                        <select class="form-control" id="type" name="type">
                            <option value="">All Types</option>
                            <option value="clinical" <?= ($_GET['type'] ?? '') === 'clinical' ? 'selected' : '' ?>>Clinical</option>
                            <option value="administrative" <?= ($_GET['type'] ?? '') === 'administrative' ? 'selected' : '' ?>>Administrative</option>
                            <option value="support" <?= ($_GET['type'] ?? '') === 'support' ? 'selected' : '' ?>>Support</option>
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
                            <a href="<?= $baseUrl ?>/departments" class="btn btn-secondary">
                                <i class="fas fa-times fa-fw"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Departments List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Departments</h6>
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
            <?php if (empty($departments)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-building fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">No departments found</h5>
                    <p class="text-gray-400">No departments match your search criteria.</p>
                    <a href="<?= $baseUrl ?>/departments/create" class="btn btn-primary">
                        <i class="fas fa-plus fa-fw"></i> Create First Department
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="departmentsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Department Name</th>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Head</th>
                                <th>Staff Count</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departments as $department): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="department-icon mr-3">
                                                <i class="fas fa-building fa-2x text-primary"></i>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($department['department_name'] ?? $department['name'] ?? 'N/A') ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($department['description'] ?? '') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?= htmlspecialchars($department['code'] ?? 'N/A') ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $typeClass = '';
                                switch ($department['type'] ?? 'general') {
                                    case 'clinical':
                                        $typeClass = 'badge-success';
                                        break;
                                    case 'administrative':
                                        $typeClass = 'badge-warning';
                                        break;
                                    case 'support':
                                        $typeClass = 'badge-info';
                                        break;
                                    default:
                                        $typeClass = 'badge-secondary';
                                }
                                ?>
                                        <span class="badge <?= $typeClass ?>">
                                            <?= ucfirst($department['type'] ?? 'General') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($department['head_name'])): ?>
                                            <div class="d-flex align-items-center">
                                                <img src="<?= !empty($department['head_image']) ? $baseUrl . '/uploads/profiles/' . $department['head_image'] : $baseUrl . '/assets/img/placeholders/default-avatar.png' ?>"
                                                     class="rounded-circle mr-2" width="32" height="32" alt="Department Head">
                                                <div>
                                                    <strong><?= htmlspecialchars($department['head_name']) ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= htmlspecialchars($department['head_role'] ?? '') ?></small>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Not assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary"><?= $department['staff_count'] ?? 0 ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?= ($department['is_active'] ?? 1) == 1 ? 'badge-success' : 'badge-secondary' ?>">
                                            <?= ($department['is_active'] ?? 1) == 1 ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= $baseUrl ?>/departments/show/<?= $department['department_id'] ?>"
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye fa-fw"></i>
                                            </a>
                                            <a href="<?= $baseUrl ?>/departments/edit/<?= $department['department_id'] ?>"
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit fa-fw"></i>
                                            </a>
                                            <?php if (($department['is_active'] ?? 1) == 1): ?>
                                                <button type="button" class="btn btn-sm btn-danger" title="Deactivate"
                                                        onclick="deactivateDepartment(<?= $department['department_id'] ?>)">
                                                    <i class="fas fa-times fa-fw"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-success" title="Activate"
                                                        onclick="activateDepartment(<?= $department['department_id'] ?>)">
                                                    <i class="fas fa-check fa-fw"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <nav aria-label="Department pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>&search=<?= htmlspecialchars($_GET['search'] ?? '') ?>&status=<?= htmlspecialchars($_GET['status'] ?? '') ?>&type=<?= htmlspecialchars($_GET['type'] ?? '') ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($_GET['search'] ?? '') ?>&status=<?= htmlspecialchars($_GET['status'] ?? '') ?>&type=<?= htmlspecialchars($_GET['type'] ?? '') ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>&search=<?= htmlspecialchars($_GET['search'] ?? '') ?>&status=<?= htmlspecialchars($_GET['status'] ?? '') ?>&type=<?= htmlspecialchars($_GET['type'] ?? '') ?>">
                                        Next
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function exportToCSV() {
    window.location.href = '<?= $baseUrl ?>/departments/export/csv';
}

function exportToPDF() {
    window.location.href = '<?= $baseUrl ?>/departments/export/pdf';
}

function activateDepartment(departmentId) {
    if (confirm('Are you sure you want to activate this department?')) {
        window.location.href = '<?= $baseUrl ?>/departments/activate/' + departmentId;
    }
}

function deactivateDepartment(departmentId) {
    if (confirm('Are you sure you want to deactivate this department?')) {
        window.location.href = '<?= $baseUrl ?>/departments/deactivate/' + departmentId;
    }
}

// Initialize DataTable
$(document).ready(function() {
    $('#departmentsTable').DataTable({
        "pageLength": 25,
        "order": [[0, "asc"]],
        "language": {
            "search": "Search departments:",
            "lengthMenu": "Show _MENU_ departments per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ departments"
        }
    });
});
</script>

 