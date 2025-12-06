<?php
/**
 * Lab Requests Index Page
 */

$pageTitle = 'Lab Requests - Nyalife HMS';
?>

<div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 text-gray-800">Lab Requests</h1>
                    <div>
                        <a href="<?= $baseUrl ?>/lab/request/new" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Request
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="<?= $baseUrl ?>/lab/requests" class="row g-3" id="lab-filter-form">
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="all" <?= $status == 'all' ? 'selected' : '' ?>>All</option>
                            <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="in_progress" <?= $status == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Search by patient name, ID, or request ID...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Lab Requests</h6>
            </div>
            <div class="card-body">
                <?php if (empty($requests)): ?>
                    <div class="alert alert-info">
                        No lab requests found matching your criteria.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered dataTable" width="100%" cellspacing="0" id="lab-requests-table">
                            <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Tests</th>
                                    <th>Date Requested</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($request['request_id']) ?></td>
                                        <td><?= htmlspecialchars($request['patient_name']) ?></td>
                                        <td><?= htmlspecialchars($request['doctor_name']) ?></td>
                                        <td><?= htmlspecialchars($request['test_count']) ?> tests</td>
                                        <td><?= date('M d, Y', strtotime($request['created_at'])) ?></td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                    switch ($request['status']) {
                                        case 'pending': $statusClass = 'warning';
                                            break;
                                        case 'in_progress': $statusClass = 'info';
                                            break;
                                        case 'completed': $statusClass = 'success';
                                            break;
                                        case 'cancelled': $statusClass = 'danger';
                                            break;
                                        default: $statusClass = 'secondary';
                                    }
                                    ?>
                                            <span class="badge bg-<?= $statusClass ?>">
                                                <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= $baseUrl ?>/lab/request/<?= $request['request_id'] ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($request['status'] == 'completed'): ?>
                                                <a href="<?= $baseUrl ?>/lab/results/print/<?= $request['request_id'] ?>" class="btn btn-sm btn-secondary" target="_blank" data-no-ajax>
                                                    <i class="fas fa-print"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total'] > $pagination['perPage']): ?>
                        <div class="d-flex justify-content-center mt-4">
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                    <?php
                                    $totalPages = ceil($pagination['total'] / $pagination['perPage']);
                        $currentPage = $pagination['page'];
                        $pageUrl = $pagination['url'];

                        // Previous button
                        if ($currentPage > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= $pageUrl . ($currentPage - 1) ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php
                        // Page numbers
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);

                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= $pageUrl . $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php
                        // Next button
                        if ($currentPage < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= $pageUrl . ($currentPage + 1) ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
</div>

<?php
// Add page specific script
$pageSpecificScripts[] = AssetHelper::getJs('lab-requests-index');
?>