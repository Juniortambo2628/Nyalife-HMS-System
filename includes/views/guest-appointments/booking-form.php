<?php
/**
 * Nyalife HMS - Guest Appointment Booking Form
 *
 * View for guest appointment booking form.
 */

$pageTitle = 'Book Guest Appointment - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-plus fa-fw"></i> Book Guest Appointment
        </h1>
        <a href="<?= $baseUrl ?>/" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left fa-fw"></i> Back to Home
        </a>
    </div>

    <!-- Guest Appointment Form -->
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
    <div class="card shadow">
        <div class="card-header py-3 bg-gradient-primary-secondary">
            <h6 class="m-0 font-weight-bold">Book Guest Appointment</h6>
        </div>
        <div class="card-body">
            <div id="guestAppointmentAlert" class="alert" role="alert" style="display: none;"></div>
            
            <!-- Step Indicator -->
            <div class="step-dots d-flex justify-content-center mb-3" aria-hidden="true"></div>
            <div id="guestAppointmentValidationStatus" class="visually-hidden" role="status" aria-live="polite"></div>
            <div class="register-progress-wrapper mb-3" aria-hidden="true">
                <div class="register-progress-bar" style="width:0%"></div>
            </div>
            
            <form id="guestAppointmentForm" action="<?= $baseUrl ?>/guest-appointments/book" method="post" data-nyalife-form="true" data-ajax="true" class="multi-step-form">
                <div class="registration-steps">
                    <!-- Step 1: Personal Information -->
                    <div class="form-step active" data-step="1">
                        <h5 class="mb-3">Personal Information</h5>
                        <p class="text-muted mb-4">Please provide your personal details.</p>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group position-relative">
                                    <label for="guest_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="guest_first_name" name="first_name" required autocomplete="given-name">
                                    <span class="field-feedback"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group position-relative">
                                    <label for="guest_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="guest_last_name" name="last_name" required autocomplete="family-name">
                                    <span class="field-feedback"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group position-relative">
                                    <label for="guest_email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="guest_email" name="email" required autocomplete="email">
                                    <div class="form-text">We will send appointment confirmation here.</div>
                                    <span class="field-feedback"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group position-relative">
                                    <label for="guest_phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="guest_phone" name="phone" required autocomplete="tel">
                                    <span class="field-feedback"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group position-relative">
                                    <label for="guest_date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="guest_date_of_birth" name="date_of_birth" required autocomplete="bday">
                                    <span class="field-feedback"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group position-relative">
                                    <label for="guest_gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select class="form-select" id="guest_gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="female">Female</option>
                                        <option value="male">Male</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <span class="field-feedback"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-outline-secondary me-2 btn-step-next">Next</button>
                        </div>
                    </div>

                    <!-- Step 2: Appointment Preferences -->
                    <div class="form-step" data-step="2">
                        <h5 class="mb-3">Appointment Preferences</h5>
                        <p class="text-muted mb-4">Select your preferred appointment date, time, and service.</p>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group position-relative">
                                    <label for="appointment_date" class="form-label">Preferred Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="appointment_date" name="appointment_date" required min="<?= date('Y-m-d') ?>">
                                    <span class="field-feedback"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group position-relative">
                                    <label for="appointment_time" class="form-label">Preferred Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
                                    <span class="field-feedback"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group position-relative">
                                    <label for="appointment_type" class="form-label">Service Needed <span class="text-danger">*</span></label>
                                    <select class="form-select" id="appointment_type" name="appointment_type" required>
                                        <option value="">Select Service</option>
                                        <?php foreach ($services as $service): ?>
                                            <option value="<?= htmlspecialchars($service['id']) ?>">
                                                <?= htmlspecialchars($service['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="field-feedback"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group position-relative">
                                    <label for="preferred_doctor" class="form-label">Preferred Doctor (Optional)</label>
                                    <select class="form-select" id="preferred_doctor" name="doctor_id">
                                        <option value="">Any Available Doctor</option>
                                        <?php foreach ($doctors as $doctor): ?>
                                            <option value="<?= htmlspecialchars($doctor['user_id']) ?>">
                                                Dr. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?>
                                                <?= !empty($doctor['specialization']) ? ' (' . htmlspecialchars($doctor['specialization']) . ')' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="field-feedback"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-group position-relative">
                                <label for="appointment_reason" class="form-label">Reason for Appointment <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="appointment_reason" name="reason" rows="3" required></textarea>
                                <span class="field-feedback"></span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary btn-step-prev">Previous</button>
                            <button type="submit" class="btn btn-primary submit-guest-appointment-btn">
                                <i class="fas fa-paper-plane me-2"></i> Submit Appointment Request
                            </button>
                        </div>
                        <div class="spinner-border text-primary d-none mt-2 mx-auto" id="guestAppointmentSpinner" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
        </div>
    </div>
</div>

<!-- Load guest appointment step form handler and validation -->
<script>
    // Make baseUrl available to JavaScript
    window.baseUrl = '<?= $baseUrl ?>';
</script>
<script src="<?= $baseUrl ?>/assets/js/guest-appointment-steps.js"></script>
<script src="<?= $baseUrl ?>/assets/js/guest-appointment-validation.js"></script> 