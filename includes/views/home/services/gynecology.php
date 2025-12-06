<?php
/**
 * Nyalife HMS - Gynecology Services Page
 */

$pageTitle = 'Gynecology Services - Nyalife HMS';
?>

<div class="container-fluid py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-4 mb-3">
                    <i class="fas fa-heartbeat text-primary me-2"></i>
                    Gynecology Services
                </h1>
                <p class="lead">Expert care for women's reproductive health and wellness</p>
            </div>
        </div>

        <?php if (isset($service)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h3><?= htmlspecialchars($service['name'] ?? 'Gynecology Services') ?></h3>
                    <p><?= htmlspecialchars($service['description'] ?? '') ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h3>Comprehensive Gynecology Services</h3>
                    <p>Our gynecology services cover all aspects of women's health from routine examinations to specialized treatments. We offer:</p>
                    <ul>
                        <li>Routine gynecological examinations</li>
                        <li>Pap smears and HPV testing</li>
                        <li>Family planning and contraception counseling</li>
                        <li>Treatment for endometriosis and PCOS</li>
                        <li>Menopause symptom management</li>
                        <li>Minimally invasive surgical procedures</li>
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

