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
                                    <h2 className="fw-bold text-white mb-4 ">Pick up where you left off</h2>
                                    <div className="d-flex flex-wrap gap-3 mt-3">
                                        {(stats.pending_lab_consultations?.length > 0 || stats.released_labs?.length > 0 || stats.in_progress_consultations?.length > 0) ? (
                                            <>
                                                {stats.in_progress_consultations?.map((c, i) => (
                                                    <Link key={`ip-${i}`} href={route('consultations.edit', c.consultation_id)} className="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3 shadow-sm">
                                                        <i className="fas fa-spinner fa-spin me-2 text-warning"></i>
                                                        Resume: {c.patient.user.first_name}
                                                    </Link>
                                                ))}
                                                {stats.released_labs?.map((l, i) => (
                                                    <Link key={`rl-${i}`} href={route('consultations.show', l.consultation_id)} className="btn btn-sm btn-light text-success fw-bold rounded-pill px-3 shadow-sm">
                                                        <i className="fas fa-flask me-2"></i>
                                                        Review Labs: {l.patient.user.first_name}
                                                    </Link>
                                                ))}
                                                {stats.pending_lab_consultations?.map((l, i) => (
                                                    <Link key={`pl-${i}`} href={route('consultations.show', l.consultation_id)} className="btn btn-sm btn-light text-muted fw-bold rounded-pill px-3 shadow-sm opacity-75">
                                                        <i className="fas fa-hourglass-half me-2"></i>
                                                        Awaiting Labs: {l.patient.user.first_name}
                                                    </Link>
                                                ))}
                                            </>
                                        ) : (
                                            <p className="mb-0 text-white">No pending tasks or lab results to review at the moment.</p>
                                        )}
                                    </div>
                                </div>
                                <i className="fas fa-user-md position-absolute" style={{ fontSize: '10rem', right: '2rem', opacity: '0.5', transform: 'rotate(10deg)', color: '#fff' }}></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="row g-4 mb-4 text-dark">
                    <div className="col-md-4">
                        <div className="card shadow-sm border-0 h-100 p-4">
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
                        <div className="card shadow-sm border-0 h-100 p-4">
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
                        <div className="card shadow-sm border-0 h-100 p-4">
                            <div className="d-flex justify-content-between align-items-start">
                                <div>
                                    <div className="text-muted small fw-bold text-uppercase mb-1">Completed (This Week)</div>
                                    <h2 className="fw-bold mb-0">{stats.completed_this_week || 0}</h2>
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
                            <div className="card-body p-4 pt-0 mt-3">
                                <div className="d-grid gap-3">
                                    <Link href={route('patients.index')} className="btn btn-light border text-start p-3 d-flex align-items-center rounded-3">
                                        <div className="bg-primary-subtle text-primary p-2 rounded me-3">
                                            <i className="fas fa-users"></i>
                                        </div>
                                        <div>
                                            <div className="fw-bold small">Find Patient</div>
                                            <div className="text-muted extra-small">Registry and records</div>
                                        </div>
                                    </Link>
                                    <Link href={route('lab.results')} className="btn btn-light border text-start p-3 d-flex align-items-center rounded-3">
                                        <div className="bg-success-subtle text-success p-2 rounded me-3">
                                            <i className="fas fa-flask"></i>
                                        </div>
                                        <div>
                                            <div className="fw-bold small">Lab Results</div>
                                            <div className="text-muted extra-small">Check pending reports</div>
                                        </div>
                                    </Link>
                                    <Link href={route('prescriptions.index')} className="btn btn-light border text-start p-3 d-flex align-items-center rounded-3">
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

                .card-body h2 {
                    font-size: 1.7rem;
                    letter-spacing: tight;
                    font-weight: 600;
                }

                .card-body p {
                    font-size: 1rem;
                    letter-spacing: tight;
                    font-weight: 600;
                }


            `}</style>
        </AuthenticatedLayout>
    );
}
