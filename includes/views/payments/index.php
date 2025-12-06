<?php
/**
 * Nyalife HMS - Payment Management Index
 *
 * View for listing and managing payments.
 */

$pageTitle = 'Payments - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-credit-card fa-fw"></i> Payments
        </h1>
        <a href="<?= $baseUrl ?>/payments/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus fa-fw"></i> Record Payment
        </a>
    </div>

    <!-- Search and Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search & Filter</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= $baseUrl ?>/payments" class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                               placeholder="Search payments...">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select class="form-control" id="payment_method" name="payment_method">
                            <option value="">All Methods</option>
                            <option value="cash" <?= ($_GET['payment_method'] ?? '') === 'cash' ? 'selected' : '' ?>>Cash</option>
                            <option value="card" <?= ($_GET['payment_method'] ?? '') === 'card' ? 'selected' : '' ?>>Card</option>
                            <option value="bank_transfer" <?= ($_GET['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                            <option value="mobile_money" <?= ($_GET['payment_method'] ?? '') === 'mobile_money' ? 'selected' : '' ?>>Mobile Money</option>
                            <option value="insurance" <?= ($_GET['payment_method'] ?? '') === 'insurance' ? 'selected' : '' ?>>Insurance</option>
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
                            <a href="<?= $baseUrl ?>/payments" class="btn btn-secondary">
                                <i class="fas fa-times fa-fw"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Payments</h6>
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
            <?php if (empty($payments)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-credit-card fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">No payments found</h5>
                    <p class="text-gray-400">No payments match your search criteria.</p>
                    <a href="<?= $baseUrl ?>/payments/create" class="btn btn-primary">
                        <i class="fas fa-plus fa-fw"></i> Record First Payment
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="paymentsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Payment #</th>
                                <th>Patient</th>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Received By</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($payment['payment_number']) ?></strong>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= !empty($payment['patient_image']) ? $baseUrl . '/uploads/profiles/' . $payment['patient_image'] : $baseUrl . '/assets/img/placeholders/default-avatar.png' ?>" 
                                                 class="rounded-circle mr-2" width="32" height="32" alt="Patient">
                                            <div>
                                                <strong><?= htmlspecialchars($payment['patient_name']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($payment['patient_number'] ?? '') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="<?= $baseUrl ?>/invoices/show/<?= $payment['invoice_id'] ?>" 
                                           class="text-primary">
                                            <?= htmlspecialchars($payment['invoice_number']) ?>
                                        </a>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                                    <td>
                                        <strong>$<?= number_format($payment['amount'], 2) ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        $methodClass = '';
                                switch ($payment['payment_method']) {
                                    case 'cash':
                                        $methodClass = 'badge-success';
                                        break;
                                    case 'card':
                                        $methodClass = 'badge-primary';
                                        break;
                                    case 'bank_transfer':
                                        $methodClass = 'badge-info';
                                        break;
                                    case 'mobile_money':
                                        $methodClass = 'badge-warning';
                                        break;
                                    case 'insurance':
                                        $methodClass = 'badge-secondary';
                                        break;
                                    default:
                                        $methodClass = 'badge-secondary';
                                }
                                ?>
                                        <span class="badge <?= $methodClass ?>">
                                            <?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($payment['received_by_name'])): ?>
                                            <i class="fas fa-user fa-fw"></i> <?= htmlspecialchars($payment['received_by_name']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not specified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                $statusClass = '';
                                switch ($payment['status']) {
                                    case 'completed':
                                        $statusClass = 'badge-success';
                                        break;
                                    case 'pending':
                                        $statusClass = 'badge-warning';
                                        break;
                                    case 'failed':
                                        $statusClass = 'badge-danger';
                                        break;
                                    case 'refunded':
                                        $statusClass = 'badge-secondary';
                                        break;
                                    default:
                                        $statusClass = 'badge-secondary';
                                }
                                ?>
                                        <span class="badge <?= $statusClass ?>">
                                            <?= ucfirst($payment['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= $baseUrl ?>/payments/show/<?= $payment['payment_id'] ?>" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye fa-fw"></i>
                                            </a>
                                            <a href="<?= $baseUrl ?>/payments/edit/<?= $payment['payment_id'] ?>" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit fa-fw"></i>
                                            </a>
                                            <a href="<?= $baseUrl ?>/payments/print/<?= $payment['payment_id'] ?>" 
                                               class="btn btn-sm btn-secondary" title="Print Receipt" target="_blank">
                                                <i class="fas fa-print fa-fw"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <nav aria-label="Payment pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>&search=<?= htmlspecialchars($_GET['search'] ?? '') ?>&payment_method=<?= htmlspecialchars($_GET['payment_method'] ?? '') ?>&date_from=<?= htmlspecialchars($_GET['date_from'] ?? '') ?>&date_to=<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($_GET['search'] ?? '') ?>&payment_method=<?= htmlspecialchars($_GET['payment_method'] ?? '') ?>&date_from=<?= htmlspecialchars($_GET['date_from'] ?? '') ?>&date_to=<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>&search=<?= htmlspecialchars($_GET['search'] ?? '') ?>&payment_method=<?= htmlspecialchars($_GET['payment_method'] ?? '') ?>&date_from=<?= htmlspecialchars($_GET['date_from'] ?? '') ?>&date_to=<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
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
    window.location.href = '<?= $baseUrl ?>/payments/export/csv';
}

function exportToPDF() {
    window.location.href = '<?= $baseUrl ?>/payments/export/pdf';
}

// Initialize DataTable
$(document).ready(function() {
    $('#paymentsTable').DataTable({
        "pageLength": 25,
        "order": [[3, "desc"]],
        "language": {
            "search": "Search payments:",
            "lengthMenu": "Show _MENU_ payments per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ payments"
        }
    });
});
</script>
 