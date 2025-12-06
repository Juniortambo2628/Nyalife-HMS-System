<?php
/**
 * Nyalife HMS - Create Department
 *
 * View for creating new departments.
 */

$pageTitle = 'Create Department - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-plus fa-fw"></i> Create Department
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

    <!-- Create Department Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Department Information</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= $baseUrl ?>/departments/store" id="createDepartmentForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Department Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
                                   required maxlength="100" placeholder="Enter department name">
                            <small class="form-text text-muted">Enter a unique department name (max 100 characters)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="code">Department Code</label>
                            <input type="text" class="form-control" id="code" name="code" 
                                   value="<?= htmlspecialchars($_POST['code'] ?? '') ?>" 
                                   maxlength="10" placeholder="Enter department code">
                            <small class="form-text text-muted">Optional short code for the department (max 10 characters)</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" 
                              placeholder="Enter department description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    <small class="form-text text-muted">Provide a detailed description of the department's purpose and responsibilities</small>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" 
                                   placeholder="Enter department location">
                            <small class="form-text text-muted">Physical location of the department</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <input type="tel" class="form-control" id="contact_number" name="contact_number" 
                                   value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>" 
                                   placeholder="Enter contact number">
                            <small class="form-text text-muted">Department contact number</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                                   placeholder="Enter email address">
                            <small class="form-text text-muted">Department email address</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="head_of_department">Head of Department</label>
                            <input type="text" class="form-control" id="head_of_department" name="head_of_department" 
                                   value="<?= htmlspecialchars($_POST['head_of_department'] ?? '') ?>" 
                                   placeholder="Enter head of department name">
                            <small class="form-text text-muted">Name of the department head</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select class="form-control" id="is_active" name="is_active">
                                <option value="1" <?= ($_POST['is_active'] ?? '1') == '1' ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= ($_POST['is_active'] ?? '1') == '0' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                            <small class="form-text text-muted">Set the department status</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="color">Department Color</label>
                            <input type="color" class="form-control" id="color" name="color" 
                                   value="<?= htmlspecialchars($_POST['color'] ?? '#007bff') ?>">
                            <small class="form-text text-muted">Choose a color to represent this department</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Additional Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                              placeholder="Enter any additional notes"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                    <small class="form-text text-muted">Any additional information about the department</small>
                </div>

                <hr>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save fa-fw"></i> Create Department
                    </button>
                    <a href="<?= $baseUrl ?>/departments" class="btn btn-secondary">
                        <i class="fas fa-times fa-fw"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Form validation
    $('#createDepartmentForm').on('submit', function(e) {
        const name = $('#name').val().trim();
        const description = $('#description').val().trim();
        
        if (!name) {
            e.preventDefault();
            alert('Department name is required.');
            $('#name').focus();
            return false;
        }
        
        if (name.length > 100) {
            e.preventDefault();
            alert('Department name cannot exceed 100 characters.');
            $('#name').focus();
            return false;
        }
        
        if (description.length > 500) {
            e.preventDefault();
            alert('Description cannot exceed 500 characters.');
            $('#description').focus();
            return false;
        }
        
        // Show loading state
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin fa-fw"></i> Creating...');
    });
    
    // Auto-generate department code from name
    $('#name').on('input', function() {
        const name = $(this).val().trim();
        if (name && !$('#code').val()) {
            const code = name.replace(/[^A-Za-z0-9]/g, '').substring(0, 10).toUpperCase();
            $('#code').val(code);
        }
    });
});
</script>

 