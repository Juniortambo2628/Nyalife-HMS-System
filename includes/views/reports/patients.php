<?php
/**
 * Nyalife HMS - Patients Report View
 * Displays patient demographics and statistics
 */
$pageTitle = 'Patient Reports - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-user-injured text-success me-2"></i>
                    Patient Reports
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
                            <h2 class="text-primary" id="totalPatients">0</h2>
                            <p class="text-muted mb-0">Total Patients</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-info" id="newPatientsMonth">0</h2>
                            <p class="text-muted mb-0">New This Month</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-success" id="activePatients">0</h2>
                            <p class="text-muted mb-0">Active Patients</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-warning" id="averageAge">0</h2>
                            <p class="text-muted mb-0">Average Age</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Gender Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="genderChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Age Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="ageChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Patient Registration Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="registrationTrendChart" height="150"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Options -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Export Options</h5>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-success" id="exportExcelBtn">
                                <i class="fas fa-file-excel me-2"></i>Export to Excel
                            </button>
                            <button class="btn btn-danger" id="exportPdfBtn">
                                <i class="fas fa-file-pdf me-2"></i>Export to PDF
                            </button>
                            <button class="btn btn-primary" id="printReportBtn">
                                <i class="fas fa-print me-2"></i>Print Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for Chart.js to be available
    function initCharts() {
        if (typeof Chart !== 'undefined') {
            loadPatientReports();
        } else {
            // Retry after a short delay if Chart.js isn't loaded yet
            setTimeout(initCharts, 100);
        }
    }
    initCharts();
});

function loadPatientReports() {
    // Mock statistics
    document.getElementById('totalPatients').textContent = '542';
    document.getElementById('newPatientsMonth').textContent = '34';
    document.getElementById('activePatients').textContent = '487';
    document.getElementById('averageAge').textContent = '38';
    
    // Gender distribution chart
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    new Chart(genderCtx, {
        type: 'doughnut',
        data: {
            labels: ['Female', 'Male'],
            datasets: [{
                data: [342, 200],
                backgroundColor: ['#e83e8c', '#007bff']
            }]
        }
    });
    
    // Age distribution chart
    const ageCtx = document.getElementById('ageChart').getContext('2d');
    new Chart(ageCtx, {
        type: 'bar',
        data: {
            labels: ['0-18', '19-30', '31-45', '46-60', '60+'],
            datasets: [{
                label: 'Patients',
                data: [45, 98, 156, 178, 65],
                backgroundColor: '#28a745'
            }]
        }
    });
    
    // Registration trend chart
    const trendCtx = document.getElementById('registrationTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'New Registrations',
                data: [28, 42, 35, 47, 39, 34],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true
            }]
        }
    });
}
</script>
