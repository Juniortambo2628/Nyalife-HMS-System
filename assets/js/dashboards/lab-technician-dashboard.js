/**
 * Nyalife HMS - Lab Technician Dashboard
 * 
 * Extracted from includes/views/dashboard/lab_technician.php
 * Uses shared utilities plus lab-specific functions
 */

import { loadDashboardMessages, setupAjaxNavigation } from '@shared/dashboard-utils';
import { createSimpleDataTable } from '@shared/datatable-utils';
import httpClient from '@common/http';

/**
 * Initialize lab technician dashboard
 */
function initLabTechDashboard() {
    console.log('Initializing lab technician dashboard...');
    
    // Initialize DataTables
    initializeDataTables();
    
    // Load dashboard messages
    loadDashboardMessages();
    
    // Setup AJAX navigation
    setupAjaxNavigation();
    
    // Setup refresh functionality for pending tests
    setupPendingTestsRefresh();
}

/**
 * Initialize all DataTables on the lab technician dashboard
 */
function initializeDataTables() {
    if (!$.fn.DataTable) {
        console.warn('DataTables library not loaded');
        return;
    }
    
    // Pending tests table
    const pendingTestsTable = document.getElementById('pendingTestsTable');
    if (pendingTestsTable) {
        createSimpleDataTable(pendingTestsTable, {
            order: [[3, 'asc']] // Sort by request date
        });
    }
}

/**
 * Setup auto-refresh for pending tests
 */
function setupPendingTestsRefresh() {
    // Refresh every 30 seconds
    setInterval(refreshPendingTests, 30000);
}

/**
 * Refresh pending tests table
 */
async function refreshPendingTests() {
    try {
        const response = await httpClient.get('/api/lab-tests/pending', {
            headers: { 'X-No-Loader': 'true' }
        });
        
        if (response.data && response.data.tests) {
            updatePendingTestsTable(response.data.tests);
        }
    } catch (error) {
        console.error('Error refreshing pending tests:', error);
    }
}

/**
 * Update pending tests table with new data
 * @param {Array} tests - Array of test objects
 */
function updatePendingTestsTable(tests) {
    const tbody = document.querySelector('#pendingTestsTable tbody');
    if (!tbody) return;
    
    if (tests.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">No pending tests</td></tr>';
        return;
    }
    
    tbody.innerHTML = tests.map(test => `
        <tr>
            <td>${test.test_id}</td>
            <td>${test.patient_name}</td>
            <td>${test.test_type}</td>
            <td>${test.request_date}</td>
            <td>
                <a href="${window.baseUrl}/lab-tests/process/${test.test_id}" class="btn btn-sm btn-primary">
                    Process
                </a>
            </td>
        </tr>
    `).join('');
}

// Initialize on DOM ready and page loaded events
document.addEventListener('DOMContentLoaded', initLabTechDashboard);
document.addEventListener('page:loaded', initLabTechDashboard);

// Export for potential use by other modules
export { initLabTechDashboard, refreshPendingTests };
