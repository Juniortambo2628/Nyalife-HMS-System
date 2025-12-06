<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Consultations</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= $baseUrl ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Consultations List -->
    <div class="card">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Consultations</h2>
                <a href="<?= $baseUrl ?>/consultations/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>New Consultation
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
                    <form method="get" action="<?= $baseUrl ?>/consultations" class="row g-3">
                        <?php if ($userRole === 'admin'): ?>
                        <div class="col-md-3">
                            <label for="doctor_id" class="form-label">Doctor</label>
                            <select name="doctor_id" id="doctor_id" class="form-select">
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
                        <div class="col-md-3">
                            <label for="patient_id" class="form-label">Patient</label>
                            <select name="patient_id" id="patient_id" class="form-select">
                                <option value="">All Patients</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?= $patient['patient_id'] ?>" <?= ($filters['patient_id'] ?? '') == $patient['patient_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Statuses</option>
                                <?php foreach ($statusOptions as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                        </div>

                        <div class="col-md-2">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i> Apply Filters
                            </button>
                            <a href="<?= $baseUrl ?>/consultations" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-1"></i> Reset
                            </a>
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
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Patient</th>
                                        <?php if ($userRole === 'admin'): ?>
                                            <th>Doctor</th>
                                        <?php endif; ?>
                                        <th>Status</th>
                                        <th>Diagnosis</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($consultations as $consultation): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?= date('M j, Y', strtotime($consultation['consultation_date'])) ?></div>
                                                <div class="text-muted small"><?= date('g:i A', strtotime($consultation['consultation_time'])) ?></div>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($consultation['patient_name']) ?></div>
                                                <div class="text-muted small">ID: <?= $consultation['patient_id'] ?></div>
                                            </td>
                                            <?php if ($userRole === 'admin'): ?>
                                                <td>Dr. <?= htmlspecialchars($consultation['doctor_name']) ?></td>
                                            <?php endif; ?>
                                            <td>
                                                <?php 
                                                $statusClass = match($consultation['status']) {
                                                    'completed' => 'bg-success',
                                                    'scheduled' => 'bg-primary',
                                                    'in_progress' => 'bg-info',
                                                    'cancelled' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $consultation['status'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($consultation['diagnosis'])): ?>
                                                    <div class="text-truncate" style="max-width: 250px;" 
                                                         title="<?= htmlspecialchars($consultation['diagnosis']) ?>">
                                                        <?= htmlspecialchars($consultation['diagnosis']) ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">No diagnosis recorded</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="<?= $baseUrl ?>/consultations/view/<?= $consultation['id'] ?>" 
                                                       class="btn btn-outline-primary" 
                                                       title="View Consultation">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if (in_array($userRole, ['admin', 'doctor'])): ?>
                                                        <a href="<?= $baseUrl ?>/consultations/edit/<?= $consultation['id'] ?>" 
                                                           class="btn btn-outline-secondary" 
                                                           title="Edit Consultation">
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

        <!-- Add some custom styles for better appearance -->
        <style>
        .table th {
            white-space: nowrap;
            vertical-align: middle;
        }
        .table td {
            vertical-align: middle;
        }
        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        </style>
    </div>
</div>
