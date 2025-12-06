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
$activeMenu = $activeMenu ?? 'dashboard';

// Force hide any loader that might block content
if (function_exists('forceHideLoader')) {
    echo forceHideLoader();
}

// Include header
require_once __DIR__ . '/../header.php';

// Include loader
require_once __DIR__ . '/../loader.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar p-3">
            <div class="d-flex flex-column">
                <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-decoration-none">
                    <span class="fs-5 text-primary"><?php echo ucfirst($roleName); ?> Menu</span>
                </a>
                <hr>
                <ul class="nav nav-pills flex-column mb-auto">
                    <?php foreach ($sidebarMenuItems as $menuItem): ?>
                        <li class="nav-item">
                            <a href="<?php echo $menuItem['url']; ?>" 
                               class="nav-link <?php echo ($activeMenu === $menuItem['id']) ? 'active' : 'text-dark'; ?>">
                                <?php if (isset($menuItem['icon'])): ?>
                                    <i class="<?php echo $menuItem['icon']; ?> me-2"></i>
                                <?php endif; ?>
                                <?php echo $menuItem['text']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2 text-primary"><?php echo $pageTitle; ?></h1>
                
                <?php if (isset($actionButtons) && !empty($actionButtons)): ?>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?php foreach ($actionButtons as $button): ?>
                            <?php
                            $btnId = isset($button['id']) ? 'id="' . $button['id'] . '"' : '';
                            $btnClass = isset($button['class']) ? $button['class'] : 'btn-primary';
                            $btnIcon = isset($button['icon']) ? '<i class="' . $button['icon'] . ' me-2"></i>' : '';
                            $btnAttributes = isset($button['attributes']) ? $button['attributes'] : '';
                            
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

// Include footer
require_once __DIR__ . '/../footer.php';
?> 