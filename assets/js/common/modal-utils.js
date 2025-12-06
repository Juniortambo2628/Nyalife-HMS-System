/**
 * Nyalife HMS - Modal Utility Functions
 * 
 * This file contains common JavaScript functions for working with Bootstrap modals
 * Updated to integrate with NyalifeCoreUI while maintaining backward compatibility
 */

// Check if core modules are available
const usingCoreModules = typeof NyalifeCoreUI !== 'undefined' && typeof NyalifeCoreUI.modal === 'function';
if (!usingCoreModules) {
    console.log('ModalUtils initialized in legacy mode');
}

// Modal management namespace
const ModalUtils = {
    /**
     * Initialize a modal
     * @param {string} modalId - The ID of the modal
     * @param {object} options - Configuration options
     * @returns {Bootstrap.Modal} The modal instance
     */
    initModal: function(modalId, options = {}) {
        const modalElement = document.getElementById(modalId);
        if (!modalElement) {
            console.error(`Modal with ID "${modalId}" not found`);
            return null;
        }

        // Default options
        const defaultOptions = {
            backdrop: 'static', // static, true, false
            keyboard: true,
            focus: true
        };

        // Merge options
        const mergedOptions = {...defaultOptions, ...options };

        // Initialize Bootstrap modal
        return new bootstrap.Modal(modalElement, mergedOptions);
    },

    /**
     * Open a modal
     * @param {string} modalId - The ID of the modal
     * @param {object} options - Configuration options
     */
    openModal: function(modalId, options = {}) {
        const modal = this.initModal(modalId, options);
        if (modal) {
            modal.show();
        }
    },

    /**
     * Close a modal
     * @param {string} modalId - The ID of the modal
     */
    closeModal: function(modalId) {
        const modalElement = document.getElementById(modalId);
        if (!modalElement) {
            console.error(`Modal with ID "${modalId}" not found`);
            return;
        }

        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }
    },

    /**
     * Reset form inside a modal
     * @param {string} modalId - The ID of the modal
     */
    resetModalForm: function(modalId) {
        const modalElement = document.getElementById(modalId);
        if (!modalElement) {
            console.error(`Modal with ID "${modalId}" not found`);
            return;
        }

        const form = modalElement.querySelector('form');
        if (form) {
            form.reset();
        }
    },

    /**
     * Fill form fields in a modal
     * @param {string} modalId - The ID of the modal
     * @param {object} data - Data to fill the form with
     */
    fillModalForm: function(modalId, data) {
        const modalElement = document.getElementById(modalId);
        if (!modalElement) {
            console.error(`Modal with ID "${modalId}" not found`);
            return;
        }

        // Loop through all form elements
        const formElements = modalElement.querySelectorAll('input, select, textarea');
        formElements.forEach(element => {
            const name = element.name || element.id;

            if (name && data.hasOwnProperty(name)) {
                // Set value based on element type
                if (element.type === 'checkbox') {
                    element.checked = Boolean(data[name]);
                } else if (element.type === 'radio') {
                    element.checked = (element.value === data[name]);
                } else if (element.tagName === 'SELECT' && element.multiple) {
                    // For multi-select
                    if (Array.isArray(data[name])) {
                        Array.from(element.options).forEach(option => {
                            option.selected = data[name].includes(option.value);
                        });
                    }
                } else {
                    element.value = data[name];
                }

                // Trigger change event for any elements that need it
                if (element.tagName === 'SELECT') {
                    element.dispatchEvent(new Event('change'));
                }
            }
        });
    },

    /**
     * Set up a confirmation modal
     * @param {string} modalId - The ID of the modal
     * @param {function} onConfirm - Function to call when confirmed
     */
    setupConfirmModal: function(modalId, onConfirm) {
        const modalElement = document.getElementById(modalId);
        if (!modalElement) {
            console.error(`Modal with ID "${modalId}" not found`);
            return;
        }

        const confirmButton = modalElement.querySelector('[id^="confirm"]');
        if (confirmButton) {
            // Remove previous listeners if any
            const newConfirmButton = confirmButton.cloneNode(true);
            confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);

            // Add new listener
            newConfirmButton.addEventListener('click', function() {
                if (typeof onConfirm === 'function') {
                    onConfirm();
                }
                bootstrap.Modal.getInstance(modalElement).hide();
            });
        }
    },

    /**
     * Creates and shows a dynamic confirmation modal
     * @param {string} title - Modal title
     * @param {string} message - Confirmation message
     * @param {function} onConfirm - Function to call when confirmed
     * @param {object} options - Additional options
     */
    confirm: function(title, message, onConfirm, options = {}) {
        // Default options
        const defaultOptions = {
            confirmText: 'Confirm',
            cancelText: 'Cancel',
            confirmClass: 'btn-danger',
            cancelClass: 'btn-secondary',
            size: 'sm',
            centered: true
        };

        // Merge options
        const mergedOptions = {...defaultOptions, ...options };

        // Create a unique ID for the modal
        const modalId = 'dynamicConfirmModal' + Date.now();

        // Create modal HTML
        const modalHtml = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog${mergedOptions.size ? ' modal-' + mergedOptions.size : ''}${mergedOptions.centered ? ' modal-dialog-centered' : ''}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn ${mergedOptions.cancelClass}" data-bs-dismiss="modal">${mergedOptions.cancelText}</button>
                        <button type="button" id="confirmAction" class="btn ${mergedOptions.confirmClass}">${mergedOptions.confirmText}</button>
                    </div>
                </div>
            </div>
        </div>
        `;

        // Append modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Get modal element
        const modalElement = document.getElementById(modalId);

        // Initialize Bootstrap modal
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });

        // Set up confirm button
        const confirmButton = modalElement.querySelector('#confirmAction');
        confirmButton.addEventListener('click', function() {
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
            modal.hide();
        });

        // Set up on hidden event to remove the modal from DOM
        modalElement.addEventListener('hidden.bs.modal', function() {
            modalElement.remove();
        });

        // Show the modal
        modal.show();

        return modal;
    },

    /**
     * Creates and shows a toast notification
     * @param {string} title - Toast title
     * @param {string} message - Toast message
     * @param {string} type - Toast type (success, error, warning, info)
     * @param {object} options - Additional options
     */
    showToast: function(title, message, type = 'info', options = {}) {
        // Default options
        const defaultOptions = {
            duration: 3000,
            position: 'top-right'
        };

        // Merge options
        const mergedOptions = {...defaultOptions, ...options };

        // Create a unique ID for the toast
        const toastId = 'toast' + Date.now();

        // Determine background class based on type
        let bgClass = 'bg-info';
        let icon = '<i class="fas fa-info-circle"></i>';

        switch (type) {
            case 'success':
                bgClass = 'bg-success';
                icon = '<i class="fas fa-check-circle"></i>';
                break;
            case 'error':
                bgClass = 'bg-danger';
                icon = '<i class="fas fa-exclamation-circle"></i>';
                break;
            case 'warning':
                bgClass = 'bg-warning';
                icon = '<i class="fas fa-exclamation-triangle"></i>';
                break;
        }

        // Create toast HTML
        const toastHtml = `
        <div class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true" id="${toastId}">
            <div class="d-flex">
                <div class="toast-body">
                    ${icon} <strong>${title}</strong>: ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        `;

        // Check if toast container exists, if not create it
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = `toast-container position-fixed ${mergedOptions.position}`;

            // Set position based on position option
            switch (mergedOptions.position) {
                case 'top-right':
                    toastContainer.style.top = '1rem';
                    toastContainer.style.right = '1rem';
                    break;
                case 'top-left':
                    toastContainer.style.top = '1rem';
                    toastContainer.style.left = '1rem';
                    break;
                case 'bottom-right':
                    toastContainer.style.bottom = '1rem';
                    toastContainer.style.right = '1rem';
                    break;
                case 'bottom-left':
                    toastContainer.style.bottom = '1rem';
                    toastContainer.style.left = '1rem';
                    break;
                case 'top-center':
                    toastContainer.style.top = '1rem';
                    toastContainer.style.left = '50%';
                    toastContainer.style.transform = 'translateX(-50%)';
                    break;
                case 'bottom-center':
                    toastContainer.style.bottom = '1rem';
                    toastContainer.style.left = '50%';
                    toastContainer.style.transform = 'translateX(-50%)';
                    break;
            }

            document.body.appendChild(toastContainer);
        }

        // Append toast to container
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);

        // Get toast element
        const toastElement = document.getElementById(toastId);

        // Initialize Bootstrap toast
        const toast = new bootstrap.Toast(toastElement, {
            delay: mergedOptions.duration
        });

        // Show the toast
        toast.show();

        // Return toast instance
        return toast;
    }
};

// Export for ES modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ModalUtils;
}