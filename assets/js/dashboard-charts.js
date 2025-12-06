/**
 * Nyalife Hospital Management System
 * Dashboard Charts JavaScript
 * 
 * This file contains chart initialization and data handling for dashboard charts
 * using the Nyalife core modules for API integration and error handling
 */

document.addEventListener('DOMContentLoaded', function() {
    // Wait for the core modules to be ready
    if (typeof NyalifeUtils !== 'undefined') {
        // Check if charts container exists before initializing
        if (NyalifeUtils.exists('#appointmentsChart')) {
            initializeAppointmentsChart();
        }

        // Initialize patients chart if container exists
        if (NyalifeUtils.exists('#patientsChart')) {
            initializePatientsChart();
        }
    } else {
        console.warn('NyalifeUtils not loaded. Charts initialization delayed.');
        // Fallback to traditional DOM checks
        const appointmentsChartContainer = document.getElementById('appointmentsChart');
        const patientsChartContainer = document.getElementById('patientsChart');

        if (appointmentsChartContainer) {
            initializeAppointmentsChart();
        }

        if (patientsChartContainer) {
            initializePatientsChart();
        }
    }
});

/**
 * Initialize appointments chart
 */
function initializeAppointmentsChart() {
    // Get the canvas element
    const ctx = document.getElementById('appointmentsChart').getContext('2d');

    // Set default data in case API call fails
    let chartData = {
        labels: ['Scheduled', 'Completed', 'Cancelled', 'No Show'],
        datasets: [{
            data: [0, 0, 0, 0],
            backgroundColor: ['#0d6efd', '#198754', '#dc3545', '#ffc107']
        }]
    };

    // Create the chart
    const appointmentsChart = new Chart(ctx, {
        type: 'doughnut',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Appointment Status'
                }
            }
        }
    });

    // Fetch data from API using NyalifeAPI if available
    if (typeof NyalifeAPI !== 'undefined' && NyalifeAPI.request) {
        NyalifeAPI.request('/modules/appointments/api/get_appointment_stats_handler.php', {
            method: 'GET',
            showLoader: false, // Don't show the global loader for chart data
            handleError: true,
            onSuccess: function(data) {
                // Update chart with actual data
                appointmentsChart.data.datasets[0].data = [
                    data.stats.scheduled || 0,
                    data.stats.completed || 0,
                    data.stats.cancelled || 0,
                    data.stats.no_show || 0
                ];
                appointmentsChart.update();
            },
            onError: function(error) {
                console.error('Error loading appointment stats:', error);
                // Optionally show error notification
                if (typeof NyalifeCoreUI !== 'undefined') {
                    NyalifeCoreUI.showNotification('error', 'Failed to load appointment statistics.');
                }
            }
        });
    } else {
        // Fallback to traditional fetch API
        fetch(getBaseUrl() + '/modules/appointments/api/get_appointment_stats_handler.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update chart with actual data
                    appointmentsChart.data.datasets[0].data = [
                        data.stats.scheduled || 0,
                        data.stats.completed || 0,
                        data.stats.cancelled || 0,
                        data.stats.no_show || 0
                    ];
                    appointmentsChart.update();
                }
            })
            .catch(error => {
                console.error('Error loading appointment stats:', error);
            });
    }
}

/**
 * Initialize patients chart
 */
function initializePatientsChart() {
    // Get the canvas element
    const ctx = document.getElementById('patientsChart').getContext('2d');

    // Set default data in case API call fails
    let chartData = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'New Patients',
            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            backgroundColor: 'rgba(5, 139, 124, 0.2)',
            borderColor: '#058b7c',
            borderWidth: 1,
            tension: 0.4
        }]
    };

    // Create the chart
    const patientsChart = new Chart(ctx, {
        type: 'line',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'New Patients by Month'
                }
            }
        }
    });

    // Helper function to get base URL
    function getBaseUrl() {
        return window.location.origin + '/Nyalife-HMS-System';
    }
}

// Helper function to get base URL
function getBaseUrl() {
    return window.location.origin + '/Nyalife-HMS-System';
}