import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import InfoModal from '@/Components/InfoModal';
import PageHeader from '@/Components/PageHeader';
import ViewToggle from '@/Components/ViewToggle';
import DashboardSelect from '@/Components/DashboardSelect';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';

export default function Index({ appointments, filters, auth }) {
    const [view, setView] = useState(() => localStorage.getItem('appointments_view') || 'list');
    const [search, setSearch] = useState(filters.search || '');
    const [filterData, setFilterData] = useState({
        status: filters.status || '',
        date: filters.date || '',
        doctor_id: filters.doctor_id || '',
        patient_id: filters.patient_id || '',
    });

    const [modalConfig, setModalConfig] = useState({
        show: false,
        appointment: null,
    });

    const handleViewChange = (newView) => {
        setView(newView);
        localStorage.setItem('appointments_view', newView);
    };

    const handleFilterChange = (e) => {
        const newData = { ...filterData, [e.target.name]: e.target.value };
        setFilterData(newData);
        applyFilters(search, newData);
    };
    
    const handleAsyncChange = (name, val) => {
        const newData = { ...filterData, [name]: val };
        setFilterData(newData);
        applyFilters(search, newData);
    };

    const applyFilters = (searchValue, extraFilters = filterData) => {
        router.get(route('appointments.index'), { search: searchValue, ...extraFilters }, {
            preserveState: true,
            replace: true,
        });
    };

    const resetFilters = () => {
        setSearch('');
        setFilterData({
            status: '',
            date: '',
            doctor_id: '',
            patient_id: '',
        });
        router.get(route('appointments.index'));
    };

    const getStatusBadgeClass = (status) => {
        const classes = {
            scheduled: 'bg-primary-subtle text-primary border border-primary-subtle',
            confirmed: 'bg-success-subtle text-success border border-success-subtle',
            completed: 'bg-success text-white',
            cancelled: 'bg-danger-subtle text-danger border border-danger-subtle',
            no_show: 'bg-secondary-subtle text-secondary border border-secondary-subtle',
        };
        return classes[status] || 'bg-light text-muted';
    };

    const columns = useMemo(() => [
        {
            header: 'Date & Time',
            accessorKey: 'appointment_date',
            cell: ({ row }) => (
                <div className="px-1">
                    <div className="fw-bold text-gray-900">{row.original.appointment_date}</div>
                    <small className="text-muted font-semibold">{row.original.appointment_time || 'N/A'}</small>
                </div>
            )
        },
        {
            header: 'Patient',
            accessorKey: 'patient_id',
            cell: ({ row }) => (
                <div>
                    <div 
                        onClick={() => openModal(row.original)}
                        className="cursor-pointer fw-bold text-pink-500 hover:text-pink-700 transition-colors"
                    >
                        {row.original.patient?.user?.first_name || 'Unknown'} {row.original.patient?.user?.last_name || 'Patient'}
                    </div>
                    <div className="extra-small text-muted font-bold text-uppercase opacity-75">ID: PAT-{row.original.patient_id}</div>
                </div>
            )
        },
        {
            header: 'Doctor',
            accessorKey: 'doctor_id',
            cell: ({ row }) => (
                <div>
                    <div className="fw-semibold text-gray-800">
                        Dr. {row.original.doctor?.user?.first_name || 'Unknown'} {row.original.doctor?.user?.last_name || 'Doctor'}
                    </div>
                    <div className="extra-small text-muted">{row.original.doctor?.specialization || 'Clinical'}</div>
                </div>
            )
        },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: ({ row }) => {
                const apt = row.original;
                const isPast = apt.status === 'scheduled' && new Date(`${apt.appointment_date}T${apt.appointment_time}`) < new Date();
                if (isPast) {
                    return (
                        <span className="badge bg-warning text-dark rounded-pill px-3 py-1 font-bold text-xs uppercase tracking-tighter shadow-sm border border-warning">
                            Overdue
                        </span>
                    );
                }
                return (
                    <span className={`${getStatusBadgeClass(apt.status)} rounded-pill px-3 py-1 font-bold text-xs uppercase tracking-tighter shadow-sm`}>
                        {(apt.status || 'pending').replace('_', ' ')}
                    </span>
                );
            }
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => (
                <div className="d-flex justify-content-end gap-2">
                    <button 
                        onClick={() => openModal(row.original)}
                        className="btn btn-sm btn-outline-primary rounded-circle p-2" 
                        title="Quick View"
                        style={{ width: '32px', height: '32px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                    >
                        <i className="fas fa-eye text-xs"></i>
                    </button>
                    <Link 
                        href={route('appointments.show', row.original.appointment_id)}
                        className="btn btn-sm btn-outline-secondary rounded-circle p-2" 
                        title="Detailed View"
                        style={{ width: '32px', height: '32px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                    >
                        <i className="fas fa-clipboard-list text-xs"></i>
                    </Link>
                </div>
            )
        }
    ], []);

    const openModal = (apt) => {
        setModalConfig({
            show: true,
            appointment: apt,
        });
    };

    const closeModal = () => {
        setModalConfig({
            show: false,
            appointment: null,
        });
    };

    const getAppointmentTabs = (apt) => {
        if (!apt) return [];
        
        const tabs = [
            {
                id: 'details',
                label: 'Visit Details',
                icon: 'fa-info-circle',
                content: (
                    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div className="space-y-4">
                                <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest">Appointment Info</h4>
                                <div className="space-y-3">
                                    <div className="flex justify-between border-b border-gray-100 pb-2">
                                        <span className="text-gray-500">Date</span>
                                        <span className="font-semibold">{apt.appointment_date}</span>
                                    </div>
                                    <div className="flex justify-between border-b border-gray-100 pb-2">
                                        <span className="text-gray-500">Time</span>
                                        <span className="font-semibold">{apt.appointment_time}</span>
                                    </div>
                                    <div className="flex justify-between border-b border-gray-100 pb-2">
                                        <span className="text-gray-500">Status</span>
                                        <span className={getStatusBadgeClass(apt.status)}>
                                            {apt.status.charAt(0).toUpperCase() + apt.status.slice(1).replace('_', ' ')}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div className="space-y-4">
                                <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest">Medical Team</h4>
                                <div className="space-y-3">
                                    <div className="flex justify-between border-b border-gray-100 pb-2">
                                        <span className="text-gray-500">Doctor</span>
                                        <span className="font-semibold">Dr. {apt.doctor.user.first_name} {apt.doctor.user.last_name}</span>
                                    </div>
                                    <div className="flex justify-between border-b border-gray-100 pb-2">
                                        <span className="text-gray-500">Department</span>
                                        <span className="font-semibold">{apt.doctor.specialization || 'General Practice'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                            <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest mb-3">Reason for Visit</h4>
                            <p className="text-gray-800 font-medium mb-0">{apt.reason || 'Not specified'}</p>
                        </div>
                        <div className="flex gap-3">
                            {apt.status === 'scheduled' && (
                                <button 
                                    onClick={() => {
                                        router.post(route('appointments.check-in', apt.appointment_id), {}, {
                                            preserveScroll: true,
                                            onSuccess: () => closeModal()
                                        });
                                    }}
                                    className="btn btn-primary px-4 py-2 rounded-xl font-bold shadow-sm"
                                >
                                    Confirm Arrival
                                </button>
                            )}
                            <Link href={route('appointments.show', apt.appointment_id)} className="btn btn-outline-secondary px-4 py-2 rounded-xl font-bold">View Full Records</Link>
                        </div>
                    </div>
                )
            }
        ];

        if (auth.user.role_name !== 'receptionist') {
            tabs.push({
                id: 'clinical',
                label: 'Clinical Notes',
                icon: 'fa-stethoscope',
                content: (
                    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest">Consultation Data</h4>
                        {apt.consultations?.length > 0 ? (
                            <div className="space-y-6">
                                {apt.consultations.map((c, i) => (
                                    <div key={i} className="p-6 rounded-2xl bg-white border border-gray-100 shadow-sm space-y-4">
                                        <div className="flex justify-between items-center bg-gray-50 -mx-6 -mt-6 p-4 rounded-t-2xl border-b border-gray-100">
                                            <span className="font-bold text-gray-900">Diagnosis: {c.diagnosis}</span>
                                            <span className="text-xs text-gray-400 font-semibold">{c.consultation_date}</span>
                                        </div>
                                        <div>
                                            <div className="text-xs font-bold text-gray-400 uppercase mb-1">Chief Complaint</div>
                                            <p className="text-gray-700">{c.chief_complaint}</p>
                                        </div>
                                        <div>
                                            <div className="text-xs font-bold text-gray-400 uppercase mb-1">Treatment Plan</div>
                                            <p className="text-gray-700">{c.treatment_plan}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-16 bg-gray-50 rounded-3xl border border-dashed border-gray-200">
                                <i className="fas fa-notes-medical text-gray-200 text-5xl mb-4"></i>
                                <p className="text-gray-500 font-medium mb-4">No clinical notes recorded yet.</p>
                                {auth.user.role === 'doctor' && (
                                    <Link href={route('consultations.create', { appointment_id: apt.appointment_id })} className="btn btn-primary px-6 rounded-pill font-bold shadow-md">
                                        Start Consultation
                                    </Link>
                                )}
                            </div>
                        )}
                    </div>
                )
            });

            tabs.push({
                id: 'prescriptions',
                label: 'Prescriptions',
                icon: 'fa-pills',
                content: (
                    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest">Medical Orders</h4>
                        {apt.prescriptions?.length > 0 ? (
                            <div className="space-y-4">
                                {apt.prescriptions.map((p, i) => (
                                    <div key={i} className="p-4 rounded-xl border border-gray-100 bg-white shadow-sm flex items-center justify-between">
                                        <div className="flex items-center gap-4">
                                            <div className="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500">
                                                <i className="fas fa-file-prescription"></i>
                                            </div>
                                            <div>
                                                <div className="font-bold text-gray-900">RX #{p.prescription_id}</div>
                                                <div className="text-xs text-gray-500">{p.prescription_date}</div>
                                            </div>
                                        </div>
                                        <Link href={route('prescriptions.show', p.prescription_id)} className="btn btn-sm btn-light border text-primary font-bold px-3 py-1 rounded-lg">View</Link>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-16 bg-gray-50 rounded-3xl">
                                <i className="fas fa-prescription-bottle text-gray-200 text-5xl mb-4"></i>
                                <p className="text-gray-400">No prescriptions found for this visit.</p>
                            </div>
                        )}
                    </div>
                )
            });
        }

        return tabs;
    };

    return (
        <AuthenticatedLayout
            header="Appointments"
        >
            <Head title="Appointments" />

            <PageHeader 
                title="Appointments List"
                breadcrumbs={[{ label: 'Appointments', active: true }]}
                actions={
                    <div className="d-flex align-items-center gap-3">
                        <ViewToggle view={view} setView={handleViewChange} />
                        <Link href={route('appointments.calendar')} className="btn btn-outline-primary border-0 bg-light shadow-sm rounded-pill px-4">
                            <i className="fas fa-calendar-alt me-2"></i>Calendar
                        </Link>
                        <Link href={route('appointments.create')} className="btn btn-primary shadow-sm rounded-pill px-4 fw-bold">
                            <i className="fas fa-plus me-2"></i>New Appointment
                        </Link>
                    </div>
                }
            />

            <div className="px-0">
                <DashboardSearch 
                    placeholder="Search by patient name, doctor, or reason..." 
                    value={search}
                    onChange={setSearch}
                    onSubmit={applyFilters}
                />

                <div className="card shadow-sm border-0 mb-4 rounded-xl" style={{ overflow: 'visible', position: 'relative', zIndex: 20 }}>
                    <div className="card-body p-3">
                        <div className="row g-3">
                            <div className="col-md-3">
                                <DashboardSelect 
                                    asyncUrl="/doctors/search"
                                    value={filterData.doctor_id} 
                                    onChange={val => handleAsyncChange('doctor_id', val)}
                                    placeholder="Select Doctor..."
                                    initialLabel={filters.doctor_name}
                                />
                            </div>
                            <div className="col-md-3">
                                <DashboardSelect 
                                    asyncUrl="/patients/search"
                                    value={filterData.patient_id} 
                                    onChange={val => handleAsyncChange('patient_id', val)}
                                    placeholder="Select Patient..."
                                    initialLabel={filters.patient_name}
                                />
                            </div>
                            <div className="col-md-2">
                                <select 
                                    name="status" 
                                    className="form-select border-0 bg-light rounded-pill font-medium" 
                                    value={filterData.status} 
                                    onChange={handleFilterChange}
                                >
                                    <option value="">All Statuses</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="no_show">No Show</option>
                                </select>
                            </div>
                            <div className="col-md-3">
                                <input 
                                    type="date" 
                                    name="date" 
                                    className="form-control border-0 bg-light rounded-pill font-medium" 
                                    value={filterData.date} 
                                    onChange={handleFilterChange} 
                                />
                            </div>
                            <div className="col-md-1 d-flex justify-content-end">
                                <button type="button" onClick={resetFilters} className="btn btn-outline-secondary rounded-circle p-0" style={{width: '38px', height: '38px'}} title="Reset Filters">
                                    <i className="fas fa-undo-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {/* View Content */}
                {view === 'list' ? (
                    <DashboardTable 
                        columns={columns}
                        data={appointments.data}
                        pagination={appointments}
                        emptyMessage="No appointments found."
                    />
                ) : (
                    <div className="row g-4">
                        {appointments.data.length > 0 ? (
                            appointments.data.map((apt) => (
                                <div key={apt.appointment_id} className="col-md-6 col-lg-4">
                                    <div className="card h-100 shadow-sm border-0 rounded-2xl overflow-hidden hover-shadow-lg transition-all duration-300">
                                        <div className="card-body p-4">
                                            <div className="d-flex justify-content-between align-items-start mb-4">
                                                <div className="d-flex gap-3">
                                                    <div className="bg-pink-50 text-pink-500 rounded-2xl p-3 flex items-center justify-center font-bold text-xl shadow-inner" style={{ width: '56px', height: '56px' }}>
                                                        <i className="fas fa-calendar-check fa-lg"></i>
                                                    </div>
                                                    <div>
                                                        <h5 className="fw-bold text-gray-900 mb-0 text-truncate" style={{ maxWidth: '160px' }}>
                                                            {apt.patient?.user?.first_name} {apt.patient?.user?.last_name}
                                                        </h5>
                                                        <span className="extra-small text-muted font-bold text-uppercase tracking-widest">ID: PAT-{apt.patient_id}</span>
                                                    </div>
                                                </div>
                                                {(() => {
                                                    const isPast = apt.status === 'scheduled' && new Date(`${apt.appointment_date}T${apt.appointment_time}`) < new Date();
                                                    if (isPast) {
                                                        return (
                                                            <span className="badge bg-warning text-dark rounded-pill px-3 py-1 font-bold text-xs uppercase tracking-tighter shadow-sm border border-warning">
                                                                Overdue
                                                            </span>
                                                        );
                                                    }
                                                    return (
                                                        <span className={`${getStatusBadgeClass(apt.status)} rounded-pill px-3 py-1 font-bold text-xs uppercase tracking-tighter`}>
                                                            {(apt.status || 'pending').replace('_', ' ')}
                                                        </span>
                                                    );
                                                })()}
                                            </div>

                                            <div className="space-y-3 mb-4">
                                                <div className="flex items-center gap-3 text-gray-600">
                                                    <i className="fas fa-clock text-muted w-5"></i>
                                                    <span className="font-medium text-sm">{apt.appointment_date} @ {apt.appointment_time || 'N/A'}</span>
                                                </div>
                                                <div className="flex items-center gap-3 text-gray-600">
                                                    <i className="fas fa-user-md text-muted w-5"></i>
                                                    <span className="font-medium text-sm">Dr. {apt.doctor?.user?.first_name} {apt.doctor?.user?.last_name}</span>
                                                </div>
                                                {apt.reason && (
                                                    <div className="bg-gray-50 p-3 rounded-xl">
                                                        <div className="text-xs text-gray-400 font-bold uppercase mb-1">Reason</div>
                                                        <p className="text-sm text-gray-800 fw-bold mb-0 line-clamp-2">{apt.reason}</p>
                                                    </div>
                                                )}
                                            </div>

                                            <div className="d-flex gap-2 border-top pt-4">
                                                <button 
                                                    onClick={() => openModal(apt)}
                                                    className="btn btn-light bg-gray-50 text-gray-700 rounded-xl flex-1 fw-bold border-0 py-2.5"
                                                >
                                                    Quick View
                                                </button>
                                                <Link 
                                                    href={route('appointments.show', apt.appointment_id)}
                                                    className="btn btn-outline-primary rounded-xl px-4 border-2"
                                                >
                                                    <i className="fas fa-external-link-alt"></i>
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="col-12 py-16 text-center bg-white rounded-3xl shadow-sm border border-gray-100">
                                <i className="fas fa-calendar-times text-gray-200 text-5xl mb-4"></i>
                                <h4 className="text-gray-400 fw-bold">No appointments found</h4>
                                <p className="text-gray-300">Try adjusting your filters or create a new appointment.</p>
                            </div>
                        )}
                    </div>
                )}
                
                {/* Pagination */}
                {appointments.links.length > 3 && (
                    <div className="card-footer bg-white border-top-0 py-3 mt-4">
                        <nav aria-label="Page navigation">
                            <ul className="pagination pagination-sm justify-content-center mb-0">
                                {appointments.links.map((link, i) => (
                                    <li key={i} className={`page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`}>
                                        <Link
                                            className="page-link rounded-circle mx-1"
                                            href={link.url}
                                            dangerouslySetInnerHTML={{ __html: link.alias || link.label }}
                                        />
                                    </li>
                                ))}
                            </ul>
                        </nav>
                    </div>
                )}
            </div>

            {/* Quick Info Modal */}
            <InfoModal
                show={modalConfig.show}
                onClose={closeModal}
                title={modalConfig.appointment ? `Visit: ${modalConfig.appointment.patient?.user?.first_name || 'Patient'}` : ''}
                subtitle="Appointment Info"
                tabs={getAppointmentTabs(modalConfig.appointment)}
            />

            <style>{`
                .rounded-xl { border-radius: 1rem; }
                .rounded-2xl { border-radius: 1.5rem; }
                .rounded-3xl { border-radius: 2rem; }
                .text-pink-500 { color: #ed64a6; }
                .text-xs { font-size: 0.75rem; }
                .extra-small { font-size: 0.7rem; }
                .tracking-tighter { letter-spacing: -0.025em; }
                .tracking-wider { letter-spacing: 0.05em; }
            `}</style>
        </AuthenticatedLayout>
    );
}
