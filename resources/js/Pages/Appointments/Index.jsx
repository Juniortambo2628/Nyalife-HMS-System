import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState, useMemo, useEffect } from 'react';
import InfoModal from '@/Components/InfoModal';
import ViewToggle from '@/Components/ViewToggle';
import DashboardSelect from '@/Components/DashboardSelect';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';
import StatusBadge from '@/Components/StatusBadge';
import TableActions from '@/Components/TableActions';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Index({ appointments, filters, auth }) {
    const [view, setView] = useState(() => localStorage.getItem('appointments_view') || 'list');
    const [search, setSearch] = useState(filters.search || '');
    const [selectedIds, setSelectedIds] = useState([]);

    useEffect(() => {
        const handleClear = () => setSelectedIds([]);
        window.addEventListener('toolbar-clear-selection', handleClear);
        return () => window.removeEventListener('toolbar-clear-selection', handleClear);
    }, []);
    const [filterData, setFilterData] = useState({
        status: filters.status || '',
        date: filters.date || '',
        doctor_id: filters.doctor_id || '',
        patient_id: filters.patient_id || '',
        quick_filter: filters.quick_filter || '',
    });

    const [modalConfig, setModalConfig] = useState({
        show: false,
        appointment: null,
    });

    const handleViewChange = (newView) => {
        setView(newView);
        localStorage.setItem('appointments_view', newView);
    };

    const handleAsyncChange = (name, val) => {
        const newData = { ...filterData, [name]: val };
        setFilterData(newData);
        applyFilters(search, newData);
    };

    const handleQuickFilterChange = (val) => {
        const newData = { ...filterData, quick_filter: val || '' };
        setFilterData(newData);
        applyFilters(search, newData);
    };

    const applyFilters = (searchValue, extraFilters = filterData) => {
        router.get(route('appointments.index'), { search: searchValue, ...extraFilters }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleSelectAll = (e) => {
        if (e.target.checked) {
            setSelectedIds(appointments.data.map(a => a.appointment_id));
        } else {
            setSelectedIds([]);
        }
    };

    const toggleSelection = (id) => {
        setSelectedIds(prev => 
            prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]
        );
    };

    const columns = useMemo(() => [
        {
            header: 'Date & Time',
            accessorKey: 'appointment_date',
            cell: ({ row }) => (
                <div className="px-1">
                    <div className="fw-bold text-gray-900">{row.original.appointment_date}</div>
                    <small className="text-muted font-bold extra-small text-uppercase opacity-75">{row.original.appointment_time || 'N/A'}</small>
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
                    <div className="fw-bold text-gray-800 small">
                        Dr. {row.original.doctor?.user?.first_name || 'Unknown'} {row.original.doctor?.user?.last_name || 'Doctor'}
                    </div>
                    <div className="extra-small text-muted font-bold text-uppercase opacity-50 tracking-tight">{row.original.doctor?.specialization || 'Clinical'}</div>
                </div>
            )
        },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: ({ row }) => {
                const apt = row.original;
                const isPast = apt.status === 'scheduled' && new Date(`${apt.appointment_date}T${apt.appointment_time}`) < new Date();
                return <StatusBadge status={isPast ? 'overdue' : apt.status} />;
            }
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => (
                <TableActions actions={[
                    { icon: 'fa-eye', label: 'Quick View', onClick: () => openModal(row.original) },
                    { icon: 'fa-clipboard-list', label: 'Detailed View', href: route('appointments.show', row.original.appointment_id) },
                ]} />
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
                                        <StatusBadge status={apt.status} />
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

        if (auth.user.role !== 'receptionist') {
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
            headerTitle="Appointments Ledger"
            breadcrumbs={[{ label: 'Appointments', active: true }]}
            headerActions={auth.user.role !== 'patient' && (
                <div className="d-flex gap-2">
                    <Link href={route('appointments.calendar')} className="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold small shadow-sm">
                        <i className="fas fa-calendar-alt me-2"></i> Calendar
                    </Link>
                </div>
            )}
        >
            <Head title="Appointments" />



            <UnifiedToolbar 
                viewOptions={[
                    { 
                        label: 'LIST VIEW', 
                        icon: 'fa-list-ul', 
                        onClick: () => handleViewChange('list'),
                        color: view === 'list' ? 'pink-500' : 'gray-400'
                    },
                    { 
                        label: 'GRID VIEW', 
                        icon: 'fa-th-large', 
                        onClick: () => handleViewChange('grid'),
                        color: view === 'grid' ? 'pink-500' : 'gray-400'
                    }
                ]}
                filters={
                    <>
                        <DashboardSelect 
                            options={[
                                { label: 'All Status', value: '' },
                                { label: 'Scheduled', value: 'scheduled' },
                                { label: 'Confirmed', value: 'confirmed' },
                                { label: 'Arrived', value: 'arrived' },
                                { label: 'Completed', value: 'completed' },
                                { label: 'Cancelled', value: 'cancelled' },
                                { label: 'No Show', value: 'no_show' }
                            ]}
                            value={filterData.status} 
                            onChange={val => handleAsyncChange('status', val)}
                            placeholder="Status..."
                            theme="dark"
                            dropup={true}
                        />
                        <DashboardSelect 
                            options={[
                                { label: 'Consultation', value: 'consultation' },
                                { label: 'Follow-up', value: 'followup' },
                                { label: 'Procedure', value: 'procedure' },
                            ]}
                            value={filterData.quick_filter}
                            onChange={handleQuickFilterChange}
                            placeholder="Type..."
                            theme="dark"
                            dropup={true}
                        />
                    </>
                }
                actions={[
                    auth.user.role !== 'patient' && { 
                        label: 'BOOK VISIT', 
                        icon: 'fa-plus', 
                        href: route('appointments.create') 
                    }
                ]}
                bulkActions={[
                    { 
                        label: 'CONFIRM BATCH', 
                        icon: 'fa-check-circle', 
                        onClick: () => console.log('Batch confirm', selectedIds) 
                    },
                    { 
                        label: 'PRINT CARDS', 
                        icon: 'fa-print', 
                        onClick: () => console.log('Batch print', selectedIds) 
                    },
                    { 
                        label: 'CANCEL', 
                        icon: 'fa-trash-alt', 
                        onClick: () => console.log('Batch cancel', selectedIds),
                        color: 'danger'
                    }
                ]}
                selectionCount={selectedIds.length}
            />

            <div className="px-0">
                <DashboardSearch 
                    placeholder="Search ledger by name, doctor, or visit reason..." 
                    value={search}
                    onChange={setSearch}
                    onSubmit={applyFilters}
                    onFilterChange={handleQuickFilterChange}
                    filters={[
                        { label: 'Today\'s Visits', value: 'today' },
                        { label: 'Upcoming', value: 'upcoming' },
                        { label: 'Past Due', value: 'overdue' }
                    ]}
                />

                {/* View Content */}
                {view === 'list' ? (
                    <DashboardTable 
                        columns={columns}
                        data={appointments.data}
                        pagination={appointments}
                        emptyMessage="No matching appointments detected in the ledger."
                        selectable={true}
                        selectedIds={selectedIds}
                        onSelectionChange={setSelectedIds}
                        idField="appointment_id"
                    />
                ) : (
                    <div className="row g-4 mb-5">
                        {appointments.data.length > 0 ? (
                            <>
                                {appointments.data.map((apt) => (
                                    <div key={apt.appointment_id} className="col-md-6 col-lg-4">
                                        <div className={`card h-100 shadow-sm border-0 rounded-2xl overflow-hidden hover-shadow-lg transition-all duration-300 bg-white ${selectedIds.includes(apt.appointment_id) ? 'ring-2 ring-primary ring-opacity-50' : ''}`}>
                                            <div className="card-body p-4 position-relative">
                                                <div className="form-check position-absolute top-0 end-0 m-4 d-flex justify-content-center align-items-center p-0">
                                                    <input 
                                                        type="checkbox" 
                                                        className="form-check-input shadow-none cursor-pointer nyl-checkbox m-0" 
                                                        checked={selectedIds.includes(apt.appointment_id)}
                                                        onChange={() => toggleSelection(apt.appointment_id)}
                                                    />
                                                </div>
                                                <div className="d-flex justify-content-between align-items-start mb-4">
                                                    <div className="d-flex gap-3">
                                                        <div className="bg-pink-50 text-pink-500 rounded-2xl p-3 d-flex align-items-center justify-content-center font-bold text-xl shadow-inner avatar-lg">
                                                            <i className="fas fa-calendar-check fa-lg"></i>
                                                        </div>
                                                        <div>
                                                            <h5 className="fw-extrabold text-gray-900 mb-0 text-truncate" style={{ maxWidth: '140px' }}>
                                                                {apt.patient?.user?.first_name} {apt.patient?.user?.last_name}
                                                            </h5>
                                                            <span className="extra-small text-muted font-bold text-uppercase tracking-widest">PAT-{apt.patient_id}</span>
                                                        </div>
                                                    </div>
                                                    {(() => {
                                                        const isPast = apt.status === 'scheduled' && new Date(`${apt.appointment_date}T${apt.appointment_time}`) < new Date();
                                                        return <StatusBadge status={isPast ? 'overdue' : apt.status} />;
                                                    })()}
                                                </div>

                                                <div className="space-y-3 mb-4">
                                                    <div className="flex items-center gap-3 text-gray-600">
                                                        <i className="fas fa-clock text-muted w-5"></i>
                                                        <span className="font-bold extra-small text-uppercase tracking-tight">{apt.appointment_date} @ {apt.appointment_time || 'N/A'}</span>
                                                    </div>
                                                    <div className="flex items-center gap-3 text-gray-600">
                                                        <i className="fas fa-user-md text-muted w-5"></i>
                                                        <span className="font-bold extra-small text-uppercase tracking-tight text-primary">Dr. {apt.doctor?.user?.first_name} {apt.doctor?.user?.last_name}</span>
                                                    </div>
                                                    {apt.reason && (
                                                        <div className="bg-gray-50 p-3 rounded-xl border border-light">
                                                            <div className="extra-small text-muted font-bold text-uppercase mb-1 tracking-widest">Reason</div>
                                                            <p className="extra-small text-gray-800 fw-bold mb-0 line-clamp-2">{apt.reason}</p>
                                                        </div>
                                                    )}
                                                </div>

                                                <div className="d-flex gap-2 border-top pt-4">
                                                    <button 
                                                        onClick={() => openModal(apt)}
                                                        className="btn btn-light bg-gray-50 text-gray-700 rounded-xl flex-1 fw-extrabold extra-small border-0 py-2.5"
                                                    >
                                                        QUICK VIEW
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
                                ))}
                                
                                {/* Unified Pagination for Grid View */}
                                <div className="col-12 mt-4">
                                    <DashboardTable 
                                        data={[]} 
                                        columns={[]} 
                                        pagination={appointments}
                                        className="bg-transparent shadow-none"
                                    />
                                </div>
                            </>
                        ) : (
                            <div className="col-12 py-16 text-center bg-white rounded-3xl shadow-sm border border-gray-100">
                                <i className="fas fa-calendar-times text-gray-200 text-5xl mb-4"></i>
                                <h4 className="text-gray-400 fw-bold">No appointments found</h4>
                                <p className="text-gray-300">Try adjusting your filters or create a new appointment.</p>
                            </div>
                        )}
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
                .extra-small { font-size: 0.7rem; }
                .fw-extrabold { font-weight: 800; }
                .tracking-tight { letter-spacing: -0.025em; }
                .line-clamp-2 {
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                    overflow: hidden;
                }
                .btn-white {
                    background: white;
                    color: #e91e63;
                    border: none;
                }
                .btn-white:hover {
                    background: #f8f9fa;
                    color: #d81b60;
                }
            `}</style>
        </AuthenticatedLayout>
    );
}
