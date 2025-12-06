<?php
/**
 * Nyalife HMS - Lab Test Types Index
 */

$pageTitle = 'Lab Test Types - Nyalife HMS';
?>

<div class="container-fluid">
 <div class="row mb-4">
        <div class="col-md-6">
            <h1>Lab Test Types</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= $baseUrl ?? '' ?>/lab/tests/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Test Type
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Search form -->
            <form method="get" action="<?= $baseUrl ?? '' ?>/lab/tests" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search by test name, category..." 
                                value="<?= htmlspecialchars($search ?? '') ?>">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <a href="<?= $baseUrl ?? '' ?>/lab/tests" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <!-- Test types table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Test Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tests)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No test types found</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($tests as $test): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($test['test_name'] ?? '') ?></strong>
                                    <?php if (!empty($test['description'])): ?>
                                        <div class="small text-muted"><?= htmlspecialchars($test['description']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($test['category'] ?? 'General') ?></td>
                                <td><?= htmlspecialchars(number_format($test['price'] ?? 0, 2)) ?></td>
                                <td>
                                    <?php if (($test['is_active'] ?? 1) == 1): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars(date('M d, Y', strtotime($test['created_at'] ?? 'now'))) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= $baseUrl ?? '' ?>/lab/tests/edit/<?= htmlspecialchars($test['test_type_id'] ?? '') ?>" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= $baseUrl ?? '' ?>/lab/tests/view/<?= htmlspecialchars($test['test_type_id'] ?? '') ?>" 
                                           class="btn btn-outline-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                title="Delete"
                                                onclick="deleteTestType(<?= htmlspecialchars($test['test_type_id'] ?? '') ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
                    <?php endif; ?>
                    
                    <?php
                // Page numbers
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);

                for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $url . $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php
                // Next button
                if ($currentPage < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $url . ($currentPage + 1) ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteTestType(testId) {
    if (confirm('Are you sure you want to delete this test type? This action cannot be undone.')) {
        // Create a form to submit the delete request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= $baseUrl ?? '' ?>/lab/tests/delete/' + testId;
        
        // Add CSRF token if available
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>