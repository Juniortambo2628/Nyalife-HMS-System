<?php
/**
 * Nyalife HMS - Appointments Calendar View
 */
$pageTitle = 'Appointments Calendar - Nyalife HMS';
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Appointment Calendar</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= $baseUrl ?>/appointments" class="btn btn-secondary me-2">
                <i class="fas fa-list"></i> List View
            </a>
            <?php if (($userRole ?? '') === 'admin' || ($userRole ?? '') === 'nurse'): ?>
            <a href="<?= $baseUrl ?>/appointments/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Appointment
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Navigation Controls -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="btn-group">
                        <button type="button" id="prev-btn" class="btn btn-outline-primary">
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <button type="button" id="today-btn" class="btn btn-outline-primary">Today</button>
                        <button type="button" id="next-btn" class="btn btn-outline-primary">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <h3 id="calendar-title" class="mb-0"></h3>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group">
                        <button type="button" id="month-btn" class="btn btn-outline-secondary active">Month</button>
                        <button type="button" id="week-btn" class="btn btn-outline-secondary">Week</button>
                        <button type="button" id="day-btn" class="btn btn-outline-secondary">Day</button>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex justify-content-center align-items-center gap-3">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary me-2">&nbsp;</span> Scheduled
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success me-2">&nbsp;</span> Completed
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-warning me-2">&nbsp;</span> Pending
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-danger me-2">&nbsp;</span> Cancelled
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Container -->
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Appointment Quick View Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="appointmentModalLabel">Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading appointment details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="viewFullDetails" class="btn btn-primary">View Full Details</button>
            </div>
        </div>
    </div>
</div>

<!-- Load FullCalendar -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>

<script>
// Initialize components when the page is loaded or reloaded via AJAX
document.addEventListener('DOMContentLoaded', initCalendar);
document.addEventListener('page:loaded', initCalendar);

function initCalendar() {
    // Get calendar events from PHP variable
    const calendarEvents = <?= $calendarEvents ?? '[]' ?>;
    
    // Initialize FullCalendar
    const calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: false, // We're using custom buttons
            height: 'auto',
            aspectRatio: 1.5,
            dayMaxEvents: true,
            navLinks: true,
            editable: false,
            selectable: true,
            events: calendarEvents,
            
            // Handle date selection
            select: function(info) {
                const userRole = '<?= ($userRole ?? '') ?>';
                if (userRole === 'admin' || userRole === 'nurse') {
                    // Redirect to create appointment with selected date
                    const selectedDate = info.startStr;
                    window.location.href = '<?= $baseUrl ?>/appointments/create?date=' + selectedDate;
                }
            },
            
            // Handle event click
            eventClick: function(info) {
                const eventId = info.event.id;
                if (eventId) {
                    // Show appointment details in modal
                    showAppointmentDetails(eventId);
                }
            }
        });
        
        calendar.render();
        
        // Custom navigation buttons
        document.getElementById('prev-btn').addEventListener('click', function() {
            calendar.prev();
            updateCalendarTitle(calendar);
        });
        
        document.getElementById('next-btn').addEventListener('click', function() {
            calendar.next();
            updateCalendarTitle(calendar);
        });
        
        document.getElementById('today-btn').addEventListener('click', function() {
            calendar.today();
            updateCalendarTitle(calendar);
        });
        
        // View toggle buttons
        document.getElementById('month-btn').addEventListener('click', function() {
            calendar.changeView('dayGridMonth');
            updateViewButtons('month');
            updateCalendarTitle(calendar);
        });
        
        document.getElementById('week-btn').addEventListener('click', function() {
            calendar.changeView('timeGridWeek');
            updateViewButtons('week');
            updateCalendarTitle(calendar);
        });
        
        document.getElementById('day-btn').addEventListener('click', function() {
            calendar.changeView('timeGridDay');
            updateViewButtons('day');
            updateCalendarTitle(calendar);
        });
        
        // Update calendar title
        function updateCalendarTitle(calendar) {
            const title = calendar.view.title;
            document.getElementById('calendar-title').textContent = title;
        }
        
        // Update view buttons
        function updateViewButtons(activeView) {
            document.getElementById('month-btn').classList.toggle('active', activeView === 'month');
            document.getElementById('week-btn').classList.toggle('active', activeView === 'week');
            document.getElementById('day-btn').classList.toggle('active', activeView === 'day');
        }
        
        // Set initial title
        updateCalendarTitle(calendar);
    }
}

// Show appointment details in modal
function showAppointmentDetails(appointmentId) {
    const modal = new bootstrap.Modal(document.getElementById('appointmentModal'));
    modal.show();
    
    // Load appointment details via AJAX
    fetch(`<?= $baseUrl ?>/api/appointments/${appointmentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const appointment = data.appointment;
                const modalBody = document.querySelector('#appointmentModal .modal-body');
                
                modalBody.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Patient Information</h6>
                            <p><strong>Name:</strong> ${appointment.patient_name || 'N/A'}</p>
                            <p><strong>Phone:</strong> ${appointment.patient_phone || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Appointment Details</h6>
                            <p><strong>Date:</strong> ${appointment.appointment_date || 'N/A'}</p>
                            <p><strong>Time:</strong> ${appointment.appointment_time || 'N/A'}</p>
                            <p><strong>Status:</strong> <span class="badge bg-${getStatusColor(appointment.status)}">${appointment.status || 'N/A'}</span></p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Reason for Visit</h6>
                            <p>${appointment.reason || 'No reason provided'}</p>
                        </div>
                    </div>
                `;
                
                // Update view full details button
                document.getElementById('viewFullDetails').onclick = function() {
                    window.location.href = `<?= $baseUrl ?>/appointments/view/${appointmentId}`;
                };
            } else {
                document.querySelector('#appointmentModal .modal-body').innerHTML = 
                    '<div class="alert alert-danger">Failed to load appointment details.</div>';
            }
        })
        .catch(error => {
            console.error('Error loading appointment details:', error);
            document.querySelector('#appointmentModal .modal-body').innerHTML = 
                '<div class="alert alert-danger">Error loading appointment details.</div>';
        });
}

// Get status color for badges
function getStatusColor(status) {
    switch (status?.toLowerCase()) {
        case 'completed':
            return 'success';
        case 'cancelled':
            return 'danger';
        case 'pending':
            return 'warning';
        default:
            return 'primary';
    }
}
</script>
