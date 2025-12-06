<?php
/**
 * Nyalife HMS - Follow-ups Statistics View
 *
 * View for displaying follow-ups statistics and analytics.
 */

$pageTitle = 'Follow-ups Statistics - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar fa-fw"></i> Follow-ups Statistics
        </h1>
        <a href="<?= $baseUrl ?>/follow-ups" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left fa-fw"></i> Back to Follow-ups
        </a>
    </div>

    <!-- Statistics Overview -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Follow-ups
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalFollowUps ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                                Completed
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $completedFollowUps ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-gray-300"></i>
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
                                Pending
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pendingFollowUps ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Completion Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $completionRate ?>%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Status Distribution Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Follow-up Status Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Priority Distribution -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Priority Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="priorityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trends -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Follow-up Trends</h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyTrendsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Doctors and Types -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Doctors by Follow-ups</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Doctor</th>
                                    <th>Total Follow-ups</th>
                                    <th>Completed</th>
                                    <th>Completion Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topDoctors as $doctor): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($doctor['doctor_name']) ?></td>
                                        <td><?= $doctor['total_follow_ups'] ?></td>
                                        <td><?= $doctor['completed_follow_ups'] ?></td>
                                        <td><?= $doctor['completion_rate'] ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Follow-up Types Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($followUpTypes as $type): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(ucfirst($type['follow_up_type'])) ?></td>
                                        <td><?= $type['count'] ?></td>
                                        <td><?= $type['percentage'] ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Pending', 'In Progress', 'Cancelled'],
            datasets: [{
                data: [<?= $completedFollowUps ?>, <?= $pendingFollowUps ?>, <?= $inProgressFollowUps ?>, <?= $cancelledFollowUps ?>],
                backgroundColor: ['#28a745', '#ffc107', '#17a2b8', '#dc3545'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Priority Distribution Chart
    const priorityCtx = document.getElementById('priorityChart').getContext('2d');
    new Chart(priorityCtx, {
        type: 'pie',
        data: {
            labels: ['Low', 'Medium', 'High', 'Urgent'],
            datasets: [{
                data: [<?= $lowPriority ?>, <?= $mediumPriority ?>, <?= $highPriority ?>, <?= $urgentPriority ?>],
                backgroundColor: ['#6c757d', '#17a2b8', '#ffc107', '#dc3545'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Monthly Trends Chart
    const monthlyCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($monthlyLabels) ?>,
            datasets: [{
                label: 'Total Follow-ups',
                data: <?= json_encode($monthlyData) ?>,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }, {
                label: 'Completed',
                data: <?= json_encode($monthlyCompleted) ?>,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });
});
</script>