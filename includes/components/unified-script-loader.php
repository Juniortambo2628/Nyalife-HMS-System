<?php
/**
 * Nyalife HMS - Unified Script Loader Component
 * This file contains the optimized script loading using consolidated modules
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

<!-- Set webpack publicPath dynamically before loading bundles -->


<!-- Core Webpack Bundles -->
<script src="<?= AssetHelper::getJs('runtime') ?>"></script>
<script src="<?= AssetHelper::getJs('vendors') ?>"></script>
<script src="<?= AssetHelper::getJs('shared') ?>"></script>
<script src="<?= AssetHelper::getJs('app') ?>"></script>

<?php
// Load Additional Scripts
if (isset($additionalScripts) && is_array($additionalScripts)) {
    foreach ($additionalScripts as $script) {
        $src = $script;
        // If it doesn't start with http or /, assume it's in assets/js/
        if (in_array(preg_match('/^http/', (string) $src), [0, false], true) && in_array(preg_match('/^\//', (string) $src), [0, false], true)) {
            $src = $baseUrl . '/assets/js/' . $script;
        }
        echo '<script src="' . htmlspecialchars((string) $src) . '"></script>' . "\n";
    }
}

// Load Page Specific Scripts
if (isset($pageSpecificScripts) && is_array($pageSpecificScripts)) {
    foreach ($pageSpecificScripts as $script) {
        $src = $script;
        // If it's already a full URL (e.g. from AssetHelper), use it. 
        // Otherwise, if it doesn't start with http or /, assume it's in assets/js/
        if (in_array(preg_match('/^http/', (string) $src), [0, false], true) && in_array(preg_match('/^\//', (string) $src), [0, false], true)) {
            $src = $baseUrl . '/assets/js/' . $script;
        }
        echo '<script src="' . htmlspecialchars((string) $src) . '"></script>' . "\n";
    }
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Create legacy aliases for any code that might still reference old modules
    if (typeof window.NyalifeAPI !== 'undefined') {
        window.AjaxUtils = window.NyalifeAPI;
    }
    
    if (typeof window.NyalifeForms !== 'undefined') {
        window.FormUtils = window.NyalifeForms;
    }
    
    if (typeof window.NyalifeUtils !== 'undefined') {
        window.DateUtils = window.NyalifeUtils;
        window.NyalifeValidation = window.NyalifeUtils;
    }
    
    if (typeof window.NyalifeCoreUI !== 'undefined') {
        window.showAlert = function(type, message, timeout = 5000) {
            return NyalifeCoreUI.showAlert(type, message, { timeout: timeout });
        };
        
        window.showNotification = function(type, message, timeout = 5000) {
            return NyalifeCoreUI.showNotification(type, message, { timeout: timeout });
        };
    }
    
    console.log('Nyalife HMS unified modules loaded successfully');
});
</script>