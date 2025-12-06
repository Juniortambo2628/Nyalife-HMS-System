/**
 * Nyalife HMS - Alerts JavaScript
 * 
 * This file contains the JavaScript functionality for displaying alerts.
 * Updated to use the core notification system.
 */

// Global function to show alerts - uses core notification system if available
function showAlert(type, message, timeout = 5000) {
    // Use the unified notification system if available
    if (window.NyalifeCoreUI && typeof NyalifeCoreUI.showAlert === 'function') {
        return NyalifeCoreUI.showAlert(type, message, { timeout: timeout });
    }
    
    // Fallback to original implementation if core modules aren't loaded
    
    // Get the alerts container or create it if it doesn't exist
    let alertsContainer = document.getElementById('alertsContainer');
    if (!alertsContainer) {
        alertsContainer = document.createElement('div');
        alertsContainer.id = 'alertsContainer';
        alertsContainer.className = 'position-fixed top-0 start-50 translate-middle-x p-3';
        alertsContainer.style.zIndex = '1050';
        document.body.appendChild(alertsContainer);
    }

    // Map alert type to Bootstrap class
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';

    // Create a unique ID for the alert
    const alertId = 'alert-' + new Date().getTime();

    // Create the alert element
    const alertElement = document.createElement('div');
    alertElement.id = alertId;
    alertElement.className = `alert ${alertClass} alert-dismissible fade show`;
    alertElement.role = 'alert';
    alertElement.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    // Add the alert to the container
    alertsContainer.appendChild(alertElement);

    // Initialize the Bootstrap alert
    const bsAlert = new bootstrap.Alert(alertElement);

    // Auto-dismiss the alert after the specified timeout
    if (timeout > 0) {
        setTimeout(() => {
            bsAlert.close();
        }, timeout);
    }

    // Remove the alert from the DOM after it's closed
    alertElement.addEventListener('closed.bs.alert', function() {
        this.remove();
    });

    return alertElement;
}

// Alias showNotification to showAlert for compatibility with both naming conventions
function showNotification(type, message, timeout = 5000) {
    return showAlert(type, message, timeout);
}