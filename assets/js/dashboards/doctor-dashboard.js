/**
 * Nyalife HMS - Doctor Dashboard
 * 
 * Extracted from includes/views/dashboard/doctor.php
 * Uses shared utilities (same pattern as admin dashboard)
 */

import { loadDashboardMessages, setupAjaxNavigation } from '@shared/dashboard-utils';
import { createSimpleDataTable } from '@shared/datatable-utils';

/**
 * Initialize doctor dashboard
 */
function initDoctorDashboard() {
    console.log('Initializing doctor dashboard...');
    
    // Initialize DataTables
    initializeDataTables();
    
    // Load dashboard messages
    loadDashboardMessages();
    
    // Setup AJAX navigation
    setupAjaxNavigation();
}

/**
 * Initialize all DataTables on the doctor dashboard
 */
function initializeDataTables() {
    if (!$.fn.DataTable) {
        console.warn('DataTables library not loaded');
        return;
    }
    
    // Today's appointments table
    const appointmentsTable = document.getElementById('todayAppointmentsTable');
    if (appointmentsTable) {
        createSimpleDataTable(appointmentsTable, {
            order: [[2, 'asc']] // Sort by time ascending
        });
    }
    
    // Recent consultations table
    const consultationsTable = document.getElementById('recentConsultationsTable');
    if (consultationsTable) {
        createSimpleDataTable(consultationsTable, {
            order: [[3, 'desc']] // Sort by date descending
        });
    }
}

// Initialize on DOM ready and page loaded events
document.addEventListener('DOMContentLoaded', initDoctorDashboard);
document.addEventListener('page:loaded', initDoctorDashboard);

// Export for potential use by other modules
export { initDoctorDashboard };
