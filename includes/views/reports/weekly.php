<?php
/**
 * Nyalife HMS - Weekly Report View
 */

$pageTitle = 'Weekly Report - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-calendar-week text-primary me-2"></i>
                    Weekly Report
                </h1>
                <a href="<?= $baseUrl ?>/reports" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Reports
                </a>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Week: <?= htmlspecialchars($startDate ?? date('Y-m-d')) ?> to <?= htmlspecialchars($endDate ?? date('Y-m-d')) ?></h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?= $baseUrl ?>/reports/weekly" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="start" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start" name="start" 
                                       value="<?= htmlspecialchars($startDate ?? date('Y-m-d', strtotime('monday this week'))) ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="end" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end" name="end" 
                                       value="<?= htmlspecialchars($endDate ?? date('Y-m-d', strtotime('sunday this week'))) ?>">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Generate Report
                                </button>
                            </div>
                        </div>
                    </form>

                    <?php if (isset($reportData)): ?>
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h4><?= $reportData['appointments'] ?? 0 ?></h4>
                                        <p class="mb-0">Appointments</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h4><?= $reportData['consultations'] ?? 0 ?></h4>
                                        <p class="mb-0">Consultations</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h4><?= $reportData['prescriptions'] ?? 0 ?></h4>
                                        <p class="mb-0">Prescriptions</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h4><?= number_format($reportData['revenue'] ?? 0, 2) ?></h4>
                                        <p class="mb-0">Revenue (KES)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Select a date range and click "Generate Report" to view weekly statistics.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

