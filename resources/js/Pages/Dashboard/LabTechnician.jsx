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
                        <div className="card shadow-sm border-0 h-100 p-4 border-start border-4 border-primary">
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
                        <div className="card shadow-sm border-0 h-100 p-4 border-start border-4 border-success">
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
                                <div className="table-responsive">
                                    <table className="table table-hover align-middle mb-0">
                                        <thead className="bg-light">
                                            <tr>
                                                <th className="px-4">Date</th>
                                                <th>Patient</th>
                                                <th>Test Type</th>
                                                <th className="text-center">Priority</th>
                                                <th className="pe-4 text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {stats.recent_requests?.length > 0 ? (
                                                stats.recent_requests.map((r) => (
                                                    <tr key={r.request_id}>
                                                        <td className="px-4">{r.request_date}</td>
                                                        <td>{r.patient?.user?.first_name} {r.patient?.user?.last_name}</td>
                                                        <td>{r.test_type?.test_name || 'N/A'}</td>
                                                        <td className="text-center">
                                                            <span className={`badge ${r.priority === 'urgent' ? 'bg-danger' : 'bg-info'}`}>
                                                                {r.priority?.toUpperCase() || 'NORMAL'}
                                                            </span>
                                                        </td>
                                                        <td className="pe-4 text-end">
                                                            <button 
                                                                onClick={() => handleProcess(r.request_id)}
                                                                className="btn btn-sm btn-primary shadow-sm"
                                                            >
                                                                Process
                                                            </button>
                                                        </td>
                                                    </tr>
                                                ))
                                            ) : (
                                                <tr>
                                                    <td colSpan="5" className="text-center py-5 text-muted">No pending requests.</td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
