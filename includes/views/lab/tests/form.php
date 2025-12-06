<?php
/**
 * Nyalife HMS - Lab Test Form
 */

$pageTitle = 'Lab Test Form - Nyalife HMS';
?>

<div class="container-fluid">
 <div class="row mb-4">
        <div class="col-md-6">
            <h1><?= isset($edit) && $edit ? 'Edit Test Type' : 'Add New Test Type' ?></h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= $baseUrl ?? '' ?>/lab/tests" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="post" action="<?= $baseUrl ?? '' ?>/lab/tests/<?= isset($edit) && $edit ? 'update/' . ($test['test_type_id'] ?? '') : 'store' ?>" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="test_name" class="form-label">Test Name *</label>
                            <input type="text" name="test_name" id="test_name" class="form-control" 
                                   value="<?= htmlspecialchars($test['test_name'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category" class="form-label">Category *</label>
                            <select name="category" id="category" class="form-select" required>
                                <option value="">Select Category</option>
                                <option value="Hematology" <?= ($test['category'] ?? '') === 'Hematology' ? 'selected' : '' ?>>Hematology</option>
                                <option value="Chemistry" <?= ($test['category'] ?? '') === 'Chemistry' ? 'selected' : '' ?>>Chemistry</option>
                                <option value="Microbiology" <?= ($test['category'] ?? '') === 'Microbiology' ? 'selected' : '' ?>>Microbiology</option>
                                <option value="Serology" <?= ($test['category'] ?? '') === 'Serology' ? 'selected' : '' ?>>Serology</option>
                                <option value="Pathology" <?= ($test['category'] ?? '') === 'Pathology' ? 'selected' : '' ?>>Pathology</option>
                                <option value="Reproductive" <?= ($test['category'] ?? '') === 'Reproductive' ? 'selected' : '' ?>>Reproductive</option>
                                <option value="General" <?= ($test['category'] ?? '') === 'General' ? 'selected' : '' ?>>General</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="price" class="form-label">Price *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="price" id="price" class="form-control" 
                                       value="<?= htmlspecialchars($test['price'] ?? '') ?>" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3"><?= htmlspecialchars($test['description'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="normal_range" class="form-label">Normal Range</label>
                            <input type="text" name="normal_range" id="normal_range" class="form-control" 
                                   value="<?= htmlspecialchars($test['normal_range'] ?? '') ?>" placeholder="e.g., 70-99 mg/dL">
                        </div>
                        
                        <div class="mb-3">
                            <label for="units" class="form-label">Units</label>
                            <input type="text" name="units" id="units" class="form-control" 
                                   value="<?= htmlspecialchars($test['units'] ?? '') ?>" placeholder="e.g., mg/dL, mmol/L">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="instructions_file" class="form-label">Instructions File</label>
                    <input type="file" name="instructions_file" id="instructions_file" class="form-control" accept=".pdf,.doc,.docx">
                    <?php if (!empty($test['instructions_file'])): ?>
                        <div class="form-text">
                            Current file: <?= htmlspecialchars($test['instructions_file']) ?>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="remove_instructions_file" value="1" id="remove_instructions_file">
                                <label class="form-check-label" for="remove_instructions_file">
                                    Remove current file
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" 
                               <?= (!isset($test['is_active']) || $test['is_active'] == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">
                            Active
                        </label>
                    </div>
                </div>
                
                <!-- Test Parameters Section -->
                <div class="mb-4">
                    <h5>Test Parameters</h5>
                    <div id="parameters-container">
                        <?php if (!empty($parameters)): ?>
                            <?php foreach ($parameters as $index => $parameter): ?>
                            <div class="row parameter-row mb-2">
                                <div class="col-md-3">
                                    <input type="text" name="parameter_names[]" class="form-control" 
                                           value="<?= htmlspecialchars($parameter['parameter_name'] ?? '') ?>" placeholder="Parameter name">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="parameter_units[]" class="form-control" 
                                           value="<?= htmlspecialchars($parameter['unit'] ?? '') ?>" placeholder="Unit">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="parameter_ranges[]" class="form-control" 
                                           value="<?= htmlspecialchars($parameter['reference_range'] ?? '') ?>" placeholder="Reference range">
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="parameter_sequences[]" class="form-control" 
                                           value="<?= htmlspecialchars($parameter['sequence'] ?? '1') ?>" placeholder="Sequence">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-parameter">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-parameter">
                        <i class="fas fa-plus"></i> Add Parameter
                    </button>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                    <button type="submit" class="btn btn-primary">
                        <?= isset($edit) && $edit ? 'Update Test Type' : 'Create Test Type' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addParameterBtn = document.getElementById('add-parameter');
    const parametersContainer = document.getElementById('parameters-container');
    
    // Add parameter row
    addParameterBtn.addEventListener('click', function() {
        const parameterRow = document.createElement('div');
        parameterRow.className = 'row parameter-row mb-2';
        parameterRow.innerHTML = `
            <div class="col-md-3">
                <input type="text" name="parameter_names[]" class="form-control" placeholder="Parameter name">
            </div>
            <div class="col-md-2">
                <input type="text" name="parameter_units[]" class="form-control" placeholder="Unit">
            </div>
            <div class="col-md-3">
                <input type="text" name="parameter_ranges[]" class="form-control" placeholder="Reference range">
            </div>
            <div class="col-md-2">
                <input type="number" name="parameter_sequences[]" class="form-control" value="1" placeholder="Sequence">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger btn-sm remove-parameter">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        parametersContainer.appendChild(parameterRow);
    });
    
    // Remove parameter row
    parametersContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-parameter') || e.target.closest('.remove-parameter')) {
            e.target.closest('.parameter-row').remove();
        }
    });
});
</script>