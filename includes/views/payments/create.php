<?php
/**
 * Nyalife HMS - Create Payment View
 *
 * View for creating new payments.
 */

$pageTitle = 'Create Payment - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-dollar-sign fa-fw"></i> Create Payment
        </h1>
        <a href="<?= $baseUrl ?>/payments" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left fa-fw"></i> Back to Payments
        </a>
    </div>

    <!-- Create Payment Form -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Payment Information</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= $baseUrl ?>/payments/store" id="payment-form">
                <div class="row">
                    <!-- Invoice Selection -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="invoice_id">Invoice <span class="text-danger">*</span></label>
                            <select name="invoice_id" id="invoice_id" class="form-control" required>
                                <option value="">Select Invoice</option>
                                <?php foreach ($invoices as $inv): ?>
                                    <option value="<?= $inv['invoice_id'] ?>" 
                                            data-total="<?= $inv['total_amount'] ?>" 
                                            data-paid="<?= $inv['amount_paid'] ?>"
                                            <?= ($invoice && $invoice['invoice_id'] == $inv['invoice_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($inv['invoice_number']) ?> - 
                                        <?= htmlspecialchars($inv['patient_name']) ?> 
                                        (<?= number_format($inv['total_amount'], 2) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="payment_date">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" id="payment_date" 
                                   class="form-control" value="<?= date('Y-m-d') ?>" required>
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
                                    <option value="<?= $method ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="amount">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="amount" class="form-control" 
                                   step="0.01" min="0.01" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="reference_number">Reference Number</label>
                            <input type="text" name="reference_number" id="reference_number" 
                                   class="form-control" placeholder="Transaction reference number">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="transaction_id">Transaction ID</label>
                            <input type="text" name="transaction_id" id="transaction_id" 
                                   class="form-control" placeholder="Transaction ID">
                        </div>
                    </div>
                </div>

                <!-- Invoice Details (shown when invoice is selected) -->
                <div id="invoice-details" style="display: none;">
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Invoice Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Invoice Total:</strong>
                                    <span id="invoice-total">0.00</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Amount Paid:</strong>
                                    <span id="amount-paid">0.00</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Balance:</strong>
                                    <span id="balance">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="form-group mt-4">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3" 
                              placeholder="Additional notes for this payment..."></textarea>
                </div>

                <!-- Submit Buttons -->
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save fa-fw"></i> Create Payment
                    </button>
                    <a href="<?= $baseUrl ?>/payments" class="btn btn-secondary">
                        <i class="fas fa-times fa-fw"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const invoiceSelect = document.getElementById('invoice_id');
    const amountInput = document.getElementById('amount');
    const invoiceDetails = document.getElementById('invoice-details');
    
    // Update invoice details when invoice is selected
    invoiceSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const total = parseFloat(selectedOption.dataset.total) || 0;
            const paid = parseFloat(selectedOption.dataset.paid) || 0;
            const balance = total - paid;
            
            document.getElementById('invoice-total').textContent = total.toFixed(2);
            document.getElementById('amount-paid').textContent = paid.toFixed(2);
            document.getElementById('balance').textContent = balance.toFixed(2);
            
            // Set max amount to balance
            amountInput.max = balance;
            amountInput.value = balance.toFixed(2);
            
            invoiceDetails.style.display = 'block';
        } else {
            invoiceDetails.style.display = 'none';
            amountInput.value = '';
        }
    });
    
    // Validate amount doesn't exceed balance
    amountInput.addEventListener('input', function() {
        const amount = parseFloat(this.value) || 0;
        const balance = parseFloat(this.max) || 0;
        
        if (amount > balance) {
            this.setCustomValidity('Amount cannot exceed the remaining balance.');
        } else {
            this.setCustomValidity('');
        }
    });
    
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
        
        // Check if amount is valid
        const amount = parseFloat(amountInput.value) || 0;
        const balance = parseFloat(amountInput.max) || 0;
        
        if (amount > balance) {
            amountInput.classList.add('is-invalid');
            isValid = false;
        } else {
            amountInput.classList.remove('is-invalid');
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields correctly.');
        }
    });
    
    // Trigger change event if invoice is pre-selected
    if (invoiceSelect.value) {
        invoiceSelect.dispatchEvent(new Event('change'));
    }
});
</script> 