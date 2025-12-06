/**
 * Nyalife HMS - Guest Appointment Modal Handler
 * This script specifically handles the guest appointment modal functionality
 * and ensures it doesn't block page interactions when not in use.
 */

(function() {
    // Function to fix the guest appointment modal
    function fixGuestAppointmentModal() {
        const guestModal = document.getElementById('guestAppointmentModal');
        
        if (!guestModal) return; // Exit if modal doesn't exist
        
        // Force hide the modal if it's not shown
        if (!guestModal.classList.contains('show')) {
            guestModal.style.display = 'none';
            guestModal.style.visibility = 'hidden';
            guestModal.style.zIndex = '-1';
            guestModal.style.pointerEvents = 'none';
            guestModal.setAttribute('aria-hidden', 'true');
            
            // Remove any lingering backdrops
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                backdrop.remove();
            });
            
            // Reset body styles
            document.body.classList.remove('modal-open');
            document.body.style.paddingRight = '';
            document.body.style.overflow = '';
        } else {
            // Ensure the modal is properly visible if it should be shown
            guestModal.style.display = 'block';
            guestModal.style.visibility = 'visible';
            guestModal.style.zIndex = 'var(--z-modal, 300)';
            guestModal.style.pointerEvents = 'auto';
            guestModal.setAttribute('aria-hidden', 'false');
            
            // Ensure the dialog is visible
            const dialog = guestModal.querySelector('.modal-dialog');
            if (dialog) {
                dialog.style.display = 'block';
                dialog.style.visibility = 'visible';
                dialog.style.opacity = '1';
            }
            
            // Ensure the content is visible
            const content = guestModal.querySelector('.modal-content');
            if (content) {
                content.style.display = 'flex';
                content.style.visibility = 'visible';
                content.style.opacity = '1';
            }
        }
    }
    
    // Function to initialize the guest appointment form
    function initGuestAppointmentForm() {
        const form = document.getElementById('guestAppointmentForm');
        
        if (!form) return; // Exit if form doesn't exist
        
        // Handle form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show spinner
            const spinner = document.getElementById('guestAppointmentSpinner');
            if (spinner) spinner.classList.remove('d-none');
            
            // Get form data
            const formData = new FormData(form);
            
            // Submit form via AJAX
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Hide spinner
                if (spinner) spinner.classList.add('d-none');
                
                // Show alert with response
                const alert = document.getElementById('guestAppointmentAlert');
                if (alert) {
                    alert.textContent = data.message;
                    alert.className = 'alert ' + (data.success ? 'alert-success' : 'alert-danger');
                    alert.style.display = 'block';
                }
                
                // Reset form if successful
                if (data.success) {
                    form.reset();
                    
                    // Close modal after delay
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('guestAppointmentModal'));
                        if (modal) modal.hide();
                    }, 3000);
                }
            })
            .catch(error => {
                // Hide spinner
                if (spinner) spinner.classList.add('d-none');
                
                // Show error alert
                const alert = document.getElementById('guestAppointmentAlert');
                if (alert) {
                    alert.textContent = 'An error occurred. Please try again later.';
                    alert.className = 'alert alert-danger';
                    alert.style.display = 'block';
                }
                
                console.error('Error submitting guest appointment form:', error);
            });
        });
    }
    
    // Add direct click handler for the appointment button
    function setupAppointmentButtonHandlers() {
        const appointmentButtons = document.querySelectorAll('[data-bs-target="#guestAppointmentModal"]');
        appointmentButtons.forEach(button => {
            button.addEventListener('click', function() {
                const guestModal = document.getElementById('guestAppointmentModal');
                if (!guestModal) return;
                
                // Force show the modal properly
                if (window.bootstrap && window.bootstrap.Modal) {
                    const bsModal = new bootstrap.Modal(guestModal);
                    bsModal.show();
                    
                    // Additional fix to ensure visibility after Bootstrap shows it
                    setTimeout(() => {
                        guestModal.style.display = 'block';
                        guestModal.style.visibility = 'visible';
                        guestModal.style.opacity = '1';
                        
                        const dialog = guestModal.querySelector('.modal-dialog');
                        if (dialog) {
                            dialog.style.display = 'block';
                            dialog.style.visibility = 'visible';
                            dialog.style.opacity = '1';
                        }
                        
                        const content = guestModal.querySelector('.modal-content');
                        if (content) {
                            content.style.display = 'flex';
                            content.style.visibility = 'visible';
                            content.style.opacity = '1';
                        }
                    }, 50);
                } else {
                    // Fallback if Bootstrap is not available
                    guestModal.classList.add('show');
                    guestModal.style.display = 'block';
                    guestModal.style.visibility = 'visible';
                    guestModal.style.opacity = '1';
                    
                    // Create backdrop if needed
                    if (document.querySelectorAll('.modal-backdrop').length === 0) {
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        document.body.appendChild(backdrop);
                    }
                    
                    // Fix body
                    document.body.classList.add('modal-open');
                    document.body.style.overflow = 'hidden';
                }
            });
        });
    }
    
    // Run on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function() {
        fixGuestAppointmentModal();
        initGuestAppointmentForm();
        setupAppointmentButtonHandlers();
        
        // Add event listener for when modal is shown
        const guestModal = document.getElementById('guestAppointmentModal');
        if (guestModal) {
            guestModal.addEventListener('shown.bs.modal', function() {
                fixGuestAppointmentModal();
            });
            
            // Add event listener for when modal is hidden
            guestModal.addEventListener('hidden.bs.modal', function() {
                fixGuestAppointmentModal();
            });
        }
    });
    
    // Also run on window load
    window.addEventListener('load', function() {
        fixGuestAppointmentModal();
        setupAppointmentButtonHandlers();
    });
})(); 