<?php
/**
 * Nyalife HMS - Invoice Management Index
 *
 * View for listing and managing invoices.
 */

$pageTitle = 'Invoices - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-invoice fa-fw"></i> Invoices
        </h1>
        <a href="<?= $baseUrl ?>/invoices/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus fa-fw"></i> Create Invoice
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_messages'])): ?>
        <?php foreach ($_SESSION['flash_messages'] as $message): ?>
            <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
        <?php unset($_SESSION['flash_messages']); ?>
    <?php endif; ?>

    <!-- Search and Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search & Filter</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= $baseUrl ?>/invoices" class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                               placeholder="Search invoices...">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="paid" <?= ($_GET['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="partially_paid" <?= ($_GET['status'] ?? '') === 'partially_paid' ? 'selected' : '' ?>>Partially Paid</option>
                            <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            <option value="overdue" <?= ($_GET['status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="date_from">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="date_to">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search fa-fw"></i> Search
                            </button>
                            <a href="<?= $baseUrl ?>/invoices" class="btn btn-secondary">
                                <i class="fas fa-times fa-fw"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Invoices</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" 
                     aria-labelledby="dropdownMenuLink">
                    <div class="dropdown-header">Export Options:</div>
                    <a class="dropdown-item" href="#" onclick="exportToCSV()">
                        <i class="fas fa-file-csv fa-fw"></i> Export to CSV
                    </a>
                    <a class="dropdown-item" href="#" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf fa-fw"></i> Export to PDF
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($invoices)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-file-invoice fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">No invoices found</h5>
                    <p class="text-gray-400">No invoices match your search criteria.</p>
                    <a href="<?= $baseUrl ?>/invoices/create" class="btn btn-primary">
                        <i class="fas fa-plus fa-fw"></i> Create First Invoice
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="invoicesTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($invoice['invoice_number']) ?></strong>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= !empty($invoice['patient_image']) ? $baseUrl . '/uploads/profiles/' . $invoice['patient_image'] : $baseUrl . '/assets/img/placeholders/default-avatar.png' ?>"
                                                 class="rounded-circle mr-2" width="32" height="32" alt="Patient">
                                            <div>
                                                <strong><?= htmlspecialchars($invoice['patient_name']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($invoice['patient_number'] ?? '') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($invoice['invoice_date'])) ?></td>
                                    <td>
                                        <?php
                                        $dueDate = strtotime($invoice['due_date']);
                                $today = time();
                                $isOverdue = $dueDate < $today && $invoice['status'] !== 'paid';
                                ?>
                                        <span class="<?= $isOverdue ? 'text-danger' : '' ?>">
                                            <?= date('M d, Y', $dueDate) ?>
                                            <?php if ($isOverdue): ?>
                                                <i class="fas fa-exclamation-triangle text-danger"></i>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong>$<?= number_format($invoice['total_amount'], 2) ?></strong>
                                    </td>
                                    <td>
                                        $<?= number_format($invoice['paid_amount'] ?? 0, 2) ?>
                                    </td>
                                    <td>
                                        <strong class="<?= ($invoice['balance'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?>">
                                            $<?= number_format($invoice['balance'] ?? 0, 2) ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php
                                $statusClass = '';
                                switch ($invoice['status']) {
                                    case 'paid':
                                        $statusClass = 'badge-success';
                                        break;
                                    case 'pending':
                                        $statusClass = 'badge-warning';
                                        break;
                                    case 'partially_paid':
                                        $statusClass = 'badge-info';
                                        break;
                                    case 'cancelled':
                                        $statusClass = 'badge-secondary';
                                        break;
                                    case 'overdue':
                                        $statusClass = 'badge-danger';
                                        break;
                                    default:
                                        $statusClass = 'badge-secondary';
                                }
                                ?>
                                        <span class="badge <?= $statusClass ?>">
                                            <?= ucfirst(str_replace('_', ' ', $invoice['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= $baseUrl ?>/invoices/show/<?= $invoice['invoice_id'] ?>"
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye fa-fw"></i>
                                            </a>
                                            <a href="<?= $baseUrl ?>/invoices/edit/<?= $invoice['invoice_id'] ?>"
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit fa-fw"></i>
                                            </a>
                                            <a href="<?= $baseUrl ?>/invoices/print/<?= $invoice['invoice_id'] ?>"
                                               class="btn btn-sm btn-secondary" title="Print" target="_blank">
                                                <i class="fas fa-print fa-fw"></i>
                                            </a>
                                            <?php if ($invoice['status'] !== 'paid' && $invoice['status'] !== 'cancelled'): ?>
                                                <a href="<?= $baseUrl ?>/payments/create?invoice_id=<?= $invoice['invoice_id'] ?>"
                                                   class="btn btn-sm btn-success" title="Record Payment">
                                                    <i class="fas fa-dollar-sign fa-fw"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <nav aria-label="Invoice pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>&search=<?= htmlspecialchars($_GET['search'] ?? '') ?>&status=<?= htmlspecialchars($_GET['status'] ?? '') ?>&date_from=<?= htmlspecialchars($_GET['date_from'] ?? '') ?>&date_to=<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($_GET['search'] ?? '') ?>&status=<?= htmlspecialchars($_GET['status'] ?? '') ?>&date_from=<?= htmlspecialchars($_GET['date_from'] ?? '') ?>&date_to=<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>&search=<?= htmlspecialchars($_GET['search'] ?? '') ?>&status=<?= htmlspecialchars($_GET['status'] ?? '') ?>&date_from=<?= htmlspecialchars($_GET['date_from'] ?? '') ?>&date_to=<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                                        Next
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function exportToCSV() {
    window.location.href = '<?= $baseUrl ?>/invoices/export/csv';
}

function exportToPDF() {
    window.location.href = '<?= $baseUrl ?>/invoices/export/pdf';
}

// Initialize DataTable - wait for jQuery and DataTables to load
document.addEventListener('DOMContentLoaded', function() {
    // Wait for jQuery and DataTables to be available
    function initDataTable() {
        if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
            $('#invoicesTable').DataTable({
                "pageLength": 25,
                "order": [[2, "desc"]],
                "language": {
                    "search": "Search invoices:",
                    "lengthMenu": "Show _MENU_ invoices per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ invoices"
                }
            });
        } else {
            // Retry after a short delay if libraries aren't loaded yet
            setTimeout(initDataTable, 100);
        }
    }
    initDataTable();
});
</script>

 