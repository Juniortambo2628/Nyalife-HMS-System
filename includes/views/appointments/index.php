<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Appointments</h1>
        <div>
            <a href="<?= $baseUrl ?>/appointments/calendar" class="btn btn-outline-primary me-2">
                <i class="fas fa-calendar-alt me-1"></i> Calendar View
            </a>
            <?php if ($userRole == 'admin' || $userRole == 'doctor' || $userRole == 'patient'): ?>
            <a href="<?= $baseUrl ?>/appointments/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Appointment
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Manage Appointments</h6>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" class="form-control" id="appointment-search" placeholder="Search appointments...">
                        <button class="btn btn-primary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" id="status-filter">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <?php if (empty($appointments)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> No appointments found.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="appointmentsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appointment): ?>
                                <tr data-status="<?= $appointment['status'] ?>">
                                    <td><?= $appointment['appointment_id'] ?></td>
                                    <td>
                                        <a href="<?= $baseUrl ?>/patients/view/<?= $appointment['patient_id'] ?>">
                                            <?= htmlspecialchars($appointment['patient_name']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($appointment['doctor_name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($appointment['appointment_date'])) ?></td>
                                    <td><?= date('h:i A', strtotime($appointment['appointment_time'])) ?></td>
                                    <td>
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
                                            case 'pending':
                                                $statusClass = 'bg-warning text-dark';
                                                break;
                                            default:
                                                $statusClass = 'bg-secondary';
                                        }
                                        ?>
                                        <span class="badge <?= $statusClass ?>">
                                            <?= ucfirst($appointment['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= $baseUrl ?>/appointments/view/<?= $appointment['appointment_id'] ?>" class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if ($userRole == 'admin' || ($userRole == 'doctor' && $appointment['status'] != 'cancelled' && $appointment['status'] != 'completed')): ?>
                                        <a href="<?= $baseUrl ?>/appointments/edit/<?= $appointment['appointment_id'] ?>" class="btn btn-xs btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if (($userRole == 'admin' || $userRole == 'doctor' || ($userRole == 'patient' && $appointment['status'] != 'completed')) && $appointment['status'] != 'cancelled'): ?>
                                        <a href="<?= $baseUrl ?>/appointments/cancel/<?= $appointment['appointment_id'] ?>" class="btn btn-xs btn-danger cancel-appointment">
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('appointment-search');
    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#appointmentsTable tbody tr');
        
        rows.forEach(row => {
            let found = false;
            const cells = row.querySelectorAll('td');
            
            cells.forEach(cell => {
                if (cell.textContent.toLowerCase().includes(searchTerm)) {
                    found = true;
                }
            });
            
            row.style.display = found ? '' : 'none';
        });
    });
    
    // Status filter
    const statusFilter = document.getElementById('status-filter');
    statusFilter.addEventListener('change', function() {
        const selectedStatus = this.value.toLowerCase();
        const rows = document.querySelectorAll('#appointmentsTable tbody tr');
        
        rows.forEach(row => {
            if (!selectedStatus || row.getAttribute('data-status') === selectedStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Cancel appointment confirmation
    const cancelButtons = document.querySelectorAll('.cancel-appointment');
    cancelButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to cancel this appointment?')) {
                e.preventDefault();
            }
        });
    });
});
</script>
