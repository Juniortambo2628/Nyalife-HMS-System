/**
 * Appointment Details Page JavaScript
 */
function initAppointmentViewPage() {
    // Initialize DataTables if available
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.datatable').DataTable({
            "paging": true,
            "ordering": true,
            "info": true,
            "responsive": true,
            "lengthChange": false,
            "pageLength": 10,
            "language": {
                "search": "Search:",
                "zeroRecords": "No matching records found"
            }
        });
    }
    
    // Handle tab changes
    const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
    if (tabLinks.length > 0) {
        tabLinks.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(event) {
                // Reinitialize DataTables when tab is shown
                const tabId = event.target.getAttribute('data-bs-target').substring(1);
                const tabContent = document.getElementById(tabId);
                if (tabContent && typeof $.fn.DataTable !== 'undefined') {
                    const table = tabContent.querySelector('.datatable');
                    if (table) {
                        $(table).DataTable().columns.adjust();
                    }
                }
            });
        });
    }
    
    // Handle cancel appointment form submission
    const cancelButton = document.getElementById('confirmCancel');
    if (cancelButton) {
        cancelButton.addEventListener('click', function() {
            const cancelForm = document.getElementById('cancelForm');
            if (cancelForm.checkValidity()) {
                submitFormWithAjax(cancelForm);
            } else {
                cancelForm.reportValidity();
            }
        });
    }
    
    // Handle medical history form submission
    const medicalHistoryForm = document.getElementById('medical-history-form');
    if (medicalHistoryForm && typeof Components !== 'undefined') {
        medicalHistoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!medicalHistoryForm.checkValidity()) {
                e.stopPropagation();
                medicalHistoryForm.classList.add('was-validated');
                return;
            }
            
            submitFormWithAjax(medicalHistoryForm);
        });
    }
    
    // Make all links use AJAX navigation
    if (typeof Components !== 'undefined') {
        // Use a more specific selector if possible, or check if we are inside the main content
        const links = document.querySelectorAll('.container-fluid a[href^="' + (window.baseUrl || '') + '"]');
        links.forEach(link => {
            if (!link.hasAttribute('data-no-ajax')) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    Components.loadPage(link.href);
                });
            }
        });
    }
    
    // Helper function to submit forms with AJAX
    function submitFormWithAjax(form) {
        if (typeof Components !== 'undefined') {
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close any open modals
                    const modals = document.querySelectorAll('.modal');
                    modals.forEach(modal => {
                        const modalInstance = bootstrap.Modal.getInstance(modal);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                    });
                    
                    // Reload the current page or redirect
                    if (data.redirect) {
                        Components.loadPage(data.redirect);
                    } else {
                        Components.loadPage(window.location.href);
                    }
                } else {
                    // Display error message
                    alert(data.message || 'An error occurred. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected error occurred. Please try again.');
            });
        } else {
            // Fallback to traditional form submission
            form.submit();
        }
    }
}

// Initialize components when the page is loaded or reloaded via AJAX
document.addEventListener('DOMContentLoaded', initAppointmentViewPage);
document.addEventListener('page:loaded', initAppointmentViewPage);
