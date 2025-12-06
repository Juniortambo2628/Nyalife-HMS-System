<?php
/**
 * Nyalife HMS - View Lab Request Details
 */

$pageTitle = 'Lab Request Details - Nyalife HMS';

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
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 text-gray-800">Lab Request Details</h1>
                    <div>
                        <a href="<?= $baseUrl ?>/lab/requests" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Requests
                        </a>
                        <?php if ($request['status'] == 'completed'): ?>
                            <a href="<?= $baseUrl ?>/lab/results/print/<?= $request['id'] ?>" class="btn btn-primary" target="_blank" data-no-ajax>
                                <i class="fas fa-print"></i> Print Results
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Request Information -->
        <div class="row">
            <!-- Request Details -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Request Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="small text-gray-500">Request ID</div>
                            <div class="font-weight-bold"><?= htmlspecialchars($request['id']) ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="small text-gray-500">Status</div>
                            <div>
                                <?php
                                $statusClass = '';
switch ($request['status']) {
    case 'pending': $statusClass = 'warning';
        break;
    case 'in_progress': $statusClass = 'info';
        break;
    case 'completed': $statusClass = 'success';
        break;
    case 'cancelled': $statusClass = 'danger';
        break;
    default: $statusClass = 'secondary';
}
?>
                                <span class="badge bg-<?= $statusClass ?>">
                                    <?= ucfirst(str_replace('_', ' ', $request['status'])) ?>
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="small text-gray-500">Date Requested</div>
                            <div><?= date('M d, Y h:i A', strtotime($request['created_at'])) ?></div>
                        </div>
                        <?php if (!empty($request['updated_at']) && $request['updated_at'] != $request['created_at']): ?>
                            <div class="mb-3">
                                <div class="small text-gray-500">Last Updated</div>
                                <div><?= date('M d, Y h:i A', strtotime($request['updated_at'])) ?></div>
                            </div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <div class="small text-gray-500">Requested By</div>
                            <div><?= htmlspecialchars($request['doctor_name']) ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="small text-gray-500">Notes</div>
                            <div><?= !empty($request['notes']) ? nl2br(htmlspecialchars($request['notes'])) : '<em>No notes provided</em>' ?></div>
                        </div>
                        
                        <?php if ($currentUser['role'] == 'lab_technician' && $request['status'] == 'pending'): ?>
                            <div class="mt-4">
                                <form action="<?= $baseUrl ?>/lab/request/<?= $request['id'] ?>/update-status" method="post" class="status-update-form">
                                    <input type="hidden" name="status" value="in_progress">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-flask"></i> Start Processing
                                    </button>
                                </form>
                            </div>
                        <?php elseif ($currentUser['role'] == 'lab_technician' && $request['status'] == 'in_progress'): ?>
                            <div class="mt-4">
                                <form action="<?= $baseUrl ?>/lab/request/<?= $request['id'] ?>/update-status" method="post" class="status-update-form">
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-check"></i> Mark as Completed
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (in_array($currentUser['role'], ['doctor', 'admin']) && $request['status'] != 'cancelled' && $request['status'] != 'completed'): ?>
                            <div class="mt-4">
                                <form action="<?= $baseUrl ?>/lab/request/<?= $request['id'] ?>/update-status" method="post" class="status-update-form">
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Are you sure you want to cancel this lab request?');">
                                        <i class="fas fa-times"></i> Cancel Request
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Patient Information -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Patient Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="small text-gray-500">Patient ID</div>
                            <div class="font-weight-bold"><?= htmlspecialchars($patient['id']) ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="small text-gray-500">Name</div>
                            <div><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="small text-gray-500">Gender</div>
                            <div><?= htmlspecialchars(ucfirst($patient['gender'])) ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="small text-gray-500">Date of Birth</div>
                            <div><?= date('M d, Y', strtotime($patient['date_of_birth'])) ?> (<?= calculateAge($patient['date_of_birth']) ?> years)</div>
                        </div>
                        <div class="mb-3">
                            <div class="small text-gray-500">Contact</div>
                            <div><?= htmlspecialchars($patient['phone']) ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="small text-gray-500">Email</div>
                            <div><?= htmlspecialchars($patient['email']) ?></div>
                        </div>
                        <div class="mt-3">
                            <a href="<?= $baseUrl ?>/patients/view/<?= $patient['id'] ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-user"></i> View Patient Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Test Results -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Test Results</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($tests)): ?>
                            <div class="alert alert-info">
                                No tests found for this request.
                            </div>
                        <?php else: ?>
                            <?php foreach ($tests as $test): ?>
                                <div class="card mb-3">
                                    <div class="card-header py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="m-0 font-weight-bold"><?= htmlspecialchars($test['name']) ?></h6>
                                            <?php
            $testStatusClass = '';
                                switch ($test['status']) {
                                    case 'pending': $testStatusClass = 'warning';
                                        break;
                                    case 'in_progress': $testStatusClass = 'info';
                                        break;
                                    case 'completed': $testStatusClass = 'success';
                                        break;
                                    default: $testStatusClass = 'secondary';
                                }
                                ?>
                                            <span class="badge bg-<?= $testStatusClass ?>">
                                                <?= ucfirst($test['status']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body py-2">
                                        <?php if ($test['status'] == 'completed'): ?>
                                            <div class="mb-2">
                                                <div class="small text-gray-500">Result</div>
                                                <div><?= htmlspecialchars($test['result']) ?></div>
                                            </div>
                                            <div class="mb-2">
                                                <div class="small text-gray-500">Reference Range</div>
                                                <div><?= htmlspecialchars($test['reference_range']) ?></div>
                                            </div>
                                            <div>
                                                <div class="small text-gray-500">Interpretation</div>
                                                <div>
                                                    <?php
                                        $interpretationClass = '';
                                            switch ($test['interpretation']) {
                                                case 'normal': $interpretationClass = 'success';
                                                    break;
                                                case 'abnormal': $interpretationClass = 'warning';
                                                    break;
                                                case 'critical': $interpretationClass = 'danger';
                                                    break;
                                                default: $interpretationClass = 'info';
                                            }
                                            ?>
                                                    <span class="text-<?= $interpretationClass ?>">
                                                        <?= ucfirst($test['interpretation']) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php elseif ($currentUser['role'] == 'lab_technician' && $test['status'] != 'pending'): ?>
                                            <form action="<?= $baseUrl ?>/lab/test/<?= $test['id'] ?>/update" method="post" class="test-result-form">
                                                <div class="form-group mb-2">
                                                    <label for="result_<?= $test['id'] ?>">Result</label>
                                                    <input type="text" class="form-control" id="result_<?= $test['id'] ?>" name="result" value="<?= htmlspecialchars($test['result'] ?? '') ?>">
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label for="reference_<?= $test['id'] ?>">Reference Range</label>
                                                    <input type="text" class="form-control" id="reference_<?= $test['id'] ?>" name="reference_range" value="<?= htmlspecialchars($test['reference_range'] ?? '') ?>">
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label for="interpretation_<?= $test['id'] ?>">Interpretation</label>
                                                    <select class="form-control" id="interpretation_<?= $test['id'] ?>" name="interpretation">
                                                        <option value="normal" <?= ($test['interpretation'] ?? '') == 'normal' ? 'selected' : '' ?>>Normal</option>
                                                        <option value="abnormal" <?= ($test['interpretation'] ?? '') == 'abnormal' ? 'selected' : '' ?>>Abnormal</option>
                                                        <option value="critical" <?= ($test['interpretation'] ?? '') == 'critical' ? 'selected' : '' ?>>Critical</option>
                                                    </select>
                                                </div>
                                                <div class="mt-2">
                                                    <button type="submit" class="btn btn-primary btn-sm">Save Results</button>
                                                </div>
                                            </form>
                                        <?php else: ?>
                                            <div class="text-center py-2">
                                                <em>Results pending</em>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
// Initialize components when the page is loaded or reloaded via AJAX
document.addEventListener('DOMContentLoaded', initLabRequestViewPage);
document.addEventListener('page:loaded', initLabRequestViewPage);

function initLabRequestViewPage() {
    // Handle status update form submissions with AJAX
    const statusForms = document.querySelectorAll('.status-update-form');
    if (statusForms.length > 0 && typeof Components !== 'undefined') {
        statusForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // If it's a cancel request, confirm first
                if (form.querySelector('input[name="status"]').value === 'cancelled') {
                    if (!confirm('Are you sure you want to cancel this lab request?')) {
                        return;
                    }
                }
                
                // Submit form via AJAX
                const formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the current page to reflect the status change
                        Components.loadPage(window.location.href);
                    } else {
                        // Display error message
                        alert(data.message || 'An error occurred while updating the request status.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An unexpected error occurred. Please try again.');
                });
            });
        });
    }
    
    // Handle test result form submissions with AJAX
    const testForms = document.querySelectorAll('.test-result-form');
    if (testForms.length > 0 && typeof Components !== 'undefined') {
        testForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Submit form via AJAX
                const formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the current page to reflect the updated results
                        Components.loadPage(window.location.href);
                    } else {
                        // Display error message
                        alert(data.message || 'An error occurred while saving test results.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An unexpected error occurred. Please try again.');
                });
            });
        });
    }
}
</script> 