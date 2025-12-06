<?php
/**
 * Nyalife HMS - Consultation Form
 */

$pageTitle = 'Consultation Form - Nyalife HMS';

// Get form data from session or existing consultation
$formData = $_SESSION['form_data'] ?? [];
$consultation = $consultation ?? [];
$isEdit = !empty($consultation);

// Clear form data from session if it exists
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}

// Merge form data with consultation data (form data takes precedence)
$formData = array_merge($consultation, $formData);

// Ensure vital_signs is an array
if (isset($formData['vital_signs']) && is_string($formData['vital_signs'])) {
    $formData['vital_signs'] = json_decode($formData['vital_signs'], true) ?: [];
} elseif (!isset($formData['vital_signs'])) {
    $formData['vital_signs'] = [];
}

// Set default values for new consultations
if (!$isEdit) {
    $formData['consultation_date'] = $formData['consultation_date'] ?? date('Y-m-d H:i:s');
    $formData['consultation_status'] = $formData['consultation_status'] ?? 'open';
}
?>


    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">Edit Consultation</h2>
        </div>
    </div>

    <form id="consultationForm" action="<?= $isEdit ? $baseUrl . "/consultations/update/" . $consultation['consultation_id'] : $baseUrl . '/consultations/store' ?>" method="POST" enctype="multipart/form-data">
        <?php if ($isEdit): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>
        
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Patient</label>
                                    <?php if (isset($appointment) && !empty($appointment)): ?>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>" readonly>
                                        <input type="hidden" name="patient_id" value="<?= $patient['patient_id'] ?? '' ?>">
                                    <?php else: ?>
                                        <select class="form-select select2" id="patient_id" name="patient_id" required>
                                            <option value="">Select Patient</option>
                                            <?php foreach ($patients as $p): ?>
                                                <option value="<?= $p['patient_id'] ?>" <?= ($patient['patient_id'] ?? $formData['patient_id'] ?? '') == $p['patient_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="doctor_id" class="form-label">Doctor <span class="text-danger">*</span></label>
                                    <select class="form-select select2" id="doctor_id" name="doctor_id" required>
                                        <option value="">Select Doctor</option>
                                        <?php foreach ($doctors as $doctor): ?>
                                            <option value="<?= $doctor['user_id'] ?>" <?= ($formData['doctor_id'] ?? '') == $doctor['user_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="consultation_date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="consultation_date" name="consultation_date" 
                                        value="<?= htmlspecialchars($formData['consultation_date'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="consultation_status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="consultation_status" name="consultation_status" required>
                                        <option value="open" <?= ($formData['consultation_status'] ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
                                        <option value="in_progress" <?= ($formData['consultation_status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="completed" <?= ($formData['consultation_status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="cancelled" <?= ($formData['consultation_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NYALIFE HISTORY TEMPLATE Accordion -->
        <div class="accordion mb-4" id="consultationAccordion">
            <!-- Patient Details/BIODATA -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingPatientDetails">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePatientDetails" aria-expanded="true" aria-controls="collapsePatientDetails">
                        Patient Details/BIODATA <span class="text-danger ms-1">*</span>
                    </button>
                </h2>
                <div id="collapsePatientDetails" class="accordion-collapse collapse show" aria-labelledby="headingPatientDetails" data-bs-parent="#consultationAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Patient</label>
                                    <?php if (isset($appointment) && !empty($appointment)): ?>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>" readonly>
                                        <input type="hidden" name="patient_id" value="<?= $patient['patient_id'] ?? '' ?>">
                                    <?php else: ?>
                                        <select class="form-select select2" id="patient_id" name="patient_id" required>
                                            <option value="">Select Patient</option>
                                            <?php foreach ($patients as $p): ?>
                                                <option value="<?= $p['patient_id'] ?>" <?= ($patient['patient_id'] ?? $formData['patient_id'] ?? '') == $p['patient_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="doctor_id" class="form-label">Doctor <span class="text-danger">*</span></label>
                                    <select class="form-select select2" id="doctor_id" name="doctor_id" required>
                                        <option value="">Select Doctor</option>
                                        <?php foreach ($doctors as $doctor): ?>
                                            <option value="<?= $doctor['user_id'] ?>" <?= ($formData['doctor_id'] ?? '') == $doctor['user_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="consultation_date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="consultation_date" name="consultation_date"
                                        value="<?= htmlspecialchars($formData['consultation_date'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="consultation_status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="consultation_status" name="consultation_status" required>
                                        <option value="open" <?= ($formData['consultation_status'] ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
                                        <option value="in_progress" <?= ($formData['consultation_status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="completed" <?= ($formData['consultation_status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="cancelled" <?= ($formData['consultation_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vital Signs -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingVitals">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVitals" aria-expanded="false" aria-controls="collapseVitals">
                        Vital Signs
                    </button>
                </h2>
                <div id="collapseVitals" class="accordion-collapse collapse" aria-labelledby="headingVitals" data-bs-parent="#consultationAccordion">
                    <div class="accordion-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="blood_pressure" class="form-label">Blood Pressure</label>
                                <input type="text" class="form-control" id="blood_pressure" name="blood_pressure"
                                    value="<?= htmlspecialchars($formData['vital_signs']['blood_pressure'] ?? '') ?>" placeholder="e.g., 120/80">
                            </div>
                            <div class="col-md-3">
                                <label for="pulse" class="form-label">Pulse (bpm)</label>
                                <input type="number" class="form-control" id="pulse" name="pulse"
                                    value="<?= htmlspecialchars($formData['vital_signs']['pulse'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="temperature" class="form-label">Temp (°C)</label>
                                <input type="number" step="0.1" class="form-control" id="temperature" name="temperature"
                                    value="<?= htmlspecialchars($formData['vital_signs']['temperature'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="respiratory_rate" class="form-label">Resp Rate</label>
                                <input type="number" class="form-control" id="respiratory_rate" name="respiratory_rate"
                                    value="<?= htmlspecialchars($formData['vital_signs']['respiratory_rate'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="oxygen_saturation" class="form-label">SpO₂ (%)</label>
                                <input type="number" class="form-control" id="oxygen_saturation" name="oxygen_saturation"
                                    value="<?= htmlspecialchars($formData['vital_signs']['oxygen_saturation'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="pain_level" class="form-label">Pain (0-10)</label>
                                <input type="number" min="0" max="10" class="form-control" id="pain_level" name="pain_level"
                                    value="<?= htmlspecialchars($formData['vital_signs']['pain_level'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="height" class="form-label">Height (cm)</label>
                                <input type="number" step="0.1" class="form-control" id="height" name="height"
                                    value="<?= htmlspecialchars($formData['vital_signs']['height'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="weight" class="form-label">Weight (kg)</label>
                                <input type="number" step="0.1" class="form-control" id="weight" name="weight"
                                    value="<?= htmlspecialchars($formData['vital_signs']['weight'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="bmi" class="form-label">BMI</label>
                                <input type="number" step="0.1" class="form-control" id="bmi" name="bmi"
                                    value="<?= htmlspecialchars($formData['vital_signs']['bmi'] ?? '') ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chief Complaints -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingComplaint">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseComplaint" aria-expanded="false" aria-controls="collapseComplaint">
                        Chief Complaints <span class="text-danger ms-1">*</span>
                    </button>
                </h2>
                <div id="collapseComplaint" class="accordion-collapse collapse" aria-labelledby="headingComplaint" data-bs-parent="#consultationAccordion">
                    <div class="accordion-body">
                        <textarea class="form-control" id="chief_complaint" name="chief_complaint" rows="3" required><?= htmlspecialchars($formData['chief_complaint'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- History of Presenting Illness -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingHistory">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHistory" aria-expanded="false" aria-controls="collapseHistory">
                        History of Presenting Illness
                    </button>
                </h2>
                <div id="collapseHistory" class="accordion-collapse collapse" aria-labelledby="headingHistory" data-bs-parent="#consultationAccordion">
                    <div class="accordion-body">
                        <textarea class="form-control" id="history_present_illness" name="history_present_illness" rows="4" placeholder="Describe the current illness in detail including onset, duration, severity, associated symptoms, etc."><?= htmlspecialchars($formData['history_present_illness'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Gynaecological History -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingGynHistory">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGynHistory" aria-expanded="false" aria-controls="collapseGynHistory">
                        Gynaecological History
                    </button>
                </h2>
                <div id="collapseGynHistory" class="accordion-collapse collapse" aria-labelledby="headingGynHistory" data-bs-parent="#consultationAccordion">
                    <div class="accordion-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="menstrual_history" class="form-label"><i class="fas fa-calendar-alt text-primary me-1"></i>Menstrual History</label>
                                <p class="text-muted small mb-2">Date of last normal menstrual period, Regularity of Flow, Number of days, Dysmenorrhea</p>
                                <textarea class="form-control" id="menstrual_history" name="menstrual_history" rows="3" placeholder="LMP: DD/MM/YYYY, Cycle: Regular/Irregular, Duration: X days, Flow: Heavy/Moderate/Light, Pain: Yes/No with details..."><?= htmlspecialchars($formData['menstrual_history'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="cervical_screening" class="form-label"><i class="fas fa-search text-primary me-1"></i>Cervical Cancer Screening History/Pap Smear Test</label>
                                <textarea class="form-control" id="cervical_screening" name="cervical_screening" rows="3" placeholder="Last pap smear date, Results, HPV vaccination status, Any abnormal results..."><?= htmlspecialchars($formData['cervical_screening'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="contraceptive_history" class="form-label"><i class="fas fa-pills text-primary me-1"></i>Contraception/Birth Control History</label>
                                <textarea class="form-control" id="contraceptive_history" name="contraceptive_history" rows="3" placeholder="Current/Previous contraceptive methods, Duration of use, Side effects, Effectiveness..."><?= htmlspecialchars($formData['contraceptive_history'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="sexual_history" class="form-label"><i class="fas fa-heart text-primary me-1"></i>Sexual Health History</label>
                                <textarea class="form-control" id="sexual_history" name="sexual_history" rows="3" placeholder="Age at first intercourse, Number of partners, Current sexual activity, History of STIs, Safe sex practices..."><?= htmlspecialchars($formData['sexual_history'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Obstetric History -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingObstetric">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseObstetric" aria-expanded="false" aria-controls="collapseObstetric">
                        Obstetric History
                    </button>
                </h2>
                <div id="collapseObstetric" class="accordion-collapse collapse" aria-labelledby="headingObstetric" data-bs-parent="#consultationAccordion">
                    <div class="accordion-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="parity" class="form-label"><i class="fas fa-baby text-primary me-1"></i>Parity/Number of Pregnancies</label>
                                <input type="text" class="form-control" id="parity" name="parity" value="<?= htmlspecialchars($formData['parity'] ?? '') ?>" placeholder="G3P2A1 (Gravida 3, Para 2, Abortion 1)">
                            </div>
                            <div class="col-md-4">
                                <label for="current_pregnancy" class="form-label"><i class="fas fa-clock text-primary me-1"></i>History of Current Pregnancy</label>
                                <textarea class="form-control" id="current_pregnancy" name="current_pregnancy" rows="2" placeholder="Gestational age, Complications, Medications, Fetal movements..."> <?= htmlspecialchars($formData['current_pregnancy'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-4">
                                <label for="past_obstetric" class="form-label"><i class="fas fa-history text-primary me-1"></i>Past OB History/Pregnancy History</label>
                                <textarea class="form-control" id="past_obstetric" name="past_obstetric" rows="2" placeholder="Year/Place of birth/Duration/Labour hours/Mode/Outcome/Sex/Weight/Complications..."> <?= htmlspecialchars($formData['past_obstetric'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <label for="obstetric_history" class="form-label"><i class="fas fa-file-medical text-primary me-1"></i>Detailed Obstetric History</label>
                                <textarea class="form-control" id="obstetric_history" name="obstetric_history" rows="4" placeholder="Comprehensive pregnancy history including all previous pregnancies with details..."> <?= htmlspecialchars($formData['obstetric_history'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical and Surgical History -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingMedicalSurgical">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMedicalSurgical" aria-expanded="false" aria-controls="collapseMedicalSurgical">
                        Medical and Surgical History
                    </button>
                </h2>
                <div id="collapseMedicalSurgical" class="accordion-collapse collapse" aria-labelledby="headingMedicalSurgical" data-bs-parent="#consultationAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="past_medical_history" class="form-label"><i class="fas fa-stethoscope text-primary me-1"></i>Past Medical History</label>
                                <textarea class="form-control" id="past_medical_history" name="past_medical_history" rows="3" placeholder="Previous illnesses, chronic conditions, hospitalizations, allergies..."> <?= htmlspecialchars($formData['past_medical_history'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="surgical_history" class="form-label"><i class="fas fa-surgery text-primary me-1"></i>Surgical History</label>
                                <textarea class="form-control" id="surgical_history" name="surgical_history" rows="3" placeholder="Previous surgeries, procedures, complications, anesthesia reactions..."> <?= htmlspecialchars($formData['surgical_history'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Family Social History -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingFamilySocial">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFamilySocial" aria-expanded="false" aria-controls="collapseFamilySocial">
                        Family Social History
                    </button>
                </h2>
                <div id="collapseFamilySocial" class="accordion-collapse collapse" aria-labelledby="headingFamilySocial" data-bs-parent="#consultationAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="family_history" class="form-label"><i class="fas fa-users text-primary me-1"></i>Family History</label>
                                <textarea class="form-control" id="family_history" name="family_history" rows="3" placeholder="Family medical history, hereditary conditions, similar illnesses in family..."> <?= htmlspecialchars($formData['family_history'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="social_history" class="form-label"><i class="fas fa-home text-primary me-1"></i>Social History</label>
                                <textarea class="form-control" id="social_history" name="social_history" rows="3" placeholder="Occupation, smoking/alcohol habits, living conditions, support system..."> <?= htmlspecialchars($formData['social_history'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review of Systems -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingSystems">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSystems" aria-expanded="false" aria-controls="collapseSystems">
                        Review of Systems
                    </button>
                </h2>
                <div id="collapseSystems" class="accordion-collapse collapse" aria-labelledby="headingSystems" data-bs-parent="#consultationAccordion">
                    <div class="accordion-body">
                        <textarea class="form-control" id="review_of_systems" name="review_of_systems" rows="4" placeholder="Systematic review of all body systems - constitutional, cardiovascular, respiratory, gastrointestinal, genitourinary, musculoskeletal, neurological, psychiatric, endocrine, hematologic, dermatologic..."> <?= htmlspecialchars($formData['review_of_systems'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingSummary">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSummary" aria-expanded="false" aria-controls="collapseSummary">
                        Summary
                    </button>
                </h2>
                <div id="collapseSummary" class="accordion-collapse collapse" aria-labelledby="headingSummary" data-bs-parent="#consultationAccordion">
                    <div class="accordion-body">
                        <textarea class="form-control" id="clinical_summary" name="clinical_summary" rows="4" placeholder="Brief summary of the case highlighting key findings, current status, and clinical reasoning..."> <?= htmlspecialchars($formData['clinical_summary'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Examination -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingExam">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExam" aria-expanded="false" aria-controls="collapseExam">
                        Examination
                    </button>
                </h2>
                <div id="collapseExam" class="accordion-collapse collapse" aria-labelledby="headingExam" data-bs-parent="#consultationAccordion">
                    <div class="accordion-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="general_examination" class="form-label"><i class="fas fa-user-check text-primary me-1"></i>General Examination</label>
                                <textarea class="form-control" id="general_examination" name="general_examination" rows="3" placeholder="General appearance, vital signs, mental status, nutritional status..."> <?= htmlspecialchars($formData['general_examination'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="systems_examination" class="form-label"><i class="fas fa-heartbeat text-primary me-1"></i>Systems Examination Findings</label>
                                <textarea class="form-control" id="systems_examination" name="systems_examination" rows="3" placeholder="Systematic examination of cardiovascular, respiratory, abdominal, neurological, musculoskeletal systems..."> <?= htmlspecialchars($formData['systems_examination'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <label for="physical_examination" class="form-label"><i class="fas fa-stethoscope text-primary me-1"></i>Detailed Physical Examination</label>
                                <textarea class="form-control" id="physical_examination" name="physical_examination" rows="4" placeholder="Complete physical examination findings..."> <?= htmlspecialchars($formData['physical_examination'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Impression/Diagnosis -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingDiagnosis">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDiagnosis" aria-expanded="false" aria-controls="collapseDiagnosis">
                        Impression/Diagnosis
                    </button>
                </h2>
                <div id="collapseDiagnosis" class="accordion-collapse collapse" aria-labelledby="headingDiagnosis" data-bs-parent="#consultationAccordion">
                    <div class="accordion-body">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="diagnosis" class="form-label"><i class="fas fa-clipboard-check text-primary me-1"></i>Primary Diagnosis</label>
                                <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3" placeholder="Primary diagnosis with ICD code if available..."> <?= htmlspecialchars($formData['diagnosis'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-4">
                                <label for="diagnosis_confidence" class="form-label"><i class="fas fa-percentage text-primary me-1"></i>Confidence Level</label>
                                <select class="form-select" id="diagnosis_confidence" name="diagnosis_confidence">
                                    <option value="">Select Confidence</option>
                                    <option value="high" <?= ($formData['diagnosis_confidence'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                                    <option value="medium" <?= ($formData['diagnosis_confidence'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                                    <option value="low" <?= ($formData['diagnosis_confidence'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                                    <option value="provisional" <?= ($formData['diagnosis_confidence'] ?? '') === 'provisional' ? 'selected' : '' ?>>Provisional</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="differential_diagnosis" class="form-label"><i class="fas fa-list text-primary me-1"></i>Differential Diagnosis</label>
                                <textarea class="form-control" id="differential_diagnosis" name="differential_diagnosis" rows="3" placeholder="Alternative diagnoses considered..."> <?= htmlspecialchars($formData['differential_diagnosis'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="diagnostic_plan" class="form-label"><i class="fas fa-flask text-primary me-1"></i>Diagnostic Plan</label>
                                <textarea class="form-control" id="diagnostic_plan" name="diagnostic_plan" rows="3" placeholder="Further investigations needed..."> <?= htmlspecialchars($formData['diagnostic_plan'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Treatment Plan -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTreatment">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTreatment" aria-expanded="false" aria-controls="collapseTreatment">
                        Treatment Plan
                    </button>
                </h2>
                <div id="collapseTreatment" class="accordion-collapse collapse" aria-labelledby="headingTreatment" data-bs-parent="#consultationAccordion">
                    <div class="accordion-body">
                        <textarea class="form-control" id="treatment_plan" name="treatment_plan" rows="4" placeholder="Detailed treatment plan including medications, procedures, lifestyle modifications, patient education..."> <?= htmlspecialchars($formData['treatment_plan'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Follow-up Instructions -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingFollowup">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFollowup" aria-expanded="false" aria-controls="collapseFollowup">
                        Follow-up Instructions
                    </button>
                </h2>
                <div id="collapseFollowup" class="accordion-collapse collapse" aria-labelledby="headingFollowup" data-bs-parent="#consultationAccordion">
                    <div class="accordion-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="follow_up_instructions" class="form-label"><i class="fas fa-calendar-check text-primary me-1"></i>Follow-up Instructions</label>
                                <textarea class="form-control" id="follow_up_instructions" name="follow_up_instructions" rows="3" placeholder="When to return, warning signs, contact information..."> <?= htmlspecialchars($formData['follow_up_instructions'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="next_appointment" class="form-label"><i class="fas fa-calendar-plus text-primary me-1"></i>Next Appointment</label>
                                <input type="text" class="form-control" id="next_appointment" name="next_appointment" value="<?= htmlspecialchars($formData['next_appointment'] ?? '') ?>" placeholder="Schedule next visit if needed">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Notes -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingNotes">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNotes" aria-expanded="false" aria-controls="collapseNotes">
                        Additional Notes
                    </button>
                </h2>
                <div id="collapseNotes" class="accordion-collapse collapse" aria-labelledby="headingNotes" data-bs-parent="#consultationAccordion">
                    <div class="accordion-body">
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional notes, observations, or comments not covered in other sections..."> <?= htmlspecialchars($formData['notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form submission buttons -->
        <div class="d-flex justify-content-between mt-4 mb-5">
            <a href="<?= isset($isEdit) && $isEdit ? $baseUrl . '/consultations/view/' . $consultation['consultation_id'] : $baseUrl . '/consultations' ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> <?= isset($isEdit) && $isEdit ? 'Update' : 'Save' ?> Consultation
            </button>
        </div>
    </form>
<!-- end duplicate wrapper removed -->

<script>
// Initialize datetime fields
document.addEventListener('DOMContentLoaded', function() {
    // Set default date if empty
    if (!document.getElementById('consultation_date').value) {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        
        document.getElementById('consultation_date').value = `${year}-${month}-${day} ${hours}:${minutes}`;
    }
    
    // Initialize datepicker if plugin available
    try {
        if (window.jQuery && typeof $('#consultation_date').datetimepicker === 'function') {
            $('#consultation_date').datetimepicker({
                format: 'YYYY-MM-DD HH:mm:ss',
                icons: {
                    time: 'fas fa-clock',
                    date: 'fas fa-calendar',
                    up: 'fas fa-arrow-up',
                    down: 'fas fa-arrow-down',
                    previous: 'fas fa-chevron-left',
                    next: 'fas fa-chevron-right',
                    today: 'fas fa-calendar-check',
                    clear: 'fas fa-trash',
                    close: 'fas fa-times'
                }
            });
        }
    } catch (e) {
        // fail-safe: do nothing if datetimepicker is not available
    }
    
    // BMI Calculator
    const heightInput = document.getElementById('height');
    const weightInput = document.getElementById('weight');
    const bmiInput = document.getElementById('bmi');
    
    function calculateBMI() {
        if (heightInput.value && weightInput.value) {
            const heightInMeters = parseFloat(heightInput.value) / 100;
            const weightInKg = parseFloat(weightInput.value);
            
            if (heightInMeters > 0 && weightInKg > 0) {
                const bmi = weightInKg / (heightInMeters * heightInMeters);
                bmiInput.value = bmi.toFixed(1);
            }
        }
    }
    
    if (heightInput) heightInput.addEventListener('input', calculateBMI);
    if (weightInput) weightInput.addEventListener('input', calculateBMI);
    
    // Calculate BMI on page load if values exist
    calculateBMI();
    
    // Handle form submission
    document.querySelector('form').addEventListener('submit', function(e) {
        // Ensure datetime fields are not empty
        const consultationDate = document.getElementById('consultation_date');
        if (!consultationDate.value) {
            e.preventDefault();
            alert('Please enter a consultation date');
            consultationDate.focus();
        }
    });
    
    // Patient gender detection for gynecological fields
    const patientSelect = document.getElementById('patient_id');
    if (patientSelect) {
        patientSelect.addEventListener('change', function() {
            // This is a placeholder - in a real app you would fetch the patient's gender
            // and show/hide gynecological fields based on that
            // For now, we'll just keep them hidden by default
            
            // Example of how to fetch patient gender using AJAX:
            /*
            const patientId = this.value;
            if (patientId) {
                fetch(`${baseUrl}/api/patients/${patientId}/gender`)
                    .then(response => response.json())
                    .then(data => {
                        const gynFields = document.querySelector('.gynecological-field');
                        if (data.gender === 'Female') {
                            gynFields.style.display = 'block';
                        } else {
                            gynFields.style.display = 'none';
                        }
                    });
            }
            */
        });
    }
});
</script>
