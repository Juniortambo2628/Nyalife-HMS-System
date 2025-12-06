<?php
/**
 * Nyalife HMS - Edit Medicine View
 *
 * View for editing existing medications.
 */

$pageTitle = 'Edit Medicine - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-pills fa-fw"></i> Edit Medicine
        </h1>
        <div>
            <a href="<?= $baseUrl ?>/pharmacy/medicines/show/<?= $medication['medication_id'] ?>" 
               class="btn btn-info btn-sm">
                <i class="fas fa-eye fa-fw"></i> View Medicine
            </a>
            <a href="<?= $baseUrl ?>/pharmacy/medicines" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left fa-fw"></i> Back to Medicines
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php include __DIR__ . '/../../components/flash_messages.php'; ?>

    <!-- Edit Medicine Form -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Medicine Information</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= $baseUrl ?>/pharmacy/medicines/update/<?= $medication['medication_id'] ?>" id="medicine-form">
                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="medication_name">Medicine Name <span class="text-danger">*</span></label>
                            <input type="text" name="medication_name" id="medication_name" 
                                   class="form-control" value="<?= htmlspecialchars($medication['medication_name']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="generic_name">Generic Name</label>
                            <input type="text" name="generic_name" id="generic_name" 
                                   class="form-control" value="<?= htmlspecialchars($medication['generic_name'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select name="category" id="category" class="form-control">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category ?>" <?= ($medication['medication_type'] === $category) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="form">Form <span class="text-danger">*</span></label>
                            <select name="form" id="form" class="form-control" required>
                                <option value="">Select Form</option>
                                <?php foreach ($forms as $form): ?>
                                    <option value="<?= $form ?>" <?= ($medication['form'] === $form) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($form) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="manufacturer">Manufacturer</label>
                            <input type="text" name="manufacturer" id="manufacturer" 
                                   class="form-control" value="<?= htmlspecialchars($medication['manufacturer'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="strength">Strength <span class="text-danger">*</span></label>
                            <input type="text" name="strength" id="strength" 
                                   class="form-control" value="<?= htmlspecialchars($medication['strength']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="unit">Unit <span class="text-danger">*</span></label>
                            <select name="unit" id="unit" class="form-control" required>
                                <option value="">Select Unit</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?= $unit ?>" <?= ($medication['unit'] === $unit) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($unit) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="price">Price</label>
                            <input type="number" name="price" id="price" 
                                   class="form-control" step="0.01" min="0" 
                                   value="<?= htmlspecialchars($medication['price'] ?? '0') ?>">
                        </div>
                    </div>
                </div>

                <!-- Description and Details -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control" 
                                      rows="3" placeholder="Medicine description..."><?= htmlspecialchars($medication['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="side_effects">Side Effects</label>
                            <textarea name="side_effects" id="side_effects" class="form-control" 
                                      rows="3" placeholder="Common side effects..."><?= htmlspecialchars($medication['side_effects'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="contraindications">Contraindications</label>
                            <textarea name="contraindications" id="contraindications" class="form-control" 
                                      rows="3" placeholder="Contraindications..."><?= htmlspecialchars($medication['contraindications'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                                       <?= $medication['is_active'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="is_active">Active Medicine</label>
                            </div>
                            <small class="form-text text-muted">Inactive medicines will not appear in prescriptions or inventory.</small>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save fa-fw"></i> Update Medicine
                    </button>
                    <a href="<?= $baseUrl ?>/pharmacy/medicines/show/<?= $medication['medication_id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-times fa-fw"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('medicine-form');
    
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
    
    // Price formatting
    const priceInput = document.getElementById('price');
    priceInput.addEventListener('input', function() {
        const value = parseFloat(this.value);
        if (!isNaN(value) && value >= 0) {
            this.value = value.toFixed(2);
        }
    });
    
    // Auto-generate generic name from medicine name if empty
    const medicineNameInput = document.getElementById('medication_name');
    const genericNameInput = document.getElementById('generic_name');
    
    medicineNameInput.addEventListener('blur', function() {
        if (!genericNameInput.value.trim()) {
            const medicineName = this.value.trim();
            if (medicineName) {
                genericNameInput.value = medicineName;
            }
        }
    });
});
</script> 