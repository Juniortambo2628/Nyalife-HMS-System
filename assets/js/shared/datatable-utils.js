/**
 * Nyalife HMS - DataTable Utilities
 * 
 * Standardized DataTable configuration and initialization
 */

// Import DataTables and Bootstrap 5 integration
import 'datatables.net';
import 'datatables.net-bs5';

/**
 * Create a DataTable with standard configuration
 * @param {string|HTMLElement} selector - Table selector or element
 * @param {Object} customOptions - Custom options to merge with defaults
 * @returns {DataTable} DataTable instance
 */
export function createDataTable(selector, customOptions = {}) {
    if (!$().DataTable) {
        console.error('DataTables library not loaded');
        return null;
    }
    
    const element = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!element) {
        console.error('Table element not found:', selector);
        return null;
    }
    
    // Default configuration
    const defaultOptions = {
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        language: {
            search: '_INPUT_',
            searchPlaceholder: 'Search...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'No entries to show',
            infoFiltered: '(filtered from _MAX_ total entries)',
            zeroRecords: 'No matching records found',
            emptyTable:'No data available in table',
            paginate: {
                first: '«',
                previous: '‹',
                next: '›',
                last: '»'
            }
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
    };
    
    // Merge custom options with defaults
    const options = { ...defaultOptions, ...customOptions };
    
    // Initialize DataTable
    return $(element).DataTable(options);
}

/**
 * Create a simple DataTable (no pagination, search, or info)
 * @param {string|HTMLElement} selector - Table selector or element
 * @param {Object} customOptions - Custom options
 * @returns {DataTable} DataTable instance
 */
export function createSimpleDataTable(selector, customOptions = {}) {
    const simpleOptions = {
        paging: false,
        searching: false,
        info: false,
        ...customOptions
    };
    
    return createDataTable(selector, simpleOptions);
}

/**
 * Destroy a DataTable instance
 * @param {string|HTMLElement} selector - Table selector or element
 */
export function destroyDataTable(selector) {
    const element = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!element) return;
    
    if ($.fn.DataTable.isDataTable(element)) {
        $(element).DataTable().destroy();
    }
}

/**
 * Reload DataTable data
 * @param {string|HTMLElement} selector - Table selector or element
 * @param {boolean} resetPaging - Whether to reset to first page
 */
export function reloadDataTable(selector, resetPaging = false) {
    const element = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!element) return;
    
    if ($.fn.DataTable.isDataTable(element)) {
        $(element).DataTable().ajax.reload(null, resetPaging);
    }
}

/**
 * Export DataTable to CSV
 * @param {string|HTMLElement} selector - Table selector or element
 * @param {string} filename - Output filename
 */
export function exportTableToCSV(selector, filename = 'export.csv') {
    const element = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!element) return;
    
    let csv = [];
    const rows = element.querySelectorAll('tr');
    
    for (let row of rows) {
        let rowData = [];
        const cols = row.querySelectorAll('td, th');
        
        for (let col of cols) {
            // Get text and escape quotes
            let data = col.innerText.replace(/"/g, '""');
            rowData.push(`"${data}"`);
        }
        
        csv.push(rowData.join(','));
    }
    
    // Download CSV
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.click();
    window.URL.revokeObjectURL(url);
}

// Make utilities available globally
if (typeof window !== 'undefined') {
    window.DataTableUtils = {
        createDataTable,
        createSimpleDataTable,
        destroyDataTable,
        reloadDataTable,
        exportTableToCSV
    };
}

export default {
    createDataTable,
    createSimpleDataTable,
    destroyDataTable,
    reloadDataTable,
    exportTableToCSV
};
