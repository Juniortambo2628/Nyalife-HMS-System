import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import DashboardSearch from '@/Components/DashboardSearch';
import StatusBadge from '@/Components/StatusBadge';
import DashboardTable from '@/Components/DashboardTable';
import ViewToggle from '@/Components/ViewToggle';
import InfoModal from '@/Components/InfoModal';
import PageHeader from '@/Components/PageHeader';
import DashboardSelect from '@/Components/DashboardSelect';
import TableActions from '@/Components/TableActions';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { useState, useMemo, useEffect } from 'react';
import { formatDateTime } from '@/Utils/dateUtils';

export default function Index({ consultations, drafts = [], filters, auth }) {
    const [view, setView] = useState(() => localStorage.getItem('consultations_view') || 'list');
    const [search, setSearch] = useState(filters.search || '');
    const [activeFilter, setActiveFilter] = useState(filters.status || '');
    const [quickFilter, setQuickFilter] = useState(filters.quick_filter || '');
    const [selectedIds, setSelectedIds] = useState([]);

    useEffect(() => {
        const handleClear = () => setSelectedIds([]);
        window.addEventListener('toolbar-clear-selection', handleClear);
        return () => window.removeEventListener('toolbar-clear-selection', handleClear);
    }, []);
    
    const [modalConfig, setModalConfig] = useState({
        show: false,
        consultation: null,
    });

    const handleViewChange = (newView) => {
        setView(newView);
        localStorage.setItem('consultations_view', newView);
    };

    const handleSearch = (searchValue) => {
        router.get(route('consultations.index'), { search: searchValue, status: activeFilter, quick_filter: quickFilter }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleFilterChange = (val) => {
        setActiveFilter(val || '');
        router.get(route('consultations.index'), { search, status: val || '', quick_filter: quickFilter }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleQuickFilterChange = (val) => {
        setQuickFilter(val);
        router.get(route('consultations.index'), { search, status: activeFilter, quick_filter: val }, {
            preserveState: true,
            replace: true,
        });
    };

    const columns = useMemo(() => [
        {
            header: 'Date',
            accessorKey: 'consultation_date',
            cell: ({ row }) => (
                <div className="fw-extrabold text-pink-500 px-1 extra-small tracking-widest uppercase">{formatDateTime(row.original.consultation_date)}</div>
            )
        },
        {
            header: 'Patient',
            accessorKey: 'patient_id',
            cell: ({ row }) => (
                <div>
                    <Link href={route('patients.show', row.original.patient_id)} className="text-decoration-none fw-bold text-pink-500 hover:text-pink-700 transition-colors">
                        {row.original.patient.user.first_name} {row.original.patient.user.last_name}
                    </Link>
                    <div className="extra-small text-muted font-bold text-uppercase tracking-widest opacity-75">ID: PAT-{row.original.patient_id}</div>
                </div>
            )
        },
        {
            header: 'Doctor',
            accessorKey: 'doctor_id',
            cell: ({ row }) => (
                <div className="fw-semibold text-gray-800">
                    Dr. {row.original.doctor.user.first_name} {row.original.doctor.user.last_name}
                </div>
            )
        },
        {
            header: 'Diagnosis',
            accessorKey: 'diagnosis',
            cell: ({ row }) => (
                <div>
                    <div className="fw-bold text-gray-700">{row.original.diagnosis || 'Clinical Notes'}</div>
                    <div className="extra-small text-muted line-clamp-1">{row.original.chief_complaint}</div>
                </div>
            )
        },
        {
            header: 'Status',
            accessorKey: 'consultation_status',
            cell: ({ row }) => (
                <StatusBadge status={row.original.consultation_status || 'in_progress'} />
            )
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => {
                const actions = [
                    { icon: 'fa-stethoscope', label: 'Quick Clinical View', onClick: () => openModal(row.original), color: 'primary' },
                ];
                if (auth.user.role !== 'patient') {
                    actions.push(
                        { icon: 'fa-edit', label: 'Edit Record', href: route('consultations.edit', row.original.consultation_id), color: 'warning' },
                    );
                    if (row.original.consultation_status !== 'completed') {
                        actions.push(
                            { icon: 'fa-check-double', label: 'Conclude & Close', color: 'success', onClick: () => {
                                if (confirm('Are you sure you want to conclude this consultation?')) {
                                    router.put(route('consultations.update', row.original.consultation_id), {
                                        ...row.original,
                                        status: 'completed'
                                    });
                                }
                            }},
                        );
                    }
                }
                actions.push({ isDivider: true });
                actions.push({ icon: 'fa-microscope', label: 'See Related Labs', href: route('lab.index', { consultation_id: row.original.consultation_id }), color: 'info' });
                actions.push({ icon: 'fa-pills', label: 'See Prescriptions', href: route('prescriptions.index', { consultation_id: row.original.consultation_id }), color: 'primary' });
                return <TableActions actions={actions} />;
            }
        }
    ], []);

    const openModal = (cons) => {
        setModalConfig({
            show: true,
            consultation: cons,
        });
    };

    const closeModal = () => {
        setModalConfig({
            show: false,
            consultation: null,
        });
    };

    const getConsultationTabs = (cons) => {
        if (!cons) return [];
        
        return [
            {
                id: 'assessment',
                label: 'Clinical Assessment',
                icon: 'fa-stethoscope',
                content: (
                    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div className="space-y-4">
                            <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest">Chief Complaint</h4>
                            <div className="bg-pink-50 p-6 rounded-2xl border border-pink-100 text-gray-800 font-medium leading-relaxed shadow-inner">
                                {cons.chief_complaint}
                            </div>
                        </div>
                        
                        <div className="space-y-4">
                            <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest">Diagnosis & Notes</h4>
                            <div className="p-6 rounded-2xl bg-white border border-gray-100 shadow-sm">
                                <h5 className="font-bold text-gray-900 mb-3">{cons.diagnosis || 'General Assessment'}</h5>
                                <p className="text-gray-600 mb-0">{cons.clinical_notes || 'No detailed clinical notes provided.'}</p>
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="p-4 rounded-xl bg-gray-50 border border-gray-100">
                                <div className="text-xs font-bold text-gray-400 uppercase mb-1">Doctor</div>
                                <div className="font-bold text-gray-900">Dr. {cons.doctor.user.first_name} {cons.doctor.user.last_name}</div>
                            </div>
                            <div className="p-4 rounded-xl bg-gray-50 border border-gray-100">
                                <div className="text-xs font-bold text-gray-400 uppercase mb-1">Date</div>
                                <div className="font-bold text-gray-900">{cons.consultation_date}</div>
                            </div>
                        </div>
                    </div>
                )
            },
            {
                id: 'plan',
                label: 'Treatment Plan',
                icon: 'fa-clipboard-check',
                content: (
                    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest">Management Strategy</h4>
                        <div className="bg-blue-50 p-8 rounded-3xl border border-blue-100 shadow-inner">
                            <p className="text-gray-800 font-medium leading-loose mb-0">
                                {cons.treatment_plan || 'No treatment plan recorded.'}
                            </p>
                        </div>
                        
                        <div className="p-6 rounded-2xl bg-white border border-gray-100 shadow-sm">
                            <h5 className="text-xs font-bold text-gray-400 uppercase mb-4">Recommendations</h5>
                            <ul className="space-y-3 ps-0 mb-0">
                                <li className="flex gap-3 text-gray-700">
                                    <i className="fas fa-check-circle text-blue-500 mt-1"></i>
                                    <span className="fw-medium">Follow-up scheduled as per facility policy.</span>
                                </li>
                                <li className="flex gap-3 text-gray-700">
                                    <i className="fas fa-check-circle text-blue-500 mt-1"></i>
                                    <span className="fw-medium">Patient advised on adherence to medication regimen.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                )
            },
            {
                id: 'orders',
                label: 'Related Orders',
                icon: 'fa-file-medical',
                content: (
                    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div className="space-y-4">
                            <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest">Laboratory Requests</h4>
                            {cons.lab_test_requests?.length > 0 ? (
                                <div className="space-y-3">
                                    {cons.lab_test_requests.map((l, i) => (
                                        <div key={i} className="p-4 rounded-xl border border-gray-100 bg-white flex justify-between items-center shadow-sm">
                                            <div className="flex items-center gap-3">
                                                <div className="avatar-sm rounded-lg bg-pink-50 text-pink-500 d-flex align-items-center justify-center">
                                                    <i className="fas fa-vial"></i>
                                                </div>
                                                <span className="font-bold text-gray-900">Request #{l.request_id}</span>
                                            </div>
                                            <StatusBadge status={l.status} />
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="p-8 text-center bg-gray-50 rounded-3xl border border-dashed text-gray-400">
                                    <i className="fas fa-vials text-3xl mb-3 opacity-20"></i>
                                    <p className="mb-0 fw-medium">No lab tests requested during this visit.</p>
                                </div>
                            )}
                        </div>

                        <div className="space-y-4">
                            <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest">Prescriptions</h4>
                            {cons.prescriptions?.length > 0 ? (
                                <div className="space-y-3">
                                    {cons.prescriptions.map((p, i) => (
                                        <div key={i} className="p-4 rounded-xl border border-gray-100 bg-white flex justify-between items-center shadow-sm">
                                            <div className="flex items-center gap-3">
                                                <div className="avatar-sm rounded-lg bg-blue-50 text-blue-500 d-flex align-items-center justify-center">
                                                    <i className="fas fa-pills"></i>
                                                </div>
                                                <span className="font-bold text-gray-900">Prescription #{p.prescription_id}</span>
                                            </div>
                                            <Link href={route('prescriptions.show', p.prescription_id)} className="btn btn-sm btn-light border fw-bold rounded-pill px-3">View</Link>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="p-8 text-center bg-gray-50 rounded-3xl border border-dashed text-gray-400">
                                    <i className="fas fa-prescription text-3xl mb-3 opacity-20"></i>
                                    <p className="mb-0 fw-medium">No medications prescribed.</p>
                                </div>
                            )}
                        </div>
                    </div>
                )
            }
        ];
    };

    return (
        <AuthenticatedLayout 
            headerTitle="Clinical Registry"
            breadcrumbs={[{ label: 'Consultations', active: true }]}
        >
            <Head title="Consultations" />

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
                                { label: 'Pending', value: 'pending' },
                                { label: 'In Progress', value: 'in_progress' },
                                { label: 'Completed', value: 'completed' },
                            ]}
                            value={activeFilter}
                            onChange={handleFilterChange}
                            placeholder="Status..."
                            theme="dark"
                            dropup={true}
                        />
                        <DashboardSelect 
                            options={[
                                { label: 'All Consults', value: '' },
                                { label: 'In Progress', value: 'in_progress' },
                                { label: 'Walk-ins', value: 'walk_in' },
                            ]}
                            value={quickFilter}
                            onChange={handleQuickFilterChange}
                            placeholder="Type..."
                            theme="dark"
                            dropup={true}
                        />
                    </>
                }
                actions={[
                    auth.user.role !== 'patient' && { label: 'NEW RECORD', icon: 'fa-plus-circle', href: route('consultations.create') }
                ]}
                bulkActions={[
                    { label: 'MARK COMPLETE', icon: 'fa-check-double', onClick: () => console.log('Complete', selectedIds) },
                    { label: 'EXPORT NOTES', icon: 'fa-file-export', onClick: () => console.log('Export', selectedIds) },
                    { label: 'DELETE', icon: 'fa-trash-alt', onClick: () => console.log('Delete', selectedIds), color: 'danger' }
                ]}
                drafts={drafts.data}
                selectionCount={selectedIds.length}
            />

            <div className="px-0">
                {/* Active Drafts Section */}
                {auth.user.role !== 'patient' && drafts && drafts.data && drafts.data.length > 0 && (
                    <div className="mb-5 animate-in fade-in slide-in-from-top-4 duration-700">
                        <div className="row g-3 flex-nowrap overflow-auto pb-3 no-scrollbar">
                            {drafts.data.map((draft) => (
                                <div key={draft.consultation_id} className="col-11 col-md-5 col-lg-4 flex-shrink-0">
                                    <div className="card h-100 border-0 shadow-sm rounded-2xl bg-white border-start border-4 border-warning shadow-hover transition-all">
                                        <div className="card-body p-4">
                                            <div className="d-flex justify-content-between align-items-center mb-4">
                                                <div className="d-flex align-items-center gap-3">
                                                    <div className="avatar-md bg-warning-subtle text-warning fw-extrabold border border-warning-subtle shadow-inner rounded-circle d-flex align-items-center justify-content-center">
                                                        {draft.patient.user.first_name.charAt(0)}
                                                    </div>
                                                    <div>
                                                        <h6 className="fw-extrabold mb-0 text-truncate text-gray-900" style={{ maxWidth: '140px' }}>
                                                            {draft.patient.user.first_name} {draft.patient.user.last_name}
                                                        </h6>
                                                        <small className="text-muted extra-small font-bold text-uppercase tracking-widest opacity-50">ID: PAT-{draft.patient_id}</small>
                                                    </div>
                                                </div>
                                                <div className="text-end">
                                                    <div className="text-muted extra-small font-bold text-uppercase opacity-30">{formatDateTime(draft.updated_at || draft.consultation_date)}</div>
                                                </div>
                                            </div>
                                            <div className="bg-gray-50 p-3 rounded-xl mb-4 border border-light">
                                                <p className="extra-small text-gray-600 fw-bold mb-0 line-clamp-2 italic opacity-75">
                                                    "{draft.chief_complaint || 'No complaint notes...'}"
                                                </p>
                                            </div>
                                            <Link 
                                                href={route('consultations.edit', draft.consultation_id)} 
                                                className="btn btn-warning w-100 rounded-pill fw-extrabold text-white shadow-sm py-2.5 d-flex align-items-center justify-content-center gap-2 transition-all hover-translate-up"
                                            >
                                                <i className="fas fa-play-circle"></i>
                                                RESUME ASSESSMENT
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                <DashboardSearch 
                    placeholder="Search by diagnosis, complaint or patient name..." 
                    value={search}
                    onChange={setSearch}
                    onSubmit={handleSearch}
                    onFilterChange={handleQuickFilterChange}
                    filters={[
                        { label: 'In Progress', value: 'in_progress' },
                        { label: 'Completed', value: 'completed' },
                        { label: 'Walk-ins', value: 'walk_in' },
                    ]}
                />

                {/* Content View */}
                {view === 'list' ? (
                    <DashboardTable 
                        columns={columns}
                        data={consultations.data}
                        pagination={consultations}
                        emptyMessage="No consultations found."
                        selectable={true}
                        selectedIds={selectedIds}
                        onSelectionChange={setSelectedIds}
                        idField="consultation_id"
                    />
                ) : (
                    <div className="row g-4">
                        {consultations.data.length > 0 ? (
                            <>
                                {consultations.data.map((cons) => (
                                    <div key={cons.consultation_id} className="col-md-6 col-lg-4">
                                        <div className="card h-100 shadow-sm border-0 rounded-2xl overflow-hidden hover-shadow-lg transition-all duration-300 bg-white shadow-hover">
                                            <div className="card-body p-4">
                                                <div className="d-flex justify-content-between align-items-start mb-4">
                                                    <div className="d-flex gap-3">
                                                        <div className="avatar-lg bg-pink-50 text-pink-500 rounded-2xl d-flex align-items-center justify-content-center shadow-inner border border-pink-100">
                                                            <i className="fas fa-stethoscope fa-lg"></i>
                                                        </div>
                                                        <div>
                                                            <Link href={route('patients.show', cons.patient_id)} className="fw-extrabold text-gray-900 text-lg mb-0 text-decoration-none hover:text-pink-500 transition-colors tracking-tighter">
                                                                {cons.patient.user.first_name} {cons.patient.user.last_name}
                                                            </Link>
                                                            <div className="extra-small text-muted font-bold text-uppercase tracking-widest opacity-50">ID: PAT-{cons.patient_id}</div>
                                                        </div>
                                                    </div>
                                                    <StatusBadge status={cons.consultation_status || 'in_progress'} />
                                                </div>

                                                <div className="space-y-3 mb-4">
                                                    <div className="d-flex align-items-center gap-3 text-gray-600">
                                                        <i className="fas fa-calendar-day text-muted w-5"></i>
                                                        <span className="fw-bold text-sm text-gray-700">{cons.consultation_date}</span>
                                                    </div>
                                                    <div className="d-flex align-items-center gap-3 text-gray-600">
                                                        <i className="fas fa-user-md text-muted w-5"></i>
                                                        <span className="fw-bold text-sm text-gray-700">Dr. {cons.doctor.user.first_name} {cons.doctor.user.last_name}</span>
                                                    </div>
                                                    <div className="bg-gray-50 p-4 rounded-2xl border border-light shadow-inner">
                                                        <div className="extra-small text-pink-500 font-bold text-uppercase tracking-widest mb-2 opacity-75">Clinical Impression</div>
                                                        <p className="text-sm text-gray-900 fw-extrabold mb-1 line-clamp-1">{cons.diagnosis || 'General Assessment'}</p>
                                                        <p className="extra-small text-gray-500 mb-0 line-clamp-2 italic opacity-75">"{cons.chief_complaint}"</p>
                                                    </div>
                                                </div>

                                                <div className="d-flex gap-2 border-top pt-4">
                                                    <button 
                                                        onClick={() => openModal(cons)}
                                                        className="btn btn-light bg-gray-50 text-gray-700 rounded-xl flex-1 fw-extrabold border-0 py-3 hover-bg-gray-100 transition-all shadow-sm"
                                                    >
                                                        CLINICAL VIEW
                                                    </button>
                                                    <Link 
                                                        href={route('consultations.show', cons.consultation_id)}
                                                        className="btn btn-outline-primary rounded-xl px-4 border-2 d-flex align-items-center justify-content-center transition-all hover-translate-up shadow-sm"
                                                    >
                                                        <i className="fas fa-file-medical-alt"></i>
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                                
                                {/* Pagination for Grid View */}
                                <div className="col-12 mt-4">
                                    <DashboardTable 
                                        data={[]} 
                                        columns={[]} 
                                        pagination={consultations}
                                        className="bg-transparent shadow-none"
                                    />
                                </div>
                            </>
                        ) : (
                            <div className="col-12 py-16 text-center bg-white rounded-3xl shadow-sm border border-gray-100">
                                <i className="fas fa-notes-medical text-gray-200 text-5xl mb-4 opacity-20"></i>
                                <h4 className="text-gray-400 fw-extrabold tracking-tighter">No consultations recorded</h4>
                                <p className="text-gray-300 small fw-bold">Try searching with different terms.</p>
                            </div>
                        )}
                    </div>
                )}
            </div>

            {/* Quick Info Modal */}
            <InfoModal
                show={modalConfig.show}
                onClose={closeModal}
                title={modalConfig.consultation ? `Clinical Record: ${modalConfig.consultation.patient.user.first_name}` : ''}
                subtitle="Clinical Assessment View"
                tabs={getConsultationTabs(modalConfig.consultation)}
            />
        </AuthenticatedLayout>
    );
}
