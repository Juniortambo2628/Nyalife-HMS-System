<?php
/**
 * Nyalife HMS - Role-Based Sidebar Component
 *
 * Creates a collapsible sidebar with role-specific actions
 */

// Ensure required variables are set
if (!isset($baseUrl)) {
    $baseUrl = defined('APP_PATH') ? APP_PATH : '';
}

error_log("Sidebar Debug - Base URL: " . $baseUrl);
error_log("Sidebar Debug - Current User: " . print_r($currentUser, true));
error_log("Sidebar Debug - User Role: " . $userRole);

if (!isset($currentUser)) {
    $currentUser = [
        'role' => 'patient',
        'firstName' => 'User',
        'lastName' => 'Name'
    ];
}

//add css for the sidebar by dynamically adding the css file to the head
$cssFile = $baseUrl . '/assets/css/sidebar.css';
$cssFile = '<link rel="stylesheet" href="' . $cssFile . '">';
echo $cssFile;  //remove the css file from default layout so it is not repeated in the head

$userRole = $currentUser['role'] ?? 'patient';

// Define role-based sidebar menu items
$sidebarMenu = [
    'admin' => [
        ['id' => 'dashboard', 'text' => 'Dashboard', 'url' => $baseUrl . '/dashboard', 'icon' => 'fas fa-tachometer-alt', 'badge' => null],
        ['id' => 'appointments', 'text' => 'Appointments', 'url' => $baseUrl . '/appointments', 'icon' => 'fas fa-calendar-alt', 'badge' => 'appointments'],
        ['id' => 'patients', 'text' => 'Patients', 'url' => $baseUrl . '/patients', 'icon' => 'fas fa-users', 'badge' => null],
        ['id' => 'consultations', 'text' => 'Consultations', 'url' => $baseUrl . '/consultations', 'icon' => 'fas fa-stethoscope', 'badge' => 'consultations'],
        ['id' => 'lab-requests', 'text' => 'Lab Requests', 'url' => $baseUrl . '/lab/requests', 'icon' => 'fas fa-flask', 'badge' => null],
        ['id' => 'prescriptions', 'text' => 'Prescriptions', 'url' => $baseUrl . '/prescriptions', 'icon' => 'fas fa-prescription', 'badge' => null],
        ['id' => 'invoices', 'text' => 'Invoices', 'url' => $baseUrl . '/invoices', 'icon' => 'fas fa-file-invoice', 'badge' => null],
        ['id' => 'users', 'text' => 'Users', 'url' => $baseUrl . '/users', 'icon' => 'fas fa-user-cog', 'badge' => null],
        ['id' => 'reports', 'text' => 'Reports', 'url' => $baseUrl . '/reports', 'icon' => 'fas fa-chart-bar', 'badge' => null],
    ],
    'doctor' => [
        ['id' => 'dashboard', 'text' => 'Dashboard', 'url' => $baseUrl . '/dashboard', 'icon' => 'fas fa-tachometer-alt', 'badge' => null],
        ['id' => 'appointments', 'text' => 'My Appointments', 'url' => $baseUrl . '/appointments', 'icon' => 'fas fa-calendar-alt', 'badge' => 'appointments'],
        ['id' => 'consultations', 'text' => 'Consultations', 'url' => $baseUrl . '/consultations', 'icon' => 'fas fa-stethoscope', 'badge' => 'consultations'],
        ['id' => 'patients', 'text' => 'View Patients', 'url' => $baseUrl . '/patients', 'icon' => 'fas fa-users', 'badge' => null],
        ['id' => 'lab-requests', 'text' => 'Lab Requests', 'url' => $baseUrl . '/lab/requests', 'icon' => 'fas fa-flask', 'badge' => null],
        ['id' => 'prescriptions', 'text' => 'Prescriptions', 'url' => $baseUrl . '/prescriptions', 'icon' => 'fas fa-prescription', 'badge' => null],
        ['id' => 'schedule', 'text' => 'My Schedule', 'url' => $baseUrl . '/appointments/calendar', 'icon' => 'fas fa-clock', 'badge' => null],
    ],
    'nurse' => [
        ['id' => 'dashboard', 'text' => 'Dashboard', 'url' => $baseUrl . '/dashboard', 'icon' => 'fas fa-tachometer-alt', 'badge' => null],
        ['id' => 'appointments', 'text' => 'Appointments', 'url' => $baseUrl . '/appointments', 'icon' => 'fas fa-calendar-alt', 'badge' => 'appointments'],
        ['id' => 'consultations', 'text' => 'Consultations', 'url' => $baseUrl . '/consultations', 'icon' => 'fas fa-stethoscope', 'badge' => 'consultations'],
        ['id' => 'patients', 'text' => 'View Patients', 'url' => $baseUrl . '/patients', 'icon' => 'fas fa-users', 'badge' => null],
        ['id' => 'vitals', 'text' => 'Record Vitals', 'url' => $baseUrl . '/vitals', 'icon' => 'fas fa-heartbeat', 'badge' => null],
    ],
    'lab_technician' => [
        ['id' => 'dashboard', 'text' => 'Dashboard', 'url' => $baseUrl . '/dashboard', 'icon' => 'fas fa-tachometer-alt', 'badge' => null],
        ['id' => 'lab-requests', 'text' => 'Lab Requests', 'url' => $baseUrl . '/lab/requests', 'icon' => 'fas fa-flask', 'badge' => null],
        ['id' => 'lab-tests', 'text' => 'Lab Tests', 'url' => $baseUrl . '/lab-tests/manage', 'icon' => 'fas fa-vial', 'badge' => null],
        ['id' => 'samples', 'text' => 'Samples', 'url' => $baseUrl . '/lab/tests', 'icon' => 'fas fa-test-tube', 'badge' => null],
    ],
    'pharmacist' => [
        ['id' => 'dashboard', 'text' => 'Dashboard', 'url' => $baseUrl . '/dashboard', 'icon' => 'fas fa-tachometer-alt', 'badge' => null],
        ['id' => 'prescriptions', 'text' => 'Prescriptions', 'url' => $baseUrl . '/prescriptions', 'icon' => 'fas fa-prescription', 'badge' => null],
        ['id' => 'inventory', 'text' => 'Inventory', 'url' => $baseUrl . '/pharmacy/inventory', 'icon' => 'fas fa-boxes', 'badge' => null],
        ['id' => 'medicines', 'text' => 'Medicines', 'url' => $baseUrl . '/pharmacy/medicines', 'icon' => 'fas fa-pills', 'badge' => null],
    ],
    'patient' => [
        ['id' => 'dashboard', 'text' => 'Dashboard', 'url' => $baseUrl . '/dashboard', 'icon' => 'fas fa-tachometer-alt', 'badge' => null],
        ['id' => 'appointments', 'text' => 'My Appointments', 'url' => $baseUrl . '/appointments', 'icon' => 'fas fa-calendar-alt', 'badge' => 'appointments'],
        ['id' => 'lab-results', 'text' => 'Lab Results', 'url' => $baseUrl . '/lab-results', 'icon' => 'fas fa-flask', 'badge' => null],
        ['id' => 'prescriptions', 'text' => 'My Prescriptions', 'url' => $baseUrl . '/prescriptions', 'icon' => 'fas fa-prescription', 'badge' => null],
        ['id' => 'profile', 'text' => 'My Profile', 'url' => $baseUrl . '/profile', 'icon' => 'fas fa-user', 'badge' => null],
    ],
];

// Get menu items for current user role
$menuItems = $sidebarMenu[$userRole] ?? $sidebarMenu['patient'];
?>

<!-- Sidebar -->
<aside id="nyalifeSidebar" class="nyalife-sidebar">
    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Toggle Sidebar">
        <i class="fas fa-chevron-left"></i>
    </button>
    
    <!-- Sidebar Header with Logo -->
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="<?= $baseUrl ?>/assets/img/logo.png" alt="Nyalife HMS" class="logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="logo-text" style="display: none;">
                <span class="logo-brand">Nyalife</span>
                <span class="logo-subtitle">HMS</span>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Menu -->
    <nav class="sidebar-nav">
        <ul class="sidebar-menu">
            <?php foreach ($menuItems as $item): ?>
            <li class="sidebar-menu-item">
                <a href="<?= htmlspecialchars($item['url']) ?>" class="sidebar-link" data-menu-id="<?= $item['id'] ?>" title="<?= htmlspecialchars($item['text']) ?>">
                    <i class="<?= htmlspecialchars($item['icon']) ?> sidebar-icon"></i>
                    <span class="menu-text"><?= htmlspecialchars($item['text']) ?></span>
                    <?php if ($item['badge'] !== '' && $item['badge'] !== '0'): ?>
                    <span class="menu-badge" id="badge-<?= $item['badge'] ?>" style="display: none;">0</span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>

<!-- Sidebar Overlay (for mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
