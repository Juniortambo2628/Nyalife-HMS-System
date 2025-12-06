<?php
/**
 * Nyalife HMS - Vital Signs History View
 */

$pageTitle = 'Vital Signs History - Nyalife HMS';

/**
 * Calculate age from date of birth
 *
 * @param string $dateOfBirth Date of birth in Y-m-d format
 * @return int Age in years
 */
function calculateAge($dateOfBirth)
{
    $dob = new DateTime($dateOfBirth);
    $now = new DateTime();
    $interval = $now->diff($dob);
    return $interval->y;
}
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Vital Signs History</h5>
                    <div>
                        <a href="<?= $baseUrl ?>/vitals/create/<?= $patient['patient_id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> New Record
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Patient Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="120">Name:</th>
                                    <td><?= htmlspecialchars(trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''))) ?: 'Unknown' ?></td>
                                </tr>
                                <tr>
                                    <th>Patient #:</th>
                                    <td><?= htmlspecialchars($patient['patient_number'] ?? (string)($patient['patient_id'] ?? '')) ?></td>
                                </tr>
                                <tr>
                                    <th>Gender:</th>
                                    <td><?= htmlspecialchars($patient['gender'] ?? '') ?></td>
                                </tr>
                                <tr>
                                    <th>Age:</th>
                                    <td><?= !empty($patient['date_of_birth']) ? calculateAge($patient['date_of_birth']) : 'N/A' ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <?php if (empty($vitalSigns)): ?>
                    <div class="alert alert-info">
                        No vital sign records found for this patient.
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="vitalsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>BP</th>
                                    <th>Pulse</th>
                                    <th>Temp</th>
                                    <th>Resp</th>
                                    <th>O₂ Sat</th>
                                    <th>Height</th>
                                    <th>Weight</th>
                                    <th>BMI</th>
                                    <th>Recorded By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vitalSigns as $vital): ?>
                                <tr>
                                    <td><?= !empty($vital['measured_at']) ? date('M d, Y H:i', strtotime($vital['measured_at'])) : '-' ?></td>
                                    <td><?= $vital['blood_pressure'] ?: '-' ?></td>
                                    <td><?= ($vital['heart_rate'] ?? null) ? ($vital['heart_rate'] . ' bpm') : '-' ?></td>
                                    <td><?= $vital['temperature'] ? $vital['temperature'] . ' °C' : '-' ?></td>
                                    <td><?= $vital['respiratory_rate'] ? $vital['respiratory_rate'] . '/min' : '-' ?></td>
                                    <td><?= $vital['oxygen_saturation'] ? $vital['oxygen_saturation'] . '%' : '-' ?></td>
                                    <td><?= ($vital['height'] ?? null) ? ($vital['height'] . ' cm') : '-' ?></td>
                                    <td><?= ($vital['weight'] ?? null) ? ($vital['weight'] . ' kg') : '-' ?></td>
                                    <td>
                                        <?php if ($vital['bmi']): ?>
                                            <?= $vital['bmi'] ?>
                                            <?php
                                            $bmiClass = '';
                                            if ($vital['bmi'] < 18.5) {
                                                $bmiClass = 'text-warning';
                                            } elseif ($vital['bmi'] >= 18.5 && $vital['bmi'] < 25) {
                                                $bmiClass = 'text-success';
                                            } elseif ($vital['bmi'] >= 25 && $vital['bmi'] < 30) {
                                                $bmiClass = 'text-warning';
                                            } else {
                                                $bmiClass = 'text-danger';
                                            }
                                    ?>
                                            <i class="fas fa-circle <?= $bmiClass ?>" style="font-size: 8px;"></i>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($vital['recorded_by_name'] ?? 'Unknown') ?></td>
                                    <td>
                                        <a href="<?= $baseUrl ?>/vitals/view/<?= $vital['vital_id'] ?? ($vital['id'] ?? '') ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <a href="<?= $baseUrl ?>/patients/view/<?= $patient['patient_id'] ?>" class="btn btn-secondary">
                            <i class="fas fa-user"></i> Back to Patient
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable if available
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#vitalsTable').DataTable({
            "order": [[0, "desc"]], // Sort by date descending
            "responsive": true,
            "language": {
                "emptyTable": "No vital signs records found"
            }
        });
    }
});
</script> 