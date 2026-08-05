import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import DashboardPanel from '@/Components/DashboardPanel';
import StatusBadge from '@/Components/StatusBadge';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { PatientIdLabel } from '@/Components/PatientTableCell';

export default function Show({ sample }) {
    const patient = sample.patient?.user;
    const collector = sample.collected_by_user;

    return (
        <AuthenticatedLayout
            headerTitle={`Sample ${sample.sample_id}`}
            breadcrumbs={[
                { label: 'Samples', url: route('lab.samples.index') },
                { label: sample.sample_id, active: true },
            ]}
        >
            <Head title={`Sample ${sample.sample_id}`} />

            <UnifiedToolbar
                actions={[
                    {
                        label: 'Register another',
                        icon: 'fa-plus',
                        href: route('lab.samples.register'),
                        color: 'success',
                    },
                    { label: 'All samples', icon: 'fa-list', href: route('lab.samples.index'), color: 'gray' },
                    { label: 'Lab requests', icon: 'fa-flask', href: route('lab.index') },
                ]}
            />

            <div className="row g-4">
                <div className="col-lg-8">
                    <DashboardPanel title="Sample details" icon="fa-vial" bodyClassName="p-4">
                        <div className="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
                            <div>
                                <div className="nyl-stat-label text-muted mb-1">Test</div>
                                <h4 className="fw-extrabold mb-0">{sample.test_type?.test_name || '—'}</h4>
                                {sample.test_type?.category && (
                                    <span className="badge bg-light text-primary extra-small fw-bold mt-2">
                                        {sample.test_type.category}
                                    </span>
                                )}
                            </div>
                            <div className="text-end">
                                <StatusBadge status={sample.status} />
                                {sample.urgent && (
                                    <span className="badge bg-danger ms-2 extra-small fw-bold">URGENT</span>
                                )}
                            </div>
                        </div>

                        <div className="row g-4">
                            <div className="col-md-6">
                                <div className="nyl-stat-label text-muted mb-1">Sample type</div>
                                <div className="fw-bold">{sample.sample_type_label}</div>
                            </div>
                            <div className="col-md-6">
                                <div className="nyl-stat-label text-muted mb-1">Collected</div>
                                <div className="fw-bold">{sample.collected_at || sample.collected_date}</div>
                            </div>
                            <div className="col-md-6">
                                <div className="nyl-stat-label text-muted mb-1">Collected by</div>
                                <div className="fw-bold">
                                    {collector ? `${collector.first_name} ${collector.last_name}` : '—'}
                                </div>
                            </div>
                            {sample.completed_at && (
                                <div className="col-md-6">
                                    <div className="nyl-stat-label text-muted mb-1">Completed</div>
                                    <div className="fw-bold">{sample.completed_at}</div>
                                </div>
                            )}
                        </div>

                        {sample.notes && (
                            <div className="mt-4 pt-4 border-top">
                                <h6 className="nyl-stat-label text-muted mb-2">Notes</h6>
                                <p className="mb-0 text-muted">{sample.notes}</p>
                            </div>
                        )}
                    </DashboardPanel>
                </div>

                <div className="col-lg-4">
                    <DashboardPanel title="Patient" icon="fa-user-injured" bodyClassName="p-4">
                        {patient ? (
                            <>
                                <div className="fw-extrabold fs-5 mb-1">
                                    {patient.first_name} {patient.last_name}
                                </div>
                                <PatientIdLabel
                                    id={sample.patient_id}
                                    variant="short"
                                    className="text-muted small mb-3"
                                />
                                <Link
                                    href={route('patients.show', sample.patient_id)}
                                    className="btn btn-sm btn-outline-primary rounded-pill"
                                >
                                    View patient record
                                </Link>
                            </>
                        ) : (
                            <p className="text-muted mb-0">Patient not found.</p>
                        )}
                    </DashboardPanel>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
