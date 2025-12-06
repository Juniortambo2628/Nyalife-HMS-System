<?php
/**
 * Nyalife HMS - Alerts Component
 * 
 * This file contains the alerts component for displaying messages
 * (success, error, warning, etc.)
 */

// Function to display alert from session
function displaySessionAlert() {
    $types = ['success', 'danger', 'warning', 'info'];
    $displayed = false;
    
    foreach ($types as $type) {
        $key = 'alert_' . $type;
        if (isset($_SESSION[$key])) {
            echo '<div class="alert alert-' . $type . ' alert-dismissible fade show mb-4" role="alert">';
            echo htmlspecialchars($_SESSION[$key]);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            
            // Clear the alert
            unset($_SESSION[$key]);
            $displayed = true;
        }
    }
    
    // Backward compatibility with older alerts
    if (!$displayed) {
        // Check for login success
        if (isset($_SESSION['login_success'])) {
            echo '<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">';
            echo htmlspecialchars($_SESSION['login_success']);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            
            // Clear the alert
            unset($_SESSION['login_success']);
        }
        
        // Check for login error
        if (isset($_SESSION['login_error'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">';
            echo htmlspecialchars($_SESSION['login_error']);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            
            // Clear the alert
            unset($_SESSION['login_error']);
        }
        
        // Check for register error
        if (isset($_SESSION['register_error'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">';
            echo htmlspecialchars($_SESSION['register_error']);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            
            // Clear the alert
            unset($_SESSION['register_error']);
        }
    }
}
?>

<!-- Container for alerts -->
<div class="container mt-3">
    <div id="alertsContainer">
        <?php displaySessionAlert(); ?>
    </div>
</div>

<!-- JavaScript for alerts -->
<script>
    /**
     * Show an alert message
     * 
     * @param {string} type The type of alert (success, danger, warning, info)
     * @param {string} message The message to display
     * @param {boolean} autoDismiss Whether to auto dismiss the alert
     * @param {number} dismissTime Time in milliseconds before auto dismissing
     */
    function showAlert(type, message, autoDismiss = true, dismissTime = 5000) {
        // Create the alert element
        const alertElement = document.createElement('div');
        alertElement.className = `alert alert-${type} alert-dismissible fade show`;
        alertElement.setAttribute('role', 'alert');
        
        // Add the message
        alertElement.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Add the alert to the container
        const alertsContainer = document.getElementById('alertsContainer');
        alertsContainer.appendChild(alertElement);
        
        // Auto dismiss if enabled
        if (autoDismiss) {
            setTimeout(() => {
                const bootstrapAlert = new bootstrap.Alert(alertElement);
                bootstrapAlert.close();
            }, dismissTime);
        }
    }
</script> 