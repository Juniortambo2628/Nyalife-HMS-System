<?php
/**
 * Nyalife HMS - Print Lab Results
 */

$pageTitle = 'Print Lab Results - Nyalife HMS';

/**
 * Calculate age from date of birth
 *
 * @param string $dateOfBirth Date of birth in Y-m-d format
 * @return int Age in years
 */
function calculateAge($dateOfBirth)
{
    $dob = new DateTime($dateOfBirth);
    $now = new DateTime();
    $interval = $now->diff($dob);
    return $interval->y;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Results - <?= htmlspecialchars($request['id']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .logo {
            max-height: 80px;
            margin-bottom: 10px;
        }
        .hospital-name {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .hospital-address {
            font-size: 14px;
            margin: 5px 0;
        }
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0 10px;
            text-align: center;
            text-transform: uppercase;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .info-item {
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 150px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .normal {
            color: green;
        }
        .abnormal {
            color: orange;
        }
        .critical {
            color: red;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
            font-size: 12px;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
            text-align: center;
        }
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print();" style="padding: 10px 20px; background-color: #4e73df; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Print Report
        </button>
        <button onclick="window.close();" style="padding: 10px 20px; background-color: #858796; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>
    
    <div class="report-header">
        <?php if (!empty($hospitalLogo)): ?>
            <img src="<?= $hospitalLogo ?>" alt="Hospital Logo" class="logo">
        <?php endif; ?>
        <h1 class="hospital-name">Nyalife Hospital</h1>
        <p class="hospital-address">123 Health Avenue, Nairobi, Kenya</p>
        <p class="hospital-address">Tel: +254 123 456 789 | Email: info@nyalife.com</p>
    </div>
    
    <h2 class="report-title">Laboratory Test Results</h2>
    
    <div class="section">
        <h3 class="section-title">Patient Information</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Patient ID:</span>
                <span><?= htmlspecialchars($patient['id']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Name:</span>
                <span><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Gender:</span>
                <span><?= htmlspecialchars(ucfirst($patient['gender'])) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Date of Birth:</span>
                <span><?= date('M d, Y', strtotime($patient['date_of_birth'])) ?> (<?= calculateAge($patient['date_of_birth']) ?> years)</span>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h3 class="section-title">Request Information</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Request ID:</span>
                <span><?= htmlspecialchars($request['id']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Date Requested:</span>
                <span><?= date('M d, Y', strtotime($request['created_at'])) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Requested By:</span>
                <span>Dr. <?= htmlspecialchars($request['doctor_name']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Date Completed:</span>
                <span><?= date('M d, Y', strtotime($request['updated_at'])) ?></span>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h3 class="section-title">Test Results</h3>
        <table>
            <thead>
                <tr>
                    <th>Test</th>
                    <th>Result</th>
                    <th>Reference Range</th>
                    <th>Interpretation</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($tests)): ?>
                    <?php foreach ($tests as $test): ?>
                        <tr>
                            <td><?= htmlspecialchars($test['name']) ?></td>
                            <td><?= htmlspecialchars($test['result']) ?></td>
                            <td><?= htmlspecialchars($test['reference_range']) ?></td>
                            <td class="<?= $test['interpretation'] ?>">
                                <?= ucfirst($test['interpretation']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No test results available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if (!empty($request['notes'])): ?>
    <div class="section">
        <h3 class="section-title">Notes</h3>
        <p><?= nl2br(htmlspecialchars($request['notes'])) ?></p>
    </div>
    <?php endif; ?>
    
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                Laboratory Technician Signature
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                Reviewed By (Doctor)
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>This is a computer-generated report and does not require a physical signature.</p>
        <p>Report generated on: <?= date('M d, Y h:i A') ?></p>
        <p>Nyalife Hospital Management System &copy; <?= date('Y') ?></p>
    </div>
</body>
</html> 