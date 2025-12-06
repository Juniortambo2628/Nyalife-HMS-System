<?php
/**
 * Nyalife HMS - Admin Dashboard
 */

$showSidebar = true;
$pageTitle = 'Admin Dashboard - Nyalife HMS';
$pageSpecificScripts[] = AssetHelper::getJs('dashboard-admin');
?>
<div class="container-fluid page-wrapper">
    <h1 class="h3 mb-4">Administrator Dashboard</h1>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $userCount ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Patients</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $patientCount ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-injured fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Appointments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $appointmentCount ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Appointments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pendingAppointments ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Appointment Overview</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area chart-container">
                        <canvas id="appointmentTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Patient Demographics</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2 chart-container">
                        <canvas id="patientDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content Row -->
    <div class="row">
        <!-- Recent Users -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Users</h6>
                    <div class="card-header-actions">
                        <div class="btn-group-desktop">
                            <a href="<?= $baseUrl ?>/users" class="btn btn-sm">View All</a>
                        </div>
                        <div class="dropdown">
                            <button class="card-header-menu-toggle dropdown-toggle" type="button" id="usersMenuToggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="usersMenuToggle">
                                <li><a class="dropdown-item" href="<?= $baseUrl ?>/users"><i class="fas fa-list me-2"></i> View All</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($recentUsers)): ?>
                        <p class="text-center">No recent users found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="recentUsersTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Email</th>
                                        <th>Registered</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentUsers as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($user['role_name']) ?></span></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Recent Patients -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Patients</h6>
                    <a href="<?= $baseUrl ?>/patients" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentPatients)): ?>
                        <p class="text-center">No recent patients found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="recentPatientsTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Gender</th>
                                        <th>Age</th>
                                        <th>Phone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentPatients as $patient): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= $baseUrl ?>/patients/view/<?= $patient['patient_id'] ?>">
                                                    <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars(ucfirst($patient['gender'])) ?></td>
                                            <td>
                                                <?php
                                                $dob = new DateTime($patient['date_of_birth']);
                                        $now = new DateTime();
                                        $age = $now->diff($dob)->y;
                                        echo $age;
                                        ?>
                                            </td>
                                            <td><?= htmlspecialchars($patient['phone']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Messages Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-gradient-primary-secondary">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-envelope me-2"></i>Recent Messages
                    </h6>
                    <div>
                        <a href="<?= $baseUrl ?>/messages/compose" class="btn btn-sm btn-light me-2">
                            <i class="fas fa-plus me-1"></i> Compose
                        </a>
                        <a href="<?= $baseUrl ?>/messages" class="btn btn-sm btn-outline-light">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="dashboard-messages-container">
                        <div class="text-center p-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading messages...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading messages...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Appointments -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Appointments</h6>
                    <a href="<?= $baseUrl ?>/appointments" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentAppointments)): ?>
                        <p class="text-center">No recent appointments found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="recentAppointmentsTable">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentAppointments as $appointment): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= $baseUrl ?>/patients/view/<?= $appointment['patient_id'] ?>">
                                                    <?= htmlspecialchars($appointment['patient_name']) ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($appointment['doctor_name']) ?></td>
                                            <td><?= date('M d, Y', strtotime($appointment['appointment_date'])) ?></td>
                                            <td><?= date('h:i A', strtotime($appointment['appointment_time'])) ?></td>
                                            <td>
                                                <?php
                                        $statusClass = '';
                                        switch ($appointment['status']) {
                                            case 'scheduled':
                                                $statusClass = 'bg-primary';
                                                break;
                                            case 'completed':
                                                $statusClass = 'bg-success';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'bg-danger';
                                                break;
                                            case 'pending':
                                                $statusClass = 'bg-warning text-dark';
                                                break;
                                            default:
                                                $statusClass = 'bg-secondary';
                                        }
                                        ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <?= ucfirst($appointment['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="<?= $baseUrl ?>/users/create" class="btn btn-primary btn-block w-100">
                                <i class="fas fa-user-plus me-2"></i> Add New User
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= $baseUrl ?>/patients/create" class="btn btn-info btn-block w-100">
                                <i class="fas fa-hospital-user me-2"></i> Register Patient
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= $baseUrl ?>/appointments/create" class="btn btn-success btn-block w-100">
                                <i class="fas fa-calendar-plus me-2"></i> Schedule Appointment
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= $baseUrl ?>/reports" class="btn btn-warning btn-block w-100">
                                <i class="fas fa-chart-bar me-2"></i> Generate Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
