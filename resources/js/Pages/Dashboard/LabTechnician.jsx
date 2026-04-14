import DashboardTable from '@/Components/DashboardTable';
import { useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';

export default function LabTechnician({ auth, stats }) {
    const handleProcess = (id) => {
        if (confirm('Start processing this lab request?')) {
            router.post(route('lab.update-status', id), {
                status: 'processing'
            }, {
                preserveScroll: true
            });
        }
    };

    const columns = useMemo(() => [
        {
            header: 'Date',
            accessorKey: 'request_date',
            cell: ({ row }) => <span className="text-muted small">{row.original.request_date}</span>
        },
        {
            header: 'Patient',
            accessorKey: 'patient',
            cell: ({ row }) => <span className="fw-semibold">{row.original.patient?.user?.first_name} {row.original.patient?.user?.last_name}</span>
        },
        {
            header: 'Test Type',
            accessorKey: 'test_type',
            cell: ({ row }) => <span className="text-muted">{row.original.test_type?.test_name || 'N/A'}</span>
        },
        {
            header: 'Priority',
            accessorKey: 'priority',
            cell: ({ row }) => (
                <div>
                    <span className={`badge ${row.original.priority === 'urgent' ? 'bg-danger' : 'bg-info'}`}>
                        {row.original.priority?.toUpperCase() || 'NORMAL'}
                    </span>
                </div>
            )
        },
        {
            header: 'Action',
            id: 'actions',
            cell: ({ row }) => (
                <div className="text-end pe-2">
                    <button 
                        onClick={() => handleProcess(row.original.request_id)}
                        className="btn btn-sm btn-primary rounded-pill px-3 shadow-sm transition-all hover-scale"
                    >
                        Process
                    </button>
                </div>
            )
        }
    ], []);

    return (
        <AuthenticatedLayout
            user={auth.user}
        >
            <Head title="Lab Dashboard" />

            <PageHeader 
                title={`Laboratory - ${auth.user.first_name}`}
                breadcrumbs={[{ label: 'Dashboard', active: true }]}
                showBack={false}
            />

            <div className="container-fluid dashboard-page px-0 h-auto">
                <div className="row g-4 mb-8 h-auto">
                    <div className="col-md-6">
                        <div className="card shadow-sm border-0 h-100 p-4">
                            <div className="d-flex justify-content-between align-items-center">
                                <div>
                                    <div className="text-muted small fw-bold text-uppercase">Pending Tests</div>
                                    <h2 className="fw-bold mb-0">{stats.pending_requests || 0}</h2>
                                </div>
                                <div className="bg-primary-subtle p-3 rounded text-primary">
                                    <i className="fas fa-hourglass-start fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-6">
                        <div className="card shadow-sm border-0 h-100 p-4">
                            <div className="d-flex justify-content-between align-items-center">
                                <div>
                                    <div className="text-muted small fw-bold text-uppercase">Completed Today</div>
                                    <h2 className="fw-bold mb-0">{stats.completed_today || 0}</h2>
                                </div>
                                <div className="bg-success-subtle p-3 rounded text-success">
                                    <i className="fas fa-vials fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="row">
                    <div className="col-lg-12">
                        <div className="card shadow-sm border-0 h-100">
                            <div className="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h6 className="mb-0 fw-bold">Recent Lab Requests</h6>
                                <Link href={route('lab.index')} className="small text-decoration-none">View All</Link>
                            </div>
                            <div className="card-body p-0">
                                <DashboardTable 
                                    columns={columns}
                                    data={stats.recent_requests || []}
                                    emptyMessage="No pending requests."
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
