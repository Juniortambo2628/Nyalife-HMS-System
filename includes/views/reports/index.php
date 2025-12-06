<?php
/**
 * Nyalife HMS - Reports Index View
 *
 * System reports page.
 */
$pageTitle = 'Reports - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-chart-bar text-primary me-2"></i>
                    System Reports
                </h1>
                <a href="<?= $baseUrl ?>/dashboard" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
            
            <!-- Statistics Overview -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>System Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-primary mb-1"><?= $stats['totalUsers'] ?? 0 ?></div>
                                        <div class="text-muted">Total Users</div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-success mb-1"><?= $stats['totalPatients'] ?? 0 ?></div>
                                        <div class="text-muted">Patients</div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-info mb-1"><?= $stats['totalDoctors'] ?? 0 ?></div>
                                        <div class="text-muted">Doctors</div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-warning mb-1"><?= $stats['totalNurses'] ?? 0 ?></div>
                                        <div class="text-muted">Nurses</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-primary mb-1"><?= $stats['totalAppointments'] ?? 0 ?></div>
                                        <div class="text-muted">Total Appointments</div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-warning mb-1"><?= $stats['pendingAppointments'] ?? 0 ?></div>
                                        <div class="text-muted">Pending</div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-success mb-1"><?= $stats['completedAppointments'] ?? 0 ?></div>
                                        <div class="text-muted">Completed</div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-info mb-1"><?= $stats['totalConsultations'] ?? 0 ?></div>
                                        <div class="text-muted">Consultations</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-secondary mb-1"><?= $stats['totalLabTests'] ?? 0 ?></div>
                                        <div class="text-muted">Lab Tests</div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-dark mb-1"><?= $stats['totalInvoices'] ?? 0 ?></div>
                                        <div class="text-muted">Invoices</div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-success mb-1">$<?= number_format($stats['totalRevenue'] ?? 0, 2) ?></div>
                                        <div class="text-muted">Total Revenue</div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-info mb-1"><?= date('M Y') ?></div>
                                        <div class="text-muted">Current Period</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Categories -->
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-primary">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-calendar-check fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">Appointment Reports</h5>
                            <p class="card-text">Generate detailed reports on appointments, scheduling, and patient flow.</p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-primary"><?= $stats['totalAppointments'] ?? 0 ?> Total</span>
                                <span class="badge bg-warning"><?= $stats['pendingAppointments'] ?? 0 ?> Pending</span>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="<?= $baseUrl ?>/reports/appointments" class="btn btn-primary">
                                    <i class="fas fa-chart-line me-2"></i>View Reports
                                </a>
                                <button class="btn btn-outline-primary" disabled>
                                    <i class="fas fa-download me-2"></i>Export Data
                                </button>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Last Updated: <?= date('M d, Y H:i') ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-success">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-injured fa-3x text-success"></i>
                            </div>
                            <h5 class="card-title">Patient Reports</h5>
                            <p class="card-text">Generate patient statistics, demographics, and medical history reports.</p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-success"><?= $stats['totalPatients'] ?? 0 ?> Patients</span>
                                <span class="badge bg-info">Demographics</span>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="<?= $baseUrl ?>/reports/patients" class="btn btn-success">
                                    <i class="fas fa-chart-pie me-2"></i>View Reports
                                </a>
                                <button class="btn btn-outline-success" disabled>
                                    <i class="fas fa-download me-2"></i>Export Data
                                </button>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Last Updated: <?= date('M d, Y H:i') ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-info">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-microscope fa-3x text-info"></i>
                            </div>
                            <h5 class="card-title">Laboratory Reports</h5>
                            <p class="card-text">Generate laboratory test reports, results analysis, and quality metrics.</p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-info"><?= $stats['totalLabTests'] ?? 0 ?> Tests</span>
                                <span class="badge bg-warning">Quality</span>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="<?= $baseUrl ?>/reports/laboratory" class="btn btn-info">
                                    <i class="fas fa-flask me-2"></i>View Reports
                                </a>
                                <button class="btn btn-outline-info" disabled>
                                    <i class="fas fa-download me-2"></i>Export Data
                                </button>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Last Updated: <?= date('M d, Y H:i') ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-warning">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-pills fa-3x text-warning"></i>
                            </div>
                            <h5 class="card-title">Pharmacy Reports</h5>
                            <p class="card-text">Generate pharmacy inventory, prescription, and medication reports.</p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-warning">Inventory</span>
                                <span class="badge bg-success">Prescriptions</span>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="<?= $baseUrl ?>/reports/pharmacy" class="btn btn-warning">
                                    <i class="fas fa-pills me-2"></i>View Reports
                                </a>
                                <button class="btn btn-outline-warning" disabled>
                                    <i class="fas fa-download me-2"></i>Export Data
                                </button>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Last Updated: <?= date('M d, Y H:i') ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-secondary">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-chart-line fa-3x text-secondary"></i>
                            </div>
                            <h5 class="card-title">Financial Reports</h5>
                            <p class="card-text">Generate financial reports, billing analysis, and revenue tracking.</p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-secondary">Revenue</span>
                                <span class="badge bg-success">$<?= number_format($stats['totalRevenue'] ?? 0, 2) ?></span>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="<?= $baseUrl ?>/reports/financial" class="btn btn-secondary">
                                    <i class="fas fa-dollar-sign me-2"></i>View Reports
                                </a>
                                <button class="btn btn-outline-secondary" disabled>
                                    <i class="fas fa-download me-2"></i>Export Data
                                </button>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Last Updated: <?= date('M d, Y H:i') ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-dark">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-chart-bar fa-3x text-dark"></i>
                            </div>
                            <h5 class="card-title">Analytics Dashboard</h5>
                            <p class="card-text">View comprehensive system analytics, trends, and performance metrics.</p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-dark">Analytics</span>
                                <span class="badge bg-info">Trends</span>
                            </div>
                            <div class="d-grid gap-2">
                                <button class="btn btn-dark" disabled>
                                    <i class="fas fa-chart-bar me-2"></i>View Analytics
                                </button>
                                <button class="btn btn-outline-dark" disabled>
                                    <i class="fas fa-download me-2"></i>Export Data
                                </button>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Coming Soon</small>
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
                                    <button class="btn btn-outline-primary w-100" disabled>
                                        <i class="fas fa-calendar me-2"></i>Daily Report
                                    </button>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button class="btn btn-outline-success w-100" disabled>
                                        <i class="fas fa-calendar-week me-2"></i>Weekly Report
                                    </button>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button class="btn btn-outline-info w-100" disabled>
                                        <i class="fas fa-calendar-alt me-2"></i>Monthly Report
                                    </button>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button class="btn btn-outline-warning w-100" disabled>
                                        <i class="fas fa-download me-2"></i>Export All
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