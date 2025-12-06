<?php
/**
 * Nyalife HMS - Reusable Script Loader Component
 * This file contains the optimized script loading that can be included in layouts
 */

// Check if this file is being accessed directly or via AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// If not AJAX and not included in another file, prevent direct access
if (!$isAjax && !defined('NYALIFE_INCLUDED')) {
    http_response_code(403);
    exit('Direct access to this file is not allowed.');
}

// Variables that should be passed:
// $baseUrl - The base URL of the application
// $additionalScripts - Array of additional script files to load
// $pageSpecificScripts - Array of page-specific script files to load
?>

<!-- Core Scripts - Loaded in optimized order -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?= $baseUrl ?>/assets/js/common/jquery-compat.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Application Core -->
<script src="<?= $baseUrl ?>/assets/js/core/forms.js"></script>
<script src="<?= $baseUrl ?>/assets/js/common/utils.js"></script>
<script src="<?= $baseUrl ?>/assets/js/common/auth-utils.js"></script>
<script src="<?= $baseUrl ?>/assets/js/common/validation.js"></script>
<script src="<?= $baseUrl ?>/assets/js/common/date-utils.js"></script>
<script src="<?= $baseUrl ?>/assets/js/common/modal-utils.js"></script>

<!-- Unified Loader System -->
<script src="<?= $baseUrl ?>/assets/js/nyalife-loader-unified.js"></script>

<!-- Animation Library -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

<!-- Main Application -->
<script src="<?= $baseUrl ?>/assets/js/nyalife.js"></script>
<script src="<?= $baseUrl ?>/assets/js/alerts.js"></script>

<?php if (isset($pageSpecificScripts) && is_array($pageSpecificScripts)): ?>
    <!-- Page Specific Scripts -->
    <?php foreach ($pageSpecificScripts as $script): ?>
        <script src="<?= $baseUrl ?>/assets/js/<?= $script ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Additional Scripts -->
<?php if (isset($additionalScripts) && is_array($additionalScripts)): ?>
    <?php foreach ($additionalScripts as $script): ?>
        <script src="<?= $baseUrl ?>/assets/js/<?= $script ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<script>
    // Initialize AOS only once
    if (typeof AOS !== 'undefined' && !window.AOS_INITIALIZED) {
        AOS.init();
        window.AOS_INITIALIZED = true;
    }
</script> 