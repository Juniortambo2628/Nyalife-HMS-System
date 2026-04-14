import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { formatDateTime } from '@/Utils/dateUtils';

export default function View({ consultation, auth }) {
    // Helpers to safely display JSON or text data
    const safeText = (text) => text || 'N/A';
    const safeObj = (obj, key) => (obj && obj[key]) ? obj[key] : 'N/A';

    return (
        <AuthenticatedLayout
            user={auth.user}
            header="Consultation Details"
        >
            <Head title={`Consultation - ${consultation.patient.user.first_name}`} />

            <PageHeader 
                title={`Consultation #${consultation.consultation_id}`}
                breadcrumbs={[
                    { label: 'Dashboard', url: route('dashboard') },
                    { label: 'Consultations', url: route('consultations.index') },
                    { label: 'View Record', active: true }
                ]}
                actions={
                    <div className="d-flex gap-2">
                        <Link 
                            href={route('prescriptions.create', { 
                                patient_id: consultation.patient_id,
                                consultation_id: consultation.consultation_id 
                            })} 
                            className="btn btn-primary rounded-pill px-4 shadow-sm fw-bold"
                        >
                            <i className="fas fa-prescription me-2"></i>Add Prescription
                        </Link>
                         <Link 
                            href={route('invoices.create', { 
                                patient_id: consultation.patient_id, 
                                consultation_id: consultation.consultation_id 
                            })} 
                            className="btn btn-success rounded-pill px-4 shadow-sm fw-bold"
                        >
                            <i className="fas fa-file-invoice-dollar me-2"></i>Generate Invoice
                        </Link>
                        <Link href={route('consultations.index')} className="btn btn-outline-secondary rounded-pill px-4 shadow-sm fw-bold">
                            <i className="fas fa-arrow-left me-2"></i>Back to List
                        </Link>
                    </div>
                }
            />

            <div className="container-fluid consultations-page px-0 pb-5">
                <div className="row g-4">
                    {/* Left Column: Patient & Basic Info */}
                    <div className="col-lg-3">
                        <div className="card border-0 shadow-sm rounded-4 mb-4 text-center overflow-hidden">
                            <div className="card-header bg-primary text-white p-4">
                                <div className="avatar-circle mx-auto mb-2 bg-white text-primary fw-bold display-6 d-flex align-items-center justify-content-center" style={{ width: '80px', height: '80px', borderRadius: '50%' }}>
                                    {consultation.patient.user.first_name.charAt(0)}
                                </div>
                                <h5 className="mb-0">{consultation.patient.user.first_name} {consultation.patient.user.last_name}</h5>
                                <small className="opacity-75">{consultation.patient_id}</small>
                            </div>
                            <div className="card-body p-3 text-start">
                                <ul className="list-group list-group-flush">
                                    <li className="list-group-item px-0 py-2 d-flex justify-content-between">
                                        <span className="text-muted small">Date</span>
                                        <span className="fw-bold small">{formatDateTime(consultation.consultation_date)}</span>
                                    </li>
                                    <li className="list-group-item px-0 py-2 d-flex justify-content-between">
                                        <span className="text-muted small">Doctor</span>
                                        <span className="fw-bold small">Dr. {consultation.doctor.user.last_name}</span>
                                    </li>
                                    <li className="list-group-item px-0 py-2 d-flex justify-content-between">
                                        <span className="text-muted small">Status</span>
                                        <span className={`badge bg-${consultation.consultation_status === 'closed' ? 'success' : 'warning'}`}>{consultation.consultation_status.toUpperCase()}</span>
                                    </li>
                                </ul>
                                <div className="d-grid mt-3">
                                    <Link href={route('patients.show', consultation.patient_id)} className="btn btn-outline-primary btn-sm rounded-pill">View Patient Profile</Link>
                                </div>
                            </div>
                        </div>

                        {/* Vital Signs Summary */}
                        {consultation.vital_signs && (
                            <div className="card border-0 shadow-sm rounded-4 mb-4">
                                <div className="card-header bg-white border-bottom fw-bold text-danger">
                                    <i className="fas fa-heartbeat me-2"></i>Recorded Vitals
                                </div>
                                <div className="card-body">
                                    <div className="row g-2 text-center">
                                        <div className="col-6 mb-2">
                                            <div className="small text-muted text-uppercase" style={{fontSize: '0.7rem'}}>BP</div>
                                            <div className="fw-bold">{safeObj(consultation.vital_signs, 'blood_pressure')}</div>
                                        </div>
                                        <div className="col-6 mb-2">
                                            <div className="small text-muted text-uppercase" style={{fontSize: '0.7rem'}}>HR</div>
                                            <div className="fw-bold">{safeObj(consultation.vital_signs, 'heart_rate')} bpm</div>
                                        </div>
                                        <div className="col-6">
                                            <div className="small text-muted text-uppercase" style={{fontSize: '0.7rem'}}>Temp</div>
                                            <div className="fw-bold">{safeObj(consultation.vital_signs, 'temperature')} °C</div>
                                        </div>
                                        <div className="col-6">
                                            <div className="small text-muted text-uppercase" style={{fontSize: '0.7rem'}}>SpO2</div>
                                            <div className="fw-bold">{safeObj(consultation.vital_signs, 'oxygen_saturation')} %</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Right Column: Detailed Records */}
                    <div className="col-lg-9">
                        {/* Clinical Data Sections (Restricted to Doctor/Admin) */}
                        {['doctor', 'admin'].includes(auth.user.role) ? (
                            <>
                                {/* 1. Complaints */}
                                <div className="card border-0 shadow-sm rounded-4 mb-4">
                                    <div className="card-header bg-white p-3 d-flex justify-content-between">
                                        <h6 className="mb-0 fw-bold text-primary">Chief Complaints</h6>
                                        {auth.user.role === 'doctor' && consultation.consultation_status === 'open' && (
                                            <Link href={route('consultations.edit', consultation.consultation_id)} className="btn btn-sm btn-outline-secondary">
                                                <i className="fas fa-edit me-1"></i>Edit
                                            </Link>
                                        )}
                                    </div>
                                    <div className="card-body p-4">
                                        <p className="lead border-start border-4 border-primary ps-3 bg-light p-3 rounded mb-3">
                                            {consultation.chief_complaint}
                                        </p>
                                        <h6 className="text-secondary small fw-bold text-uppercase mt-4">History of Present Illness</h6>
                                        <p className="text-muted">{safeText(consultation.history_present_illness)}</p>
                                    </div>
                                </div>

                                {/* 2. Histories Grid */}
                                <div className="row g-4 mb-4">
                                    <div className="col-md-6">
                                        <div className="card border-0 shadow-sm rounded-4 h-100">
                                            <div className="card-body">
                                                <h6 className="fw-bold text-primary mb-3"><i className="fas fa-notes-medical me-2"></i>Medical & Surgical History</h6>
                                                <div className="mb-3">
                                                    <small className="text-muted text-uppercase fw-bold d-block mb-1">Past Medical</small>
                                                    <p className="small mb-0">{safeText(consultation.past_medical_history)}</p>
                                                </div>
                                                <div>
                                                    <small className="text-muted text-uppercase fw-bold d-block mb-1">Surgical</small>
                                                    <p className="small mb-0">{safeText(consultation.surgical_history)}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="card border-0 shadow-sm rounded-4 h-100">
                                            <div className="card-body">
                                                <h6 className="fw-bold text-primary mb-3"><i className="fas fa-users me-2"></i>Family & Social History</h6>
                                                <div className="mb-3">
                                                    <small className="text-muted text-uppercase fw-bold d-block mb-1">Family History</small>
                                                    <p className="small mb-0">{safeText(consultation.family_history)}</p>
                                                </div>
                                                <div>
                                                    <small className="text-muted text-uppercase fw-bold d-block mb-1">Social History</small>
                                                    <p className="small mb-0">{safeText(consultation.social_history)}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* 3. Gynaecological & Obstetric */}
                                <div className="card border-0 shadow-sm rounded-4 mb-4">
                                    <div className="card-header bg-pink-50 text-pink-700 p-3">
                                        <h6 className="mb-0 fw-bold"><i className="fas fa-venus me-2"></i>Reproductive Health</h6>
                                    </div>
                                    <div className="card-body p-4">
                                        <div className="row g-4">
                                            <div className="col-md-6 border-end">
                                                <h6 className="text-secondary small fw-bold text-uppercase mb-3">Gynaecological</h6>
                                                <ul className="list-unstyled small">
                                                    <li className="mb-2"><strong>LMP:</strong> {safeObj(consultation.menstrual_history, 'last_period_date')}</li>
                                                    <li className="mb-2"><strong>Cycle:</strong> {safeObj(consultation.menstrual_history, 'regularity')}, {safeObj(consultation.menstrual_history, 'flow_duration')} days</li>
                                                    <li className="mb-2"><strong>Dysmenorrhea:</strong> {safeObj(consultation.menstrual_history, 'dysmenorrhea')}</li>
                                                    <li className="mb-2"><strong>Pap Smear:</strong> {safeText(consultation.cervical_screening)}</li>
                                                    <li className="mb-2"><strong>Contraception:</strong> {safeText(consultation.contraceptive_history)}</li>
                                                </ul>
                                            </div>
                                            <div className="col-md-6">
                                                <h6 className="text-secondary small fw-bold text-uppercase mb-3">Obstetric</h6>
                                                <p className="small mb-2"><strong>Parity:</strong> {safeText(consultation.parity)}</p>
                                                <p className="small mb-3"><strong>Current Pregnancy:</strong> {safeText(consultation.current_pregnancy)}</p>
                                                
                                                {consultation.past_obstetric && consultation.past_obstetric.length > 0 ? (
                                                    <div className="table-responsive">
                                                        <table className="table table-sm table-bordered small mb-0">
                                                            <thead className="table-light">
                                                                <tr>
                                                                    <th>Year</th>
                                                                    <th>Mode</th>
                                                                    <th>Outcome</th>
                                                                    <th>Sex</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                {consultation.past_obstetric.map((rec, i) => (
                                                                    <tr key={i}>
                                                                        <td>{rec.year}</td>
                                                                        <td>{rec.mode_of_delivery}</td>
                                                                        <td>{rec.outcome}</td>
                                                                        <td>{rec.sex}</td>
                                                                    </tr>
                                                                ))}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                ) : (
                                                    <p className="text-muted small italic">No past pregnancy records.</p>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* 4. Examination */}
                                <div className="card border-0 shadow-sm rounded-4 mb-4">
                                    <div className="card-header bg-white p-3">
                                        <h6 className="mb-0 fw-bold text-primary">Examination Findings</h6>
                                    </div>
                                    <div className="card-body p-4">
                                        <div className="mb-3">
                                            <small className="text-muted text-uppercase fw-bold d-block mb-1">General Examination</small>
                                            <p>{safeText(consultation.general_examination)}</p>
                                        </div>
                                        <div className="mb-3">
                                            <small className="text-muted text-uppercase fw-bold d-block mb-1">System Review</small>
                                            <p>{safeText(consultation.review_of_systems)}</p>
                                        </div>
                                        <div>
                                            <small className="text-muted text-uppercase fw-bold d-block mb-1">Specific Systems</small>
                                            <p>{safeText(consultation.systems_examination)}</p>
                                        </div>
                                    </div>
                                </div>

                                {/* 5. Diagnosis & Plan */}
                                <div className="card border-0 shadow-sm rounded-4 border-start border-5 border-success">
                                    <div className="card-header bg-success-subtle p-3">
                                        <h6 className="mb-0 fw-bold text-success-emphasis">Diagnosis & Management</h6>
                                    </div>
                                    <div className="card-body p-4">
                                        <div className="row">
                                            <div className="col-md-12 mb-4">
                                                <h5 className="fw-bold mb-2">My Impression</h5>
                                                <p className="lead bg-light p-3 rounded">{consultation.diagnosis}</p>
                                            </div>
                                            <div className="col-md-6">
                                                <h6 className="text-secondary small fw-bold text-uppercase mb-2">Treatment Plan</h6>
                                                <p className="small border p-3 rounded">{safeText(consultation.treatment_plan)}</p>
                                            </div>
                                            <div className="col-md-6">
                                                <h6 className="text-secondary small fw-bold text-uppercase mb-2">Instructions / Notes</h6>
                                                <ul className="list-unstyled small">
                                                    <li className="mb-2"><i className="fas fa-check-circle me-2 text-success"></i><strong>Follow-up:</strong> {safeText(consultation.follow_up_instructions)}</li>
                                                    <li className="mb-2"><i className="fas fa-sticky-note me-2 text-warning"></i><strong>Notes:</strong> {safeText(consultation.notes)}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </>
                        ) : (
                            <div className="card border-0 shadow-sm rounded-4 mb-4 bg-light border-start border-5 border-info">
                                <div className="card-body p-5 text-center">
                                    <div className="display-4 text-info mb-4">
                                        <i className="fas fa-user-shield"></i>
                                    </div>
                                    <h5 className="fw-bold">Medical Records Access Restricted</h5>
                                    <p className="text-muted mb-0">Professional nursing access is limited to patient biodata and vital signs. Full clinical narratives and diagnoses are restricted to attending physicians and administrators.</p>
                                </div>
                            </div>
                        )}

                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
