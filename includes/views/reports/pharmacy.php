<?php
/**
 * Nyalife HMS - Pharmacy Report View
 * Displays pharmacy inventory and prescription statistics
 */
$pageTitle = 'Pharmacy Reports - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-pills text-warning me-2"></i>
                    Pharmacy Reports
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
                            <h2 class="text-primary" id="totalMedications">0</h2>
                            <p class="text-muted mb-0">Total Medications</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-success" id="prescriptionsDispensed">0</h2>
                            <p class="text-muted mb-0">Prescriptions Dispensed</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-warning" id="lowStockItems">0</h2>
                            <p class="text-muted mb-0">Low Stock Items</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-danger" id="expiringSoon">0</h2>
                            <p class="text-muted mb-0">Expiring Soon</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Top Dispensed Medications</h5>
                        </div>
                        <div class="card-body">    <canvas id="topMedicationsChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Prescription Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="prescriptionStatusChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Monthly Dispensing Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="dispensingTrendChart" height="150"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Alert Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Inventory Alerts</h5>
                    <button class="btn btn-sm btn-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>View All Alerts
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Medication</th>
                                    <th>Current Stock</th>
                                    <th>Min Stock Level</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Paracetamol 500mg</td>
                                    <td>45</td>
                                    <td>100</td>
                                    <td>2025-08-15</td>
                                    <td><span class="badge bg-warning">Low Stock</span></td>
                                </tr>
                                <tr>
                                    <td>Amoxicillin 250mg</td>
                                    <td>150</td>
                                    <td>100</td>
                                    <td>2025-01-20</td>
                                    <td><span class="badge bg-danger">Expiring Soon</span></td>
                                </tr>
                                <tr>
                                    <td>Ibuprofen 400mg</td>
                                    <td>32</td>
                                    <td>75</td>
                                    <td>2025-11-30</td>
                                    <td><span class="badge bg-warning">Low Stock</span></td>
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
    loadPharmacyReports();
});

function loadPharmacyReports() {
    // Mock statistics
    document.getElementById('totalMedications').textContent = '248';
    document.getElementById('prescriptionsDispensed').textContent = '432';
    document.getElementById('lowStockItems').textContent = '12';
    document.getElementById('expiringSoon').textContent = '5';
    
    // Top medications chart
    const topMedCtx = document.getElementById('topMedicationsChart').getContext('2d');
    new Chart(topMedCtx, {
        type: 'bar',
        data: {
            labels: ['Paracetamol', 'Amoxicillin', 'Ibuprofen', 'Metformin', 'Aspirin'],
            datasets: [{
                label: 'Units Dispensed',
                data: [245, 198, 176, 142, 128],
                backgroundColor: '#ffc107'
            }]
        },
        options: {
            indexAxis: 'y'
        }
    });
    
    // Prescription status chart
    const statusCtx = document.getElementById('prescriptionStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Dispensed', 'Pending', 'Partially Dispensed', 'Cancelled'],
            datasets: [{
                data: [389, 28, 12, 3],
                backgroundColor: ['#28a745', '#ffc107', '#17a2b8', '#dc3545']
            }]
        }
    });
    
    // Dispensing trend chart
    const trendCtx = document.getElementById('dispensingTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Prescriptions Dispensed',
                data: [340, 380, 420, 398, 445, 432],
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4,
                fill: true
            }]
        }
    });
}
</script>
