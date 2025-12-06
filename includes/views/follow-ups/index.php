<?php
/**
 * Nyalife HMS - Follow-ups Index
 *
 * View for listing all follow-ups with filtering and search capabilities.
 */

$pageTitle = 'Follow-ups - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-check fa-fw"></i> Follow-ups
        </h1>
        <a href="<?= $baseUrl ?>/follow-ups/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus fa-fw"></i> New Follow-up
        </a>
    </div>

    <!-- Filters and Search -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters & Search</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= $baseUrl ?>/follow-ups" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="in_progress" <?= ($_GET['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="completed" <?= ($_GET['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="priority">Priority</label>
                            <select class="form-control" id="priority" name="priority">
                                <option value="">All Priorities</option>
                                <option value="low" <?= ($_GET['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= ($_GET['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="high" <?= ($_GET['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                                <option value="urgent" <?= ($_GET['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_from">Date From</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_to">Date To</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                                   placeholder="Search by patient name, doctor name, or notes">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-flex">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search fa-fw"></i> Search
                                </button>
                                <a href="<?= $baseUrl ?>/follow-ups" class="btn btn-secondary">
                                    <i class="fas fa-times fa-fw"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Follow-ups List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Follow-ups List</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($followUps)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="followUpsTable">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Follow-up Date</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($followUps as $followUp): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($followUp['patient_name']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($followUp['patient_phone']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($followUp['doctor_name']) ?></td>
                                    <td>
                                        <?= date('M j, Y', strtotime($followUp['follow_up_date'])) ?>
                                        <br><small class="text-muted"><?= date('g:i A', strtotime($followUp['follow_up_time'] ?? '00:00:00')) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?= htmlspecialchars(ucfirst($followUp['follow_up_type'])) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $followUp['status'] === 'completed' ? 'success' : ($followUp['status'] === 'cancelled' ? 'danger' : 'warning') ?>">
                                            <?= htmlspecialchars(ucfirst($followUp['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $followUp['priority'] === 'urgent' ? 'danger' : ($followUp['priority'] === 'high' ? 'warning' : 'info') ?>">
                                            <?= htmlspecialchars(ucfirst($followUp['priority'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= $baseUrl ?>/follow-ups/show/<?= $followUp['follow_up_id'] ?>"
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye fa-fw"></i>
                                            </a>
                                            <a href="<?= $baseUrl ?>/follow-ups/edit/<?= $followUp['follow_up_id'] ?>"
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit fa-fw"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="updateStatus(<?= $followUp['follow_up_id'] ?>, 'completed')"
                                                    title="Mark Complete">
                                                <i class="fas fa-check fa-fw"></i>
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
                    <nav aria-label="Follow-ups pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['current'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $baseUrl ?>/follow-ups?page=<?= $pagination['current_page'] - 1 ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $pagination['total']; $i++): ?>
                                <li class="page-item <?= $i == $pagination['current'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $baseUrl ?>/follow-ups?page=<?= $i ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagination['current'] < $pagination['total']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $baseUrl ?>/follow-ups?page=<?= $pagination['current_page'] + 1 ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                                        Next
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-4">
                    <img src="<?= $baseUrl ?>/assets/img/illustrations/no-follow-ups.svg" alt="No Follow-ups" class="mb-3" style="max-width: 200px;">
                    <h5 class="text-muted">No Follow-ups Found</h5>
                    <p class="text-muted">There are no follow-ups matching your criteria.</p>
                    <a href="<?= $baseUrl ?>/follow-ups/create" class="btn btn-primary">
                        <i class="fas fa-plus fa-fw"></i> Create First Follow-up
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
        $('#followUpsTable').DataTable({
            pageLength: 25,
            order: [[2, 'asc']],
            responsive: true,
            language: {
                "search": "Search follow-ups:",
                "lengthMenu": "Show _MENU_ follow-ups per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ follow-ups"
            }
        });
    }
});

function updateStatus(followUpId, status) {
    if (confirm('Are you sure you want to update this follow-up status?')) {
        fetch('<?= $baseUrl ?>/follow-ups/update-status/' + followUpId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update follow-up status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected error occurred.');
        });
    }
}
</script>