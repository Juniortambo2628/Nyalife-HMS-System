/**
 * Appointments Index Page JavaScript
 */
function initAppointmentsPage() {
    // Initialize Select2 dropdowns if available
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Select an option'
        });
    }
    
    // Initialize datepickers if available
    if (typeof $.fn.datepicker !== 'undefined') {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
    }
    
    // Initialize DataTables if available
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.datatable').DataTable({
            "paging": true,
            "ordering": true,
            "info": true,
            "searching": true,
            "responsive": true,
            "lengthChange": false,
            "pageLength": 25,
            "language": {
                "search": "Search:",
                "zeroRecords": "No matching records found",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "No entries to show",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            },
            "dom": '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                   '<"row"<"col-sm-12"tr>>' +
                   '<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
        });
    }
    
    // Handle filter form submission with AJAX
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            if (typeof Components !== 'undefined') {
                e.preventDefault();
                const formData = new FormData(filterForm);
                const queryString = new URLSearchParams(formData).toString();
                const url = filterForm.action + (queryString ? '?' + queryString : '');

                Components.loadPage(url);
            }
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initAppointmentsPage);
document.addEventListener('page:loaded', initAppointmentsPage);

