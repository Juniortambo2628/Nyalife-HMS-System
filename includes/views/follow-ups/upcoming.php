<?php
/**
 * Nyalife HMS - Upcoming Follow-ups View
 *
 * View for displaying upcoming follow-ups.
 */

$pageTitle = 'Upcoming Follow-ups - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-alt fa-fw"></i> Upcoming Follow-ups
        </h1>
        <div>
            <a href="<?= $baseUrl ?>/follow-ups/create" class="btn btn-primary btn-sm">
                <i class="fas fa-plus fa-fw"></i> New Follow-up
            </a>
            <a href="<?= $baseUrl ?>/follow-ups" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left fa-fw"></i> Back to Follow-ups
            </a>
        </div>
    </div>

    <!-- Upcoming Follow-ups List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Upcoming Follow-ups</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($upcomingFollowUps)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="upcomingFollowUpsTable">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Follow-up Date</th>
                                <th>Type</th>
                                <th>Priority</th>
                                <th>Days Until</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingFollowUps as $followUp): ?>
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
                                        <span class="badge badge-<?= $followUp['priority'] === 'urgent' ? 'danger' : ($followUp['priority'] === 'high' ? 'warning' : 'info') ?>">
                                            <?= htmlspecialchars(ucfirst($followUp['priority'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $followUpDate = new DateTime($followUp['follow_up_date'] . ' ' . ($followUp['follow_up_time'] ?? '00:00:00'));
                                $now = new DateTime();
                                $diff = $now->diff($followUpDate);

                                if ($followUpDate > $now) {
                                    $daysUntil = $diff->days;
                                    if ($daysUntil == 0) {
                                        echo '<span class="badge badge-warning">Today</span>';
                                    } elseif ($daysUntil == 1) {
                                        echo '<span class="badge badge-info">Tomorrow</span>';
                                    } else {
                                        echo '<span class="badge badge-secondary">' . $daysUntil . ' days</span>';
                                    }
                                } else {
                                    echo '<span class="badge badge-danger">Overdue</span>';
                                }
                                ?>
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
            <?php else: ?>
                <div class="text-center py-4">
                    <img src="<?= $baseUrl ?>/assets/img/illustrations/no-follow-ups.svg" alt="No Upcoming Follow-ups" class="mb-3 img-max-200">
                    <h5 class="text-muted">No Upcoming Follow-ups</h5>
                    <p class="text-muted">There are no upcoming follow-ups scheduled.</p>
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
        $('#upcomingFollowUpsTable').DataTable({
            pageLength: 25,
            order: [[2, 'asc']], // Sort by follow-up date
            responsive: true,
            language: {
                "search": "Search upcoming follow-ups:",
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