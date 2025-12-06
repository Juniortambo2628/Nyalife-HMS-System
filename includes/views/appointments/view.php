<?php
/**
 * Nyalife HMS - Appointment Details View
 */

$pageTitle = 'Appointment Details - Nyalife HMS';
?>
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Appointment Details</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="<?= $baseUrl ?>/appointments" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Appointments
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($successMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($errorMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Appointment Summary -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Appointment Summary</h6>
                        <div>
                            <?php if ($appointment['status'] != 'cancelled' && $appointment['status'] != 'completed'): ?>
                                <?php if ($userRole == 'admin' || $userRole == 'doctor'): ?>
                                    <a href="<?= $baseUrl ?>/appointments/edit/<?= $appointment['appointment_id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                <?php endif; ?>
                                <?php if (in_array($userRole ?? '', ['nurse','admin','doctor'])): ?>
                                    <a href="<?= $baseUrl ?>/vitals/create/<?= $appointment['patient_id'] ?>?appointment_id=<?= $appointment['appointment_id'] ?>&return=<?= urlencode($baseUrl . '/appointments/view/' . $appointment['appointment_id']) ?>" class="btn btn-sm btn-success ms-2">
                                        <i class="fas fa-heartbeat me-1"></i> Record Vitals
                                    </a>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
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
                            <span class="badge <?= $statusClass ?> fs-6 mb-3 d-inline-block">
                                <?= ucfirst($appointment['status']) ?>
                            </span>
                            
                            <div class="row mb-2">
                                <div class="col-md-4 fw-bold">Appointment ID:</div>
                                <div class="col-md-8"><?= $appointment['appointment_id'] ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4 fw-bold">Date & Time:</div>
                                <div class="col-md-8">
                                    <?= date('F d, Y', strtotime($appointment['appointment_date'])) ?> at 
                                    <?= date('h:i A', strtotime($appointment['appointment_time'])) ?>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="border-bottom pb-2 mb-3">Patient Information</h6>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Patient Name:</div>
                            <div class="col-md-8">
                                <a href="<?= $baseUrl ?>/patients/view/<?= $appointment['patient_id'] ?>">
                                    <?= htmlspecialchars($appointment['patient_name']) ?>
                                </a>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Gender:</div>
                            <div class="col-md-8"><?= ucfirst($appointment['patient_gender']) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Age:</div>
                            <div class="col-md-8">
                                <?php
    $dob = new DateTime($appointment['patient_dob']);
$now = new DateTime();
$age = $now->diff($dob)->y;
echo $age . ' years';
?>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Phone:</div>
                            <div class="col-md-8"><?= htmlspecialchars($appointment['patient_phone'] ?? 'N/A') ?></div>
                        </div>
                        
                        <h6 class="border-bottom pb-2 mb-3 mt-4">Doctor Information</h6>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Doctor Name:</div>
                            <div class="col-md-8">Dr. <?= htmlspecialchars($appointment['doctor_name']) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Specialization:</div>
                            <div class="col-md-8"><?= htmlspecialchars($appointment['doctor_specialization'] ?? 'N/A') ?></div>
                        </div>
                        
                        <h6 class="border-bottom pb-2 mb-3 mt-4">Appointment Details</h6>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Reason:</div>
                            <div class="col-md-8"><?= htmlspecialchars($appointment['reason']) ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4 fw-bold">Created:</div>
                            <div class="col-md-8"><?= date('M d, Y, h:i A', strtotime($appointment['created_at'])) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Medical History -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Medical History</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($medicalHistory)): ?>
                            <?php if ($userRole == 'doctor' && $appointment['status'] == 'scheduled'): ?>
                                <form action="<?= $baseUrl ?>/appointments/<?= $appointment['appointment_id'] ?>/medical-history" method="post" id="medical-history-form">
                                    <div class="mb-3">
                                        <label for="diagnosis" class="form-label">Diagnosis</label>
                                        <textarea class="form-control" id="diagnosis" name="diagnosis" rows="2" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="treatment" class="form-label">Treatment</label>
                                        <textarea class="form-control" id="treatment" name="treatment" rows="2" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Save Medical History</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> No medical history recorded for this appointment.
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="row mb-2">
                                <div class="col-md-4 fw-bold">Diagnosis:</div>
                                <div class="col-md-8"><?= htmlspecialchars($medicalHistory['diagnosis']) ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4 fw-bold">Treatment:</div>
                                <div class="col-md-8"><?= htmlspecialchars($medicalHistory['treatment']) ?></div>
                            </div>
                            <?php if (!empty($medicalHistory['notes'])): ?>
                            <div class="row mb-2">
                                <div class="col-md-4 fw-bold">Notes:</div>
                                <div class="col-md-8"><?= nl2br(htmlspecialchars($medicalHistory['notes'])) ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="row mb-2">
                                <div class="col-md-4 fw-bold">Recorded By:</div>
                                <div class="col-md-8">Dr. <?= htmlspecialchars($medicalHistory['doctor_name']) ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4 fw-bold">Date:</div>
                                <div class="col-md-8"><?= date('M d, Y, h:i A', strtotime($medicalHistory['created_at'])) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($userRole == 'doctor' || $userRole == 'admin'): ?>
                <!-- Quick Actions -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="<?= $baseUrl ?>/prescriptions/create?appointment_id=<?= $appointment['appointment_id'] ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-prescription me-2"></i> Add Prescription
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="<?= $baseUrl ?>/lab/tests/create?appointment_id=<?= $appointment['appointment_id'] ?>" class="btn btn-info w-100">
                                    <i class="fas fa-flask me-2"></i> Order Lab Test
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Prescriptions and Lab Results Tabs -->
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <ul class="nav nav-tabs card-header-tabs" id="appointmentDetailsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="prescriptions-tab" data-bs-toggle="tab" data-bs-target="#prescriptions" type="button" role="tab" aria-controls="prescriptions" aria-selected="true">
                            <i class="fas fa-prescription-bottle-alt me-1"></i> Prescriptions
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="lab-tests-tab" data-bs-toggle="tab" data-bs-target="#lab-tests" type="button" role="tab" aria-controls="lab-tests" aria-selected="false">
                            <i class="fas fa-vial me-1"></i> Lab Tests
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="appointmentDetailsTabsContent">
                    <!-- Prescriptions Tab -->
                    <div class="tab-pane fade show active" id="prescriptions" role="tabpanel" aria-labelledby="prescriptions-tab">
                        <?php if (empty($prescriptions)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> No prescriptions found for this appointment.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover datatable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Medication</th>
                                            <th>Dosage</th>
                                            <th>Instructions</th>
                                            <th>Status</th>
                                            <th>Prescribed On</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($prescriptions as $prescription): ?>
                                            <tr>
                                                <td><?= $prescription['prescription_id'] ?></td>
                                                <td><?= htmlspecialchars($prescription['medication']) ?></td>
                                                <td><?= htmlspecialchars($prescription['dosage']) ?></td>
                                                <td><?= htmlspecialchars($prescription['instructions']) ?></td>
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
                                                case 'cancelled':
                                                    $statusClass = 'bg-danger';
                                                    break;
                                                default:
                                                    $statusClass = 'bg-secondary';
                                            }
                                            ?>
                                                    <span class="badge <?= $statusClass ?>">
                                                        <?= ucfirst($prescription['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('M d, Y', strtotime($prescription['created_at'])) ?></td>
                                                <td>
                                                    <a href="<?= $baseUrl ?>/prescriptions/view/<?= $prescription['prescription_id'] ?>" class="btn btn-sm btn-info">
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
                    
                    <!-- Lab Tests Tab -->
                    <div class="tab-pane fade" id="lab-tests" role="tabpanel" aria-labelledby="lab-tests-tab">
                        <?php if (empty($labTests)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> No lab tests found for this appointment.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover datatable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Test Name</th>
                                            <th>Status</th>
                                            <th>Ordered On</th>
                                            <th>Results</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($labTests as $test): ?>
                                            <tr>
                                                <td><?= $test['lab_test_id'] ?></td>
                                                <td><?= htmlspecialchars($test['test_name']) ?></td>
                                                <td>
                                                    <?php
                                            $statusClass = '';
                                            switch ($test['status']) {
                                                case 'completed':
                                                    $statusClass = 'bg-success';
                                                    break;
                                                case 'pending':
                                                    $statusClass = 'bg-warning text-dark';
                                                    break;
                                                case 'cancelled':
                                                    $statusClass = 'bg-danger';
                                                    break;
                                                case 'in-progress':
                                                    $statusClass = 'bg-info';
                                                    break;
                                                default:
                                                    $statusClass = 'bg-secondary';
                                            }
                                            ?>
                                                    <span class="badge <?= $statusClass ?>">
                                                        <?= ucfirst($test['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('M d, Y', strtotime($test['created_at'])) ?></td>
                                                <td>
                                                    <?php if ($test['status'] == 'completed'): ?>
                                                        <a href="<?= $baseUrl ?>/lab-tests/update-result/<?= $test['lab_test_id'] ?>" class="btn btn-sm btn-success">
                                                            <i class="fas fa-file-medical"></i> View Results
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not available</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="<?= $baseUrl ?>/lab-tests/process/<?= $test['lab_test_id'] ?>" class="btn btn-sm btn-info">
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
<!-- end duplicate wrapper removed -->

<!-- Cancel Appointment Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Cancel Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="cancelForm" action="<?= $baseUrl ?>/appointments/cancel/<?= $appointment['appointment_id'] ?>" method="post">
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">Reason for Cancellation</label>
                        <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3" required></textarea>
                    </div>
                </form>
                <p class="text-danger">Warning: This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="confirmCancel">Cancel Appointment</button>
            </div>
        </div>
    </div>
</div>

<?php
// Add page specific script
$pageSpecificScripts[] = AssetHelper::getJs('appointments-view');
?>
