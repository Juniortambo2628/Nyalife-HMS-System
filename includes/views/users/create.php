<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Add New User</h1>
        <a href="<?= $baseUrl ?>/users" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> Back to Users
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
        </div>
        <div class="card-body">
            <?php 
            $errors = SessionManager::get('form_errors', []);
            $formData = SessionManager::get('form_data', []);
            
            // Clear the session data
            SessionManager::remove('form_errors');
            SessionManager::remove('form_data');
            
            if (!empty($errors) && isset($errors['general'])): 
            ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $errors['general'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <form action="<?= $baseUrl ?>/users/store" method="post" id="createUserForm">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                               id="first_name" name="first_name" value="<?= $formData['first_name'] ?? '' ?>" required>
                        <?php if (isset($errors['first_name'])): ?>
                        <div class="invalid-feedback"><?= $errors['first_name'] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                               id="last_name" name="last_name" value="<?= $formData['last_name'] ?? '' ?>" required>
                        <?php if (isset($errors['last_name'])): ?>
                        <div class="invalid-feedback"><?= $errors['last_name'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                               id="email" name="email" value="<?= $formData['email'] ?? '' ?>" required>
                        <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?= $errors['email'] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                               id="phone" name="phone" value="<?= $formData['phone'] ?? '' ?>" required>
                        <?php if (isset($errors['phone'])): ?>
                        <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="role_id" class="form-label">Role</label>
                        <select class="form-select <?= isset($errors['role_id']) ? 'is-invalid' : '' ?>" 
                                id="role_id" name="role_id" required>
                            <option value="">Select Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['role_id'] ?>" <?= (isset($formData['role_id']) && $formData['role_id'] == $role['role_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role['role_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['role_id'])): ?>
                        <div class="invalid-feedback"><?= $errors['role_id'] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                               id="username" name="username" value="<?= $formData['username'] ?? '' ?>" required>
                        <?php if (isset($errors['username'])): ?>
                        <div class="invalid-feedback"><?= $errors['username'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Doctor-specific fields (only shown when role is doctor) -->
                <div id="doctor-fields" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="specialization" class="form-label">Specialization</label>
                            <input type="text" class="form-control" id="specialization" name="specialization" value="<?= $formData['specialization'] ?? '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="license_number" class="form-label">License Number</label>
                            <input type="text" class="form-control" id="license_number" name="license_number" value="<?= $formData['license_number'] ?? '' ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                               id="password" name="password" required>
                        <div class="form-text">Password must be at least 8 characters and include uppercase, lowercase, and numbers.</div>
                        <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?= $errors['password'] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                               id="confirm_password" name="confirm_password" required>
                        <?php if (isset($errors['confirm_password'])): ?>
                        <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-outline-secondary">Reset</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role_id');
    const doctorFields = document.getElementById('doctor-fields');
    
    // Show/hide doctor-specific fields based on role selection
    roleSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const roleName = selectedOption.textContent.trim().toLowerCase();
        
        if (roleName === 'doctor') {
            doctorFields.style.display = 'block';
        } else {
            doctorFields.style.display = 'none';
        }
    });
    
    // Form validation
    const createUserForm = document.getElementById('createUserForm');
    createUserForm.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match');
        }
    });
    
    // Initialize - check if doctor role is already selected (in case of form errors)
    const roleOptions = Array.from(roleSelect.options);
    const selectedIndex = roleSelect.selectedIndex;
    
    if (selectedIndex > 0) {
        const roleName = roleOptions[selectedIndex].textContent.trim().toLowerCase();
        if (roleName === 'doctor') {
            doctorFields.style.display = 'block';
        }
    }
});
</script>
