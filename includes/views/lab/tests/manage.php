<?php
/**
 * Nyalife HMS - Lab Test Management View
 */

$pageTitle = 'Lab Test Management - Nyalife HMS';
?>
<div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Manage Lab Samples</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="<?= $baseUrl ?? '' ?>/lab-tests/completed" class="btn btn-secondary">
                    <i class="fas fa-check-circle"></i> Completed Tests
                </a>
                <a href="<?= $baseUrl ?? '' ?>/lab-tests/register-sample" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Register New Sample
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- Search and filter form -->
                <form method="get" action="<?= $baseUrl ?? '' ?>/lab-tests/manage" class="mb-4" id="lab-filter-form">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search by Patient Name, ID or Sample ID" 
                                    value="<?= htmlspecialchars($search ?? '') ?>">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select" id="status-filter">
                                <option value="registered" <?= ($status ?? '') === 'registered' ? 'selected' : '' ?>>Registered</option>
                                <option value="in_progress" <?= ($status ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="pending_results" <?= ($status ?? '') === 'pending_results' ? 'selected' : '' ?>>Pending Results</option>
                                <option value="completed" <?= ($status ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <a href="<?= $baseUrl ?? '' ?>/lab-tests/manage" class="btn btn-outline-secondary w-100" id="reset-filters">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Samples table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="lab-samples-table">
                        <thead>
                            <tr>
                                <th>Sample ID</th>
                                <th>Patient</th>
                                <th>Test Type</th>
                                <th>Sample Type</th>
                                <th>Collection Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($samples)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No samples found</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($samples as $sample): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($sample['sample_id'] ?? '') ?>
                                        <?php if (!empty($sample['urgent']) && $sample['urgent'] == 1): ?>
                                            <span class="badge bg-danger">URGENT</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($sample['patient_name'] ?? '') ?>
                                        <div class="small text-muted"><?= htmlspecialchars($sample['patient_number'] ?? '') ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($sample['test_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars(ucfirst($sample['sample_type'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars(date('M d, Y', strtotime($sample['collected_date'] ?? 'now'))) ?></td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                    $statusText = '';
                                    switch ($sample['status'] ?? '') {
                                        case 'registered':
                                            $statusClass = 'bg-secondary';
                                            $statusText = 'Registered';
                                            break;
                                        case 'in_progress':
                                            $statusClass = 'bg-primary';
                                            $statusText = 'In Progress';
                                            break;
                                        case 'pending_results':
                                            $statusClass = 'bg-warning';
                                            $statusText = 'Pending Results';
                                            break;
                                        case 'completed':
                                            $statusClass = 'bg-success';
                                            $statusText = 'Completed';
                                            break;
                                        default:
                                            $statusClass = 'bg-secondary';
                                            $statusText = 'Unknown';
                                    }
                                    ?>
                                        <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= $baseUrl ?? '' ?>/lab/samples/view/<?= htmlspecialchars($sample['sample_id'] ?? '') ?>" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (($sample['status'] ?? '') !== 'completed'): ?>
                                            <a href="<?= $baseUrl ?? '' ?>/lab/samples/results/<?= htmlspecialchars($sample['sample_id'] ?? '') ?>" 
                                               class="btn btn-outline-success" title="Enter Results">
                                                <i class="fas fa-flask"></i>
                                            </a>
                                            <?php else: ?>
                                            <a href="<?= $baseUrl ?? '' ?>/lab/samples/print/<?= htmlspecialchars($sample['sample_id'] ?? '') ?>" 
                                               class="btn btn-outline-secondary" title="Print Results" target="_blank" data-no-ajax>
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (!empty($pagination) && $pagination['total'] > $pagination['perPage']): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php
                        $totalPages = ceil($pagination['total'] / $pagination['perPage']);
                    $currentPage = $pagination['page'];
                    $url = $pagination['url'];

                    // Previous button
                    if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $url . ($currentPage - 1) ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">&laquo;</span>
                        </li>
                        <?php endif;

// Page numbers
$start = max(1, $currentPage - 2);
$end = min($totalPages, $currentPage + 2);

if ($start > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $url ?>1">1</a>
                        </li>
                        <?php if ($start > 2): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                        <?php endif;
endif;

for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $url . $i ?>"><?= $i ?></a>
                        </li>
                        <?php endfor;

if ($end < $totalPages):
    if ($end < $totalPages - 1): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $url . $totalPages ?>"><?= $totalPages ?></a>
                        </li>
                        <?php endif;

// Next button
if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $url . ($currentPage + 1) ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">&raquo;</span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
</div>

<script>
// Initialize components when the page is loaded or reloaded via AJAX
document.addEventListener('DOMContentLoaded', initLabTestsManagePage);
document.addEventListener('page:loaded', initLabTestsManagePage);

function initLabTestsManagePage() {
    // Initialize DataTable if available
    if ($.fn.DataTable && document.getElementById('lab-samples-table')) {
        const table = document.getElementById('lab-samples-table');
        
        // Destroy existing DataTable instance if it exists
        if ($.fn.DataTable.isDataTable('#lab-samples-table')) {
            $('#lab-samples-table').DataTable().destroy();
        }
        
        // Verify table structure before initializing
        const thead = table.querySelector('thead tr');
        const tbody = table.querySelector('tbody');
        
        if (thead && tbody) {
            const headerCols = thead.querySelectorAll('th').length;
            const firstRow = tbody.querySelector('tr');
            
            // Only initialize if structure is valid
            if (firstRow) {
                const bodyCols = firstRow.querySelectorAll('td').length;
                if (headerCols === bodyCols) {
                    $('#lab-samples-table').DataTable({
                        "paging": false, // We're using our own pagination
                        "ordering": true,
                        "info": false,
                        "searching": false, // We have our own search form
                        "responsive": true,
                        "autoWidth": false,
                        "columnDefs": [
                            { "orderable": false, "targets": 6 } // Disable sorting on Actions column
                        ]
                    });
                } else {
                    console.warn('Column count mismatch: header has', headerCols, 'columns, but body has', bodyCols, 'columns');
                }
            } else {
                // Empty table - initialize with just header
                $('#lab-samples-table').DataTable({
                    "paging": false,
                    "ordering": true,
                    "info": false,
                    "searching": false,
                    "responsive": true,
                    "autoWidth": false
                });
            }
        }
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
    
    // Handle status filter change with AJAX
    const statusFilter = document.getElementById('status-filter');
    if (statusFilter && typeof Components !== 'undefined') {
        statusFilter.addEventListener('change', function() {
            filterForm.dispatchEvent(new Event('submit'));
        });
    }
    
    // Handle reset filters with AJAX
    const resetButton = document.getElementById('reset-filters');
    if (resetButton && typeof Components !== 'undefined') {
        resetButton.addEventListener('click', function(e) {
            e.preventDefault();
            Components.loadPage(resetButton.href);
        });
    }
    
    // Handle pagination links with AJAX
    const paginationLinks = document.querySelectorAll('.pagination .page-link');
    if (paginationLinks.length > 0 && typeof Components !== 'undefined') {
        paginationLinks.forEach(link => {
            if (link.getAttribute('href')) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    Components.loadPage(this.href);
                });
            }
        });
    }
}
</script> 