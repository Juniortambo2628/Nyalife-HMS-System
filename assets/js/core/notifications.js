/**
 * Nyalife HMS - Core Notifications Module
 * 
 * Provides a unified system for alerts, toasts, and form error messages
 */

const NyalifeCoreUI = (function() {
    // Private variables
    const defaults = {
        alertContainer: '#alertsContainer',
        alertTimeout: 5000,
        toastContainer: '#toastContainer',
        toastTimeout: 3000
    };
    
    /**
     * Create or get the alert container
     * @returns {HTMLElement} The alert container element
     */
    function getAlertContainer() {
        let container = document.querySelector(defaults.alertContainer);
        
        if (!container) {
            container = document.createElement('div');
            container.id = defaults.alertContainer.substring(1);
            container.className = 'position-fixed top-0 start-50 translate-middle-x p-3';
            container.style.zIndex = '1050';
            document.body.appendChild(container);
        }
        
        return container;
    }
    
    /**
     * Create or get the toast container
     * @returns {HTMLElement} The toast container element
     */
    function getToastContainer() {
        let container = document.querySelector(defaults.toastContainer);
        
        if (!container) {
            container = document.createElement('div');
            container.id = defaults.toastContainer.substring(1);
            container.className = 'position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '1050';
            document.body.appendChild(container);
        }
        
        return container;
    }
    
    /**
     * Get the Bootstrap color class for a notification type
     * @param {string} type - Notification type ('success', 'error', 'warning', 'info')
     * @returns {string} Bootstrap color class
     */
    function getColorClass(type) {
        const typeMap = {
            'success': 'success',
            'error': 'danger',
            'warning': 'warning',
            'info': 'info',
            'primary': 'primary',
            'secondary': 'secondary',
            'dark': 'dark',
            'light': 'light'
        };
        
        return typeMap[type] || 'info';
    }
    
    /**
     * Generate a unique ID for notifications
     * @returns {string} Unique ID
     */
    function generateId() {
        return 'notify-' + new Date().getTime() + '-' + Math.random().toString(36).substring(2, 9);
    }
    
    // Public methods
    return {
        /**
         * Show a notification (auto-selects between alert and toast based on type)
         * @param {string} type - Type of notification ('success', 'error', 'warning', 'info')
         * @param {string} message - Notification message
         * @param {Object} options - Additional options
         */
        showNotification: function(type, message, options = {}) {
            // For errors and warnings, use alerts (more prominent)
            if (type === 'error' || type === 'warning') {
                this.showAlert(type, message, options);
            } else {
                // For success and info, use toasts (less intrusive)
                this.showToast(options.title || '', message, type, options);
            }
        },
        
        /**
         * Show an alert notification
         * @param {string} type - Alert type ('success', 'error', 'warning', 'info')
         * @param {string} message - Alert message
         * @param {Object} options - Additional options
         * @returns {HTMLElement} The created alert element
         */
        showAlert: function(type, message, options = {}) {
            const container = getAlertContainer();
            const alertId = generateId();
            const colorClass = getColorClass(type);
            const timeout = options.timeout !== undefined ? options.timeout : defaults.alertTimeout;
            
            // Create alert element
            const alertElement = document.createElement('div');
            alertElement.id = alertId;
            alertElement.className = `alert alert-${colorClass} alert-dismissible fade show`;
            alertElement.role = 'alert';
            
            // Create alert content
            alertElement.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            // Add to container
            container.appendChild(alertElement);
            
            // Initialize Bootstrap alert
            const bsAlert = new bootstrap.Alert(alertElement);
            
            // Auto-dismiss if timeout > 0
            if (timeout > 0) {
                setTimeout(() => {
                    if (alertElement) {
                        bsAlert.close();
                    }
                }, timeout);
            }
            
            // Remove from DOM when hidden
            alertElement.addEventListener('closed.bs.alert', function() {
                this.remove();
            });
            
            return alertElement;
        },
        
        /**
         * Show a toast notification
         * @param {string} title - Toast title
         * @param {string} message - Toast message
         * @param {string} type - Toast type ('success', 'error', 'warning', 'info')
         * @param {Object} options - Additional options
         * @returns {HTMLElement} The created toast element
         */
        showToast: function(title, message, type = 'info', options = {}) {
            const container = getToastContainer();
            const toastId = generateId();
            const colorClass = getColorClass(type);
            const timeout = options.timeout !== undefined ? options.timeout : defaults.toastTimeout;
            
            // Create toast element
            const toastElement = document.createElement('div');
            toastElement.id = toastId;
            toastElement.className = `toast bg-${colorClass} text-white`;
            toastElement.role = 'alert';
            toastElement.setAttribute('aria-live', 'assertive');
            toastElement.setAttribute('aria-atomic', 'true');
            
            // Create toast content
            toastElement.innerHTML = `
                <div class="toast-header bg-${colorClass} text-white">
                    <strong class="me-auto">${title}</strong>
                    <small>${options.time || ''}</small>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            `;
            
            // Add to container
            container.appendChild(toastElement);
            
            // Initialize Bootstrap toast
            const bsToast = new bootstrap.Toast(toastElement, {
                autohide: timeout > 0,
                delay: timeout
            });
            
            // Show the toast
            bsToast.show();
            
            // Remove from DOM when hidden
            toastElement.addEventListener('hidden.bs.toast', function() {
                this.remove();
            });
            
            return toastElement;
        },
        
        /**
         * Show form validation errors
         * @param {HTMLElement|string} form - Form element or form ID
         * @param {Object|Array} errors - Validation errors
         */
        showFormErrors: function(form, errors) {
            // Get form element if string was provided
            if (typeof form === 'string') {
                form = document.getElementById(form);
            }
            
            if (!form) {
                console.error('Form not found');
                return;
            }
            
            // Reset previous errors
            form.querySelectorAll('.is-invalid').forEach(field => {
                field.classList.remove('is-invalid');
            });
            
            form.querySelectorAll('.invalid-feedback').forEach(feedback => {
                feedback.remove();
            });
            
            // Show error summary if needed
            if (errors._summary) {
                this.showAlert('error', errors._summary);
                delete errors._summary;
            }
            
            // Handle array of errors
            if (Array.isArray(errors)) {
                errors.forEach(error => {
                    this.showAlert('error', error);
                });
                return;
            }
            
            // Handle object of errors
            for (const fieldName in errors) {
                const field = form.querySelector(`[name="${fieldName}"]`);
                if (field) {
                    // Add error class
                    field.classList.add('is-invalid');
                    
                    // Add error message
                    const fieldContainer = field.closest('.form-group, .mb-3') || field.parentElement;
                    if (fieldContainer) {
                        const feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        feedback.textContent = errors[fieldName];
                        fieldContainer.appendChild(feedback);
                    }
                }
            }
        },
        
        /**
         * Confirm action with a confirmation dialog
         * @param {string} message - Confirmation message
         * @param {Object} options - Additional options
         * @returns {Promise} Promise resolving to true if confirmed, false otherwise
         */
        confirm: function(message, options = {}) {
            return new Promise(resolve => {
                const defaults = {
                    title: 'Confirm Action',
                    confirmText: 'Yes',
                    cancelText: 'No',
                    type: 'warning'
                };
                
                const settings = {...defaults, ...options};
                const modalId = generateId();
                const colorClass = getColorClass(settings.type);
                
                // Create modal element
                const modalElement = document.createElement('div');
                modalElement.id = modalId;
                modalElement.className = 'modal fade';
                modalElement.tabIndex = -1;
                modalElement.role = 'dialog';
                modalElement.setAttribute('aria-hidden', 'true');
                
                // Create modal content
                modalElement.innerHTML = `
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-${colorClass} text-white">
                                <h5 class="modal-title">${settings.title}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>${message}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${settings.cancelText}</button>
                                <button type="button" class="btn btn-${colorClass}" id="${modalId}-confirm">${settings.confirmText}</button>
                            </div>
                        </div>
                    </div>
                `;
                
                // Add to document
                document.body.appendChild(modalElement);
                
                // Initialize Bootstrap modal
                const modal = new bootstrap.Modal(modalElement);
                
                // Handle confirmation
                document.getElementById(`${modalId}-confirm`).addEventListener('click', () => {
                    modal.hide();
                    resolve(true);
                });
                
                // Handle cancellation
                modalElement.addEventListener('hidden.bs.modal', () => {
                    modalElement.remove();
                    resolve(false);
                });
                
                // Show modal
                modal.show();
            });
        }
    };
})();

// For CommonJS environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NyalifeCoreUI;
}
