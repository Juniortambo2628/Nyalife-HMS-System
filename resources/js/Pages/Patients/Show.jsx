import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import PageHeader from '@/Components/PageHeader';

export default function Show({ patient, auth }) {
    const isReceptionist = auth.user.role_name === 'receptionist';
    const [activeTab, setActiveTab] = useState(patient.consultations && !isReceptionist ? 'consultations' : 'appointments');

    const calculateAge = (dob) => {
        const birthDate = new Date(dob);
        const difference = Date.now() - birthDate.getTime();
        const ageDate = new Date(difference);
        return Math.abs(ageDate.getUTCFullYear() - 1970);
    };

    return (
        <AuthenticatedLayout
            header="Patient Profile"
        >
            <Head title={`Patient - ${patient.user?.first_name || 'Profile'} ${patient.user?.last_name || ''}`} />

            <PageHeader 
                title={`${patient.user?.first_name || 'Patient'} ${patient.user?.last_name || 'Profile'}`}
                breadcrumbs={[
                    { label: 'Patients', url: route('patients.index') },
                    { label: 'Profile', active: true }
                ]}
                actions={
                    <div className="d-flex gap-2">
                        {patient.consultations && (
                            <Link href={route('consultations.create', { patient_id: patient.patient_id })} className="btn btn-primary rounded-pill px-4 font-bold shadow-sm">
                                <i className="fas fa-stethoscope me-2"></i>New Consultation
                            </Link>
                        )}
                    </div>
                }
            />

            <div className="px-0">

                <div className="row">
                    {/* Left Column: Personal Info */}
                    <div className="col-lg-4">
                        <div className="card shadow-sm border-0 mb-4">
                            <div className="card-body p-4 text-center">
                                <div className="avatar-circle mx-auto mb-3" style={{ width: '100px', height: '100px', fontSize: '2.5rem', background: 'linear-gradient(45deg, #4CAF50, #81C784)', color: 'white', border: '5px solid #fff', boxShadow: '0 5px 15px rgba(0,0,0,0.1)' }}>
                                    {patient.user?.first_name?.charAt(0) || 'P'}{patient.user?.last_name?.charAt(0) || ''}
                                </div>
                                <h4 className="mb-1">{patient.user?.first_name} {patient.user?.last_name}</h4>
                                <p className="text-muted small mb-3">ID: {patient.patient_id} | No: {patient.patient_number}</p>
                                
                                <div className="d-flex justify-content-center gap-2 mb-4">
                                    <span className="badge bg-light text-dark border px-3 py-2">
                                        {(patient.gender || 'unknown').charAt(0).toUpperCase() + (patient.gender || 'unknown').slice(1).toLowerCase()}
                                    </span>
                                    <span className="badge bg-light text-dark border px-3 py-2">
                                        {calculateAge(patient.date_of_birth || patient.user?.date_of_birth)} Years
                                    </span>
                                    {patient.blood_group && <span className="badge bg-danger px-3 py-2">{patient.blood_group}</span>}
                                </div>

                                <div className="text-start space-y-3">
                                    <div className="d-flex align-items-center mb-2">
                                        <div className="bg-light p-2 rounded text-primary me-3 flex-shrink-0" style={{ width: '35px', textAlign: 'center' }}>
                                            <i className="fas fa-phone-alt"></i>
                                        </div>
                                        <div>
                                            <div className="small text-muted">Phone Number</div>
                                            <div className="fw-semibold">{patient.user.phone || 'N/A'}</div>
                                        </div>
                                    </div>
                                    <div className="d-flex align-items-center mb-2">
                                        <div className="bg-light p-2 rounded text-primary me-3 flex-shrink-0" style={{ width: '35px', textAlign: 'center' }}>
                                            <i className="fas fa-envelope"></i>
                                        </div>
                                        <div>
                                            <div className="small text-muted">Email Address</div>
                                            <div className="fw-semibold text-truncate" style={{ maxWidth: '200px' }}>{patient.user.email}</div>
                                        </div>
                                    </div>
                                    <div className="d-flex align-items-center mb-2">
                                        <div className="bg-light p-2 rounded text-primary me-3 flex-shrink-0" style={{ width: '35px', textAlign: 'center' }}>
                                            <i className="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div>
                                            <div className="small text-muted">Residential Address</div>
                                            <div className="fw-semibold">{patient.user.address || 'N/A'}</div>
                                        </div>
                                    </div>
                                    <div className="d-flex align-items-center">
                                        <div className="bg-light p-2 rounded text-danger me-3 flex-shrink-0" style={{ width: '35px', textAlign: 'center' }}>
                                            <i className="fas fa-heartbeat"></i>
                                        </div>
                                        <div>
                                            <div className="small text-muted">Next of Kin (NOK)</div>
                                            <div className="fw-semibold">
                                                {patient.emergency_name ? `${patient.emergency_name}` : ''}
                                                {patient.emergency_name && patient.emergency_contact ? ' - ' : ''}
                                                {patient.emergency_contact || (patient.emergency_name ? '' : 'N/A')}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr className="my-4" />
                                <div className="d-grid gap-2">
                                    <Link href={route('patients.edit', patient.patient_id)} className="btn btn-outline-primary btn-sm">
                                        <i className="fas fa-edit me-2"></i>Edit Profile
                                    </Link>
                                    <Link href={route('appointments.create', { patient_id: patient.patient_id })} className="btn btn-primary btn-sm">
                                        <i className="fas fa-calendar-plus me-2"></i>Schedule Visit
                                    </Link>
                                </div>
                            </div>
                        </div>

                        {/* Recent Vitals Card */}
                        <div className="card shadow-sm border-0">
                            <div className="card-header bg-white py-3">
                                <h6 className="mb-0 fw-bold">Recent Vitals</h6>
                            </div>
                            <div className="card-body p-0">
                                {patient.vitals.length > 0 ? (
                                    <ul className="list-group list-group-flush">
                                        {patient.vitals.slice(0, 3).map(v => (
                                            <li key={v.vital_id} className="list-group-item py-3">
                                                <div className="d-flex justify-content-between mb-2">
                                                    <small className="text-muted fw-bold">{v.measured_at}</small>
                                                    <span className="badge bg-primary-subtle text-primary border-primary-subtle border">Latest</span>
                                                </div>
                                                <div className="row g-2">
                                                    <div className="col-6">
                                                        <div className="small text-muted">BP: <span className="text-dark fw-bold">{v.blood_pressure}</span></div>
                                                        <div className="small text-muted">Temp: <span className="text-dark fw-bold">{v.temperature}°C</span></div>
                                                    </div>
                                                    <div className="col-6">
                                                        <div className="small text-muted">Pulse: <span className="text-dark fw-bold">{v.heart_rate}</span></div>
                                                        <div className="small text-muted">SpO2: <span className="text-dark fw-bold">{v.oxygen_saturation}%</span></div>
                                                    </div>
                                                </div>
                                            </li>
                                        ))}
                                    </ul>
                                ) : (
                                    <div className="p-4 text-center text-muted small">No vitals recorded yet.</div>
                                )}
                            </div>
                            <div className="card-footer bg-white text-center">
                                <Link href="#" className="small text-decoration-none">View All Vitals</Link>
                            </div>
                        </div>
                    </div>

                    {/* Right Column: Tabs and Records */}
                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 mb-4">
                            <div className="card-header bg-white border-bottom-0 pt-3">
                                <ul className="nav nav-tabs card-header-tabs border-bottom-0 gap-3">
                                    {patient.consultations && !isReceptionist && (
                                    <li className="nav-item">
                                        <button 
                                            className={`nav-link border-0 bg-transparent rounded-0 px-1 ${activeTab === 'consultations' ? 'fw-bold text-primary border-bottom border-primary border-3' : 'text-muted'}`} 
                                            onClick={() => setActiveTab('consultations')}
                                        >
                                            Consultations
                                        </button>
                                    </li>
                                    )}
                                    <li className="nav-item">
                                        <button 
                                            className={`nav-link border-0 bg-transparent rounded-0 px-1 ${activeTab === 'appointments' ? 'fw-bold text-primary border-bottom border-primary border-3' : 'text-muted'}`} 
                                            onClick={() => setActiveTab('appointments')}
                                        >
                                            Appointments
                                        </button>
                                    </li>
                                    {patient.prescriptions && !isReceptionist && (
                                    <li className="nav-item">
                                        <button 
                                            className={`nav-link border-0 bg-transparent rounded-0 px-1 ${activeTab === 'prescriptions' ? 'fw-bold text-primary border-bottom border-primary border-3' : 'text-muted'}`} 
                                            onClick={() => setActiveTab('prescriptions')}
                                        >
                                            Prescriptions
                                        </button>
                                    </li>
                                    )}
                                </ul>
                            </div>
                            <div className="card-body p-0">
                                <div className="tab-content">
                                    {activeTab === 'consultations' && patient.consultations && (
                                        <div className="table-responsive">
                                            <table className="table table-hover align-middle mb-0">
                                                <thead className="bg-light">
                                                    <tr>
                                                        <th className="ps-4">Date</th>
                                                        <th>Doctor</th>
                                                        <th>Diagnosis</th>
                                                        <th className="pe-4 text-end">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {patient.consultations.length > 0 ? (
                                                        patient.consultations.map(c => (
                                                            <tr key={c.consultation_id}>
                                                                <td className="ps-4">{c.consultation_date}</td>
                                                                <td>Dr. {c.doctor?.user?.first_name || 'Staff'} {c.doctor?.user?.last_name || ''}</td>
                                                                <td className="text-truncate" style={{ maxWidth: '200px' }}>{c.diagnosis}</td>
                                                                <td className="pe-4 text-end">
                                                                    <Link href={route('consultations.show', c.consultation_id)} className="btn btn-sm btn-light border">
                                                                        <i className="fas fa-eye"></i>
                                                                    </Link>
                                                                </td>
                                                            </tr>
                                                        ))
                                                    ) : (
                                                        <tr><td colSpan="4" className="text-center py-5 text-muted">No consultations found.</td></tr>
                                                    )}
                                                </tbody>
                                            </table>
                                        </div>
                                    )}

                                    {activeTab === 'appointments' && (
                                        <div className="table-responsive">
                                            <table className="table table-hover align-middle mb-0">
                                                <thead className="bg-light">
                                                    <tr>
                                                        <th className="ps-4">Date & Time</th>
                                                        <th>Doctor</th>
                                                        <th>Status</th>
                                                        <th className="pe-4 text-end">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {patient.appointments.length > 0 ? (
                                                        patient.appointments.map(a => (
                                                            <tr key={a.appointment_id}>
                                                                <td className="ps-4">
                                                                    <div className="fw-bold">{a.appointment_date}</div>
                                                                    <small className="text-muted">{a.appointment_time}</small>
                                                                </td>
                                                                <td>Dr. {a.doctor?.user?.first_name || 'Staff'} {a.doctor?.user?.last_name || ''}</td>
                                                                <td><span className={`badge ${a.status === 'completed' ? 'bg-success' : 'bg-primary'}`}>{a.status}</span></td>
                                                                <td className="pe-4 text-end">
                                                                    <Link href={route('appointments.show', a.appointment_id)} className="btn btn-sm btn-light border">
                                                                        <i className="fas fa-eye"></i>
                                                                    </Link>
                                                                </td>
                                                            </tr>
                                                        ))
                                                    ) : (
                                                        <tr><td colSpan="4" className="text-center py-5 text-muted">No appointments found.</td></tr>
                                                    )}
                                                </tbody>
                                            </table>
                                        </div>
                                    )}

                                    {activeTab === 'prescriptions' && patient.prescriptions && (
                                        <div className="p-4">
                                            <div className="d-flex justify-content-between align-items-center mb-3">
                                                <h6 className="fw-bold mb-0 text-primary">Medicine History</h6>
                                                <Link href={route('prescriptions.create', { patient_id: patient.patient_id })} className="btn btn-primary btn-sm rounded-pill px-3">
                                                    <i className="fas fa-plus me-1"></i>New Prescription
                                                </Link>
                                            </div>
                                            <div className="table-responsive">
                                                <table className="table table-hover align-middle mb-0">
                                                    <thead className="bg-light">
                                                        <tr>
                                                            <th className="ps-4">Date</th>
                                                            <th>Medicines</th>
                                                            <th>Status</th>
                                                            <th className="pe-4 text-end">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {patient.prescriptions.length > 0 ? (
                                                            patient.prescriptions.map(p => (
                                                                <tr key={p.prescription_id}>
                                                                    <td className="ps-4">{p.prescription_date}</td>
                                                                    <td style={{ maxWidth: '250px' }}>
                                                                        <div className="text-truncate">
                                                                            {p.items.map(i => i.medicine_name).join(', ')}
                                                                        </div>
                                                                    </td>
                                                                    <td><span className="badge bg-info">{p.status}</span></td>
                                                                    <td className="pe-4 text-end">
                                                                        <Link href={route('prescriptions.show', p.prescription_id)} className="btn btn-sm btn-light border">
                                                                            <i className="fas fa-eye"></i>
                                                                        </Link>
                                                                    </td>
                                                                </tr>
                                                            ))
                                                        ) : (
                                                            <tr><td colSpan="4" className="text-center py-5 text-muted">No prescriptions found.</td></tr>
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
            
            <style>{`
                .avatar-circle {
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                }
                .space-y-3 > * + * {
                    margin-top: 1rem;
                }
            `}</style>
        </AuthenticatedLayout>
    );
}
