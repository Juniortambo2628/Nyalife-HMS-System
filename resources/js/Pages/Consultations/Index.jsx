import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import DashboardSearch from '@/Components/DashboardSearch';
import StatusBadge from '@/Components/StatusBadge';
import DashboardTable from '@/Components/DashboardTable';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import ViewToggle from '@/Components/ViewToggle';
import InfoModal from '@/Components/InfoModal';
import TableActions from '@/Components/TableActions';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import GridCardActions from '@/Components/GridCardActions';
import { PatientIdLabel } from '@/Components/PatientTableCell';
import { TableCellPrimary, TableCellStack, TableCellSub } from '@/Components/TableCells';
import StatCardGrid from '@/Components/StatCardGrid';
import { useState, useMemo, useEffect } from 'react';
import { formatDateTime } from '@/Utils/dateUtils';

export default function Index({ consultations, drafts = [], filters, auth, stats }) {
    const [view, setView] = useState(() => localStorage.getItem('consultations_view') || 'list');
    const [search, setSearch] = useState(filters.search || '');
    const [activeFilter, setActiveFilter] = useState(filters.status || '');
    const [quickFilter, setQuickFilter] = useState(filters.quick_filter || '');
    const [selectedIds, setSelectedIds] = useState([]);

    const statItems = useMemo(() => [
        {
            label: 'Total Consultations',
            value: stats?.total ?? 0,
            icon: 'fa-stethoscope',
            color: 'primary',
        },
        {
            label: 'In Progress',
            value: stats?.in_progress ?? 0,
            icon: 'fa-spinner',
            color: 'warning',
        },
        {
            label: 'Completed',
            value: stats?.completed ?? 0,
            icon: 'fa-check-circle',
            color: 'success',
        },
        {
            label: 'Today',
            value: stats?.today ?? 0,
            icon: 'fa-calendar-day',
            color: 'info',
        },
    ], [stats]);

    useEffect(() => {
        const handleClear = () => setSelectedIds([]);
        window.addEventListener('toolbar-clear-selection', handleClear);
        return () => window.removeEventListener('toolbar-clear-selection', handleClear);
    }, []);
    
    const [modalConfig, setModalConfig] = useState({
        show: false,
        consultation: null,
    });

    const handleBulkAction = (action) => {
        router.post(route('consultations.bulk-action'), {
            action: action,
            ids: selectedIds
        }, {
            onSuccess: () => setSelectedIds([]),
        });
    };

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
                <TableCellSub>{formatDateTime(row.original.consultation_date)}</TableCellSub>
            )
        },
        {
            header: 'Patient',
            accessorKey: 'patient_id',
            cell: ({ row }) => (
                <div>
                    <Link href={route('patients.show', row.original.patient_id)} className="text-decoration-none">
                        <TableCellPrimary className="text-pink-500">
                            {row.original.patient.user.first_name} {row.original.patient.user.last_name}
                        </TableCellPrimary>
                    </Link>
                    <PatientIdLabel id={row.original.patient_id} />
                </div>
            )
        },
        {
            header: 'Doctor',
            accessorKey: 'doctor_id',
            cell: ({ row }) => (
                <TableCellStack
                    primary={`Dr. ${row.original.doctor.user.first_name} ${row.original.doctor.user.last_name}`}
                />
            )
        },
        {
            header: 'Diagnosis',
            accessorKey: 'diagnosis',
            cell: ({ row }) => (
                <TableCellStack
                    primary={row.original.diagnosis || 'Clinical notes'}
                    secondary={row.original.chief_complaint}
                />
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

    const visitPrescription = (prescriptionId) => {
        closeModal();
        router.visit(route('prescriptions.show', prescriptionId));
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
                            <div className="nyl-content-box__title">Chief Complaint</div>
                            <div className="nyl-content-box nyl-content-box--highlight fw-medium">
                                {cons.chief_complaint}
                            </div>
                        </div>
                        
                        <div className="space-y-4">
                            <div className="nyl-content-box__title">Diagnosis & Notes</div>
                            <div className="nyl-content-box">
                                <h5 className="font-bold text-gray-900 mb-3">{cons.diagnosis || 'General Assessment'}</h5>
                                <p className="text-gray-600 mb-0">{cons.clinical_notes || 'No detailed clinical notes provided.'}</p>
                            </div>
                        </div>

                        <div className="nyl-meta-grid">
                            <div className="nyl-meta-item">
                                <div className="nyl-meta-item__label">Doctor</div>
                                <div className="nyl-meta-item__value">Dr. {cons.doctor.user.first_name} {cons.doctor.user.last_name}</div>
                            </div>
                            <div className="nyl-meta-item">
                                <div className="nyl-meta-item__label">Date</div>
                                <div className="nyl-meta-item__value">{cons.consultation_date}</div>
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
                        <div className="nyl-content-box__title">Management Strategy</div>
                        <div className="nyl-content-box nyl-content-box--info fw-medium leading-loose">
                            {cons.treatment_plan || 'No treatment plan recorded.'}
                        </div>
                        
                        <div className="nyl-content-box">
                            <div className="nyl-content-box__title mb-4">Recommendations</div>
                            <ul className="space-y-3 ps-0 mb-0 list-unstyled">
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
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-light border fw-bold rounded-pill px-3"
                                                onClick={() => visitPrescription(p.prescription_id)}
                                            >
                                                View
                                            </button>
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

            <StatCardGrid items={statItems} cols={4} />

            <UnifiedToolbar 
                viewMode={view}
                onViewModeChange={handleViewChange}
                filterGroups={[
                    {
                        id: 'status',
                        label: 'Status',
                        emptyLabel: 'All status',
                        value: activeFilter,
                        onChange: handleFilterChange,
                        options: [
                            { label: 'Pending', value: 'pending' },
                            { label: 'In Progress', value: 'in_progress' },
                            { label: 'Completed', value: 'completed' },
                        ],
                    },
                    {
                        id: 'type',
                        label: 'Type',
                        emptyLabel: 'All consults',
                        value: quickFilter,
                        onChange: handleQuickFilterChange,
                        options: [
                            { label: 'In Progress', value: 'in_progress' },
                            { label: 'Walk-ins', value: 'walk_in' },
                        ],
                    },
                ]}
                actions={[
                    auth.user.role !== 'patient' && { label: 'NEW RECORD', icon: 'fa-plus-circle', href: route('consultations.create') }
                ]}
                bulkActions={[
                    { label: 'MARK COMPLETE', icon: 'fa-check-double', onClick: () => handleBulkAction('complete') },
                    { label: 'EXPORT NOTES', icon: 'fa-file-export', onClick: () => handleBulkAction('export') },
                    { label: 'DELETE', icon: 'fa-trash-alt', onClick: () => handleBulkAction('delete'), color: 'danger' }
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
                                                        <PatientIdLabel id={draft.patient_id} />
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
                    <RegistryTablePanel
                        title="Consultation registry"
                        icon="fa-stethoscope"
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
                                                            <PatientIdLabel id={cons.patient_id} />
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

                                                <GridCardActions actions={[
                                                    { icon: 'fa-stethoscope', label: 'Clinical view', onClick: () => openModal(cons) },
                                                    { icon: 'fa-file-medical-alt', label: 'Open record', href: route('consultations.show', cons.consultation_id) },
                                                ]} />
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
