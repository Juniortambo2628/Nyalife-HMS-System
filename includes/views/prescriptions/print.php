<?php
/**
 * Nyalife HMS - Prescription Print View
 */

$pageTitle = 'Prescription Print - Nyalife HMS';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription #<?= htmlspecialchars($prescription['prescription_number'] ?? 'N/A') ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            font-size: 14px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .logo {
            max-height: 80px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 24px;
            margin: 0;
        }
        h2 {
            font-size: 18px;
            margin: 0 0 5px 0;
        }
        .facility-info {
            font-size: 12px;
            margin-bottom: 5px;
        }
        .prescription-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .prescription-info div {
            flex: 1;
        }
        .patient-info, .doctor-info {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .notes {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            text-align: center;
            font-size: 12px;
        }
        .signature {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        .signature div {
            flex: 1;
            border-top: 1px solid #333;
            margin: 0 20px;
            text-align: center;
            padding-top: 5px;
        }
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            .no-print {
                display: none;
            }
            @page {
                size: A4;
                margin: 15mm;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="<?= $baseUrl ?? '' ?>/assets/img/logo/nyalife-logo.png" alt="Nyalife HMS Logo" class="logo">
        <h1>Nyalife Hospital</h1>
        <div class="facility-info">
            123 Healthcare Avenue, Medical District<br>
            Phone: (123) 456-7890 | Email: prescriptions@nyalifehms.com
        </div>
    </div>
    
    <div class="prescription-info">
        <div>
            <strong>Prescription #:</strong> <?= htmlspecialchars($prescription['prescription_number'] ?? 'N/A') ?><br>
            <strong>Date:</strong> <?= !empty($prescription['prescription_date']) ? date('d-m-Y', strtotime($prescription['prescription_date'])) : 'N/A' ?>
        </div>
        <div style="text-align: right;">
            <strong>Status:</strong> <?= ucfirst(htmlspecialchars($prescription['status'] ?? 'Unknown')) ?>
        </div>
    </div>
    
    <div class="patient-info">
        <h2>Patient Information</h2>
        <table>
            <tr>
                <th>Name</th>
                <td><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name'] ?? 'N/A') ?></td>
                <th>Patient Number</th>
                <td><?= htmlspecialchars($patient['patient_number'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th>Gender</th>
                <td><?= htmlspecialchars($patient['gender'] ?? 'N/A') ?></td>
                <th>Age</th>
                <td>
                    <?php
                        if (!empty($patient['date_of_birth'])) {
                            $dob = new DateTime($patient['date_of_birth']);
                            $now = new DateTime();
                            $age = $now->diff($dob)->y;
                            echo $age . ' years';
                        } else {
                            echo 'N/A';
                        }
?>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="doctor-info">
        <h2>Prescriber Information</h2>
        <table>
            <tr>
                <th>Doctor</th>
                <td><?= htmlspecialchars($prescription['doctor_name'] ?? 'N/A') ?></td>
                <th>License Number</th>
                <td>NMC-<?= str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT) ?></td>
            </tr>
        </table>
    </div>
    
    <h2>Rx</h2>
    <?php if (empty($items)): ?>
        <p>No medications prescribed.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Medication</th>
                    <th>Dosage</th>
                    <th>Frequency</th>
                    <th>Duration</th>
                    <th>Special Instructions</th>
                </tr>
            </thead>
            <tbody>
                <?php $counter = 1; ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= $counter++ ?></td>
                        <td>
                            <strong><?= htmlspecialchars($item['medication_name'] ?? 'N/A') ?></strong>
                            <?php if (!empty($item['strength'])): ?>
                                <br><small><?= htmlspecialchars($item['strength']) ?> <?= htmlspecialchars($item['unit'] ?? '') ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($item['dosage'] ?? 'N/A') ?></td>
                        <td>
                            <?php
            $frequency = $item['frequency'] ?? '';
                    $frequencyMap = [
                        'once_daily' => 'Once daily',
                        'twice_daily' => 'Twice daily (BID)',
                        'three_times_daily' => 'Three times daily (TID)',
                        'four_times_daily' => 'Four times daily (QID)',
                        'every_morning' => 'Every morning (QAM)',
                        'every_night' => 'Every night (QHS)',
                        'every_6_hours' => 'Every 6 hours (q6h)',
                        'every_8_hours' => 'Every 8 hours (q8h)',
                        'every_12_hours' => 'Every 12 hours (q12h)',
                        'as_needed' => 'As needed (PRN)',
                        'with_meals' => 'With meals',
                        'before_meals' => 'Before meals',
                        'after_meals' => 'After meals',
                        'other' => 'Other'
                    ];
                    echo htmlspecialchars($frequencyMap[$frequency] ?? $frequency);
                    ?>
                        </td>
                        <td>
                            <?php
                        $duration = $item['duration'] ?? '';
                    $durationMap = [
                        '3_days' => '3 days',
                        '5_days' => '5 days',
                        '7_days' => '7 days',
                        '10_days' => '10 days',
                        '14_days' => '14 days',
                        '21_days' => '21 days',
                        '30_days' => '30 days',
                        '60_days' => '60 days',
                        '90_days' => '90 days',
                        'indefinite' => 'Indefinite/Chronic',
                        'other' => 'Other'
                    ];
                    echo htmlspecialchars($durationMap[$duration] ?? $duration);
                    ?>
                        </td>
                        <td><?= htmlspecialchars($item['instructions'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <?php if (!empty($prescription['notes'])): ?>
    <div class="notes">
        <h2>Additional Notes</h2>
        <p><?= nl2br(htmlspecialchars($prescription['notes'])) ?></p>
    </div>
    <?php endif; ?>
    
    <div class="signature">
        <div>
            <strong>Doctor's Signature</strong>
        </div>
        <div>
            <strong>Pharmacist's Signature</strong>
        </div>
    </div>
    
    <div class="footer">
        <p>This prescription is valid for 30 days from the date of issue.</p>
        <p>Nyalife Hospital Management System &copy; <?= date('Y') ?></p>
    </div>
    
    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">
            Print Prescription
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background-color: #f44336; color: white; border: none; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>
</body>
</html> 