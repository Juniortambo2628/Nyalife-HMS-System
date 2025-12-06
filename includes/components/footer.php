<?php
/**
 * Nyalife HMS - Reusable Footer Component
 * This file contains the footer HTML that can be loaded via AJAX or included directly
 */

$pageTitle = 'Footer - Nyalife HMS';
// Check if this file is being accessed directly or via AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// If not AJAX and not included in another file, prevent direct access
if (!$isAjax && !defined('NYALIFE_INCLUDED')) {
    http_response_code(403);
    exit('Direct access to this file is not allowed.');
}

// Variables that should be passed:
// $baseUrl - The base URL of the application
?>
<!-- Footer -->
<footer class="footer">
    <div class="footer-top">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="footer-about">
                        <a href="<?= $baseUrl ?>" class="footer-logo">
                            <img src="<?= $baseUrl ?>/assets/img/logo/Logo2-transparent.png" alt="Nyalife HMS" height="50">
                        </a>
                        <p class="d-none d-md-block">Nyalife Women's Clinic is dedicated to providing exceptional healthcare services with compassion and expertise.</p>
                        <p class="d-md-none" style="font-size: 0.7rem;">Dedicated to providing exceptional healthcare services with compassion and expertise.</p>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 col-6">
                    <div class="footer-links">
                        <h4>Quick Links</h4>
                        <ul>
                            <li><a href="<?= $baseUrl ?>"><i class="fas fa-chevron-right"></i> Home</a></li>
                            <li><a href="<?= $baseUrl ?>/#about"><i class="fas fa-chevron-right"></i> About Us</a></li>
                            <li><a href="<?= $baseUrl ?>/services"><i class="fas fa-chevron-right"></i> Services</a></li>
                            <li><a href="<?= $baseUrl ?>/guest-appointments"><i class="fas fa-chevron-right"></i> Book Appointment</a></li>
                            <li><a href="<?= $baseUrl ?>/#contact"><i class="fas fa-chevron-right"></i> Contact</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="footer-contact">
                        <h4>Contact Us</h4>
                        <ul>
                            <li><i class="fas fa-map-marker-alt"></i> <span>JemPark Complex, Suite A5, Sabaki, Kenya</span></li>
                            <li><i class="fas fa-phone-alt"></i> <span>+254 746 516 514</span></li>
                            <li><i class="fas fa-envelope"></i> <span>info@nyalifewomensclinic.com</span></li>
                            <li><i class="fas fa-clock"></i> <span>Mon - Sat: 8:00 AM - 8:00 PM</span></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="footer-newsletter">
                        <h4>Newsletter</h4>
                        <p class="d-none d-md-block">Subscribe to our newsletter for health tips and updates.</p>
                        <p class="d-md-none" style="font-size: 0.7rem; margin-bottom: 0.75rem;">Subscribe for health tips and updates.</p>
                        <form class="newsletter-form" action="<?= $baseUrl ?>/newsletter/subscribe" method="post">
                            <input type="email" name="email" placeholder="Your Email Address" required>
                            <button type="submit"><i class="fas fa-paper-plane"></i></button>
                        </form>
                        <div class="social-links mt-3">
                            <a href="<?= $baseUrl ?>/social/facebook" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="<?= $baseUrl ?>/social/twitter" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="<?= $baseUrl ?>/social/instagram" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="<?= $baseUrl ?>/social/linkedin" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0">&copy; <?= date('Y') ?> Nyalife Women's Clinic. All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </div>
</footer>

<script>
// Load doctors for guest appointment modal
document.addEventListener('DOMContentLoaded', function() {
    const guestAppointmentModal = document.getElementById('guestAppointmentModal');
    const preferredDoctorSelect = document.getElementById('preferred_doctor');

    if (guestAppointmentModal && preferredDoctorSelect) {
        // Load doctors when modal is shown
        guestAppointmentModal.addEventListener('show.bs.modal', function() {
            loadDoctors();
        });

        // Set minimum date to today
        const appointmentDateInput = document.getElementById('appointment_date');
        if (appointmentDateInput) {
            const today = new Date().toISOString().split('T')[0];
            appointmentDateInput.setAttribute('min', today);
        }
    }

    function loadDoctors() {
        fetch('<?= $baseUrl ?>/api/doctors', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.doctors) {
                // Clear existing options except the first one
                preferredDoctorSelect.innerHTML = '<option value="">Any Available Doctor</option>';

                // Add doctor options
                data.doctors.forEach(doctor => {
                    const option = document.createElement('option');
                    option.value = doctor.user_id;
                    option.textContent = `Dr. ${doctor.first_name} ${doctor.last_name}`;
                    if (doctor.specialization) {
                        option.textContent += ` (${doctor.specialization})`;
                    }
                    preferredDoctorSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading doctors:', error);
        });
    }
});
</script>

  <!-- Patient Registration Modal -->
  <div class="modal fade" id="registerPatientModal" tabindex="-1" aria-labelledby="registerPatientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="registerPatientModalLabel">Patient Registration</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="registerPatientAlert" class="alert" role="alert" style="display: none;"></div>
                    <p class="text-muted mb-4">Please fill out this form to register as a new patient.</p>
                    <form id="registerPatientForm" action="<?= $baseUrl ?>/register" method="post" data-nyalife-form="true" data-validate-blur="true" data-ajax="true">
                        <input type="hidden" name="role" value="patient">
                        <!-- Registration form fields -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="female">Female</option>
                                    <option value="male">Male</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" autocomplete="username" required>
                                <div class="form-text">This will be used for login</div>
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
                                <div class="form-text">Minimum 8 characters with letters and numbers</div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                                <div class="form-text">Re-enter password to confirm</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms of Service</a> and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>
                                </label>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Register</button>
                            <div class="spinner-border text-primary d-none mt-2 mx-auto" id="registerPatientSpinner" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Modal -->
    <div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="serviceModalLabel">Service Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="service-modal-bg">
                    <div id="modalServiceContent" class="service-modal-content p-4"></div>
                </div>
                <div class="modal-footer justify-content-start">
                    <div class="d-flex gap-3">
                        <button type="button" class="btn btn-primary service-modal-btn" data-bs-toggle="modal" data-bs-target="#loginModal">Log in and Book Appointment</button>
                        <button type="button" class="btn btn-secondary service-modal-btn" data-bs-toggle="modal" data-bs-target="#registerPatientModal">Sign up as New Client</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="loginModalLabel">Login</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="loginAlert" class="alert" role="alert" style="display: none;"></div>
                    <form id="loginForm" action="<?= $baseUrl ?>/includes/controllers/ajax/auth_handler.php" method="post" data-nyalife-form="true" data-validate-blur="true" data-ajax="true">
                        <div class="mb-3">
                            <label for="login_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="login_username" name="username" autocomplete="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="login_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="login_password" name="password" required autocomplete="current-password" pb-role="password">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                            <label class="form-check-label" for="remember_me">Remember me</label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                            <div class="spinner-border text-primary d-none mt-2 mx-auto" id="loginSpinner" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <p class="mb-0">Don't have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#registerPatientModal" data-bs-dismiss="modal">Register here</a></p>
                            <p class="mt-2"><a href="#" id="forgotPasswordLink">Forgot Password?</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Guest Appointment Modal -->
    <div class="modal fade" id="guestAppointmentModal" tabindex="-1" aria-labelledby="guestAppointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="guestAppointmentModalLabel">Book a Guest Appointment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalGuestAppointmentAlert" class="alert" role="alert" style="display: none;"></div>
                    <p class="text-muted mb-4">Please provide your details and desired appointment time. We will contact you to confirm.</p>
                    <form id="modalGuestAppointmentForm" action="<?= $baseUrl ?>/guest-appointments/book" method="post" data-nyalife-form="true" data-ajax="true">
                        <!-- Patient Details (for guest) -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="modal_guest_first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="modal_guest_first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="modal_guest_last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="modal_guest_last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="modal_guest_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="modal_guest_email" name="email" required>
                                <div class="form-text">We will send appointment confirmation here.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="modal_guest_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="modal_guest_phone" name="phone" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="modal_guest_date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="modal_guest_date_of_birth" name="date_of_birth" required>
                            </div>
                            <div class="col-md-6">
                                <label for="modal_guest_gender" class="form-label">Gender</label>
                                <select class="form-select" id="modal_guest_gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="female">Female</option>
                                    <option value="male">Male</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Appointment Details -->
                        <h5 class="mb-3">Appointment Preferences</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="modal_appointment_date" class="form-label">Preferred Date</label>
                                <input type="date" class="form-control" id="modal_appointment_date" name="appointment_date" required>
                            </div>
                            <div class="col-md-6">
                                <label for="modal_appointment_time" class="form-label">Preferred Time</label>
                                <input type="time" class="form-control" id="modal_appointment_time" name="appointment_time" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="modal_appointment_type" class="form-label">Service Needed</label>
                                <select class="form-select" id="modal_appointment_type" name="appointment_type" required>
                                    <option value="">Select Service</option>
                                    <option value="new_visit">New Patient Consultation</option>
                                    <option value="follow_up">Follow-up</option>
                                    <option value="routine_checkup">Routine Check-up</option>
                                    <option value="consultation">General Consultation</option>
                                    <!-- These can be dynamically populated from your 'services' table -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="modal_preferred_doctor" class="form-label">Preferred Doctor (Optional)</label>
                                <select class="form-select" id="modal_preferred_doctor" name="doctor_id">
                                    <option value="">Any Available Doctor</option>
                                    <!-- Doctors will be loaded dynamically via AJAX -->
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="modal_appointment_reason" class="form-label">Reason for Appointment</label>
                            <textarea class="form-control" id="modal_appointment_reason" name="reason" rows="3" required></textarea>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary submit-guest-appointment-btn">
                                <i class="fas fa-paper-plane me-2"></i> Submit Appointment Request
                            </button>
                            <div class="spinner-border text-primary d-none mt-2 mx-auto" id="modalGuestAppointmentSpinner" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>