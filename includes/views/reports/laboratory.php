<?php
/**
 * Nyalife HMS - Laboratory Report View
 * Displays laboratory test statistics and reports
 */
$pageTitle = 'Laboratory Reports - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-microscope text-info me-2"></i>
                    Laboratory Reports
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
                            <h2 class="text-primary" id="totalTests">0</h2>
                            <p class="text-muted mb-0">Total Tests</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-success" id="completedTests">0</h2>
                            <p class="text-muted mb-0">Completed</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-warning" id="pendingTests">0</h2>
                            <p class="text-muted mb-0">Pending</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-info" id="avgTurnaroundTime">0</h2>
                            <p class="text-muted mb-0">Avg TAT (hours)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Tests by Category</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="categoryChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Test Status Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="statusChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Test Volume Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="volumeTrendChart" height="150"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Popular Tests Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Most Requested Tests</h5>
                    <button class="btn btn-sm btn-success">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Test Name</th>
                                    <th>Total Requests</th>
                                    <th>Completed</th>
                                    <th>Pending</th>
                                    <th>Avg TAT</th>
                                </tr>
                            </thead>
                            <tbody id="popularTestsTable">
                                <tr>
                                    <td>Complete Blood Count (CBC)</td>
                                    <td>87</td>
                                    <td>82</td>
                                    <td>5</td>
                                    <td>24 hrs</td>
                                </tr>
                                <tr>
                                    <td>Blood Glucose Test</td>
                                    <td>65</td>
                                    <td>63</td>
                                    <td>2</td>
                                    <td>12 hrs</td>
                                </tr>
                                <tr>
                                    <td>Urinalysis</td>
                                    <td>54</td>
                                    <td>51</td>
                                    <td>3</td>
                                    <td>18 hrs</td>
                                </tr>
                                <tr>
                                    <td>Lipid Profile</td>
                                    <td>42</td>
                                    <td>40</td>
                                    <td>2</td>
                                    <td>24 hrs</td>
                                </tr>
                                <tr>
                                    <td>Liver Function Test</td>
                                    <td>38</td>
                                    <td>36</td>
                                    <td>2</td>
                                    <td>36 hrs</td>
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
    loadLaboratoryReports();
});

function loadLaboratoryReports() {
    // Mock statistics
    document.getElementById('totalTests').textContent = '324';
    document.getElementById('completedTests').textContent = '298';
    document.getElementById('pendingTests').textContent = '26';
    document.getElementById('avgTurnaroundTime').textContent = '22';
    
    // Tests by category chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: ['Hematology', 'Biochemistry', 'Microbiology', 'Immunology', 'Pathology'],
            datasets: [{
                label: 'Number of Tests',
                data: [87, 125, 45, 38, 29],
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#17a2b8', '#6c757d']
            }]
        },
        options: {
            indexAxis: 'y'
        }
    });
    
    // Status distribution chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'In Progress', 'Pending', 'Cancelled'],
            datasets: [{
                data: [298, 18, 26, 8],
                backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545']
            }]
        }
    });
    
    // Volume trend chart
    const trendCtx = document.getElementById('volumeTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Test Volume',
                data: [72, 85, 78, 89],
                borderColor: '#17a2b8',
                backgroundColor: 'rgba(23, 162, 184, 0.1)',
                tension: 0.4,
                fill: true
            }]
        }
    });
}
</script>
