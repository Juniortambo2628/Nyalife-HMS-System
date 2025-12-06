<?php
/**
 * Nyalife HMS - Pharmacy Services Page
 */

$pageTitle = 'Pharmacy Services - Nyalife HMS';
?>

<div class="container-fluid py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-4 mb-3">
                    <i class="fas fa-pills text-primary me-2"></i>
                    Pharmacy Services
                </h1>
                <p class="lead">Full-service pharmacy with prescription and over-the-counter medications</p>
            </div>
        </div>

        <?php if (isset($service)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h3><?= htmlspecialchars($service['name'] ?? 'Pharmacy Services') ?></h3>
                    <p><?= htmlspecialchars($service['description'] ?? '') ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h3>Comprehensive Pharmacy Services</h3>
                    <p>Our in-house pharmacy ensures you receive your prescribed medications conveniently and promptly. We offer:</p>
                    <ul>
                        <li>Prescription medications</li>
                        <li>Over-the-counter medications</li>
                        <li>Medication counseling and consultation</li>
                        <li>Medication management services</li>
                        <li>Health and wellness products</li>
                        <li>Insurance billing assistance</li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="<?= $baseUrl ?>/services" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Services
            </a>
            <a href="<?= $baseUrl ?>/pharmacy" class="btn btn-primary">
                <i class="fas fa-shopping-cart me-2"></i>Visit Pharmacy
            </a>
        </div>
    </div>
</div>

