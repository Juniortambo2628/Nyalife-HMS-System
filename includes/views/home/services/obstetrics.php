<?php
/**
 * Nyalife HMS - Obstetrics Services Page
 */

$pageTitle = 'Obstetrics Care - Nyalife HMS';
?>

<div class="container-fluid py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-4 mb-3">
                    <i class="fas fa-user-md text-primary me-2"></i>
                    Obstetrics Care
                </h1>
                <p class="lead">Comprehensive prenatal, delivery, and postnatal care for expectant mothers</p>
            </div>
        </div>

        <?php if (isset($service)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h3><?= htmlspecialchars($service['name'] ?? 'Obstetrics Care') ?></h3>
                    <p><?= htmlspecialchars($service['description'] ?? '') ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h3>Comprehensive Obstetrics Care</h3>
                    <p>Our team of experienced obstetricians provides personalized care throughout your pregnancy journey. We offer:</p>
                    <ul>
                        <li>Regular prenatal check-ups and monitoring</li>
                        <li>Ultrasound screenings and imaging</li>
                        <li>Genetic testing and counseling</li>
                        <li>Specialized care for high-risk pregnancies</li>
                        <li>Delivery and labor support</li>
                        <li>Postnatal care and follow-up</li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="<?= $baseUrl ?>/services" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Services
            </a>
            <a href="<?= $baseUrl ?>/appointments/create" class="btn btn-primary">
                <i class="fas fa-calendar-plus me-2"></i>Book Appointment
            </a>
        </div>
    </div>
</div>

