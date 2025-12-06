/**
 * Nyalife HMS - Patient Dashboard
 * 
 * Extracted from includes/views/dashboard/patient.php
 */

import { loadDashboardMessages, setupAjaxNavigation } from '@shared/dashboard-utils';
import { createSimpleDataTable } from '@shared/datatable-utils';

/**
 * Initialize patient dashboard
 */
function initPatientDashboard() {
    console.log('Initializing patient dashboard...');
    
    // Initialize DataTables
    initializeDataTables();
    
    // Load dashboard messages
    loadDashboardMessages();
    
    // Setup AJAX navigation
    setupAjaxNavigation();
}

/**
 * Initialize all DataTables on the patient dashboard
 */
function initializeDataTables() {
    if (!$.fn.DataTable) {
        console.warn('DataTables library not loaded');
        return;
    }
    
    // Appointments history table
    const appointmentsTable = document.getElementById('appointmentsHistoryTable');
    if (appointmentsTable) {
        createSimpleDataTable(appointmentsTable, {
            order: [[1, 'desc']] // Sort by date descending
        });
    }
    
    // Prescriptions table
    const prescriptionsTable = document.getElementById('prescriptionsTable');
    if (prescriptionsTable) {
        createSimpleDataTable(prescriptionsTable, {
            order: [[1, 'desc']] // Sort by date descending
        });
    }
}

// Initialize on DOM ready and page loaded events
document.addEventListener('DOMContentLoaded', initPatientDashboard);
document.addEventListener('page:loaded', initPatientDashboard);

// Export for potential use by other modules
export { initPatientDashboard };
