<?php
/**
 * Nyalife HMS - Payment Details View
 *
 * View for displaying payment details.
 */

$pageTitle = 'Payment Details - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-dollar-sign fa-fw"></i> Payment Details
        </h1>
        <div>
            <a href="<?= $baseUrl ?>/payments/print/<?= $payment['payment_id'] ?>" 
               class="btn btn-secondary btn-sm" target="_blank">
                <i class="fas fa-print fa-fw"></i> Print Receipt
            </a>
            <a href="<?= $baseUrl ?>/payments/edit/<?= $payment['payment_id'] ?>" 
               class="btn btn-warning btn-sm">
                <i class="fas fa-edit fa-fw"></i> Edit Payment
            </a>
            <a href="<?= $baseUrl ?>/payments" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left fa-fw"></i> Back to Payments
            </a>
        </div>
    </div>

    <!-- Payment Details -->
    <div class="row">
        <!-- Payment Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Payment ID:</strong></td>
                                    <td><?= htmlspecialchars($payment['payment_id']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Date:</strong></td>
                                    <td><?= date('F j, Y', strtotime($payment['payment_date'])) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Method:</strong></td>
                                    <td><?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <?php
                                        $statusClass = '';
switch ($payment['status']) {
    case 'completed':
        $statusClass = 'badge badge-success';
        break;
    case 'pending':
        $statusClass = 'badge badge-warning';
        break;
    case 'failed':
        $statusClass = 'badge badge-danger';
        break;
    case 'refunded':
        $statusClass = 'badge badge-info';
        break;
}
?>
                                        <span class="<?= $statusClass ?>">
                                            <?= ucfirst($payment['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Amount:</strong></td>
                                    <td><strong class="text-success"><?= number_format($payment['amount'], 2) ?></strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Reference Number:</strong></td>
                                    <td><?= htmlspecialchars($payment['reference_number'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Transaction ID:</strong></td>
                                    <td><?= htmlspecialchars($payment['transaction_id'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Recorded By:</strong></td>
                                    <td><?= htmlspecialchars($payment['recorded_by_name']) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Information -->
            <?php if (!empty($payment['invoice'])): ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Related Invoice</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Invoice Number:</strong></td>
                                        <td>
                                            <a href="<?= $baseUrl ?>/invoices/show/<?= $payment['invoice']['invoice_id'] ?>">
                                                <?= htmlspecialchars($payment['invoice']['invoice_number']) ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Patient:</strong></td>
                                        <td><?= htmlspecialchars($payment['invoice']['patient_name']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Invoice Date:</strong></td>
                                        <td><?= date('F j, Y', strtotime($payment['invoice']['created_at'])) ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Invoice Total:</strong></td>
                                        <td><?= number_format($payment['invoice']['total_amount'], 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Amount Paid:</strong></td>
                                        <td><?= number_format($payment['invoice']['amount_paid'], 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Balance:</strong></td>
                                        <td>
                                            <?php
    $balance = $payment['invoice']['total_amount'] - $payment['invoice']['amount_paid'];
                $balanceClass = $balance > 0 ? 'text-danger' : 'text-success';
                ?>
                                            <span class="<?= $balanceClass ?>">
                                                <?= number_format($balance, 2) ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Additional Information -->
        <div class="col-lg-4">
            <!-- Payment Notes -->
            <?php if (!empty($payment['notes'])): ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Notes</h6>
                    </div>
                    <div class="card-body">
                        <p><?= nl2br(htmlspecialchars($payment['notes'])) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Payment Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($payment['status'] === 'completed'): ?>
                            <button type="button" class="btn btn-info btn-sm" onclick="showRefundModal()">
                                <i class="fas fa-undo fa-fw"></i> Process Refund
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($payment['status'] === 'pending'): ?>
                            <button type="button" class="btn btn-success btn-sm" onclick="markAsCompleted()">
                                <i class="fas fa-check fa-fw"></i> Mark as Completed
                            </button>
                        <?php endif; ?>
                        
                        <a href="<?= $baseUrl ?>/payments/print/<?= $payment['payment_id'] ?>" 
                           class="btn btn-secondary btn-sm" target="_blank">
                            <i class="fas fa-print fa-fw"></i> Print Receipt
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="refundModalLabel">Process Refund</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="refundForm" method="POST" action="<?= $baseUrl ?>/payments/refund/<?= $payment['payment_id'] ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="refund_amount">Refund Amount</label>
                        <input type="number" class="form-control" id="refund_amount" name="refund_amount" 
                               value="<?= $payment['amount'] ?>" max="<?= $payment['amount'] ?>" step="0.01" required>
                        <small class="form-text text-muted">Maximum refund amount: <?= number_format($payment['amount'], 2) ?></small>
                    </div>
                    <div class="form-group">
                        <label for="refund_reason">Reason for Refund</label>
                        <textarea class="form-control" id="refund_reason" name="refund_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Process Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any page-specific functionality
    console.log('Payment details page loaded');
});

function showRefundModal() {
    const modal = new bootstrap.Modal(document.getElementById('refundModal'));
    modal.show();
}

function markAsCompleted() {
    if (confirm('Are you sure you want to mark this payment as completed?')) {
        fetch('<?= $baseUrl ?>/payments/mark-completed/<?= $payment['payment_id'] ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to mark payment as completed.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected error occurred.');
        });
    }
}
</script> 