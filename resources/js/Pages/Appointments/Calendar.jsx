import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import PageHeader from '@/Components/PageHeader';

export default function Calendar({ appointments, auth }) {
    const calendarEvents = appointments.map(apt => ({
        id: apt.id,
        title: apt.title,
        start: apt.start,
        backgroundColor: apt.status === 'cancelled' ? '#dc3545' : (apt.status === 'completed' ? '#198754' : '#0d6efd'),
        borderColor: 'transparent',
        textColor: '#fff',
        extendedProps: {
            status: apt.status
        }
    }));

    const handleEventClick = (info) => {
        // Simple navigation to appointment show page
        window.location.href = route('appointments.show', info.event.id);
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header="Appointments Calendar"
        >
            <Head title="Appointments Calendar" />

            <PageHeader 
                title="Schedules & Appointments"
                breadcrumbs={[
                    { label: 'Appointments', url: route('appointments.index') },
                    { label: 'Calendar View', active: true }
                ]}
                actions={
                    <Link href={route('appointments.index')} className="btn btn-outline-secondary rounded-pill px-4 shadow-sm fw-bold">
                        <i className="fas fa-list me-2"></i>List View
                    </Link>
                }
            />

            <div className="container-fluid appointments-page px-0">
                <div className="card shadow-sm border-0">
                    <div className="card-body p-4">
                        <FullCalendar
                            plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin]}
                            initialView="dayGridMonth"
                            headerToolbar={{
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            }}
                            events={calendarEvents}
                            eventClick={handleEventClick}
                            eventTimeFormat={{
                                hour: 'numeric',
                                minute: '2-digit',
                                meridiem: 'short'
                            }}
                            height="auto"
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
