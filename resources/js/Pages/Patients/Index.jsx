import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';
import PageHeader from '@/Components/PageHeader';
import ViewToggleComp from '@/Components/ViewToggle';
import InfoModalComp from '@/Components/InfoModal';
import { useState, useMemo } from 'react';

export default function Index({ patients, filters, auth }) {
    const [view, setView] = useState(() => localStorage.getItem('patients_view') || 'list');
    const [search, setSearch] = useState(filters.search || '');
    const [activeFilter, setActiveFilter] = useState(filters.status || '');

    const [modalConfig, setModalConfig] = useState({
        show: false,
        patient: null,
    });

    const handleViewChange = (newView) => {
        setView(newView);
        localStorage.setItem('patients_view', newView);
    };

    const applyFilters = (searchValue) => {
        router.get(route('patients.index'), { search: searchValue, status: activeFilter }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleFilterChange = (filterValue) => {
        setActiveFilter(filterValue);
        router.get(route('patients.index'), { search, status: filterValue }, {
            preserveState: true,
            replace: true,
        });
    };

    const resetFilters = () => {
        setSearch('');
        setActiveFilter('');
        router.get(route('patients.index'));
    };

    const calculateAge = (dob) => {
        if (!dob) return 'N/A';
        const birthDate = new Date(dob);
        if (isNaN(birthDate.getTime())) return 'N/A';
        const difference = Date.now() - birthDate.getTime();
        const ageDate = new Date(difference);
        return Math.abs(ageDate.getUTCFullYear() - 1970);
    };

    const columns = useMemo(() => [
        {
            header: 'Patient ID',
            accessorKey: 'patient_id',
            cell: ({ row }) => (
                <span className="badge bg-light text-primary fw-bold p-2">PAT-{row.original.patient_id}</span>
            )
        },
        {
            header: 'Name',
            accessorKey: 'user.first_name',
            cell: ({ row }) => (
                <div>
                    <button 
                        onClick={() => openModal(row.original)}
                        className="text-decoration-none fw-bold text-gray-900 border-0 bg-transparent p-0 hover:text-pink-500 transition-colors"
                    >
                        {row.original.user.first_name} {row.original.user.last_name}
                    </button>
                    <div className="small text-muted">{row.original.user.email}</div>
                </div>
            )
        },
        {
            header: 'Gender',
            accessorKey: 'gender',
            cell: ({ row }) => {
                const gender = (row.original.gender || 'unknown').toLowerCase();
                const isMale = gender === 'male' || gender === 'm';
                const isFemale = gender === 'female' || gender === 'f';
                
                return (
                    <span className={`badge rounded-pill px-3 py-2 ${isMale ? 'bg-blue-100 text-blue-700' : (isFemale ? 'bg-pink-100 text-pink-700' : 'bg-gray-100 text-gray-700')}`}>
                        {gender.charAt(0).toUpperCase() + gender.slice(1)}
                    </span>
                );
            }
        },
        {
            header: 'Age',
            accessorKey: 'date_of_birth',
            cell: ({ row }) => (
                <div className="fw-semibold text-center">
                    {calculateAge(row.original.date_of_birth)}
                </div>
            )
        },
        {
            header: 'Phone',
            accessorKey: 'user.phone',
            cell: ({ row }) => (
                <div className="text-gray-600">{row.original.user.phone || 'N/A'}</div>
            )
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
                        href={route('patients.show', row.original.patient_id)}
                        className="btn btn-sm btn-outline-secondary rounded-circle p-2" 
                        title="Full Profile"
                        style={{ width: '32px', height: '32px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                    >
                        <i className="fas fa-user-edit text-xs"></i>
                    </Link>
                </div>
            )
        }
    ], []);

    const openModal = (patient) => {
        setModalConfig({
            show: true,
            patient: patient,
        });
    };

    const closeModal = () => {
        setModalConfig({
            show: false,
            patient: null,
        });
    };

    const getPatientTabs = (patient) => {
        if (!patient) return [];
        
        return [
            {
                id: 'info',
                label: 'Patient Info',
                icon: 'fa-user-circle',
                content: (
                    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div className="space-y-4">
                                <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest">Demographics</h4>
                                <div className="space-y-3">
                                    <div className="flex justify-between border-b border-gray-100 pb-2">
                                        <span className="text-gray-500">Full Name</span>
                                        <span className="font-semibold">{patient.user.first_name} {patient.user.last_name}</span>
                                    </div>
                                    <div className="flex justify-between border-b border-gray-100 pb-2">
                                        <span className="text-gray-500">Gender</span>
                                        <span className="font-semibold text-capitalize">{patient.gender}</span>
                                    </div>
                                    <div className="flex justify-between border-b border-gray-100 pb-2">
                                        <span className="text-gray-500">Age</span>
                                        <span className="font-semibold">{calculateAge(patient.date_of_birth)} years</span>
                                    </div>
                                    <div className="flex justify-between border-b border-gray-100 pb-2">
                                        <span className="text-gray-500">Blood Group</span>
                                        <span className="font-semibold">{patient.blood_group || 'Not Specified'}</span>
                                    </div>
                                </div>
                            </div>
                            <div className="space-y-4">
                                <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest">Contact Details</h4>
                                <div className="space-y-3">
                                    <div className="flex justify-between border-b border-gray-100 pb-2">
                                        <span className="text-gray-500">Phone</span>
                                        <span className="font-semibold">{patient.user.phone}</span>
                                    </div>
                                    <div className="flex justify-between border-b border-gray-100 pb-2">
                                        <span className="text-gray-500">Email</span>
                                        <span className="font-semibold">{patient.user.email}</span>
                                    </div>
                                    <div className="flex flex-col gap-1">
                                        <span className="text-gray-500">Address</span>
                                        <span className="font-semibold">{patient.address || 'No address recorded'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="bg-pink-50 rounded-2xl p-6 border border-pink-100">
                            <h4 className="text-pink-600 text-xs font-bold uppercase tracking-widest mb-3">Emergency Contact</h4>
                            <div className="font-semibold text-gray-800">{patient.emergency_contact || 'None registered'}</div>
                        </div>
                    </div>
                )
            },
            {
                id: 'history',
                label: 'Recent Visits',
                icon: 'fa-history',
                content: (
                    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest">Recent Activity</h4>
                        {patient.consultations?.length > 0 ? (
                            <div className="space-y-4">
                                {patient.consultations.map((c, i) => (
                                    <div key={i} className="flex gap-4 p-4 rounded-xl border border-gray-100 hover:border-pink-200 transition-colors bg-white shadow-sm">
                                        <div className="w-12 h-12 rounded-lg bg-pink-50 flex items-center justify-center text-pink-500 flex-shrink-0">
                                            <i className="fas fa-stethoscope"></i>
                                        </div>
                                        <div className="flex-1">
                                            <div className="flex justify-between mb-1">
                                                <div className="font-bold text-gray-900">{c.diagnosis || 'Clinical Assessment'}</div>
                                                <div className="text-xs text-gray-400">{c.consultation_date}</div>
                                            </div>
                                            <p className="text-sm text-gray-500 line-clamp-1">{c.chief_complaint}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-12 bg-gray-50 rounded-3xl">
                                <i className="fas fa-folder-open text-gray-200 text-4xl mb-4"></i>
                                <p className="text-gray-400">No recent consultations found.</p>
                            </div>
                        )}
                    </div>
                )
            },
            {
                id: 'appointments',
                label: 'Schedule',
                icon: 'fa-calendar-alt',
                content: (
                    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest">Upcoming Appointments</h4>
                        {patient.appointments?.length > 0 ? (
                            <div className="space-y-4">
                                {patient.appointments.map((a, i) => (
                                    <div key={i} className="flex gap-4 p-4 rounded-xl border border-gray-100 bg-white shadow-sm">
                                        <div className="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 flex-shrink-0">
                                            <i className="fas fa-calendar-check"></i>
                                        </div>
                                        <div className="flex-1">
                                            <div className="font-bold text-gray-900">{a.appointment_date} @ {a.appointment_time}</div>
                                            <div className="text-xs text-gray-500">Status: <span className="text-capitalize">{a.status}</span></div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-12 bg-gray-50 rounded-3xl">
                                <i className="fas fa-calendar-times text-gray-200 text-4xl mb-4"></i>
                                <p className="text-gray-400">No upcoming appointments.</p>
                                <Link href={route('appointments.create', { patient_id: patient.patient_id })} className="btn btn-primary btn-sm mt-4">Book Now</Link>
                            </div>
                        )}
                    </div>
                )
            }
        ];
    };

    return (
        <AuthenticatedLayout
            header="Patients"
        >
            <Head title="Patients" />

            <PageHeader 
                title="Patients Registry"
                breadcrumbs={[{ label: 'Patients', active: true }]}
                actions={
                    <div className="d-flex align-items-center gap-3">
                        <ViewToggleComp view={view} setView={handleViewChange} />
                        <Link href={route('patients.create')} className="btn btn-primary shadow-sm rounded-pill px-4 py-2 fw-bold">
                            <i className="fas fa-plus me-2"></i>Register Patient
                        </Link>
                    </div>
                }
            />

            <div className="px-0">
                <DashboardSearch 
                    placeholder="Search by Name, Phone, Email, or Patient ID..." 
                    value={search}
                    onChange={setSearch}
                    onSubmit={applyFilters}
                    onFilterChange={handleFilterChange}
                    filters={[
                        { label: 'All Patients', value: '' },
                        { label: 'Recently Registered', value: 'recent' },
                        { label: 'Male Only', value: 'male' },
                        { label: 'Female Only', value: 'female' },
                    ]}
                />

                {/* View Content */}
                {view === 'list' ? (
                    <DashboardTable 
                        columns={columns}
                        data={patients.data}
                        pagination={patients}
                        emptyMessage="No patients found matching your search."
                    />
                ) : (
                    <div className="row g-4">
                        {patients.data.length > 0 ? (
                            patients.data.map((p) => (
                                <div key={p.patient_id} className="col-md-6 col-lg-4">
                                    <div className="card h-100 shadow-sm border-0 rounded-2xl overflow-hidden hover-shadow-lg transition-all duration-300">
                                        <div className="card-body p-4">
                                            <div className="d-flex justify-content-between align-items-start mb-4">
                                                <div className="d-flex gap-3">
                                                    <div className="bg-pink-50 text-pink-500 rounded-2xl p-3 flex items-center justify-center font-bold text-xl shadow-inner" style={{ width: '56px', height: '56px' }}>
                                                        {p.user?.first_name?.charAt(0) || 'P'}{p.user?.last_name?.charAt(0) || ''}
                                                    </div>
                                                    <div>
                                                        <h5 className="fw-bold text-gray-900 mb-0">{p.user?.first_name} {p.user?.last_name}</h5>
                                                        <span className="extra-small text-muted font-bold text-uppercase tracking-widest">ID: PAT-{p.patient_id}</span>
                                                    </div>
                                                </div>
                                                <span className="badge bg-light text-pink-500 rounded-pill px-3 py-1 font-bold">
                                                    {calculateAge(p.date_of_birth)}Y / {(p.gender || 'U').charAt(0).toUpperCase()}
                                                </span>
                                            </div>

                                            <div className="space-y-3 mb-4">
                                                <div className="flex items-center gap-3 text-gray-600">
                                                    <i className="fas fa-phone-alt text-muted w-5"></i>
                                                    <span className="font-medium text-sm">{p.user.phone || 'No phone'}</span>
                                                </div>
                                                <div className="flex items-center gap-3 text-gray-600">
                                                    <i className="fas fa-envelope text-muted w-5"></i>
                                                    <span className="font-medium text-sm text-truncate">{p.user.email}</span>
                                                </div>
                                                <div className="flex items-center gap-3 text-gray-600">
                                                    <i className="fas fa-history text-muted w-5"></i>
                                                    <span className="font-medium text-sm">{p.consultations_count || 0} Consultations</span>
                                                </div>
                                            </div>

                                            <div className="d-flex gap-2 border-top pt-4">
                                                <button 
                                                    onClick={() => openModal(p)}
                                                    className="btn btn-light bg-gray-50 text-gray-700 rounded-xl flex-1 fw-bold border-0 py-2.5"
                                                >
                                                    Quick View
                                                </button>
                                                <Link 
                                                    href={route('patients.show', p.patient_id)}
                                                    className="btn btn-outline-primary rounded-xl px-4 border-2"
                                                >
                                                    <i className="fas fa-user-circle"></i>
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="col-12 py-16 text-center bg-white rounded-3xl shadow-sm border border-gray-100">
                                <i className="fas fa-users-slash text-gray-200 text-5xl mb-4"></i>
                                <h4 className="text-gray-400 fw-bold">No patients found</h4>
                                <p className="text-gray-300">Try adjusting your filters or register a new patient.</p>
                            </div>
                        )}
                    </div>
                )}

                {/* Pagination */}
                {patients.links.length > 3 && (
                    <div className="card-footer bg-white border-top-0 py-3 mt-4">
                        <nav aria-label="Page navigation">
                            <ul className="pagination pagination-sm justify-content-center mb-0">
                                {patients.links.map((link, i) => (
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
            <InfoModalComp
                show={modalConfig.show}
                onClose={closeModal}
                title={modalConfig.patient ? `${modalConfig.patient.user.first_name} ${modalConfig.patient.user.last_name}` : ''}
                subtitle="Patient Details"
                tabs={getPatientTabs(modalConfig.patient)}
            />

            <style>{`
                .bg-blue-100 { background-color: #ebf8ff; }
                .text-blue-700 { color: #2b6cb0; }
                .bg-pink-100 { background-color: #fff5f7; }
                .text-pink-700 { color: #b83280; }
                .bg-pink-50 { background-color: #fffafb; }
                .text-pink-500 { color: #ed64a6; }
                .border-pink-100 { border-color: #fed7e2; }
                .rounded-xl { border-radius: 1rem; }
                .rounded-2xl { border-radius: 1.5rem; }
                .rounded-3xl { border-radius: 2rem; }
                .text-xs { font-size: 0.75rem; }
                .tracking-widest { letter-spacing: 0.1em; }
            `}</style>
        </AuthenticatedLayout>
    );
}
