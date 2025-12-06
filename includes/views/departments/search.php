<?php
/**
 * Nyalife HMS - Department Search View
 *
 * View for searching departments.
 */

$pageTitle = 'Search Departments - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-search fa-fw"></i> Search Departments
        </h1>
        <a href="<?= $baseUrl ?>/departments" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left fa-fw"></i> Back to Departments
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search Departments</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= $baseUrl ?>/departments/search" class="mb-4">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="query">Search Query</label>
                            <input type="text" class="form-control" id="query" name="query"
                                   value="<?= htmlspecialchars($_GET['query'] ?? '') ?>"
                                   placeholder="Search by name, description...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All</option>
                                <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($_GET['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>Search
                </button>
            </form>

            <?php if (isset($results) && !empty($results)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $dept): ?>
                                <tr>
                                    <td><?= htmlspecialchars($dept['name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($dept['description'] ?? '') ?></td>
                                    <td>
                                        <span class="badge badge-<?= ($dept['is_active'] ?? 0) ? 'success' : 'secondary' ?>">
                                            <?= ($dept['is_active'] ?? 0) ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= $baseUrl ?>/departments/show/<?= $dept['department_id'] ?? '' ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif (isset($results)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No departments found matching your search criteria.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

