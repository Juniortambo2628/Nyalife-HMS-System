<?php
/**
 * Nyalife HMS - Consultations List View
 */

$pageTitle = 'Consultations List - Nyalife HMS';
?>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/consultations.css">
    <div class="container-fluid consultations-page">
        <div class="row mb-3 mb-md-4">
            <div class="col-12 col-md-6 mb-2 mb-md-0">
                <h1 class="mb-0">Consultations</h1>
            </div>
            <div class="col-12 col-md-6 text-md-end">
                <a href="<?= $baseUrl ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> <span class="d-none d-sm-inline">Back to </span>Dashboard
                </a>
            </div>
        </div>

        <!-- Consultations List -->
        <div class="card">
            <div class="container py-4">
                <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
                    <a href="<?= $baseUrl ?>/consultations/create" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1 me-sm-2"></i>New<span class="d-none d-sm-inline"> Consultation</span>
                    </a>
                </div>

                <!-- Flash Messages -->
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($successMessage) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($errorMessage) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($infoMessage)): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i> <?= htmlspecialchars($infoMessage) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Consultations</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" action="<?= $baseUrl ?>/consultations" class="row g-2 g-md-3" id="filter-form">
                            <?php if ($userRole === 'admin'): ?>
                            <div class="col-12 col-sm-6 col-md-3">
                                <label for="doctor_id" class="form-label">Doctor</label>
                                <select name="doctor_id" id="doctor_id" class="form-select form-select-sm select2">
                                    <option value="">All Doctors</option>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <option value="<?= $doctor['user_id'] ?>" <?= ($filters['doctor_id'] ?? '') == $doctor['user_id'] ? 'selected' : '' ?>>
                                            Dr. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>

                            <?php if (in_array($userRole, ['admin', 'nurse'])): ?>
                            <div class="col-12 col-sm-6 col-md-3">
                                <label for="patient_id" class="form-label">Patient</label>
                                <select name="patient_id" id="patient_id" class="form-select form-select-sm select2">
                                    <option value="">All Patients</option>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?= $patient['patient_id'] ?>" <?= ($filters['patient_id'] ?? '') == $patient['patient_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>

                            <div class="col-12 col-sm-6 col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select form-select-sm">
                                    <option value="">All Statuses</option>
                                    <?php foreach ($statusOptions as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-sm-6 col-md-2">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control form-control-sm datepicker" id="date_from" name="date_from" 
                                       value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                            </div>

                            <div class="col-12 col-sm-6 col-md-2">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control form-control-sm datepicker" id="date_to" name="date_to" 
                                       value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                            </div>

                            <div class="col-12 mt-2 mt-md-3">
                                <div class="d-flex flex-column flex-sm-row gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm flex-fill flex-sm-grow-0">
                                        <i class="fas fa-search me-1"></i> Apply Filters
                                    </button>
                                    <a href="<?= $baseUrl ?>/consultations" class="btn btn-outline-secondary btn-sm flex-fill flex-sm-grow-0">
                                        <i class="fas fa-undo me-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Consultations Table -->
                <?php if (empty($consultations)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> No consultations found matching your criteria.
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Patient</th>
                                                <th>Doctor</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Type</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($consultations as $consultation): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= $baseUrl ?>/patients/view/<?= $consultation['patient_id'] ?>" class="text-decoration-none">
                                                        <?= htmlspecialchars($consultation['patient_name']) ?>
                                                    </a>
                                                </td>
                                                    <td>Dr. <?= htmlspecialchars($consultation['doctor_name']) ?></td>
                                                <td><?= htmlspecialchars($consultation['formatted_date']) ?></td>
                                                <td><span class="<?= $consultation['status_class'] ?>"><?= htmlspecialchars($consultation['status_label']) ?></span></td>
                                                <td>
                                                    <?php if (!empty($consultation['is_walk_in'])): ?>
                                                        <span class="badge bg-info">Walk-in</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Appointment</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="<?= $baseUrl ?>/consultations/view/<?= $consultation['id'] ?>" class="btn btn-outline-primary" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($userRole === 'doctor' || $userRole === 'admin'): ?>
                                                        <a href="<?= $baseUrl ?>/consultations/edit/<?= $consultation['id'] ?>" class="btn btn-outline-secondary" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
<!-- end duplicate wrapper removed -->
</div>

<?php
// Add page specific script
$pageSpecificScripts[] = AssetHelper::getJs('consultations');
?>
