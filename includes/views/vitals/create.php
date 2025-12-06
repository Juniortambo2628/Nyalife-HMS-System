<?php
/**
 * Nyalife HMS - Create Vital Signs View
 */

$pageTitle = 'Create Vital Signs - Nyalife HMS';
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Record Vital Signs</h5>
                </div>
                <div class="card-body">
                    <form id="vitalSignForm" action="<?= $baseUrl ?>/vitals/store" method="POST" class="needs-validation" novalidate data-ajax="true">
                        <?php if (isset($appointmentId) && $appointmentId): ?>
                        <input type="hidden" name="appointment_id" value="<?= (int)$appointmentId ?>">
                        <?php endif; ?>
                        <?php if (!empty($returnUrl)): ?>
                        <input type="hidden" name="return" value="<?= htmlspecialchars($returnUrl) ?>">
                        <?php endif; ?>
                        <?php if (!isset($patient) || empty($patient)): ?>
                        <div class="form-group mb-3">
                            <label for="patient_id">Select Patient</label>
                            <select name="patient_id" id="patient_id" class="form-control select2" required>
                                <option value="">-- Select Patient --</option>
                                <?php foreach ($patients as $p): ?>
                                <option value="<?= $p['patient_id'] ?>"
                                    data-first_name="<?= htmlspecialchars($p['first_name'] ?? '') ?>"
                                    data-last_name="<?= htmlspecialchars($p['last_name'] ?? '') ?>"
                                    data-phone="<?= htmlspecialchars($p['phone'] ?? '') ?>"
                                    data-email="<?= htmlspecialchars($p['email'] ?? '') ?>"
                                    data-dob="<?= htmlspecialchars($p['date_of_birth'] ?? '') ?>"
                                    data-blood_group="<?= htmlspecialchars($p['blood_group'] ?? '') ?>"
                                ><?= htmlspecialchars(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) ?> (<?= htmlspecialchars($p['patient_number'] ?? '') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a patient</div>
                        </div>

                        <div id="selectedPatientInfo" class="alert alert-light d-none">
                            <strong>Selected Patient:</strong>
                            <div id="selectedPatientName"></div>
                            <div id="selectedPatientDetails" class="small text-muted"></div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <strong>Patient:</strong> <?= htmlspecialchars(trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''))) ?> (<?= htmlspecialchars($patient['patient_number'] ?? (string)($patient['patient_id'] ?? '')) ?>)
                        </div>
                        <input type="hidden" name="patient_id" value="<?= htmlspecialchars($patient['patient_id'] ?? '') ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="blood_pressure">Blood Pressure (mmHg)</label>
                                    <input type="text" class="form-control" id="blood_pressure" name="blood_pressure" placeholder="e.g. 120/80">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="pulse">Pulse Rate (bpm)</label>
                                    <input type="number" class="form-control" id="pulse" name="pulse" placeholder="e.g. 72">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="temperature">Temperature (°C)</label>
                                    <input type="number" step="0.1" class="form-control" id="temperature" name="temperature" placeholder="e.g. 37.0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="respiratory_rate">Respiratory Rate (breaths/min)</label>
                                    <input type="number" class="form-control" id="respiratory_rate" name="respiratory_rate" placeholder="e.g. 16">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="oxygen_saturation">Oxygen Saturation (%)</label>
                                    <input type="number" step="0.1" class="form-control" id="oxygen_saturation" name="oxygen_saturation" placeholder="e.g. 98">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="pain_level">Pain Level (0-10)</label>
                                    <input type="number" min="0" max="10" class="form-control" id="pain_level" name="pain_level" placeholder="Scale 0-10">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="height">Height (cm)</label>
                                    <input type="number" step="0.1" class="form-control" id="height" name="height" placeholder="e.g. 170">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="weight">Weight (kg)</label>
                                    <input type="number" step="0.1" class="form-control" id="weight" name="weight" placeholder="e.g. 70">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="bmi">BMI</label>
                                    <input type="number" step="0.01" class="form-control" id="bmi" name="bmi" placeholder="Calculated automatically" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="notes">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">Save Vital Signs</button>
                            <a href="<?= isset($patient) ? $baseUrl . '/vitals/history/' . $patient['patient_id'] : $baseUrl . '/patients' ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate BMI when height or weight changes
    const heightInput = document.getElementById('height');
    const weightInput = document.getElementById('weight');
    const bmiInput = document.getElementById('bmi');
    
    function calculateBMI() {
        if (heightInput.value && weightInput.value) {
            const heightInMeters = heightInput.value / 100;
            const bmi = weightInput.value / (heightInMeters * heightInMeters);
            bmiInput.value = bmi.toFixed(2);
        } else {
            bmiInput.value = '';
        }
    }
    
    heightInput.addEventListener('input', calculateBMI);
    weightInput.addEventListener('input', calculateBMI);
    
    // Form validation
    const form = document.getElementById('vitalSignForm');
    form.addEventListener('submit', function(event) {
        // If select2 is used for patient selection, ensure underlying select has value before validation
        try {
            if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                const sel = $('#patient_id');
                if (sel.length && !sel.val()) {
                    const data = sel.select2('data');
                    if (data && data.length && data[0].id) {
                        sel.val(data[0].id).trigger('change');
                    }
                }
            }
        } catch (err) {
            // ignore
            console.debug('select2 sync error', err);
        }

        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
    
    // Initialize select2 if available
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        // Ensure select2 selections update underlying <select> value and validation state
        $('#patient_id').on('select2:select', function (e) {
            // trigger change to update underlying value
            $(this).trigger('change');
            // remove validation error styling if present
            try {
                this.classList.remove('is-invalid');
                this.setCustomValidity('');
                this.setAttribute('aria-invalid', 'false');
                const feedback = this.closest('.form-group')?.querySelector('.invalid-feedback');
                if (feedback) feedback.style.display = 'none';
                // Clear form-level validation UI so the new selection is accepted
                try {
                    form.classList.remove('was-validated');
                    // Report validity to hide native browser message
                    form.reportValidity();
                } catch (err) {}
            } catch (err) {
                console.debug('select2 validation clear error', err);
            }
            // Autofill selected patient info into the small info box
            try {
                const opt = this.querySelector('option[value="' + $(this).val() + '"]');
                if (opt) {
                    const first = opt.getAttribute('data-first_name') || '';
                    const last = opt.getAttribute('data-last_name') || '';
                    const phone = opt.getAttribute('data-phone') || '';
                    const email = opt.getAttribute('data-email') || '';
                    const dob = opt.getAttribute('data-dob') || '';
                    const blood = opt.getAttribute('data-blood_group') || '';

                    document.getElementById('selectedPatientName').textContent = first + ' ' + last;
                    document.getElementById('selectedPatientDetails').textContent = (phone ? 'Phone: ' + phone + ' | ' : '') + (email ? 'Email: ' + email + ' | ' : '') + (dob ? 'DOB: ' + dob + ' | ' : '') + (blood ? 'Blood: ' + blood : '');
                    document.getElementById('selectedPatientInfo').classList.remove('d-none');
                }
            } catch(e){}
        });

        // also clear validation state on normal change
        $('#patient_id').on('change', function(){
            try { 
                this.setCustomValidity(''); 
                this.setAttribute('aria-invalid','false');
                // clear validation UI
                form.classList.remove('was-validated');
                form.reportValidity();
            } catch(e){}
        });

        // If select2 shows a selection but underlying select has no value (edge case), set it on submit
        const sel = $('#patient_id');
        if (sel.length) {
            const originalSubmit = form.onsubmit;
            form.addEventListener('submit', function(event) {
                if (!sel.val()) {
                    const data = sel.select2('data');
                    if (data && data.length && data[0].id) {
                        sel.val(data[0].id).trigger('change');
                    }
                }
                // ensure browser validity does not block if select2 selected
                try {
                    if (sel.val()) {
                        sel.get(0).setCustomValidity('');
                        sel.get(0).setAttribute('aria-invalid','false');
                    }
                } catch(e){}
                // allow existing validation logic to run
            });
        }
    }
});
</script> 