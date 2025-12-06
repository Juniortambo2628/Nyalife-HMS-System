<?php
/**
 * Nyalife HMS - Prescription Details View
 */

$pageTitle = 'Prescription Details - Nyalife HMS';
?>
<div class="container-fluid">
        <div class="row mb-2 mt-2">
    <div class="col-md-6">
        <h1>Prescription Details</h1>
        <h4 class="text-muted"><?= htmlspecialchars($prescription['prescription_number'] ?? 'N/A') ?></h4>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= $baseUrl ?? '' ?>/prescriptions" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Prescriptions
        </a>
        <a href="<?= $baseUrl ?? '' ?>/prescriptions/print/<?= $prescription['prescription_id'] ?? '' ?>" class="btn btn-info" target="_blank">
            <i class="fa fa-print"></i> Print Prescription
        </a>
        
        <?php if (($prescription['status'] ?? '') === 'active'): ?>
            <?php if (SessionManager::get('role') === 'pharmacist' || SessionManager::get('role') === 'admin'): ?>
                <a href="<?= $baseUrl ?? '' ?>/prescriptions/dispense/<?= $prescription['prescription_id'] ?? '' ?>" class="btn btn-success">
                    <i class="fa fa-check"></i> Dispense Medication
                </a>
            <?php endif; ?>
            
            <?php if (SessionManager::get('role') === 'doctor' || SessionManager::get('role') === 'admin'): ?>
                <a href="<?= $baseUrl ?? '' ?>/prescriptions/cancel/<?= $prescription['prescription_id'] ?? '' ?>" class="btn btn-danger">
                    <i class="fa fa-times"></i> Cancel Prescription
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h5>Prescription Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th>Prescription Number</th>
                        <td><?= htmlspecialchars($prescription['prescription_number'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Date</th>
                        <td><?= !empty($prescription['prescription_date']) ? date('d-m-Y', strtotime($prescription['prescription_date'])) : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <?php
                                $status = $prescription['status'] ?? 'unknown';
$statusClass = 'bg-secondary';
if ($status === 'active') {
    $statusClass = 'bg-primary';
} elseif ($status === 'dispensed') {
    $statusClass = 'bg-success';
} elseif ($status === 'cancelled') {
    $statusClass = 'bg-danger';
}
?>
                            <span class="badge <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($status)) ?></span>
                        </td>
                    </tr>
                    <?php if (($prescription['status'] ?? '') === 'dispensed' && !empty($prescription['dispensed_at'])): ?>
                    <tr>
                        <th>Dispensed On</th>
                        <td><?= date('d-m-Y H:i', strtotime($prescription['dispensed_at'])) ?></td>
                    </tr>
                    <tr>
                        <th>Dispensed By</th>
                        <td><?= htmlspecialchars($prescription['dispensed_by_name'] ?? 'N/A') ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (($prescription['status'] ?? '') === 'cancelled' && !empty($prescription['cancelled_at'])): ?>
                    <tr>
                        <th>Cancelled On</th>
                        <td><?= date('d-m-Y H:i', strtotime($prescription['cancelled_at'])) ?></td>
                    </tr>
                    <tr>
                        <th>Cancelled By</th>
                        <td><?= htmlspecialchars($prescription['cancelled_by_name'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Reason</th>
                        <td><?= htmlspecialchars($prescription['cancellation_reason'] ?? 'N/A') ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h5>Patient Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th>Patient Name</th>
                        <td><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Patient Number</th>
                        <td><?= htmlspecialchars($patient['patient_number'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Gender</th>
                        <td><?= htmlspecialchars($patient['gender'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Age</th>
                        <td>
                            <?php
    if (!empty($patient['date_of_birth'])) {
        $dob = new DateTime($patient['date_of_birth']);
        $now = new DateTime();
        $age = $now->diff($dob)->y;
        echo $age . ' years';
    } else {
        echo 'N/A';
    }
?>
                        </td>
                    </tr>
                </table>
                <div class="mt-3">
                    <a href="<?= $baseUrl ?? '' ?>/patients/view/<?= $patient['patient_id'] ?? '' ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-user"></i> View Patient Record
                    </a>
                    <a href="<?= $baseUrl ?? '' ?>/prescriptions?patient_id=<?= $patient['patient_id'] ?? '' ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fa fa-list"></i> Patient Prescriptions
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h5>Prescriber Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th>Doctor Name</th>
                        <td><?= htmlspecialchars($prescription['doctor_name'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Created By</th>
                        <td><?= htmlspecialchars($prescription['created_by_name'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Created On</th>
                        <td><?= !empty($prescription['created_at']) ? date('d-m-Y H:i', strtotime($prescription['created_at'])) : 'N/A' ?></td>
                    </tr>
                </table>
                <?php if (!empty($prescription['appointment_id'])): ?>
                <div class="mt-3">
                    <a href="<?= $baseUrl ?? '' ?>/appointments/view/<?= $prescription['appointment_id'] ?>" class="btn btn-sm btn-outline-info">
                        <i class="fa fa-calendar"></i> View Related Appointment
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h5>Prescription Items</h5>
    </div>
    <div class="card-body">
        <?php if (empty($items)): ?>
            <div class="alert alert-info">No medications found in this prescription.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Medication</th>
                            <th>Dosage</th>
                            <th>Frequency</th>
                            <th>Duration</th>
                            <th>Instructions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $counter = 1; ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= $counter++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($item['medication_name'] ?? 'N/A') ?></strong>
                                    <?php if (!empty($item['strength'])): ?>
                                        <br><small><?= htmlspecialchars($item['strength']) ?> <?= htmlspecialchars($item['unit'] ?? '') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item['dosage'] ?? 'N/A') ?></td>
                                <td>
                                    <?php
            $frequency = $item['frequency'] ?? '';
                            $frequencyMap = [
                                'once_daily' => 'Once daily',
                                'twice_daily' => 'Twice daily (BID)',
                                'three_times_daily' => 'Three times daily (TID)',
                                'four_times_daily' => 'Four times daily (QID)',
                                'every_morning' => 'Every morning (QAM)',
                                'every_night' => 'Every night (QHS)',
                                'every_6_hours' => 'Every 6 hours (q6h)',
                                'every_8_hours' => 'Every 8 hours (q8h)',
                                'every_12_hours' => 'Every 12 hours (q12h)',
                                'as_needed' => 'As needed (PRN)',
                                'with_meals' => 'With meals',
                                'before_meals' => 'Before meals',
                                'after_meals' => 'After meals',
                                'other' => 'Other'
                            ];
                            echo htmlspecialchars($frequencyMap[$frequency] ?? $frequency);
                            ?>
                                </td>
                                <td>
                                    <?php
                                $duration = $item['duration'] ?? '';
                            $durationMap = [
                                '3_days' => '3 days',
                                '5_days' => '5 days',
                                '7_days' => '7 days',
                                '10_days' => '10 days',
                                '14_days' => '14 days',
                                '21_days' => '21 days',
                                '30_days' => '30 days',
                                '60_days' => '60 days',
                                '90_days' => '90 days',
                                'indefinite' => 'Indefinite/Chronic',
                                'other' => 'Other'
                            ];
                            echo htmlspecialchars($durationMap[$duration] ?? $duration);
                            ?>
                                </td>
                                <td><?= htmlspecialchars($item['instructions'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($prescription['notes'])): ?>
<div class="card mt-3">
    <div class="card-header">
        <h5>Additional Notes</h5>
    </div>
    <div class="card-body">
        <p><?= nl2br(htmlspecialchars($prescription['notes'])) ?></p>
    </div>
</div>
<?php endif; ?> 