/**
 * Lab Requests Index Page JavaScript
 */
function initLabRequestsPage() {
    // Initialize DataTable if available
    if ($.fn.DataTable && document.getElementById('lab-requests-table')) {
        $('#lab-requests-table').DataTable({
            "paging": false, // We're using our own pagination
            "ordering": true,
            "info": false,
            "searching": false, // We have our own search form
            "responsive": true
        });
    }
    
    // Handle filter form submission with AJAX
    const filterForm = document.getElementById('lab-filter-form');
    if (filterForm && typeof Components !== 'undefined') {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Build the URL with query parameters
            const formData = new FormData(filterForm);
            const queryString = new URLSearchParams(formData).toString();
            const url = filterForm.action + '?' + queryString;
            
            // Load the page via AJAX
            Components.loadPage(url);
        });
    }
    
    // Handle pagination links with AJAX
    const paginationLinks = document.querySelectorAll('.pagination .page-link');
    if (paginationLinks.length > 0 && typeof Components !== 'undefined') {
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                Components.loadPage(this.href);
            });
        });
    }
}

// Initialize components when the page is loaded or reloaded via AJAX
document.addEventListener('DOMContentLoaded', initLabRequestsPage);
document.addEventListener('page:loaded', initLabRequestsPage);
