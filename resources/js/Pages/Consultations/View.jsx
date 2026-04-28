import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { formatDateTime } from '@/Utils/dateUtils';
import { useState } from 'react';
import InfoModalComp from '@/Components/InfoModal';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function View({ consultation, auth }) {
    // Helpers to safely display JSON or text data
    const safeText = (text) => text || 'N/A';
    const safeObj = (obj, key) => (obj && obj[key]) ? obj[key] : 'N/A';

    const [selectedLabRequest, setSelectedLabRequest] = useState(null);

    const getLabResultTabs = (lab) => {
        if (!lab) return [];
        const tabs = [];
        const results = typeof lab.results === 'string' ? JSON.parse(lab.results) : (lab.results || {});

        if (results.quantitative && results.quantitative.length > 0) {
            tabs.push({ id: 'quantitative', label: 'Quantitative Results', icon: 'fa-list-ol', content: (
                <div className="table-responsive animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <table className="table table-hover align-middle">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-3 extra-small fw-extrabold text-muted text-uppercase border-0">Parameter</th>
                                <th className="px-4 py-3 extra-small fw-extrabold text-muted text-uppercase border-0">Result</th>
                                <th className="px-4 py-3 extra-small fw-extrabold text-muted text-uppercase border-0">Unit</th>
                                <th className="px-4 py-3 extra-small fw-extrabold text-muted text-uppercase border-0">Range</th>
                                <th className="px-4 py-3 extra-small fw-extrabold text-muted text-uppercase border-0">Status</th>
                            </tr>
                        </thead>
                        <tbody className="border-0">
                            {results.quantitative.map((r, idx) => (
                                <tr key={idx} className="border-bottom border-gray-50">
                                    <td className="px-4 py-3 fw-bold text-gray-800">{r.label}</td>
                                    <td className="px-4 py-3 fw-extrabold fs-5 text-primary">{r.value}</td>
                                    <td className="px-4 py-3 text-muted small">{r.unit || '-'}</td>
                                    <td className="px-4 py-3 text-muted font-mono extra-small">{r.normalRange || '-'}</td>
                                    <td className="px-4 py-3">
                                        {r.isAbnormal ? 
                                            <span className="badge bg-danger-subtle text-danger rounded-pill px-3 py-1.5 fw-bold extra-small text-uppercase"><i className="fas fa-exclamation-triangle me-1"></i>Abnormal</span> : 
                                            <span className="badge bg-success-subtle text-success rounded-pill px-3 py-1.5 fw-bold extra-small text-uppercase"><i className="fas fa-check me-1"></i>Normal</span>
                                        }
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )} );
        }

        if (results.observations) {
            tabs.push({ id: 'observations', label: 'Observations', icon: 'fa-eye', content: (
                <div className="animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div className="p-5 bg-gray-50 rounded-3xl border border-gray-100 shadow-inner">
                        <h6 className="extra-small fw-extrabold text-primary text-uppercase tracking-widest mb-4">Qualitative Findings</h6>
                        <div className="text-gray-800 leading-relaxed font-medium" style={{ whiteSpace: 'pre-wrap' }}>{results.observations}</div>
                    </div>
                </div>
            )} );
        }

        if (results.conclusion) {
            tabs.push({ id: 'conclusion', label: 'Conclusion', icon: 'fa-user-md', content: (
                <div className="animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div className="p-5 bg-primary-subtle rounded-3xl border border-primary-subtle shadow-sm">
                        <h6 className="extra-small fw-extrabold text-primary text-uppercase tracking-widest mb-4">Certified Conclusion</h6>
                        <div className="text-primary-emphasis fw-extrabold leading-relaxed" style={{ whiteSpace: 'pre-wrap' }}>{results.conclusion}</div>
                    </div>
                </div>
            )} );
        }

        if (results.attachments && results.attachments.length > 0) {
            tabs.push({ id: 'attachments', label: 'Evidence', icon: 'fa-paperclip', content: (
                <div className="animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div className="row g-3">
                        {results.attachments.map((file, idx) => (
                            <div key={idx} className="col-sm-6">
                                <a href={`/storage/${file.path}`} target="_blank" rel="noopener noreferrer" className="card h-100 border-0 shadow-sm rounded-2xl bg-white hover-lift transition-all p-3">
                                    <div className="d-flex align-items-center gap-3">
                                        <div className="avatar-md bg-light rounded-xl d-flex align-items-center justify-content-center">
                                            <i className={`fas fa-lg ${file.type.startsWith('image') ? 'fa-file-image text-pink-500' : 'fa-file-pdf text-danger'}`}></i>
                                        </div>
                                        <div className="overflow-hidden">
                                            <h6 className="text-truncate mb-0 fw-bold small text-gray-900" title={file.name}>{file.name}</h6>
                                            <div className="extra-small text-muted font-bold opacity-50 text-uppercase">{(file.size / 1024).toFixed(1)} KB</div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        ))}
                    </div>
                </div>
            )} );
        }

        tabs.push({ id: 'metadata', label: 'Info', icon: 'fa-info-circle', content: (
            <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div className="p-4 rounded-2xl bg-blue-50 border border-blue-100 shadow-inner">
                    <div className="row g-4">
                        <div className="col-6">
                            <div className="extra-small text-blue-400 fw-extrabold text-uppercase tracking-widest mb-1">Investigation</div>
                            <div className="fw-extrabold text-blue-900">{lab.test_type?.test_name}</div>
                        </div>
                        <div className="col-6">
                            <div className="extra-small text-blue-400 fw-extrabold text-uppercase tracking-widest mb-1">Category</div>
                            <div className="fw-extrabold text-blue-900">{lab.test_type?.category || 'LAB'}</div>
                        </div>
                    </div>
                </div>
                <div className="space-y-3 px-2">
                    <div className="d-flex justify-content-between border-bottom border-gray-50 pb-2">
                        <span className="extra-small fw-bold text-muted text-uppercase">Status</span>
                        <span className="badge bg-success rounded-pill px-3 py-1 fw-extrabold extra-small">{(lab.status || 'completed').toUpperCase()}</span>
                    </div>
                    <div className="d-flex justify-content-between border-bottom border-gray-50 pb-2">
                        <span className="extra-small fw-bold text-muted text-uppercase">Certified On</span>
                        <span className="fw-extrabold text-gray-900 small">{lab.completed_at ? new Date(lab.completed_at).toLocaleString() : 'N/A'}</span>
                    </div>
                    <div className="d-flex justify-content-between border-bottom border-gray-50 pb-2">
                        <span className="extra-small fw-bold text-muted text-uppercase">Performed By</span>
                        <span className="fw-extrabold text-gray-900 small">{lab.assignedToUser ? `${lab.assignedToUser.first_name} ${lab.assignedToUser.last_name}` : 'Laboratory Staff'}</span>
                    </div>
                </div>
            </div>
        )} );

        return tabs;
    };


    return (
        <AuthenticatedLayout header="Consultation Record">
            <Head title={`Consultation - ${consultation.patient.user.first_name}`} />

            <PageHeader 
                title="Clinical Narrative"
                breadcrumbs={[
                    { label: 'Consultations', url: route('consultations.index') },
                    { label: `Record #${consultation.consultation_id}`, active: true }
                ]}
            />

            <div className="container-fluid consultations-page px-0 pb-5">
                <div className="row g-4">
                    {/* Left Column: Patient & Basic Info */}
                    <div className="col-lg-3">
                        <div className="card border-0 shadow-sm rounded-3xl mb-4 text-center overflow-hidden bg-white shadow-hover">
                            <div className="card-header bg-gradient-primary-to-secondary p-5 border-0">
                                <div className="avatar-xl mx-auto mb-3 bg-white text-primary fw-extrabold shadow-lg rounded-circle d-flex align-items-center justify-content-center tracking-tightest fs-2">
                                    {consultation.patient.user.first_name.charAt(0)}
                                </div>
                                <h5 className="mb-1 text-white fw-extrabold tracking-tighter">{consultation.patient.user.first_name} {consultation.patient.user.last_name}</h5>
                                <div className="extra-small font-bold text-white opacity-50 tracking-widest uppercase">PAT-ID: {consultation.patient_id}</div>
                            </div>
                            <div className="card-body p-4 text-start">
                                <div className="space-y-4">
                                    <div className="d-flex justify-content-between align-items-center border-bottom border-gray-50 pb-2">
                                        <span className="extra-small fw-bold text-muted text-uppercase">Visit Date</span>
                                        <span className="fw-extrabold text-gray-900 small">{formatDateTime(consultation.consultation_date)}</span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center border-bottom border-gray-50 pb-2">
                                        <span className="extra-small fw-bold text-muted text-uppercase">Physician</span>
                                        <span className="fw-extrabold text-gray-900 small">Dr. {consultation.doctor.user.last_name}</span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center">
                                        <span className="extra-small fw-bold text-muted text-uppercase">Status</span>
                                        <span className={`badge rounded-pill px-3 py-1 fw-extrabold extra-small text-uppercase ${consultation.consultation_status === 'closed' ? 'bg-success text-white' : 'bg-warning text-dark'}`}>
                                            {consultation.consultation_status}
                                        </span>
                                    </div>
                                </div>
                                <div className="d-grid mt-5">
                                    <Link href={route('patients.show', consultation.patient_id)} className="btn btn-outline-primary btn-sm rounded-pill fw-bold tracking-widest extra-small py-2.5">
                                        VIEW PATIENT PROFILE
                                    </Link>
                                </div>
                            </div>
                        </div>

                        {/* Vital Signs Summary */}
                        {consultation.vital_signs && (
                            <div className="card border-0 shadow-sm rounded-3xl mb-4 bg-white shadow-hover">
                                <div className="card-header bg-white border-bottom-0 py-4 px-4">
                                    <h6 className="mb-0 fw-extrabold text-primary extra-small text-uppercase tracking-widest">
                                        <i className="fas fa-heartbeat me-2"></i>Physical Vitals
                                    </h6>
                                </div>
                                <div className="card-body p-4 pt-0">
                                    <div className="row g-3 text-center">
                                        <div className="col-6">
                                            <div className="p-3 rounded-2xl bg-gray-50 border border-gray-100">
                                                <div className="extra-small text-muted text-uppercase fw-bold opacity-50 mb-1">BP</div>
                                                <div className="fw-extrabold text-gray-900">{safeObj(consultation.vital_signs, 'blood_pressure')}</div>
                                            </div>
                                        </div>
                                        <div className="col-6">
                                            <div className="p-3 rounded-2xl bg-gray-50 border border-gray-100">
                                                <div className="extra-small text-muted text-uppercase fw-bold opacity-50 mb-1">HR</div>
                                                <div className="fw-extrabold text-gray-900">{safeObj(consultation.vital_signs, 'heart_rate')} <span className="extra-small opacity-50">bpm</span></div>
                                            </div>
                                        </div>
                                        <div className="col-6">
                                            <div className="p-3 rounded-2xl bg-gray-50 border border-gray-100">
                                                <div className="extra-small text-muted text-uppercase fw-bold opacity-50 mb-1">Temp</div>
                                                <div className="fw-extrabold text-gray-900">{safeObj(consultation.vital_signs, 'temperature')} <span className="extra-small opacity-50">°C</span></div>
                                            </div>
                                        </div>
                                        <div className="col-6">
                                            <div className="p-3 rounded-2xl bg-gray-50 border border-gray-100">
                                                <div className="extra-small text-muted text-uppercase fw-bold opacity-50 mb-1">SpO2</div>
                                                <div className="fw-extrabold text-gray-900">{safeObj(consultation.vital_signs, 'oxygen_saturation')} <span className="extra-small opacity-50">%</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Right Column: Detailed Records */}
                    <div className="col-lg-9">
                        {['doctor', 'admin'].includes(auth.user.role) ? (
                            <div className="space-y-6">
                                {/* 1. Complaints */}
                                <div className="card border-0 shadow-sm rounded-3xl bg-white shadow-hover">
                                    <div className="card-header bg-white py-4 px-5 d-flex justify-content-between align-items-center border-bottom-0">
                                        <h6 className="mb-0 fw-extrabold text-pink-500 extra-small text-uppercase tracking-widest">Initial Assessment</h6>
                                        {auth.user.role === 'doctor' && consultation.consultation_status === 'open' && (
                                            <Link href={route('consultations.edit', consultation.consultation_id)} className="btn btn-sm btn-outline-pink rounded-pill px-3 fw-bold extra-small">
                                                <i className="fas fa-edit me-1"></i>EDIT RECORD
                                            </Link>
                                        )}
                                    </div>
                                    <div className="card-body p-5 pt-0">
                                        <div className="p-4 bg-light rounded-2xl border-l-4 border-pink-500 mb-5 shadow-inner">
                                            <div className="extra-small fw-extrabold text-pink-500 text-uppercase tracking-widest mb-3 opacity-50">Chief Complaint</div>
                                            <p className="lead fw-extrabold text-gray-900 mb-0 tracking-tight">{consultation.chief_complaint}</p>
                                        </div>
                                        
                                        <div>
                                            <h6 className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-4 border-bottom border-gray-50 pb-2">History of Present Illness</h6>
                                            <p className="text-gray-700 leading-relaxed font-medium">{safeText(consultation.history_present_illness)}</p>
                                        </div>
                                    </div>
                                </div>

                                {/* 2. Histories Grid */}
                                <div className="row g-4">
                                    <div className="col-md-6">
                                        <div className="card border-0 shadow-sm rounded-3xl h-100 bg-white shadow-hover">
                                            <div className="card-body p-5">
                                                <h6 className="extra-small fw-extrabold text-primary text-uppercase tracking-widest mb-5 d-flex align-items-center">
                                                    <div className="avatar-sm bg-primary-subtle text-primary rounded-lg d-flex align-items-center justify-content-center me-3">
                                                        <i className="fas fa-notes-medical"></i>
                                                    </div>
                                                    Clinical Background
                                                </h6>
                                                <div className="mb-4">
                                                    <div className="extra-small text-muted fw-extrabold text-uppercase tracking-widest mb-2 opacity-50">Past Medical</div>
                                                    <p className="small text-gray-800 fw-bold">{safeText(consultation.past_medical_history)}</p>
                                                </div>
                                                <div>
                                                    <div className="extra-small text-muted fw-extrabold text-uppercase tracking-widest mb-2 opacity-50">Surgical History</div>
                                                    <p className="small text-gray-800 fw-bold">{safeText(consultation.surgical_history)}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="col-md-6">
                                        <div className="card border-0 shadow-sm rounded-3xl h-100 bg-white shadow-hover">
                                            <div className="card-body p-5">
                                                <h6 className="extra-small fw-extrabold text-primary text-uppercase tracking-widest mb-5 d-flex align-items-center">
                                                    <div className="avatar-sm bg-primary-subtle text-primary rounded-lg d-flex align-items-center justify-content-center me-3">
                                                        <i className="fas fa-users"></i>
                                                    </div>
                                                    Social & Family
                                                </h6>
                                                <div className="mb-4">
                                                    <div className="extra-small text-muted fw-extrabold text-uppercase tracking-widest mb-2 opacity-50">Family Pedigree</div>
                                                    <p className="small text-gray-800 fw-bold">{safeText(consultation.family_history)}</p>
                                                </div>
                                                <div>
                                                    <div className="extra-small text-muted fw-extrabold text-uppercase tracking-widest mb-2 opacity-50">Social & Environmental</div>
                                                    <p className="small text-gray-800 fw-bold">{safeText(consultation.social_history)}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* 3. Gynaecological & Obstetric */}
                                <div className="card border-0 shadow-sm rounded-3xl bg-white shadow-hover overflow-hidden">
                                    <div className="card-header bg-pink-50 text-pink-700 py-4 px-5 border-0 d-flex align-items-center gap-3">
                                        <i className="fas fa-venus fs-5 opacity-50"></i>
                                        <h6 className="mb-0 fw-extrabold extra-small text-uppercase tracking-widest">Reproductive Health Narrative</h6>
                                    </div>
                                    <div className="card-body p-5">
                                        <div className="row g-5">
                                            <div className="col-md-6 border-end border-gray-100">
                                                <h6 className="extra-small fw-extrabold text-pink-400 text-uppercase tracking-widest mb-4">Gynaecological Profile</h6>
                                                <div className="space-y-3">
                                                    <div className="d-flex justify-content-between">
                                                        <span className="small text-muted">LMP:</span>
                                                        <span className="small fw-extrabold text-gray-900">{safeObj(consultation.menstrual_history, 'last_period_date')}</span>
                                                    </div>
                                                    <div className="d-flex justify-content-between">
                                                        <span className="small text-muted">Cycle:</span>
                                                        <span className="small fw-extrabold text-gray-900">{safeObj(consultation.menstrual_history, 'regularity')}, {safeObj(consultation.menstrual_history, 'flow_duration')} days</span>
                                                    </div>
                                                    <div className="d-flex justify-content-between">
                                                        <span className="small text-muted">Pap Smear:</span>
                                                        <span className="small fw-extrabold text-gray-900">{safeText(consultation.cervical_screening)}</span>
                                                    </div>
                                                    <div className="d-flex justify-content-between">
                                                        <span className="small text-muted">Contraception:</span>
                                                        <span className="small fw-extrabold text-gray-900">{safeText(consultation.contraceptive_history)}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="col-md-6">
                                                <h6 className="extra-small fw-extrabold text-pink-400 text-uppercase tracking-widest mb-4">Obstetric History</h6>
                                                <div className="row mb-4">
                                                    <div className="col-6">
                                                        <div className="extra-small text-muted mb-1">Parity</div>
                                                        <div className="fw-extrabold text-gray-900">{safeText(consultation.parity)}</div>
                                                    </div>
                                                    <div className="col-6">
                                                        <div className="extra-small text-muted mb-1">Current Status</div>
                                                        <div className="fw-extrabold text-gray-900">{safeText(consultation.current_pregnancy)}</div>
                                                    </div>
                                                </div>
                                                
                                                {consultation.past_obstetric?.length > 0 ? (
                                                    <div className="table-responsive rounded-2xl border border-gray-100 overflow-hidden shadow-inner">
                                                        <table className="table table-sm table-hover align-middle mb-0 extra-small">
                                                            <thead className="bg-gray-50">
                                                                <tr>
                                                                    <th className="px-3 py-2 fw-extrabold border-0">YEAR</th>
                                                                    <th className="px-3 py-2 fw-extrabold border-0">MODE</th>
                                                                    <th className="px-3 py-2 fw-extrabold border-0">OUTCOME</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody className="border-0">
                                                                {consultation.past_obstetric.map((rec, i) => (
                                                                    <tr key={i} className="border-bottom border-gray-50">
                                                                        <td className="px-3 py-2 fw-bold">{rec.year}</td>
                                                                        <td className="px-3 py-2 text-muted">{rec.mode_of_delivery}</td>
                                                                        <td className="px-3 py-2 fw-extrabold text-gray-800">{rec.outcome}</td>
                                                                    </tr>
                                                                ))}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                ) : (
                                                    <div className="p-4 bg-gray-50 rounded-2xl text-center">
                                                        <p className="text-muted extra-small fw-bold text-uppercase opacity-50 mb-0">No past pregnancy records found.</p>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* 4. Examination */}
                                <div className="card border-0 shadow-sm rounded-3xl bg-white shadow-hover">
                                    <div className="card-header bg-white py-4 px-5 border-bottom-0">
                                        <h6 className="mb-0 fw-extrabold text-primary extra-small text-uppercase tracking-widest">Physical Examination Findings</h6>
                                    </div>
                                    <div className="card-body p-5 pt-0">
                                        <div className="row g-5">
                                            <div className="col-md-4">
                                                <div className="extra-small text-muted fw-extrabold text-uppercase tracking-widest mb-3 opacity-50">General Systems</div>
                                                <p className="small text-gray-800 fw-bold">{safeText(consultation.general_examination)}</p>
                                            </div>
                                            <div className="col-md-4">
                                                <div className="extra-small text-muted fw-extrabold text-uppercase tracking-widest mb-3 opacity-50">Review of Systems</div>
                                                <p className="small text-gray-800 fw-bold">{safeText(consultation.review_of_systems)}</p>
                                            </div>
                                            <div className="col-md-4">
                                                <div className="extra-small text-muted fw-extrabold text-uppercase tracking-widest mb-3 opacity-50">Targeted Systems</div>
                                                <p className="small text-gray-800 fw-bold">{safeText(consultation.systems_examination)}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* 5. Diagnosis & Plan */}
                                <div className="card border-0 shadow-sm rounded-3xl bg-white shadow-hover border-l-4 border-success overflow-hidden">
                                    <div className="card-header bg-success-subtle py-4 px-5 border-0">
                                        <h6 className="mb-0 fw-extrabold text-success-emphasis extra-small text-uppercase tracking-widest">Diagnosis & Management Protocol</h6>
                                    </div>
                                    <div className="card-body p-5">
                                        <div className="mb-5">
                                            <div className="extra-small fw-extrabold text-success text-uppercase tracking-widest mb-3 opacity-50">Clinical Impression</div>
                                            <h4 className="fw-extrabold text-gray-900 tracking-tightest">{consultation.diagnosis}</h4>
                                        </div>
                                        
                                        <div className="row g-5">
                                            <div className="col-md-6">
                                                <h6 className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-4 border-bottom border-gray-50 pb-2">Treatment Strategy</h6>
                                                <div className="p-4 bg-gray-50 rounded-2xl border border-gray-100 shadow-inner leading-relaxed small font-medium">
                                                    {safeText(consultation.treatment_plan)}
                                                </div>
                                            </div>
                                            <div className="col-md-6">
                                                <h6 className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-4 border-bottom border-gray-50 pb-2">Clinical Instructions</h6>
                                                <div className="space-y-4 px-2">
                                                    <div>
                                                        <div className="extra-small fw-bold text-success mb-1 uppercase tracking-widest">Follow-up</div>
                                                        <p className="small fw-extrabold text-gray-900">{safeText(consultation.follow_up_instructions)}</p>
                                                    </div>
                                                    <div>
                                                        <div className="extra-small fw-bold text-warning mb-1 uppercase tracking-widest">Internal Notes</div>
                                                        <p className="small text-muted font-medium italic">{safeText(consultation.notes)}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* 6. Services & Diagnostics */}
                                <div className="card border-0 shadow-sm rounded-3xl bg-white shadow-hover mt-4">
                                    <div className="card-header bg-white py-4 px-5 border-bottom-0">
                                        <h6 className="mb-0 fw-extrabold text-primary extra-small text-uppercase tracking-widest">
                                            <i className="fas fa-microscope me-2"></i>Investigation & Pharmacy Registry
                                        </h6>
                                    </div>
                                    <div className="card-body p-5 pt-0">
                                        <div className="row g-5">
                                            {/* Laboratory Tests */}
                                            <div className="col-lg-4 border-end border-gray-100">
                                                <h6 className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-4">Laboratory Requests</h6>
                                                {(!consultation.lab_test_requests || consultation.lab_test_requests.length === 0) ? (
                                                    <p className="text-muted extra-small italic fw-bold opacity-50">No laboratory tests ordered.</p>
                                                ) : (
                                                    <div className="d-flex flex-wrap gap-2">
                                                        {consultation.lab_test_requests.map(lab => (
                                                            <button
                                                                type="button"
                                                                key={lab.request_id}
                                                                onClick={() => lab.status === 'completed' ? setSelectedLabRequest(lab) : null}
                                                                className={`badge border px-3 py-2 rounded-pill d-inline-flex align-items-center gap-2 text-truncate transition-all ${lab.status === 'completed' ? 'bg-success-subtle text-success border-success-subtle cursor-pointer hover-lift shadow-sm' : 'bg-warning-subtle text-warning-emphasis border-warning-subtle cursor-default'}`}
                                                                style={{ maxWidth: '100%' }}
                                                                title={lab.status === 'completed' ? 'Click to view results' : 'Results pending'}
                                                            >
                                                                <i className={`fas ${lab.status === 'completed' ? 'fa-check-double' : 'fa-clock'} flex-shrink-0`}></i>
                                                                <span className="text-truncate fw-extrabold extra-small">{lab.test_type?.test_name || 'Lab Test'}</span>
                                                                {lab.status === 'completed' && <i className="fas fa-external-link-alt ms-1 flex-shrink-0" style={{ fontSize: '0.6rem' }}></i>}
                                                            </button>
                                                        ))}
                                                    </div>
                                                )}
                                            </div>

                                            {/* Services & Procedures */}
                                            <div className="col-lg-4 border-end border-gray-100">
                                                <h6 className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-4">Billed Services</h6>
                                                {(!consultation.invoices || consultation.invoices.length === 0) ? (
                                                    <p className="text-muted extra-small italic fw-bold opacity-50">No services billed in this visit.</p>
                                                ) : (
                                                    <div className="space-y-3">
                                                        {consultation.invoices.flatMap(inv => inv.items || []).filter(item => ['service', 'procedure'].includes(item.item_type)).map(item => (
                                                            <div key={item.item_id} className="p-3 rounded-xl bg-gray-50 border border-gray-100">
                                                                <div className="fw-extrabold text-gray-800 small text-truncate">{item.description}</div>
                                                                <div className="extra-small text-muted font-bold uppercase opacity-50 tracking-widest">{item.item_type}</div>
                                                            </div>
                                                        ))}
                                                    </div>
                                                )}
                                            </div>

                                            {/* Prescriptions */}
                                            <div className="col-lg-4">
                                                <h6 className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-4">Pharmacy Registry</h6>
                                                {(!consultation.prescriptions || consultation.prescriptions.length === 0) ? (
                                                    <p className="text-muted extra-small italic fw-bold opacity-50">No medications prescribed.</p>
                                                ) : (
                                                    <div className="space-y-3">
                                                        {consultation.prescriptions.flatMap(rx => (rx.items || []).map(item => ({...item, rxStatus: rx.status}))).map(item => (
                                                            <div key={item.item_id} className="p-3 rounded-xl bg-white border border-gray-100 shadow-sm d-flex justify-content-between align-items-center gap-2">
                                                                <div className="overflow-hidden">
                                                                    <div className="fw-extrabold text-gray-900 small text-truncate">{item.medication?.medication_name || 'MED'} {item.medication?.strength}</div>
                                                                    <div className="extra-small text-muted font-medium text-truncate">{item.dosage} | {item.frequency} | {item.duration}</div>
                                                                </div>
                                                                <span className={`badge flex-shrink-0 extra-small rounded-pill ${item.rxStatus === 'dispensed' ? 'bg-success text-white' : 'bg-warning text-dark'}`}>{item.rxStatus}</span>
                                                            </div>
                                                        ))}
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ) : (
                            <div className="card border-0 shadow-sm rounded-3xl mb-4 bg-white shadow-hover border-l-4 border-info overflow-hidden">
                                <div className="card-body p-5 text-center py-16">
                                    <div className="bg-info-subtle text-info p-5 rounded-circle d-inline-flex align-items-center justify-content-center mb-5 shadow-sm border border-info-subtle" style={{ width: '120px', height: '120px' }}>
                                        <i className="fas fa-user-shield fa-3x"></i>
                                    </div>
                                    <h4 className="fw-extrabold text-gray-900 tracking-tightest mb-3">CLINICAL RECORDS RESTRICTED</h4>
                                    <p className="text-muted fw-medium mx-auto opacity-75" style={{ maxWidth: '500px' }}>
                                        Professional nursing access is limited to patient biodata and vital sign monitoring. Clinical narratives, diagnostic findings, and professional management plans are restricted to attending physicians and administrators.
                                    </p>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Lab Result Modal */}
            <InfoModalComp
                show={!!selectedLabRequest}
                onClose={() => setSelectedLabRequest(null)}
                title={selectedLabRequest?.test_type?.test_name || 'Laboratory Analysis'}
                subtitle={`Certified on ${selectedLabRequest?.completed_at ? new Date(selectedLabRequest.completed_at).toLocaleDateString() : 'Pending verification'}`}
                icon="fa-flask"
                tabs={getLabResultTabs(selectedLabRequest)}
            />

            <UnifiedToolbar 
                actions={[
                    { 
                        label: 'ADD PRESCRIPTION', 
                        icon: 'fa-prescription', 
                        href: route('prescriptions.create', { 
                            patient_id: consultation.patient_id,
                            consultation_id: consultation.consultation_id 
                        })
                    },
                    { 
                        label: 'GENERATE INVOICE', 
                        icon: 'fa-file-invoice-dollar', 
                        href: route('invoices.create', { 
                            patient_id: consultation.patient_id, 
                            consultation_id: consultation.consultation_id 
                        }),
                        color: 'success'
                    },
                    { 
                        label: 'ADD LAB REQUEST', 
                        icon: 'fa-flask', 
                        href: route('lab.create', { 
                            patient_id: consultation.patient_id, 
                            consultation_id: consultation.consultation_id 
                        }),
                        color: 'warning'
                    },
                    { 
                        label: 'BACK TO REGISTRY', 
                        icon: 'fa-layer-group', 
                        href: route('consultations.index'),
                        color: 'gray'
                    }
                ]}
            />
        </AuthenticatedLayout>
    );
}
