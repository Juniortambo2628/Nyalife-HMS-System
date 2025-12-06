<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">User Details</h1>
        <div>
            <a href="<?= $baseUrl ?>/users" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Back to Users
            </a>
            <?php if ($currentUser['id'] != $user['user_id']): ?>
            <a href="<?= $baseUrl ?>/users/edit/<?= $user['user_id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Edit User
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <!-- User Information -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
                    <span class="badge <?= $user['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                        <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Full Name</h6>
                            <p class="mb-0 fs-5"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Username</h6>
                            <p class="mb-0 fs-5"><?= htmlspecialchars($user['username']) ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Email</h6>
                            <p class="mb-0"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Phone</h6>
                            <p class="mb-0"><?= htmlspecialchars($user['phone']) ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Role</h6>
                            <p class="mb-0">
                                <span class="badge 
                                    <?php
                                    $roleClass = '';
                                    switch (strtolower($user['role_name'])) {
                                        case 'admin':
                                            $roleClass = 'bg-danger';
                                            break;
                                        case 'doctor':
                                            $roleClass = 'bg-primary';
                                            break;
                                        case 'nurse':
                                            $roleClass = 'bg-success';
                                            break;
                                        case 'lab_technician':
                                            $roleClass = 'bg-info';
                                            break;
                                        case 'pharmacist':
                                            $roleClass = 'bg-warning text-dark';
                                            break;
                                        case 'patient':
                                            $roleClass = 'bg-secondary';
                                            break;
                                        default:
                                            $roleClass = 'bg-secondary';
                                            break;
                                    }
                                    ?>
                                    <?= $roleClass ?>">
                                    <?= ucfirst($user['role_name']) ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Created Date</h6>
                            <p class="mb-0"><?= date('M d, Y', strtotime($user['created_at'])) ?></p>
                        </div>
                    </div>
                    
                    <?php if (strtolower($user['role_name']) == 'doctor' && isset($doctorDetails)): ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Specialization</h6>
                            <p class="mb-0"><?= htmlspecialchars($doctorDetails['specialization'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">License Number</h6>
                            <p class="mb-0"><?= htmlspecialchars($doctorDetails['license_number'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (strtolower($user['role_name']) == 'patient' && isset($patientDetails)): ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Patient Number</h6>
                            <p class="mb-0"><?= htmlspecialchars($patientDetails['patient_number'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Blood Group</h6>
                            <p class="mb-0"><?= htmlspecialchars($patientDetails['blood_group'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (strtolower($user['role_name']) == 'patient' && isset($patientDetails)): ?>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h6 class="text-muted mb-1">Emergency Contact</h6>
                            <p class="mb-0">
                                <?= htmlspecialchars($patientDetails['emergency_name'] ?? 'N/A') ?> 
                                (<?= htmlspecialchars($patientDetails['relationship'] ?? 'N/A') ?>)
                                <?= !empty($patientDetails['emergency_contact']) ? '- ' . htmlspecialchars($patientDetails['emergency_contact']) : '' ?>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Activity and Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Account Activity</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Last Login</h6>
                        <p class="mb-0"><?= isset($user['last_login']) ? date('M d, Y, h:i A', strtotime($user['last_login'])) : 'Never' ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Last Updated</h6>
                        <p class="mb-0"><?= date('M d, Y', strtotime($user['updated_at'])) ?></p>
                    </div>
                    
                    <?php if (isset($activityLogs) && !empty($activityLogs)): ?>
                    <div class="mb-0">
                        <h6 class="text-muted mb-1">Recent Activity</h6>
                        <ul class="list-group small">
                            <?php foreach(array_slice($activityLogs, 0, 5) as $log): ?>
                            <li class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <span><?= ucfirst($log['action']) ?></span>
                                    <small class="text-muted"><?= date('M d, h:i A', strtotime($log['created_at'])) ?></small>
                                </div>
                                <small><?= htmlspecialchars($log['description']) ?></small>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= $baseUrl ?>/users/edit/<?= $user['user_id'] ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i> Edit User
                        </a>
                        
                        <?php if ($currentUser['id'] != $user['user_id']): ?>
                        <button type="button" class="btn btn-<?= $user['is_active'] ? 'danger' : 'success' ?>" 
                                data-bs-toggle="modal" data-bs-target="#statusModal">
                            <i class="fas fa-<?= $user['is_active'] ? 'user-slash' : 'user-check' ?> me-2"></i>
                            <?= $user['is_active'] ? 'Deactivate User' : 'Activate User' ?>
                        </button>
                        
                        <?php if (strtolower($user['role_name']) == 'patient'): ?>
                        <a href="<?= $baseUrl ?>/appointments/create?patient_id=<?= $user['user_id'] ?>" class="btn btn-primary">
                            <i class="fas fa-calendar-plus me-2"></i> Schedule Appointment
                        </a>
                        <?php endif; ?>
                        
                        <a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash me-2"></i> Delete User
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (strtolower($user['role_name']) == 'patient' && isset($patientAppointments)): ?>
    <!-- Patient Appointments -->
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Patient Appointments</h6>
            <a href="<?= $baseUrl ?>/appointments/create?patient_id=<?= $user['user_id'] ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> New Appointment
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($patientAppointments)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> No appointments found for this patient.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Doctor</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patientAppointments as $appointment): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($appointment['appointment_date'])) ?></td>
                                    <td><?= date('h:i A', strtotime($appointment['appointment_time'])) ?></td>
                                    <td><?= htmlspecialchars($appointment['doctor_name']) ?></td>
                                    <td><?= ucwords(str_replace('_', ' ', $appointment['appointment_type'])) ?></td>
                                    <td>
                                        <span class="badge 
                                            <?php
                                            $statusClass = '';
                                            switch ($appointment['status']) {
                                                case 'scheduled':
                                                    $statusClass = 'bg-primary';
                                                    break;
                                                case 'completed':
                                                    $statusClass = 'bg-success';
                                                    break;
                                                case 'cancelled':
                                                    $statusClass = 'bg-danger';
                                                    break;
                                                case 'no_show':
                                                    $statusClass = 'bg-warning text-dark';
                                                    break;
                                                default:
                                                    $statusClass = 'bg-secondary';
                                            }
                                            ?>
                                            <?= $statusClass ?>">
                                            <?= ucfirst(str_replace('_', ' ', $appointment['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= $baseUrl ?>/appointments/view/<?= $appointment['appointment_id'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($appointment['status'] == 'scheduled'): ?>
                                        <a href="<?= $baseUrl ?>/appointments/edit/<?= $appointment['appointment_id'] ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= $baseUrl ?>/appointments/cancel/<?= $appointment['appointment_id'] ?>" class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to cancel this appointment?');">
                                            <i class="fas fa-times"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">
                    <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?> User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to <?= $user['is_active'] ? 'deactivate' : 'activate' ?> 
                    <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>?</p>
                
                <?php if ($user['is_active']): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Deactivating a user will prevent them from logging into the system.
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Activating a user will allow them to log into the system again.
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="<?= $baseUrl ?>/users/toggle-status/<?= $user['user_id'] ?>" class="btn btn-<?= $user['is_active'] ? 'danger' : 'success' ?>">
                    <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>?</p>
                
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This action cannot be undone! Deleting a user will remove all their associated data from the system.
                    <br><br>
                    Instead of deleting, consider deactivating the user account.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="<?= $baseUrl ?>/users/delete/<?= $user['user_id'] ?>" class="btn btn-danger">
                    Delete Permanently
                </a>
            </div>
        </div>
    </div>
</div>
