/**
 * Create Appointment Page JavaScript
 */
function initCreateAppointmentPage() {
    const baseUrl = window.baseUrl || '';
    
    // Initialize Select2 dropdowns if available
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Select an option'
        });
    }
    
    // Initialize datepickers if available
    if (typeof $.fn.datepicker !== 'undefined') {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
            startDate: new Date()
        });
    }
    
    // Handle form submission with AJAX
    const appointmentForm = document.getElementById('appointment-form');
    if (appointmentForm && typeof Components !== 'undefined') {
        appointmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (!appointmentForm.checkValidity()) {
                e.stopPropagation();
                appointmentForm.classList.add('was-validated');
                return;
            }
            
            // Submit form via AJAX
            const formData = new FormData(appointmentForm);
            
            fetch(appointmentForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to the view page or appointments list
                    if (data.redirect) {
                        Components.loadPage(data.redirect);
                    } else {
                        Components.loadPage(baseUrl + '/appointments');
                    }
                } else {
                    // Show error message
                    if (typeof Components !== 'undefined' && Components.showNotification) {
                        Components.showNotification(data.message || 'An error occurred', 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof Components !== 'undefined' && Components.showNotification) {
                    Components.showNotification('An error occurred while creating the appointment', 'error');
                }
            });
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initCreateAppointmentPage);
document.addEventListener('page:loaded', initCreateAppointmentPage);

