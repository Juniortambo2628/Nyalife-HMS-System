import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';
import ViewToggle from '@/Components/ViewToggle';
import InfoModal from '@/Components/InfoModal';
import DashboardSelect from '@/Components/DashboardSelect';
import UserAvatar from '@/Components/UserAvatar';
import TableActions from '@/Components/TableActions';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { useState, useMemo, useEffect } from 'react';

export default function Index({ patients, filters, auth }) {
    const [view, setView] = useState(() => localStorage.getItem('patients_view') || 'list');
    const [search, setSearch] = useState(filters.search || '');
    const [activeFilter, setActiveFilter] = useState(filters.status || '');
    const [selectedIds, setSelectedIds] = useState([]);

    useEffect(() => {
        const handleClear = () => setSelectedIds([]);
        window.addEventListener('toolbar-clear-selection', handleClear);
        return () => window.removeEventListener('toolbar-clear-selection', handleClear);
    }, []);

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

    const calculateAge = (dob) => {
        if (!dob) return 'N/A';
        const birthDate = new Date(dob);
        if (isNaN(birthDate.getTime())) return 'N/A';
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) age--;
        return age;
    };

    const columns = useMemo(() => [
        {
            header: 'Patient Registry',
            accessorKey: 'patient_id',
            cell: ({ row }) => (
                <div className="d-flex align-items-center gap-3 py-1">
                    <UserAvatar user={row.original.user} size="md" className="shadow-sm" />
                    <div>
                        <button 
                            onClick={() => openModal(row.original)}
                            className="text-decoration-none fw-extrabold text-gray-900 border-0 bg-transparent p-0 hover:text-primary transition-all tracking-tight"
                        >
                            {row.original.user.first_name} {row.original.user.last_name}
                        </button>
                        <div className="extra-small text-muted font-bold opacity-50 text-uppercase tracking-widest mt-1">PAT-{row.original.patient_id}</div>
                    </div>
                </div>
            )
        },
        {
            header: 'Vital Profile',
            accessorKey: 'gender',
            cell: ({ row }) => {
                const gender = (row.original.gender || 'unknown').toLowerCase();
                const isMale = gender === 'male' || gender === 'm';
                const isFemale = gender === 'female' || gender === 'f';
                
                return (
                    <div className="d-flex align-items-center gap-2">
                        <span className={`badge rounded-pill px-3 py-1.5 fw-extrabold extra-small border ${isMale ? 'bg-blue-50 text-blue-600 border-blue-100' : (isFemale ? 'bg-pink-50 text-pink-600 border-pink-100' : 'bg-gray-50 text-gray-600 border-gray-100')}`}>
                            <i className={`fas fa-${isMale ? 'mars' : (isFemale ? 'venus' : 'user')} me-1`}></i>
                            {gender.toUpperCase()}
                        </span>
                        <span className="badge bg-gray-50 text-gray-500 border rounded-pill px-3 py-1.5 fw-extrabold extra-small">
                            {calculateAge(row.original.date_of_birth)}Y
                        </span>
                    </div>
                );
            }
        },
        {
            header: 'Communication',
            accessorKey: 'user.phone',
            cell: ({ row }) => (
                <div className="space-y-1">
                    <div className="small fw-extrabold text-gray-800">{row.original.user.phone || 'N/A'}</div>
                    <div className="extra-small text-muted font-medium text-truncate" style={{maxWidth: '150px'}}>{row.original.user.email}</div>
                </div>
            )
        },
        {
            header: 'Clinical Activity',
            id: 'activity',
            cell: ({ row }) => (
                <div className="d-flex align-items-center gap-3">
                    <div className="text-center border-end pe-3 border-gray-100">
                        <div className="fw-extrabold text-gray-900 small">{row.original.consultations_count || 0}</div>
                        <div className="extra-small text-muted font-bold uppercase opacity-50 tracking-tighter">Visits</div>
                    </div>
                    <div className="text-center">
                        <div className="fw-extrabold text-gray-900 small">{row.original.appointments_count || 0}</div>
                        <div className="extra-small text-muted font-bold uppercase opacity-50 tracking-tighter">Slots</div>
                    </div>
                </div>
            )
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => (
                <TableActions actions={[
                    { icon: 'fa-heartbeat', label: 'Record Vitals', href: route('vitals.create', { patient_id: row.original.patient_id }) },
                    { icon: 'fa-eye', label: 'Quick View', onClick: () => openModal(row.original) },
                    { icon: 'fa-user-circle', label: 'Full Profile', href: route('patients.show', row.original.patient_id) },
                ]} />
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
                label: 'Personal Data',
                icon: 'fa-id-card',
                content: (
                    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div className="row g-5">
                            <div className="col-md-6 border-end border-gray-100">
                                <h6 className="extra-small fw-extrabold text-primary text-uppercase tracking-widest mb-4 opacity-50">Demographics Registry</h6>
                                <div className="space-y-4">
                                    <div className="d-flex justify-content-between align-items-center border-bottom border-gray-50 pb-2">
                                        <span className="extra-small fw-bold text-muted text-uppercase">Full Identity</span>
                                        <span className="fw-extrabold text-gray-900 small">{patient.user.first_name} {patient.user.last_name}</span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center border-bottom border-gray-50 pb-2">
                                        <span className="extra-small fw-bold text-muted text-uppercase">Biological Gender</span>
                                        <span className="fw-extrabold text-gray-900 small text-uppercase">{patient.gender}</span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center border-bottom border-gray-50 pb-2">
                                        <span className="extra-small fw-bold text-muted text-uppercase">Current Age</span>
                                        <span className="fw-extrabold text-gray-900 small">{calculateAge(patient.date_of_birth)} Years</span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center">
                                        <span className="extra-small fw-bold text-muted text-uppercase">Blood Profile</span>
                                        <span className="badge bg-danger rounded-pill px-3 py-1 fw-extrabold extra-small">{patient.blood_group || 'N/A'}</span>
                                    </div>
                                </div>
                            </div>
                            <div className="col-md-6">
                                <h6 className="extra-small fw-extrabold text-primary text-uppercase tracking-widest mb-4 opacity-50">Communication Matrix</h6>
                                <div className="space-y-4">
                                    <div className="d-flex justify-content-between align-items-center border-bottom border-gray-50 pb-2">
                                        <span className="extra-small fw-bold text-muted text-uppercase">Direct Phone</span>
                                        <span className="fw-extrabold text-gray-900 small font-mono">{patient.user.phone}</span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center border-bottom border-gray-50 pb-2">
                                        <span className="extra-small fw-bold text-muted text-uppercase">Email Reach</span>
                                        <span className="fw-extrabold text-gray-900 small text-truncate" style={{maxWidth: '180px'}}>{patient.user.email}</span>
                                    </div>
                                    <div className="pt-2">
                                        <span className="extra-small fw-bold text-muted text-uppercase d-block mb-1">Residential Address</span>
                                        <span className="small fw-bold text-gray-700 leading-relaxed">{patient.address || 'Address not recorded in system'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="p-4 bg-pink-50 rounded-2xl border border-pink-100 shadow-inner">
                            <h6 className="extra-small fw-extrabold text-pink-500 text-uppercase tracking-widest mb-3">Emergency Response (NOK)</h6>
                            <div className="fw-extrabold text-gray-800 d-flex align-items-center gap-3">
                                <i className="fas fa-user-shield text-pink-300"></i>
                                {patient.emergency_name ? `${patient.emergency_name.toUpperCase()}` : 'NOT REGISTERED'}
                                {patient.emergency_contact && <span className="opacity-50 font-mono small">— {patient.emergency_contact}</span>}
                            </div>
                        </div>
                    </div>
                )
            },
            {
                id: 'history',
                label: 'Visit Timeline',
                icon: 'fa-history',
                content: (
                    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <h6 className="extra-small fw-extrabold text-primary text-uppercase tracking-widest opacity-50 mb-4">Historical Encounters</h6>
                        {patient.consultations?.length > 0 ? (
                            <div className="space-y-3">
                                {patient.consultations.map((c, i) => (
                                    <div key={i} className="p-4 rounded-2xl border border-gray-100 hover-lift transition-all bg-white shadow-sm d-flex gap-4 align-items-center">
                                        <div className="avatar-md bg-pink-50 text-pink-500 rounded-xl d-flex align-items-center justify-content-center flex-shrink-0 border border-pink-100">
                                            <i className="fas fa-stethoscope"></i>
                                        </div>
                                        <div className="flex-1 overflow-hidden">
                                            <div className="d-flex justify-content-between align-items-center mb-1">
                                                <div className="fw-extrabold text-gray-900 text-truncate">{c.diagnosis || 'General Consultation'}</div>
                                                <div className="extra-small text-muted font-bold opacity-50">{c.consultation_date}</div>
                                            </div>
                                            <p className="extra-small text-muted font-medium mb-0 text-truncate opacity-75">{c.chief_complaint}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-16 bg-gray-50 rounded-3xl border border-dashed border-gray-200">
                                <i className="fas fa-folder-open text-gray-200 display-6 mb-4 opacity-20"></i>
                                <p className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-0 opacity-50">No historical encounters detected</p>
                            </div>
                        )}
                    </div>
                )
            }
        ];
    };

    return (
        <AuthenticatedLayout 
            headerTitle="Patients Registry"
            breadcrumbs={[{ label: 'Patients', active: true }]}
        >
            <Head title="Patients" />

            <UnifiedToolbar 
                viewOptions={[
                    { label: 'LIST VIEW', icon: 'fa-list-ul', onClick: () => handleViewChange('list'), color: view === 'list' ? 'pink-500' : 'gray-400' },
                    { label: 'GRID VIEW', icon: 'fa-th-large', onClick: () => handleViewChange('grid'), color: view === 'grid' ? 'pink-500' : 'gray-400' }
                ]}
                filters={
                    <DashboardSelect 
                        options={[
                            { label: 'All Subjects', value: '' },
                            { label: 'Recently Registered', value: 'recent' },
                            { label: 'Biological Male', value: 'male' },
                            { label: 'Biological Female', value: 'female' },
                        ]}
                        value={activeFilter} 
                        onChange={handleFilterChange}
                        theme="dark"
                        dropup={true}
                        placeholder="Filter..."
                    />
                }
                actions={[
                    { label: 'REGISTER NEW', icon: 'fa-user-plus', href: route('patients.create') }
                ]}
                bulkActions={[
                    { label: 'EXPORT RECORDS', icon: 'fa-file-export', onClick: () => console.log('Export', selectedIds) },
                    { label: 'PRINT CARDS', icon: 'fa-id-card', onClick: () => console.log('Print cards', selectedIds) }
                ]}
                selectionCount={selectedIds.length}
            />

            <div className="px-0 pb-20">
                <DashboardSearch 
                    placeholder="Search by ID, Name, Phone or Email..." 
                    value={search}
                    onChange={setSearch}
                    onSubmit={applyFilters}
                    onFilterChange={handleFilterChange}
                    filters={[
                        { label: 'All Subjects', value: '' },
                        { label: 'Recently Registered', value: 'recent' },
                        { label: 'Biological Male', value: 'male' },
                        { label: 'Biological Female', value: 'female' },
                    ]}
                />

                {/* View Content */}
                {view === 'list' ? (
                    <DashboardTable 
                        columns={columns}
                        data={patients.data}
                        pagination={patients}
                        emptyMessage="No medical subjects found matching your criteria."
                        selectable={true}
                        selectedIds={selectedIds}
                        onSelectionChange={setSelectedIds}
                        idField="patient_id"
                    />
                ) : (
                    <div className="row g-4">
                        {patients.data.length > 0 ? (
                            <>
                                {patients.data.map((p) => (
                                    <div key={p.patient_id} className="col-md-6 col-lg-4">
                                        <div className="card h-100 shadow-sm border-0 rounded-3xl overflow-hidden hover-lift shadow-hover transition-all duration-300 bg-white">
                                            <div className="card-body p-4">
                                                <div className="d-flex justify-content-between align-items-start mb-5">
                                                    <div className="d-flex gap-3">
                                                        <UserAvatar user={p.user} size="lg" className="rounded-2xl shadow-sm border border-white" />
                                                        <div>
                                                            <h5 className="fw-extrabold text-gray-900 mb-0 tracking-tightest">{p.user?.first_name} {p.user?.last_name}</h5>
                                                            <div className="extra-small text-muted font-bold text-uppercase tracking-widest opacity-50">PAT-{p.patient_id}</div>
                                                        </div>
                                                    </div>
                                                    <span className="badge bg-primary-subtle text-primary rounded-pill px-3 py-1.5 fw-extrabold extra-small border border-primary-subtle">
                                                        {calculateAge(p.date_of_birth)}Y / {(p.gender || 'U').charAt(0).toUpperCase()}
                                                    </span>
                                                </div>

                                                <div className="space-y-4 mb-5 px-1">
                                                    <div className="d-flex align-items-center gap-3">
                                                        <div className="avatar-xs bg-gray-50 text-gray-400 rounded-lg d-flex align-items-center justify-content-center border"><i className="fas fa-phone-alt extra-small"></i></div>
                                                        <span className="fw-bold text-gray-700 small font-mono">{p.user.phone || 'N/A'}</span>
                                                    </div>
                                                    <div className="d-flex align-items-center gap-3">
                                                        <div className="avatar-xs bg-gray-50 text-gray-400 rounded-lg d-flex align-items-center justify-content-center border"><i className="fas fa-history extra-small"></i></div>
                                                        <span className="fw-extrabold text-gray-900 small">{p.consultations_count || 0} Clinical Encounters</span>
                                                    </div>
                                                </div>

                                                <div className="d-flex gap-2 pt-4 border-top border-gray-50">
                                                    <button 
                                                        onClick={() => openModal(p)}
                                                        className="btn btn-light bg-gray-50 text-gray-700 rounded-pill flex-1 fw-extrabold extra-small tracking-widest border-0 py-2.5 shadow-sm"
                                                    >
                                                        QUICK VIEW
                                                    </button>
                                                    <Link
                                                        href={route('vitals.create', { patient_id: p.patient_id })}
                                                        className="btn btn-outline-success rounded-circle p-2 avatar-sm d-flex align-items-center justify-content-center shadow-sm border-2"
                                                        title="Record Vitals"
                                                    >
                                                        <i className="fas fa-heartbeat text-xs"></i>
                                                    </Link>
                                                    <Link 
                                                        href={route('patients.show', p.patient_id)}
                                                        className="btn btn-primary rounded-circle p-2 avatar-sm d-flex align-items-center justify-content-center shadow-sm"
                                                    >
                                                        <i className="fas fa-chevron-right text-xs"></i>
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))}

                                {/* Pagination for Grid View */}
                                <div className="col-12 mt-5">
                                    <DashboardTable 
                                        data={[]} 
                                        columns={[]} 
                                        pagination={patients}
                                        className="bg-transparent shadow-none"
                                    />
                                </div>
                            </>
                        ) : (
                            <div className="col-12 py-24 text-center bg-white rounded-4 shadow-sm border border-gray-100">
                                <i className="fas fa-users-slash text-gray-100 display-4 mb-4"></i>
                                <h4 className="text-gray-400 fw-extrabold tracking-tightest">NO SUBJECTS DETECTED</h4>
                                <p className="text-muted extra-small fw-bold text-uppercase tracking-widest opacity-50">Adjust your search parameters or initiate a new registration.</p>
                            </div>
                        )}
                    </div>
                )}
            </div>

            {/* Quick Info Modal */}
            <InfoModal
                show={modalConfig.show}
                onClose={closeModal}
                title={modalConfig.patient ? `${modalConfig.patient.user.first_name} ${modalConfig.patient.user.last_name}` : ''}
                subtitle="Diagnostic Overview"
                tabs={getPatientTabs(modalConfig.patient)}
                icon="fa-user-nurse"
            />
        </AuthenticatedLayout>
    );
}
