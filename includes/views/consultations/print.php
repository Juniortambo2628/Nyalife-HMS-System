<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .hospital-logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .hospital-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
        }
        .hospital-tagline {
            color: #7f8c8d;
            margin: 5px 0 0;
            font-size: 14px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin: 20px 0 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        .patient-info, .doctor-info {
            margin-bottom: 20px;
        }
        .info-label {
            font-weight: bold;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        .info-value {
            margin-bottom: 10px;
        }
        .consultation-details {
            margin: 20px 0;
        }
        .signature-area {
            margin-top: 50px;
            text-align: right;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            display: inline-block;
            margin-top: 50px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #7f8c8d;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        @media print {
            .no-print {
                display: none;
            }
            .container {
                width: 100%;
            }
            body {
                padding: 0;
                font-size: 12px;
            }
            .section-title {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="hospital-name">Nyalife Hospital</div>
            <div class="hospital-tagline">Quality Healthcare Services</div>
            <div class="hospital-address">123 Hospital Road, Nyalife, Kenya</div>
            <div class="hospital-contact">Phone: +254 700 123456 | Email: info@nyalifehms.com</div>
        </div>

        <!-- Title -->
        <h2 class="text-center mb-4">Consultation Report</h2>
        
        <!-- Consultation Info -->
        <div class="row">
            <div class="col-md-6">
                <div class="info-label">Consultation ID:</div>
                <div class="info-value">#<?= str_pad($consultation['consultation_id'], 6, '0', STR_PAD_LEFT) ?></div>
                
                <div class="info-label">Date:</div>
                <div class="info-value"><?= date('F j, Y g:i A', strtotime($consultation['consultation_date'])) ?></div>
                
                <div class="info-label">Status:</div>
                <div class="info-value">
                    <span class="badge bg-<?=
                        $consultation['status'] === 'completed' ? 'success' :
                        ($consultation['status'] === 'in_progress' ? 'info' :
                        ($consultation['status'] === 'cancelled' ? 'danger' : 'primary')) ?>">
                        <?= ucwords(str_replace('_', ' ', $consultation['status'])) ?>
                    </span>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <div class="info-label">Date Printed:</div>
                <div class="info-value"><?= date('F j, Y g:i A') ?></div>
                
                <div class="info-label">Printed By:</div>
                <div class="info-value"><?= htmlspecialchars($_SESSION['user_name'] ?? 'System') ?></div>
            </div>
        </div>

        <hr>

        <!-- Patient & Doctor Info -->
        <div class="row">
            <div class="col-md-6">
                <div class="section-title">Patient Information</div>
                <div class="info-label">Name:</div>
                <div class="info-value"><?= htmlspecialchars($consultation['patient_first_name'] . ' ' . $consultation['patient_last_name']) ?></div>
                
                <div class="info-label">Date of Birth:</div>
                <div class="info-value"><?= !empty($patient['date_of_birth']) ? date('F j, Y', strtotime($patient['date_of_birth'])) : 'N/A' ?></div>
                
                <div class="info-label">Gender:</div>
                <div class="info-value"><?= !empty($patient['gender']) ? ucfirst($patient['gender']) : 'N/A' ?></div>
                
                <div class="info-label">Phone:</div>
                <div class="info-value"><?= !empty($patient['phone']) ? htmlspecialchars($patient['phone']) : 'N/A' ?></div>
            </div>
            <div class="col-md-6">
                <div class="section-title">Doctor Information</div>
                <div class="info-label">Name:</div>
                <div class="info-value">Dr. <?= htmlspecialchars($consultation['doctor_first_name'] . ' ' . $consultation['doctor_last_name']) ?></div>
                
                <div class="info-label">Specialization:</div>
                <div class="info-value"><?= !empty($consultation['specialization']) ? htmlspecialchars($consultation['specialization']) : 'N/A' ?></div>
                
                <div class="info-label">Department:</div>
                <div class="info-value"><?= !empty($consultation['department']) ? htmlspecialchars($consultation['department']) : 'N/A' ?></div>
            </div>
        </div>

        <!-- Consultation Details -->
        <div class="consultation-details">
            <div class="section-title">Consultation Details</div>
            
            <div class="mb-3">
                <div class="info-label">Diagnosis:</div>
                <div class="border rounded p-3">
                    <?= !empty($consultation['diagnosis']) ? nl2br(htmlspecialchars($consultation['diagnosis'])) :
                        '<span class="text-muted">No diagnosis recorded</span>' ?>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="info-label">Treatment Plan:</div>
                <div class="border rounded p-3">
                    <?= !empty($consultation['treatment_plan']) ? nl2br(htmlspecialchars($consultation['treatment_plan'])) :
                        '<span class="text-muted">No treatment plan recorded</span>' ?>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="info-label">Notes:</div>
                <div class="border rounded p-3">
                    <?= !empty($consultation['notes']) ? nl2br(htmlspecialchars($consultation['notes'])) :
                        '<span class="text-muted">No additional notes</span>' ?>
                </div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="row mt-5">
            <div class="col-md-6">
                <div class="signature-area">
                    <div class="signature-line"></div>
                    <div>Patient's Signature</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="signature-area">
                    <div class="signature-line"></div>
                    <div>Doctor's Signature</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is a computer-generated document. No signature is required.</p>
            <p>&copy; <?= date('Y') ?> Nyalife Hospital. All rights reserved.</p>
        </div>
    </div>

    <!-- Print Button -->
    <div class="container mt-3 no-print">
        <div class="text-center">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-2"></i> Print
            </button>
            <a href="<?= $baseUrl ?>/consultations" class="btn btn-secondary ms-2">
                <i class="fas fa-arrow-left me-2"></i> Back to Consultations
            </a>
        </div>
    </div>

    <script>
        // Auto-print when the page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
        
        // Redirect after print
        window.onafterprint = function() {
            // Optional: Add any post-print logic here
        };
    </script>
</body>
</html>
