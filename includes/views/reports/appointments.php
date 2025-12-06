<?php
/**
 * Nyalife HMS - Appointments Report View
 * Displays appointment statistics and reports
 */
$pageTitle = 'Appointment Reports - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-calendar-check text-primary me-2"></i>
                    Appointment Reports
                </h1>
                <a href="<?= $baseUrl ?>/reports" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Reports
                </a>
            </div>

            <!-- Filters Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Report Filters</h5>
                </div>
                <div class="card-body">
                    <form id="reportFiltersForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDate" name="startDate" value="<?= date('Y-m-01') ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDate" name="endDate" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="doctorFilter" class="form-label">Doctor</label>
                            <select class="form-select" id="doctorFilter" name="doctor">
                                <option value="">All Doctors</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="statusFilter" class="form-label">Status</label>
                            <select class="form-select" id="statusFilter" name="status">
                                <option value="">All Status</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="no_show">No Show</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Generate Report
                            </button>
                            <button type="button" class="btn btn-success" id="exportBtn">
                                <i class="fas fa-file-excel me-2"></i>Export to Excel
                            </button>
                            <button type="button" class="btn btn-danger" id="exportPdfBtn">
                                <i class="fas fa-file-pdf me-2"></i>Export to PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistics Overview -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-primary" id="totalAppointments">0</h2>
                            <p class="text-muted mb-0">Total Appointments</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-success" id="completedAppointments">0</h2>
                            <p class="text-muted mb-0">Completed</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-warning" id="scheduledAppointments">0</h2>
                            <p class="text-muted mb-0">Scheduled</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-danger" id="cancelledAppointments">0</h2>
                            <p class="text-muted mb-0">Cancelled</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Appointments by Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="statusChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Appointments Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="trendChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Appointment Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="appointmentsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load appointment report data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAppointmentReport();
    
    // Handle form submission
    document.getElementById('reportFiltersForm').addEventListener('submit', function(e) {
        e.preventDefault();
        loadAppointmentReport();
    });
});

function loadAppointmentReport() {
    const form = document.getElementById('reportFiltersForm');
    const formData = new FormData(form);
    
    // For now, generate mock data
    // TODO: Replace with actual API call
    generateMockData();
}

function generateMockData() {
    // Mock statistics
    document.getElementById('totalAppointments').textContent = '156';
    document.getElementById('completedAppointments').textContent = '98';
    document.getElementById('scheduledAppointments').textContent = '42';
    document.getElementById('cancelledAppointments').textContent = '16';
    
    // Status chart (pie chart)
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: ['Completed', 'Scheduled', 'Cancelled', 'No Show'],
            datasets: [{
                data: [98, 42, 16, 0],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#6c757d']
            }]
        }
    });
    
    // Trend chart (line chart)
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Appointments',
                data: [32, 45, 38, 41],
                borderColor: '#007bff',
                tension: 0.1
            }]
        }
    });
}
</script>
