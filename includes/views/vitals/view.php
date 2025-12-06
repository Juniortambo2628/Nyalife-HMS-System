<?php
/**
 * Nyalife HMS - Vital Signs Details View
 */

$pageTitle = 'Vital Signs Details - Nyalife HMS';
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Vital Signs Record</h5>
                    <div>
                        <a href="<?= $baseUrl ?>/vitals/history/<?= $patient['patient_id'] ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-history"></i> View History
                        </a>
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
                                    <td><?= htmlspecialchars($patient['patient_number'] ?? '') ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Record Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="120">Recorded By:</th>
                                    <td><?= htmlspecialchars($vitalSign['recorded_by_name'] ?? 'Not recorded') ?></td>
                                </tr>
                                <tr>
                                    <th>Date/Time:</th>
                                    <td><?= !empty($vitalSign['measured_at']) ? date('M d, Y h:i A', strtotime($vitalSign['measured_at'])) : 'Not recorded' ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="text-muted">Vital Signs</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Blood Pressure</th>
                                            <th>Pulse</th>
                                            <th>Temperature</th>
                                            <th>Respiratory Rate</th>
                                            <th>O₂ Saturation</th>
                                            <th>Pain Level</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                            <td data-field~="blood_pressure"><?= $vitalSign['blood_pressure'] ? htmlspecialchars($vitalSign['blood_pressure']) . ' mmHg' : '<span class="text-muted">Not recorded</span>' ?></td>
                                            <td data-field~="pulse"><?= $vitalSign['heart_rate'] ? htmlspecialchars($vitalSign['heart_rate']) . ' bpm' : '<span class="text-muted">Not recorded</span>' ?></td>
                                            <td data-field~="temperature"><?= $vitalSign['temperature'] ? htmlspecialchars($vitalSign['temperature']) . ' °C' : '<span class="text-muted">Not recorded</span>' ?></td>
                                            <td data-field~="respiratory_rate"><?= $vitalSign['respiratory_rate'] ? htmlspecialchars($vitalSign['respiratory_rate']) . ' br/min' : '<span class="text-muted">Not recorded</span>' ?></td>
                                            <td data-field~="oxygen_saturation"><?= $vitalSign['oxygen_saturation'] ? htmlspecialchars($vitalSign['oxygen_saturation']) . '%' : '<span class="text-muted">Not recorded</span>' ?></td>
                                            <td data-field~="pain_level"><?= $vitalSign['pain_level'] !== null ? htmlspecialchars($vitalSign['pain_level']) . '/10' : '<span class="text-muted">Not recorded</span>' ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h6 class="text-muted">Measurements</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Height</th>
                                            <th>Weight</th>
                                            <th>BMI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?= $vitalSign['height'] ? htmlspecialchars($vitalSign['height']) . ' cm' : '<span class="text-muted">Not recorded</span>' ?></td>
                                            <td><?= $vitalSign['weight'] ? htmlspecialchars($vitalSign['weight']) . ' kg' : '<span class="text-muted">Not recorded</span>' ?></td>
                                            <td>
                                                <?php if ($vitalSign['bmi']): ?>
                                                    <?= htmlspecialchars($vitalSign['bmi']) ?>
                                                    <?php
                                                    $bmiClass = '';
                                                    $bmiStatus = '';
                                                    if ($vitalSign['bmi'] < 18.5) {
                                                        $bmiClass = 'text-warning';
                                                        $bmiStatus = 'Underweight';
                                                    } elseif ($vitalSign['bmi'] >= 18.5 && $vitalSign['bmi'] < 25) {
                                                        $bmiClass = 'text-success';
                                                        $bmiStatus = 'Normal';
                                                    } elseif ($vitalSign['bmi'] >= 25 && $vitalSign['bmi'] < 30) {
                                                        $bmiClass = 'text-warning';
                                                        $bmiStatus = 'Overweight';
                                                    } else {
                                                        $bmiClass = 'text-danger';
                                                        $bmiStatus = 'Obese';
                                                    }
?>
                                                    <span class="<?= $bmiClass ?>">(<?= $bmiStatus ?>)</span>
                                                <?php else: ?>
                                                    <span class="text-muted">Not recorded</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <?php if ($vitalSign['notes']): ?>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h6 class="text-muted">Notes</h6>
                            <div class="card">
                                <div class="card-body bg-light">
                                    <?= nl2br(htmlspecialchars($vitalSign['notes'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <a href="<?= $baseUrl ?>/patients/view/<?= $patient['patient_id'] ?>" class="btn btn-secondary">
                            <i class="fas fa-user"></i> Back to Patient
                        </a>
                        <?php if (in_array($userRole ?? '', ['admin', 'doctor'])): ?>
                        <a href="#" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal">
                            <i class="fas fa-trash"></i> Delete Record
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

                        <?php if (in_array($userRole ?? '', ['admin', 'doctor'])): ?>
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this vital signs record? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="<?= $baseUrl ?>/vitals/delete/<?= $vitalSign['id'] ?>" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?> 