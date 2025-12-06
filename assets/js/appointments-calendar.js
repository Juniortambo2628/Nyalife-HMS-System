/**
 * Appointments Calendar JavaScript
 */
function initCalendar() {
    // Get calendar events from window variable (set by PHP)
    const calendarEvents = window.calendarEvents || [];
    
    // Initialize FullCalendar
    const calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        const baseUrl = window.baseUrl || '';
        const userRole = window.userRole || '';
        
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
                if (userRole === 'admin' || userRole === 'nurse') {
                    // Redirect to create appointment with selected date
                    const selectedDate = info.startStr;
                    window.location.href = baseUrl + '/appointments/create?date=' + selectedDate;
                }
            },

            // Handle event click
            eventClick: function(info) {
                const eventId = info.event.id;
                if (eventId) {
                    // Show appointment details in modal
                    if (typeof showAppointmentDetails === 'function') {
                        showAppointmentDetails(eventId);
                    }
                }
            }
        });

        calendar.render();

        // Custom navigation buttons
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                calendar.prev();
                if (typeof updateCalendarTitle === 'function') {
                    updateCalendarTitle(calendar);
                }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                calendar.next();
                if (typeof updateCalendarTitle === 'function') {
                    updateCalendarTitle(calendar);
                }
            });
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initCalendar);
document.addEventListener('page:loaded', initCalendar);

