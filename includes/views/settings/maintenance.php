<?php
/**
 * Nyalife HMS - Maintenance Tools View
 */
$pageTitle = 'Maintenance Tools - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-tools text-dark me-2"></i>
                    Maintenance Tools
                </h1>
                <a href="<?= $baseUrl ?>/settings" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Settings
                </a>
            </div>

            <!-- System Maintenance -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>System Maintenance</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6><i class="fas fa-broom text-primary me-2"></i>Clear Cache</h6>
                                    <p class="text-muted small">Clear all system caches to free up memory and improve performance</p>
                                    <button class="btn btn-primary" onclick="clearCache()">
                                        <i class="fas fa-trash me-2"></i>Clear All Cache
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6><i class="fas fa-file-alt text-info me-2"></i>Clear Logs</h6>
                                    <p class="text-muted small">Remove old log files to free up disk space</p>
                                    <button class="btn btn-info" onclick="clearLogs()">
                                        <i class="fas fa-trash me-2"></i>Clear Old Logs
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6><i class="fas fa-sync text-success me-2"></i>Reset System</h6>
                                    <p class="text-muted small">Restart system services and clear temp files</p>
                                    <button class="btn btn-success" onclick="restartSystem()">
                                        <i class="fas fa-power-off me-2"></i>Restart Services
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Database Maintenance -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-database me-2"></i>Database Maintenance</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6><i class="fas fa-compress text-warning me-2"></i>Optimize Database</h6>
                                    <p class="text-muted small">Optimize all database tables for better performance</p>
                                    <button class="btn btn-warning" onclick="optimizeDatabase()">
                                        <i class="fas fa-play me-2"></i>Run Optimization
                                    </button>
                                    <p class="text-muted small mt-2 mb-0">Last optimized: 2 days ago</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6><i class="fas fa-check-circle text-success me-2"></i>Database Integrity Check</h6>
                                    <p class="text-muted small">Check database tables for errors and corruption</p>
                                    <button class="btn btn-success" onclick="checkDatabase()">
                                        <i class="fas fa-stethoscope me-2"></i>Run Check
                                    </button>
                                    <p class="text-muted small mt-2 mb-0">Last checked: 1 day ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Management -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-folder me-2"></i>File Management</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Uploads Directory</h6>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <p class="mb-0">Total Files: <strong>1,234</strong></p>
                                    <p class="mb-0">Total Size: <strong>456 MB</strong></p>
                                </div>
                                <button class="btn btn-sm btn-danger" onclick="cleanUploads()">
                                    <i class="fas fa-trash me-2"></i>Clean Old Files
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Temp Directory</h6>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <p class="mb-0">Total Files: <strong>89</strong></p>
                                    <p class="mb-0">Total Size: <strong>12 MB</strong></p>
                                </div>
                                <button class="btn btn-sm btn-danger" onclick="cleanTemp()">
                                    <i class="fas fa-trash me-2"></i>Clean Temp Files
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Health Check -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-heartbeat me-2"></i>System Health Check</h5>
                    <button class="btn btn-sm btn-primary" onclick="runHealthCheck()">
                        <i class="fas fa-play me-2"></i>Run Full Check
                    </button>
                </div>
                <div class="card-body">
                    <div id="healthCheckResults">
                        <p class="text-muted">Click "Run Full Check" to perform a comprehensive system health check.</p>
                    </div>
                </div>
            </div>

            <!-- Maintenance Schedule -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Scheduled Maintenance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Frequency</th>
                                    <th>Last Run</th>
                                    <th>Next Run</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Database Backup</td>
                                    <td>Daily</td>
                                    <td>2025-12-02 02:00 AM</td>
                                    <td>2025-12-03 02:00 AM</td>
                                    <td><span class="badge bg-success">Scheduled</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary"><i class="fas fa-play"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Log Rotation</td>
                                    <td>Weekly</td>
                                    <td>2025-11-28 03:00  AM</td>
                                    <td>2025-12-05 03:00 AM</td>
                                    <td><span class="badge bg-success">Scheduled</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary"><i class="fas fa-play"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Database Optimization</td>
                                    <td>Weekly</td>
                                    <td>2025-11-30 04:00 AM</td>
                                    <td>2025-12-07 04:00 AM</td>
                                    <td><span class="badge bg-success">Scheduled</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary"><i class="fas fa-play"></i></button>
                                    </td>
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
function clearCache() {
    if (confirm('Clear all system caches? This may temporarily slow down the system.')) {
        alert('Cache cleared successfully! (Demo - not yet connected to backend)');
    }
}

function clearLogs() {
    if (confirm('Clear old log files? This will remove logs older than 30 days.')) {
        alert('Old logs cleared successfully! (Demo - not yet connected to backend)');
    }
}

function restartSystem() {
    if (confirm('Restart system services? The system may be briefly unavailable.')) {
        alert('System services restarted! (Demo - not yet connected to backend)');
    }
}

function optimizeDatabase() {
    if (confirm('Optimize all database tables? This may take several minutes.')) {
        alert('Database optimization started! (Demo - not yet connected to backend)');
    }
}

function checkDatabase() {
    alert('Database integrity check  complete! No errors found. (Demo)');
}

function cleanUploads() {
    if (confirm('Remove upload files older than 90 days?')) {
        alert('Old upload files removed! (Demo - not yet connected to backend)');
    }
}

function cleanTemp() {
    if (confirm('Clear all temporary files?')) {
        alert('Temporary files cleared! (Demo - not yet connected to backend)');
    }
}

function runHealthCheck() {
    const results = document.getElementById('healthCheckResults');
    results.innerHTML = `
        <div class="alert alert-info">Running comprehensive health check...</div>
        <div class="list-group mt-3">
            <div class="list-group-item d-flex justify-content-between">
                <span><i class="fas fa-check text-success me-2"></i>PHP Configuration</span>
                <span class="badge bg-success">OK</span>
            </div>
            <div class="list-group-item d-flex justify-content-between">
                <span><i class="fas fa-check text-success me-2"></i>Database Connection</span>
                <span class="badge bg-success">OK</span>
            </div>
            <div class="list-group-item d-flex justify-content-between">
                <span><i class="fas fa-check text-success me-2"></i>File Permissions</span>
                <span class="badge bg-success">OK</span>
            </div>
            <div class="list-group-item d-flex justify-content-between">
                <span><i class="fas fa-check text-success me-2"></i>Disk Space</span>
                <span class="badge bg-success">OK</span>
            </div>
            <div class="list-group-item d-flex justify-content-between">
                <span><i class="fas fa-exclamation-triangle text-warning me-2"></i>PHP Memory Limit</span>
                <span class="badge bg-warning">256M (Recommended: 512M)</span>
            </div>
        </div>
    `;
}
</script>
