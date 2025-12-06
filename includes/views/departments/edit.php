<?php
/**
 * Nyalife HMS - Edit Department View
 *
 * View for editing department information.
 */

$pageTitle = 'Edit Department - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit fa-fw"></i> Edit Department
        </h1>
        <a href="<?= $baseUrl ?>/departments" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left fa-fw"></i> Back to Departments
        </a>
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

    <!-- Edit Department Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Department Information</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= $baseUrl ?>/departments/update/<?= $department['department_id'] ?>" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Department Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($department['department_name'] ?? $department['name'] ?? '') ?>" required>
                            <div class="invalid-feedback">
                                Please provide a department name.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="code">Department Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="code" name="code" 
                                   value="<?= htmlspecialchars($department['code'] ?? '') ?>" required>
                            <div class="invalid-feedback">
                                Please provide a department code.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="type">Department Type <span class="text-danger">*</span></label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="clinical" <?= $department['type'] === 'clinical' ? 'selected' : '' ?>>Clinical</option>
                                <option value="administrative" <?= $department['type'] === 'administrative' ? 'selected' : '' ?>>Administrative</option>
                                <option value="support" <?= $department['type'] === 'support' ? 'selected' : '' ?>>Support</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a department type.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="head_id">Department Head</label>
                            <select class="form-control" id="head_id" name="head_id">
                                <option value="">Select Department Head</option>
                                <?php foreach ($staff as $member): ?>
                                    <option value="<?= $member['user_id'] ?>" <?= $department['head_id'] == $member['user_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?> 
                                        (<?= htmlspecialchars($member['role']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($department['description'] ?? '') ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   value="<?= htmlspecialchars($department['location'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <input type="tel" class="form-control" id="contact_number" name="contact_number" 
                                   value="<?= htmlspecialchars($department['contact_number'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($department['email'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="1" <?= ($department['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= ($department['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($department['notes'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save fa-fw"></i> Update Department
                    </button>
                    <a href="<?= $baseUrl ?>/departments" class="btn btn-secondary">
                        <i class="fas fa-times fa-fw"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Department Staff Section -->
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
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
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

// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>

 