<?php
/**
 * Nyalife HMS - Reusable Header Component
 * This file contains the header HTML that can be loaded via AJAX or included directly
 */

$pageTitle = 'Header - Nyalife HMS';

// Check if this file is being accessed directly or via AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// If not AJAX and not included in another file, prevent direct access
if (!$isAjax && !defined('NYALIFE_INCLUDED')) {
    http_response_code(403);
    exit('Direct access to this file is not allowed.');
}

// Variables that should be passed:
// $baseUrl - The base URL of the application
// $isLoggedIn - Whether the user is logged in
// $currentUser - The current user's information
// $activeMenu - The active menu item

// Set default value for $activeMenu if not defined
if (!isset($activeMenu)) {
    // Try to determine active menu from current URL
    $currentUrl = $_SERVER['REQUEST_URI'] ?? '';

    if (str_contains((string) $currentUrl, '/dashboard')) {
        $activeMenu = 'dashboard';
    } elseif (str_contains((string) $currentUrl, '/patients')) {
        $activeMenu = 'patients';
    } elseif (str_contains((string) $currentUrl, '/appointments')) {
        $activeMenu = 'appointments';
    } elseif (str_contains((string) $currentUrl, '/consultations')) {
        $activeMenu = 'consultations';
    } elseif (str_contains((string) $currentUrl, '/departments')) {
        $activeMenu = 'departments';
    } elseif (str_contains((string) $currentUrl, '/invoices')) {
        $activeMenu = 'invoices';
    } elseif (str_contains((string) $currentUrl, '/payments')) {
        $activeMenu = 'payments';
    } elseif (str_contains((string) $currentUrl, '/follow-ups')) {
        $activeMenu = 'follow_ups';
    } elseif (str_contains((string) $currentUrl, '/lab')) {
        $activeMenu = 'lab';
    } elseif (str_contains((string) $currentUrl, '/pharmacy')) {
        $activeMenu = 'pharmacy';
    } elseif (str_contains((string) $currentUrl, '/prescriptions')) {
        $activeMenu = 'prescriptions';
    } elseif (str_contains((string) $currentUrl, '/messages')) {
        $activeMenu = 'messages';
    } elseif (str_contains((string) $currentUrl, '/users') || str_contains((string) $currentUrl, '/settings') || str_contains((string) $currentUrl, '/reports')) {
        $activeMenu = 'admin';
    } else {
        $activeMenu = '';
    }
}

// Define role-based navigation permissions
$rolePermissions = [
    'admin' => [
        'dashboard' => true,
        'patients' => true,
        'appointments' => true,
        'consultations' => true,
        'departments' => true,
        'invoices' => true,
        'payments' => true,
        'follow_ups' => true,
        'lab' => ['requests' => true, 'tests' => true],
        'pharmacy' => ['medicines' => true, 'inventory' => true, 'orders' => true],
        'prescriptions' => true,
        'messages' => true,
        'admin' => ['users' => true, 'settings' => true, 'reports' => true]
    ],
    'doctor' => [
        'dashboard' => true,
        'patients' => true,
        'appointments' => true,
        'consultations' => true,
        'follow_ups' => true,
        'lab' => ['requests' => true, 'tests' => false],
        'prescriptions' => true,
        'messages' => true
    ],
    'nurse' => [
        'dashboard' => true,
        'patients' => true,
        'appointments' => true,
        'consultations' => true,
        'follow_ups' => true,
        'messages' => true
    ],
    'lab_technician' => [
        'dashboard' => true,
        'lab' => ['requests' => true, 'tests' => true],
        'messages' => true
    ],
    'pharmacist' => [
        'dashboard' => true,
        'pharmacy' => ['medicines' => true, 'inventory' => true, 'orders' => true],
        'prescriptions' => true,
        'messages' => true
    ],
    'patient' => [
        'dashboard' => true,
        'appointments' => true,
        'messages' => true
    ]
];

// Get current user's role
$userRole = $currentUser['role'] ?? '';
$userPermissions = $rolePermissions[$userRole] ?? [];

// Debug information (remove in production)
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_log("Header Debug - User Role: " . $userRole);
    error_log("Header Debug - Current User: " . print_r($currentUser, true));
    error_log("Header Debug - User Permissions: " . print_r($userPermissions, true));
}

// Helper function to check if user has access to a module
function hasModuleAccess($permissions, $module)
{
    if (!isset($permissions[$module])) {
        return false;
    }

    if (is_array($permissions[$module])) {
        // For modules with sub-items, check if any sub-item is accessible
        return array_filter($permissions[$module]) !== [];
    }

    return $permissions[$module];
}

// Helper function to check if user has access to a specific sub-module
function hasSubModuleAccess($permissions, $module, $subModule)
{
    if (!isset($permissions[$module]) || !is_array($permissions[$module])) {
        return false;
    }

    return isset($permissions[$module][$subModule]) && $permissions[$module][$subModule];
}
?>

<!-- Header -->
<header class="header">
    <nav class="navbar navbar-expand-lg navbar-dark h-100" style="background-color: var(--primary-color);">
        <div class="container">
            <a class="navbar-brand" href="<?= $baseUrl ?>">
                <img src="<?= $baseUrl ?>/assets/img/logo/Logo2-transparent.png" alt="Nyalife HMS" height="40">
                Nyalife HMS
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if ($isLoggedIn): ?>
                        <!-- Dashboard - Available to all logged-in users -->
                        <li class="nav-item">
                            <a class="nav-link <?= $activeMenu === 'dashboard' ? 'active' : '' ?>" href="<?= $baseUrl ?>/dashboard">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        
                        <!-- Patient Care - Grouped dropdown for patient-related functions -->
                        <?php if (hasModuleAccess($userPermissions, 'patients') || hasModuleAccess($userPermissions, 'appointments') || hasModuleAccess($userPermissions, 'consultations') || hasModuleAccess($userPermissions, 'follow_ups')): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle <?= (in_array($activeMenu, ['patients', 'appointments', 'consultations', 'follow_ups'])) ? 'active' : '' ?>" href="javascript:void(0);" id="patientCareDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-injured me-1"></i>Patient Care
                                </a>
                                <ul class="dropdown-menu">
                                    <?php if (hasModuleAccess($userPermissions, 'patients')): ?>
                                        <li><a class="dropdown-item <?= $activeMenu === 'patients' ? 'active' : '' ?>" href="<?= $baseUrl ?>/patients"><i class="fas fa-users me-2"></i>Patients</a></li>
                                    <?php endif; ?>
                                    <?php if (hasModuleAccess($userPermissions, 'appointments')): ?>
                                        <li><a class="dropdown-item <?= $activeMenu === 'appointments' ? 'active' : '' ?>" href="<?= $baseUrl ?>/appointments"><i class="fas fa-calendar-alt me-2"></i>Appointments</a></li>
                                    <?php endif; ?>
                                    <?php if (hasModuleAccess($userPermissions, 'consultations')): ?>
                                        <li><a class="dropdown-item <?= $activeMenu === 'consultations' ? 'active' : '' ?>" href="<?= $baseUrl ?>/consultations"><i class="fas fa-stethoscope me-2"></i>Consultations</a></li>
                                    <?php endif; ?>
                                    <?php if (hasModuleAccess($userPermissions, 'follow_ups')): ?>
                                        <li><a class="dropdown-item <?= $activeMenu === 'follow_ups' ? 'active' : '' ?>" href="<?= $baseUrl ?>/follow-ups"><i class="fas fa-phone me-2"></i>Follow-ups</a></li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Clinical Services - Grouped dropdown for clinical functions -->
                        <?php if (hasModuleAccess($userPermissions, 'lab') || hasModuleAccess($userPermissions, 'pharmacy') || hasModuleAccess($userPermissions, 'prescriptions')): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle <?= (in_array($activeMenu, ['lab', 'pharmacy', 'prescriptions']) || str_starts_with((string) $activeMenu, 'lab_') || str_starts_with((string) $activeMenu, 'pharmacy_')) ? 'active' : '' ?>" href="javascript:void(0);" id="clinicalServicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-hospital me-1"></i>Clinical Services
                                </a>
                                <ul class="dropdown-menu">
                                    <?php if (hasModuleAccess($userPermissions, 'lab')): ?>
                                        <li><h6 class="dropdown-header"><i class="fas fa-flask me-2"></i>Laboratory</h6></li>
                                        <?php if (hasSubModuleAccess($userPermissions, 'lab', 'requests')): ?>
                                            <li><a class="dropdown-item <?= $activeMenu === 'lab_requests' ? 'active' : '' ?>" href="<?= $baseUrl ?>/lab/requests"><i class="fas fa-file-medical me-2"></i>Lab Requests</a></li>
                                        <?php endif; ?>
                                        <?php if (hasSubModuleAccess($userPermissions, 'lab', 'tests')): ?>
                                            <li><a class="dropdown-item <?= $activeMenu === 'lab_tests' ? 'active' : '' ?>" href="<?= $baseUrl ?>/lab/tests"><i class="fas fa-vial me-2"></i>Lab Tests</a></li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider"></li>
                                    <?php endif; ?>
                                    
                                    <?php if (hasModuleAccess($userPermissions, 'pharmacy')): ?>
                                        <li><h6 class="dropdown-header"><i class="fas fa-pills me-2"></i>Pharmacy</h6></li>
                                        <?php if (hasSubModuleAccess($userPermissions, 'pharmacy', 'medicines')): ?>
                                            <li><a class="dropdown-item <?= $activeMenu === 'pharmacy_medicines' ? 'active' : '' ?>" href="<?= $baseUrl ?>/pharmacy/medicines"><i class="fas fa-pills me-2"></i>Medicines</a></li>
                                        <?php endif; ?>
                                        <?php if (hasSubModuleAccess($userPermissions, 'pharmacy', 'inventory')): ?>
                                            <li><a class="dropdown-item <?= $activeMenu === 'pharmacy_inventory' ? 'active' : '' ?>" href="<?= $baseUrl ?>/pharmacy/inventory"><i class="fas fa-boxes me-2"></i>Inventory</a></li>
                                        <?php endif; ?>
                                        <?php if (hasSubModuleAccess($userPermissions, 'pharmacy', 'orders')): ?>
                                            <li><a class="dropdown-item <?= $activeMenu === 'pharmacy_orders' ? 'active' : '' ?>" href="<?= $baseUrl ?>/pharmacy/orders"><i class="fas fa-shopping-cart me-2"></i>Orders</a></li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider"></li>
                                    <?php endif; ?>
                                    
                                    <?php if (hasModuleAccess($userPermissions, 'prescriptions')): ?>
                                        <li><a class="dropdown-item <?= $activeMenu === 'prescriptions' ? 'active' : '' ?>" href="<?= $baseUrl ?>/prescriptions"><i class="fas fa-prescription me-2"></i>Prescriptions</a></li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Financial Management - Grouped dropdown for financial functions -->
                        <?php if (hasModuleAccess($userPermissions, 'invoices') || hasModuleAccess($userPermissions, 'payments')): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle <?= ($activeMenu === 'invoices' || $activeMenu === 'payments') ? 'active' : '' ?>" href="javascript:void(0);" id="financialDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-dollar-sign me-1"></i>Financial
                                </a>
                                <ul class="dropdown-menu">
                                    <?php if (hasModuleAccess($userPermissions, 'invoices')): ?>
                                        <li><a class="dropdown-item <?= $activeMenu === 'invoices' ? 'active' : '' ?>" href="<?= $baseUrl ?>/invoices"><i class="fas fa-file-invoice me-2"></i>Invoices</a></li>
                                    <?php endif; ?>
                                    <?php if (hasModuleAccess($userPermissions, 'payments')): ?>
                                        <li><a class="dropdown-item <?= $activeMenu === 'payments' ? 'active' : '' ?>" href="<?= $baseUrl ?>/payments"><i class="fas fa-credit-card me-2"></i>Payments</a></li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Administration - Available to admin only -->
                        <?php if (hasModuleAccess($userPermissions, 'departments') || hasModuleAccess($userPermissions, 'admin')): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle <?= ($activeMenu === 'departments' || $activeMenu === 'admin' || str_starts_with((string) $activeMenu, 'admin_')) ? 'active' : '' ?>" href="javascript:void(0);" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-cogs me-1"></i>Administration
                                </a>
                                <ul class="dropdown-menu">
                                    <?php if (hasModuleAccess($userPermissions, 'departments')): ?>
                                        <li><a class="dropdown-item <?= $activeMenu === 'departments' ? 'active' : '' ?>" href="<?= $baseUrl ?>/departments"><i class="fas fa-building me-2"></i>Departments</a></li>
                                    <?php endif; ?>
                                    <?php if (hasModuleAccess($userPermissions, 'admin')): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header"><i class="fas fa-shield-alt me-2"></i>System Management</h6></li>
                                        <?php if (hasSubModuleAccess($userPermissions, 'admin', 'users')): ?>
                                            <li><a class="dropdown-item <?= $activeMenu === 'admin_users' ? 'active' : '' ?>" href="<?= $baseUrl ?>/users"><i class="fas fa-users-cog me-2"></i>Users</a></li>
                                        <?php endif; ?>
                                        <?php if (hasSubModuleAccess($userPermissions, 'admin', 'settings')): ?>
                                            <li><a class="dropdown-item <?= $activeMenu === 'admin_settings' ? 'active' : '' ?>" href="<?= $baseUrl ?>/settings"><i class="fas fa-cog me-2"></i>System Settings</a></li>
                                        <?php endif; ?>
                                        <?php if (hasSubModuleAccess($userPermissions, 'admin', 'reports')): ?>
                                            <li><a class="dropdown-item <?= $activeMenu === 'admin_reports' ? 'active' : '' ?>" href="<?= $baseUrl ?>/reports"><i class="fas fa-chart-bar me-2"></i>Reports</a></li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if ($isLoggedIn): ?>
                        <!-- Notifications Link -->
                        <li class="nav-item" id="notifications-container">
                            <a class="nav-link" href="<?= $baseUrl ?>/notifications" id="notificationsToggle" title="Notifications" aria-label="Notifications">
                                <i class="fas fa-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" id="notification-count" style="display: none;">
                                    0
                                </span>
                            </a>
                        </li>
                        
                        <!-- Messages Link -->
                        <li class="nav-item" id="messages-container">
                            <a class="nav-link" href="<?= $baseUrl ?>/messages" id="messagesToggle" title="Messages" aria-label="Messages">
                                <i class="fas fa-envelope"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" id="messages-count" style="display: none;">
                                    0
                                </span>
                            </a>
                        </li>
                        
                        <!-- Profile Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="javascript:void(0);" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="User Menu">
                                <i class="fas fa-user-circle"></i>
                                <span class="d-none d-md-inline ms-1"><?= htmlspecialchars($currentUser['firstName'] . ' ' . $currentUser['lastName']) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="<?= $baseUrl ?>/profile">
                                        <i class="fas fa-user"></i>My Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= $baseUrl ?>/profile/edit">
                                        <i class="fas fa-user-edit"></i>Edit Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= $baseUrl ?>/profile/change-password">
                                        <i class="fas fa-key"></i>Change Password
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?= $baseUrl ?>/logout">
                                        <i class="fas fa-sign-out-alt"></i>Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $baseUrl ?>/login">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<!-- Flash Messages -->
<div id="flash-messages-container" class="container">
    <?php if (!empty($flashMessages)): ?>
        <?php foreach ($flashMessages as $flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>