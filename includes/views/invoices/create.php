<?php
/**
 * Nyalife HMS - Create Invoice View
 *
 * View for creating new invoices.
 */

$pageTitle = 'Create Invoice - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-invoice fa-fw"></i> Create Invoice
        </h1>
        <a href="<?= $baseUrl ?>/invoices" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left fa-fw"></i> Back to Invoices
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

    <!-- Create Invoice Form -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Invoice Information</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= $baseUrl ?>/invoices/store" id="invoice-form">
                <div class="row">
                    <!-- Patient and Doctor Selection -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="patient_id">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" id="patient_id" class="form-control" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?= $patient['patient_id'] ?>" 
                                            <?= ($selectedPatient == $patient['patient_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?> 
                                        (<?= htmlspecialchars($patient['patient_number']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="doctor_id">Doctor</label>
                            <select name="doctor_id" id="doctor_id" class="form-control">
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?= $doctor['user_id'] ?>" 
                                            <?= ($selectedDoctor == $doctor['user_id']) ? 'selected' : '' ?>>
                                        Dr. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="invoice_date">Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" id="invoice_date" 
                                   class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="due_date">Due Date <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" id="due_date" 
                                   class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="mt-4">
                    <h6 class="font-weight-bold">Invoice Items</h6>
                    <div id="invoice-items">
                        <div class="row item-row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Item/Service <span class="text-danger">*</span></label>
                                    <select name="items[0][service_id]" class="form-control service-select" required>
                                        <option value="">Select Service</option>
                                        <?php foreach ($services as $service): ?>
                                            <option value="<?= $service['service_id'] ?>" 
                                                    data-price="<?= $service['price'] ?>">
                                                <?= htmlspecialchars($service['service_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="items[0][quantity]" class="form-control quantity-input" 
                                           value="1" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Unit Price</label>
                                    <input type="number" name="items[0][unit_price]" class="form-control unit-price-input" 
                                           step="0.01" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Total</label>
                                    <input type="number" name="items[0][total_price]" class="form-control total-price-input" 
                                           step="0.01" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-danger btn-block remove-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-success btn-sm mt-2" id="add-item">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>

                <!-- Totals -->
                <div class="row mt-4">
                    <div class="col-md-6 offset-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Subtotal:</strong></td>
                                <td class="text-right">
                                    <span id="subtotal">0.00</span>
                                    <input type="hidden" name="subtotal" id="subtotal-input" value="0">
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tax Rate (%):</strong></td>
                                <td class="text-right">
                                    <input type="number" name="tax_rate" id="tax-rate" class="form-control form-control-sm" 
                                           value="0" min="0" max="100" step="0.01" style="width: 100px; display: inline-block;">
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tax Amount:</strong></td>
                                <td class="text-right">
                                    <span id="tax-amount">0.00</span>
                                    <input type="hidden" name="tax_amount" id="tax-amount-input" value="0">
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Total:</strong></td>
                                <td class="text-right">
                                    <strong><span id="total-amount">0.00</span></strong>
                                    <input type="hidden" name="total_amount" id="total-amount-input" value="0">
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Notes -->
                <div class="form-group mt-4">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3" 
                              placeholder="Additional notes for this invoice..."></textarea>
                </div>

                <!-- Submit Buttons -->
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save fa-fw"></i> Create Invoice
                    </button>
                    <a href="<?= $baseUrl ?>/invoices" class="btn btn-secondary">
                        <i class="fas fa-times fa-fw"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemCount = 1;
    
    // Add new item row
    document.getElementById('add-item').addEventListener('click', function() {
        const template = document.querySelector('.item-row').cloneNode(true);
        template.querySelectorAll('input, select').forEach(input => {
            input.name = input.name.replace('[0]', `[${itemCount}]`);
            input.value = '';
        });
        template.querySelector('.service-select').selectedIndex = 0;
        template.querySelector('.quantity-input').value = '1';
        template.querySelector('.unit-price-input').value = '';
        template.querySelector('.total-price-input').value = '';
        
        document.getElementById('invoice-items').appendChild(template);
        itemCount++;
        
        // Reattach event listeners
        attachItemEventListeners(template);
    });
    
    // Remove item row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            if (document.querySelectorAll('.item-row').length > 1) {
                e.target.closest('.item-row').remove();
                calculateTotals();
            }
        }
    });
    
    // Attach event listeners to item rows
    function attachItemEventListeners(row) {
        const serviceSelect = row.querySelector('.service-select');
        const quantityInput = row.querySelector('.quantity-input');
        const unitPriceInput = row.querySelector('.unit-price-input');
        const totalPriceInput = row.querySelector('.total-price-input');
        
        serviceSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.dataset.price || 0;
            unitPriceInput.value = price;
            calculateItemTotal(row);
        });
        
        quantityInput.addEventListener('input', function() {
            calculateItemTotal(row);
        });
    }
    
    // Calculate item total
    function calculateItemTotal(row) {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.unit-price-input').value) || 0;
        const total = quantity * unitPrice;
        row.querySelector('.total-price-input').value = total.toFixed(2);
        calculateTotals();
    }
    
    // Calculate invoice totals
    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.total-price-input').forEach(input => {
            subtotal += parseFloat(input.value) || 0;
        });
        
        const taxRate = parseFloat(document.getElementById('tax-rate').value) || 0;
        const taxAmount = subtotal * (taxRate / 100);
        const total = subtotal + taxAmount;
        
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('subtotal-input').value = subtotal.toFixed(2);
        document.getElementById('tax-amount').textContent = taxAmount.toFixed(2);
        document.getElementById('tax-amount-input').value = taxAmount.toFixed(2);
        document.getElementById('total-amount').textContent = total.toFixed(2);
        document.getElementById('total-amount-input').value = total.toFixed(2);
    }
    
    // Tax rate change
    document.getElementById('tax-rate').addEventListener('input', calculateTotals);
    
    // Attach event listeners to initial row
    attachItemEventListeners(document.querySelector('.item-row'));
    
    // Form validation
    document.getElementById('invoice-form').addEventListener('submit', function(e) {
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
});
</script> 