<?php
/**
 * Nyalife HMS - Services Page
 *
 * View for displaying all services.
 */

$pageTitle = 'Our Services - Nyalife HMS';
?>

<div class="container-fluid py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h1 class="display-4 mb-3">Our Services</h1>
                <p class="lead">Comprehensive healthcare services tailored to women's health and wellness</p>
            </div>
        </div>

        <?php if (isset($services) && !empty($services)): ?>
            <div class="row g-4">
                <?php foreach ($services as $service): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="<?= htmlspecialchars($service['icon'] ?? 'fas fa-heart') ?> text-primary me-2"></i>
                                    <?= htmlspecialchars($service['name'] ?? '') ?>
                                </h5>
                                <p class="card-text"><?= htmlspecialchars($service['description'] ?? '') ?></p>
                                <?php if (isset($service['link'])): ?>
                                    <a href="<?= htmlspecialchars($service['link']) ?>" class="btn btn-primary btn-sm">
                                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <h3>Our Services</h3>
                            <div class="row mt-4">
                                <div class="col-md-3 mb-4">
                                    <a href="<?= $baseUrl ?>/services/obstetrics" class="text-decoration-none">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <i class="fas fa-user-md fa-3x text-primary mb-3"></i>
                                                <h5>Obstetrics Care</h5>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-3 mb-4">
                                    <a href="<?= $baseUrl ?>/services/gynecology" class="text-decoration-none">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <i class="fas fa-heartbeat fa-3x text-primary mb-3"></i>
                                                <h5>Gynecology Services</h5>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-3 mb-4">
                                    <a href="<?= $baseUrl ?>/services/laboratory" class="text-decoration-none">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <i class="fas fa-notes-medical fa-3x text-primary mb-3"></i>
                                                <h5>Laboratory Services</h5>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-3 mb-4">
                                    <a href="<?= $baseUrl ?>/services/pharmacy" class="text-decoration-none">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <i class="fas fa-pills fa-3x text-primary mb-3"></i>
                                                <h5>Pharmacy Services</h5>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

