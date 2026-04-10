import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';

export default function Pharmacist({ auth, stats }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
        >
            <Head title="Pharmacy Dashboard" />

            <PageHeader 
                title={`Pharmacy Station - ${auth.user.first_name}`}
                breadcrumbs={[{ label: 'Dashboard', active: true }]}
                showBack={false}
            />

            <div className="container-fluid dashboard-page px-0 h-auto">
                <div className="row g-4 mb-8 h-auto">
                    <div className="col-md-4">
                        <div className="card shadow-sm border-0 h-100 p-4 border-start border-4 border-warning">
                            <div className="d-flex justify-content-between align-items-center">
                                <div>
                                    <div className="text-muted small fw-bold text-uppercase">Pending Dispensing</div>
                                    <h2 className="fw-bold mb-0">{stats.pending_prescriptions || 0}</h2>
                                </div>
                                <div className="bg-warning-subtle p-3 rounded text-warning">
                                    <i className="fas fa-prescription fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-4">
                        <div className="card shadow-sm border-0 h-100 p-4 border-start border-4 border-success">
                            <div className="d-flex justify-content-between align-items-center">
                                <div>
                                    <div className="text-muted small fw-bold text-uppercase">Dispensed Today</div>
                                    <h2 className="fw-bold mb-0">14</h2>
                                </div>
                                <div className="bg-success-subtle p-3 rounded text-success">
                                    <i className="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-4">
                        <div className="card shadow-sm border-0 h-100 p-4 border-start border-4 border-danger">
                            <div className="d-flex justify-content-between align-items-center">
                                <div>
                                    <div className="text-muted small fw-bold text-uppercase">Low Stock Alerts</div>
                                    <h2 className="fw-bold mb-0">5</h2>
                                </div>
                                <div className="bg-danger-subtle p-3 rounded text-danger">
                                    <i className="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="row">
                    <div className="col-lg-12">
                        <div className="card shadow-sm border-0 h-100">
                            <div className="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h6 className="mb-0 fw-bold">Recent Pending Prescriptions</h6>
                                <Link href={route('prescriptions.index')} className="small text-decoration-none">View All</Link>
                            </div>
                            <div className="card-body p-0">
                                <div className="table-responsive">
                                    <table className="table table-hover align-middle mb-0">
                                        <thead className="bg-light">
                                            <tr>
                                                <th className="px-4">Date</th>
                                                <th>Patient</th>
                                                <th>Doctor</th>
                                                <th className="pe-4 text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {stats.recent_prescriptions?.length > 0 ? (
                                                stats.recent_prescriptions.map((p) => (
                                                    <tr key={p.prescription_id}>
                                                        <td className="px-4">{p.prescription_date}</td>
                                                        <td>{p.patient.user.first_name} {p.patient.user.last_name}</td>
                                                        <td>Dr. {p.doctor.first_name} {p.doctor.last_name}</td>
                                                        <td className="pe-4 text-end">
                                                            <Link href={route('prescriptions.show', p.prescription_id)} className="btn btn-sm btn-success shadow-sm">Dispense</Link>
                                                        </td>
                                                    </tr>
                                                ))
                                            ) : (
                                                <tr>
                                                    <td colSpan="4" className="text-center py-5 text-muted">No pending prescriptions.</td>
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
