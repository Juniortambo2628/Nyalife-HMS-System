<?php
/**
 * Nyalife HMS - Patient Dashboard
 */

$pageTitle = 'Patient Dashboard - Nyalife HMS';
?>
<div class="container-fluid page-wrapper">
    <h1 class="h3 mb-4">Patient Dashboard</h1>
    
    <!-- Welcome Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-2">Welcome, <?= htmlspecialchars($currentUser['firstName'] . ' ' . $currentUser['lastName']) ?></h4>
                            <p class="mb-0">
                                Here you can manage your appointments, view your medical history, and keep track of your health information.
                                If you need any assistance, please contact our support team.
                            </p>
                        </div>
                        <div class="ms-3">
                            <span class="bg-primary p-3 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="fas fa-user fa-3x text-white"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="<?= $baseUrl ?>/appointments/create" class="btn btn-primary btn-block w-100">
                                <i class="fas fa-calendar-plus me-2"></i> New Appointment
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= $baseUrl ?>/profile" class="btn btn-info btn-block w-100">
                                <i class="fas fa-user-edit me-2"></i> Update Profile
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= $baseUrl ?>/messages" class="btn btn-success btn-block w-100">
                                <i class="fas fa-envelope me-2"></i> Messages
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= $baseUrl ?>/medical-history" class="btn btn-warning btn-block w-100">
                                <i class="fas fa-file-medical me-2"></i> Medical Records
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content Row -->
    <div class="row">
        <!-- Upcoming Appointments -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
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
                        <div class="text-center p-4">
                            <img src="<?= $baseUrl ?>/assets/img/illustrations/no-appointments.svg" alt="No appointments" class="mb-3 img-max-150">
                            <p class="text-muted">You have no upcoming appointments.</p>
                            <a href="<?= $baseUrl ?>/appointments/create" class="btn btn-sm btn-primary">Book an Appointment</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="upcomingAppointmentsTable">
                                <thead>
                                    <tr>
                                        <th>Doctor</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcomingAppointments as $appointment): ?>
                                        <tr>
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
                                                <?php if ($appointment['status'] == 'scheduled' || $appointment['status'] == 'pending'): ?>
                                                <a href="<?= $baseUrl ?>/appointments/reschedule/<?= $appointment['appointment_id'] ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-clock"></i>
                                                </a>
                                                <a href="<?= $baseUrl ?>/appointments/cancel/<?= $appointment['appointment_id'] ?>" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-times"></i>
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
        
        <!-- Lab Results -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Lab Results</h6>
                    <a href="<?= $baseUrl ?>/lab-results" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($labResults)): ?>
                        <div class="text-center p-4">
                            <img src="<?= $baseUrl ?>/assets/img/illustrations/no-appointments.svg" alt="No lab results" class="mb-3 img-max-150">
                            <p class="text-muted">You have no recent lab results.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="labResultsTable">
                                <thead>
                                    <tr>
                                        <th>Test Name</th>
                                        <th>Date</th>
                                        <th>Result</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($labResults as $result): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($result['test_name']) ?></td>
                                            <td><?= date('M d, Y', strtotime($result['test_date'])) ?></td>
                                            <td><?= $result['result_status'] == 'completed' ? htmlspecialchars($result['result_value']) : 'Pending' ?></td>
                                            <td>
                                                <?php
                                        $statusClass = '';
                                        switch ($result['result_status']) {
                                            case 'completed':
                                                $statusClass = 'bg-success';
                                                break;
                                            case 'pending':
                                                $statusClass = 'bg-warning text-dark';
                                                break;
                                            default:
                                                $statusClass = 'bg-secondary';
                                        }
                                        ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <?= ucfirst($result['result_status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= $baseUrl ?>/lab-results/view/<?= $result['result_id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($result['result_status'] == 'completed'): ?>
                                                <a href="<?= $baseUrl ?>/lab-results/download/<?= $result['result_id'] ?>" class="btn btn-sm btn-primary" data-no-ajax>
                                                    <i class="fas fa-download"></i>
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
    </div>
    
    <div class="row">
        <!-- Prescriptions -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Prescriptions</h6>
                    <a href="<?= $baseUrl ?>/prescriptions" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($prescriptions)): ?>
                        <div class="text-center p-4">
                            <img src="<?= $baseUrl ?>/assets/img/illustrations/no-appointments.svg" alt="No prescriptions" class="mb-3 img-max-150">
                            <p class="text-muted">You have no recent prescriptions.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="prescriptionsTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Doctor</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($prescriptions as $prescription): ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($prescription['prescription_date'])) ?></td>
                                            <td><?= htmlspecialchars($prescription['doctor_name']) ?></td>
                                            <td>
                                                <?php
                                        $statusClass = '';
                                        switch ($prescription['status']) {
                                            case 'filled':
                                                $statusClass = 'bg-success';
                                                break;
                                            case 'pending':
                                                $statusClass = 'bg-warning text-dark';
                                                break;
                                            default:
                                                $statusClass = 'bg-secondary';
                                        }
                                        ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <?= ucfirst($prescription['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= $baseUrl ?>/prescriptions/view/<?= $prescription['prescription_id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($prescription['status'] == 'filled'): ?>
                                                <a href="<?= $baseUrl ?>/prescriptions/download/<?= $prescription['prescription_id'] ?>" class="btn btn-sm btn-primary" data-no-ajax>
                                                    <i class="fas fa-download"></i>
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
        
        <!-- Medical History -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Medical History</h6>
                    <a href="<?= $baseUrl ?>/medical-history" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($medicalHistory)): ?>
                        <div class="text-center p-4">
                            <img src="<?= $baseUrl ?>/assets/img/illustrations/no-appointments.svg" alt="No medical history" class="mb-3 img-max-150">
                            <p class="text-muted">No medical history found.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="medicalHistoryTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Doctor</th>
                                        <th>Type</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($medicalHistory as $record): ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($record['record_date'])) ?></td>
                                            <td><?= htmlspecialchars($record['doctor_name']) ?></td>
                                            <td><?= htmlspecialchars($record['record_type']) ?></td>
                                            <td>
                                                <a href="<?= $baseUrl ?>/medical-history/view/<?= $record['record_id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
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

<!-- Bundled Assets -->
<link rel="stylesheet" href="<?= AssetHelper::getCss('shared') ?>">
<script src="<?= AssetHelper::getJs('runtime') ?>"></script>
<script src="<?= AssetHelper::getJs('vendors') ?>"></script>
<script src="<?= AssetHelper::getJs('shared') ?>"></script>
<script src="<?= AssetHelper::getJs('app') ?>"></script>
<script src="<?= AssetHelper::getJs('dashboard-patient') ?>"></script>
</body>
</html>