<?php
/**
 * Nyalife HMS - Payment Statistics View
 *
 * View for displaying payment statistics and analytics.
 */

$pageTitle = 'Payment Statistics - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar fa-fw"></i> Payment Statistics
        </h1>
        <div>
            <a href="<?= $baseUrl ?>/payments" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left fa-fw"></i> Back to Payments
            </a>
        </div>
    </div>

    <!-- Period Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Period</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= $baseUrl ?>/payments/statistics" class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="period">Period</label>
                        <select name="period" id="period" class="form-control">
                            <option value="today" <?= ($period == 'today') ? 'selected' : '' ?>>Today</option>
                            <option value="week" <?= ($period == 'week') ? 'selected' : '' ?>>This Week</option>
                            <option value="month" <?= ($period == 'month') ? 'selected' : '' ?>>This Month</option>
                            <option value="quarter" <?= ($period == 'quarter') ? 'selected' : '' ?>>This Quarter</option>
                            <option value="year" <?= ($period == 'year') ? 'selected' : '' ?>>This Year</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="date_from">From Date</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" 
                               value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="date_to">To Date</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" 
                               value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter fa-fw"></i> Apply Filter
                    </button>
                    <a href="<?= $baseUrl ?>/payments/statistics" class="btn btn-secondary">
                        <i class="fas fa-times fa-fw"></i> Clear Filter
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Payments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($statistics['total_payments']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                Total Amount
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($statistics['total_amount'], 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
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
                                Average Payment
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($statistics['average_payment'], 2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                Success Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($statistics['success_rate'], 1) ?>%
                            </div>
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
        <!-- Payment Method Distribution -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Methods Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="paymentMethodsChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <?php foreach ($statistics['payment_methods'] as $method => $data): ?>
                            <span class="mr-2">
                                <i class="fas fa-circle" style="color: <?= $data['color'] ?>"></i> 
                                <?= ucfirst(str_replace('_', ' ', $method)) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Status Distribution -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Status</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="paymentStatusChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <?php foreach ($statistics['payment_statuses'] as $status => $data): ?>
                            <span class="mr-2">
                                <i class="fas fa-circle" style="color: <?= $data['color'] ?>"></i> 
                                <?= ucfirst($status) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily/Weekly Trends -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Trends</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="paymentTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Paying Patients -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Paying Patients</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Total Payments</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($statistics['top_patients'] as $patient): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($patient['patient_name']) ?></td>
                                        <td><?= $patient['payment_count'] ?></td>
                                        <td><?= number_format($patient['total_amount'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Payments</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($statistics['recent_payments'] as $payment): ?>
                                    <tr>
                                        <td><?= date('M j, Y', strtotime($payment['payment_date'])) ?></td>
                                        <td><?= htmlspecialchars($payment['patient_name']) ?></td>
                                        <td><?= number_format($payment['amount'], 2) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $payment['status'] == 'completed' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($payment['status']) ?>
                                            </span>
                                        </td>
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
    // Payment Methods Chart
    const paymentMethodsCtx = document.getElementById('paymentMethodsChart').getContext('2d');
    new Chart(paymentMethodsCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($statistics['payment_methods'])) ?>,
            datasets: [{
                data: <?= json_encode(array_column($statistics['payment_methods'], 'amount')) ?>,
                backgroundColor: <?= json_encode(array_column($statistics['payment_methods'], 'color')) ?>,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Payment Status Chart
    const paymentStatusCtx = document.getElementById('paymentStatusChart').getContext('2d');
    new Chart(paymentStatusCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($statistics['payment_statuses'])) ?>,
            datasets: [{
                data: <?= json_encode(array_column($statistics['payment_statuses'], 'count')) ?>,
                backgroundColor: <?= json_encode(array_column($statistics['payment_statuses'], 'color')) ?>,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Payment Trends Chart
    const paymentTrendsCtx = document.getElementById('paymentTrendsChart').getContext('2d');
    new Chart(paymentTrendsCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_keys($statistics['daily_trends'])) ?>,
            datasets: [{
                label: 'Daily Payments',
                data: <?= json_encode(array_values($statistics['daily_trends'])) ?>,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script> 