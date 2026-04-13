import DashboardTable from '@/Components/DashboardTable';
import { useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';

export default function Doctor({ auth, stats }) {
    const columns = useMemo(() => [
        {
            header: 'Time',
            accessorKey: 'appointment_time',
            cell: ({ row }) => <span className="fw-bold">{row.original.appointment_time}</span>
        },
        {
            header: 'Patient',
            accessorKey: 'patient',
            cell: ({ row }) => (
                <div>
                    <div className="fw-semibold">{row.original.patient.user.first_name} {row.original.patient.user.last_name}</div>
                    <small className="text-muted">ID: {row.original.patient_id}</small>
                </div>
            )
        },
        {
            header: 'Type',
            accessorKey: 'appointment_type',
            cell: ({ row }) => <span className="badge bg-light text-dark border">{row.original.appointment_type}</span>
        },
        {
            header: 'Action',
            id: 'actions',
            cell: ({ row }) => (
                <div className="text-end pe-2">
                    <Link href={route('consultations.create', { appointment_id: row.original.appointment_id })} className="btn btn-sm btn-primary rounded-pill px-3 shadow-sm transition-all hover-scale">
                        Start Consultation
                    </Link>
                </div>
            )
        }
    ], []);

    return (
        <AuthenticatedLayout
            user={auth.user}
        >
            <Head title="Doctor Dashboard" />

            <PageHeader 
                title={`Welcome, Dr. ${auth.user.first_name}!`}
                breadcrumbs={[{ label: 'Dashboard', active: true }]}
                showBack={false}
            />

            <div className="container-fluid dashboard-page px-0 h-auto">
                <div className="row mb-4 h-auto">
                    <div className="col-12">
                        <div className="card shadow-sm border-0 bg-primary text-white overflow-hidden" style={{ borderRadius: '15px' }}>
                            <div className="card-body p-4 p-md-5 d-flex align-items-center position-relative">
                                <div style={{ zIndex: '1' }}>
                                    <h2 className="fw-bold mb-2">Welcome back, Dr. {auth.user.last_name}!</h2>
                                    <p className="mb-0 opacity-75">You have {stats.today_appointments?.length || 0} appointments scheduled for today.</p>
                                </div>
                                <i className="fas fa-user-md position-absolute" style={{ fontSize: '10rem', right: '2rem', opacity: '0.1', transform: 'rotate(10deg)' }}></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="row g-4 mb-4">
                    <div className="col-md-4">
                        <div className="card shadow-sm border-0 h-100 p-4 border-start border-4 border-info">
                            <div className="d-flex justify-content-between align-items-start">
                                <div>
                                    <div className="text-muted small fw-bold text-uppercase mb-1">Today's Visits</div>
                                    <h2 className="fw-bold mb-0">{stats.today_appointments?.length || 0}</h2>
                                </div>
                                <div className="bg-info-subtle p-3 rounded text-info">
                                    <i className="fas fa-calendar-check fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-4">
                        <div className="card shadow-sm border-0 h-100 p-4 border-start border-4 border-warning">
                            <div className="d-flex justify-content-between align-items-start">
                                <div>
                                    <div className="text-muted small fw-bold text-uppercase mb-1">Pending Reviews</div>
                                    <h2 className="fw-bold mb-0">{stats.pending_appointments || 0}</h2>
                                </div>
                                <div className="bg-warning-subtle p-3 rounded text-warning">
                                    <i className="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-4">
                        <div className="card shadow-sm border-0 h-100 p-4 border-start border-4 border-success">
                            <div className="d-flex justify-content-between align-items-start">
                                <div>
                                    <div className="text-muted small fw-bold text-uppercase mb-1">Completed (This Week)</div>
                                    <h2 className="fw-bold mb-0">12</h2>
                                </div>
                                <div className="bg-success-subtle p-3 rounded text-success">
                                    <i className="fas fa-check-double fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="row">
                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 mb-4 h-100">
                            <div className="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h6 className="mb-0 fw-bold">Today's Appointment Schedule</h6>
                                <Link href={route('appointments.index')} className="small text-decoration-none">View All</Link>
                            </div>
                            <div className="card-body p-0">
                                <DashboardTable 
                                    columns={columns}
                                    data={stats.today_appointments || []}
                                    emptyMessage="No appointments for today."
                                />
                            </div>
                        </div>
                    </div>
                    <div className="col-lg-4">
                        <div className="card shadow-sm border-0 mb-4 bg-white overflow-hidden">
                            <div className="card-header bg-white py-3 border-0">
                                <h6 className="mb-0 fw-bold">Clinical Quick Actions</h6>
                            </div>
                            <div className="card-body p-4 pt-0">
                                <div className="d-grid gap-3">
                                    <Link href={route('patients.index')} className="btn btn-light border text-start p-3 d-flex align-items-center">
                                        <div className="bg-primary-subtle text-primary p-2 rounded me-3">
                                            <i className="fas fa-users"></i>
                                        </div>
                                        <div>
                                            <div className="fw-bold small">Find Patient</div>
                                            <div className="text-muted extra-small">Registry and records</div>
                                        </div>
                                    </Link>
                                    <Link href={route('lab.results')} className="btn btn-light border text-start p-3 d-flex align-items-center">
                                        <div className="bg-success-subtle text-success p-2 rounded me-3">
                                            <i className="fas fa-flask"></i>
                                        </div>
                                        <div>
                                            <div className="fw-bold small">Lab Results</div>
                                            <div className="text-muted extra-small">Check pending reports</div>
                                        </div>
                                    </Link>
                                    <Link href={route('prescriptions.index')} className="btn btn-light border text-start p-3 d-flex align-items-center">
                                        <div className="bg-warning-subtle text-warning p-2 rounded me-3">
                                            <i className="fas fa-prescription"></i>
                                        </div>
                                        <div>
                                            <div className="fw-bold small">Prescriptions</div>
                                            <div className="text-muted extra-small">Previous medications</div>
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
