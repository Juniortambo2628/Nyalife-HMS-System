import Chart from 'chart.js/auto';
import 'chartjs-adapter-date-fns';

// Default configuration
Chart.defaults.font.family = "'Inter', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', sans-serif";
Chart.defaults.color = '#6c757d';
Chart.defaults.scale.grid.color = '#e9ecef';

/**
 * Create a line chart
 * @param {HTMLCanvasElement} canvas - The canvas element
 * @param {Object} data - Chart data
 * @param {Object} options - Chart options
 * @returns {Chart} The chart instance
 */
export function createLineChart(canvas, data, options = {}) {
    return new Chart(canvas, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            },
            ...options
        }
    });
}

/**
 * Create a bar chart
 * @param {HTMLCanvasElement} canvas - The canvas element
 * @param {Object} data - Chart data
 * @param {Object} options - Chart options
 * @returns {Chart} The chart instance
 */
export function createBarChart(canvas, data, options = {}) {
    return new Chart(canvas, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            ...options
        }
    });
}

/**
 * Create a doughnut chart
 * @param {HTMLCanvasElement} canvas - The canvas element
 * @param {Object} data - Chart data
 * @param {Object} options - Chart options
 * @returns {Chart} The chart instance
 */
export function createDoughnutChart(canvas, data, options = {}) {
    return new Chart(canvas, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                }
            },
            ...options
        }
    });
}
