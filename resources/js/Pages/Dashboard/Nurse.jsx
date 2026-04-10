import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';

export default function Nurse({ auth, stats }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
        >
            <Head title="Nurse Dashboard" />

            <PageHeader 
                title={`Nurse Station - ${auth.user.first_name}`}
                breadcrumbs={[{ label: 'Dashboard', active: true }]}
                showBack={false}
            />

            <div className="container-fluid dashboard-page px-0 h-auto">
                <div className="row g-4 mb-8 h-auto">
                    <div className="col-md-6">
                        <div className="card shadow-sm border-0 h-100 p-4 border-start border-4 border-info">
                            <div className="d-flex justify-content-between align-items-center">
                                <div>
                                    <div className="text-muted small fw-bold text-uppercase">Checked-In Today</div>
                                    <h2 className="fw-bold mb-0">{stats.checked_in_patients || 0}</h2>
                                </div>
                                <div className="bg-info-subtle p-3 rounded text-info">
                                    <i className="fas fa-user-check fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-6">
                        <div className="card shadow-sm border-0 h-100 p-4 border-start border-4 border-primary">
                            <div className="d-flex justify-content-between align-items-center">
                                <div>
                                    <div className="text-muted small fw-bold text-uppercase">Triage Queue</div>
                                    <h2 className="fw-bold mb-0">4</h2>
                                </div>
                                <div className="bg-primary-subtle p-3 rounded text-primary">
                                    <i className="fas fa-heartbeat fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="row">
                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 mb-4 h-100">
                            <div className="card-header bg-white py-3">
                                <h6 className="mb-0 fw-bold">Upcoming Appointments (Triage)</h6>
                            </div>
                            <div className="card-body p-0">
                                <div className="table-responsive">
                                    <table className="table table-hover align-middle mb-0">
                                        <thead className="bg-light">
                                            <tr>
                                                <th className="px-4">Time</th>
                                                <th>Patient</th>
                                                <th>Doctor</th>
                                                <th className="pe-4 text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {stats.upcoming_appointments?.length > 0 ? (
                                                stats.upcoming_appointments.map((a) => (
                                                    <tr key={a.appointment_id}>
                                                        <td className="px-4 fw-bold">{a.appointment_time}</td>
                                                        <td>{a.patient.user.first_name} {a.patient.user.last_name}</td>
                                                        <td>Dr. {a.doctor.user.last_name}</td>
                                                        <td className="pe-4 text-end">
                                                            <button className="btn btn-sm btn-info shadow-sm">Record Vitals</button>
                                                        </td>
                                                    </tr>
                                                ))
                                            ) : (
                                                <tr>
                                                    <td colSpan="4" className="text-center py-5 text-muted">No upcoming appointments in queue.</td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div className="col-lg-4">
                        <div className="card shadow-sm border-0 mb-4 bg-white overflow-hidden h-100">
                            <div className="card-header bg-white py-3 border-0">
                                <h6 className="mb-0 fw-bold">Nursing Quick Actions</h6>
                            </div>
                            <div className="card-body p-4 pt-0">
                                <div className="d-grid gap-3">
                                    <Link href={route('patients.index')} className="btn btn-light border text-start p-3 d-flex align-items-center">
                                        <div className="bg-primary-subtle text-primary p-2 rounded me-3">
                                            <i className="fas fa-user-plus"></i>
                                        </div>
                                        <div>
                                            <div className="fw-bold small">New Registration</div>
                                            <div className="text-muted extra-small">Register walk-in patient</div>
                                        </div>
                                    </Link>
                                    <button className="btn btn-light border text-start p-3 d-flex align-items-center">
                                        <div className="bg-danger-subtle text-danger p-2 rounded me-3">
                                            <i className="fas fa-notes-medical"></i>
                                        </div>
                                        <div>
                                            <div className="fw-bold small">Emergency Triage</div>
                                            <div className="text-muted extra-small">Immediate assessment</div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <style>{`
                .extra-small {
                    font-size: 0.75rem;
                }
            `}</style>
        </AuthenticatedLayout>
    );
}
