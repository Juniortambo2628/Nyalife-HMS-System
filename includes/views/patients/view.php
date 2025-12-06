<?php
/**
 * Nyalife HMS - Patient Details View
 */

$pageTitle = 'Patient Details - Nyalife HMS';
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
<div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Patient Details</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="<?= $baseUrl ?>/patients" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Patients
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($successMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($errorMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Patient Information Card -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Patient Information</h5>
                        <a href="<?= $baseUrl ?>/patients/edit/<?= $patient['patient_id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                    <div class="card-body">
						<div class="text-center mb-4">
							<div class="avatar-circle mx-auto">
								<span class="avatar-initials">
									<?= strtoupper(substr($patient['first_name'] ?? 'U', 0, 1) . substr($patient['last_name'] ?? 'N', 0, 1)) ?>
								</span>
							</div>
							<h4 class="mt-3"><?= htmlspecialchars(trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''))) ?: 'Unknown' ?></h4>
							<p class="text-muted mb-0">Patient ID: <?= htmlspecialchars((string)($patient['patient_id'] ?? '')) ?></p>
						</div>
                        
                        <hr>
                        
                        <div class="patient-details">
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Gender:</div>
                                <div class="col-7" data-field="gender"><?= isset($patient['gender']) && $patient['gender'] !== null ? htmlspecialchars(ucfirst($patient['gender'])) : 'Not recorded' ?></div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Date of Birth:</div>
                                <div class="col-7" data-field="date_of_birth"><?= !empty($patient['date_of_birth']) ? htmlspecialchars($patient['date_of_birth']) . ' (' . calculateAge($patient['date_of_birth']) . ' years)' : 'Not recorded' ?></div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Blood Type:</div>
                                <div class="col-7" data-field="blood_type"><?= htmlspecialchars($patient['blood_type'] ?? 'Not recorded') ?></div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Phone:</div>
                                <div class="col-7" data-field="phone"><?= htmlspecialchars($patient['phone'] ?? 'Not provided') ?></div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Email:</div>
                                <div class="col-7" data-field="email"><?= htmlspecialchars($patient['email'] ?? 'Not provided') ?></div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Address:</div>
                                <div class="col-7" data-field="address"><?= htmlspecialchars($patient['address'] ?? 'Not provided') ?></div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Emergency Contact:</div>
                                <div class="col-7" data-field="emergency_contact_name"><?= htmlspecialchars($patient['emergency_contact_name'] ?? 'Not provided') ?></div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Emergency Phone:</div>
                                <div class="col-7" data-field="emergency_contact_phone"><?= htmlspecialchars($patient['emergency_contact_phone'] ?? 'Not provided') ?></div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-5 fw-bold">Registration Date:</div>
                                <div class="col-7" data-field="registration_date"><?= htmlspecialchars($patient['registration_date'] ?? 'Not recorded') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Medical History and Actions -->
            <div class="col-md-8">
                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a href="<?= $baseUrl ?>/consultations/create?patient_id=<?= $patient['patient_id'] ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-stethoscope me-2"></i> New Consultation
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="<?= $baseUrl ?>/vitals/create?patient_id=<?= $patient['patient_id'] ?>" class="btn btn-success w-100">
                                    <i class="fas fa-heartbeat me-2"></i> Record Vitals
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="<?= $baseUrl ?>/prescriptions/create?patient_id=<?= $patient['patient_id'] ?>" class="btn btn-info w-100 text-white">
                                    <i class="fas fa-prescription me-2"></i> New Prescription
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="<?= $baseUrl ?>/lab/request/new?patient_id=<?= $patient['patient_id'] ?>" class="btn btn-warning w-100">
                                    <i class="fas fa-flask me-2"></i> Lab Request
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabs for Patient Records -->
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="patientTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="consultations-tab" data-bs-toggle="tab" data-bs-target="#consultations" type="button" role="tab" aria-controls="consultations" aria-selected="true">
                                    <i class="fas fa-stethoscope me-1"></i> Consultations
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="vitals-tab" data-bs-toggle="tab" data-bs-target="#vitals" type="button" role="tab" aria-controls="vitals" aria-selected="false">
                                    <i class="fas fa-heartbeat me-1"></i> Vitals
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="prescriptions-tab" data-bs-toggle="tab" data-bs-target="#prescriptions" type="button" role="tab" aria-controls="prescriptions" aria-selected="false">
                                    <i class="fas fa-prescription-bottle-alt me-1"></i> Prescriptions
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="lab-results-tab" data-bs-toggle="tab" data-bs-target="#lab-results" type="button" role="tab" aria-controls="lab-results" aria-selected="false">
                                    <i class="fas fa-flask me-1"></i> Lab Results
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="patientTabsContent">
                            <!-- Consultations Tab -->
                            <div class="tab-pane fade show active" id="consultations" role="tabpanel" aria-labelledby="consultations-tab">
                                <?php if (empty($consultations)): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> No consultation records found for this patient.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover datatable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Doctor</th>
                                                    <th>Chief Complaint</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($consultations as $consultation): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($consultation['formatted_date'] ?? 'N/A') ?></td>
                                                        <td>Dr. <?= htmlspecialchars($consultation['doctor_name'] ?? 'Unknown') ?></td>
                                                        <td>
                                                            <?php
                                                            $complaint = $consultation['chief_complaint'] ?? 'N/A';
                                                    echo htmlspecialchars(substr($complaint, 0, 50));
                                                    if (strlen($complaint) > 50) {
                                                        echo '...';
                                                    }
                                                    ?>
                                                        </td>
                                                        <td><span class="<?= $consultation['status_class'] ?? 'badge bg-secondary' ?>"><?= htmlspecialchars($consultation['status_label'] ?? 'Unknown') ?></span></td>
                                                        <td>
                                                            <?php
                                                    $consultationId = $consultation['id'] ?? $consultation['consultation_id'] ?? null;
                                                    if ($consultationId): ?>
                                                                <a href="<?= $baseUrl ?>/consultations/view/<?= $consultationId ?>" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">N/A</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Vitals Tab -->
                            <div class="tab-pane fade" id="vitals" role="tabpanel" aria-labelledby="vitals-tab">
                                <?php if (empty($vitals)): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> No vital sign records found for this patient.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover datatable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>BP</th>
                                                    <th>Pulse</th>
                                                    <th>Temperature</th>
                                                    <th>Respiratory Rate</th>
                                                    <th>SpO2</th>
                                                    <th>Recorded By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($vitals as $vital): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($vital['formatted_date'] ?? $vital['recorded_at'] ?? 'N/A') ?></td>
                                                        <td><?php
                                                    $bp = $vital['blood_pressure'] ?? null;
                                                    echo htmlspecialchars($bp ?: 'N/A');
                                                    if ($bp) {
                                                        echo ' mmHg';
                                                    }
                                                    ?></td>
                                                        <td><?php
                                                        $pulse = $vital['heart_rate'] ?? null;
                                                    echo htmlspecialchars($pulse ?: 'N/A');
                                                    if ($pulse) {
                                                        echo ' bpm';
                                                    }
                                                    ?></td>
                                                        <td><?php
                                                        $temp = $vital['temperature'] ?? null;
                                                    echo htmlspecialchars($temp ?: 'N/A');
                                                    if ($temp) {
                                                        echo ' °C';
                                                    }
                                                    ?></td>
                                                        <td><?php
                                                        $resp = $vital['respiratory_rate'] ?? null;
                                                    echo htmlspecialchars($resp ?: 'N/A');
                                                    if ($resp) {
                                                        echo ' bpm';
                                                    }
                                                    ?></td>
                                                        <td><?php
                                                        $spo2 = $vital['oxygen_saturation'] ?? null;
                                                    echo htmlspecialchars($spo2 ?: 'N/A');
                                                    if ($spo2) {
                                                        echo '%';
                                                    }
                                                    ?></td>
                                                        <td><?= htmlspecialchars($vital['recorded_by'] ?? 'Unknown') ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Prescriptions Tab -->
                            <div class="tab-pane fade" id="prescriptions" role="tabpanel" aria-labelledby="prescriptions-tab">
                                <?php if (empty($prescriptions)): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> No prescription records found for this patient.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover datatable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Doctor</th>
                                                    <th>Medications</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($prescriptions as $prescription): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($prescription['formatted_date'] ?? 'N/A') ?></td>
                                                        <td>Dr. <?= htmlspecialchars($prescription['doctor_name'] ?? 'Unknown') ?></td>
                                                        <td>
                                                            <span data-field="prescription_medications">
                                                            <?php if (!empty($prescription['items'])): ?>
                                                                <?php
                                                            $medicationNames = [];
                                                                foreach ($prescription['items'] as $item) {
                                                                    $medicationNames[] = htmlspecialchars($item['medication_name'] . ' (' . $item['dosage'] . ')');
                                                                }
                                                                echo implode(', ', $medicationNames);
                                                                ?>
                                                            <?php else: ?>
                                                                0 medications
                                                            <?php endif; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $status = $prescription['status'] ?? 'pending';
                                                    $statusClass = 'bg-secondary';
                                                    $statusLabel = 'Unknown';

                                                    switch ($status) {
                                                        case 'pending':
                                                            $statusClass = 'bg-warning text-dark';
                                                            $statusLabel = 'Pending';
                                                            break;
                                                        case 'dispensed':
                                                            $statusClass = 'bg-success';
                                                            $statusLabel = 'Dispensed';
                                                            break;
                                                        case 'partially_dispensed':
                                                            $statusClass = 'bg-info';
                                                            $statusLabel = 'Partially Dispensed';
                                                            break;
                                                        case 'cancelled':
                                                            $statusClass = 'bg-danger';
                                                            $statusLabel = 'Cancelled';
                                                            break;
                                                        default:
                                                            $statusClass = 'bg-secondary';
                                                            $statusLabel = ucfirst($status ?: 'Unknown');
                                                            break;
                                                    }
                                                    ?>
                                                            <span class="badge <?= $statusClass ?>" data-field~="prescription_status"><?= $statusLabel ?></span>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($prescription['prescription_id'])): ?>
                                                                <a href="<?= $baseUrl ?>/prescriptions/view/<?= $prescription['prescription_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">N/A</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Lab Results Tab -->
                            <div class="tab-pane fade" id="lab-results" role="tabpanel" aria-labelledby="lab-results-tab">
                                <?php if (empty($labResults)): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> No laboratory test results found for this patient.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover datatable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Test</th>
                                                    <th>Result</th>
                                                    <th>Reference Range</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($labResults as $result): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($result['formatted_date'] ?? 'N/A') ?></td>
                                                        <td><?= htmlspecialchars($result['test_name'] ?? 'Unknown Test') ?></td>
                                                        <td>
                                                            <?= htmlspecialchars($result['result_value'] ?? 'N/A') ?>
                                                            <?php if (!empty($result['units'])): ?>
                                                                <?= htmlspecialchars($result['units']) ?>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= htmlspecialchars($result['normal_range'] ?? 'N/A') ?></td>
                                                        <td>
                                                            <?php
                                                    $status = $result['status'] ?? 'completed';
                                                    $statusClass = 'bg-secondary';
                                                    $statusLabel = 'Unknown';

                                                    switch ($status) {
                                                        case 'pending':
                                                            $statusClass = 'bg-warning text-dark';
                                                            $statusLabel = 'Pending';
                                                            break;
                                                        case 'processing':
                                                            $statusClass = 'bg-info';
                                                            $statusLabel = 'Processing';
                                                            break;
                                                        case 'completed':
                                                            $statusClass = 'bg-success';
                                                            $statusLabel = 'Completed';
                                                            break;
                                                        case 'cancelled':
                                                            $statusClass = 'bg-danger';
                                                            $statusLabel = 'Cancelled';
                                                            break;
                                                    }
                                                    ?>
                                                            <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($result['test_item_id'])): ?>
                                                                <a href="<?= $baseUrl ?>/lab-tests/view/<?= $result['test_item_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">N/A</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- end duplicate wrapper removed -->

<style>
.avatar-circle {
    width: 100px;
    height: 100px;
    background-color: #007bff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-initials {
    color: white;
    font-size: 2rem;
    font-weight: bold;
}

.badge {
    font-size: 0.85rem;
}
</style>

<script>
// Initialize components when the page is loaded or reloaded via AJAX
document.addEventListener('DOMContentLoaded', initPatientViewPage);
document.addEventListener('page:loaded', initPatientViewPage);

function initPatientViewPage() {
    // Initialize DataTables if available
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.datatable').DataTable({
            "paging": true,
            "ordering": true,
            "info": true,
            "responsive": true,
            "lengthChange": false,
            "pageLength": 5,
            "language": {
                "search": "Search:",
                "zeroRecords": "No matching records found"
            }
        });
    }
    
    // Handle tab changes
    const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
    if (tabLinks.length > 0) {
        tabLinks.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(event) {
                // Reinitialize DataTables when tab is shown
                const tabId = event.target.getAttribute('data-bs-target').substring(1);
                const tabContent = document.getElementById(tabId);
                if (tabContent && typeof $.fn.DataTable !== 'undefined') {
                    const table = tabContent.querySelector('.datatable');
                    if (table) {
                        $(table).DataTable().columns.adjust();
                    }
                }
            });
        });
    }
    
    // Make all links in the tabs use AJAX navigation
    if (typeof Components !== 'undefined') {
        const links = document.querySelectorAll('#main-content a[href^="<?= $baseUrl ?>"]');
        links.forEach(link => {
            if (!link.hasAttribute('data-no-ajax')) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    Components.loadPage(link.href);
                });
            }
        });
    }
}
</script>