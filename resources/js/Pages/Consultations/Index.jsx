import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';
import ViewToggle from '@/Components/ViewToggle';
import InfoModal from '@/Components/InfoModal';
import PageHeader from '@/Components/PageHeader';
import { useState, useMemo } from 'react';

export default function Index({ consultations, filters, auth }) {
    const [view, setView] = useState(() => localStorage.getItem('consultations_view') || 'list');
    const [search, setSearch] = useState(filters.search || '');
    
    const [modalConfig, setModalConfig] = useState({
        show: false,
        consultation: null,
    });

    const handleViewChange = (newView) => {
        setView(newView);
        localStorage.setItem('consultations_view', newView);
    };

    const handleSearch = (searchValue) => {
        router.get(route('consultations.index'), { search: searchValue }, {
            preserveState: true,
            replace: true,
        });
    };

    const columns = useMemo(() => [
        {
            header: 'Date',
            accessorKey: 'consultation_date',
            cell: ({ row }) => (
                <div className="fw-bold text-gray-900">{row.original.consultation_date}</div>
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
                    <div className="extra-small text-muted font-bold text-uppercase opacity-75">ID: PAT-{row.original.patient_id}</div>
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
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => (
                <div className="d-flex justify-content-end gap-2">
                    <button 
                        onClick={() => openModal(row.original)}
                        className="btn btn-sm btn-outline-primary rounded-circle p-2" 
                        title="Clinical View"
                        style={{ width: '32px', height: '32px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                    >
                        <i className="fas fa-stethoscope text-xs"></i>
                    </button>
                    <Link 
                        href={route('consultations.show', row.original.consultation_id)}
                        className="btn btn-sm btn-outline-secondary rounded-circle p-2" 
                        title="Full Record"
                        style={{ width: '32px', height: '32px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                    >
                        <i className="fas fa-file-medical-alt text-xs"></i>
                    </Link>
                </div>
            )
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
                            <div className="bg-pink-50 p-6 rounded-2xl border border-pink-100 text-gray-800 font-medium leading-relaxed">
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
                        <div className="bg-blue-50/50 p-8 rounded-3xl border border-blue-100 shadow-inner">
                            <p className="text-gray-800 font-medium leading-loose mb-0">
                                {cons.treatment_plan || 'No treatment plan recorded.'}
                            </p>
                        </div>
                        
                        <div className="p-6 rounded-2xl bg-white border border-gray-100 shadow-sm">
                            <h5 className="text-xs font-bold text-gray-400 uppercase mb-4">Recommendations</h5>
                            <ul className="space-y-3 ps-0">
                                <li className="flex gap-3 text-gray-700">
                                    <i className="fas fa-check-circle text-blue-500 mt-1"></i>
                                    <span>Follow-up scheduled as per facility policy.</span>
                                </li>
                                <li className="flex gap-3 text-gray-700">
                                    <i className="fas fa-check-circle text-blue-500 mt-1"></i>
                                    <span>Patient advised on adherence to medication regimen.</span>
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
                                                <i className="fas fa-vial text-pink-500"></i>
                                                <span className="font-bold text-gray-900">Request #{l.request_id}</span>
                                            </div>
                                            <span className="badge bg-light text-dark">{l.status}</span>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="p-6 text-center bg-gray-50 rounded-2xl text-gray-400">No lab tests requested during this visit.</div>
                            )}
                        </div>

                        <div className="space-y-4">
                            <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest">Prescriptions</h4>
                            {cons.prescriptions?.length > 0 ? (
                                <div className="space-y-3">
                                    {cons.prescriptions.map((p, i) => (
                                        <div key={i} className="p-4 rounded-xl border border-gray-100 bg-white flex justify-between items-center shadow-sm">
                                            <div className="flex items-center gap-3">
                                                <i className="fas fa-pills text-blue-500"></i>
                                                <span className="font-bold text-gray-900">Prescription #{p.prescription_id}</span>
                                            </div>
                                            <Link href={route('prescriptions.show', p.prescription_id)} className="btn btn-sm btn-outline-primary">View</Link>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="p-6 text-center bg-gray-50 rounded-2xl text-gray-400">No medications prescribed.</div>
                            )}
                        </div>
                    </div>
                )
            }
        ];
    };

    return (
        <AuthenticatedLayout
            header="Clinical Consultations"
        >
            <Head title="Consultations" />

            <PageHeader 
                title="Clinical Registry"
                breadcrumbs={[{ label: 'Consultations', active: true }]}
                actions={
                    <div className="d-flex align-items-center gap-3">
                        <ViewToggle view={view} setView={handleViewChange} />
                        <Link href={route('consultations.create')} className="btn btn-primary shadow-sm rounded-pill px-4 fw-bold">
                            <i className="fas fa-plus me-2"></i>New Consultation
                        </Link>
                    </div>
                }
            />

            <div className="px-0">
                <DashboardSearch 
                    placeholder="Search by diagnosis, complaint or patient name..." 
                    value={search}
                    onChange={setSearch}
                    onSubmit={handleSearch}
                />

                {/* Content View */}
                {view === 'list' ? (
                    <DashboardTable 
                        columns={columns}
                        data={consultations.data}
                        pagination={consultations}
                        emptyMessage="No consultations found."
                    />
                ) : (
                    <div className="row g-4">
                        {consultations.data.length > 0 ? (
                            consultations.data.map((cons) => (
                                <div key={cons.consultation_id} className="col-md-6 col-lg-4">
                                    <div className="card h-100 shadow-sm border-0 rounded-2xl overflow-hidden hover-shadow-lg transition-all duration-300">
                                        <div className="card-body p-4">
                                            <div className="d-flex justify-content-between align-items-start mb-4">
                                                <div className="d-flex gap-3">
                                                    <div className="bg-pink-50 text-pink-500 rounded-2xl p-3 flex items-center justify-center" style={{ width: '56px', height: '56px' }}>
                                                        <i className="fas fa-stethoscope fa-lg"></i>
                                                    </div>
                                                    <div>
                                                        <Link href={route('patients.show', cons.patient_id)} className="fw-bold text-gray-900 text-lg mb-0 text-decoration-none">
                                                            {cons.patient.user.first_name} {cons.patient.user.last_name}
                                                        </Link>
                                                        <div className="extra-small text-muted font-bold text-uppercase tracking-widest">ID: PAT-{cons.patient_id}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="space-y-3 mb-4">
                                                <div className="flex items-center gap-3 text-gray-600">
                                                    <i className="fas fa-calendar-day text-muted w-5"></i>
                                                    <span className="font-medium text-sm">{cons.consultation_date}</span>
                                                </div>
                                                <div className="flex items-center gap-3 text-gray-600">
                                                    <i className="fas fa-user-md text-muted w-5"></i>
                                                    <span className="font-medium text-sm">Dr. {cons.doctor.user.first_name} {cons.doctor.user.last_name}</span>
                                                </div>
                                                <div className="bg-gray-50 p-3 rounded-xl">
                                                    <div className="text-xs text-gray-400 font-bold uppercase mb-1">Diagnosis</div>
                                                    <p className="text-sm text-gray-800 fw-bold mb-1 line-clamp-1">{cons.diagnosis || 'General Assessment'}</p>
                                                    <p className="extra-small text-gray-500 mb-0 line-clamp-2 italic">"{cons.chief_complaint}"</p>
                                                </div>
                                            </div>

                                            <div className="d-flex gap-2 border-top pt-4">
                                                <button 
                                                    onClick={() => openModal(cons)}
                                                    className="btn btn-light bg-gray-50 text-gray-700 rounded-xl flex-1 fw-bold border-0 py-2.5"
                                                >
                                                    Clinical View
                                                </button>
                                                <Link 
                                                    href={route('consultations.show', cons.consultation_id)}
                                                    className="btn btn-outline-primary rounded-xl px-4 border-2"
                                                >
                                                    <i className="fas fa-file-medical-alt"></i>
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="col-12 py-16 text-center bg-white rounded-3xl shadow-sm border border-gray-100">
                                <i className="fas fa-notes-medical text-gray-200 text-5xl mb-4"></i>
                                <h4 className="text-gray-400 fw-bold">No consultations recorded</h4>
                                <p className="text-gray-300">Try searching with different terms.</p>
                            </div>
                        )}
                    </div>
                )}
                
                {/* Pagination */}
                {consultations.links.length > 3 && (
                    <div className="card-footer bg-white border-top-0 py-3 mt-4">
                        <nav aria-label="Page navigation">
                            <ul className="pagination pagination-sm justify-content-center mb-0">
                                {consultations.links.map((link, i) => (
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
                title={modalConfig.consultation ? `Clinical: ${modalConfig.consultation.patient?.user?.first_name || 'Patient'}` : ''}
                subtitle="Assessment Details"
                tabs={getConsultationTabs(modalConfig.consultation)}
            />

            <style>{`
                .rounded-xl { border-radius: 1rem; }
                .rounded-2xl { border-radius: 1.5rem; }
                .rounded-3xl { border-radius: 2rem; }
                .text-pink-500 { color: #ed64a6; }
                .extra-small { font-size: 0.7rem; }
                .tracking-wider { letter-spacing: 0.05em; }
                .bg-pink-50 { background-color: #fffafb; }
                .border-pink-100 { border-color: #fed7e2; }
                .shadow-inner { box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06); }
            `}</style>
        </AuthenticatedLayout>
    );
}
