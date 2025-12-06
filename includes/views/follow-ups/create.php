<?php
/**
 * Nyalife HMS - Create Follow-up View
 *
 * View for creating new follow-ups.
 */

$pageTitle = 'Create Follow-up - Nyalife HMS';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-plus fa-fw"></i> Create Follow-up
        </h1>
        <a href="<?= $baseUrl ?>/follow-ups" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left fa-fw"></i> Back to Follow-ups
        </a>
    </div>

    <!-- Create Follow-up Form -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Follow-up Information</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= $baseUrl ?>/follow-ups/store" id="follow-up-form">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="patient_id">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" id="patient_id" class="form-select" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?= $patient['patient_id'] ?>">
                                        <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?> (<?= htmlspecialchars($patient['patient_id']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="consultation_id">Related Consultation</label>
                            <select name="consultation_id" id="consultation_id" class="form-select">
                                <option value="">Select Consultation (Optional)</option>
                                <?php foreach ($consultations as $consultation): ?>
                                    <option value="<?= $consultation['consultation_id'] ?>">
                                        <?= date('M j, Y', strtotime($consultation['consultation_date'])) ?> - <?= htmlspecialchars($consultation['diagnosis']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="follow_up_date">Follow-up Date <span class="text-danger">*</span></label>
                            <input type="date" name="follow_up_date" id="follow_up_date" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="follow_up_time">Follow-up Time</label>
                            <input type="time" name="follow_up_time" id="follow_up_time" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="follow_up_type">Follow-up Type <span class="text-danger">*</span></label>
                            <select name="follow_up_type" id="follow_up_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="general">General Check-up</option>
                                <option value="post_surgery">Post-Surgery</option>
                                <option value="medication_review">Medication Review</option>
                                <option value="lab_results">Lab Results Review</option>
                                <option value="specialist_referral">Specialist Referral</option>
                                <option value="emergency">Emergency Follow-up</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="priority">Priority <span class="text-danger">*</span></label>
                            <select name="priority" id="priority" class="form-select" required>
                                <option value="">Select Priority</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reason">Reason for Follow-up <span class="text-danger">*</span></label>
                    <textarea name="reason" id="reason" class="form-control" rows="3" required></textarea>
                </div>

                <div class="form-group">
                    <label for="notes">Additional Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="scheduled">Scheduled</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save fa-fw"></i> Create Follow-up
                    </button>
                    <a href="<?= $baseUrl ?>/follow-ups" class="btn btn-secondary">
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
    const form = document.getElementById('follow-up-form');
    
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
    
    // Set default date to today
    const dateInput = document.getElementById('follow_up_date');
    if (dateInput && !dateInput.value) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
});
</script>