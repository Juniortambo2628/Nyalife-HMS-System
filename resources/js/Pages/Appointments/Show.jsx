import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import PageHeader from '@/Components/PageHeader';

export default function Show({ appointment, auth }) {
    const isReceptionist = auth.user.role === 'receptionist';
    const [activeTab, setActiveTab] = useState('summary');
    const { patch, processing } = useForm({
        status: appointment.status,
    });

    const updateStatus = (status) => {
        if (confirm(`Are you sure you want to change the status to ${status}?`)) {
            patch(route('appointments.update', appointment.appointment_id), {
                data: { status },
                preserveState: true,
            });
        }
    };

    const getStatusBadgeClass = (status) => {
        switch (status) {
            case 'scheduled': return 'badge bg-info';
            case 'confirmed': return 'badge bg-primary';
            case 'completed': return 'badge bg-success';
            case 'cancelled': return 'badge bg-danger';
            case 'no_show': return 'badge bg-warning text-dark';
            default: return 'badge bg-secondary';
        }
    };

    return (
        <AuthenticatedLayout
            header="Appointment Details"
        >
            <Head title={`Appointment - ${appointment.patient?.user?.first_name || 'Patient'}`} />

            <PageHeader 
                title={`Appointment #${appointment.appointment_id}`}
                breadcrumbs={[
                    { label: 'Appointments', url: route('appointments.index') },
                    { label: 'Details', active: true }
                ]}
                actions={
                    auth?.user?.role === 'nurse' ? (
                        <Link href={route('consultations.create', { patient_id: appointment.patient_id, appointment_id: appointment.appointment_id })} className="btn btn-primary rounded-pill px-4 font-bold shadow-sm">
                            <i className="fas fa-heartbeat me-2"></i>Record Vitals
                        </Link>
                    ) : (
                        <Link href={route('consultations.create', { patient_id: appointment.patient_id, appointment_id: appointment.appointment_id })} className="btn btn-primary rounded-pill px-4 font-bold shadow-sm">
                            <i className="fas fa-stethoscope me-2"></i>Start Consultation
                        </Link>
                    )
                }
            />

            <div className="px-0">

                <div className="row">
                    <div className="col-lg-4">
                        {/* Quick Info Card */}
                        <div className="card shadow-sm border-0 mb-4">
                            <div className="card-body p-4 text-center">
                                <div className="avatar-circle mx-auto mb-3" style={{ width: '80px', height: '80px', fontSize: '2rem', background: 'linear-gradient(45deg, #2196F3, #21CBF3)', color: 'white', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                    {appointment.patient?.user?.first_name?.charAt(0) || 'P'}
                                </div>
                                <h4 className="mb-1">{appointment.patient?.user?.first_name || 'Unknown'} {appointment.patient?.user?.last_name || 'Patient'}</h4>
                                <p className="text-muted small mb-3">Patient ID: {appointment.patient_id}</p>
                                <span className={getStatusBadgeClass(appointment.status)}>
                                    {(appointment.status || 'pending').charAt(0).toUpperCase() + (appointment.status || 'pending').slice(1).replace('_', ' ')}
                                </span>
                                <hr className="my-4" />
                                <div className="text-start">
                                    <div className="mb-2"><i className="fas fa-calendar-alt text-primary me-2"></i><strong>Date:</strong> {appointment.appointment_date}</div>
                                    <div className="mb-2"><i className="fas fa-clock text-primary me-2"></i><strong>Time:</strong> {appointment.appointment_time}</div>
                                    <div className="mb-0"><i className="fas fa-user-md text-primary me-2"></i><strong>Doctor:</strong> Dr. {appointment.doctor?.user?.first_name || 'Staff'} {appointment.doctor?.user?.last_name || ''}</div>
                                </div>
                                
                                {['admin', 'doctor', 'receptionist'].includes(auth.user.role) && (
                                    <div className="mt-4 d-grid gap-2">
                                        {appointment.status === 'scheduled' && (
                                            (() => {
                                                const aptDate = new Date(`${appointment.appointment_date}T${appointment.appointment_time}`);
                                                const now = new Date();
                                                const isPast = aptDate < now;
                                                
                                                if (isPast) {
                                                    return (
                                                        <div className="alert alert-warning mb-0 text-center py-2 text-sm">
                                                            <i className="fas fa-exclamation-triangle me-2"></i>
                                                            Past Appointment
                                                        </div>
                                                    );
                                                }
                                                
                                                return (
                                                    <button onClick={() => updateStatus('confirmed')} className="btn btn-primary btn-sm">Confirm Appointment</button>
                                                );
                                            })()
                                        )}
                                        {['scheduled', 'confirmed'].includes(appointment.status) && (
                                            <>
                                                <button onClick={() => updateStatus('completed')} className="btn btn-success btn-sm">Mark as Completed</button>
                                                <button onClick={() => updateStatus('cancelled')} className="btn btn-danger btn-sm">Cancel Appointment</button>
                                            </>
                                        )}
                                        {appointment.status === 'scheduled' && new Date(`${appointment.appointment_date}T${appointment.appointment_time}`) < new Date() && (
                                             <button onClick={() => updateStatus('no_show')} className="btn btn-dark btn-sm">Mark as No Show</button>
                                        )}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0">
                            <div className="card-header bg-white border-bottom pt-4 pb-0 px-4">
                                <nav className="flex space-x-8" aria-label="Tabs">
                                    <button
                                        onClick={() => setActiveTab('summary')}
                                        className={`${
                                            activeTab === 'summary'
                                                ? 'border-pink-500 text-pink-600'
                                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                        } whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-colors`}
                                    >
                                        General Info
                                    </button>
                                    {!isReceptionist && (
                                        <>
                                            <button
                                                onClick={() => setActiveTab('history')}
                                                className={`${
                                                    activeTab === 'history'
                                                        ? 'border-pink-500 text-pink-600'
                                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                                } whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-colors`}
                                            >
                                                Clinical History
                                            </button>
                                            <button
                                                onClick={() => setActiveTab('prescriptions')}
                                                className={`${
                                                    activeTab === 'prescriptions'
                                                        ? 'border-pink-500 text-pink-600'
                                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                                } whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-colors`}
                                            >
                                                Prescriptions
                                            </button>
                                        </>
                                    )}
                                </nav>
                            </div>
                            <div className="card-body p-4 pt-0">
                                <div className="tab-content py-4">
                                    {activeTab === 'summary' && (
                                        <div>
                                            <h5 className="mb-3 border-bottom pb-2">Reason for Visit</h5>
                                            <p className="bg-light p-3 rounded">{appointment.reason || 'No reason provided.'}</p>
                                            
                                            <h5 className="mb-3 border-bottom pb-2 mt-4">Internal Notes</h5>
                                            <p className="bg-light p-3 rounded">{appointment.notes || 'No notes available.'}</p>
                                        </div>
                                    )}

                                    {activeTab === 'history' && (
                                        <div>
                                            <h5 className="mb-3">Consultations</h5>
                                            {appointment.consultations?.length > 0 ? (
                                                appointment.consultations.map(c => (
                                                    <div key={c.consultation_id} className="card border mb-3">
                                                        <div className="card-body">
                                                            <div className="d-flex justify-content-between">
                                                                <h6>{c.diagnosis}</h6>
                                                                <small className="text-muted">{c.created_at}</small>
                                                            </div>
                                                            <p className="small mb-0 mt-2">{c.treatment_plan}</p>
                                                        </div>
                                                    </div>
                                                ))
                                            ) : (
                                                <div className="alert alert-info">No consultation records for this appointment.</div>
                                            )}
                                        </div>
                                    )}

                                    {activeTab === 'prescriptions' && (
                                        <div>
                                            <div className="d-flex justify-content-between align-items-center mb-3">
                                                <h5 className="mb-0">Prescriptions</h5>
                                                {auth.user.role === 'doctor' && (
                                                    <Link href="#" className="btn btn-sm btn-primary">Add New</Link>
                                                )}
                                            </div>
                                            <div className="table-responsive">
                                                <table className="table">
                                                    <thead>
                                                        <tr>
                                                            <th>Medicine</th>
                                                            <th>Dosage</th>
                                                            <th>Duration</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {appointment.prescriptions?.length > 0 ? (
                                                            appointment.prescriptions.map(p => (
                                                                p.items?.map(item => (
                                                                    <tr key={item.item_id}>
                                                                        <td>{item.medicine_name}</td>
                                                                        <td>{item.dosage}</td>
                                                                        <td>{item.duration} {item.duration_unit}</td>
                                                                        <td><span className="badge bg-light text-dark border">{p.status}</span></td>
                                                                    </tr>
                                                                ))
                                                            ))
                                                        ) : (
                                                            <tr><td colSpan="4" className="text-center py-4 bg-light">No prescriptions issued.</td></tr>
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
        </AuthenticatedLayout>
    );
}
