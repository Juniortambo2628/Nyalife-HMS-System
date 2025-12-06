<?php
/**
 * Nyalife HMS - Laboratory Services Page
 */

$pageTitle = 'Laboratory Services - Nyalife HMS';
?>

<div class="container-fluid py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-4 mb-3">
                    <i class="fas fa-notes-medical text-primary me-2"></i>
                    Laboratory Services
                </h1>
                <p class="lead">State-of-the-art diagnostic and testing facilities</p>
            </div>
        </div>

        <?php if (isset($service)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h3><?= htmlspecialchars($service['name'] ?? 'Laboratory Services') ?></h3>
                    <p><?= htmlspecialchars($service['description'] ?? '') ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h3>Advanced Laboratory Services</h3>
                    <p>Our advanced laboratory is equipped with the latest technology to provide quick and accurate results. We offer:</p>
                    <ul>
                        <li>Blood tests and complete blood count (CBC)</li>
                        <li>Urinalysis and urine culture</li>
                        <li>Hormone level testing</li>
                        <li>Genetic testing and screening</li>
                        <li>Amniotic fluid analysis</li>
                        <li>Various health screenings</li>
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

