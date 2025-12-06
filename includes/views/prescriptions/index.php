<?php
/**
 * Nyalife HMS - Prescriptions Index View
 */

$pageTitle = 'Prescriptions - Nyalife HMS';
?>

<div class="container-fluid">
<div class="row mb-2 mt-2">
    <div class="col-md-6">
        <h1>Prescriptions Management</h1>
    </div>
    <div class="col-md-6 text-end">
        <?php if (SessionManager::get('role') === 'doctor' || SessionManager::get('role') === 'admin'): ?>
        <a href="<?= $baseUrl ?? '' ?>/prescriptions/create" class="btn btn-primary">
            <i class="fa fa-plus"></i> Create New Prescription
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-2 mt-2">
    <div class="card-body" style="background: var(--secondary-color); backdrop-filter: blur(4px); opacity: 0.7;">
        <form action="<?= $baseUrl ?? '' ?>/prescriptions" method="get" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="active" <?= ($status ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="dispensed" <?= ($status ?? '') === 'dispensed' ? 'selected' : '' ?>>Dispensed</option>
                    <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    <option value="all" <?= ($status ?? '') === 'all' ? 'selected' : '' ?>>All</option>
                </select>
            </div>
            <?php if (isset($patient) && $patient): ?>
            <div class="col-md-9">
                <p class="mt-4">
                    <strong>Filtering for patient:</strong> 
                    <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>
                    <a href="<?= $baseUrl ?? '' ?>/prescriptions?status=<?= $status ?? 'active' ?>" class="btn btn-sm btn-secondary ms-2">
                        <i class="fa fa-times"></i> Clear Filter
                    </a>
                </p>
            </div>
            <?php else: ?>
            <div class="col-md-7">
                <label for="patient_search" class="form-label">Patient Search</label>
                <input type="text" class="form-control" id="patient_search" placeholder="Search by patient name or number...">
                <div id="patient_results" class="dropdown-menu" style="width: 100%;"></div>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button class="btn btn-primary w-100" type="submit">
                    <i class="fa fa-filter"></i> Filter
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if (empty($prescriptions)): ?>
    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> No prescriptions found.
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Prescription #</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prescriptions as $prescription):
                            // Ensure all required fields exist and have default values if not set
                            $prescription = array_merge([
                                'prescription_id' => 0,
                                'prescription_number' => 'N/A',
                                'patient_name' => 'Unknown Patient',
                                'doctor_name' => 'Unknown Doctor',
                                'prescription_date' => null,
                                'status' => 'Unknown',
                                'item_count' => 0
                            ], $prescription);

                            // Format date if it exists
                            $prescDate = !empty($prescription['prescription_date']) ? date('d-m-Y', strtotime($prescription['prescription_date'])) : 'N/A';

                            // Set status badge colors
                            $statusClass = 'bg-secondary';
                            if ($prescription['status'] === 'active') {
                                $statusClass = 'bg-primary';
                            } elseif ($prescription['status'] === 'dispensed') {
                                $statusClass = 'bg-success';
                            } elseif ($prescription['status'] === 'cancelled') {
                                $statusClass = 'bg-danger';
                            }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($prescription['prescription_number']) ?></td>
                                <td><?= htmlspecialchars($prescription['patient_name']) ?></td>
                                <td><?= htmlspecialchars($prescription['doctor_name']) ?></td>
                                <td><?= $prescDate ?></td>
                                <td><span class="badge <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($prescription['status'])) ?></span></td>
                                <td><?= (int)$prescription['item_count'] ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= $baseUrl ?? '' ?>/prescriptions/view/<?= $prescription['prescription_id'] ?>" class="btn btn-xs btn-info" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        
                                        <?php if ($prescription['status'] === 'active'): ?>
                                            <?php if (SessionManager::get('role') === 'pharmacist' || SessionManager::get('role') === 'admin'): ?>
                                                <a href="<?= $baseUrl ?? '' ?>/prescriptions/dispense/<?= $prescription['prescription_id'] ?>" class="btn btn-xs btn-success" title="Dispense">
                                                    <i class="fa fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (SessionManager::get('role') === 'doctor' || SessionManager::get('role') === 'admin'): ?>
                                                <a href="<?= $baseUrl ?? '' ?>/prescriptions/cancel/<?= $prescription['prescription_id'] ?>" class="btn btn-xs btn-danger" title="Cancel">
                                                    <i class="fa fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <a href="<?= $baseUrl ?? '' ?>/prescriptions/print/<?= $prescription['prescription_id'] ?>" class="btn btn-xs btn-secondary" title="Print" target="_blank">
                                            <i class="fa fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if (isset($pagination) && isset($pagination['total']) && isset($pagination['page']) && isset($pagination['perPage'])): ?>
                <?php $totalPages = ceil($pagination['total'] / $pagination['perPage']); ?>
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $pagination['url'] . ($pagination['page'] - 1) ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $pagination['url'] . $i ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['page'] < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $pagination['url'] . ($pagination['page'] + 1) ?>">
                                        Next
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
                
                <div class="text-center mt-3">
                    <p>Showing <?= count($prescriptions) ?> of <?= $pagination['total'] ?> prescriptions</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Patient search functionality
    const patientSearch = document.getElementById('patient_search');
    const patientResults = document.getElementById('patient_results');
    
    if (patientSearch) {
        patientSearch.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (query.length < 2) {
                patientResults.style.display = 'none';
                return;
            }
            
            // Fetch patients based on search
            fetch('<?= $baseUrl ?? '' ?>/api/patients/search?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    patientResults.innerHTML = '';
                    
                    if (data.length === 0) {
                        patientResults.innerHTML = '<div class="dropdown-item">No patients found</div>';
                    } else {
                        data.forEach(patient => {
                            const item = document.createElement('a');
                            item.className = 'dropdown-item';
                            item.href = '<?= $baseUrl ?? '' ?>/prescriptions?patient_id=' + patient.patient_id + '&status=<?= $status ?? 'active' ?>';
                            item.textContent = `${patient.first_name} ${patient.last_name} (${patient.patient_number})`;
                            patientResults.appendChild(item);
                        });
                    }
                    
                    patientResults.style.display = 'block';
                })
                .catch(error => console.error('Error fetching patients:', error));
        });
        
        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target !== patientSearch) {
                patientResults.style.display = 'none';
            }
        });
    }
});
</script> 