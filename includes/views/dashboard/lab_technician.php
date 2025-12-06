<?php
/**
 * Nyalife HMS - Lab Technician Dashboard
 */

$pageTitle = 'Lab Technician Dashboard - Nyalife HMS';
?>
<div class="container-fluid page-wrapper">
        <h1 class="h3 mb-4">Lab Technician Dashboard</h1>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-primary shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Pending Tests</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($pendingLabTests) ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-flask fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-success shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Completed Tests</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($completedLabTests) ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-info shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Today's Samples</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-vial fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-warning shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Urgent Tests</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pending Lab Tests -->
        <div class="row mb-4">
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Pending Lab Tests</h6>
                        <a href="<?= $baseUrl ?>/lab-tests/pending" class="btn btn-sm btn-primary">
                            <i class="fas fa-list me-1"></i> View All
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pendingLabTests)): ?>
                            <div class="text-center p-4">
                                <img src="<?= $baseUrl ?>/assets/img/illustrations/no-appointments.svg" alt="No tests" class="img-fluid mb-3 img-max-150 img-error-handler" data-error-icon="fas fa-microscope">
                                <p class="text-muted">No pending lab tests at the moment.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="pendingLabTestsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Test</th>
                                            <th>Patient</th>
                                            <th>Requested By</th>
                                            <th>Date</th>
                                            <th>Priority</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendingLabTests as $test): ?>
                                            <tr>
                                                <td><?= $test['request_id'] ?? 'N/A' ?></td>
                                                <td><?= htmlspecialchars($test['test_name'] ?? 'Unknown Test') ?></td>
                                                <td>
                                                    <a href="<?= $baseUrl ?>/patients/view/<?= $test['patient_id'] ?? 0 ?>">
                                                        <?= htmlspecialchars($test['patient_name'] ?? 'Unknown Patient') ?>
                                                    </a>
                                                </td>
                                                <td><?= htmlspecialchars($test['requested_by_name'] ?? 'Unknown') ?></td>
                                                <td><?= isset($test['request_date']) ? date('M d, Y', strtotime($test['request_date'])) : 'N/A' ?></td>
                                                <td>
                                                    <?php
                                                    $priorityClass = '';
                                            switch ($test['priority'] ?? '') {
                                                case 'urgent':
                                                    $priorityClass = 'bg-danger';
                                                    break;
                                                case 'stat':
                                                    $priorityClass = 'bg-warning text-dark';
                                                    break;
                                                default:
                                                    $priorityClass = 'bg-secondary';
                                                    break;
                                            }
                                            ?>
                                                    <span class="badge <?= $priorityClass ?>">
                                                        <?= ucfirst($test['priority'] ?? 'routine') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="<?= $baseUrl ?>/lab-tests/process/<?= $test['request_id'] ?? 0 ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-flask"></i>
                                                    </a>
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
            
            <!-- Quick Actions & Info -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?= $baseUrl ?>/lab-tests/pending" class="btn btn-primary">
                                <i class="fas fa-flask me-2"></i> View Pending Tests
                            </a>
                            <a href="<?= $baseUrl ?>/lab-tests/completed" class="btn btn-success">
                                <i class="fas fa-check-circle me-2"></i> View Completed Tests
                            </a>
                            <a href="<?= $baseUrl ?>/lab-tests/register-sample" class="btn btn-info">
                                <i class="fas fa-vial me-2"></i> Register New Sample
                            </a>
                            <a href="<?= $baseUrl ?>/lab-tests/manage" class="btn btn-warning">
                                <i class="fas fa-cog me-2"></i> Manage Test Types
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">My Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="profile-image-container">
                                <img src="<?= $baseUrl ?>/assets/img/profiles/default-lab-tech.png" class="img-profile" onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'fas fa-microscope\'></i>';">
                            </div>
                            <h5 class="mt-2"><?= htmlspecialchars($currentUser['firstName'] . ' ' . $currentUser['lastName']) ?></h5>
                            <p class="text-muted">
                                <i class="fas fa-microscope me-1"></i> Lab Technician
                            </p>
                        </div>
                        
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-envelope me-2"></i> Email</span>
                                <span class="text-muted"><?= htmlspecialchars($currentUser['email'] ?? '-') ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-phone me-2"></i> Phone</span>
                                <span class="text-muted"><?= htmlspecialchars($currentUser['phone'] ?? '-') ?></span>
                            </li>
                        </ul>
                        
                        <div class="mt-3 text-center">
                            <a href="<?= $baseUrl ?>/profile" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-user-edit me-1"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Results -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Recently Completed Tests</h6>
                        <a href="<?= $baseUrl ?>/lab-tests/completed" class="btn btn-sm btn-primary">
                            View All
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($completedLabTests)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> No recently completed lab tests found.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="completedLabTestsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Test</th>
                                            <th>Patient</th>
                                            <th>Completion Date</th>
                                            <th>Performed By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($completedLabTests as $test): ?>
                                            <tr>
                                                <td><?= $test['request_id'] ?? 'N/A' ?></td>
                                                <td><?= htmlspecialchars($test['test_name'] ?? 'Unknown Test') ?></td>
                                                <td>
                                                    <a href="<?= $baseUrl ?>/patients/view/<?= $test['patient_id'] ?? 0 ?>">
                                                        <?= htmlspecialchars($test['patient_name'] ?? 'Unknown Patient') ?>
                                                    </a>
                                                </td>
                                                <td><?= isset($test['completed_at']) ? date('M d, Y', strtotime($test['completed_at'])) : 'N/A' ?></td>
                                                <td><?= htmlspecialchars($test['performed_by_name'] ?? 'Unknown') ?></td>
                                                <td>
                                                    <a href="<?= $baseUrl ?>/lab-tests/view/<?= $test['request_id'] ?? 0 ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?= $baseUrl ?>/lab-tests/print/<?= $test['request_id'] ?? 0 ?>" class="btn btn-sm btn-secondary" data-no-ajax>
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                            </div>
                        <?php endif; ?>
                    </div>
    </div>

    <!-- Bundled Assets -->
    <link rel="stylesheet" href="<?= AssetHelper::getCss('shared') ?>">
    <script src="<?= AssetHelper::getJs('runtime') ?>"></script>
    <script src="<?= AssetHelper::getJs('vendors') ?>"></script>
    <script src="<?= AssetHelper::getJs('shared') ?>"></script>
    <script src="<?= AssetHelper::getJs('app') ?>"></script>
    <script src="<?= AssetHelper::getJs('dashboard-lab') ?>"></script>
</body>
</html>
    <script>
    // Initialize components when the page is loaded or reloaded via AJAX
    document.addEventListener('DOMContentLoaded', initLabTechDashboard);
    document.addEventListener('page:loaded', initLabTechDashboard);

    function initLabTechDashboard() {
        // Initialize DataTables if they exist
        if ($.fn.DataTable) {
            // Pending lab tests table
            const pendingTable = document.getElementById('pendingLabTestsTable');
            if (pendingTable) {
                $(pendingTable).DataTable({
                    paging: false,
                    searching: false,
                    info: false,
                    order: [[5, 'desc'], [4, 'asc']] // Sort by priority desc, then date asc
                });
            }
            
            // Completed lab tests table
            const completedTable = document.getElementById('completedLabTestsTable');
            if (completedTable) {
                $(completedTable).DataTable({
                    paging: false,
                    searching: false,
                    info: false,
                    order: [[3, 'desc']] // Sort by completion date desc
                });
            }
        }
        
        // Auto-refresh pending tests every 30 seconds
        setInterval(refreshPendingTests, 30000);
        
        // Initial refresh after 5 seconds
        setTimeout(refreshPendingTests, 5000);
        
        // Add AJAX navigation to all links if Components is available
        if (typeof Components !== 'undefined') {
            // Get all links in the main content area
            const links = document.querySelectorAll('#main-content a[href^="<?= $baseUrl ?>"]');
            links.forEach(link => {
                // Skip links that should not use AJAX
                if (!link.hasAttribute('data-no-ajax') && 
                    !link.getAttribute('href').includes('#') &&
                    !link.getAttribute('href').endsWith('.pdf') &&
                    !link.getAttribute('href').endsWith('.doc') &&
                    !link.getAttribute('href').endsWith('.docx')) {
                    
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        Components.loadPage(this.href);
                    });
                }
            });
        }
    }

    // Function to refresh pending tests via AJAX
    function refreshPendingTests() {
        fetch('<?= $baseUrl ?>/api/lab-tests/pending')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    updatePendingTestsTable(data.data);
                }
            })
            .catch(error => {
                console.error('Error refreshing pending tests:', error);
            });
    }

    // Function to update the pending tests table
    function updatePendingTestsTable(tests) {
        const tbody = document.querySelector('#pendingLabTestsTable tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        if (tests.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No pending tests found</td></tr>';
            return;
        }
        
        tests.forEach(test => {
            const row = document.createElement('tr');
            
            // Determine priority class
            let priorityClass = 'bg-secondary';
            switch (test.priority) {
                case 'urgent':
                    priorityClass = 'bg-danger';
                    break;
                case 'stat':
                    priorityClass = 'bg-warning text-dark';
                    break;
            }
            
            row.innerHTML = `
                <td>${test.request_id || 'N/A'}</td>
                <td>${test.test_name || 'Unknown Test'}</td>
                <td><a href="<?= $baseUrl ?>/patients/view/${test.patient_id || 0}">${test.patient_name || 'Unknown Patient'}</a></td>
                <td>${test.requested_by_name || 'Unknown'}</td>
                <td>${test.request_date ? new Date(test.request_date).toLocaleDateString() : 'N/A'}</td>
                <td><span class="badge ${priorityClass}">${test.priority || 'routine'}</span></td>
                <td><a href="<?= $baseUrl ?>/lab-tests/process/${test.request_id || 0}" class="btn btn-sm btn-primary"><i class="fas fa-flask"></i></a></td>
            `;
            
            tbody.appendChild(row);
        });
    }
    </script>
