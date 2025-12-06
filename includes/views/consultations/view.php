<?php
/**
 * Nyalife HMS - Consultation Details View
 */

$pageTitle = 'Consultation Details - Nyalife HMS';

/**
 * Get button text and class based on whether field has data
 *
 * @param mixed $value Field value
 * @param string $defaultText Default text when no data
 * @return array Array with 'text' and 'class' keys
 */
function getButtonInfo($value, $defaultText = 'No data recorded')
{
    $hasData = !empty($value) && $value !== $defaultText;
    return [
        'text' => $hasData ? 'Edit' : 'Add',
        'class' => $hasData ? 'btn-outline-primary' : 'btn-outline-success'
    ];
}
?>

    <div class="container-fluid px-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2 class="mb-0">Consultation Details</h2>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group">
                    <a href="<?= $baseUrl ?>/consultations/print/<?= $consultation['consultation_id'] ?>" 
                       class="btn btn-outline-secondary me-2" target="_blank">
                        <i class="fas fa-print"></i> Print
                    </a>
                    <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                        <a href="<?= $baseUrl ?>/consultations/edit/<?= $consultation['consultation_id'] ?>"
                           class="btn btn-outline-primary me-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="<?= $baseUrl ?>/lab/request/new?patient_id=<?= $consultation['patient_id'] ?>&appointment_id=<?= $consultation['appointment_id'] ?? '' ?>"
                           class="btn btn-warning me-2">
                            <i class="fas fa-flask"></i> Lab Request
                        </a>
                    <?php endif; ?>
                    <a href="<?= $baseUrl ?>/consultations" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Basic Information Card -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-md bg-primary bg-opacity-10 rounded-circle me-3">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($consultation['patient_first_name'] . ' ' . $consultation['patient_last_name']) ?></h6>
                                        <small class="text-muted">Patient</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-md bg-info bg-opacity-10 rounded-circle me-3">
                                        <i class="fas fa-user-md text-info"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Dr. <?= htmlspecialchars($consultation['doctor_first_name'] . ' ' . $consultation['doctor_last_name']) ?></h6>
                                        <small class="text-muted">Doctor</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-2">
                                    <small class="text-muted d-block">Date & Time</small>
                                    <span><?= date('M j, Y g:i A', strtotime($consultation['consultation_date'])) ?></span>
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <span class="badge <?= $statusClass ?> fs-6"><?= $statusLabel ?></span>
                                <?php if (!empty($consultation['is_walk_in'])): ?>
                                    <span class="badge bg-info ms-2">Walk-in</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <div class="col-md-8">
                <!-- Accordion for consultation details -->
                <div class="accordion mb-4" id="consultationAccordion">
                    <!-- Chief Complaint -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingComplaint">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseComplaint" aria-expanded="true" aria-controls="collapseComplaint">
                                <i class="fas fa-exclamation-circle me-2 text-danger"></i> <strong>Chief Complaint</strong>
                                <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                                    <?php $complaintBtn = getButtonInfo($consultation['chief_complaint'] ?? '', 'No chief complaint recorded'); ?>
                                    <a href="#editComplaint" class="btn btn-sm <?= $complaintBtn['class'] ?> ms-2" data-bs-toggle="collapse" data-bs-target="#editComplaint" aria-expanded="false">
                                        <i class="fas fa-<?= $complaintBtn['text'] === 'Add' ? 'plus' : 'edit' ?>"></i> <?= $complaintBtn['text'] ?>
                                    </a>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseComplaint" class="accordion-collapse collapse show" aria-labelledby="headingComplaint" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <?= !empty($consultation['chief_complaint']) ? nl2br(htmlspecialchars($consultation['chief_complaint'])) :
                                    '<span class="text-muted">No chief complaint recorded</span>' ?>
                            </div>
                        </div>
                        <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                        <div id="editComplaint" class="accordion-collapse collapse" aria-labelledby="headingComplaint" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <form method="post" action="<?= $baseUrl ?>/consultations/update-field/<?= $consultation['consultation_id'] ?>">
                                    <input type="hidden" name="field" value="chief_complaint">
                                    <div class="mb-3">
                                        <label for="chief_complaint_edit" class="form-label">Chief Complaint</label>
                                        <textarea class="form-control" id="chief_complaint_edit" name="value" rows="3" required><?= htmlspecialchars($consultation['chief_complaint'] ?? '') ?></textarea>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#collapseComplaint">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Vital Signs -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingVitals">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVitals" aria-expanded="false" aria-controls="collapseVitals">
                                <i class="fas fa-heartbeat me-2 text-danger"></i> <strong>Vital Signs</strong>
                                <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                                    <?php $vitalsBtn = getButtonInfo(!empty($vitalSigns) && count($vitalSigns) > 0, 'No vital signs recorded'); ?>
                                    <a href="#editVitals" class="btn btn-sm <?= $vitalsBtn['class'] ?> ms-2" data-bs-toggle="collapse" data-bs-target="#editVitals" aria-expanded="false">
                                        <i class="fas fa-<?= $vitalsBtn['text'] === 'Add' ? 'plus' : 'edit' ?>"></i> <?= $vitalsBtn['text'] ?>
                                    </a>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseVitals" class="accordion-collapse collapse" aria-labelledby="headingVitals" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <?php
                                $vitalSigns = !empty($consultation['vital_signs']) ?
                                    (is_string($consultation['vital_signs']) ? json_decode($consultation['vital_signs'], true) : $consultation['vital_signs']) :
                                    [];

if (!empty($vitalSigns)):
    ?>
                                    <div class="row">
                                        <?php if (!empty($vitalSigns['blood_pressure'])): ?>
                                        <div class="col-md-3 mb-3">
                                            <small class="text-dark fw-bold d-block">Blood Pressure</small>
                                            <span><?= htmlspecialchars($vitalSigns['blood_pressure']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($vitalSigns['pulse'])): ?>
                                        <div class="col-md-3 mb-3">
                                            <small class="text-dark fw-bold d-block">Pulse</small>
                                            <span><?= htmlspecialchars($vitalSigns['pulse']) ?> bpm</span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($vitalSigns['temperature'])): ?>
                                        <div class="col-md-3 mb-3">
                                            <small class="text-dark fw-bold d-block">Temperature</small>
                                            <span><?= htmlspecialchars($vitalSigns['temperature']) ?> °C</span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($vitalSigns['respiratory_rate'])): ?>
                                        <div class="col-md-3 mb-3">
                                            <small class="text-dark fw-bold d-block">Respiratory Rate</small>
                                            <span><?= htmlspecialchars($vitalSigns['respiratory_rate']) ?> breaths/min</span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($vitalSigns['oxygen_saturation'])): ?>
                                        <div class="col-md-3 mb-3">
                                            <small class="text-dark fw-bold d-block">SpO₂</small>
                                            <span><?= htmlspecialchars($vitalSigns['oxygen_saturation']) ?>%</span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($vitalSigns['pain_level'])): ?>
                                        <div class="col-md-3 mb-3">
                                            <small class="text-dark fw-bold d-block">Pain Level</small>
                                            <span><?= htmlspecialchars($vitalSigns['pain_level']) ?>/10</span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($vitalSigns['height'])): ?>
                                        <div class="col-md-3 mb-3">
                                            <small class="text-dark fw-bold d-block">Height</small>
                                            <span><?= htmlspecialchars($vitalSigns['height']) ?> cm</span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($vitalSigns['weight'])): ?>
                                        <div class="col-md-3 mb-3">
                                            <small class="text-dark fw-bold d-block">Weight</small>
                                            <span><?= htmlspecialchars($vitalSigns['weight']) ?> kg</span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($vitalSigns['bmi'])): ?>
                                        <div class="col-md-3 mb-3">
                                            <small class="text-dark fw-bold d-block">BMI</small>
                                            <span><?= htmlspecialchars($vitalSigns['bmi']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No vital signs recorded</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                        <div id="editVitals" class="accordion-collapse collapse" aria-labelledby="headingVitals" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <form method="post" action="<?= $baseUrl ?>/consultations/update-vitals/<?= $consultation['consultation_id'] ?>">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="blood_pressure" class="form-label">Blood Pressure</label>
                                            <input type="text" class="form-control" id="blood_pressure" name="blood_pressure" value="<?= htmlspecialchars($vitalSigns['blood_pressure'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="pulse" class="form-label">Pulse (bpm)</label>
                                            <input type="number" class="form-control" id="pulse" name="pulse" value="<?= htmlspecialchars($vitalSigns['pulse'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="temperature" class="form-label">Temperature (°C)</label>
                                            <input type="number" step="0.1" class="form-control" id="temperature" name="temperature" value="<?= htmlspecialchars($vitalSigns['temperature'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="respiratory_rate" class="form-label">Respiratory Rate</label>
                                            <input type="number" class="form-control" id="respiratory_rate" name="respiratory_rate" value="<?= htmlspecialchars($vitalSigns['respiratory_rate'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="oxygen_saturation" class="form-label">O₂ Saturation (%)</label>
                                            <input type="number" class="form-control" id="oxygen_saturation" name="oxygen_saturation" value="<?= htmlspecialchars($vitalSigns['oxygen_saturation'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="pain_level" class="form-label">Pain Level (0-10)</label>
                                            <input type="number" min="0" max="10" class="form-control" id="pain_level" name="pain_level" value="<?= htmlspecialchars($vitalSigns['pain_level'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="height" class="form-label">Height (cm)</label>
                                            <input type="number" step="0.1" class="form-control" id="height" name="height" value="<?= htmlspecialchars($vitalSigns['height'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="weight" class="form-label">Weight (kg)</label>
                                            <input type="number" step="0.1" class="form-control" id="weight" name="weight" value="<?= htmlspecialchars($vitalSigns['weight'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary">Update Vitals</button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#collapseVitals">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- History of Present Illness -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingHistory">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHistory" aria-expanded="false" aria-controls="collapseHistory">
                                <i class="fas fa-file-medical-alt me-2 text-danger"></i> <strong>History of Present Illness</strong>
                                <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                                    <?php $historyBtn = getButtonInfo($consultation['history_present_illness'] ?? '', 'No history recorded'); ?>
                                    <a href="#editHistory" class="btn btn-sm <?= $historyBtn['class'] ?> ms-2" data-bs-toggle="collapse" data-bs-target="#editHistory" aria-expanded="false">
                                        <i class="fas fa-<?= $historyBtn['text'] === 'Add' ? 'plus' : 'edit' ?>"></i> <?= $historyBtn['text'] ?>
                                    </a>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseHistory" class="accordion-collapse collapse" aria-labelledby="headingHistory" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <?= !empty($consultation['history_present_illness']) ? nl2br(htmlspecialchars($consultation['history_present_illness'])) :
        '<span class="text-muted">No history recorded</span>' ?>
                            </div>
                        </div>
                        <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                        <div id="editHistory" class="accordion-collapse collapse" aria-labelledby="headingHistory" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <form method="post" action="<?= $baseUrl ?>/consultations/update-field/<?= $consultation['consultation_id'] ?>">
                                    <input type="hidden" name="field" value="history_present_illness">
                                    <div class="mb-3">
                                        <label for="history_present_illness_edit" class="form-label">History of Present Illness</label>
                                        <textarea class="form-control" id="history_present_illness_edit" name="value" rows="4" placeholder="Describe the history of the present illness..."><?= htmlspecialchars($consultation['history_present_illness'] ?? '') ?></textarea>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#collapseHistory">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Gynaecological History -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingGynHistory">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGynHistory" aria-expanded="false" aria-controls="collapseGynHistory">
                                <i class="fas fa-venus me-2 text-primary"></i> <strong>Gynaecological History</strong>
                                <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                                    <?php $gynBtn = getButtonInfo($consultation['gynecological_history'] ?? '', 'No gynecological history recorded'); ?>
                                    <a href="#editGynHistory" class="btn btn-sm <?= $gynBtn['class'] ?> ms-2" data-bs-toggle="collapse" data-bs-target="#editGynHistory" aria-expanded="false">
                                        <i class="fas fa-<?= $gynBtn['text'] === 'Add' ? 'plus' : 'edit' ?>"></i> <?= $gynBtn['text'] ?>
                                    </a>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseGynHistory" class="accordion-collapse collapse" aria-labelledby="headingGynHistory" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-calendar-alt text-primary me-1"></i>Menstrual History</label>
                                        <p class="text-muted small mb-2">Date of last normal menstrual period, Regularity of Flow, Number of days, Dysmenorrhea</p>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['menstrual_history']) ? nl2br(htmlspecialchars($consultation['menstrual_history'])) :
                    '<span class="text-muted">No menstrual history recorded</span>' ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-search text-primary me-1"></i>Cervical Cancer Screening History/Pap Smear Test</label>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['cervical_screening']) ? nl2br(htmlspecialchars($consultation['cervical_screening'])) :
                    '<span class="text-muted">No screening history recorded</span>' ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-pills text-primary me-1"></i>Contraception/Birth Control History</label>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['contraceptive_history']) ? nl2br(htmlspecialchars($consultation['contraceptive_history'])) :
                    '<span class="text-muted">No contraceptive history recorded</span>' ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-heart text-primary me-1"></i>Sexual Health History</label>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['sexual_history']) ? nl2br(htmlspecialchars($consultation['sexual_history'])) :
                    '<span class="text-muted">No sexual health history recorded</span>' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                        <div id="editGynHistory" class="accordion-collapse collapse" aria-labelledby="headingGynHistory" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <form method="post" action="<?= $baseUrl ?>/consultations/update-field/<?= $consultation['consultation_id'] ?>">
                                    <input type="hidden" name="field" value="gynecological_history">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="menstrual_history_edit" class="form-label">Menstrual History</label>
                                            <textarea class="form-control" id="menstrual_history_edit" name="menstrual_history" rows="2" placeholder="LMP: DD/MM/YYYY, Cycle: Regular/Irregular..."><?php echo htmlspecialchars($consultation['menstrual_history'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cervical_screening_edit" class="form-label">Cervical Cancer Screening</label>
                                            <textarea class="form-control" id="cervical_screening_edit" name="cervical_screening" rows="2" placeholder="Last pap smear date, Results..."><?php echo htmlspecialchars($consultation['cervical_screening'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="contraceptive_history_edit" class="form-label">Contraceptive History</label>
                                            <textarea class="form-control" id="contraceptive_history_edit" name="contraceptive_history" rows="2" placeholder="Current/Previous methods..."><?php echo htmlspecialchars($consultation['contraceptive_history'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="sexual_history_edit" class="form-label">Sexual Health History</label>
                                            <textarea class="form-control" id="sexual_history_edit" name="sexual_history" rows="2" placeholder="Age at first intercourse, Partners..."><?php echo htmlspecialchars($consultation['sexual_history'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#collapseGynHistory">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Obstetric History -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingObstetric">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseObstetric" aria-expanded="false" aria-controls="collapseObstetric">
                                <i class="fas fa-baby me-2 text-primary"></i> <strong>Obstetric History</strong>
                                <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                                    <?php $obstetricBtn = getButtonInfo($consultation['obstetric_history'] ?? '', 'No obstetric history recorded'); ?>
                                    <a href="#editObstetric" class="btn btn-sm <?= $obstetricBtn['class'] ?> ms-2" data-bs-toggle="collapse" data-bs-target="#editObstetric" aria-expanded="false">
                                        <i class="fas fa-<?= $obstetricBtn['text'] === 'Add' ? 'plus' : 'edit' ?>"></i> <?= $obstetricBtn['text'] ?>
                                    </a>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseObstetric" class="accordion-collapse collapse" aria-labelledby="headingObstetric" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label"><i class="fas fa-baby text-primary me-1"></i>Parity/Number of Pregnancies</label>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['parity']) ? htmlspecialchars($consultation['parity']) :
                    '<span class="text-muted">Not recorded</span>' ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label"><i class="fas fa-clock text-primary me-1"></i>History of Current Pregnancy</label>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['current_pregnancy']) ? nl2br(htmlspecialchars($consultation['current_pregnancy'])) :
                    '<span class="text-muted">Not recorded</span>' ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label"><i class="fas fa-history text-primary me-1"></i>Past OB History/Pregnancy History</label>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['past_obstetric']) ? nl2br(htmlspecialchars($consultation['past_obstetric'])) :
                    '<span class="text-muted">Not recorded</span>' ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <label class="form-label"><i class="fas fa-file-medical text-primary me-1"></i>Detailed Obstetric History</label>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['obstetric_history']) ? nl2br(htmlspecialchars($consultation['obstetric_history'])) :
                    '<span class="text-muted">No detailed history recorded</span>' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                        <div id="editObstetric" class="accordion-collapse collapse" aria-labelledby="headingObstetric" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <form method="post" action="<?= $baseUrl ?>/consultations/update-field/<?= $consultation['consultation_id'] ?>">
                                    <input type="hidden" name="field" value="obstetric_history">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label for="parity_edit" class="form-label">Parity/Number of Pregnancies</label>
                                            <input type="text" class="form-control" id="parity_edit" name="parity" value="<?php echo htmlspecialchars($consultation['parity'] ?? ''); ?>" placeholder="G3P2A1 (Gravida 3, Para 2, Abortion 1)">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="current_pregnancy_edit" class="form-label">History of Current Pregnancy</label>
                                            <textarea class="form-control" id="current_pregnancy_edit" name="current_pregnancy" rows="2" placeholder="Gestational age, Complications..."><?php echo htmlspecialchars($consultation['current_pregnancy'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="past_obstetric_edit" class="form-label">Past OB History/Pregnancy History</label>
                                            <textarea class="form-control" id="past_obstetric_edit" name="past_obstetric" rows="2" placeholder="Year/Place of birth/Duration/Labour hours..."><?php echo htmlspecialchars($consultation['past_obstetric'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <label for="obstetric_history_edit" class="form-label">Detailed Obstetric History</label>
                                            <textarea class="form-control" id="obstetric_history_edit" name="value" rows="4" placeholder="Comprehensive pregnancy history..."><?php echo htmlspecialchars($consultation['obstetric_history'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#collapseObstetric">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Medical and Surgical History -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingMedicalSurgical">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMedicalSurgical" aria-expanded="false" aria-controls="collapseMedicalSurgical">
                                <i class="fas fa-stethoscope me-2 text-primary"></i> <strong>Medical and Surgical History</strong>
                                <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                                    <?php $medicalBtn = getButtonInfo($consultation['past_medical_history'] ?? '', 'No medical history recorded'); ?>
                                    <a href="#editMedicalSurgical" class="btn btn-sm <?= $medicalBtn['class'] ?> ms-2" data-bs-toggle="collapse" data-bs-target="#editMedicalSurgical" aria-expanded="false">
                                        <i class="fas fa-<?= $medicalBtn['text'] === 'Add' ? 'plus' : 'edit' ?>"></i> <?= $medicalBtn['text'] ?>
                                    </a>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseMedicalSurgical" class="accordion-collapse collapse" aria-labelledby="headingMedicalSurgical" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-stethoscope text-primary me-1"></i>Past Medical History</label>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['past_medical_history']) ? nl2br(htmlspecialchars($consultation['past_medical_history'])) :
                    '<span class="text-muted">No past medical history recorded</span>' ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-surgery text-primary me-1"></i>Surgical History</label>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['surgical_history']) ? nl2br(htmlspecialchars($consultation['surgical_history'])) :
                    '<span class="text-muted">No surgical history recorded</span>' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                        <div id="editMedicalSurgical" class="accordion-collapse collapse" aria-labelledby="headingMedicalSurgical" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <form method="post" action="<?= $baseUrl ?>/consultations/update-field/<?= $consultation['consultation_id'] ?>">
                                    <input type="hidden" name="field" value="past_medical_history">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="past_medical_history_edit" class="form-label">Past Medical History</label>
                                            <textarea class="form-control" id="past_medical_history_edit" name="value" rows="3" placeholder="Previous illnesses, chronic conditions..."><?php echo htmlspecialchars($consultation['past_medical_history'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="surgical_history_edit" class="form-label">Surgical History</label>
                                            <textarea class="form-control" id="surgical_history_edit" name="surgical_history" rows="3" placeholder="Previous surgeries, procedures..."><?php echo htmlspecialchars($consultation['surgical_history'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#collapseMedicalSurgical">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Family Social History -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFamilySocial">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFamilySocial" aria-expanded="false" aria-controls="collapseFamilySocial">
                                <i class="fas fa-users me-2 text-primary"></i> <strong>Family Social History</strong>
                                <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                                    <?php $familyBtn = getButtonInfo($consultation['family_history'] ?? '', 'No family history recorded'); ?>
                                    <a href="#editFamilySocial" class="btn btn-sm <?= $familyBtn['class'] ?> ms-2" data-bs-toggle="collapse" data-bs-target="#editFamilySocial" aria-expanded="false">
                                        <i class="fas fa-<?= $familyBtn['text'] === 'Add' ? 'plus' : 'edit' ?>"></i> <?= $familyBtn['text'] ?>
                                    </a>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseFamilySocial" class="accordion-collapse collapse" aria-labelledby="headingFamilySocial" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-users text-primary me-1"></i>Family History</label>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['family_history']) ? nl2br(htmlspecialchars($consultation['family_history'])) :
                    '<span class="text-muted">No family history recorded</span>' ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-home text-primary me-1"></i>Social History</label>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['social_history']) ? nl2br(htmlspecialchars($consultation['social_history'])) :
                    '<span class="text-muted">No social history recorded</span>' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                        <div id="editFamilySocial" class="accordion-collapse collapse" aria-labelledby="headingFamilySocial" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <form method="post" action="<?= $baseUrl ?>/consultations/update-field/<?= $consultation['consultation_id'] ?>">
                                    <input type="hidden" name="field" value="family_history">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="family_history_edit" class="form-label">Family History</label>
                                            <textarea class="form-control" id="family_history_edit" name="value" rows="3" placeholder="Family medical history, hereditary conditions..."><?php echo htmlspecialchars($consultation['family_history'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="social_history_edit" class="form-label">Social History</label>
                                            <textarea class="form-control" id="social_history_edit" name="social_history" rows="3" placeholder="Occupation, smoking/alcohol habits..."><?php echo htmlspecialchars($consultation['social_history'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#collapseFamilySocial">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Summary -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSummary">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSummary" aria-expanded="false" aria-controls="collapseSummary">
                                <i class="fas fa-file-alt me-2 text-primary"></i> <strong>Summary</strong>
                                <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                                    <?php $summaryBtn = getButtonInfo($consultation['clinical_summary'] ?? '', 'No summary recorded'); ?>
                                    <a href="#editSummary" class="btn btn-sm <?= $summaryBtn['class'] ?> ms-2" data-bs-toggle="collapse" data-bs-target="#editSummary" aria-expanded="false">
                                        <i class="fas fa-<?= $summaryBtn['text'] === 'Add' ? 'plus' : 'edit' ?>"></i> <?= $summaryBtn['text'] ?>
                                    </a>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseSummary" class="accordion-collapse collapse" aria-labelledby="headingSummary" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <?= !empty($consultation['clinical_summary']) ? nl2br(htmlspecialchars($consultation['clinical_summary'])) :
        '<span class="text-muted">No clinical summary recorded</span>' ?>
                            </div>
                        </div>
                        <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                        <div id="editSummary" class="accordion-collapse collapse" aria-labelledby="headingSummary" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <form method="post" action="<?= $baseUrl ?>/consultations/update-field/<?= $consultation['consultation_id'] ?>">
                                    <input type="hidden" name="field" value="clinical_summary">
                                    <div class="mb-3">
                                        <label for="clinical_summary_edit" class="form-label">Clinical Summary</label>
                                        <textarea class="form-control" id="clinical_summary_edit" name="value" rows="4" placeholder="Brief summary of the case..."><?php echo htmlspecialchars($consultation['clinical_summary'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#collapseSummary">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Examination -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingExam">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExam" aria-expanded="false" aria-controls="collapseExam">
                                <i class="fas fa-stethoscope me-2 text-primary"></i> <strong>Examination</strong>
                                <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                                    <?php $examBtn = getButtonInfo($consultation['physical_examination'] ?? '', 'No examination recorded'); ?>
                                    <a href="#editExam" class="btn btn-sm <?= $examBtn['class'] ?> ms-2" data-bs-toggle="collapse" data-bs-target="#editExam" aria-expanded="false">
                                        <i class="fas fa-<?= $examBtn['text'] === 'Add' ? 'plus' : 'edit' ?>"></i> <?= $examBtn['text'] ?>
                                    </a>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseExam" class="accordion-collapse collapse" aria-labelledby="headingExam" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-user-check text-primary me-1"></i>General Examination</label>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['general_examination']) ? nl2br(htmlspecialchars($consultation['general_examination'])) :
                    '<span class="text-muted">No general examination recorded</span>' ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-heartbeat text-primary me-1"></i>Systems Examination Findings</label>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['systems_examination']) ? nl2br(htmlspecialchars($consultation['systems_examination'])) :
                    '<span class="text-muted">No systems examination recorded</span>' ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <label class="form-label"><i class="fas fa-stethoscope text-primary me-1"></i>Detailed Physical Examination</label>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['physical_examination']) ? nl2br(htmlspecialchars($consultation['physical_examination'])) :
                    '<span class="text-muted">No detailed examination recorded</span>' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                        <div id="editExam" class="accordion-collapse collapse" aria-labelledby="headingExam" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <form method="post" action="<?= $baseUrl ?>/consultations/update-field/<?= $consultation['consultation_id'] ?>">
                                    <input type="hidden" name="field" value="physical_examination">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="general_examination_edit" class="form-label">General Examination</label>
                                            <textarea class="form-control" id="general_examination_edit" name="general_examination" rows="2" placeholder="General appearance, vital signs..."><?php echo htmlspecialchars($consultation['general_examination'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="systems_examination_edit" class="form-label">Systems Examination Findings</label>
                                            <textarea class="form-control" id="systems_examination_edit" name="systems_examination" rows="2" placeholder="Cardiovascular, respiratory, abdominal..."><?php echo htmlspecialchars($consultation['systems_examination'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <label for="physical_examination_edit" class="form-label">Detailed Physical Examination</label>
                                            <textarea class="form-control" id="physical_examination_edit" name="value" rows="4" placeholder="Complete physical examination findings..."><?php echo htmlspecialchars($consultation['physical_examination'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#collapseExam">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Impression/Diagnosis -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingDiagnosis">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDiagnosis" aria-expanded="false" aria-controls="collapseDiagnosis">
                                <i class="fas fa-clipboard-check me-2 text-primary"></i> <strong>Impression/Diagnosis</strong>
                                <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                                    <?php $diagnosisBtn = getButtonInfo($consultation['diagnosis'] ?? '', 'No diagnosis recorded'); ?>
                                    <a href="#editDiagnosis" class="btn btn-sm <?= $diagnosisBtn['class'] ?> ms-2" data-bs-toggle="collapse" data-bs-target="#editDiagnosis" aria-expanded="false">
                                        <i class="fas fa-<?= $diagnosisBtn['text'] === 'Add' ? 'plus' : 'edit' ?>"></i> <?= $diagnosisBtn['text'] ?>
                                    </a>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseDiagnosis" class="accordion-collapse collapse" aria-labelledby="headingDiagnosis" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <label class="form-label"><i class="fas fa-clipboard-check text-primary me-1"></i>Primary Diagnosis</label>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['diagnosis']) ? nl2br(htmlspecialchars($consultation['diagnosis'])) :
                    '<span class="text-muted">No primary diagnosis recorded</span>' ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label"><i class="fas fa-percentage text-primary me-1"></i>Confidence Level</label>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['diagnosis_confidence']) ? htmlspecialchars(ucfirst($consultation['diagnosis_confidence'])) :
                    '<span class="text-muted">Not specified</span>' ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-list text-primary me-1"></i>Differential Diagnosis</label>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['differential_diagnosis']) ? nl2br(htmlspecialchars($consultation['differential_diagnosis'])) :
                    '<span class="text-muted">No differential diagnosis recorded</span>' ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-flask text-primary me-1"></i>Diagnostic Plan</label>
                                        <div class="border p-2 bg-light">
                                            <?= !empty($consultation['diagnostic_plan']) ? nl2br(htmlspecialchars($consultation['diagnostic_plan'])) :
                    '<span class="text-muted">No diagnostic plan recorded</span>' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                        <div id="editDiagnosis" class="accordion-collapse collapse" aria-labelledby="headingDiagnosis" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <form method="post" action="<?= $baseUrl ?>/consultations/update-field/<?= $consultation['consultation_id'] ?>">
                                    <input type="hidden" name="field" value="diagnosis">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-8">
                                            <label for="diagnosis_edit" class="form-label">Primary Diagnosis</label>
                                            <textarea class="form-control" id="diagnosis_edit" name="value" rows="3" placeholder="Primary diagnosis with ICD code..."><?php echo htmlspecialchars($consultation['diagnosis'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="diagnosis_confidence_edit" class="form-label">Confidence Level</label>
                                            <select class="form-select" id="diagnosis_confidence_edit" name="diagnosis_confidence">
                                                <option value="">Select Confidence</option>
                                                <option value="high" <?php echo($consultation['diagnosis_confidence'] ?? '') === 'high' ? 'selected' : ''; ?>>High</option>
                                                <option value="medium" <?php echo($consultation['diagnosis_confidence'] ?? '') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                                <option value="low" <?php echo($consultation['diagnosis_confidence'] ?? '') === 'low' ? 'selected' : ''; ?>>Low</option>
                                                <option value="provisional" <?php echo($consultation['diagnosis_confidence'] ?? '') === 'provisional' ? 'selected' : ''; ?>>Provisional</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="differential_diagnosis_edit" class="form-label">Differential Diagnosis</label>
                                            <textarea class="form-control" id="differential_diagnosis_edit" name="differential_diagnosis" rows="3" placeholder="Alternative diagnoses considered..."><?php echo htmlspecialchars($consultation['differential_diagnosis'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="diagnostic_plan_edit" class="form-label">Diagnostic Plan</label>
                                            <textarea class="form-control" id="diagnostic_plan_edit" name="diagnostic_plan" rows="3" placeholder="Further investigations needed..."><?php echo htmlspecialchars($consultation['diagnostic_plan'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#collapseDiagnosis">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Review of Systems -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSystems">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSystems" aria-expanded="false" aria-controls="collapseSystems">
                                <i class="fas fa-clipboard-list me-2 text-danger"></i> <strong>Review of Systems</strong>
                                <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                                    <?php $systemsBtn = getButtonInfo($consultation['review_of_systems'] ?? '', 'No systems review recorded'); ?>
                                    <a href="#editSystems" class="btn btn-sm <?= $systemsBtn['class'] ?> ms-2" data-bs-toggle="collapse" data-bs-target="#editSystems" aria-expanded="false">
                                        <i class="fas fa-<?= $systemsBtn['text'] === 'Add' ? 'plus' : 'edit' ?>"></i> <?= $systemsBtn['text'] ?>
                                    </a>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseSystems" class="accordion-collapse collapse" aria-labelledby="headingSystems" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <?= !empty($consultation['review_of_systems']) ? nl2br(htmlspecialchars($consultation['review_of_systems'])) :
        '<span class="text-muted">No systems review recorded</span>' ?>
                            </div>
                        </div>
                        <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                        <div id="editSystems" class="accordion-collapse collapse" aria-labelledby="headingSystems" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <form method="post" action="<?= $baseUrl ?>/consultations/update-field/<?= $consultation['consultation_id'] ?>">
                                    <input type="hidden" name="field" value="review_of_systems">
                                    <div class="mb-3">
                                        <label for="review_of_systems_edit" class="form-label">Review of Systems</label>
                                        <textarea class="form-control" id="review_of_systems_edit" name="value" rows="4" placeholder="Document the review of systems..."><?= htmlspecialchars($consultation['review_of_systems'] ?? '') ?></textarea>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#collapseSystems">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Physical Examination -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingExam">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExam" aria-expanded="false" aria-controls="collapseExam">
                                <i class="fas fa-stethoscope me-2 text-danger"></i> <strong>Physical Examination</strong>
                                <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                                    <?php $examBtn = getButtonInfo($consultation['physical_examination'] ?? '', 'No physical examination recorded'); ?>
                                    <a href="#editExam" class="btn btn-sm <?= $examBtn['class'] ?> ms-2" data-bs-toggle="collapse" data-bs-target="#editExam" aria-expanded="false">
                                        <i class="fas fa-<?= $examBtn['text'] === 'Add' ? 'plus' : 'edit' ?>"></i> <?= $examBtn['text'] ?>
                                    </a>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseExam" class="accordion-collapse collapse" aria-labelledby="headingExam" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <?= !empty($consultation['physical_examination']) ? nl2br(htmlspecialchars($consultation['physical_examination'])) :
        '<span class="text-muted">No physical examination recorded</span>' ?>
                            </div>
                        </div>
                        <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                        <div id="editExam" class="accordion-collapse collapse" aria-labelledby="headingExam" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <form method="post" action="<?= $baseUrl ?>/consultations/update-field/<?= $consultation['consultation_id'] ?>">
                                    <input type="hidden" name="field" value="physical_examination">
                                    <div class="mb-3">
                                        <label for="physical_examination_edit" class="form-label">Physical Examination</label>
                                        <textarea class="form-control" id="physical_examination_edit" name="value" rows="4" placeholder="Document the physical examination findings..."><?= htmlspecialchars($consultation['physical_examination'] ?? '') ?></textarea>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#collapseExam">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Diagnosis -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingDiagnosis">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDiagnosis" aria-expanded="false" aria-controls="collapseDiagnosis">
                                <i class="fas fa-diagnoses me-2 text-danger"></i> <strong>Diagnosis</strong>
                                <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                                    <?php $diagnosisBtn = getButtonInfo($consultation['diagnosis'] ?? '', 'No diagnosis recorded'); ?>
                                    <a href="#editDiagnosis" class="btn btn-sm <?= $diagnosisBtn['class'] ?> ms-2" data-bs-toggle="collapse" data-bs-target="#editDiagnosis" aria-expanded="false">
                                        <i class="fas fa-<?= $diagnosisBtn['text'] === 'Add' ? 'plus' : 'edit' ?>"></i> <?= $diagnosisBtn['text'] ?>
                                    </a>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseDiagnosis" class="accordion-collapse collapse" aria-labelledby="headingDiagnosis" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <?= !empty($consultation['diagnosis']) ? nl2br(htmlspecialchars($consultation['diagnosis'])) :
        '<span class="text-muted">No diagnosis recorded</span>' ?>
                            </div>
                        </div>
                        <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                        <div id="editDiagnosis" class="accordion-collapse collapse" aria-labelledby="headingDiagnosis" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <form method="post" action="<?= $baseUrl ?>/consultations/update-field/<?= $consultation['consultation_id'] ?>">
                                    <input type="hidden" name="field" value="diagnosis">
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-12">
                                            <label for="diagnosis_edit" class="form-label">Primary Diagnosis</label>
                                            <textarea class="form-control" id="diagnosis_edit" name="value" rows="3" placeholder="Enter primary diagnosis..."><?php echo htmlspecialchars($consultation['diagnosis'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="diagnosis_confidence_edit" class="form-label">Confidence Level</label>
                                            <select class="form-select" id="diagnosis_confidence_edit" name="diagnosis_confidence">
                                                <option value="">Select Confidence</option>
                                                <option value="high" <?php echo($consultation['diagnosis_confidence'] ?? '') === 'high' ? 'selected' : ''; ?>>High</option>
                                                <option value="medium" <?php echo($consultation['diagnosis_confidence'] ?? '') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                                <option value="low" <?php echo($consultation['diagnosis_confidence'] ?? '') === 'low' ? 'selected' : ''; ?>>Low</option>
                                                <option value="provisional" <?php echo($consultation['diagnosis_confidence'] ?? '') === 'provisional' ? 'selected' : ''; ?>>Provisional</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="differential_diagnosis_edit" class="form-label">Differential Diagnosis</label>
                                            <textarea class="form-control" id="differential_diagnosis_edit" name="differential_diagnosis" rows="2" placeholder="Enter differential diagnoses..."><?php echo htmlspecialchars($consultation['differential_diagnosis'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="diagnostic_plan_edit" class="form-label">Diagnostic Plan</label>
                                        <textarea class="form-control" id="diagnostic_plan_edit" name="diagnostic_plan" rows="3" placeholder="Enter diagnostic plan..."><?php echo htmlspecialchars($consultation['diagnostic_plan'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#collapseDiagnosis">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Treatment Plan -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTreatment">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTreatment" aria-expanded="false" aria-controls="collapseTreatment">
                                <i class="fas fa-pills me-2 text-danger"></i> <strong>Treatment Plan</strong>
                                <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                                    <?php $treatmentBtn = getButtonInfo($consultation['treatment_plan'] ?? '', 'No treatment plan recorded'); ?>
                                    <a href="#editTreatment" class="btn btn-sm <?= $treatmentBtn['class'] ?> ms-2" data-bs-toggle="collapse" data-bs-target="#editTreatment" aria-expanded="false">
                                        <i class="fas fa-<?= $treatmentBtn['text'] === 'Add' ? 'plus' : 'edit' ?>"></i> <?= $treatmentBtn['text'] ?>
                                    </a>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseTreatment" class="accordion-collapse collapse" aria-labelledby="headingTreatment" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <?= !empty($consultation['treatment_plan']) ? nl2br(htmlspecialchars($consultation['treatment_plan'])) :
        '<span class="text-muted">No treatment plan recorded</span>' ?>
                            </div>
                        </div>
                        <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                        <div id="editTreatment" class="accordion-collapse collapse" aria-labelledby="headingTreatment" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <form method="post" action="<?= $baseUrl ?>/consultations/update-field/<?= $consultation['consultation_id'] ?>">
                                    <input type="hidden" name="field" value="treatment_plan">
                                    <div class="mb-3">
                                        <label for="treatment_plan_edit" class="form-label">Treatment Plan</label>
                                        <textarea class="form-control" id="treatment_plan_edit" name="value" rows="4" placeholder="Enter treatment plan..."><?= htmlspecialchars($consultation['treatment_plan'] ?? '') ?></textarea>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#collapseTreatment">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Follow-up Instructions -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFollowup">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFollowup" aria-expanded="false" aria-controls="collapseFollowup">
                                <i class="fas fa-calendar-check me-2 text-danger"></i> <strong>Follow-up Instructions</strong>
                                <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                                    <?php $followupBtn = getButtonInfo($consultation['follow_up_instructions'] ?? '', 'No follow-up instructions recorded'); ?>
                                    <a href="#editFollowup" class="btn btn-sm <?= $followupBtn['class'] ?> ms-2" data-bs-toggle="collapse" data-bs-target="#editFollowup" aria-expanded="false">
                                        <i class="fas fa-<?= $followupBtn['text'] === 'Add' ? 'plus' : 'edit' ?>"></i> <?= $followupBtn['text'] ?>
                                    </a>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseFollowup" class="accordion-collapse collapse" aria-labelledby="headingFollowup" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <?= !empty($consultation['follow_up_instructions']) ? nl2br(htmlspecialchars($consultation['follow_up_instructions'])) :
        '<span class="text-muted">No follow-up instructions recorded</span>' ?>
                            </div>
                        </div>
                        <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                        <div id="editFollowup" class="accordion-collapse collapse" aria-labelledby="headingFollowup" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <form method="post" action="<?= $baseUrl ?>/consultations/update-field/<?= $consultation['consultation_id'] ?>">
                                    <input type="hidden" name="field" value="follow_up_instructions">
                                    <div class="mb-3">
                                        <label for="follow_up_instructions_edit" class="form-label">Follow-up Instructions</label>
                                        <textarea class="form-control" id="follow_up_instructions_edit" name="value" rows="3" placeholder="Enter follow-up instructions..."><?= htmlspecialchars($consultation['follow_up_instructions'] ?? '') ?></textarea>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#collapseFollowup">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Notes -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingNotes">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNotes" aria-expanded="false" aria-controls="collapseNotes">
                                <i class="fas fa-sticky-note me-2 text-danger"></i> <strong>Additional Notes</strong>
                                <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                                    <?php $notesBtn = getButtonInfo($consultation['notes'] ?? '', 'No additional notes'); ?>
                                    <a href="#editNotes" class="btn btn-sm <?= $notesBtn['class'] ?> ms-2" data-bs-toggle="collapse" data-bs-target="#editNotes" aria-expanded="false">
                                        <i class="fas fa-<?= $notesBtn['text'] === 'Add' ? 'plus' : 'edit' ?>"></i> <?= $notesBtn['text'] ?>
                                    </a>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseNotes" class="accordion-collapse collapse" aria-labelledby="headingNotes" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <?= !empty($consultation['notes']) ? nl2br(htmlspecialchars($consultation['notes'])) :
        '<span class="text-muted">No additional notes</span>' ?>
                            </div>
                        </div>
                        <?php if (in_array($userRole, ['doctor', 'admin'])): ?>
                        <div id="editNotes" class="accordion-collapse collapse" aria-labelledby="headingNotes" data-bs-parent="#consultationAccordion">
                            <div class="accordion-body">
                                <form method="post" action="<?= $baseUrl ?>/consultations/update-field/<?= $consultation['consultation_id'] ?>">
                                    <input type="hidden" name="field" value="notes">
                                    <div class="mb-3">
                                        <label for="notes_edit" class="form-label">Additional Notes</label>
                                        <textarea class="form-control" id="notes_edit" name="value" rows="3" placeholder="Enter additional notes..."><?= htmlspecialchars($consultation['notes'] ?? '') ?></textarea>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#collapseNotes">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Patient Information Card -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Patient Information</h6>
                        <a href="<?= $baseUrl ?>/patients/view/<?= $patient['patient_id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt"></i> View Profile
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar avatar-xl bg-primary bg-opacity-10 rounded-circle mb-2">
                                <i class="fas fa-user fa-2x text-primary"></i>
                            </div>
                            <h5><?= htmlspecialchars($consultation['patient_first_name'] . ' ' . $consultation['patient_last_name']) ?></h5>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <small class="text-muted d-block">Date of Birth</small>
                                <span><?= !empty($patient['date_of_birth']) ? date('F j, Y', strtotime($patient['date_of_birth'])) : 'N/A' ?></span>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted d-block">Gender</small>
                                <span><?= !empty($patient['gender']) ? ucfirst($patient['gender']) : 'N/A' ?></span>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted d-block">Phone</small>
                                <span><?= !empty($patient['phone']) ? htmlspecialchars($patient['phone']) : 'N/A' ?></span>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted d-block">Email</small>
                                <span><?= !empty($patient['email']) ? htmlspecialchars($patient['email']) : 'N/A' ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Doctor Information Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Doctor Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar avatar-xl bg-info bg-opacity-10 rounded-circle mb-2">
                                <i class="fas fa-user-md fa-2x text-info"></i>
                            </div>
                            <h5>Dr. <?= htmlspecialchars($consultation['doctor_first_name'] . ' ' . $consultation['doctor_last_name']) ?></h5>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <small class="text-muted d-block">Specialization</small>
                                <span><?= !empty($consultation['specialization']) ? htmlspecialchars($consultation['specialization']) : 'N/A' ?></span>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted d-block">Department</small>
                                <span><?= !empty($consultation['department']) ? htmlspecialchars($consultation['department']) : 'N/A' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($consultation['appointment_id'])): ?>
                <!-- Appointment Information Card -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Appointment</h6>
                        <a href="<?= $baseUrl ?>/appointments/view/<?= $consultation['appointment_id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt"></i> View Details
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted d-block">Appointment ID</small>
                            <span>#<?= htmlspecialchars($consultation['appointment_id']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<!-- end duplicate wrapper removed -->
<script>
// Use global AJAX handler: mark consultation update forms for ajax handling
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('form[action*="/consultations/update-field/"]').forEach(function(f){ f.setAttribute('data-ajax','true'); });
    document.querySelectorAll('form[action*="/consultations/update-vitals/"]').forEach(function(f){ f.setAttribute('data-ajax','true'); });
});
</script>

<!-- Modal for confirming actions -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to <span id="actionText"></span> this consultation?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAction">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle action buttons
    const actionButtons = document.querySelectorAll('.action-btn');
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    const actionText = document.getElementById('actionText');
    const confirmAction = document.getElementById('confirmAction');
    
    let currentAction = '';
    let currentUrl = '';
    
    actionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            currentAction = this.dataset.action;
            currentUrl = this.href;
            
            // Set the action text in the modal
            actionText.textContent = this.dataset.action;
            
            // Show the modal
            confirmModal.show();
        });
    });
    
    // Handle confirm button in modal
    confirmAction.addEventListener('click', function() {
        if (currentAction && currentUrl) {
            window.location.href = currentUrl;
        }
    });
});
</script>
