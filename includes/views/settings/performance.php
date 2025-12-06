<?php
/**
 * Nyalife HMS - Performance Monitoring View
 */
$pageTitle = 'Performance Monitoring - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-tachometer-alt text-secondary me-2"></i>
                    Performance Monitoring
                </h1>
                <a href="<?= $baseUrl ?>/settings" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Settings
                </a>
            </div>

            <!-- System Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-info" id="cpuUsage">0%</h2>
                            <p class="text-muted mb-0">CPU Usage</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-warning" id="memoryUsage">0%</h2>
                            <p class="text-muted mb-0">Memory Usage</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-success" id="diskUsage">0%</h2>
                            <p class="text-muted mb-0">Disk Usage</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h2 class="text-primary" id="activeUsers">0</h2>
                            <p class="text-muted mb-0">Active Users</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Charts -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Response Time Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="responseTimeChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Database Query Performance</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="queryPerformanceChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Health -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-heartbeat me-2"></i>System Health Checks</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="list-group">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-database text-success me-2"></i>Database Connection</span>
                                    <span class="badge bg-success">Healthy</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-server text-success me-2"></i>Web Server</span>
                                    <span class="badge bg-success">Running</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-envelope text-success me-2"></i>Email Service</span>
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="list-group">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-hdd text-warning me-2"></i>Disk Space</span>
                                    <span class="badge bg-warning">65% Used</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-memory text-success me-2"></i>PHP Memory Limit</span>
                                    <span class="badge bg-success">256M</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-clock text-success me-2"></i>System Uptime</span>
                                    <span class="badge bg-success">15 days</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slow Queries Log -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Slow Queries (>1s)</h5>
                    <button class="btn btn-sm btn-secondary">
                        <i class="fas fa-sync me-2"></i>Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Query</th>
                                    <th>Execution Time</th>
                                    <th>Count (24h)</th>
                                    <th>Last Seen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>SELECT * FROM appointments WHERE...</code></td>
                                    <td>1.23s</td>
                                    <td>15</td>
                                    <td>2 hours ago</td>
                                </tr>
                                <tr>
                                    <td><code>SELECT * FROM lab_samples JOIN...</code></td>
                                    <td>1.45s</td>
                                    <td>8</td>
                                    <td>5 hours ago</td>
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
    loadPerformanceData();
    
    // Refresh data every 30 seconds
    setInterval(loadPerformanceData, 30000);
});

function loadPerformanceData() {
    // Mock system statistics
    document.getElementById('cpuUsage').textContent = '32%';
    document.getElementById('memoryUsage').textContent = '58%';
    document.getElementById('diskUsage').textContent = '65%';
    document.getElementById('activeUsers').textContent = '24';
    
    // Response time chart
    const responseCtx = document.getElementById('responseTimeChart').getContext('2d');
    new Chart(responseCtx, {
        type: 'line',
        data: {
            labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
            datasets: [{
                label: 'Response Time (ms)',
                data: [120, 145, 280, 350, 290, 180],
                borderColor: '#17a2b8',
                backgroundColor: 'rgba(23, 162, 184, 0.1)',
                tension: 0.4,
                fill: true
            }]
        }
    });
    
    // Query performance chart
    const queryCtx = document.getElementById('queryPerformanceChart').getContext('2d');
    new Chart(queryCtx, {
        type: 'bar',
        data: {
            labels: ['<100ms', '100-500ms', '500ms-1s', '>1s'],
            datasets: [{
                label: 'Query Count',
                data: [2340, 456, 87, 23],
                backgroundColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545']
            }]
        }
    });
}
</script>
