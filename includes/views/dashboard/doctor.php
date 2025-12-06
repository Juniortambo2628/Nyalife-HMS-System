<div class="container-fluid px-4 py-3">
    <h1 class="h3 mb-4">Doctor Dashboard</h1>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold mb-1">
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
                                My Patients</div>
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
    
    <!-- Today's Schedule -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Today's Schedule</h6>
                    <a href="<?= $baseUrl ?>/appointments/calendar" class="btn btn-sm btn-primary">
                        <i class="fas fa-calendar me-1"></i> View Calendar
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($todayAppointments)): ?>
                        <div class="text-center py-4">
                            <img src="<?= $baseUrl ?>/assets/img/illustrations/no-appointments.png" alt="No appointments" class="img-fluid mb-3" style="max-width: 200px;">
                            <p class="mb-0">You have no appointments scheduled for today.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Patient</th>
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
                                                <div class="small text-muted">
                                                    <?= ucfirst($appointment['patient_gender']) ?>, 
                                                    <?php
                                                    $dob = new DateTime($appointment['patient_dob']);
                                                    $now = new DateTime();
                                                    $age = $now->diff($dob)->y;
                                                    echo $age . ' years';
                                                    ?>
                                                </div>
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
                                            <td>
                                                <a href="<?= $baseUrl ?>/appointments/view/<?= $appointment['appointment_id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if ($appointment['status'] == 'scheduled'): ?>
                                                <a href="<?= $baseUrl ?>/appointments/start/<?= $appointment['appointment_id'] ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-play"></i>
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
        
        <!-- Quick Actions & Doctor Info -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= $baseUrl ?>/appointments/create" class="btn btn-primary">
                            <i class="fas fa-calendar-plus me-2"></i> Schedule Appointment
                        </a>
                        <a href="<?= $baseUrl ?>/patients" class="btn btn-info">
                            <i class="fas fa-user-injured me-2"></i> Manage Patients
                        </a>
                        <a href="<?= $baseUrl ?>/prescriptions" class="btn btn-success">
                            <i class="fas fa-prescription-bottle-alt me-2"></i> Manage Prescriptions
                        </a>
                        <a href="<?= $baseUrl ?>/lab-requests" class="btn btn-warning">
                            <i class="fas fa-flask me-2"></i> Lab Requests
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">My Information</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="<?= $baseUrl ?>/assets/img/profiles/default-doctor.png" class="img-profile rounded-circle" width="100">
                        <h5 class="mt-2">Dr. <?= htmlspecialchars($currentUser['firstName'] . ' ' . $currentUser['lastName']) ?></h5>
                        <p class="text-muted">
                            <i class="fas fa-user-md me-1"></i> <?= htmlspecialchars($currentUser['specialization'] ?? 'General Practitioner') ?>
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
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-id-card me-2"></i> License #</span>
                            <span class="text-muted"><?= htmlspecialchars($currentUser['licenseNumber'] ?? '-') ?></span>
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
    
    <!-- Upcoming Appointments -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Upcoming Appointments</h6>
                    <a href="<?= $baseUrl ?>/appointments" class="btn btn-sm btn-primary">
                        View All
                    </a>
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
                                                    <?= ucfirst($appointment['status']) ?>
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
