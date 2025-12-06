<?php
/**
 * Nyalife HMS - Users Index View
 */

$pageTitle = 'Users - Nyalife HMS';
?>
<div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">User Management</h1>
            <a href="<?= $baseUrl ?>/users/create" class="btn btn-primary">
                <i class="fas fa-user-plus me-1"></i> Add New User
            </a>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Users</h6>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <input type="text" class="form-control" id="user-search" placeholder="Search users...">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="role-filter">
                            <option value="">All Roles</option>
                            <option value="admin">Administrators</option>
                            <option value="doctor">Doctors</option>
                            <option value="nurse">Nurses</option>
                            <option value="lab_technician">Lab Technicians</option>
                            <option value="pharmacist">Pharmacists</option>
                            <option value="patient">Patients</option>
                        </select>
                    </div>
                </div>

                <?php if (empty($users)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> No users found.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="usersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr data-role="<?= strtolower($user['role_name']) ?>">
                                        <td><?= $user['user_id'] ?></td>
                                        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
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
                                            <span class="badge <?= $roleClass ?>">
                                                <?= ucfirst($user['role_name']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $user['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                        <td class="text-center">
                                            <a href="<?= $baseUrl ?>/users/view/<?= $user['user_id'] ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= $baseUrl ?>/users/edit/<?= $user['user_id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['user_id'] != $currentUser['id']): ?>
                                            <a href="<?= $baseUrl ?>/users/delete/<?= $user['user_id'] ?>" class="btn btn-sm btn-danger delete-user">
                                                <i class="fas fa-trash"></i>
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
// Initialize components when the page is loaded or reloaded via AJAX
document.addEventListener('DOMContentLoaded', initUsersPage);
document.addEventListener('page:loaded', initUsersPage);

function initUsersPage() {
    // Initialize DataTable if available
    if ($.fn.DataTable && document.getElementById('usersTable')) {
        $('#usersTable').DataTable({
            "paging": true,
            "ordering": true,
            "info": true,
            "responsive": true,
            "searching": false, // We have our own search
            "dom": '<"top"f>rt<"bottom"ip><"clear">'
        });
    }
    
    // Search functionality
    const searchInput = document.getElementById('user-search');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#usersTable tbody tr');
            
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
    }
    
    // Role filter
    const roleFilter = document.getElementById('role-filter');
    if (roleFilter) {
        roleFilter.addEventListener('change', function() {
            const selectedRole = this.value.toLowerCase();
            const rows = document.querySelectorAll('#usersTable tbody tr');
            
            rows.forEach(row => {
                if (!selectedRole || row.getAttribute('data-role') === selectedRole) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Delete user confirmation with AJAX support
    const deleteButtons = document.querySelectorAll('.delete-user');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                if (typeof Components !== 'undefined') {
                    // Use AJAX to delete the user
                    fetch(this.href, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload the current page to reflect the changes
                            Components.loadPage(window.location.href);
                        } else {
                            alert(data.message || 'An error occurred while deleting the user.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An unexpected error occurred. Please try again.');
                    });
                } else {
                    // Fallback to traditional navigation if Components is not available
                    window.location.href = this.href;
                }
            }
        });
    });
}
</script>
