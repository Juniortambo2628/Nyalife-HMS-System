<?php
/**
 * Nyalife HMS - Update Lab Test Results View
 */

$pageTitle = 'Update Lab Test Results - Nyalife HMS';
?>
<div class="container-fluid">
<div class="row mb-4">
    <div class="col-md-6">
        <h1>Update Test Results</h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= $baseUrl ?? '' ?>/lab/samples/results/<?= htmlspecialchars($sample['sample_id'] ?? '') ?>" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left"></i> Back to Results
        </a>
        <a href="<?= $baseUrl ?? '' ?>/lab-tests/manage" class="btn btn-outline-secondary">
            <i class="fas fa-list"></i> Manage Samples
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Test Results for <?= htmlspecialchars($sample['sample_id'] ?? '') ?></h5>
            </div>
            <div class="card-body">
                <form method="post" action="<?= $baseUrl ?? '' ?>/lab-tests/update-result/<?= htmlspecialchars($sample['sample_id'] ?? '') ?>" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="in_progress" <?= ($sample['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="pending_results" <?= ($sample['status'] ?? '') === 'pending_results' ? 'selected' : '' ?>>Pending Results</option>
                                <option value="completed" <?= ($sample['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="2"><?= htmlspecialchars($sample['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <!-- File Upload Section -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="attachments" class="form-label">Upload Attachments (Images, Documents)</label>
                            <input type="file" name="attachments[]" id="attachments" class="form-control" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                            <small class="text-muted">Supported formats: Images (JPG, PNG, GIF), Documents (PDF, DOC, DOCX, XLS, XLSX, TXT). Max 10MB per file.</small>
                        </div>
                    </div>

                    <?php if (!empty($parameters)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Result</th>
                                    <th>Unit</th>
                                    <th>Reference Range</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($parameters as $parameter): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($parameter['parameter_name'] ?? '') ?></strong>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="results[<?= htmlspecialchars($parameter['parameter_id'] ?? '') ?>]" 
                                               class="form-control" 
                                               value="<?= htmlspecialchars($existingResults[$parameter['parameter_id']] ?? '') ?>"
                                               placeholder="Enter result">
                                    </td>
                                    <td><?= htmlspecialchars($parameter['unit'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($parameter['reference_range'] ?? '') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No parameters defined for this test type.
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($attachments)): ?>
                    <div class="row mb-3 mt-3">
                        <div class="col-md-12">
                            <h6>Existing Attachments:</h6>
                            <div class="row">
                                <?php foreach ($attachments as $attachment): ?>
                                <div class="col-md-3 mb-2">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <?php if ($attachment['file_type'] === 'image'): ?>
                                                <img src="<?= $baseUrl ?? '' ?><?= $attachment['file_path'] ?>" 
                                                     class="img-thumbnail" 
                                                     style="max-height: 100px; cursor: pointer;"
                                                     onclick="window.open('<?= $baseUrl ?? '' ?><?= $attachment['file_path'] ?>', '_blank')">
                                            <?php else: ?>
                                                <i class="fas fa-file fa-3x text-primary"></i>
                                            <?php endif; ?>
                                            <p class="small mb-0 mt-1"><?= htmlspecialchars($attachment['file_name']) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Results
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Sample Information</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Sample ID:</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($sample['sample_id'] ?? '') ?></dd>
                    
                    <dt class="col-sm-4">Test Type:</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($testType['test_name'] ?? '') ?></dd>
                    
                    <dt class="col-sm-4">Sample Type:</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars(ucfirst($sample['sample_type'] ?? '')) ?></dd>
                    
                    <dt class="col-sm-4">Collection Date:</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars(date('M d, Y', strtotime($sample['collected_date'] ?? ''))) ?></dd>
                    
                    <dt class="col-sm-4">Current Status:</dt>
                    <dd class="col-sm-8">
                        <?php
                        $statusClass = '';
$statusText = '';
switch ($sample['status'] ?? '') {
    case 'registered':
        $statusClass = 'bg-info';
        $statusText = 'Registered';
        break;
    case 'in_progress':
        $statusClass = 'bg-warning';
        $statusText = 'In Progress';
        break;
    case 'pending_results':
        $statusClass = 'bg-primary';
        $statusText = 'Pending Results';
        break;
    case 'completed':
        $statusClass = 'bg-success';
        $statusText = 'Completed';
        break;
    case 'cancelled':
        $statusClass = 'bg-danger';
        $statusText = 'Cancelled';
        break;
    default:
        $statusClass = 'bg-secondary';
        $statusText = 'Unknown';
}
?>
                        <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                    </dd>
                </dl>
            </div>
        </div>
        
        <?php if (!empty($sample['notes'])): ?>
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Current Notes</h5>
            </div>
            <div class="card-body">
                <p class="text-muted"><?= htmlspecialchars($sample['notes']) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>

<script>
// Handle form submission with AJAX to provide better error handling
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[method="post"]');
    
    if (form) {
        // Handle file input change to show selected files
        const fileInput = document.getElementById('attachments');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const files = this.files;
                if (files.length > 0) {
                    let fileList = '<div class="mt-2"><strong>Selected files:</strong><ul class="small">';
                    for (let i = 0; i < files.length; i++) {
                        const fileSize = (files[i].size / 1024 / 1024).toFixed(2);
                        fileList += `<li>${files[i].name} (${fileSize} MB)</li>`;
                    }
                    fileList += '</ul></div>';
                    
                    // Remove existing file list if present
                    const existingList = document.getElementById('selected-files');
                    if (existingList) {
                        existingList.remove();
                    }
                    
                    // Add new file list
                    fileInput.insertAdjacentHTML('afterend', `<div id="selected-files">${fileList}</div>`);
                }
            });
        }
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            
            // Validate file sizes
            const fileInput = document.getElementById('attachments');
            if (fileInput && fileInput.files.length > 0) {
                let hasError = false;
                for (let i = 0; i < fileInput.files.length; i++) {
                    const file = fileInput.files[i];
                    if (file.size > 10 * 1024 * 1024) { // 10MB
                        alert(`File "${file.name}" is too large. Maximum size is 10MB.`);
                        hasError = true;
                        break;
                    }
                }
                if (hasError) return;
            }
            
            // Disable submit button
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            // Show loader
            if (window.NyalifeLoader) {
                window.NyalifeLoader.show('Updating test results...');
            }
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                // Check if response is JSON or HTML
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    // If HTML response, it's likely an error page
                    return response.text().then(text => {
                        throw new Error('Server returned HTML instead of JSON. Likely a PHP error.');
                    });
                }
            })
            .then(data => {
                // Hide loader
                if (window.NyalifeLoader) {
                    window.NyalifeLoader.hide();
                }
                
                if (data.success || data.redirect) {
                    // Success - redirect
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else if (data.success) {
                        window.location.href = form.action.replace('/update-result/', '/view/');
                    }
                } else {
                    // Show error
                    alert(data.message || 'Failed to update test results');
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
                
                // Hide loader
                if (window.NyalifeLoader) {
                    window.NyalifeLoader.hide();
                }
                
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        });
    }
});
</script>