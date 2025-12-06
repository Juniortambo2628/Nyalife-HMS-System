<?php
/**
 * Nyalife HMS - Print Payment Receipt View
 *
 * View for printing payment receipts.
 */

$pageTitle = 'Payment Receipt - Nyalife HMS';
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
        
        .receipt-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .receipt-details {
            flex: 1;
        }
        
        .receipt-number {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .receipt-info {
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
        
        .payment-details {
            margin-bottom: 30px;
        }
        
        .payment-details table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .payment-details td {
            padding: 8px;
            border: none;
        }
        
        .payment-details .text-right {
            text-align: right;
        }
        
        .amount-highlight {
            font-size: 20px;
            font-weight: bold;
            color: #28a745;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-failed { background-color: #f8d7da; color: #721c24; }
        .status-refunded { background-color: #cce5ff; color: #004085; }
        
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
        
        .invoice-link {
            color: #007bff;
            text-decoration: none;
        }
        
        .invoice-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Receipt
    </button>
    
    <div class="header">
        <div class="hospital-name">Nyalife Hospital Management System</div>
        <div class="hospital-info">
            <div>123 Hospital Street, Nairobi, Kenya</div>
            <div>Phone: +254 700 000 000 | Email: info@nyalife.com</div>
            <div>Website: www.nyalife.com</div>
        </div>
    </div>
    
    <div class="receipt-header">
        <div class="receipt-details">
            <div class="receipt-number">PAYMENT RECEIPT #<?= htmlspecialchars($payment['payment_id']) ?></div>
            <div class="receipt-info">
                <div><strong>Date:</strong> <?= date('F j, Y', strtotime($payment['payment_date'])) ?></div>
                <div><strong>Time:</strong> <?= date('g:i A', strtotime($payment['created_at'])) ?></div>
                <div><strong>Status:</strong> 
                    <span class="status-badge status-<?= $payment['status'] ?>">
                        <?= ucfirst($payment['status']) ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="patient-info">
            <div class="patient-name">Patient Information</div>
            <div class="patient-details">
                <?php if (!empty($payment['invoice'])): ?>
                    <div><strong>Name:</strong> <?= htmlspecialchars($payment['invoice']['patient_name']) ?></div>
                    <div><strong>Patient ID:</strong> <?= htmlspecialchars($payment['invoice']['patient_number']) ?></div>
                <?php else: ?>
                    <div>No patient information available</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="payment-details">
        <table>
            <tr>
                <td><strong>Payment Method:</strong></td>
                <td class="text-right"><?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?></td>
            </tr>
            <tr>
                <td><strong>Amount:</strong></td>
                <td class="text-right amount-highlight"><?= number_format($payment['amount'], 2) ?></td>
            </tr>
            <?php if (!empty($payment['reference_number'])): ?>
                <tr>
                    <td><strong>Reference Number:</strong></td>
                    <td class="text-right"><?= htmlspecialchars($payment['reference_number']) ?></td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($payment['transaction_id'])): ?>
                <tr>
                    <td><strong>Transaction ID:</strong></td>
                    <td class="text-right"><?= htmlspecialchars($payment['transaction_id']) ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td><strong>Recorded By:</strong></td>
                <td class="text-right"><?= htmlspecialchars($payment['recorded_by_name']) ?></td>
            </tr>
        </table>
    </div>
    
    <?php if (!empty($payment['invoice'])): ?>
        <div style="margin-bottom: 30px;">
            <h4>Related Invoice</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px; border: 1px solid #dee2e6;"><strong>Invoice Number:</strong></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6;">
                        <?= htmlspecialchars($payment['invoice']['invoice_number']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #dee2e6;"><strong>Invoice Date:</strong></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6;">
                        <?= date('F j, Y', strtotime($payment['invoice']['created_at'])) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #dee2e6;"><strong>Invoice Total:</strong></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6;">
                        <?= number_format($payment['invoice']['total_amount'], 2) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #dee2e6;"><strong>Amount Paid:</strong></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6;">
                        <?= number_format($payment['invoice']['amount_paid'], 2) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #dee2e6;"><strong>Balance:</strong></td>
                    <td style="padding: 8px; border: 1px solid #dee2e6;">
                        <?php
                        $balance = $payment['invoice']['total_amount'] - $payment['invoice']['amount_paid'];
        $balanceClass = $balance > 0 ? 'color: #dc3545;' : 'color: #28a745;';
        ?>
                        <span style="<?= $balanceClass ?>">
                            <?= number_format($balance, 2) ?>
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($payment['notes'])): ?>
        <div style="margin-bottom: 30px;">
            <h4>Notes:</h4>
            <p><?= nl2br(htmlspecialchars($payment['notes'])) ?></p>
        </div>
    <?php endif; ?>
    
    <div class="footer">
        <p>Thank you for your payment to Nyalife Hospital Management System</p>
        <p>For any questions regarding this receipt, please contact us at billing@nyalife.com</p>
        <p>This is a computer-generated receipt. No signature required.</p>
    </div>
    
    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html> 