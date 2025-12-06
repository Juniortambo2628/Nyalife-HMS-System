<?php
/**
 * Nyalife HMS - Edit Consultation View
 */

$pageTitle = 'Edit Consultation - Nyalife HMS';

// Use the renderView method to include the layout
// The content of this file will be captured and inserted into the layout

// Define status options
$statusOptions = [
    'scheduled' => 'Scheduled',
    'in_progress' => 'In Progress',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled'
];

// Set default values for missing fields and map to the correct keys
$consultationId = $consultation['consultation_id'] ?? null;
$consultationDate = $consultation['consultation_date'] ?? date('Y-m-d');
$consultationTime = isset($consultation['consultation_time']) ? $consultation['consultation_time'] : date('H:i');
$consultationStatus = $consultation['consultation_status'] ?? 'scheduled';
$isWalkIn = $consultation['is_walk_in'] ?? 0;
$chiefComplaint = $consultation['chief_complaint'] ?? '';
$notes = $consultation['notes'] ?? '';

// Ensure vital_signs is an array
if (isset($consultation['vital_signs']) && is_string($consultation['vital_signs'])) {
    $consultation['vital_signs'] = json_decode($consultation['vital_signs'], true) ?: [];
} elseif (!isset($consultation['vital_signs'])) {
    $consultation['vital_signs'] = [];
}
?>

    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Edit Consultation</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="<?= $baseUrl ?>/consultations/view/<?= $consultationId ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Consultation
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($errorMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Consultation Form -->
        <div class="card">
            <div class="card-body">
                <form method="post" action="<?= $baseUrl ?>/consultations/edit/<?= $consultationId ?>" id="consultation-form">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" id="patient_id" class="form-select select2" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?= $patient['patient_id'] ?>" <?= $consultation['patient_id'] == $patient['patient_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?> (<?= htmlspecialchars($patient['patient_id']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="doctor_id" class="form-label">Doctor <span class="text-danger">*</span></label>
                            <select name="doctor_id" id="doctor_id" class="form-select select2" required>
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?= $doctor['user_id'] ?>" <?= $consultation['doctor_id'] == $doctor['user_id'] ? 'selected' : '' ?>>
                                        Dr. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="consultation_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control datepicker" id="consultation_date" name="consultation_date" 
                                   value="<?= htmlspecialchars($consultationDate) ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="consultation_time" class="form-label">Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="consultation_time" name="consultation_time" 
                                   value="<?= htmlspecialchars($consultationTime) ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="consultation_status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="consultation_status" id="consultation_status" class="form-select" required>
                                <?php foreach ($statusOptions as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $consultationStatus === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="is_walk_in" name="is_walk_in" value="1" 
                                       <?= $isWalkIn ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_walk_in">
                                    Walk-in Consultation
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion for Medical Information -->
                    <div class="accordion mb-4 mt-4" id="consultationAccordion">
                        <!-- Chief Complaint -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingComplaint">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseComplaint" aria-expanded="true" aria-controls="collapseComplaint">
                                    <i class="fas fa-clipboard-list me-2"></i> Chief Complaint <span class="text-danger ms-1">*</span>
                                </button>
                            </h2>
                            <div id="collapseComplaint" class="accordion-collapse collapse show" aria-labelledby="headingComplaint" data-bs-parent="#consultationAccordion">
                                <div class="accordion-body">
                                    <textarea class="form-control" id="chief_complaint" name="chief_complaint" rows="3" required><?= htmlspecialchars($chiefComplaint) ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Vital Signs -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingVitals">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVitals" aria-expanded="false" aria-controls="collapseVitals">
                                    <i class="fas fa-heartbeat me-2"></i> Vital Signs
                                </button>
                            </h2>
                            <div id="collapseVitals" class="accordion-collapse collapse" aria-labelledby="headingVitals" data-bs-parent="#consultationAccordion">
                                <div class="accordion-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="blood_pressure" class="form-label">Blood Pressure</label>
                                            <input type="text" class="form-control" id="blood_pressure" name="blood_pressure" 
                                                value="<?= htmlspecialchars($consultation['vital_signs']['blood_pressure'] ?? '') ?>" placeholder="e.g., 120/80">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pulse" class="form-label">Pulse (bpm)</label>
                                            <input type="number" class="form-control" id="pulse" name="pulse" 
                                                value="<?= htmlspecialchars($consultation['vital_signs']['pulse'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="temperature" class="form-label">Temp (°C)</label>
                                            <input type="number" step="0.1" class="form-control" id="temperature" name="temperature" 
                                                value="<?= htmlspecialchars($consultation['vital_signs']['temperature'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="respiratory_rate" class="form-label">Resp Rate</label>
                                            <input type="number" class="form-control" id="respiratory_rate" name="respiratory_rate" 
                                                value="<?= htmlspecialchars($consultation['vital_signs']['respiratory_rate'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="oxygen_saturation" class="form-label">SpO₂ (%)</label>
                                            <input type="number" class="form-control" id="oxygen_saturation" name="oxygen_saturation" 
                                                value="<?= htmlspecialchars($consultation['vital_signs']['oxygen_saturation'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pain_level" class="form-label">Pain (0-10)</label>
                                            <input type="number" min="0" max="10" class="form-control" id="pain_level" name="pain_level" 
                                                value="<?= htmlspecialchars($consultation['vital_signs']['pain_level'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="height" class="form-label">Height (cm)</label>
                                            <input type="number" step="0.1" class="form-control" id="height" name="height" 
                                                value="<?= htmlspecialchars($consultation['vital_signs']['height'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="weight" class="form-label">Weight (kg)</label>
                                            <input type="number" step="0.1" class="form-control" id="weight" name="weight" 
                                                value="<?= htmlspecialchars($consultation['vital_signs']['weight'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="bmi" class="form-label">BMI</label>
                                            <input type="number" step="0.1" class="form-control" id="bmi" name="bmi" 
                                                value="<?= htmlspecialchars($consultation['vital_signs']['bmi'] ?? '') ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- History of Present Illness -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingHistory">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHistory" aria-expanded="false" aria-controls="collapseHistory">
                                    <i class="fas fa-file-medical me-2"></i> History of Present Illness
                                </button>
                            </h2>
                            <div id="collapseHistory" class="accordion-collapse collapse" aria-labelledby="headingHistory" data-bs-parent="#consultationAccordion">
                                <div class="accordion-body">
                                    <textarea class="form-control" id="history_present_illness" name="history_present_illness" rows="4"><?= htmlspecialchars($consultation['history_present_illness'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Review of Systems -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingSystems">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSystems" aria-expanded="false" aria-controls="collapseSystems">
                                    <i class="fas fa-clipboard-check me-2"></i> Review of Systems
                                </button>
                            </h2>
                            <div id="collapseSystems" class="accordion-collapse collapse" aria-labelledby="headingSystems" data-bs-parent="#consultationAccordion">
                                <div class="accordion-body">
                                    <textarea class="form-control" id="review_of_systems" name="review_of_systems" rows="4"><?= htmlspecialchars($consultation['review_of_systems'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Physical Examination -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingExam">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExam" aria-expanded="false" aria-controls="collapseExam">
                                    <i class="fas fa-stethoscope me-2"></i> Physical Examination
                                </button>
                            </h2>
                            <div id="collapseExam" class="accordion-collapse collapse" aria-labelledby="headingExam" data-bs-parent="#consultationAccordion">
                                <div class="accordion-body">
                                    <textarea class="form-control" id="physical_examination" name="physical_examination" rows="4"><?= htmlspecialchars($consultation['physical_examination'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Diagnosis -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingDiagnosis">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDiagnosis" aria-expanded="false" aria-controls="collapseDiagnosis">
                                    <i class="fas fa-diagnoses me-2"></i> Diagnosis
                                </button>
                            </h2>
                            <div id="collapseDiagnosis" class="accordion-collapse collapse" aria-labelledby="headingDiagnosis" data-bs-parent="#consultationAccordion">
                                <div class="accordion-body">
                                    <textarea class="form-control" id="diagnosis" name="diagnosis" rows="4"><?= htmlspecialchars($consultation['diagnosis'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Treatment Plan -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTreatment">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTreatment" aria-expanded="false" aria-controls="collapseTreatment">
                                    <i class="fas fa-pills me-2"></i> Treatment Plan
                                </button>
                            </h2>
                            <div id="collapseTreatment" class="accordion-collapse collapse" aria-labelledby="headingTreatment" data-bs-parent="#consultationAccordion">
                                <div class="accordion-body">
                                    <textarea class="form-control" id="treatment_plan" name="treatment_plan" rows="4"><?= htmlspecialchars($consultation['treatment_plan'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Follow-up Instructions -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFollowup">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFollowup" aria-expanded="false" aria-controls="collapseFollowup">
                                    <i class="fas fa-calendar-check me-2"></i> Follow-up Instructions
                                </button>
                            </h2>
                            <div id="collapseFollowup" class="accordion-collapse collapse" aria-labelledby="headingFollowup" data-bs-parent="#consultationAccordion">
                                <div class="accordion-body">
                                    <textarea class="form-control" id="follow_up_instructions" name="follow_up_instructions" rows="3"><?= htmlspecialchars($consultation['follow_up_instructions'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingNotes">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNotes" aria-expanded="false" aria-controls="collapseNotes">
                                    <i class="fas fa-sticky-note me-2"></i> Additional Notes
                                </button>
                            </h2>
                            <div id="collapseNotes" class="accordion-collapse collapse" aria-labelledby="headingNotes" data-bs-parent="#consultationAccordion">
                                <div class="accordion-body">
                                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional notes or comments"><?= htmlspecialchars($notes) ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Medical History -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingMedicalHistory">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMedicalHistory" aria-expanded="false" aria-controls="collapseMedicalHistory">
                                    <i class="fas fa-history me-2"></i> Medical History
                                </button>
                            </h2>
                            <div id="collapseMedicalHistory" class="accordion-collapse collapse" aria-labelledby="headingMedicalHistory" data-bs-parent="#consultationAccordion">
                                <div class="accordion-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="past_medical_history" class="form-label">Past Medical History</label>
                                            <textarea class="form-control" id="past_medical_history" name="past_medical_history" rows="2"><?= htmlspecialchars($consultation['past_medical_history'] ?? '') ?></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="family_history" class="form-label">Family History</label>
                                            <textarea class="form-control" id="family_history" name="family_history" rows="2"><?= htmlspecialchars($consultation['family_history'] ?? '') ?></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="social_history" class="form-label">Social History</label>
                                            <textarea class="form-control" id="social_history" name="social_history" rows="2"><?= htmlspecialchars($consultation['social_history'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                    
                                    <!-- Gynecological History -->
                                    <div class="gynecological-field">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="obstetric_history" class="form-label">Obstetric History</label>
                                                <textarea class="form-control" id="obstetric_history" name="obstetric_history" rows="2"><?= htmlspecialchars($consultation['obstetric_history'] ?? '') ?></textarea>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="gynecological_history" class="form-label">Gynecological History</label>
                                                <textarea class="form-control" id="gynecological_history" name="gynecological_history" rows="2"><?= htmlspecialchars($consultation['gynecological_history'] ?? '') ?></textarea>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="menstrual_history" class="form-label">Menstrual History</label>
                                                <textarea class="form-control" id="menstrual_history" name="menstrual_history" rows="2"><?= htmlspecialchars($consultation['menstrual_history'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <label for="contraceptive_history" class="form-label">Contraceptive History</label>
                                                <textarea class="form-control" id="contraceptive_history" name="contraceptive_history" rows="2"><?= htmlspecialchars($consultation['contraceptive_history'] ?? '') ?></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="sexual_history" class="form-label">Sexual History</label>
                                                <textarea class="form-control" id="sexual_history" name="sexual_history" rows="2"><?= htmlspecialchars($consultation['sexual_history'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= $baseUrl ?>/consultations/view/<?= $consultationId ?>" class="btn btn-outline-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Consultation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
// Initialize components when the page is loaded or reloaded via AJAX
document.addEventListener('DOMContentLoaded', initEditConsultationPage);
document.addEventListener('page:loaded', initEditConsultationPage);

function initEditConsultationPage() {
    // Initialize Select2 dropdowns if available
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Select an option'
        });
    }
    
    // Initialize datepickers if available
    if (typeof $.fn.datepicker !== 'undefined') {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
    }
    
    // BMI Calculator
    const heightInput = document.getElementById('height');
    const weightInput = document.getElementById('weight');
    const bmiInput = document.getElementById('bmi');
    
    function calculateBMI() {
        if (heightInput && weightInput && heightInput.value && weightInput.value) {
            const heightInMeters = parseFloat(heightInput.value) / 100;
            const weightInKg = parseFloat(weightInput.value);
            
            if (heightInMeters > 0 && weightInKg > 0) {
                const bmi = weightInKg / (heightInMeters * heightInMeters);
                bmiInput.value = bmi.toFixed(1);
            }
        }
    }
    
    if (heightInput && weightInput) {
        heightInput.addEventListener('input', calculateBMI);
        weightInput.addEventListener('input', calculateBMI);
        
        // Calculate BMI on page load if values exist
        calculateBMI();
    }
    
    // Handle form submission with AJAX
    const consultationForm = document.getElementById('consultation-form');
    if (consultationForm && typeof Components !== 'undefined') {
        consultationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (!consultationForm.checkValidity()) {
                e.stopPropagation();
                consultationForm.classList.add('was-validated');
                return;
            }
            
            // Submit form via AJAX
            const formData = new FormData(consultationForm);
            
            fetch(consultationForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to the view page
                    if (data.redirect) {
                        Components.loadPage(data.redirect);
                    } else {
                        Components.loadPage('<?= $baseUrl ?>/consultations/view/<?= $consultationId ?>');
                    }
                } else {
                    // Display error message
                    alert(data.message || 'An error occurred while updating the consultation.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected error occurred. Please try again.');
            });
        });
    }
}
</script>
