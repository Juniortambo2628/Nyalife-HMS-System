<?php
/**
 * Nyalife HMS - Financial Report View
 * Displays financial statistics and revenue reports
 */
$pageTitle = 'Financial Reports - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-chart-line text-secondary me-2"></i>
                    Financial Reports
                </h1>
                <a href="<?= $baseUrl ?>/reports" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Reports
                </a>
            </div>

            <!-- Statistics Overview -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-success" id="totalRevenue">$0</h2>
                            <p class="text-muted mb-0">Total Revenue</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-primary" id="collectedPayments">$0</h2>
                            <p class="text-muted mb-0">Collected Payments</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-warning" id="pendingPayments">$0</h2>
                            <p class="text-muted mb-0">Pending Payments</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-info" id="totalInvoices">0</h2>
                            <p class="text-muted mb-0">Total Invoices</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Revenue Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueTrendChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Payment Methods</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="paymentMethodsChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Revenue by Service</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueByServiceChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Invoice Status Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="invoiceStatusChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Transactions</h5>
                    <div>
                        <button class="btn btn-sm btn-success">
                            <i class="fas fa-file-excel me-2"></i>Export
                        </button>
                        <button class="btn btn-sm btn-danger">
                            <i class="fas fa-file-pdf me-2"></i>PDF
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Patient</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#INV-1234</td>
                                    <td>Jane Doe</td>
                                    <td>2025-12-01</td>
                                    <td>$150.00</td>
                                    <td>Cash</td>
                                    <td><span class="badge bg-success">Paid</span></td>
                                </tr>
                                <tr>
                                    <td>#INV-1235</td>
                                    <td>John Smith</td>
                                    <td>2025-12-01</td>
                                    <td>$275.00</td>
                                    <td>Credit Card</td>
                                    <td><span class="badge bg-success">Paid</span></td>
                                </tr>
                                <tr>
                                    <td>#INV-1236</td>
                                    <td>Mary Johnson</td>
                                    <td>2025-12-02</td>
                                    <td>$320.00</td>
                                    <td>Insurance</td>
                                    <td><span class="badge bg-warning">Pending</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadFinancialReports();
});

function loadFinancialReports() {
    // Mock statistics
    document.getElementById('totalRevenue').textContent = '$45,280';
    document.getElementById('collectedPayments').textContent = '$38,450';
    document.getElementById('pendingPayments').textContent = '$6,830';
    document.getElementById('totalInvoices').textContent = '324';
    
    // Revenue trend chart
    const trendCtx = document.getElementById('revenueTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Revenue ($)',
                data: [38200, 42500, 39800, 44100, 41300, 45280],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Payment methods chart
    const methodsCtx = document.getElementById('paymentMethodsChart').getContext('2d');
    new Chart(methodsCtx, {
        type: 'doughnut',
        data: {
            labels: ['Cash', 'Credit Card', 'Insurance', 'Mobile Payment'],
            datasets: [{
                data: [145, 98, 62, 19],
                backgroundColor: ['#28a745', '#007bff', '#ffc107', '#17a2b8']
            }]
        }
    });
    
    // Revenue by service chart
    const serviceCtx = document.getElementById('revenueByServiceChart').getContext('2d');
    new Chart(serviceCtx, {
        type: 'bar',
        data: {
            labels: ['Consultations', 'Lab Tests', 'Pharmacy', 'Procedures', 'Other'],
            datasets: [{
                label: 'Revenue ($)',
                data: [18500, 12300, 8900, 4200, 1380],
                backgroundColor: ['#007bff', '#17a2b8', '#ffc107', '#28a745', '#6c757d']
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Invoice status chart
    const statusCtx = document.getElementById('invoiceStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: ['Paid', 'Pending', 'Partially Paid', 'Overdue', 'Cancelled'],
            datasets: [{
                data: [278, 28, 12, 4, 2],
                backgroundColor: ['#28a745', '#ffc107', '#17a2b8', '#dc3545', '#6c757d']
            }]
        }
    });
}
</script>
