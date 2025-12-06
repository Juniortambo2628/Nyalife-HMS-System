<?php
/**
 * Nyalife HMS - Prescription Form View
 */

$pageTitle = 'Prescription Form - Nyalife HMS';
?>
<div class="container-fluid">
<div class="row mb-2 mt-2">
    <div class="col-md-6">
        <h1><?= isset($prescription) ? 'Edit Prescription' : 'Create Prescription' ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= $baseUrl ?? '' ?>/prescriptions" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Prescriptions
        </a>
    </div>
</div>

<form id="prescription-form" action="<?= $baseUrl ?? '' ?>/prescriptions/store" method="post" class="needs-validation" novalidate>
    <?php if (isset($prescription) && !empty($prescription['prescription_id'])): ?>
        <input type="hidden" name="prescription_id" value="<?= $prescription['prescription_id'] ?>">
    <?php endif; ?>
    
    <?php if (isset($appointmentId) && $appointmentId): ?>
        <input type="hidden" name="appointment_id" value="<?= $appointmentId ?>">
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5>Patient Information</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($patient) && $patient): ?>
                        <input type="hidden" name="patient_id" value="<?= $patient['patient_id'] ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Patient Name</th>
                                        <td><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Patient Number</th>
                                        <td><?= htmlspecialchars($patient['patient_number'] ?? 'N/A') ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Gender</th>
                                        <td><?= htmlspecialchars($patient['gender'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Age</th>
                                        <td>
                                            <?php
                                                if (!empty($patient['date_of_birth'])) {
                                                    $dob = new DateTime($patient['date_of_birth']);
                                                    $now = new DateTime();
                                                    $age = $now->diff($dob)->y;
                                                    echo $age . ' years';
                                                } else {
                                                    echo 'N/A';
                                                }
?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <label for="patient-search" class="form-label">Search Patient</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="patient-search" placeholder="Search by name, patient number, or phone...">
                                <button class="btn btn-outline-secondary" type="button" id="search-patient-btn">
                                    <i class="fa fa-search"></i> Search
                                </button>
                            </div>
                            <div id="patient-results" class="mt-2"></div>
                            <input type="hidden" name="patient_id" id="patient_id" required>
                            <div class="invalid-feedback">Please select a patient.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-header">
                    <h5>Prescription Items</h5>
                </div>
                <div class="card-body">
                    <div id="medication-items">
                        <!-- Medication items will be added here -->
                    </div>
                    
                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" class="btn btn-success" id="add-medication-btn">
                            <i class="fa fa-plus"></i> Add Medication
                        </button>
                        
                        <?php if (!empty($commonMedications)): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="commonMedsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Quick Add Common Medications
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="commonMedsDropdown">
                                <?php foreach ($commonMedications as $med): ?>
                                <li>
                                    <a class="dropdown-item common-med-item" href="#" 
                                       data-id="<?= $med['medication_id'] ?>" 
                                       data-name="<?= htmlspecialchars($med['medication_name']) ?>"
                                       data-strength="<?= htmlspecialchars($med['strength'] ?? '') ?>"
                                       data-unit="<?= htmlspecialchars($med['unit'] ?? '') ?>">
                                        <?= htmlspecialchars($med['medication_name']) ?>
                                        <?php if (!empty($med['strength'])): ?>
                                            (<?= htmlspecialchars($med['strength']) ?> <?= htmlspecialchars($med['unit'] ?? '') ?>)
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <input type="hidden" name="items" id="prescription-items-json">
                    <div class="invalid-feedback" id="items-feedback">Please add at least one medication.</div>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-header">
                    <h5>Additional Notes</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <textarea class="form-control" name="notes" rows="3" placeholder="Enter any additional notes or instructions..."><?= htmlspecialchars($prescription['notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fa fa-save"></i> Save Prescription
                </button>
                <a href="<?= $baseUrl ?? '' ?>/prescriptions" class="btn btn-secondary btn-lg">
                    Cancel
                </a>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5>Medication Search</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="medication-search" class="form-label">Search Medication</label>
                        <input type="text" class="form-control" id="medication-search" placeholder="Enter medication name...">
                        <div id="medication-results" class="mt-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Medication Item Template -->
<template id="medication-item-template">
    <div class="card mb-3 medication-item">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h5 class="medication-name">Medication Name</h5>
                <button type="button" class="btn-close remove-medication" aria-label="Close"></button>
            </div>
            <input type="hidden" class="medication-id">
            
            <div class="row mt-2">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Dosage</label>
                        <input type="text" class="form-control dosage" placeholder="e.g., 1 tablet, 5ml" required>
                        <div class="invalid-feedback">Please enter dosage.</div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Frequency</label>
                        <select class="form-select frequency" required>
                            <option value="">Select frequency</option>
                            <?php foreach ($frequencies as $key => $label): ?>
                                <option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select frequency.</div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Duration</label>
                        <select class="form-select duration" required>
                            <option value="">Select duration</option>
                            <?php foreach ($durations as $key => $label): ?>
                                <option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select duration.</div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control quantity" placeholder="Total quantity">
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Special Instructions</label>
                <textarea class="form-control instructions" rows="2" placeholder="Any special instructions..."></textarea>
            </div>
        </div>
    </div>
</template>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const form = document.getElementById('prescription-form');
    const medicationItems = document.getElementById('medication-items');
    const addMedicationBtn = document.getElementById('add-medication-btn');
    const itemsJsonInput = document.getElementById('prescription-items-json');
    const template = document.getElementById('medication-item-template');
    const medicationSearch = document.getElementById('medication-search');
    const medicationResults = document.getElementById('medication-results');
    
    // Patient search if needed
    const patientSearch = document.getElementById('patient-search');
    const patientResults = document.getElementById('patient-results');
    const patientIdInput = document.getElementById('patient_id');
    
    let medications = [];
    
    // Initialize
    updateItemsJson();
    
    // Add medication button
    addMedicationBtn.addEventListener('click', function() {
        addMedicationItem();
    });
    
    // Common medications
    document.querySelectorAll('.common-med-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const strength = this.getAttribute('data-strength');
            const unit = this.getAttribute('data-unit');
            
            let displayName = name;
            if (strength && unit) {
                displayName += ` (${strength} ${unit})`;
            }
            
            addMedicationItem({
                id: id,
                name: displayName
            });
        });
    });
    
    // Medication search
    if (medicationSearch) {
        medicationSearch.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (query.length < 2) {
                medicationResults.innerHTML = '';
                return;
            }
            
            // Fetch medications based on search
            fetch('<?= $baseUrl ?? '' ?>/api/medications/search?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    medicationResults.innerHTML = '';
                    
                    if (data.length === 0) {
                        medicationResults.innerHTML = '<div class="alert alert-info">No medications found</div>';
                    } else {
                        const list = document.createElement('div');
                        list.className = 'list-group';
                        
                        data.forEach(med => {
                            const item = document.createElement('button');
                            item.type = 'button';
                            item.className = 'list-group-item list-group-item-action';
                            
                            let displayName = med.medication_name;
                            if (med.strength && med.unit) {
                                displayName += ` (${med.strength} ${med.unit})`;
                            }
                            
                            item.textContent = displayName;
                            
                            item.addEventListener('click', function() {
                                addMedicationItem({
                                    id: med.medication_id,
                                    name: displayName
                                });
                                medicationResults.innerHTML = '';
                                medicationSearch.value = '';
                            });
                            
                            list.appendChild(item);
                        });
                        
                        medicationResults.appendChild(list);
                    }
                })
                .catch(error => {
                    console.error('Error fetching medications:', error);
                    medicationResults.innerHTML = '<div class="alert alert-danger">Error loading medications</div>';
                });
        });
    }
    
    // Patient search
    if (patientSearch) {
        patientSearch.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (query.length < 2) {
                patientResults.innerHTML = '';
                return;
            }
            
            // Fetch patients based on search
            fetch('<?= $baseUrl ?? '' ?>/api/patients/search?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    patientResults.innerHTML = '';
                    
                    if (data.length === 0) {
                        patientResults.innerHTML = '<div class="alert alert-info">No patients found</div>';
                    } else {
                        const list = document.createElement('div');
                        list.className = 'list-group';
                        
                        data.forEach(patient => {
                            const item = document.createElement('button');
                            item.type = 'button';
                            item.className = 'list-group-item list-group-item-action';
                            item.textContent = `${patient.first_name} ${patient.last_name} (${patient.patient_number})`;
                            
                            item.addEventListener('click', function() {
                                patientIdInput.value = patient.patient_id;
                                patientSearch.value = `${patient.first_name} ${patient.last_name} (${patient.patient_number})`;
                                patientResults.innerHTML = '';
                                
                                // Add patient info display
                                const infoDiv = document.createElement('div');
                                infoDiv.className = 'alert alert-success mt-2';
                                infoDiv.innerHTML = `<strong>Selected Patient:</strong> ${patient.first_name} ${patient.last_name} (${patient.patient_number})`;
                                patientResults.appendChild(infoDiv);
                            });
                            
                            list.appendChild(item);
                        });
                        
                        patientResults.appendChild(list);
                    }
                })
                .catch(error => {
                    console.error('Error fetching patients:', error);
                    patientResults.innerHTML = '<div class="alert alert-danger">Error loading patients</div>';
                });
        });
    }
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }
        
        // Prepare data
        updateItemsJson();
        
        // Submit form via AJAX
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to view page
                window.location.href = data.redirect_url;
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the prescription.');
        });
    });
    
    // Functions
    function addMedicationItem(medication = null) {
        const clone = document.importNode(template.content, true);
        const item = clone.querySelector('.medication-item');
        
        if (medication) {
            item.querySelector('.medication-name').textContent = medication.name;
            item.querySelector('.medication-id').value = medication.id;
        }
        
        // Add event listener for remove button
        item.querySelector('.remove-medication').addEventListener('click', function() {
            item.remove();
            updateItemsJson();
        });
        
        // Add to DOM
        medicationItems.appendChild(item);
        
        // Add to medications array
        medications.push(item);
        
        // Update JSON input
        updateItemsJson();
    }
    
    function updateItemsJson() {
        const items = [];
        
        document.querySelectorAll('.medication-item').forEach(item => {
            const medicationId = item.querySelector('.medication-id').value;
            const dosage = item.querySelector('.dosage').value;
            const frequency = item.querySelector('.frequency').value;
            const duration = item.querySelector('.duration').value;
            const quantity = item.querySelector('.quantity').value;
            const instructions = item.querySelector('.instructions').value;
            
            items.push({
                medication_id: medicationId,
                dosage: dosage,
                frequency: frequency,
                duration: duration,
                quantity: quantity,
                instructions: instructions
            });
        });
        
        itemsJsonInput.value = JSON.stringify(items);
    }
    
    function validateForm() {
        let isValid = true;
        form.classList.add('was-validated');
        
        // Check if patient is selected
        if (patientIdInput && patientIdInput.value === '') {
            isValid = false;
        }
        
        // Check if medications are added
        const items = document.querySelectorAll('.medication-item');
        if (items.length === 0) {
            document.getElementById('items-feedback').style.display = 'block';
            isValid = false;
        } else {
            document.getElementById('items-feedback').style.display = 'none';
            
            // Validate each medication item
            items.forEach(item => {
                const dosage = item.querySelector('.dosage').value;
                const frequency = item.querySelector('.frequency').value;
                const duration = item.querySelector('.duration').value;
                
                if (!dosage || !frequency || !duration) {
                    isValid = false;
                }
            });
        }
        
        return isValid;
    }
    
    // Event delegation for form fields
    medicationItems.addEventListener('change', function(e) {
        if (e.target.matches('.dosage, .frequency, .duration, .quantity, .instructions')) {
            updateItemsJson();
        }
    });
});
</script> 