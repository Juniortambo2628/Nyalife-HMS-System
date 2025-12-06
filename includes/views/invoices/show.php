<?php
/**
 * Nyalife HMS - Invoice Details View
 *
 * View for displaying invoice details.
 */

$pageTitle = 'Invoice Details - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-invoice fa-fw"></i> Invoice Details
        </h1>
        <div>
            <a href="<?= $baseUrl ?>/invoices/print/<?= $invoice['invoice_id'] ?>" 
               class="btn btn-secondary btn-sm" target="_blank">
                <i class="fas fa-print fa-fw"></i> Print Invoice
            </a>
            <a href="<?= $baseUrl ?>/invoices/edit/<?= $invoice['invoice_id'] ?>" 
               class="btn btn-warning btn-sm">
                <i class="fas fa-edit fa-fw"></i> Edit Invoice
            </a>
            <a href="<?= $baseUrl ?>/invoices" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left fa-fw"></i> Back to Invoices
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php include __DIR__ . '/../components/flash_messages.php'; ?>

    <!-- Invoice Details -->
    <div class="row">
        <!-- Invoice Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Invoice Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Invoice Number:</strong></td>
                                    <td><?= htmlspecialchars($invoice['invoice_number']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Date Created:</strong></td>
                                    <td><?= date('F j, Y', strtotime($invoice['created_at'])) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Due Date:</strong></td>
                                    <td><?= date('F j, Y', strtotime($invoice['due_date'])) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <?php
                                        $statusClass = '';
switch ($invoice['status']) {
    case 'pending':
        $statusClass = 'badge badge-warning';
        break;
    case 'paid':
        $statusClass = 'badge badge-success';
        break;
    case 'partially_paid':
        $statusClass = 'badge badge-info';
        break;
    case 'cancelled':
        $statusClass = 'badge badge-danger';
        break;
    case 'overdue':
        $statusClass = 'badge badge-danger';
        break;
}
?>
                                        <span class="<?= $statusClass ?>">
                                            <?= ucfirst(str_replace('_', ' ', $invoice['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Patient:</strong></td>
                                    <td><?= htmlspecialchars($invoice['patient_name']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Doctor:</strong></td>
                                    <td><?= htmlspecialchars($invoice['doctor_name'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Subtotal:</strong></td>
                                    <td><?= number_format($invoice['subtotal'], 2) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tax:</strong></td>
                                    <td><?= number_format($invoice['tax_amount'], 2) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Total:</strong></td>
                                    <td><strong><?= number_format($invoice['total_amount'], 2) ?></strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Invoice Items</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($invoice['items'])): ?>
                                    <?php foreach ($invoice['items'] as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                                            <td><?= htmlspecialchars($item['description']) ?></td>
                                            <td><?= $item['quantity'] ?></td>
                                            <td><?= number_format($item['unit_price'], 2) ?></td>
                                            <td><?= number_format($item['total_price'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No items found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Information</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($invoice['payments'])): ?>
                        <h6>Payment History</h6>
                        <?php foreach ($invoice['payments'] as $payment): ?>
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <span><?= date('M j, Y', strtotime($payment['payment_date'])) ?></span>
                                    <span class="text-success"><?= number_format($payment['amount'], 2) ?></span>
                                </div>
                                <small class="text-muted"><?= ucfirst($payment['payment_method']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No payments recorded</p>
                    <?php endif; ?>

                    <?php if ($invoice['status'] !== 'paid' && $invoice['status'] !== 'cancelled'): ?>
                        <div class="mt-3">
                            <a href="<?= $baseUrl ?>/payments/create?invoice_id=<?= $invoice['invoice_id'] ?>" 
                               class="btn btn-success btn-block">
                                <i class="fas fa-dollar-sign fa-fw"></i> Record Payment
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notes -->
            <?php if (!empty($invoice['notes'])): ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Notes</h6>
                    </div>
                    <div class="card-body">
                        <p><?= nl2br(htmlspecialchars($invoice['notes'])) ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any page-specific functionality
    console.log('Invoice details page loaded');
});
</script> 