<?php
/**
 * Nyalife HMS - Print Invoice View
 *
 * View for printing invoices.
 */

$pageTitle = 'Print Invoice - Nyalife HMS';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #fff;
            color: #333;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .hospital-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .hospital-info {
            font-size: 14px;
            color: #666;
        }
        
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .invoice-details {
            flex: 1;
        }
        
        .invoice-number {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .invoice-info {
            font-size: 14px;
            line-height: 1.5;
        }
        
        .patient-info {
            flex: 1;
            text-align: right;
        }
        
        .patient-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .patient-details {
            font-size: 14px;
            line-height: 1.5;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table th {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        
        .items-table td {
            border: 1px solid #dee2e6;
            padding: 12px;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .totals {
            width: 100%;
            margin-bottom: 30px;
        }
        
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals td {
            padding: 8px;
            border: none;
        }
        
        .totals .text-right {
            text-align: right;
        }
        
        .totals .total-row {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #333;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .print-button:hover {
            background-color: #0056b3;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-paid { background-color: #d4edda; color: #155724; }
        .status-partially_paid { background-color: #cce5ff; color: #004085; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        .status-overdue { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Invoice
    </button>
    
    <div class="header">
        <div class="hospital-name">Nyalife Hospital Management System</div>
        <div class="hospital-info">
            <div>123 Hospital Street, Nairobi, Kenya</div>
            <div>Phone: +254 700 000 000 | Email: info@nyalife.com</div>
            <div>Website: www.nyalife.com</div>
        </div>
    </div>
    
    <div class="invoice-header">
        <div class="invoice-details">
            <div class="invoice-number">INVOICE #<?= htmlspecialchars($invoice['invoice_number']) ?></div>
            <div class="invoice-info">
                <div><strong>Date:</strong> <?= date('F j, Y', strtotime($invoice['created_at'])) ?></div>
                <div><strong>Due Date:</strong> <?= date('F j, Y', strtotime($invoice['due_date'])) ?></div>
                <div><strong>Status:</strong> 
                    <span class="status-badge status-<?= $invoice['status'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $invoice['status'])) ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="patient-info">
            <div class="patient-name">Patient Information</div>
            <div class="patient-details">
                <div><strong>Name:</strong> <?= htmlspecialchars($invoice['patient_name']) ?></div>
                <div><strong>Patient ID:</strong> <?= htmlspecialchars($invoice['patient_number']) ?></div>
                <?php if (!empty($invoice['doctor_name'])): ?>
                    <div><strong>Doctor:</strong> <?= htmlspecialchars($invoice['doctor_name']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <table class="items-table">
        <thead>
            <tr>
                <th>Item/Service</th>
                <th>Description</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($invoice['items'])): ?>
                <?php foreach ($invoice['items'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                        <td><?= htmlspecialchars($item['description']) ?></td>
                        <td class="text-right"><?= $item['quantity'] ?></td>
                        <td class="text-right"><?= number_format($item['unit_price'], 2) ?></td>
                        <td class="text-right"><?= number_format($item['total_price'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No items found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="totals">
        <table>
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td class="text-right"><?= number_format($invoice['subtotal'], 2) ?></td>
            </tr>
            <tr>
                <td><strong>Tax (<?= $invoice['tax_rate'] ?? 0 ?>%):</strong></td>
                <td class="text-right"><?= number_format($invoice['tax_amount'], 2) ?></td>
            </tr>
            <tr class="total-row">
                <td><strong>Total Amount:</strong></td>
                <td class="text-right"><strong><?= number_format($invoice['total_amount'], 2) ?></strong></td>
            </tr>
        </table>
    </div>
    
    <?php if (!empty($invoice['notes'])): ?>
        <div style="margin-bottom: 30px;">
            <h4>Notes:</h4>
            <p><?= nl2br(htmlspecialchars($invoice['notes'])) ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($invoice['payments'])): ?>
        <div style="margin-bottom: 30px;">
            <h4>Payment History:</h4>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Method</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoice['payments'] as $payment): ?>
                        <tr>
                            <td><?= date('F j, Y', strtotime($payment['payment_date'])) ?></td>
                            <td><?= ucfirst($payment['payment_method']) ?></td>
                            <td class="text-right"><?= number_format($payment['amount'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <div class="footer">
        <p>Thank you for choosing Nyalife Hospital Management System</p>
        <p>For any questions regarding this invoice, please contact us at billing@nyalife.com</p>
        <p>This is a computer-generated invoice. No signature required.</p>
    </div>
    
    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html> 