/**
 * Nyalife HMS - Pharmacist Dashboard
 * 
 * Extracted from includes/views/dashboard/pharmacist.php
 */

import { loadDashboardMessages, setupAjaxNavigation } from '@shared/dashboard-utils';
import { createSimpleDataTable } from '@shared/datatable-utils';

/**
 * Initialize pharmacist dashboard
 */
function initPharmacistDashboard() {
    console.log('Initializing pharmacist dashboard...');
    
    // Initialize DataTables
    initializeDataTables();
    
    // Load dashboard messages
    loadDashboardMessages();
    
    // Setup AJAX navigation
    setupAjaxNavigation();
}

/**
 * Initialize all DataTables on the pharmacist dashboard
 */
function initializeDataTables() {
    if (!$.fn.DataTable) {
        console.warn('DataTables library not loaded');
        return;
    }
    
    // Pending prescriptions table
    const prescriptionsTable = document.getElementById('pendingPrescriptionsTable');
    if (prescriptionsTable) {
        createSimpleDataTable(prescriptionsTable, {
            order: [[2, 'asc']] // Sort by date/time
        });
    }
    
    // Low stock medications table
    const lowStockTable = document.getElementById('lowStockTable');
    if (lowStockTable) {
        createSimpleDataTable(lowStockTable, {
            order: [[2, 'asc']] // Sort by quantity
        });
    }
}

// Initialize on DOM ready and page loaded events
document.addEventListener('DOMContentLoaded', initPharmacistDashboard);
document.addEventListener('page:loaded', initPharmacistDashboard);

// Export for potential use by other modules
export { initPharmacistDashboard };
