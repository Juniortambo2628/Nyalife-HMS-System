<?php
/**
 * Nyalife HMS - Edit Appointment View
 */

$pageTitle = 'Edit Appointment - Nyalife HMS';
?>
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Edit Appointment</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="<?= $baseUrl ?>/appointments/view/<?= $appointment['id'] ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Appointment
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($errorMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Appointment Form -->
        <div class="card">
            <div class="card-body">
                <form method="post" action="<?= $baseUrl ?>/appointments/edit/<?= $appointment['id'] ?>" id="appointment-form">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" id="patient_id" class="form-select select2" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?= $patient['patient_id'] ?>" <?= $appointment['patient_id'] == $patient['patient_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?> (<?= htmlspecialchars($patient['patient_id']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="doctor_id" class="form-label">Doctor <span class="text-danger">*</span></label>
                            <select name="doctor_id" id="doctor_id" class="form-select select2" required>
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?= $doctor['staff_id'] ?>" <?= $appointment['doctor_id'] == $doctor['staff_id'] ? 'selected' : '' ?>>
                                        Dr. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="appointment_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control datepicker" id="appointment_date" name="appointment_date" 
                                   value="<?= htmlspecialchars($appointment['date']) ?>" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="appointment_time" class="form-label">Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="appointment_time" name="appointment_time" 
                                   value="<?= htmlspecialchars($appointment['time']) ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="appointment_type" class="form-label">Appointment Type <span class="text-danger">*</span></label>
                            <select name="appointment_type" id="appointment_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <?php foreach ($appointmentTypes as $type): ?>
                                    <option value="<?= $type ?>" <?= $appointment['appointment_type'] === $type ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(ucfirst($type)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select" required>
                                <?php foreach ($statusOptions as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $appointment['status'] === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Visit <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required><?= htmlspecialchars($appointment['reason']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($appointment['notes']) ?></textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= $baseUrl ?>/appointments/view/<?= $appointment['id'] ?>" class="btn btn-outline-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Appointment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Add page specific script
$pageSpecificScripts[] = AssetHelper::getJs('appointments-edit');
?>