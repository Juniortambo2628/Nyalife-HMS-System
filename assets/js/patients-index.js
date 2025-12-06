/**
 * Patients Index Page JavaScript
 */
function initPatientsPage() {
    // Initialize DataTables if available
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.datatable').DataTable({
            "paging": true,
            "ordering": true,
            "info": true,
            "responsive": true,
            "lengthChange": false,
            "pageLength": 25,
            "language": {
                "search": "Search:",
                "zeroRecords": "No matching records found"
            }
        });
    }
    
    // Handle filter form submission with AJAX
    const filterForm = document.getElementById('filter-form');
    if (filterForm && typeof Components !== 'undefined') {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(filterForm);
            const queryString = new URLSearchParams(formData).toString();
            const url = filterForm.action + (queryString ? '?' + queryString : '');
            
            Components.loadPage(url);
        });
    }
}

// Initialize components when the page is loaded or reloaded via AJAX
document.addEventListener('DOMContentLoaded', initPatientsPage);
document.addEventListener('page:loaded', initPatientsPage);
