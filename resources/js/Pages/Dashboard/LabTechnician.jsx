import DashboardTable from '@/Components/DashboardTable';
import StatCard from '@/Components/StatCard';
import { useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import DashboardHero from '@/Components/DashboardHero';

export default function LabTechnician({ auth, stats }) {
    const handleProcess = (id) => {
        if (confirm('Start processing this lab request?')) {
            router.post(route('lab.update-status', id), {
                status: 'processing'
            }, {
                preserveScroll: true,
                onSuccess: () => router.visit(route('lab.show', id))
            });
        }
    };

    const columns = useMemo(() => [
        {
            header: 'Patient',
            accessorKey: 'patient',
            cell: ({ row }) => (
                <div>
                    <div className="fw-bold text-gray-900">{row.original.patient?.user?.first_name} {row.original.patient?.user?.last_name}</div>
                    <div className="extra-small text-muted fw-bold text-uppercase opacity-75">ID: PAT-{row.original.patient_id}</div>
                </div>
            )
        },
        {
            header: 'Test Type',
            accessorKey: 'test_type',
            cell: ({ row }) => <span className="fw-semibold text-gray-700">{row.original.test_type?.test_name || 'N/A'}</span>
        },
        {
            header: 'Priority',
            accessorKey: 'priority',
            cell: ({ row }) => {
                const p = (row.original.priority || 'normal').toLowerCase();
                const colors = {
                    urgent: 'bg-danger-subtle text-danger border-danger-subtle',
                    stat: 'bg-dark text-white border-dark',
                    normal: 'bg-info-subtle text-info border-info-subtle'
                };
                return (
                    <span className={`badge rounded-pill px-3 py-2 fw-bold border nyl-badge-sm ${colors[p] || colors.normal}`}>
                        {p.toUpperCase()}
                    </span>
                );
            }
        },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: ({ row }) => {
                const s = row.original.status;
                const colors = {
                    processing: 'bg-primary-subtle text-primary border-primary-subtle',
                    pending: 'bg-warning-subtle text-warning border-warning-subtle',
                    completed: 'bg-success-subtle text-success border-success-subtle'
                };
                return (
                    <span className={`badge rounded-pill px-3 py-2 fw-bold border nyl-badge-sm ${colors[s] || 'bg-light'}`}>
                        {s.toUpperCase()}
                    </span>
                );
            }
        },
        {
            header: 'Action',
            id: 'actions',
            cell: ({ row }) => (
                <div className="text-end">
                    {row.original.status === 'pending' ? (
                        <button 
                            onClick={() => handleProcess(row.original.request_id)}
                            className="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm hover-scale"
                        >
                            Start Work
                        </button>
                    ) : (
                        <Link href={route('lab.show', row.original.request_id)} className="btn btn-sm btn-light border text-primary rounded-pill px-4 fw-bold hover-scale">
                            Enter Results
                        </Link>
                    )}
                </div>
            )
        }
    ], []);

    const statItems = [
        { label: 'Awaiting Processing', value: stats.pending_requests || 0, icon: 'fa-hourglass-start', color: 'primary' },
        { label: 'Completed Today', value: stats.completed_today || 0, icon: 'fa-check-double', color: 'success' }
    ];

    return (
        <AuthenticatedLayout 
            header="Laboratory Dashboard"
            toolbarActions={
                <div className="d-flex align-items-center gap-2">
                    <Link href={route('lab.index')} className="btn btn-light border rounded-pill px-4 py-2 fw-bold small shadow-sm">
                        <i className="fas fa-list-ul me-1"></i> Request Registry
                    </Link>
                    <Link href={route('lab.tests')} className="btn btn-primary rounded-pill px-4 py-2 fw-bold small shadow-sm">
                        <i className="fas fa-vials me-1"></i> Test Catalog
                    </Link>
                </div>
            }
        >
            <Head title="Lab Dashboard" />

            <PageHeader 
                title={`Lab Station - ${auth.user.first_name}`}
                breadcrumbs={[{ label: 'Dashboard', active: true }]}
                showBack={false}
            />

            <div className="px-0">
                <DashboardHero 
                    title="Pathology Lab Station"
                    subtitle={`Manage diagnostic requests and reports. You have ${stats.pending_requests || 0} pending tests awaiting processing.`}
                    icon="fa-flask"
                />


                <div className="row g-4 mb-4">
                    {statItems.map((s, i) => (
                        <div key={i} className="col-md-6">
                            <StatCard {...s} />
                        </div>
                    ))}
                </div>

                <div className="card shadow-sm border-0 rounded-2xl mb-4 bg-white overflow-hidden shadow-hover">
                    <div className="card-header bg-white py-4 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                        <h6 className="mb-0 fw-extrabold text-gray-900"><i className="fas fa-microscope text-pink-500 me-2"></i>Active Diagnostics Queue</h6>
                        <Link href={route('lab.index')} className="btn btn-light btn-sm rounded-pill px-3 fw-bold border text-muted">Full Registry</Link>
                    </div>
                    <div className="card-body p-0">
                        <DashboardTable 
                            columns={columns}
                            data={stats.recent_requests || []}
                            emptyMessage="No pending diagnostic requests."
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
