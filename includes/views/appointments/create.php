<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Schedule New Appointment</h1>
        <a href="<?= $baseUrl ?>/appointments" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> Back to Appointments
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Appointment Details</h6>
        </div>
        <div class="card-body">
            <form action="<?= $baseUrl ?>/appointments/store" method="post" id="appointmentForm">
                <!-- Patient Selection -->
                <div class="mb-3">
                    <label for="patient_id" class="form-label">Patient</label>
                    <?php if ($userRole === 'patient'): ?>
                        <input type="hidden" name="patient_id" value="<?= $selectedPatientId ?>">
                        <input type="text" class="form-control" value="<?= htmlspecialchars($patients[0]['first_name'] . ' ' . $patients[0]['last_name']) ?>" readonly>
                    <?php else: ?>
                        <select class="form-select" id="patient_id" name="patient_id" required>
                            <option value="">Select Patient</option>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?= $patient['patient_id'] ?>">
                                    <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?> - <?= htmlspecialchars($patient['email']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                
                <!-- Doctor Selection -->
                <div class="mb-3">
                    <label for="doctor_id" class="form-label">Doctor</label>
                    <select class="form-select" id="doctor_id" name="doctor_id" required>
                        <option value="">Select Doctor</option>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?= $doctor['user_id'] ?>">
                                Dr. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?> - <?= htmlspecialchars($doctor['specialization']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Date and Time Selection -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="appointment_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="appointment_date" name="appointment_date" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="appointment_time" class="form-label">Time</label>
                        <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
                    </div>
                </div>
                
                <!-- Reason for Appointment -->
                <div class="mb-3">
                    <label for="reason" class="form-label">Reason for Appointment</label>
                    <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                </div>
                
                <!-- Available Time Slots (will be populated via AJAX when doctor is selected) -->
                <div class="mb-4" id="time-slots-container" style="display: none;">
                    <label class="form-label">Available Time Slots</label>
                    <div id="time-slots" class="d-flex flex-wrap gap-2">
                        <!-- Time slots will be populated here -->
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-outline-secondary">Reset</button>
                    <button type="submit" class="btn btn-primary">Schedule Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const doctorSelect = document.getElementById('doctor_id');
    const dateInput = document.getElementById('appointment_date');
    const timeInput = document.getElementById('appointment_time');
    const timeSlotsContainer = document.getElementById('time-slots-container');
    const timeSlots = document.getElementById('time-slots');
    
    // Function to check doctor availability
    function checkAvailability() {
        const doctorId = doctorSelect.value;
        const appointmentDate = dateInput.value;
        
        if (!doctorId || !appointmentDate) {
            timeSlotsContainer.style.display = 'none';
            return;
        }
        
        // Show loading indicator
        timeSlots.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
        timeSlotsContainer.style.display = 'block';
        
        // In a real implementation, this would make an AJAX call to the server
        // For now, we'll simulate available time slots
        setTimeout(() => {
            // This would normally be returned from the server
            const availableSlots = [
                '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
                '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'
            ];
            
            // Clear loading indicator
            timeSlots.innerHTML = '';
            
            if (availableSlots.length === 0) {
                timeSlots.innerHTML = '<div class="alert alert-info">No available time slots for the selected date.</div>';
                return;
            }
            
            // Create buttons for each time slot
            availableSlots.forEach(slot => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn btn-outline-primary time-slot-btn';
                button.textContent = slot;
                button.addEventListener('click', () => {
                    timeInput.value = slot;
                    // Remove active class from all buttons
                    document.querySelectorAll('.time-slot-btn').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    // Add active class to the clicked button
                    button.classList.add('active');
                });
                timeSlots.appendChild(button);
            });
        }, 1000);
    }
    
    // Add event listeners
    doctorSelect.addEventListener('change', checkAvailability);
    dateInput.addEventListener('change', checkAvailability);
    
    // Form validation
    const appointmentForm = document.getElementById('appointmentForm');
    appointmentForm.addEventListener('submit', function(e) {
        if (!timeInput.value) {
            e.preventDefault();
            alert('Please select an appointment time');
        }
    });
});
</script>
