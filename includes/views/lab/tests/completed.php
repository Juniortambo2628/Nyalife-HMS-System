<?php
/**
 * Nyalife HMS - Completed Lab Tests View
 */

$pageTitle = 'Completed Lab Tests - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Completed Lab Tests</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= $baseUrl ?? '' ?>/lab-tests/manage" class="btn btn-secondary">
                <i class="fas fa-flask"></i> Manage Samples
            </a>
            <a href="<?= $baseUrl ?? '' ?>/lab-tests/register-sample" class="btn btn-primary">
                <i class="fas fa-plus"></i> Register New Sample
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Search form -->
            <form method="get" action="<?= $baseUrl ?? '' ?>/lab-tests/completed" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search by Patient Name, ID or Sample ID" 
                                value="<?= htmlspecialchars($search ?? '') ?>">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <a href="<?= $baseUrl ?? '' ?>/lab-tests/completed" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <!-- Test results table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Sample ID</th>
                            <th>Patient</th>
                            <th>Test Type</th>
                            <th>Completed Date</th>
                            <th>Completed By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tests)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No completed tests found</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($tests as $test): ?>
                            <tr>
                                <td><?= htmlspecialchars($test['sample_id'] ?? '') ?></td>
                                <td>
                                    <?= htmlspecialchars($test['patient_name'] ?? '') ?>
                                    <div class="small text-muted"><?= htmlspecialchars($test['patient_number'] ?? '') ?></div>
                                </td>
                                <td><?= htmlspecialchars($test['test_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars(date('M d, Y', strtotime($test['completed_at'] ?? 'now'))) ?></td>
                                <td><?= htmlspecialchars($test['completed_by_name'] ?? '') ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= $baseUrl ?? '' ?>/lab/samples/view/<?= htmlspecialchars($test['sample_id'] ?? '') ?>" 
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= $baseUrl ?? '' ?>/lab/samples/results/<?= htmlspecialchars($test['sample_id'] ?? '') ?>" 
                                           class="btn btn-outline-info" title="View Results">
                                            <i class="fas fa-flask"></i>
                                        </a>
                                        <a href="<?= $baseUrl ?? '' ?>/lab/samples/print/<?= htmlspecialchars($test['sample_id'] ?? '') ?>" 
                                           class="btn btn-outline-secondary" title="Print Results" target="_blank" data-no-ajax>
                                            <i class="fas fa-print"></i>
                                        </a>
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