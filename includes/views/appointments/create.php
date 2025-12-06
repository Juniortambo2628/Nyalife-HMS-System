<?php
/**
 * Nyalife HMS - Create Appointment View
 */

// Define default values if not provided
if (!isset($appointmentTypes) || !is_array($appointmentTypes)) {
    $appointmentTypes = ['consultation', 'follow_up', 'emergency', 'routine_checkup', 'vaccination', 'lab_test'];
}

if (!isset($statusOptions) || !is_array($statusOptions)) {
    $statusOptions = [
        'scheduled' => 'Scheduled',
        'confirmed' => 'Confirmed',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_show' => 'No Show'
    ];
}
?>

<!-- Wrap the entire content in a main-content div for AJAX loading -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/appointments">Appointments</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create Appointment</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Create New Appointment</h2>
                <a href="<?= $baseUrl ?>/appointments" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Appointments
                </a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12" id="main-content">
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($errorMessage) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Appointment Form -->
            <div class="card">
                <div class="card-body">
                    <form method="post" action="<?= $baseUrl ?>/appointments/store" id="appointment-form">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
                                <select name="patient_id" id="patient_id" class="form-select select2" required>
                                    <option value="">Select Patient</option>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?= $patient['patient_id'] ?>" <?= ($formData['patient_id'] ?? '') == $patient['patient_id'] ? 'selected' : '' ?>>
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
                                        <option value="<?= $doctor['staff_id'] ?>" <?= ($formData['doctor_id'] ?? '') == $doctor['staff_id'] ? 'selected' : '' ?>>
                                            Dr. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="appointment_date" class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control datepicker" id="appointment_date" name="appointment_date" 
                                       value="<?= htmlspecialchars($formData['appointment_date'] ?? date('Y-m-d')) ?>" required>
                            </div>
                            
                            <div class="col-md-6">
								<label for="appointment_time" class="form-label">Time <span class="text-danger">*</span></label>
								<input type="time" class="form-control" id="appointment_time" name="appointment_time" 
									   value="<?= htmlspecialchars($formData['appointment_time'] ?? '08:00') ?>" required>
								<div id="appointment-time-error" class="invalid-feedback d-block" style="display:none;"></div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="appointment_type" class="form-label">Appointment Type <span class="text-danger">*</span></label>
                                <select name="appointment_type" id="appointment_type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <?php foreach ($appointmentTypes as $type): ?>
                                        <option value="<?= $type ?>" <?= ($formData['appointment_type'] ?? '') === $type ? 'selected' : '' ?>>
                                            <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $type))) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select" required>
                                    <?php foreach ($statusOptions as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= ($formData['status'] ?? 'scheduled') === $value ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Visit <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required><?= htmlspecialchars($formData['reason'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($formData['notes'] ?? '') ?></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-outline-secondary me-md-2">Clear</button>
                            <button type="submit" class="btn btn-primary">Create Appointment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize components when the page is loaded or reloaded via AJAX
document.addEventListener('DOMContentLoaded', initCreateAppointmentPage);
document.addEventListener('page:loaded', initCreateAppointmentPage);

function initCreateAppointmentPage() {
    const baseUrl = '<?= $baseUrl ?>';
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
            todayHighlight: true,
            startDate: new Date()
        });
    }
    
    // Handle form submission with AJAX
    const appointmentForm = document.getElementById('appointment-form');
    if (appointmentForm && typeof Components !== 'undefined') {
        appointmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (!appointmentForm.checkValidity()) {
                e.stopPropagation();
                appointmentForm.classList.add('was-validated');
                return;
            }
            
            // Submit form via AJAX
            const formData = new FormData(appointmentForm);
            
            fetch(appointmentForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to the view page or appointments list
                    if (data.redirect) {
                        Components.loadPage(data.redirect);
                    } else {
                        Components.loadPage('<?= $baseUrl ?>/appointments');
                    }
                } else {
                    // Display error message
                    alert(data.message || 'An error occurred while creating the appointment.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected error occurred. Please try again.');
            });
        });
    }
    
    // Live validation: within opening hours + availability
    const doctorSelect = document.getElementById('doctor_id');
    const dateInput = document.getElementById('appointment_date');
    const timeInput = document.getElementById('appointment_time');
    const timeError = document.getElementById('appointment-time-error');
    let validateAbort = null;
    
    async function validateTimeAndAvailability() {
        const doctorId = doctorSelect.value;
        const date = dateInput.value;
        const time = timeInput.value;
        if (!date || !time) return;

        timeError.style.display = 'none';
        timeError.textContent = '';

        try {
            if (validateAbort) { validateAbort.abort(); }
            validateAbort = new AbortController();
            const signal = validateAbort.signal;

            // 1) Opening hours validation
            const res1 = await fetch(`${baseUrl}/api/validate-appointment`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ date, time }),
                signal
            });
            const out1 = await res1.json();
            if (!out1.success || out1.available === false) {
                timeError.textContent = out1.message || 'Selected time is outside clinic hours.';
                timeError.style.display = 'block';
                return;
            }

            // 2) Doctor availability (if doctor chosen)
            if (doctorId) {
                const res2 = await fetch(`${baseUrl}/api/check-availability?doctor_id=${encodeURIComponent(doctorId)}&date=${encodeURIComponent(date)}&time=${encodeURIComponent(time)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal
                });
                const out2 = await res2.json();
                if (!out2.available) {
                    timeError.textContent = out2.message || 'The selected doctor is not available at this time. Please choose another time or doctor.';
                    timeError.style.display = 'block';
                    return;
                }
            }
        } catch (e) {
            if (e.name !== 'AbortError') {
                console.error('Validation error', e);
            }
        }
    }
    
    if (doctorSelect && dateInput && timeInput) {
        const debounced = (fn, d=250) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), d); }; };
        const run = debounced(validateTimeAndAvailability, 200);
        dateInput.addEventListener('change', run);
        timeInput.addEventListener('change', run);
        doctorSelect.addEventListener('change', run);
        // initial
        run();
    }
}
</script>
