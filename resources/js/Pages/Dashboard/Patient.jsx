import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';

export default function Patient({ auth, stats }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
        >
            <Head title="My Dashboard" />

            <PageHeader 
                title={`Welcome, ${auth.user.first_name}!`}
                breadcrumbs={[{ label: 'Dashboard', active: true }]}
                showBack={false}
            />

            <div className="container-fluid dashboard-page px-0 h-auto">
                <div className="row mb-4 h-auto">
                    <div className="col-12">
                        <div className="card shadow-sm border-0 bg-success text-white overflow-hidden" style={{ borderRadius: '15px' }}>
                            <div className="card-body p-4 p-md-5 d-flex align-items-center position-relative">
                                <div style={{ zIndex: '1' }}>
                                    <h2 className="fw-bold mb-2">Hello, {auth.user.first_name}!</h2>
                                    <p className="mb-0 opacity-75">Your health is our priority. You have {stats.my_appointments?.length || 0} upcoming visits.</p>
                                </div>
                                <i className="fas fa-heartbeat position-absolute" style={{ fontSize: '10rem', right: '2rem', opacity: '0.1', transform: 'rotate(-10deg)' }}></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="row g-4 mb-4">
                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 mb-4 h-100">
                            <div className="card-header bg-white py-3">
                                <h6 className="mb-0 fw-bold">My Upcoming Appointments</h6>
                            </div>
                            <div className="card-body p-0">
                                <div className="table-responsive">
                                    <table className="table table-hover align-middle mb-0">
                                        <thead className="bg-light">
                                            <tr>
                                                <th className="px-4">Date & Time</th>
                                                <th>Doctor</th>
                                                <th className="pe-4 text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {stats.my_appointments?.length > 0 ? (
                                                stats.my_appointments.map((a) => (
                                                    <tr key={a.appointment_id}>
                                                        <td className="px-4">
                                                            <div className="fw-bold">{a.appointment_date}</div>
                                                            <small className="text-muted">{a.appointment_time}</small>
                                                        </td>
                                                        <td>Dr. {a.doctor.user.first_name} {a.doctor.user.last_name}</td>
                                                        <td className="pe-4 text-end">
                                                            <Link href={route('appointments.show', a.appointment_id)} className="btn btn-sm btn-outline-primary shadow-sm">
                                                                Details
                                                            </Link>
                                                        </td>
                                                    </tr>
                                                ))
                                            ) : (
                                                <tr>
                                                    <td colSpan="3" className="text-center py-5 text-muted">No upcoming appointments.</td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div className="card-footer bg-white border-top-0 py-3 text-center">
                                <Link href={route('appointments.create')} className="btn btn-primary btn-sm px-4 shadow-sm">
                                    <i className="fas fa-calendar-plus me-2"></i>Book New Appointment
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div className="col-lg-4">
                        <div className="card shadow-sm border-0 mb-4 bg-white h-100">
                            <div className="card-header bg-white py-3">
                                <h6 className="mb-0 fw-bold">My Medical Record Quick Links</h6>
                            </div>
                            <div className="card-body p-4 pt-0">
                                <div className="list-group list-group-flush">
                                    <Link href="#" className="list-group-item list-group-item-action py-3 px-0 border-0 d-flex align-items-center">
                                        <div className="bg-info-subtle text-info p-2 rounded me-3 shadow-sm">
                                            <i className="fas fa-prescription-bottle-alt"></i>
                                        </div>
                                        <div>
                                            <div className="fw-bold small">My Prescriptions</div>
                                            <div className="text-muted extra-small">View active medications</div>
                                        </div>
                                    </Link>
                                    <Link href="#" className="list-group-item list-group-item-action py-3 px-0 border-0 d-flex align-items-center">
                                        <div className="bg-success-subtle text-success p-2 rounded me-3 shadow-sm">
                                            <i className="fas fa-flask"></i>
                                        </div>
                                        <div>
                                            <div className="fw-bold small">Lab Reports</div>
                                            <div className="text-muted extra-small">Check your test results</div>
                                        </div>
                                    </Link>
                                    <Link href={route('invoices.index')} className="list-group-item list-group-item-action py-3 px-0 border-0 d-flex align-items-center">
                                        <div className="bg-warning-subtle text-warning p-2 rounded me-3 shadow-sm">
                                            <i className="fas fa-file-invoice-dollar"></i>
                                        </div>
                                        <div>
                                            <div className="fw-bold small">Billing & Invoices</div>
                                            <div className="text-muted extra-small">View payment history</div>
                                        </div>
                                    </Link>
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
