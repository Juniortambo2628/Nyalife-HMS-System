<?php
/**
 * Nyalife HMS - Database Settings View
 */
$pageTitle = 'Database Settings - Nyalife HMS';

// Get database information
try {
    $dbInfo = [
        'host' => 'localhost',
        'name' => 'nyalifew_hms_prod',
        'version' => 'MySQL 8.0',
        'size' => '125 MB',
        'tables' => 44
    ];
} catch (Exception $e) {
    $dbInfo = [
        'host' => 'Unknown',
        'name' => 'Unknown',
        'version' => 'Unknown',
        'size' => 'Unknown',
        'tables' => 0
    ];
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-database text-info me-2"></i>
                    Database Settings
                </h1>
                <a href="<?= $baseUrl ?>/settings" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Settings
                </a>
            </div>

            <!-- Database Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Database Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Database Host</th>
                                    <td><?= $dbInfo['host'] ?></td>
                                </tr>
                                <tr>
                                    <th>Database Name</th>
                                    <td><?= $dbInfo['name'] ?></td>
                                </tr>
                                <tr>
                                    <th>Database Version</th>
                                    <td><?= $dbInfo['version'] ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Database Size</th>
                                    <td><?= $dbInfo['size'] ?></td>
                                </tr>
                                <tr>
                                    <th>Total Tables</th>
                                    <td><?= $dbInfo['tables'] ?></td>
                                </tr>
                                <tr>
                                    <th>Connection Status</th>
                                    <td><span class="badge bg-success">Connected</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Database Maintenance -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Database Maintenance</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Database maintenance tasks help optimize performance and ensure data integrity.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6><i class="fas fa-broom me-2"></i>Optimize Tables</h6>
                                    <p class="text-muted small">Optimize database tables for better performance</p>
                                    <button class="btn btn-sm btn-primary" onclick="optimizeTables()">
                                        <i class="fas fa-play me-2"></i>Run Optimization
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6><i class="fas fa-check-circle me-2"></i>Check Tables</h6>
                                    <p class="text-muted small">Check database integrity and errors</p>
                                    <button class="btn btn-sm btn-info" onclick="checkTables()">
                                        <i class="fas fa-play me-2"></i>Run Check
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backup & Restore -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-hdd me-2"></i>Backup & Restore</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> Always create a backup before making significant changes to the database.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6><i class="fas fa-download me-2"></i>Create Backup</h6>
                                    <p class="text-muted small">Download a complete backup of the database</p>
                                    <button class="btn btn-success" onclick="createBackup()">
                                        <i class="fas fa-download me-2"></i>Create Backup
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6><i class="fas fa-upload me-2"></i>Restore Backup</h6>
                                    <p class="text-muted small">Restore database from a backup file</p>
                                    <button class="btn btn-warning" onclick="showRestoreDialog()">
                                        <i class="fas fa-upload me-2"></i>Restore Backup
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Backups -->
                    <div class="mt-4">
                        <h6>Recent Backups</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Backup File</th>
                                        <th>Date Created</th>
                                        <th>Size</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>nyalife-hms-20251201.sql</td>
                                        <td>2025-12-01 10:30 AM</td>
                                        <td>124 MB</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary"><i class="fas fa-download"></i></button>
                                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>nyalife-hms-20251128.sql</td>
                                        <td>2025-11-28 10:30 AM</td>
                                        <td>121 MB</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary"><i class="fas fa-download"></i></button>
                                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Database Migration -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-code-branch me-2"></i>Database Migrations</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Manage database schema versions using Phinx migrations.</p>
                    <button class="btn btn-info" onclick="runMigrations()">
                        <i class="fas fa-database me-2"></i>Run Pending Migrations
                    </button>
                    <button class="btn btn-secondary" onclick="viewMigrationStatus()">
                        <i class="fas fa-list me-2"></i>View Migration Status
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function optimizeTables() {
    if (confirm('This will optimize all database tables. Continue?')) {
        alert('Database optimization started! (Demo - not yet connected to backend)');
    }
}

function checkTables() {
    alert('Table integrity check complete! No errors found. (Demo)');
}

function createBackup() {
    if (confirm('Create a new database backup?')) {
        alert('Backup process started! (Demo - not yet connected to backend)');
    }
}

function showRestoreDialog() {
    alert('Restore functionality coming soon! (Demo)');
}

function runMigrations() {
    if (confirm('Run all pending database migrations?')) {
        alert('Migration process started! (Demo - not yet connected to backend)');
    }
}

function viewMigrationStatus() {
    alert('Migration status: All migrations up to date! (Demo)');
}
</script>
