/**
 * Edit Appointment Page JavaScript
 */
function initEditAppointmentPage() {
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
                    Components.showNotification('An error occurred while updating the appointment', 'error');
                }
            });
        });
    }
    // Check for doctor availability when date or time changes
    const doctorSelect = document.getElementById('doctor_id');
    const dateInput = document.getElementById('appointment_date');
    const timeInput = document.getElementById('appointment_time');
    
    // Store original values to avoid checking if nothing changed
    let originalDoctorId = doctorSelect ? doctorSelect.value : '';
    let originalDate = dateInput ? dateInput.value : '';
    let originalTime = timeInput ? timeInput.value : '';
    
    // Get appointment ID from URL or hidden field if editing
    const urlParts = window.location.pathname.split('/');
    const appointmentId = urlParts[urlParts.length - 1]; // Assuming URL ends with ID
    
    function checkAvailability() {
        const doctorId = doctorSelect.value;
        const date = dateInput.value;
        const time = timeInput.value;
        
        // Only check if something changed and we have all values
        if ((doctorId !== originalDoctorId || date !== originalDate || time !== originalTime) && 
            doctorId && date && time && typeof Components !== 'undefined') {
            
            // Update originals
            originalDoctorId = doctorId;
            originalDate = date;
            originalTime = time;
            
            fetch(`${baseUrl}/api/check-availability?doctor_id=${doctorId}&date=${date}&time=${time}&exclude_appointment_id=${appointmentId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.available) {
                    alert('The selected doctor is not available at this time. Please choose another time or doctor.');
                }
            })
            .catch(error => {
                console.error('Error checking availability:', error);
            });
        }
    }
    
    if (doctorSelect && dateInput && timeInput) {
        dateInput.addEventListener('change', checkAvailability);
        timeInput.addEventListener('change', checkAvailability);
        doctorSelect.addEventListener('change', checkAvailability);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initEditAppointmentPage);
document.addEventListener('page:loaded', initEditAppointmentPage);

