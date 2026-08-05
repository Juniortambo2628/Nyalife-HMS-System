import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

export default function Calendar({ appointments, auth }) {
    const calendarEvents = appointments.map((apt) => ({
        id: apt.id,
        title: apt.title,
        start: apt.start,
        backgroundColor: apt.status === 'cancelled' ? '#dc3545' : apt.status === 'completed' ? '#198754' : '#0d6efd',
        borderColor: 'transparent',
        textColor: '#fff',
        extendedProps: {
            status: apt.status,
        },
    }));

    const handleEventClick = (info) => {
        window.location.href = route('appointments.show', info.event.id);
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            headerTitle="Schedules & Appointments"
            breadcrumbs={[
                { label: 'Appointments', url: route('appointments.index') },
                { label: 'Calendar View', active: true },
            ]}
        >
            <Head title="Appointments Calendar" />

            <UnifiedToolbar
                actions={[
                    {
                        label: 'List view',
                        icon: 'fa-list',
                        href: route('appointments.index'),
                        color: 'gray',
                    },
                    auth.user.role !== 'patient' && {
                        label: 'Book visit',
                        icon: 'fa-plus',
                        href: route('appointments.create'),
                    },
                ].filter(Boolean)}
            />

            <div className="container-fluid appointments-page px-0">
                <div className="card shadow-sm border-0 rounded-2xl">
                    <div className="card-body p-4">
                        <FullCalendar
                            plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin]}
                            initialView="dayGridMonth"
                            headerToolbar={{
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay',
                            }}
                            events={calendarEvents}
                            eventClick={handleEventClick}
                            eventTimeFormat={{
                                hour: 'numeric',
                                minute: '2-digit',
                                meridiem: 'short',
                            }}
                            height="auto"
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
