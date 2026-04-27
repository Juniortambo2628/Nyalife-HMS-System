import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import PageHeader from '@/Components/PageHeader';
import UserAvatar from '@/Components/UserAvatar';
import StatusBadge from '@/Components/StatusBadge';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Show({ appointment, auth }) {
    const isReceptionist = auth.user.role === 'receptionist';
    const [activeTab, setActiveTab] = useState('summary');
    
    const updateStatus = (status) => {
        if (confirm(`Are you sure you want to change the status to ${status}?`)) {
            router.patch(route('appointments.update', appointment.appointment_id), { status }, {
                preserveState: true,
            });
        }
    };

    return (
        <AuthenticatedLayout header="Appointment Detail">
            <Head title={`Appointment - ${appointment.patient?.user?.first_name || 'Patient'}`} />

            <PageHeader 
                title={`Visit Management`}
                breadcrumbs={[
                    { label: 'Appointments', url: route('appointments.index') },
                    { label: `REF #${appointment.appointment_id}`, active: true }
                ]}
            />

            <div className="px-0 pb-5">
                <div className="row g-4">
                    <div className="col-lg-4">
                        {/* Quick Info Card */}
                        <div className="card shadow-sm border-0 mb-4 rounded-3xl overflow-hidden bg-white shadow-hover">
                            <div className="card-header bg-gradient-primary-to-secondary p-5 border-0 text-center">
                                <UserAvatar user={appointment.patient?.user} size="xl" className="mb-3 border border-4 border-white shadow-lg" />
                                <h4 className="mb-1 text-white fw-extrabold tracking-tighter">{appointment.patient?.user?.first_name} {appointment.patient?.user?.last_name}</h4>
                                <div className="extra-small font-bold text-white opacity-50 tracking-widest uppercase mb-3">PAT-ID: {appointment.patient_id}</div>
                                <StatusBadge status={appointment.status} />
                            </div>
                            <div className="card-body p-4">
                                <div className="space-y-4">
                                    <div className="d-flex justify-content-between align-items-center border-bottom border-gray-50 pb-2">
                                        <div className="d-flex align-items-center gap-2">
                                            <i className="fas fa-calendar-alt text-primary opacity-50"></i>
                                            <span className="extra-small fw-bold text-muted text-uppercase">Scheduled Date</span>
                                        </div>
                                        <span className="fw-extrabold text-gray-900 small">{appointment.appointment_date}</span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center border-bottom border-gray-50 pb-2">
                                        <div className="d-flex align-items-center gap-2">
                                            <i className="fas fa-clock text-primary opacity-50"></i>
                                            <span className="extra-small fw-bold text-muted text-uppercase">Allocated Time</span>
                                        </div>
                                        <span className="fw-extrabold text-gray-900 small">{appointment.appointment_time}</span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center">
                                        <div className="d-flex align-items-center gap-2">
                                            <i className="fas fa-user-md text-primary opacity-50"></i>
                                            <span className="extra-small fw-bold text-muted text-uppercase">Assigned Doctor</span>
                                        </div>
                                        <span className="fw-extrabold text-gray-900 small">Dr. {appointment.doctor?.user?.last_name}</span>
                                    </div>
                                </div>
                                
                                {['admin', 'doctor', 'receptionist'].includes(auth.user.role) && (
                                    <div className="mt-5 space-y-2">
                                        {appointment.status === 'scheduled' && (
                                            (() => {
                                                const aptDate = new Date(`${appointment.appointment_date}T${appointment.appointment_time}`);
                                                const now = new Date();
                                                const isPast = aptDate < now;
                                                
                                                if (isPast) {
                                                    return (
                                                        <div className="alert alert-warning border-0 rounded-2xl p-3 mb-0 d-flex align-items-center gap-3">
                                                            <i className="fas fa-exclamation-circle fs-4"></i>
                                                            <div className="extra-small fw-bold text-uppercase tracking-wider">Historical Record Only</div>
                                                        </div>
                                                    );
                                                }
                                                
                                                return (
                                                    <button onClick={() => updateStatus('confirmed')} className="btn btn-primary w-100 rounded-pill py-2.5 fw-extrabold extra-small tracking-widest shadow-sm">
                                                        CONFIRM ATTENDANCE
                                                    </button>
                                                );
                                            })()
                                        )}
                                        {['scheduled', 'confirmed'].includes(appointment.status) && (
                                            <div className="row g-2">
                                                <div className="col-6">
                                                    <button onClick={() => updateStatus('completed')} className="btn btn-success w-100 rounded-pill py-2.5 fw-extrabold extra-small tracking-widest shadow-sm">
                                                        COMPLETE
                                                    </button>
                                                </div>
                                                <div className="col-6">
                                                    <button onClick={() => updateStatus('cancelled')} className="btn btn-danger w-100 rounded-pill py-2.5 fw-extrabold extra-small tracking-widest shadow-sm">
                                                        CANCEL
                                                    </button>
                                                </div>
                                            </div>
                                        )}
                                        {appointment.status === 'scheduled' && new Date(`${appointment.appointment_date}T${appointment.appointment_time}`) < new Date() && (
                                             <button onClick={() => updateStatus('no_show')} className="btn btn-dark w-100 rounded-pill py-2.5 fw-extrabold extra-small tracking-widest shadow-sm">
                                                MARK AS NO SHOW
                                             </button>
                                        )}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 rounded-3xl bg-white shadow-hover overflow-hidden">
                            <div className="card-header bg-white border-bottom-0 pt-4 pb-0 px-5">
                                <div className="d-flex gap-4 border-bottom border-gray-100">
                                    <button
                                        onClick={() => setActiveTab('summary')}
                                        className={`pb-3 extra-small fw-extrabold tracking-widest transition-all border-bottom-2 ${
                                            activeTab === 'summary' ? 'text-primary border-primary' : 'text-muted border-transparent opacity-50'
                                        }`}
                                    >
                                        GENERAL
                                    </button>
                                    {!isReceptionist && (
                                        <>
                                            <button
                                                onClick={() => setActiveTab('history')}
                                                className={`pb-3 extra-small fw-extrabold tracking-widest transition-all border-bottom-2 ${
                                                    activeTab === 'history' ? 'text-primary border-primary' : 'text-muted border-transparent opacity-50'
                                                }`}
                                            >
                                                CLINICAL
                                            </button>
                                            <button
                                                onClick={() => setActiveTab('prescriptions')}
                                                className={`pb-3 extra-small fw-extrabold tracking-widest transition-all border-bottom-2 ${
                                                    activeTab === 'prescriptions' ? 'text-primary border-primary' : 'text-muted border-transparent opacity-50'
                                                }`}
                                            >
                                                PHARMACY
                                            </button>
                                        </>
                                    )}
                                </div>
                            </div>
                            <div className="card-body p-5 pt-0">
                                <div className="tab-content py-5">
                                    {activeTab === 'summary' && (
                                        <div className="space-y-6">
                                            <div>
                                                <h6 className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-3">Primary Concern</h6>
                                                <div className="p-4 bg-light rounded-2xl border-l-4 border-primary shadow-inner fw-bold text-gray-800 leading-relaxed">
                                                    {appointment.reason || 'Routine medical check-up / No specific reason provided.'}
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <h6 className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-3">Triage Notes</h6>
                                                <div className="p-4 bg-gray-50 rounded-2xl border border-gray-100 text-muted small leading-relaxed font-medium">
                                                    {appointment.notes || 'No triage or internal notes recorded for this visit.'}
                                                </div>
                                            </div>
                                        </div>
                                    )}

                                    {activeTab === 'history' && (
                                        <div className="space-y-4">
                                            <h6 className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-4">Historical Consultations</h6>
                                            {appointment.consultations?.length > 0 ? (
                                                <div className="space-y-3">
                                                    {appointment.consultations.map(c => (
                                                        <div key={c.consultation_id} className="p-4 rounded-2xl bg-white border border-gray-100 shadow-sm hover-lift transition-all">
                                                            <div className="d-flex justify-content-between align-items-center mb-2">
                                                                <h6 className="fw-extrabold text-gray-900 mb-0">{c.diagnosis}</h6>
                                                                <span className="extra-small text-muted font-bold opacity-50">{c.created_at}</span>
                                                            </div>
                                                            <p className="small text-muted mb-0">{c.treatment_plan}</p>
                                                        </div>
                                                    ))}
                                                </div>
                                            ) : (
                                                <div className="p-5 text-center bg-light rounded-3xl border border-gray-50">
                                                    <i className="fas fa-folder-open text-gray-300 fa-3x mb-3 opacity-20"></i>
                                                    <p className="text-muted extra-small fw-bold text-uppercase tracking-widest mb-0">No active clinical records</p>
                                                </div>
                                            )}
                                        </div>
                                    )}

                                    {activeTab === 'prescriptions' && (
                                        <div className="space-y-4">
                                            <div className="d-flex justify-content-between align-items-center mb-4">
                                                <h6 className="mb-0 extra-small fw-extrabold text-muted text-uppercase tracking-widest">Active Prescriptions</h6>
                                                {auth.user.role === 'doctor' && (
                                                    <Link href="#" className="btn btn-primary btn-sm rounded-pill px-3 fw-bold extra-small tracking-widest">NEW RX</Link>
                                                )}
                                            </div>
                                            <div className="table-responsive rounded-2xl border border-gray-100 overflow-hidden shadow-inner">
                                                <table className="table table-hover align-middle mb-0">
                                                    <thead className="bg-gray-50">
                                                        <tr>
                                                            <th className="px-4 py-3 extra-small fw-extrabold text-muted border-0">MEDICINE</th>
                                                            <th className="px-4 py-3 extra-small fw-extrabold text-muted border-0">DOSAGE</th>
                                                            <th className="px-4 py-3 extra-small fw-extrabold text-muted border-0">DURATION</th>
                                                            <th className="px-4 py-3 extra-small fw-extrabold text-muted border-0 text-center">STATUS</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody className="border-0">
                                                        {appointment.prescriptions?.length > 0 ? (
                                                            appointment.prescriptions.flatMap(p => (
                                                                p.items?.map(item => (
                                                                    <tr key={item.item_id} className="border-bottom border-gray-50">
                                                                        <td className="px-4 py-3 fw-bold text-gray-800">{item.medicine_name}</td>
                                                                        <td className="px-4 py-3 small text-muted font-medium">{item.dosage}</td>
                                                                        <td className="px-4 py-3 small text-muted font-medium">{item.duration} {item.duration_unit}</td>
                                                                        <td className="px-4 py-3 text-center">
                                                                            <span className={`badge rounded-pill px-3 py-1.5 fw-extrabold extra-small text-uppercase ${p.status === 'dispensed' ? 'bg-success text-white' : 'bg-warning text-dark'}`}>
                                                                                {p.status}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                ))
                                                            ))
                                                        ) : (
                                                            <tr>
                                                                <td colSpan="4" className="text-center py-5 bg-gray-50">
                                                                    <p className="text-muted extra-small fw-bold text-uppercase tracking-widest mb-0 opacity-50">No pharmacy entries found</p>
                                                                </td>
                                                            </tr>
                                                        )}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <UnifiedToolbar 
                actions={
                    <div className="d-flex align-items-center gap-2">
                        {auth?.user?.role === 'nurse' ? (
                            <Link href={route('consultations.create', { patient_id: appointment.patient_id, appointment_id: appointment.appointment_id })} className="btn btn-primary rounded-pill px-4 py-2 fw-extrabold small shadow-sm">
                                <i className="fas fa-heartbeat me-1"></i> Record Vitals
                            </Link>
                        ) : auth?.user?.role === 'doctor' ? (
                            <Link href={route('consultations.create', { patient_id: appointment.patient_id, appointment_id: appointment.appointment_id })} className="btn btn-primary rounded-pill px-4 py-2 fw-extrabold small shadow-sm">
                                <i className="fas fa-stethoscope me-1"></i> Start Consultation
                            </Link>
                        ) : null}
                        <Link href={route('appointments.index')} className="btn btn-light rounded-pill px-4 py-2 fw-extrabold small border shadow-sm">
                            <i className="fas fa-list me-1"></i> Back to Registry
                        </Link>
                    </div>
                }
            />
        </AuthenticatedLayout>
    );
}
