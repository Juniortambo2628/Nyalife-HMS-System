import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import UserAvatar from '@/Components/UserAvatar';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Show({ patient, auth }) {
    const isReceptionist = auth.user.role === 'receptionist';
    const [activeTab, setActiveTab] = useState(patient.consultations && !isReceptionist ? 'consultations' : 'appointments');

    const calculateAge = (dob) => {
        if (!dob) return 'N/A';
        const birthDate = new Date(dob);
        const difference = Date.now() - birthDate.getTime();
        const ageDate = new Date(difference);
        return Math.abs(ageDate.getUTCFullYear() - 1970);
    };

    return (
        <AuthenticatedLayout header="Patient Detail">
            <Head title={`Patient - ${patient.user?.first_name || 'Profile'}`} />

            <PageHeader 
                title={`Clinical Subject Record`}
                breadcrumbs={[
                    { label: 'Registry', url: route('patients.index') },
                    { label: `PAT-${patient.patient_id}`, active: true }
                ]}
            />

            <div className="px-0 pb-5">
                <div className="row g-4">
                    {/* Left Column: Personal Info */}
                    <div className="col-lg-4">
                        <div className="card shadow-sm border-0 rounded-3xl mb-4 bg-white shadow-hover overflow-hidden">
                            <div className="card-header bg-gradient-primary-to-secondary p-5 border-0 text-center">
                                <UserAvatar user={patient.user} size="2xl" className="mb-3 border border-4 border-white shadow-lg" />
                                <h3 className="fw-extrabold text-white mb-1 tracking-tighter">{patient.user?.first_name} {patient.user?.last_name}</h3>
                                <div className="extra-small font-bold text-white opacity-50 tracking-widest uppercase mb-4">
                                    ID: PAT-{patient.patient_id} | REF: {patient.patient_number}
                                </div>
                                
                                <div className="d-flex justify-content-center gap-2">
                                    <span className="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-2 fw-extrabold extra-small border border-white border-opacity-10">
                                        <i className={`fas fa-${patient.gender === 'male' ? 'mars' : 'venus'} me-1`}></i>
                                        {(patient.gender || 'unknown').toUpperCase()}
                                    </span>
                                    <span className="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-2 fw-extrabold extra-small border border-white border-opacity-10">
                                        {calculateAge(patient.date_of_birth || patient.user?.date_of_birth)} YEARS
                                    </span>
                                    {patient.blood_group && (
                                        <span className="badge bg-white text-primary rounded-pill px-3 py-2 fw-extrabold extra-small shadow-sm">
                                            {patient.blood_group}
                                        </span>
                                    )}
                                </div>
                            </div>
                            <div className="card-body p-4 pt-5">
                                <div className="space-y-4 px-2">
                                    <div className="d-flex align-items-center">
                                        <div className="avatar-sm bg-gray-50 text-primary rounded-xl d-flex align-items-center justify-content-center me-3 flex-shrink-0 border">
                                            <i className="fas fa-phone-alt text-xs opacity-50"></i>
                                        </div>
                                        <div>
                                            <div className="extra-small text-muted fw-bold text-uppercase tracking-wider opacity-50">Primary Contact</div>
                                            <div className="fw-extrabold text-gray-800">{patient.user.phone || 'N/A'}</div>
                                        </div>
                                    </div>
                                    <div className="d-flex align-items-center">
                                        <div className="avatar-sm bg-gray-50 text-primary rounded-xl d-flex align-items-center justify-content-center me-3 flex-shrink-0 border">
                                            <i className="fas fa-envelope text-xs opacity-50"></i>
                                        </div>
                                        <div className="overflow-hidden">
                                            <div className="extra-small text-muted fw-bold text-uppercase tracking-wider opacity-50">Electronic Mail</div>
                                            <div className="fw-extrabold text-gray-800 text-truncate">{patient.user.email}</div>
                                        </div>
                                    </div>
                                    <div className="d-flex align-items-center">
                                        <div className="avatar-sm bg-gray-50 text-primary rounded-xl d-flex align-items-center justify-content-center me-3 flex-shrink-0 border">
                                            <i className="fas fa-map-marker-alt text-xs opacity-50"></i>
                                        </div>
                                        <div>
                                            <div className="extra-small text-muted fw-bold text-uppercase tracking-wider opacity-50">Physical Address</div>
                                            <div className="fw-extrabold text-gray-800">{patient.user.address || 'Sabaki / Unknown'}</div>
                                        </div>
                                    </div>
                                    <div className="d-flex align-items-center pt-3 border-top border-gray-50">
                                        <div className="avatar-sm bg-pink-50 text-pink-500 rounded-xl d-flex align-items-center justify-content-center me-3 flex-shrink-0 border border-pink-100">
                                            <i className="fas fa-user-shield text-xs opacity-50"></i>
                                        </div>
                                        <div>
                                            <div className="extra-small text-muted fw-bold text-uppercase tracking-wider opacity-50">Emergency Contact</div>
                                            <div className="fw-extrabold text-gray-900 small">
                                                {patient.emergency_name || 'NOT SPECIFIED'}
                                                {patient.emergency_contact ? ` - ${patient.emergency_contact}` : ''}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Recent Vitals Card */}
                        <div className="card shadow-sm border-0 rounded-3xl bg-white shadow-hover overflow-hidden">
                            <div className="card-header bg-white py-4 px-5 border-bottom-0">
                                <h6 className="mb-0 fw-extrabold text-primary extra-small text-uppercase tracking-widest">
                                    <i className="fas fa-vial me-2 opacity-50"></i>Physical Monitoring
                                </h6>
                            </div>
                            <div className="card-body p-0">
                                {patient.vitals.length > 0 ? (
                                    <div className="list-group list-group-flush">
                                        {patient.vitals.slice(0, 3).map((v, i) => (
                                            <div key={v.vital_id} className="list-group-item py-4 px-5 border-gray-50">
                                                <div className="d-flex justify-content-between align-items-center mb-3">
                                                    <span className="extra-small fw-extrabold text-muted text-uppercase tracking-widest">{v.measured_at}</span>
                                                    {i === 0 && <span className="badge bg-success-subtle text-success border border-success-subtle rounded-pill extra-small px-2 fw-bold">LATEST</span>}
                                                </div>
                                                <div className="row g-3">
                                                    <div className="col-6">
                                                        <div className="extra-small text-muted fw-bold mb-1 uppercase opacity-50">BP</div>
                                                        <div className="fw-extrabold text-gray-900">{v.blood_pressure}</div>
                                                    </div>
                                                    <div className="col-6">
                                                        <div className="extra-small text-muted fw-bold mb-1 uppercase opacity-50">Temp</div>
                                                        <div className="fw-extrabold text-gray-900">{v.temperature}°C</div>
                                                    </div>
                                                    <div className="col-6">
                                                        <div className="extra-small text-muted fw-bold mb-1 uppercase opacity-50">Pulse</div>
                                                        <div className="fw-extrabold text-gray-900">{v.heart_rate} <span className="extra-small opacity-50">bpm</span></div>
                                                    </div>
                                                    <div className="col-6">
                                                        <div className="extra-small text-muted fw-bold mb-1 uppercase opacity-50">SpO2</div>
                                                        <div className="fw-extrabold text-gray-900">{v.oxygen_saturation}%</div>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="p-12 text-center">
                                        <div className="bg-gray-50 text-gray-200 p-5 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style={{ width: '80px', height: '80px' }}>
                                            <i className="fas fa-heartbeat fa-2x opacity-20"></i>
                                        </div>
                                        <p className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-0 opacity-50">No clinical monitoring data</p>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Right Column: Tabs and Records */}
                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 rounded-3xl bg-white h-100 overflow-hidden shadow-hover">
                            <div className="card-header bg-white border-0 p-5 pb-0">
                                <div className="d-flex gap-4 border-bottom border-gray-100">
                                    {patient.consultations && !isReceptionist && (
                                        <button 
                                            className={`pb-3 extra-small fw-extrabold tracking-widest transition-all border-bottom-2 ${
                                                activeTab === 'consultations' ? 'text-primary border-primary' : 'text-muted border-transparent opacity-50'
                                            }`} 
                                            onClick={() => setActiveTab('consultations')}
                                        >
                                            CLINICAL NARRATIVES
                                        </button>
                                    )}
                                    <button 
                                        className={`pb-3 extra-small fw-extrabold tracking-widest transition-all border-bottom-2 ${
                                            activeTab === 'appointments' ? 'text-primary border-primary' : 'text-muted border-transparent opacity-50'
                                        }`} 
                                        onClick={() => setActiveTab('appointments')}
                                    >
                                        SCHEDULED VISITS
                                    </button>
                                    {patient.prescriptions && !isReceptionist && (
                                        <button 
                                            className={`pb-3 extra-small fw-extrabold tracking-widest transition-all border-bottom-2 ${
                                                activeTab === 'prescriptions' ? 'text-primary border-primary' : 'text-muted border-transparent opacity-50'
                                            }`} 
                                            onClick={() => setActiveTab('prescriptions')}
                                        >
                                            PHARMACY REGISTRY
                                        </button>
                                    )}
                                </div>
                            </div>
                            
                            <div className="card-body p-0">
                                <div className="tab-content">
                                    {activeTab === 'consultations' && patient.consultations && (
                                        <div className="table-responsive">
                                            <table className="table table-hover align-middle mb-0">
                                                <thead className="bg-gray-50">
                                                    <tr>
                                                        <th className="px-5 py-3 extra-small fw-extrabold text-muted text-uppercase tracking-widest border-0">Timestamp</th>
                                                        <th className="px-5 py-3 extra-small fw-extrabold text-muted text-uppercase tracking-widest border-0">Attending Physician</th>
                                                        <th className="px-5 py-3 extra-small fw-extrabold text-muted text-uppercase tracking-widest border-0">Diagnosis</th>
                                                        <th className="px-5 py-3 text-end extra-small fw-extrabold text-muted text-uppercase tracking-widest border-0">Record</th>
                                                    </tr>
                                                </thead>
                                                <tbody className="border-0">
                                                    {patient.consultations.length > 0 ? (
                                                        patient.consultations.map(c => (
                                                            <tr key={c.consultation_id} className="border-bottom border-gray-50">
                                                                <td className="px-5 py-3 fw-extrabold text-gray-900 small">{c.consultation_date}</td>
                                                                <td className="px-5 py-3 fw-bold text-gray-700 small">Dr. {c.doctor?.user?.last_name || 'Staff'}</td>
                                                                <td className="px-5 py-3">
                                                                    <div className="text-truncate extra-small fw-extrabold text-primary text-uppercase" style={{ maxWidth: '200px' }}>{c.diagnosis}</div>
                                                                </td>
                                                                <td className="px-5 py-3 text-end">
                                                                    <Link href={route('consultations.show', c.consultation_id)} className="btn btn-sm btn-light border text-primary rounded-circle p-2 shadow-sm">
                                                                        <i className="fas fa-chevron-right text-xs"></i>
                                                                    </Link>
                                                                </td>
                                                            </tr>
                                                        ))
                                                    ) : (
                                                        <tr><td colSpan="4" className="text-center py-20 text-muted extra-small fw-extrabold text-uppercase opacity-20">No clinical narratives found</td></tr>
                                                    )}
                                                </tbody>
                                            </table>
                                        </div>
                                    )}

                                    {activeTab === 'appointments' && (
                                        <div className="table-responsive">
                                            <table className="table table-hover align-middle mb-0">
                                                <thead className="bg-gray-50">
                                                    <tr>
                                                        <th className="px-5 py-3 extra-small fw-extrabold text-muted text-uppercase tracking-widest border-0">Allocation</th>
                                                        <th className="px-5 py-3 extra-small fw-extrabold text-muted text-uppercase tracking-widest border-0">Practitioner</th>
                                                        <th className="px-5 py-3 extra-small fw-extrabold text-muted text-uppercase tracking-widest border-0">Status</th>
                                                        <th className="px-5 py-3 text-end extra-small fw-extrabold text-muted text-uppercase tracking-widest border-0">Record</th>
                                                    </tr>
                                                </thead>
                                                <tbody className="border-0">
                                                    {patient.appointments.length > 0 ? (
                                                        patient.appointments.map(a => (
                                                            <tr key={a.appointment_id} className="border-bottom border-gray-50">
                                                                <td className="px-5 py-3">
                                                                    <div className="fw-extrabold text-gray-900 small">{a.appointment_date}</div>
                                                                    <div className="extra-small text-muted font-bold opacity-50">{a.appointment_time}</div>
                                                                </td>
                                                                <td className="px-5 py-3 fw-bold text-gray-700 small">Dr. {a.doctor?.user?.last_name || 'Staff'}</td>
                                                                <td className="px-5 py-3"><StatusBadge status={a.status} /></td>
                                                                <td className="px-5 py-3 text-end">
                                                                    <Link href={route('appointments.show', a.appointment_id)} className="btn btn-sm btn-light border text-primary rounded-circle p-2 shadow-sm">
                                                                        <i className="fas fa-chevron-right text-xs"></i>
                                                                    </Link>
                                                                </td>
                                                            </tr>
                                                        ))
                                                    ) : (
                                                        <tr><td colSpan="4" className="text-center py-20 text-muted extra-small fw-extrabold text-uppercase opacity-20">No scheduled visits recorded</td></tr>
                                                    )}
                                                </tbody>
                                            </table>
                                        </div>
                                    )}

                                    {activeTab === 'prescriptions' && patient.prescriptions && (
                                        <div className="p-0">
                                            <div className="d-flex justify-content-between align-items-center px-5 py-4 border-bottom border-gray-50">
                                                <h6 className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-0">Active Medications</h6>
                                                <Link href={route('prescriptions.create', { patient_id: patient.patient_id })} className="btn btn-primary btn-sm rounded-pill px-4 fw-extrabold extra-small tracking-widest shadow-sm">
                                                    NEW PRESCRIPTION
                                                </Link>
                                            </div>
                                            <div className="table-responsive">
                                                <table className="table table-hover align-middle mb-0">
                                                    <thead className="bg-gray-50">
                                                        <tr>
                                                            <th className="px-5 py-3 extra-small fw-extrabold text-muted text-uppercase tracking-widest border-0">Date Issued</th>
                                                            <th className="px-5 py-3 extra-small fw-extrabold text-muted text-uppercase tracking-widest border-0">Itemization</th>
                                                            <th className="px-5 py-3 extra-small fw-extrabold text-muted text-uppercase tracking-widest border-0">Dispensing</th>
                                                            <th className="px-5 py-3 text-end extra-small fw-extrabold text-muted text-uppercase tracking-widest border-0">Record</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody className="border-0">
                                                        {patient.prescriptions.length > 0 ? (
                                                            patient.prescriptions.map(p => (
                                                                <tr key={p.prescription_id} className="border-bottom border-gray-50">
                                                                    <td className="px-5 py-3 fw-extrabold text-gray-900 small">{p.prescription_date}</td>
                                                                    <td className="px-5 py-3" style={{ maxWidth: '250px' }}>
                                                                        <div className="text-truncate extra-small fw-bold text-gray-500 uppercase tracking-tighter">
                                                                            {p.items.map(i => i.medicine_name).join(' • ')}
                                                                        </div>
                                                                    </td>
                                                                    <td className="px-5 py-3">
                                                                        <span className={`badge rounded-pill px-3 py-1.5 fw-extrabold extra-small text-uppercase ${p.status === 'dispensed' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning-emphasis'}`}>
                                                                            {p.status}
                                                                        </span>
                                                                    </td>
                                                                    <td className="px-5 py-3 text-end">
                                                                        <Link href={route('prescriptions.show', p.prescription_id)} className="btn btn-sm btn-light border text-primary rounded-circle p-2 shadow-sm">
                                                                            <i className="fas fa-chevron-right text-xs"></i>
                                                                        </Link>
                                                                    </td>
                                                                </tr>
                                                            ))
                                                        ) : (
                                                            <tr><td colSpan="4" className="text-center py-20 text-muted extra-small fw-extrabold text-uppercase opacity-20">No pharmacy entries recorded</td></tr>
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
                actions={[
                    ['doctor', 'nurse', 'admin', 'lab_technician'].includes(auth.user.role) && { 
                        label: 'NEW CONSULTATION', 
                        icon: 'fa-stethoscope', 
                        href: route('consultations.create', { patient_id: patient.patient_id }) 
                    },
                    { 
                        label: 'SCHEDULE VISIT', 
                        icon: 'fa-calendar-plus', 
                        href: route('appointments.create', { patient_id: patient.patient_id }),
                        color: 'pink'
                    },
                    { 
                        label: 'EDIT PROFILE', 
                        icon: 'fa-user-edit', 
                        href: route('patients.edit', patient.patient_id),
                        color: 'gray'
                    }
                ].filter(Boolean)}
            />
        </AuthenticatedLayout>
    );
}
