import { Head, Link } from '@inertiajs/react';
import { formatDateLong } from '@/Utils/dateUtils';

export default function GuestConfirmation({ appointment }) {
    const dateStr = formatDateLong(appointment.appointment_date);

    return (
        <div className="landing-wrapper min-vh-100 d-flex flex-column">
            <Head title="Appointment Request Received" />

            <nav className="navbar landing-navbar">
                <div className="container">
                    <Link className="navbar-brand" href="/">
                        <img src="/assets/img/logo/Logo2-transparent.png" alt="Nyalife" height="42" />
                    </Link>
                </div>
            </nav>

            <main className="flex-grow-1 d-flex align-items-center py-5">
                <div className="container">
                    <div className="row justify-content-center">
                        <div className="col-lg-7">
                            <div className="card border-0 shadow-lg rounded-4 overflow-hidden">
                                <div className="card-body p-5 text-center position-relative">
                                    <div className="bg-success-subtle text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-4 shadow-sm border border-success-subtle"
                                        style={{ width: 100, height: 100 }}>
                                        <i className="fas fa-check-circle fa-3x"></i>
                                    </div>

                                    <h1 className="fw-bold text-gray-900 mb-3 h2">Request Received</h1>
                                    <p className="text-muted mb-4 leading-relaxed">
                                        Thank you, <strong>{appointment.patient_name}</strong>. Our clinical coordinators will contact you at{' '}
                                        <strong>{appointment.patient_email}</strong> to confirm your appointment.
                                    </p>

                                    <div className="bg-light rounded-4 p-4 text-start mb-4">
                                        <div className="row g-3">
                                            <div className="col-sm-6">
                                                <div className="extra-small text-muted fw-bold text-uppercase tracking-widest mb-1">Reference</div>
                                                <div className="fw-bold">#{appointment.appointment_id}</div>
                                            </div>
                                            <div className="col-sm-6">
                                                <div className="extra-small text-muted fw-bold text-uppercase tracking-widest mb-1">Status</div>
                                                <span className="badge bg-warning-subtle text-warning-emphasis text-capitalize">{appointment.status}</span>
                                            </div>
                                            <div className="col-sm-6">
                                                <div className="extra-small text-muted fw-bold text-uppercase tracking-widest mb-1">Appointment Type</div>
                                                <span className="fw-medium">
                                                    {appointment.appointment_type === 'telehealth' ? (
                                                        <><i className="fas fa-video text-info me-1"></i> Telehealth</>
                                                    ) : (
                                                        <><i className="fas fa-building me-1"></i> In-Person</>
                                                    )}
                                                </span>
                                            </div>
                                            <div className="col-sm-6">
                                                <div className="extra-small text-muted fw-bold text-uppercase tracking-widest mb-1">Preferred Date</div>
                                                <div className="fw-medium">{dateStr}</div>
                                            </div>
                                            <div className="col-sm-6">
                                                <div className="extra-small text-muted fw-bold text-uppercase tracking-widest mb-1">Preferred Time</div>
                                                <div className="fw-medium">{appointment.appointment_time || '—'}</div>
                                            </div>
                                            {appointment.reason && (
                                                <div className="col-12">
                                                    <div className="extra-small text-muted fw-bold text-uppercase tracking-widest mb-1">Reason</div>
                                                    <div className="fw-medium">{appointment.reason}</div>
                                                </div>
                                            )}
                                        </div>
                                    </div>

                                    <div className="d-grid gap-3 d-sm-flex justify-content-sm-center">
                                        <Link
                                            href={`/register?name=${encodeURIComponent(appointment.patient_name)}&email=${encodeURIComponent(appointment.patient_email || '')}&phone=${encodeURIComponent(appointment.patient_phone || '')}`}
                                            className="btn btn-primary btn-lg rounded-pill fw-semibold px-4"
                                        >
                                            <i className="fas fa-user-plus me-2"></i>
                                            Complete Profile Registration
                                        </Link>
                                        <Link href="/" className="btn btn-light btn-lg rounded-pill text-muted fw-medium px-4">
                                            Return to Home
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    );
}
