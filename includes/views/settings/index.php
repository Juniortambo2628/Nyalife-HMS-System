<?php
/**
 * Nyalife HMS - Settings Index View
 *
 * System settings page.
 */
$pageTitle = 'Settings - Nyalife HMS';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-cog text-primary me-2"></i>
                    System Settings
                </h1>
                <a href="<?= $baseUrl ?>/dashboard" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
            
            <!-- System Overview -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>System Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-primary mb-1"><?= $systemStats['totalUsers'] ?? 0 ?></div>
                                        <div class="text-muted small">Total Users</div>
                                    </div>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-success mb-1"><?= $systemStats['totalPatients'] ?? 0 ?></div>
                                        <div class="text-muted small">Patients</div>
                                    </div>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-info mb-1"><?= $systemStats['totalDoctors'] ?? 0 ?></div>
                                        <div class="text-muted small">Doctors</div>
                                    </div>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-warning mb-1"><?= $systemStats['totalNurses'] ?? 0 ?></div>
                                        <div class="text-muted small">Nurses</div>
                                    </div>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-secondary mb-1"><?= $systemStats['totalDepartments'] ?? 0 ?></div>
                                        <div class="text-muted small">Departments</div>
                                    </div>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-dark mb-1"><?= $systemStats['systemUptime'] ?? '24/7' ?></div>
                                        <div class="text-muted small">Uptime</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Settings Categories -->
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-primary">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-users fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">User Management</h5>
                            <p class="card-text">Manage system users, roles, permissions, and access control.</p>
                            <div class="d-grid gap-2">
                                <a href="<?= $baseUrl ?>/users" class="btn btn-primary">
                                    <i class="fas fa-users me-2"></i>Manage Users
                                </a>
                                <a href="<?= $baseUrl ?>/settings/users" class="btn btn-outline-primary">
                                    <i class="fas fa-cog me-2"></i>User Settings
                                </a>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Total Users: <?= $systemStats['totalUsers'] ?? 0 ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-success">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-cogs fa-3x text-success"></i>
                            </div>
                            <h5 class="card-title">System Configuration</h5>
                            <p class="card-text">Configure system settings, preferences, and operational parameters.</p>
                            <div class="d-grid gap-2">
                                <a href="<?= $baseUrl ?>/settings/system" class="btn btn-success">
                                    <i class="fas fa-cogs me-2"></i>System Settings
                                </a>
                                <button class="btn btn-outline-success" disabled>
                                    <i class="fas fa-clock me-2"></i>Coming Soon
                                </button>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>System Status: Active</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-info">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-database fa-3x text-info"></i>
                            </div>
                            <h5 class="card-title">Database Settings</h5>
                            <p class="card-text">Manage database configuration, backups, and maintenance tasks.</p>
                            <div class="d-grid gap-2">
                                <a href="<?= $baseUrl ?>/settings/database" class="btn btn-info">
                                    <i class="fas fa-database me-2"></i>Database Settings
                                </a>
                                <button class="btn btn-outline-info" disabled>
                                    <i class="fas fa-download me-2"></i>Backup/Restore
                                </button>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Database: MySQL</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-warning">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-shield-alt fa-3x text-warning"></i>
                            </div>
                            <h5 class="card-title">Security Settings</h5>
                            <p class="card-text">Configure security policies, authentication, and access controls.</p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-warning" disabled>
                                    <i class="fas fa-shield-alt me-2"></i>Security Settings
                                </button>
                                <button class="btn btn-outline-warning" disabled>
                                    <i class="fas fa-lock me-2"></i>Access Control
                                </button>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Security Level: High</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-secondary">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-chart-line fa-3x text-secondary"></i>
                            </div>
                            <h5 class="card-title">Performance Monitoring</h5>
                            <p class="card-text">Monitor system performance, logs, and operational metrics.</p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-secondary" disabled>
                                    <i class="fas fa-chart-line me-2"></i>Performance
                                </button>
                                <button class="btn btn-outline-secondary" disabled>
                                    <i class="fas fa-list-alt me-2"></i>System Logs
                                </button>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Monitoring: Active</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-dark">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-tools fa-3x text-dark"></i>
                            </div>
                            <h5 class="card-title">Maintenance Tools</h5>
                            <p class="card-text">System maintenance, updates, and troubleshooting tools.</p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-dark" disabled>
                                    <i class="fas fa-tools me-2"></i>Maintenance
                                </button>
                                <button class="btn btn-outline-dark" disabled>
                                    <i class="fas fa-sync me-2"></i>Updates
                                </button>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Last Update: Today</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="<?= $baseUrl ?>/users" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-user-plus me-2"></i>Add New User
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button class="btn btn-outline-success w-100" disabled>
                                        <i class="fas fa-download me-2"></i>Export Data
                                    </button>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button class="btn btn-outline-warning w-100" disabled>
                                        <i class="fas fa-cog me-2"></i>System Check
                                    </button>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button class="btn btn-outline-info w-100" disabled>
                                        <i class="fas fa-chart-bar me-2"></i>View Logs
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 