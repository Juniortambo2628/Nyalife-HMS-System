<?php
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

<form id="consultationForm" action="<?= $isEdit ? site_url("consultations/update/" . $consultation['consultation_id']) : site_url('consultations/store') ?>" method="POST" enctype="multipart/form-data">
    <?php if ($isEdit): ?>
        <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>
    
    <div class="row">
        <!-- Left Column -->
        <div class="col-md-8">
            <!-- Patient and Doctor Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Patient & Doctor Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Patient</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>" readonly>
                                <input type="hidden" name="patient_id" value="<?= $patient['patient_id'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
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
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="consultation_date" class="form-label">Consultation Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="consultation_date" name="consultation_date" 
                                       value="<?= htmlspecialchars($formData['consultation_date'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
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
            
            <!-- Chief Complaint -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Chief Complaint <span class="text-danger">*</span></h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <textarea class="form-control" id="chief_complaint" name="chief_complaint" rows="3" required><?= htmlspecialchars($formData['chief_complaint'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- History of Present Illness -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">History of Present Illness</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <textarea class="form-control tinymce" id="history_present_illness" name="history_present_illness" rows="6"><?= htmlspecialchars($formData['history_present_illness'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Review of Systems -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Review of Systems</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <textarea class="form-control tinymce" id="review_of_systems" name="review_of_systems" rows="6"><?= htmlspecialchars($formData['review_of_systems'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Physical Examination -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Physical Examination</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <textarea class="form-control tinymce" id="physical_examination" name="physical_examination" rows="6"><?= htmlspecialchars($formData['physical_examination'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Diagnosis -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Diagnosis</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <textarea class="form-control tinymce" id="diagnosis" name="diagnosis" rows="4"><?= htmlspecialchars($formData['diagnosis'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Treatment Plan -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Treatment Plan</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <textarea class="form-control tinymce" id="treatment_plan" name="treatment_plan" rows="4"><?= htmlspecialchars($formData['treatment_plan'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Follow-up Instructions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Follow-up Instructions</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <textarea class="form-control" id="follow_up_instructions" name="follow_up_instructions" rows="3"><?= htmlspecialchars($formData['follow_up_instructions'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Notes -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Additional Notes</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional notes or comments"><?= htmlspecialchars($formData['notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column -->
        <div class="col-md-4">
            <!-- Vital Signs -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Vital Signs</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="blood_pressure" class="form-label">Blood Pressure</label>
                                <input type="text" class="form-control" id="blood_pressure" name="blood_pressure" 
                                       value="<?= htmlspecialchars($formData['vital_signs']['blood_pressure'] ?? '') ?>" placeholder="e.g., 120/80">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="pulse" class="form-label">Pulse (bpm)</label>
                                <input type="number" class="form-control" id="pulse" name="pulse" 
                                       value="<?= htmlspecialchars($formData['vital_signs']['pulse'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="temperature" class="form-label">Temperature (°C)</label>
                                <input type="number" step="0.1" class="form-control" id="temperature" name="temperature" 
                                       value="<?= htmlspecialchars($formData['vital_signs']['temperature'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="respiratory_rate" class="form-label">Respiratory Rate</label>
                                <input type="number" class="form-control" id="respiratory_rate" name="respiratory_rate" 
                                       value="<?= htmlspecialchars($formData['vital_signs']['respiratory_rate'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="oxygen_saturation" class="form-label">SpO₂ (%)</label>
                                <input type="number" class="form-control" id="oxygen_saturation" name="oxygen_saturation" 
                                       value="<?= htmlspecialchars($formData['vital_signs']['oxygen_saturation'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="pain_level" class="form-label">Pain Level (0-10)</label>
                                <input type="number" min="0" max="10" class="form-control" id="pain_level" name="pain_level" 
                                       value="<?= htmlspecialchars($formData['vital_signs']['pain_level'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="height" class="form-label">Height (cm)</label>
                                <input type="number" step="0.1" class="form-control" id="height" name="height" 
                                       value="<?= htmlspecialchars($formData['vital_signs']['height'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="weight" class="form-label">Weight (kg)</label>
                                <input type="number" step="0.1" class="form-control" id="weight" name="weight" 
                                       value="<?= htmlspecialchars($formData['vital_signs']['weight'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="bmi" class="form-label">BMI</label>
                                <input type="number" step="0.1" class="form-control" id="bmi" name="bmi" 
                                       value="<?= htmlspecialchars($formData['vital_signs']['bmi'] ?? '') ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Medical History -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Medical History</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="past_medical_history" class="form-label">Past Medical History</label>
                        <textarea class="form-control" id="past_medical_history" name="past_medical_history" rows="3"><?= htmlspecialchars($formData['past_medical_history'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="family_history" class="form-label">Family History</label>
                        <textarea class="form-control" id="family_history" name="family_history" rows="3"><?= htmlspecialchars($formData['family_history'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="social_history" class="form-label">Social History</label>
                        <textarea class="form-control" id="social_history" name="social_history" rows="2"><?= htmlspecialchars($formData['social_history'] ?? '') ?></textarea>
                    </div>
                    
                    <!-- Gynecological History (Conditional) -->
                    <div class="gynecological-field" style="display: none;">
                        <div class="mb-3">
                            <label for="obstetric_history" class="form-label">Obstetric History</label>
                            <textarea class="form-control" id="obstetric_history" name="obstetric_history" rows="2"><?= htmlspecialchars($formData['obstetric_history'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="gynecological_history" class="form-label">Gynecological History</label>
                            <textarea class="form-control" id="gynecological_history" name="gynecological_history" rows="2"><?= htmlspecialchars($formData['gynecological_history'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="menstrual_history" class="form-label">Menstrual History</label>
                            <textarea class="form-control" id="menstrual_history" name="menstrual_history" rows="2"><?= htmlspecialchars($formData['menstrual_history'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="contraceptive_history" class="form-label">Contraceptive History</label>
                            <textarea class="form-control" id="contraceptive_history" name="contraceptive_history" rows="2"><?= htmlspecialchars($formData['contraceptive_history'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="sexual_history" class="form-label">Sexual History</label>
                            <textarea class="form-control" id="sexual_history" name="sexual_history" rows="2"><?= htmlspecialchars($formData['sexual_history'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <a href="<?= $isEdit ? site_url('consultations/view/' . $consultation['consultation_id']) : site_url('consultations') ?>" 
                   class="btn btn-secondary btn-back">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </a>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> <?= $isEdit ? 'Update' : 'Save' ?> Consultation
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
