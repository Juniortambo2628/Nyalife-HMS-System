/**
 * Nyalife HMS - Medical History Module Scripts
 * This file contains JavaScript for the Medical History module
 * Updated to use the core modules fully
 */

// Initialize on document ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if core modules are available
    const usingCoreModules = typeof NyalifeUtils !== 'undefined' && typeof NyalifeForms !== 'undefined';
    
    if (usingCoreModules) {
        NyalifeUtils.log('Medical History module initialized with core framework');
    }
    
    // Initialize medical history functionality
    if (typeof initMedicalHistoryForm !== 'function') {
        window.initMedicalHistoryForm = initMedicalHistoryHandler;
    }
    
    // Auto-initialize if element exists
    const medicalHistoryForm = usingCoreModules
        ? NyalifeUtils.select('form[data-medical-history]')
        : document.querySelector('form[data-medical-history]');
    
    if (medicalHistoryForm) {
        initMedicalHistoryHandler(medicalHistoryForm);
    }
    
    // Initialize datatables on any table with the data-table class
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('.data-table').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [
                [5, 10, 25, 50, -1],
                [5, 10, 25, 50, "All"]
            ],
            order: [
                [0, 'desc']
            ]
        });
    }

    // Handle tab loading for medical history
    if (usingCoreModules) {
        NyalifeUtils.selectAll('#historyTabs a[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function() {
                // Clean up chart canvas issues when tab is shown
                setTimeout(function() {
                    window.dispatchEvent(new Event('resize'));
                }, 10);
            });
        });
    } else if (typeof $ !== 'undefined') {
        $('#historyTabs a').on('shown.bs.tab', function() {
            // Clean up chart canvas issues when tab is shown
            setTimeout(function() {
                window.dispatchEvent(new Event('resize'));
            }, 10);
        });
    }
    
    // Register date utilities if core utils not available
    if (!usingCoreModules) {
        // Helper function to format dates
        window.formatDate = function(dateString) {
            return moment(dateString).format('MMMM D, YYYY');
        };

        // Helper function to format date and time
        window.formatDateTime = function(dateString) {
            return moment(dateString).format('MMMM D, YYYY [at] h:mm A');
        };

        // Helper to get age from date of birth
        window.calculateAge = function(dateOfBirth) {
            return moment().diff(moment(dateOfBirth), 'years');
        };

        // Helper to get badge classes based on status
        window.getStatusClass = function(status) {
            switch (status.toLowerCase()) {
                case 'open':
                case 'pending':
                    return 'warning';
                case 'in progress':
                case 'processing':
                    return 'primary';
                case 'completed':
                case 'closed':
                    return 'success';
                case 'cancelled':
                    return 'danger';
                case 'referred':
                    return 'info';
                default:
                    return 'secondary';
            }
        };
    }

    // Handle modal for viewing medical records
    $('body').on('click', '.view-record-btn', function(e) {
        e.preventDefault();
        const recordId = $(this).data('id');
        const recordType = $(this).data('type');

        // Show loader
        if (window.NyalifeLoader) {
            NyalifeLoader.show('Loading record details...');
        } else {
            Nyalife.Loader.show('Loading record details...');
        }

        // Load modal content based on record type
        switch (recordType) {
            case 'consultation':
                loadConsultationDetails(recordId);
                break;
            case 'lab':
                loadLabDetails(recordId);
                break;
            case 'prescription':
                loadPrescriptionDetails(recordId);
                break;
            default:
                if (window.NyalifeLoader) {
                    NyalifeLoader.hide();
                } else {
                    Nyalife.Loader.hide();
                }
                console.error('Unknown record type:', recordType);
        }
    });

    // Clean up modal when closed
    $('body').on('hidden.bs.modal', '.modal', function() {
        // Reset tab selection
        $(this).find('.nav-tabs .nav-link:first').tab('show');
    });
});

// Extend the Nyalife namespace if it exists
if (typeof Nyalife === 'undefined') {
    window.Nyalife = {};
}

/**
 * Initialize medical history form handler
 * @param {HTMLElement|string} form - The form element or form ID
 */
function initMedicalHistoryHandler(form) {
    // Get form element if string was provided
    if (typeof form === 'string') {
        form = document.getElementById(form);
    }
    
    if (!form) return;
    
    // If NyalifeForms is available, use it for form handling
    if (window.NyalifeForms) {
        NyalifeForms.initForm(form, {
            submitViaAjax: true,
            resetAfterSubmit: false,
            loaderMessage: 'Saving medical history...',
            onSuccess: function(response) {
                // Show success message
                if (window.NyalifeCoreUI) {
                    NyalifeCoreUI.showNotification('success', response.message || 'Medical history saved successfully');
                }
                
                // Refresh page or relevant section if needed
                if (response.redirect) {
                    setTimeout(() => window.location.href = response.redirect, 1000);
                } else if (response.refresh) {
                    setTimeout(() => window.location.reload(), 1000);
                } else if (response.section) {
                    // Refresh just a section
                    Nyalife.MedicalHistory.refreshCard(response.section, response.url, response.patientId);
                }
            },
            onError: function(error) {
                // Show error message
                if (window.NyalifeCoreUI) {
                    NyalifeCoreUI.showNotification('error', error.message || 'Failed to save medical history');
                } else {
                    Nyalife.MedicalHistory.handleError(error.message || 'Failed to save medical history');
                }
            }
        });
    } else {
        // Legacy behavior - handle form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loader
            if (window.NyalifeLoader) {
                NyalifeLoader.show('Saving medical history...');
            } else if (Nyalife.loader) {
                Nyalife.loader.show('Saving medical history...');
            }
            
            // Submit form using fetch
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
                // Hide loader
                if (window.NyalifeLoader) {
                    NyalifeLoader.hide();
                } else if (Nyalife.loader) {
                    Nyalife.loader.hide();
                }
                
                if (data.success) {
                    // Show success message
                    Nyalife.MedicalHistory.handleSuccess(data.message || 'Medical history saved successfully');
                    
                    // Handle redirects or refreshes
                    if (data.redirect) {
                        setTimeout(() => window.location.href = data.redirect, 1000);
                    } else if (data.refresh) {
                        setTimeout(() => window.location.reload(), 1000);
                    }
                } else {
                    // Show error message
                    Nyalife.MedicalHistory.handleError(data.message || 'Failed to save medical history');
                }
            })
            .catch(error => {
                // Hide loader
                if (window.NyalifeLoader) {
                    NyalifeLoader.hide();
                } else if (Nyalife.loader) {
                    Nyalife.loader.hide();
                }
                
                // Show error message
                Nyalife.MedicalHistory.handleError('An error occurred. Please try again.');
                console.error('Form submission error:', error);
            });
        });
    }
}

// Medical History module namespace
Nyalife.MedicalHistory = {
    // Function to handle error display
    handleError: function(message) {
        if (window.NyalifeCoreUI) {
            NyalifeCoreUI.showNotification('error', message);
        } else if (Nyalife.Notifications) {
            Nyalife.Notifications.error(message);
        } else {
            alert('Error: ' + message);
        }
    },
    
    // Function to handle success messages
    handleSuccess: function(message) {
        if (window.NyalifeCoreUI) {
            NyalifeCoreUI.showNotification('success', message);
        } else if (Nyalife.Notifications) {
            Nyalife.Notifications.success(message);
        } else if (typeof showAlert === 'function') {
            showAlert('success', message);
        } else {
            alert('Success: ' + message);
        }
    },

    // Function to refresh a data card
    refreshCard: function(containerId, url, patientId) {
        const container = $('#' + containerId);

        if (container.length === 0) {
            return;
        }

        // Add loading class
        container.addClass('loading-container');
        container.html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading data...</p>
            </div>
        `);

        // Load content
        $.ajax({
            url: url,
            type: 'GET',
            data: { patient_id: patientId },
            success: function(response) {
                container.html(response).removeClass('loading-container');
            },
            error: function() {
                container.html('<div class="alert alert-danger">Error loading data. Please try again.</div>')
                    .removeClass('loading-container');
            }
        });
    }
};