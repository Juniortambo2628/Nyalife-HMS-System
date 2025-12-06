<?php
/**
 * Nyalife HMS - Nurse Dashboard
 */

$showSidebar = true;
$pageTitle = 'Nurse Dashboard - Nyalife HMS';
?>
<div class="container-fluid page-wrapper">
    <h1 class="h3 mb-4">Nurse Dashboard</h1>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Today's Appointments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($todayAppointments) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
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
                                Upcoming Appointments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($upcomingAppointments) ?></div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($pendingAppointments ?? []) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Today's Schedule -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Today's Schedule</h6>
                    <div class="card-header-actions">
                        <div class="btn-group-desktop">
                            <a href="<?= $baseUrl ?>/appointments/calendar" class="btn btn-sm">
                                <i class="fas fa-calendar me-1"></i> <span class="d-none d-sm-inline">View Calendar</span>
                            </a>
                        </div>
                        <div class="dropdown">
                            <button class="card-header-menu-toggle dropdown-toggle" type="button" id="scheduleMenuToggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="scheduleMenuToggle">
                                <li><a class="dropdown-item" href="<?= $baseUrl ?>/appointments/calendar"><i class="fas fa-calendar me-2"></i> View Calendar</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($todayAppointments)): ?>
                        <div class="text-center p-4">
                            <img src="<?= $baseUrl ?>/assets/img/illustrations/no-appointments.svg" alt="No appointments" class="img-fluid mb-3 img-max-150 img-error-handler" data-error-icon="fas fa-calendar-times">
                            <p class="text-muted">No appointments scheduled for today.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($todayAppointments as $appointment): ?>
                                        <tr>
                                            <td><?= date('h:i A', strtotime($appointment['appointment_time'])) ?></td>
                                            <td>
                                                <a href="<?= $baseUrl ?>/patients/view/<?= $appointment['patient_id'] ?>">
                                                    <?= htmlspecialchars($appointment['patient_name']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($appointment['doctor_name']) ?>
                                            </td>
                                            <td><?= htmlspecialchars($appointment['reason']) ?></td>
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
                                            case 'no_show':
                                                $statusClass = 'bg-warning text-dark';
                                                break;
                                            default:
                                                $statusClass = 'bg-secondary';
                                        }
                                        ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $appointment['status'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= $baseUrl ?>/appointments/view/<?= $appointment['appointment_id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if ($appointment['status'] == 'scheduled'): ?>
                                                <a href="<?= $baseUrl ?>/appointments/check-in/<?= $appointment['appointment_id'] ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <?php endif; ?>
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
        
        <!-- Quick Actions & Info -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= $baseUrl ?>/appointments/create" class="btn btn-primary">
                            <i class="fas fa-calendar-plus me-2"></i> Schedule Appointment
                        </a>
                        <a href="<?= $baseUrl ?>/patients" class="btn btn-info">
                            <i class="fas fa-user-injured me-2"></i> View Patients
                        </a>
                        <a href="<?= $baseUrl ?>/vitals/record" class="btn btn-success">
                            <i class="fas fa-heartbeat me-2"></i> Record Vitals
                        </a>
                        <a href="<?= $baseUrl ?>/lab-requests/new" class="btn btn-warning">
                            <i class="fas fa-flask me-2"></i> Request Lab Test
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">My Information</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="profile-image-container">
                            <img src="<?= $baseUrl ?>/assets/img/profiles/default-nurse.png" class="img-profile" onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'fas fa-user-nurse\'></i>';">
                        </div>
                        <h5 class="mt-2"><?= htmlspecialchars($currentUser['firstName'] . ' ' . $currentUser['lastName']) ?></h5>
                        <p class="text-muted">
                            <i class="fas fa-user-nurse me-1"></i> Nurse
                        </p>
                    </div>
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-envelope me-2"></i> Email</span>
                            <span class="text-muted"><?= htmlspecialchars($currentUser['email'] ?? '-') ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-phone me-2"></i> Phone</span>
                            <span class="text-muted"><?= htmlspecialchars($currentUser['phone'] ?? '-') ?></span>
                        </li>
                    </ul>
                    
                    <div class="mt-3 text-center">
                        <a href="<?= $baseUrl ?>/profile" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user-edit me-1"></i> Edit Profile
                        </a>
                    </div>
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
                    <div class="card-header-actions">
                        <div class="btn-group-desktop">
                            <a href="<?= $baseUrl ?>/messages/compose" class="btn btn-sm me-2">
                                <i class="fas fa-plus me-1"></i> <span class="d-none d-sm-inline">Compose</span>
                            </a>
                            <a href="<?= $baseUrl ?>/messages" class="btn btn-sm">
                                View All
                            </a>
                        </div>
                        <div class="dropdown">
                            <button class="card-header-menu-toggle dropdown-toggle" type="button" id="messagesMenuToggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="messagesMenuToggle">
                                <li><a class="dropdown-item" href="<?= $baseUrl ?>/messages/compose"><i class="fas fa-plus me-2"></i> Compose</a></li>
                                <li><a class="dropdown-item" href="<?= $baseUrl ?>/messages"><i class="fas fa-envelope me-2"></i> View All</a></li>
                            </ul>
                        </div>
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
    
    <!-- Upcoming Appointments -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Upcoming Appointments</h6>
                    <div class="card-header-actions">
                        <div class="btn-group-desktop">
                            <a href="<?= $baseUrl ?>/appointments" class="btn btn-sm">
                                View All
                            </a>
                        </div>
                        <div class="dropdown">
                            <button class="card-header-menu-toggle dropdown-toggle" type="button" id="appointmentsMenuToggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="appointmentsMenuToggle">
                                <li><a class="dropdown-item" href="<?= $baseUrl ?>/appointments"><i class="fas fa-list me-2"></i> View All</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($upcomingAppointments)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No upcoming appointments found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcomingAppointments as $appointment): ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($appointment['appointment_date'])) ?></td>
                                            <td><?= date('h:i A', strtotime($appointment['appointment_time'])) ?></td>
                                            <td>
                                                <a href="<?= $baseUrl ?>/patients/view/<?= $appointment['patient_id'] ?>">
                                                    <?= htmlspecialchars($appointment['patient_name']) ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($appointment['doctor_name']) ?></td>
                                            <td><?= htmlspecialchars($appointment['reason']) ?></td>
                                            <td>
                                                <?php
                                        $statusClass = '';
                                        switch ($appointment['status']) {
                                            case 'scheduled':
                                                $statusClass = 'bg-primary';
                                                break;
                                            case 'pending':
                                                $statusClass = 'bg-warning text-dark';
                                                break;
                                            default:
                                                $statusClass = 'bg-secondary';
                                        }
                                        ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $appointment['status'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= $baseUrl ?>/appointments/view/<?= $appointment['appointment_id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= $baseUrl ?>/appointments/edit/<?= $appointment['appointment_id'] ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
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
</div>

<style>
/* Dashboard Messages Card Styles */
.message-item {
    transition: background-color 0.2s ease;
}

.message-item:hover {
    background-color: var(--bs-gray-50, #f8f9fa);
}

.message-item.unread {
    background-color: rgba(32, 201, 151, 0.05);
}

.message-item.unread:hover {
    background-color: rgba(32, 201, 151, 0.1);
}

.message-item:last-child {
    border-bottom: none !important;
}

.message-avatar .rounded-circle {
    background: linear-gradient(135deg, var(--primary-color, #20c997) 0%, var(--secondary-color, #e91e63) 100%) !important;
    font-weight: 600;
}

.message-indicator {
    background-color: var(--info, #0dcaf0) !important;
}

/* Dashboard card header gradient */
.card-header[style*="gradient"] {
    border-bottom: none;
}

.card-header[style*="gradient"] .btn-light {
    background-color: rgba(255, 255, 255, 0.9);
    border-color: rgba(255, 255, 255, 0.9);
    color: var(--primary-color, #20c997);
}

.card-header[style*="gradient"] .btn-light:hover {
    background-color: white;
    border-color: white;
}

.card-header[style*="gradient"] .btn-outline-light:hover {
    background-color: rgba(255, 255, 255, 0.1);
}
</style>

<!-- Bundled Assets -->
<link rel="stylesheet" href="<?= AssetHelper::getCss('shared') ?>">
<script src="<?= AssetHelper::getJs('runtime') ?>"></script>
<script src="<?= AssetHelper::getJs('vendors') ?>"></script>
<script src="<?= AssetHelper::getJs('shared') ?>"></script>
<script src="<?= AssetHelper::getJs('app') ?>"></script>
<script src="<?= AssetHelper::getJs('dashboard-nurse') ?>"></script>
</body>
</html>
