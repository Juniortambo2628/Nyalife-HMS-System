/**
 * Nyalife HMS - Nurse Dashboard
 * 
 * Extracted from includes/views/dashboard/nurse.php
 * Uses shared utilities
 */

import { loadDashboardMessages, setupAjaxNavigation } from '@shared/dashboard-utils';
import { createSimpleDataTable } from '@shared/datatable-utils';

/**
 * Initialize nurse dashboard
 */
function initNurseDashboard() {
    console.log('Initializing nurse dashboard...');
    
    // Initialize DataTables
    initializeDataTables();
    
    // Load dashboard messages
    loadDashboardMessages();
    
    // Setup AJAX navigation
    setupAjaxNavigation();
}

/**
 * Initialize all DataTables on the nurse dashboard
 */
function initializeDataTables() {
    if (!$.fn.DataTable) {
        console.warn('DataTables library not loaded');
        return;
    }
    
    // Pending vitals table
    const vitalsTable = document.getElementById('pendingVitalsTable');
    if (vitalsTable) {
        createSimpleDataTable(vitalsTable, {
            order: [[2, 'asc']] // Sort by appointment time
        });
    }
    
    // Today's appointments table
    const appointmentsTable = document.getElementById('todayAppointmentsTable');
    if (appointmentsTable) {
        createSimpleDataTable(appointmentsTable, {
            order: [[2, 'asc']] // Sort by time ascending
        });
    }
}

// Initialize on DOM ready and page loaded events
document.addEventListener('DOMContentLoaded', initNurseDashboard);
document.addEventListener('page:loaded', initNurseDashboard);

// Export for potential use by other modules
export { initNurseDashboard };
