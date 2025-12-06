<?php
/**
 * Nyalife HMS - Guest Appointment Confirmation
 *
 * View for guest appointment confirmation.
 */

$pageTitle = 'Appointment Confirmation - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow text-center">
                <div class="card-body py-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h2 class="text-success mb-3">Appointment Request Submitted!</h2>
                    
                    <p class="lead text-muted mb-4">
                        Thank you for submitting your appointment request. We have received your information and will contact you shortly to confirm your appointment.
                    </p>
                    
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle me-2"></i>What happens next?</h5>
                        <ul class="list-unstyled mb-0 text-start">
                            <li><i class="fas fa-clock me-2"></i>We will review your request within 24 hours</li>
                            <li><i class="fas fa-phone me-2"></i>You will receive a confirmation call or email</li>
                            <li><i class="fas fa-calendar me-2"></i>Your appointment will be scheduled based on availability</li>
                            <li><i class="fas fa-envelope me-2"></i>You will receive appointment details via email</li>
                        </ul>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <a href="<?= $baseUrl ?>/guest-appointments" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-calendar-plus me-2"></i>Book Another Appointment
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= $baseUrl ?>/" class="btn btn-outline-secondary btn-lg w-100">
                                <i class="fas fa-home me-2"></i>Return to Home
                            </a>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <p class="text-muted">
                            <strong>Need immediate assistance?</strong><br>
                            Call us at <strong>+254 XXX XXX XXX</strong> or email <strong>appointments@nyalife.com</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 