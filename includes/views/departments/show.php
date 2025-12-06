<?php
/**
 * Nyalife HMS - Department Details
 *
 * View for displaying department information.
 */

$pageTitle = 'Department Details - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building fa-fw"></i> Department Details
        </h1>
        <div>
            <a href="<?= $baseUrl ?>/departments/edit/<?= $department['department_id'] ?>" class="btn btn-warning btn-sm">
                <i class="fas fa-edit fa-fw"></i> Edit Department
            </a>
            <a href="<?= $baseUrl ?>/departments" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left fa-fw"></i> Back to Departments
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_messages'])): ?>
        <?php foreach ($_SESSION['flash_messages'] as $message): ?>
            <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
        <?php unset($_SESSION['flash_messages']); ?>
    <?php endif; ?>

    <!-- Department Information -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Department Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Department Name:</label>
                                <p class="form-control-plaintext"><?= htmlspecialchars($department['department_name'] ?? $department['name'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Department Code:</label>
                                <p class="form-control-plaintext">
                                    <span class="badge badge-info"><?= htmlspecialchars($department['code'] ?? 'N/A') ?></span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Department Type:</label>
                                <p class="form-control-plaintext">
                                    <?php
                                    $typeClass = '';
switch ($department['type']) {
    case 'clinical':
        $typeClass = 'badge-success';
        break;
    case 'administrative':
        $typeClass = 'badge-warning';
        break;
    case 'support':
        $typeClass = 'badge-info';
        break;
    default:
        $typeClass = 'badge-secondary';
}
?>
                                    <span class="badge <?= $typeClass ?>">
                                        <?= ucfirst($department['type']) ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Status:</label>
                                <p class="form-control-plaintext">
                                    <span class="badge <?= ($department['is_active'] ?? 1) == 1 ? 'badge-success' : 'badge-secondary' ?>">
                                        <?= ($department['is_active'] ?? 1) == 1 ? 'Active' : 'Inactive' ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($department['description'])): ?>
                        <div class="form-group">
                            <label class="font-weight-bold">Description:</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($department['description']) ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Location:</label>
                                <p class="form-control-plaintext"><?= htmlspecialchars($department['location'] ?? 'Not specified') ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Contact Number:</label>
                                <p class="form-control-plaintext"><?= htmlspecialchars($department['contact_number'] ?? 'Not specified') ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Email:</label>
                                <p class="form-control-plaintext"><?= htmlspecialchars($department['email'] ?? 'Not specified') ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Created:</label>
                                <p class="form-control-plaintext"><?= date('M d, Y', strtotime($department['created_at'])) ?></p>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($department['notes'])): ?>
                        <div class="form-group">
                            <label class="font-weight-bold">Notes:</label>
                            <p class="form-control-plaintext"><?= htmlspecialchars($department['notes']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Department Head -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Department Head</h6>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($department['head_name'])): ?>
                        <img src="<?= !empty($department['head_image']) ? $baseUrl . '/uploads/profiles/' . $department['head_image'] : $baseUrl . '/assets/img/placeholders/default-avatar.png' ?>"
                             class="rounded-circle mb-3" width="80" height="80" alt="Department Head">
                        <h5 class="font-weight-bold"><?= htmlspecialchars($department['head_name']) ?></h5>
                        <p class="text-muted"><?= htmlspecialchars($department['head_role'] ?? '') ?></p>
                        <a href="<?= $baseUrl ?>/users/view/<?= $department['head_id'] ?>" class="btn btn-sm btn-info">
                            <i class="fas fa-user fa-fw"></i> View Profile
                        </a>
                    <?php else: ?>
                        <i class="fas fa-user-tie fa-3x text-gray-300 mb-3"></i>
                        <h5 class="text-gray-500">No Head Assigned</h5>
                        <p class="text-gray-400">This department has no head assigned.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <div class="h4 mb-0 text-primary"><?= $department['staff_count'] ?? 0 ?></div>
                                <div class="text-xs text-muted">Staff Members</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-0 text-success"><?= $department['active_staff_count'] ?? 0 ?></div>
                            <div class="text-xs text-muted">Active Staff</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Staff -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Department Staff</h6>
            <a href="<?= $baseUrl ?>/departments/<?= $department['department_id'] ?>/staff/add" class="btn btn-primary btn-sm">
                <i class="fas fa-plus fa-fw"></i> Add Staff Member
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($departmentStaff)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">No staff assigned</h5>
                    <p class="text-gray-400">This department has no staff members assigned yet.</p>
                    <a href="<?= $baseUrl ?>/departments/<?= $department['department_id'] ?>/staff/add" class="btn btn-primary">
                        <i class="fas fa-plus fa-fw"></i> Add First Staff Member
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="staffTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departmentStaff as $staff): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= !empty($staff['image']) ? $baseUrl . '/uploads/profiles/' . $staff['image'] : $baseUrl . '/assets/img/placeholders/default-avatar.png' ?>"
                                                 class="rounded-circle mr-2" width="32" height="32" alt="Staff">
                                            <div>
                                                <strong><?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?= htmlspecialchars($staff['role']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($staff['email'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($staff['phone'] ?? '') ?></td>
                                    <td>
                                        <span class="badge <?= $staff['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>">
                                            <?= ucfirst($staff['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= $baseUrl ?>/users/view/<?= $staff['user_id'] ?>" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye fa-fw"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" title="Remove from Department"
                                                    onclick="removeStaffMember(<?= $staff['user_id'] ?>)">
                                                <i class="fas fa-user-times fa-fw"></i>
                                            </button>
                                        </div>
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
function removeStaffMember(userId) {
    if (confirm('Are you sure you want to remove this staff member from the department?')) {
        window.location.href = '<?= $baseUrl ?>/departments/<?= $department['department_id'] ?>/staff/remove/' + userId;
    }
}

// Initialize DataTable
$(document).ready(function() {
    $('#staffTable').DataTable({
        "pageLength": 10,
        "order": [[0, "asc"]],
        "language": {
            "search": "Search staff:",
            "lengthMenu": "Show _MENU_ staff per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ staff"
        }
    });
});
</script>

 