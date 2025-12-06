<?php
/**
 * Nyalife HMS - Alert Component
 * 
 * This file contains the reusable alert component for the system.
 */

require_once __DIR__ . '/../constants.php';

/**
 * Generate an alert with the given parameters
 * 
 * @param string $message Alert message
 * @param string $type Alert type (success, danger, warning, info)
 * @param bool $dismissible Whether the alert can be dismissed
 * @param bool $autoDismiss Whether to auto dismiss the alert
 * @param int $dismissTime Time in milliseconds before auto dismissing
 * @return string The alert HTML
 */
function generateAlert($message, $type = NOTIFICATION_INFO, $dismissible = true, $autoDismiss = false, $dismissTime = 5000) {
    $alertClass = 'alert alert-' . $type;
    if ($dismissible) {
        $alertClass .= ' alert-dismissible fade show';
    }
    
    $html = '<div class="' . $alertClass . '" role="alert">';
    $html .= $message;
    
    if ($dismissible) {
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    }
    
    $html .= '</div>';
    
    if ($autoDismiss) {
        $html .= "<script>
            setTimeout(function() {
                var alert = document.querySelector('.alert');
                if (alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, $dismissTime);
        </script>";
    }
    
    return $html;
}

/**
 * Display session alerts
 * 
 * @return string The alerts HTML
 */
function displaySessionAlerts() {
    $html = '';
    $types = [
        'success' => NOTIFICATION_SUCCESS,
        'danger' => NOTIFICATION_ERROR,
        'warning' => NOTIFICATION_WARNING,
        'info' => NOTIFICATION_INFO
    ];
    
    foreach ($types as $type => $constant) {
        $key = 'alert_' . $type;
        if (isset($_SESSION[$key])) {
            $html .= generateAlert($_SESSION[$key], $type);
            unset($_SESSION[$key]);
        }
    }
    
    return $html;
}

/**
 * Set a session alert
 * 
 * @param string $message Alert message
 * @param string $type Alert type (success, danger, warning, info)
 */
function setSessionAlert($message, $type = NOTIFICATION_INFO) {
    ensureSession();
    $_SESSION['alert_' . $type] = $message;
}

/**
 * Display an error alert
 * 
 * @param string $message Error message
 * @param bool $dismissible Whether the alert can be dismissed
 * @return string The error alert HTML
 */
if (!function_exists('displayError')) {
    function displayError($message, $dismissible = true) {
        return generateAlert($message, NOTIFICATION_ERROR, $dismissible);
    }
}

/**
 * Display a success alert
 * 
 * @param string $message Success message
 * @param bool $dismissible Whether the alert can be dismissed
 * @return string The success alert HTML
 */
if (!function_exists('displaySuccess')) {
    function displaySuccess($message, $dismissible = true) {
        return generateAlert($message, NOTIFICATION_SUCCESS, $dismissible);
    }
}

/**
 * Display a warning alert
 * 
 * @param string $message Warning message
 * @param bool $dismissible Whether the alert can be dismissed
 * @return string The warning alert HTML
 */
if (!function_exists('displayWarning')) {
    function displayWarning($message, $dismissible = true) {
        return generateAlert($message, NOTIFICATION_WARNING, $dismissible);
    }
}

/**
 * Display an info alert
 * 
 * @param string $message Info message
 * @param bool $dismissible Whether the alert can be dismissed
 * @return string The info alert HTML
 */
if (!function_exists('displayInfo')) {
    function displayInfo($message, $dismissible = true) {
        return generateAlert($message, NOTIFICATION_INFO, $dismissible);
    }
}

/**
 * Display validation errors
 * 
 * @param array $errors Array of error messages
 * @return string The validation errors HTML
 */
function displayValidationErrors($errors) {
    if (empty($errors)) {
        return '';
    }
    
    $message = '<ul class="mb-0">';
    foreach ($errors as $error) {
        $message .= '<li>' . $error . '</li>';
    }
    $message .= '</ul>';
    
    return generateAlert($message, NOTIFICATION_ERROR);
}
?> 