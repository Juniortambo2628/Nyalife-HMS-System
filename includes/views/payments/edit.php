<?php
/**
 * Nyalife HMS - Edit Payment View
 *
 * View for editing existing payments.
 */

$pageTitle = 'Edit Payment - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-dollar-sign fa-fw"></i> Edit Payment
        </h1>
        <div>
            <a href="<?= $baseUrl ?>/payments/show/<?= $payment['payment_id'] ?>" 
               class="btn btn-info btn-sm">
                <i class="fas fa-eye fa-fw"></i> View Payment
            </a>
            <a href="<?= $baseUrl ?>/payments" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left fa-fw"></i> Back to Payments
            </a>
        </div>
    </div>

    <!-- Edit Payment Form -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Payment Information</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= $baseUrl ?>/payments/update/<?= $payment['payment_id'] ?>" id="payment-form">
                <div class="row">
                    <!-- Invoice Information (Read-only) -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Invoice</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($payment['invoice']['invoice_number'] ?? 'N/A') ?>" readonly>
                            <input type="hidden" name="invoice_id" value="<?= $payment['invoice_id'] ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="payment_date">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" id="payment_date" 
                                   class="form-control" value="<?= $payment['payment_date'] ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="payment_method">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" id="payment_method" class="form-control" required>
                                <option value="">Select Payment Method</option>
                                <?php foreach ($paymentMethods as $method => $label): ?>
                                    <option value="<?= $method ?>" <?= $payment['payment_method'] == $method ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="amount">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="amount" class="form-control" 
                                   value="<?= $payment['amount'] ?>" step="0.01" min="0.01" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="reference_number">Reference Number</label>
                            <input type="text" name="reference_number" id="reference_number" 
                                   class="form-control" value="<?= htmlspecialchars($payment['reference_number'] ?? '') ?>" 
                                   placeholder="Transaction reference number">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="transaction_id">Transaction ID</label>
                            <input type="text" name="transaction_id" id="transaction_id" 
                                   class="form-control" value="<?= htmlspecialchars($payment['transaction_id'] ?? '') ?>" 
                                   placeholder="Transaction ID">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="pending" <?= $payment['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="completed" <?= $payment['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="failed" <?= $payment['status'] == 'failed' ? 'selected' : '' ?>>Failed</option>
                                <option value="refunded" <?= $payment['status'] == 'refunded' ? 'selected' : '' ?>>Refunded</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Invoice Details -->
                <?php if (!empty($payment['invoice'])): ?>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Invoice Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Invoice Total:</strong>
                                    <span><?= number_format($payment['invoice']['total_amount'], 2) ?></span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Amount Paid:</strong>
                                    <span><?= number_format($payment['invoice']['amount_paid'], 2) ?></span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Balance:</strong>
                                    <span>
                                        <?php
                                        $balance = $payment['invoice']['total_amount'] - $payment['invoice']['amount_paid'];
                    $balanceClass = $balance > 0 ? 'text-danger' : 'text-success';
                    ?>
                                        <span class="<?= $balanceClass ?>">
                                            <?= number_format($balance, 2) ?>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Notes -->
                <div class="form-group mt-4">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3" 
                              placeholder="Additional notes for this payment..."><?= htmlspecialchars($payment['notes'] ?? '') ?></textarea>
                </div>

                <!-- Submit Buttons -->
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save fa-fw"></i> Update Payment
                    </button>
                    <a href="<?= $baseUrl ?>/payments/show/<?= $payment['payment_id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-times fa-fw"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    document.getElementById('payment-form').addEventListener('submit', function(e) {
        const requiredFields = this.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
    
    // Status change confirmation
    const statusSelect = document.getElementById('status');
    const originalStatus = statusSelect.value;
    
    statusSelect.addEventListener('change', function() {
        if (this.value !== originalStatus) {
            if (confirm('Are you sure you want to change the payment status?')) {
                // Allow the change
            } else {
                this.value = originalStatus;
            }
        }
    });
});
</script> 