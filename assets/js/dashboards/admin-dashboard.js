/**
 * Nyalife HMS - Admin Dashboard
 * 
 * Extracted from includes/views/dashboard/admin.php
 * Uses shared utilities to eliminate duplicate code
 */

import { loadDashboardMessages, setupAjaxNavigation } from '@shared/dashboard-utils';
import { createSimpleDataTable } from '@shared/datatable-utils';

/**
 * Initialize admin dashboard
 */
function initAdminDashboard() {
    console.log('Initializing admin dashboard...');
    
    // Initialize DataTables
    initializeDataTables();
    
    // Load dashboard messages
    loadDashboardMessages();
    
    // Setup AJAX navigation
    setupAjaxNavigation();
    
    // Initialize Charts
    initDashboardCharts();
}

/**
 * Initialize dashboard charts
 */
function initDashboardCharts() {
    const appointmentCtx = document.getElementById('appointmentTrendChart');
    const patientCtx = document.getElementById('patientDistributionChart');
    
    if (appointmentCtx) {
        import('@shared/chart-utils').then(({ createLineChart }) => {
            // Mock data - in production this would come from an API
            const data = {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Appointments',
                    data: [12, 19, 15, 25, 22, 10, 5],
                    borderColor: '#20c997',
                    backgroundColor: 'rgba(32, 201, 151, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            };
            
            createLineChart(appointmentCtx, data, {
                plugins: {
                    title: {
                        display: true,
                        text: 'Weekly Appointments'
                    }
                }
            });
        }).catch(err => console.error('Error loading chart utils:', err));
    }
    
    if (patientCtx) {
        import('@shared/chart-utils').then(({ createDoughnutChart }) => {
            // Mock data
            const data = {
                labels: ['Male', 'Female', 'Other'],
                datasets: [{
                    data: [45, 52, 3],
                    backgroundColor: ['#36a2eb', '#ff6384', '#ffcd56'],
                    borderWidth: 0
                }]
            };
            
            createDoughnutChart(patientCtx, data, {
                plugins: {
                    title: {
                        display: true,
                        text: 'Patient Demographics'
                    }
                }
            });
        }).catch(err => console.error('Error loading chart utils:', err));
    }
}

/**
 * Initialize all DataTables on the admin dashboard
 */
function initializeDataTables() {
    if (!$.fn.DataTable) {
        console.warn('DataTables library not loaded');
        return;
    }
    
    // Recent users table
    const usersTable = document.getElementById('recentUsersTable');
    if (usersTable) {
        createSimpleDataTable(usersTable, {
            order: [[3, 'desc']] // Sort by registration date, newest first
        });
    }
    
    // Recent patients table
    const patientsTable = document.getElementById('recentPatientsTable');
    if (patientsTable) {
        createSimpleDataTable(patientsTable, {
            order: [[0, 'asc']] // Sort by name
        });
    }
    
    // Recent appointments table
    const appointmentsTable = document.getElementById('recentAppointmentsTable');
    if (appointmentsTable) {
        createSimpleDataTable(appointmentsTable, {
            order: [[2, 'desc'], [3, 'asc']] // Sort by date desc, then time asc
        });
    }
}

// Initialize on DOM ready and page loaded events
document.addEventListener('DOMContentLoaded', initAdminDashboard);
document.addEventListener('page:loaded', initAdminDashboard);

// Export for potential use by other modules
export { initAdminDashboard };
