<div class="container-fluid">
    <!-- Patient Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="avatar avatar-xxl bg-light rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-user fa-3x text-muted"></i>
                    </div>
                </div>
                <div>
                    <h1 class="mb-1"><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h1>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-primary">Patient #<?= htmlspecialchars($patient['patient_number']) ?></span>
                        <span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($patient['gender'])) ?></span>
                        <span class="badge bg-info">DOB: <?= date('M j, Y', strtotime($patient['date_of_birth'])) ?></span>
                        <?php if (!empty($patient['blood_group'])): ?>
                            <span class="badge bg-danger"><?= htmlspecialchars($patient['blood_group']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= $baseUrl ?>/patients/edit/<?= $patient['patient_id'] ?>" class="btn btn-outline-primary">
                <i class="fas fa-edit"></i> Edit Patient
            </a>
            <a href="<?= $baseUrl ?>/patients" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Patients
            </a>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <ul class="nav nav-tabs" id="patientTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                <i class="fas fa-user me-1"></i> Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="medical-tab" data-bs-toggle="tab" data-bs-target="#medical" type="button" role="tab">
                <i class="fas fa-notes-medical me-1"></i> Medical History
                <?php if (!empty($medicalHistory)): ?>
                    <span class="badge bg-primary rounded-pill"><?= count($medicalHistory) ?></span>
                <?php endif; ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments" type="button" role="tab">
                <i class="fas fa-calendar-alt me-1"></i> Appointments
                <?php if (!empty($appointments)): ?>
                    <span class="badge bg-primary rounded-pill"><?= count($appointments) ?></span>
                <?php endif; ?>
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content p-3 border border-top-0 rounded-bottom bg-white">
        <!-- Overview Tab -->
        <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>Personal Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Patient ID</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($patient['patient_number']) ?></dd>

                                <dt class="col-sm-4">Full Name</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></dd>

                                <dt class="col-sm-4">Date of Birth</dt>
                                <dd class="col-sm-8"><?= date('F j, Y', strtotime ($patient['date_of_birth'])) ?></dd>

                                <dt class="col-sm-4">Age</dt>
                                <dd class="col-sm-8">
                                    <?php
                                        $dob = new DateTime($patient['date_of_birth']);
                                        $now = new DateTime();
                                        $age = $now->diff($dob);
                                        echo $age->y . ' years';
                                    ?>
                                </dd>


                                <dt class="col-sm-4">Gender</dt>
                                <dd class="col-sm-8"><?= ucfirst(htmlspecialchars($patient['gender'])) ?></dd>

                                <?php if (!empty($patient['blood_group'])): ?>
                                    <dt class="col-sm-4">Blood Group</dt>
                                    <dd class="col-sm-8"><?= htmlspecialchars($patient['blood_group']) ?></dd>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-address-card me-2"></i>Contact Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <?php if (!empty($patient['email'])): ?>
                                    <dt class="col-sm-4">Email</dt>
                                    <dd class="col-sm-8">
                                        <a href="mailto:<?= htmlspecialchars($patient['email']) ?>"><?= htmlspecialchars($patient['email']) ?></a>
                                    </dd>
                                <?php endif; ?>

                                <?php if (!empty($patient['phone'])): ?>
                                    <dt class="col-sm-4">Phone</dt>
                                    <dd class="col-sm-8">
                                        <a href="tel:<?= preg_replace('/[^0-9+]/', '', $patient['phone']) ?>"><?= htmlspecialchars($patient['phone']) ?></a>
                                    </dd>
                                <?php endif; ?>

                                <?php if (!empty($patient['address'])): ?>
                                    <dt class="col-sm-4">Address</dt>
                                    <dd class="col-sm-8">
                                        <?= nl2br(htmlspecialchars($patient['address'])) ?>
                                        <?php if (!empty($patient['city']) || !empty($patient['state']) || !empty($patient['postal_code'])): ?>
                                            <br>
                                            <?php 
                                                $addressParts = [];
                                                if (!empty($patient['city'])) $addressParts[] = $patient['city'];
                                                if (!empty($patient['state'])) $addressParts[] = $patient['state'];
                                                if (!empty($patient['postal_code'])) $addressParts[] = $patient['postal_code'];
                                                echo htmlspecialchars(implode(', ', $addressParts));
                                            ?>
                                        <?php endif; ?>
                                        <?php if (!empty($patient['country'])): ?>
                                            <br><?= htmlspecialchars($patient['country']) ?>
                                        <?php endif; ?>
                                    </dd>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <?php if (!empty($patient['emergency_contact_name']) || !empty($patient['emergency_contact_phone'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>Emergency Contact
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if (!empty($patient['emergency_contact_name'])): ?>
                                <div class="col-md-6">
                                    <strong>Name:</strong> <?= htmlspecialchars($patient['emergency_contact_name']) ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($patient['emergency_contact_phone'])): ?>
                                <div class="col-md-6">
                                    <strong>Phone:</strong> 
                                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $patient['emergency_contact_phone']) ?>">
                                        <?= htmlspecialchars($patient['emergency_contact_phone']) ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($patient['emergency_contact_relation'])): ?>
                                <div class="col-md-12 mt-2">
                                    <strong>Relationship:</strong> <?= htmlspecialchars($patient['emergency_contact_relation']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Medical History Tab -->
        <div class="tab-pane fade" id="medical" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Medical History</h5>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMedicalRecordModal">
                    <i class="fas fa-plus"></i> Add Medical Record
                </button>
            </div>

            <?php if (empty($medicalHistory)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No medical records found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Record Type</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($medicalHistory as $record): ?>
                                <tr>
                                    <td><?= date('M j, Y', strtotime($record['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($record['record_type']) ?></td>
                                    <td><?= nl2br(htmlspecialchars(substr($record['description'], 0, 100) . (strlen($record['description']) > 100 ? '...' : ''))) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary view-record" data-id="<?= $record['id'] ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Appointments Tab -->
        <div class="tab-pane fade" id="appointments" role="tabpanel">
            <h5 class="mb-3">Upcoming Appointments</h5>
            
            <?php if (empty($appointments)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No upcoming appointments found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date & Time</th>
                                <th>Appointment Type</th>
                                <th>Status</th>
                                <th>Doctor</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appointment): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= date('M j, Y', strtotime($appointment['appointment_date'])) ?></div>
                                        <div class="text-muted small"><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($appointment['appointment_type']) ?></td>
                                    <td>
                                        <span class="badge 
                                            <?= $appointment['status'] === 'scheduled' ? 'bg-success' : 
                                               ($appointment['status'] === 'completed' ? 'bg-secondary' : 'bg-warning') ?>">
                                            <?= ucfirst(htmlspecialchars($appointment['status'])) ?>
                                        </span>
                                    </td>
                                    <td>Dr. <?= htmlspecialchars($appointment['doctor_name'] ?? 'N/A') ?></td>
                                    <td>
                                        <a href="<?= $baseUrl ?>/appointments/view/<?= $appointment['appointment_id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="text-end mt-3">
                <a href="<?= $baseUrl ?>/appointments/create?patient_id=<?= $patient['patient_id'] ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Schedule New Appointment
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Add Medical Record Modal -->
<div class="modal fade" id="addMedicalRecordModal" tabindex="-1" aria-labelledby="addMedicalRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMedicalRecordModalLabel">Add Medical Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= $baseUrl ?>/patients/<?= $patient['patient_id'] ?>/medical-records" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="patient_id" value="<?= $patient['patient_id'] ?>">
                    
                    <div class="mb-3">
                        <label for="record_type" class="form-label">Record Type</label>
                        <select class="form-select" id="record_type" name="record_type" required>
                            <option value="">Select Record Type</option>
                            <option value="Consultation">Consultation</option>
                            <option value="Diagnosis">Diagnosis</option>
                            <option value="Treatment">Treatment</option>
                            <option value="Lab Result">Lab Result</option>
                            <option value="Prescription">Prescription</option>
                            <option value="Procedure">Procedure</option>
                            <option value="Follow-up">Follow-up</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="record_date" class="form-label">Record Date</label>
                                <input type="date" class="form-control" id="record_date" name="record_date" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="doctor_id" class="form-label">Doctor</label>
                                <select class="form-select" id="doctor_id" name="doctor_id">
                                    <option value="">Select Doctor</option>
                                    <?php foreach ($doctors ?? [] as $doctor): ?>
                                        <option value="<?= $doctor['staff_id'] ?>">Dr. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="attachments" class="form-label">Attachments</label>
                        <input class="form-control" type="file" id="attachments" name="attachments[]" multiple>
                        <div class="form-text">You can upload multiple files (max 5MB each).</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Medical Record Modal -->
<div class="modal fade" id="viewRecordModal" tabindex="-1" aria-labelledby="viewRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewRecordModalLabel">Medical Record Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="recordDetails">
                <!-- Record details will be loaded here via AJAX -->
                <div class="text-center my-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="printRecord" class="btn btn-outline-primary">
                    <i class="fas fa-print"></i> Print
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// View medical record details
$(document).ready(function() {
    $('.view-record').on('click', function() {
        const recordId = $(this).data('id');
        const modal = new bootstrap.Modal(document.getElementById('viewRecordModal'));
        
        // Show loading state
        $('#recordDetails').html(`
            <div class="text-center my-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        
        // Load record details via AJAX
        $.get(`<?= $baseUrl ?>/patients/<?= $patient['patient_id'] ?>/medical-records/${recordId}`, function(data) {
            $('#recordDetails').html(data);
            
            // Update print button href
            $('#printRecord').attr('href', `<?= $baseUrl ?>/patients/<?= $patient['patient_id'] ?>/medical-records/${recordId}/print`);
        }).fail(function() {
            $('#recordDetails').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Failed to load record details. Please try again.
                </div>
            `);
        });
        
        modal.show();
    });
});
</script>