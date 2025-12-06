<?php
/**
 * Nyalife HMS - Appointments Index View
 */
?>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/appointments.css">

    <div class="container-fluid appointments-page">
        <div class="row mb-3 mb-md-4">
            <div class="col-12 col-md-6 mb-2 mb-md-0">
                <h1 class="mb-0">Appointments</h1>
            </div>
            <div class="col-12 col-md-6 text-md-end">
                <a href="<?= $baseUrl ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> <span class="d-none d-sm-inline">Back to </span>Dashboard
                </a>
            </div>
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

        <!-- Appointments List -->
        <div class="card">
            <div class="container py-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 mb-md-4 gap-2">
                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-sm-auto">
                        <a href="<?= $baseUrl ?>/appointments/calendar" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-calendar-alt me-1 me-sm-2"></i><span class="d-none d-sm-inline">Calendar </span>View
                        </a>
                        <a href="<?= $baseUrl ?>/appointments/create" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1 me-sm-2"></i>New<span class="d-none d-sm-inline"> Appointment</span>
                        </a>
                    </div>
                </div>

                <!-- Filters Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Appointments</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" action="<?= $baseUrl ?>/appointments" class="row g-2 g-md-3" id="filter-form">
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

                            <?php if (in_array($userRole, ['admin', 'doctor'])): ?>
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
                                    <?php
                                    // Define default status options if not provided
                                    if (!isset($statusOptions) || !is_array($statusOptions)) {
                                        $statusOptions = [
                                            'scheduled' => 'Scheduled',
                                            'confirmed' => 'Confirmed',
                                            'completed' => 'Completed',
                                            'cancelled' => 'Cancelled',
                                            'no_show' => 'No Show'
                                        ];
                                    }

                                    foreach ($statusOptions as $value => $label):
                                        ?>
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
                                    <a href="<?= $baseUrl ?>/appointments" class="btn btn-outline-secondary btn-sm flex-fill flex-sm-grow-0">
                                        <i class="fas fa-undo me-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Appointments Table -->
                <?php if (empty($appointments)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> No appointments found matching your criteria.
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Patient</th>
                                            <th>Doctor</th>
                                            <th>Type</th>
                                            <th>Source</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($appointments as $appointment): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($appointment['formatted_datetime'] ?? 'N/A') ?></td>
                                                <td>
                                                    <a href="<?= $baseUrl ?>/patients/view/<?= $appointment['patient_id'] ?? '' ?>" class="text-decoration-none">
                                                        <?= htmlspecialchars($appointment['patient_name'] ?? 'Unknown Patient') ?>
                                                    </a>
                                                </td>
                                                <td>Dr. <?= htmlspecialchars($appointment['doctor_name'] ?? 'Unknown Doctor') ?></td>
                                                <td><?= htmlspecialchars($appointment['appointment_type'] ?? 'N/A') ?></td>
                                                <td>
                                                    <?php $src = $appointment['source_label'] ?? 'Internal'; ?>
                                                    <span class="badge <?= strtolower($src) === 'guest' ? 'bg-warning text-dark' : 'bg-secondary' ?>">
                                                        <?= htmlspecialchars($src) ?>
                                                    </span>
                                                </td>
                                                <td><span class="<?= $appointment['status_class'] ?? 'badge bg-secondary' ?>"><?= htmlspecialchars($appointment['status_label'] ?? 'Unknown') ?></span></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="<?= $baseUrl ?>/appointments/view/<?= $appointment['id'] ?? $appointment['appointment_id'] ?? '' ?>" class="btn btn-outline-primary" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if (in_array($userRole, ['admin', 'doctor', 'receptionist'])): ?>
                                                        <a href="<?= $baseUrl ?>/appointments/edit/<?= $appointment['id'] ?? $appointment['appointment_id'] ?? '' ?>" class="btn btn-outline-secondary" title="Edit">
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
    </div>

<?php
// Add page specific script
$pageSpecificScripts[] = AssetHelper::getJs('appointments-index');
?>
