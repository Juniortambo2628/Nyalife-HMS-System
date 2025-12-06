<div class="row mb-2 mt-2">
    <div class="col-md-6">
        <h1>Patient Management</h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= $baseUrl ?>/patients/create" class="btn btn-primary">
            <i class="fa fa-plus"></i> Register New Patient
        </a>
    </div>
</div>

<div class="card mb-2 mt-2">
    <div class="card-body" style="background: var(--secondary-color); backdrop-filter: blur(4px); opacity: 0.7;">
        <form action="<?= $baseUrl ?>/patients" method="get" class="row g-3">
            <div class="col-md-8">
                <div class="input-group align-items-center justify-content-center">
                    <input type="text" class="form-control" name="search" placeholder="Search by name, patient number, email or phone..." value="<?= htmlspecialchars($searchTerm) ?>">
                    <button class="btn btn-lg btn-primary" type="submit">
                        <i class="fa fa-search text-white"></i> Search
                    </button>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <?php if (!empty($searchTerm)): ?>
                    <a href="<?= $baseUrl ?>/patients" class="btn btn-secondary">
                        <i class="fa fa-times"></i> Clear Search
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

<?php if (empty($patients)): ?>
    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> No patients found.
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Patient Number</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Date of Birth</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $patient): 
                            // Ensure all required fields exist and have default values if not set
                            $patient = array_merge([
                                'first_name' => '',
                                'last_name' => '',
                                'gender' => 'Unknown',
                                'date_of_birth' => null,
                                'registration_date' => null,
                                'patient_number' => 'N/A',
                                'patient_id' => 0
                            ], $patient);
                            
                            // Format dates if they exist
                            $dob = !empty($patient['date_of_birth']) ? date('d-m-Y', strtotime($patient['date_of_birth'])) : 'N/A';
                            $regDate = !empty($patient['registration_date']) ? date('d-m-Y', strtotime($patient['registration_date'])) : 'N/A';
                            $fullName = trim(htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']));
                            $fullName = !empty($fullName) ? $fullName : 'Unnamed Patient';
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($patient['patient_number']) ?></td>
                                <td><?= $fullName ?></td>
                                <td><?= htmlspecialchars($patient['gender']) ?></td>
                                <td><?= $dob ?></td>
                                <td><?= $regDate ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= $baseUrl ?>/patients/view/<?= $patient['patient_id'] ?>" class="btn btn-xs btn-info" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="<?= $baseUrl ?>/patients/edit/<?= $patient['patient_id'] ?>" class="btn btn-xs btn-primary" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $baseUrl ?>/patients?page=<?= $currentPage - 1 ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>">
                                    Previous
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $baseUrl ?>/patients?page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $baseUrl ?>/patients?page=<?= $currentPage + 1 ?><?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>">
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
                <p>Showing <?= count($patients) ?> of <?= $totalPatients ?> patients</p>
            </div>
        </div>
    </div>
<?php endif; ?>
</div>

