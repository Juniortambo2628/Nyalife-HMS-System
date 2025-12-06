<?php
/**
 * Nyalife HMS - Patients List View
 */

$pageTitle = 'Patients List - Nyalife HMS';
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
                <h1>Patients</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="<?= $baseUrl ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
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

        <!-- Patients List -->
        <div class="card">
            <div class="container py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="<?= $baseUrl ?>/patients/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Patient
                    </a>
                </div>

                <!-- Filters Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Patients</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" action="<?= $baseUrl ?>/patients" class="row g-3" id="filter-form">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Name, ID, Phone, Email" value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                            </div>

                            <div class="col-md-3">
                                <label for="filter-gender" class="form-label">Gender</label>
                                <select name="gender" id="filter-gender" class="form-select">
                                    <option value="">All</option>
                                    <option value="male" <?= ($filters['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= ($filters['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                    <option value="other" <?= ($filters['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="min_age" class="form-label">Min Age</label>
                                <input type="number" class="form-control" id="min_age" name="min_age" min="0" max="120" 
                                       value="<?= htmlspecialchars($filters['min_age'] ?? '') ?>">
                            </div>

                            <div class="col-md-3">
                                <label for="max_age" class="form-label">Max Age</label>
                                <input type="number" class="form-control" id="max_age" name="max_age" min="0" max="120" 
                                       value="<?= htmlspecialchars($filters['max_age'] ?? '') ?>">
                            </div>

                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i> Apply Filters
                                </button>
                                <a href="<?= $baseUrl ?>/patients" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Patients Table -->
                <?php if (empty($patients)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> No patients found matching your criteria.
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 datatable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Patient ID</th>
                                            <th>Name</th>
                                            <th>Gender</th>
                                            <th>Age</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Source</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($patients as $patient): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($patient['patient_id'] ?? '') ?></td>
                                                <td>
                                                    <a href="<?= $baseUrl ?>/patients/view/<?= $patient['patient_id'] ?>" class="text-decoration-none">
                                                        <?= htmlspecialchars(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')) ?>
                                                    </a>
                                                </td>
                                                <td><?= isset($patient['gender']) ? htmlspecialchars(ucfirst($patient['gender'])) : '' ?></td>
                                                <td><?= isset($patient['date_of_birth']) ? htmlspecialchars(calculateAge($patient['date_of_birth'])) : '' ?></td>
                                                <td><?= htmlspecialchars($patient['phone'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($patient['email'] ?? '') ?></td>
                                                <td>
                                                    <?php $ps = $patient['source_label'] ?? 'Internal'; ?>
                                                    <span class="badge <?= strtolower($ps) === 'guest' ? 'bg-warning text-dark' : 'bg-secondary' ?>">
                                                        <?= htmlspecialchars($ps) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="<?= $baseUrl ?>/patients/view/<?= $patient['patient_id'] ?>" class="btn btn-outline-primary" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="<?= $baseUrl ?>/patients/edit/<?= $patient['patient_id'] ?>" class="btn btn-outline-secondary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<!-- end duplicate wrapper removed -->

<?php
// Add page specific script
$pageSpecificScripts[] = AssetHelper::getJs('patients-index');
?>

