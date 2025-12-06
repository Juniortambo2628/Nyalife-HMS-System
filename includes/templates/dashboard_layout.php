<?php
/**
 * Nyalife HMS - Dashboard Layout Template
 *
 * This file provides a standardized layout template for dashboard pages.
 *
 * Required variables:
 * $pageTitle - The title of the page
 * $roleName - The role of the current user (admin, doctor, patient, etc.)
 * $activeMenu - The active menu item
 * $pageContent - The content of the page (can be a function call or included file)
 * $sidebarMenuItems - Array of sidebar menu items
 */

// Ensure this file is included in a valid context
if (!isset($pageTitle) || !isset($roleName)) {
    die('This file cannot be accessed directly');
}

// Default active menu if not set
$activeMenu ??= 'dashboard';

// Force hide any loader that might block content
if (function_exists('dashboard_force_hide_loader')) {
    echo dashboard_force_hide_loader();
}

// Add classes to body
if (function_exists('add_body_class')) {
    add_body_class('signed-in dashboard-page');
}

// Include header using reusable component
define('NYALIFE_INCLUDED', true);
define('NYALIFE_DASHBOARD_LAYOUT', true);
include_once __DIR__ . '/../components/header.php';

// Include sidebar component
include_once __DIR__ . '/../components/sidebar.php';

// Loader is now injected directly by nyalife-loader-unified.js
?>

<div class="container-fluid main-content-container">
    <!-- Main content -->
    <div class="p-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2 text-primary"><?php echo $pageTitle; ?></h1>

                <?php if (isset($actionButtons) && !empty($actionButtons)): ?>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?php foreach ($actionButtons as $button): ?>
                            <?php
                            $btnId = isset($button['id']) ? 'id="' . $button['id'] . '"' : '';
                            $btnClass = $button['class'] ?? 'btn-primary';
                            $btnIcon = isset($button['icon']) ? '<i class="' . $button['icon'] . ' me-2"></i>' : '';
                            $btnAttributes = $button['attributes'] ?? '';

                            if (isset($button['modal'])) {
                                $btnAttributes .= ' data-bs-toggle="modal" data-bs-target="#' . $button['modal'] . '"';
                            }
                            ?>
                            <button type="button" 
                                    class="btn btn-sm <?php echo $btnClass; ?> me-2" 
                                    <?php echo $btnId; ?> 
                                    <?php echo $btnAttributes; ?>>
                                <?php echo $btnIcon . $button['text']; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Welcome message for dashboard index only -->
            <?php if ($activeMenu === 'dashboard'): ?>
                <div class="alert alert-primary" role="alert">
                    <h4 class="alert-heading">Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>!</h4>
                    <p>Welcome to your dashboard. Here you can manage your tasks and view relevant information.</p>
                </div>
            <?php endif; ?>

        <!-- Page content -->
        <?php
        // Display page content
        if (isset($pageContent)) {
            if (is_callable($pageContent)) {
                call_user_func($pageContent);
            } else {
                echo $pageContent;
            }
        }
?>
    </div>
</div>

<?php
// Include modals if any
if (isset($modals) && !empty($modals)) {
    foreach ($modals as $modal) {
        if (file_exists($modal)) {
            include $modal;
        }
    }
}

// Include footer using reusable component
include_once __DIR__ . '/../components/footer.php';
