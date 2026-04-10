import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';

export default function Requests({ requests, filters }) {
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
            header="Lab Requests"
        >
            <Head title="Lab Requests" />

            <PageHeader 
                title="Laboratory Requests"
                breadcrumbs={[{ label: 'Lab', url: route('lab.index') }, { label: 'Requests', active: true }]}
                actions={
                    <Link href={route('lab.tests')} className="btn btn-outline-primary rounded-pill px-4 font-bold shadow-sm">
                        <i className="fas fa-list me-2"></i>Test Catalog
                    </Link>
                }
            />

            <div className="py-0">
                <div className="card shadow-sm border-0 rounded-xl overflow-hidden">
                    <div className="card-header bg-white py-3 border-bottom-0 d-none">
                        <div className="d-flex justify-content-between align-items-center">
                            <h5 className="mb-0 fw-bold text-gray-800">Laboratory Requests Queue</h5>
                        </div>
                    </div>
                    
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead className="bg-light">
                                <tr className="text-uppercase small tracking-wider">
                                    <th className="px-4 py-3 text-muted">Request ID</th>
                                    <th className="py-3 text-muted">Patient</th>
                                    <th className="py-3 text-muted">Test Type</th>
                                    <th className="py-3 text-muted">Status</th>
                                    <th className="py-3 text-muted">Date</th>
                                    <th className="pe-4 py-3 text-end text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {requests.data.length > 0 ? (
                                    requests.data.map((req) => (
                                        <tr key={req.request_id}>
                                            <td className="px-4 fw-bold text-gray-900">LAB-{req.request_id}</td>
                                            <td>
                                                <div className="fw-semibold">{req.patient?.user?.first_name} {req.patient?.user?.last_name}</div>
                                                <small className="text-muted">ID: PAT-{req.patient_id}</small>
                                            </td>
                                            <td>
                                                <span className="badge bg-soft-info text-info rounded-pill px-3">
                                                    {req.test_type?.name || 'Standard Test'}
                                                </span>
                                            </td>
                                            <td>
                                                <span className={`badge rounded-pill px-3 py-1 ${
                                                    req.status === 'completed' ? 'bg-success' : 'bg-warning text-dark'
                                                }`}>
                                                    {req.status}
                                                </span>
                                            </td>
                                            <td>{new Date(req.created_at).toLocaleDateString()}</td>
                                            <td className="pe-4 text-end">
                                                {req.status === 'pending' ? (
                                                    <button 
                                                        onClick={() => handleProcess(req.request_id)}
                                                        className="btn btn-sm btn-primary rounded-pill px-3"
                                                    >
                                                        Process
                                                    </button>
                                                ) : (
                                                    <Link 
                                                        href={route('lab.show', req.request_id)}
                                                        className="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                    >
                                                        View
                                                    </Link>
                                                )}
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="6" className="text-center py-5 text-muted">
                                            No lab requests found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
