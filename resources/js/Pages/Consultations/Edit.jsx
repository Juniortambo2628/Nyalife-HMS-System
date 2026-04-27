import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { toast } from 'react-hot-toast';
import DashboardSelect from '@/Components/DashboardSelect';
import PageHeader from '@/Components/PageHeader';
import FormSection from '@/Components/FormSection';
import FormField from '@/Components/FormField';
import FormSelect from '@/Components/FormSelect';
import QuickPatientModal from '@/Components/QuickPatientModal';
import Modal from '@/Components/Modal';
import ConsultationDraftSwitcher from '@/Components/ConsultationDraftSwitcher';
import InfoModalComp from '@/Components/InfoModal';
import { useState, useEffect, useRef, useCallback } from 'react';

import { toLocalISO } from '@/Utils/dateUtils';

const AUTOSAVE_KEY = 'nyalife_consultation_edit_draft';
const AUTOSAVE_INTERVAL = 15000;

export default function Edit({ 
    consultation,
    patients, 
    doctors, 
    medical_procedures = [], 
    lab_test_types = [], medications = [], 
    procedure_services = [], 
    auth,
    ...props 
}) {
    const consultationDrafts = props.drafts || { data: [] };

    const [isModalOpen, setIsModalOpen] = useState(false);
    
    // Robustly get patient name from loaded relation
    const getPatientName = (cons) => {
        const p = cons.patient;
        if (!p) return "";
        const u = p.user;
        if (!u) return "";
        return `${u.first_name || ''} ${u.last_name || ''}`.trim();
    };

    const [quickPatientLabel, setQuickPatientLabel] = useState(getPatientName(consultation));
    const [showLabConfirmModal, setShowLabConfirmModal] = useState(false);
    const [selectedLabRequest, setSelectedLabRequest] = useState(null);
    const [toast, setToast] = useState(null);
    const [patientGender, setPatientGender] = useState(consultation.patient?.user?.gender?.toLowerCase() || 'unknown');
    const [isPartnerContext, setIsPartnerContext] = useState(false);
    const [skipRepro, setSkipRepro] = useState(false);
    const [autosaveStatus, setAutosaveStatus] = useState('');
    const autosaveTimerRef = useRef(null);

    const { data, setData, put, processing, errors, transform } = useForm({
        patient_id: consultation.patient_id || '',
        patient_label: getPatientName(consultation),
        doctor_id: consultation.doctor_id || '',
        appointment_id: consultation.appointment_id || '',
        consultation_date: consultation.consultation_date ? consultation.consultation_date.slice(0, 16) : toLocalISO(),
        priority: consultation.priority || 'normal',
        is_walk_in: consultation.is_walk_in,
        status: consultation.consultation_status || 'in_progress',
        
        // Vitals
        vital_signs: consultation.vital_signs || {
            blood_pressure: '',
            temperature: '',
            heart_rate: '',
            respiratory_rate: '',
            oxygen_saturation: '',
            weight: '',
            height: '',
            bmi: '',
        },

        chief_complaint: consultation.chief_complaint || '',
        history_present_illness: consultation.history_present_illness || '',
        
        menstrual_history: consultation.menstrual_history || {
            last_period_date: '',
            regularity: 'regular',
            flow_duration: '',
            dysmenorrhea: 'none',
        },
        cervical_screening: consultation.cervical_screening || '',
        contraceptive_history: consultation.contraceptive_history || '',
        sexual_history: consultation.sexual_history || '',
        
        parity: consultation.parity || '',
        current_pregnancy: consultation.current_pregnancy || '',
        past_obstetric: consultation.past_obstetric || [],
        
        past_medical_history: consultation.past_medical_history || '',
        surgical_history: consultation.surgical_history || '',
        
        family_history: consultation.family_history || '',
        social_history: consultation.social_history || '',
        
        review_of_systems: consultation.review_of_systems || '',
        general_examination: consultation.general_examination || '',
        systems_examination: consultation.systems_examination || '',
        
        diagnosis: consultation.diagnosis || '',
        treatment_plan: consultation.treatment_plan || '',
        follow_up_instructions: consultation.follow_up_instructions || '',
        notes: consultation.notes || '',

        // These arrays are for NEW items added during this edit session
        requested_procedures: [],
        requested_labs: [],
        requested_service_items: [],
        requested_prescriptions: [],
    });

    // Existing labs already saved
    const existingLabs = consultation.lab_test_requests || [];

    // Helper for nested state updates
    const setNestedData = (parent, key, value) => {
        setData(parent, {
            ...data[parent],
            [key]: value
        });
    };

    // Repeater helpers
    const addObstetricRecord = () => {
        setData('past_obstetric', [
            ...data.past_obstetric,
            { year: '', place_of_birth: '', duration: '', mode_of_delivery: '', outcome: '', sex: '', weight: '', complications: '' }
        ]);
    };

    const removeObstetricRecord = (index) => {
        const newRecords = [...data.past_obstetric];
        newRecords.splice(index, 1);
        setData('past_obstetric', newRecords);
    };

    const updateObstetricRecord = (index, field, value) => {
        const newRecords = [...data.past_obstetric];
        newRecords[index][field] = value;
        setData('past_obstetric', newRecords);
    };

    // Service adders
    const addProcedure = (procId) => {
        const proc = medical_procedures.find(p => p.procedure_id == procId);
        if (proc && !data.requested_procedures.find(p => p.procedure_id == procId)) {
            setData('requested_procedures', [...data.requested_procedures, { procedure_id: proc.procedure_id, name: proc.name, category: proc.category }]);
        }
    };
    const removeProcedure = (procId) => setData('requested_procedures', data.requested_procedures.filter(p => p.procedure_id != procId));

    const addLab = (labId) => {
        const lab = lab_test_types.find(l => l.test_type_id == labId);
        if (lab && !data.requested_labs.find(l => l.test_type_id == labId)) {
            setData('requested_labs', [...data.requested_labs, { test_type_id: lab.test_type_id, test_name: lab.test_name, category: lab.category }]);
        }
    };
    const removeLab = (labId) => setData('requested_labs', data.requested_labs.filter(l => l.test_type_id != labId));

    const addServiceItem = (svcId) => {
        const svc = procedure_services.find(s => s.test_type_id == svcId);
        if (svc && !data.requested_service_items.find(s => s.test_type_id == svcId)) {
            setData('requested_service_items', [...data.requested_service_items, { test_type_id: svc.test_type_id, test_name: svc.test_name, category: svc.category }]);
        }
    };
    const removeServiceItem = (svcId) => setData('requested_service_items', data.requested_service_items.filter(s => s.test_type_id != svcId));

    // ====== PRESCRIPTIONS ======
    const [prescriptions, setPrescriptions] = useState(consultation.prescriptions || []);
    const [newRx, setNewRx] = useState({ medication_id: '', dosage: '', frequency: '', duration: '', instructions: '' });

    const addPrescription = () => {
        if (!newRx.medication_id) return;
        const med = medications.find(m => m.medication_id == newRx.medication_id);
        if (!med) return;
        const rxItem = { ...newRx, medication_name: med.medication_name, strength: med.strength, unit: med.unit };
        setData('requested_prescriptions', [...(data.requested_prescriptions || []), rxItem]);
        setNewRx({ medication_id: '', dosage: '', frequency: '', duration: '', instructions: '' });
    };
    const removePrescription = (idx) => setData('requested_prescriptions', (data.requested_prescriptions || []).filter((_, i) => i !== idx));


    // Submit
    const submit = (e, targetStatus = data.status, moveLabs = false) => {
        if (e) e.preventDefault();

        if (targetStatus === 'in_progress' && data.requested_service_items.length > 0 && !showLabConfirmModal && !moveLabs) {
            setShowLabConfirmModal(true);
            return;
        }

        transform((data) => ({
            ...data,
            status: targetStatus,
            requested_labs: moveLabs 
                ? [...data.requested_labs, ...data.requested_service_items] 
                : data.requested_labs,
            requested_service_items: moveLabs ? [] : data.requested_service_items
        }));

        put(route('consultations.update', consultation.consultation_id), {
            onSuccess: () => {
                setToast({ message: 'Changes saved successfully!', type: 'success' });
                reset('requested_labs', 'requested_service_items', 'requested_procedures', 'requested_prescriptions');
                
                if (moveLabs) {
                    setTimeout(() => router.visit(route('lab.index')), 1500);
                } else if (targetStatus === 'completed') {
                    setTimeout(() => router.visit(route('dashboard')), 1500);
                }
            },
            onError: (errs) => {
                const errorMsg = Object.values(errs).flat().join(' | ');
                setToast({ message: `Update failed: ${errorMsg}`, type: 'danger' });
            },
            preserveScroll: true,
        });
    };

    const confirmMoveToLabs = () => {
        setShowLabConfirmModal(false);
        submit(null, 'in_progress', true);
    };

    const labsByCategory = lab_test_types.reduce((groups, lab) => {
        const cat = lab.category || 'Other';
        if (!groups[cat]) groups[cat] = [];
        groups[cat].push(lab);
        return groups;
    }, {});

    const servicesByCategory = procedure_services.reduce((groups, svc) => {
        const cat = svc.category || 'Other';
        if (!groups[cat]) groups[cat] = [];
        groups[cat].push(svc);
        return groups;
    }, {});

    // Build tabs for a completed lab request detail modal
    const getLabResultTabs = (lab) => {
        if (!lab) return [];
        const results = lab.results || {};
        const hasTemplate = lab.test_type?.template && Array.isArray(lab.test_type.template) && lab.test_type.template.length > 0;

        return [
            {
                id: 'results',
                label: 'Test Results',
                icon: 'fa-flask',
                content: (
                    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <div className="d-flex align-items-center gap-3 mb-4">
                            <div className="bg-success-subtle text-success p-2 rounded-circle d-flex align-items-center justify-content-center" style={{ width: '48px', height: '48px' }}>
                                <i className="fas fa-check-double"></i>
                            </div>
                            <div>
                                <h5 className="fw-bold mb-0">{lab.test_type?.test_name}</h5>
                                <span className="text-muted small">{lab.test_type?.category || 'Clinical Laboratory'}</span>
                            </div>
                            <span className="badge bg-success rounded-pill ms-auto px-3 py-2">Completed</span>
                        </div>

                        {hasTemplate ? (
                            <div className="table-responsive rounded-3 border overflow-hidden">
                                <table className="table table-hover align-middle mb-0">
                                    <thead className="bg-light">
                                        <tr>
                                            <th className="px-4 py-3 small fw-bold text-muted border-0">PARAMETER</th>
                                            <th className="px-4 py-3 small fw-bold text-muted border-0">RESULT</th>
                                            <th className="px-4 py-3 small fw-bold text-muted border-0">UNIT</th>
                                            <th className="px-4 py-3 small fw-bold text-muted border-0 text-center">REF. RANGE</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {lab.test_type.template.map((item, idx) => (
                                            <tr key={idx} className="border-bottom border-light">
                                                <td className="px-4 py-3 fw-bold text-gray-800">{item.label}</td>
                                                <td className="px-4 py-3 fw-bold" style={{ color: '#e91e63' }}>{results.lab_results?.[item.label] || '\u2014'}</td>
                                                <td className="px-4 py-3 text-muted small">{item.unit || '\u2014'}</td>
                                                <td className="px-4 py-3 text-center small text-muted">{item.normalRange || '\u2014'}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        ) : (
                            <div className="p-4 border rounded-3" style={{ backgroundColor: '#f0f7ff' }}>
                                <h6 className="fw-bold text-muted text-uppercase mb-2" style={{ fontSize: '0.7rem' }}>Findings Narrative</h6>
                                <div style={{ whiteSpace: 'pre-wrap', lineHeight: 1.6 }}>{results.lab_results || 'No quantitative results recorded.'}</div>
                            </div>
                        )}
                    </div>
                )
            },
            {
                id: 'observations',
                label: 'Observations',
                icon: 'fa-eye',
                content: (
                    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest">Clinical Observations</h4>
                        <div className="p-4 rounded-3 border" style={{ backgroundColor: '#f9fafb', fontStyle: 'italic', lineHeight: 1.7 }}>
                            {results.observations || 'No specific clinical observations recorded.'}
                        </div>
                        <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest mt-4">Professional Conclusion</h4>
                        <div className="p-4 rounded-3 border fw-bold" style={{ backgroundColor: '#f0fdf4', borderColor: '#bbf7d0', color: '#166534', lineHeight: 1.7 }}>
                            {results.conclusions || 'No final conclusion recorded.'}
                        </div>
                    </div>
                )
            },
            {
                id: 'attachments',
                label: 'Attachments',
                icon: 'fa-paperclip',
                content: (
                    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest">Attached Files & Images</h4>
                        {results.attachments?.length > 0 ? (
                            <div className="row g-3">
                                {results.attachments.map((file, idx) => (
                                    <div key={idx} className="col-md-4">
                                        <div className="card border-0 shadow-sm rounded-3 overflow-hidden h-100" style={{ cursor: 'pointer' }}>
                                            <div style={{ height: '160px' }} className="position-relative">
                                                {file.type?.startsWith('image/') ? (
                                                    <img src={file.data} alt={file.name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                                                ) : (
                                                    <div className="w-100 h-100 d-flex align-items-center justify-content-center" style={{ backgroundColor: '#f3f4f6' }}>
                                                        <i className={`fas ${file.type?.includes('pdf') ? 'fa-file-pdf text-danger' : 'fa-file-medical text-primary'} fa-3x`} style={{ opacity: 0.5 }}></i>
                                                    </div>
                                                )}
                                            </div>
                                            <div className="p-2 text-center">
                                                <div className="small fw-bold text-truncate">{file.name}</div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-5" style={{ backgroundColor: '#f9fafb', borderRadius: '1.5rem' }}>
                                <i className="fas fa-folder-open text-gray-300 fa-3x mb-3 d-block"></i>
                                <p className="text-muted">No attachments or images were uploaded for this investigation.</p>
                            </div>
                        )}
                    </div>
                )
            },
            {
                id: 'metadata',
                label: 'Request Info',
                icon: 'fa-info-circle',
                content: (
                    <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest">Test Configuration</h4>
                        <div className="p-4 rounded-3 border mb-4" style={{ backgroundColor: '#f0f7ff' }}>
                            <div className="row g-3">
                                <div className="col-md-6">
                                    <div className="small text-muted mb-1">Test Name</div>
                                    <div className="fw-bold">{lab.test_type?.test_name}</div>
                                </div>
                                <div className="col-md-6">
                                    <div className="small text-muted mb-1">Category</div>
                                    <div className="fw-bold">{lab.test_type?.category || 'N/A'}</div>
                                </div>
                                <div className="col-md-6">
                                    <div className="small text-muted mb-1">Sample Type</div>
                                    <div className="fw-bold">{lab.test_type?.sample_type || 'Standard'}</div>
                                </div>
                                <div className="col-md-6">
                                    <div className="small text-muted mb-1">Turn Around Time</div>
                                    <div className="fw-bold">{lab.test_type?.turnaround_time || 'Standard Processing'}</div>
                                </div>
                            </div>
                            {lab.test_type?.template && Array.isArray(lab.test_type.template) && lab.test_type.template.length > 0 && (
                                <div className="mt-3 pt-3 border-top">
                                    <div className="small text-muted mb-2 fw-bold">Parameters & Reference Ranges</div>
                                    <div className="table-responsive">
                                        <table className="table table-sm mb-0" style={{ fontSize: '0.8rem' }}>
                                            <thead><tr><th className="border-0 text-muted">Parameter</th><th className="border-0 text-muted">Unit</th><th className="border-0 text-muted">Normal Range</th></tr></thead>
                                            <tbody>
                                                {lab.test_type.template.map((t, i) => (
                                                    <tr key={i}><td className="fw-bold">{t.label}</td><td>{t.unit || '\u2014'}</td><td>{t.normalRange || '\u2014'}</td></tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            )}
                        </div>

                        <h4 className="text-gray-400 text-xs font-bold uppercase tracking-widest">Processing Details</h4>
                        <div className="space-y-3">
                            <div className="d-flex justify-content-between border-bottom pb-2" style={{ borderColor: '#f3f4f6' }}>
                                <span className="text-muted">Request ID</span>
                                <span className="fw-bold font-monospace">LAB-{lab.request_id}</span>
                            </div>
                            <div className="d-flex justify-content-between border-bottom pb-2" style={{ borderColor: '#f3f4f6' }}>
                                <span className="text-muted">Priority</span>
                                <span className={`fw-bold ${lab.priority === 'urgent' ? 'text-danger' : ''}`}>{(lab.priority || 'normal').toUpperCase()}</span>
                            </div>
                            <div className="d-flex justify-content-between border-bottom pb-2" style={{ borderColor: '#f3f4f6' }}>
                                <span className="text-muted">Status</span>
                                <span className="badge bg-success rounded-pill px-3 py-1">{(lab.status || 'completed').toUpperCase()}</span>
                            </div>
                            <div className="d-flex justify-content-between border-bottom pb-2" style={{ borderColor: '#f3f4f6' }}>
                                <span className="text-muted">Requested Date</span>
                                <span className="fw-bold">{lab.created_at ? new Date(lab.created_at).toLocaleString() : 'N/A'}</span>
                            </div>
                            <div className="d-flex justify-content-between border-bottom pb-2" style={{ borderColor: '#f3f4f6' }}>
                                <span className="text-muted">Completed Date</span>
                                <span className="fw-bold">{lab.completed_at ? new Date(lab.completed_at).toLocaleString() : 'N/A'}</span>
                            </div>
                            <div className="d-flex justify-content-between border-bottom pb-2" style={{ borderColor: '#f3f4f6' }}>
                                <span className="text-muted">Performed By</span>
                                <span className="fw-bold">
                                    {lab.assignedToUser ? (
                                        <span className="d-inline-flex align-items-center gap-2">
                                            <span className="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center" style={{ width: '28px', height: '28px', fontSize: '0.7rem', fontWeight: 700 }}>
                                                {lab.assignedToUser.first_name?.charAt(0)}{lab.assignedToUser.last_name?.charAt(0)}
                                            </span>
                                            {lab.assignedToUser.first_name} {lab.assignedToUser.last_name}
                                        </span>
                                    ) : 'Unassigned'}
                                </span>
                            </div>
                            {lab.notes && (
                                <div className="mt-3 pt-3 border-top">
                                    <div className="small text-muted mb-1 fw-bold">Lab Notes</div>
                                    <div className="p-3 rounded-2 bg-light" style={{ whiteSpace: 'pre-wrap' }}>{lab.notes}</div>
                                </div>
                            )}
                        </div>
                    </div>
                )
            },
        ];
    };

    return (
        <AuthenticatedLayout user={auth.user} header={`Edit Consultation #${consultation.consultation_id}`}>
            <Head title="Edit Consultation" />

            <PageHeader 
                title={`Edit Consultation: ${data.patient_label}`}
                breadcrumbs={[
                    { label: 'Dashboard', url: route('dashboard') },
                    { label: 'Consultations', url: route('consultations.index') },
                    { label: 'Edit Record', active: true }
                ]}
            />

            <form onSubmit={e => submit(e, 'completed')} className="row g-4 pb-5">
                {/* Same structure as Create.jsx */}
                <FormSection title="Patient Biodata & Vitals" icon="fas fa-user-injured" headerClassName="bg-gradient-primary-to-secondary text-white p-4">
                    <div className="row g-3 mb-4">
                        <FormField label="Patient" required className="col-md-4">
                            <input type="text" className="form-control form-control-lg bg-light border-0" value={data.patient_label} disabled />
                        </FormField>
                        <FormField label="Attending Doctor" required className="col-md-4">
                            <FormSelect 
                                className="form-select form-select-lg bg-light border-0"
                                value={data.doctor_id}
                                onChange={e => setData('doctor_id', e.target.value)}
                                options={doctors}
                            />
                        </FormField>
                        <FormField label="Date & Time" required className="col-md-4">
                            <input type="datetime-local" className="form-control form-control-lg bg-light border-0" value={data.consultation_date} onChange={e => setData('consultation_date', e.target.value)} />
                        </FormField>
                    </div>

                    <div className="row g-3 mb-4">
                        <FormField label="Priority Level" className="col-md-4">
                            <div className="d-flex gap-2">
                                <button type="button" className={`btn rounded-pill px-4 flex-fill fw-bold ${data.priority === 'normal' ? 'btn-primary' : 'btn-light'}`} onClick={() => setData('priority', 'normal')}>Normal</button>
                                <button type="button" className={`btn rounded-pill px-4 flex-fill fw-bold ${data.priority === 'emergency' ? 'btn-danger' : 'btn-light'}`} onClick={() => setData('priority', 'emergency')}>Emergency</button>
                            </div>
                        </FormField>
                    </div>

                    <h6 className="text-primary fw-bold mb-3 border-bottom pb-2">Vital Signs</h6>
                    <div className="row g-3">
                        <FormField label="BP (mmHg)" className="col-md-3"><input type="text" className="form-control" value={data.vital_signs.blood_pressure} onChange={e => setNestedData('vital_signs', 'blood_pressure', e.target.value)} /></FormField>
                        <FormField label="Heart Rate (bpm)" className="col-md-3"><input type="number" className="form-control" value={data.vital_signs.heart_rate} onChange={e => setNestedData('vital_signs', 'heart_rate', e.target.value)} /></FormField>
                        <FormField label="Temp (°C)" className="col-md-3"><input type="number" step="0.1" className="form-control" value={data.vital_signs.temperature} onChange={e => setNestedData('vital_signs', 'temperature', e.target.value)} /></FormField>
                        <FormField label="SpO2 (%)" className="col-md-3"><input type="number" className="form-control" value={data.vital_signs.oxygen_saturation} onChange={e => setNestedData('vital_signs', 'oxygen_saturation', e.target.value)} /></FormField>
                        <FormField label="Weight (kg)" className="col-md-3"><input type="number" step="0.1" className="form-control" value={data.vital_signs.weight} onChange={e => setNestedData('vital_signs', 'weight', e.target.value)} /></FormField>
                        <FormField label="Height (cm)" className="col-md-3"><input type="number" className="form-control" value={data.vital_signs.height} onChange={e => setNestedData('vital_signs', 'height', e.target.value)} /></FormField>
                    </div>
                </FormSection>

                {/* Complaints & History */}
                <div className="col-lg-6">
                    <FormSection title="Chief Complaints & HPI" className="h-100">
                        <FormField label="Chief Complaints" className="mb-3">
                            <textarea className="form-control bg-light border-0" rows="3" value={data.chief_complaint} onChange={e => setData('chief_complaint', e.target.value)} />
                        </FormField>
                        <FormField label="History of Present Illness">
                            <textarea className="form-control bg-light border-0" rows="5" value={data.history_present_illness} onChange={e => setData('history_present_illness', e.target.value)} />
                        </FormField>
                    </FormSection>
                </div>

                <div className="col-lg-6">
                    <FormSection title="Medical & Surgical History" className="h-100">
                        <FormField label="Past Medical History" className="mb-3"><textarea className="form-control" rows="2" value={data.past_medical_history} onChange={e => setData('past_medical_history', e.target.value)} /></FormField>
                        <FormField label="Surgical History" className="mb-3"><textarea className="form-control" rows="2" value={data.surgical_history} onChange={e => setData('surgical_history', e.target.value)} /></FormField>
                        <div className="row">
                            <FormField label="Family History" className="col-md-6"><textarea className="form-control" rows="2" value={data.family_history} onChange={e => setData('family_history', e.target.value)} /></FormField>
                            <FormField label="Social History" className="col-md-6"><textarea className="form-control" rows="2" value={data.social_history} onChange={e => setData('social_history', e.target.value)} /></FormField>
                        </div>
                    </FormSection>
                </div>

                {/* Gynae/Obs Sections */}
                {(patientGender === 'female' || isPartnerContext) && !skipRepro && (
                    <>
                    <div className="col-12">
                        <FormSection title="Gynaecological History" icon="fas fa-venus" headerClassName="bg-pink-50 text-pink-700 p-3">
                            <div className="row g-4">
                                <div className="col-lg-6 border-end">
                                    <h6 className="text-secondary small fw-bold text-uppercase mb-3">Menstrual History</h6>
                                    <div className="row g-3">
                                        <FormField label="LMP Date" className="col-md-6"><input type="date" className="form-control" value={data.menstrual_history.last_period_date} onChange={e => setNestedData('menstrual_history', 'last_period_date', e.target.value)} /></FormField>
                                        <FormField label="Regularity" className="col-md-6"><FormSelect value={data.menstrual_history.regularity} onChange={e => setNestedData('menstrual_history', 'regularity', e.target.value)} options={[{value:'regular', label:'Regular'}, {value:'irregular', label:'Irregular'}]} /></FormField>
                                    </div>
                                </div>
                                <div className="col-lg-6">
                                    <FormField label="Cervical Screening"><textarea className="form-control" rows="2" value={data.cervical_screening} onChange={e => setData('cervical_screening', e.target.value)} /></FormField>
                                </div>
                            </div>
                        </FormSection>
                    </div>
                    <div className="col-12">
                        <FormSection title="Obstetric History" icon="fas fa-baby-carriage">
                            <div className="row g-3 mb-4">
                                <FormField label="Parity" className="col-md-4"><input type="number" className="form-control" value={data.parity} onChange={e => setData('parity', e.target.value)} /></FormField>
                                <FormField label="Current Pregnancy Notes" className="col-md-8"><input type="text" className="form-control" value={data.current_pregnancy} onChange={e => setData('current_pregnancy', e.target.value)} /></FormField>
                            </div>
                        </FormSection>
                    </div>
                    </>
                )}

                {/* Examination */}
                <div className="col-12">
                    <FormSection title="Examination & Review of Systems">
                        <div className="row g-4">
                            <FormField label="General Examination" className="col-md-6"><textarea className="form-control" rows="3" value={data.general_examination} onChange={e => setData('general_examination', e.target.value)} /></FormField>
                            <FormField label="Review of Systems" className="col-md-6"><textarea className="form-control" rows="3" value={data.review_of_systems} onChange={e => setData('review_of_systems', e.target.value)} /></FormField>
                            <FormField label="Specific Systems Examination" className="col-12"><textarea className="form-control" rows="3" value={data.systems_examination} onChange={e => setData('systems_examination', e.target.value)} /></FormField>
                        </div>
                    </FormSection>
                </div>

                {/* Service Requests */}
                <div className="col-12">
                    <FormSection title="Additional Services & Diagnostics" icon="fas fa-microscope" headerClassName="bg-blue-50 text-blue-700 p-3">
                         <div className="row g-4">
                            <div className="col-lg-4 border-end">
                                <h6 className="fw-bold mb-3 text-secondary small text-uppercase">Laboratory Tests</h6>

                                {existingLabs.length > 0 && (
                                     <div className="mb-4">
                                         <h6 className="text-xs fw-bold text-primary text-uppercase mb-2">Already Requested</h6>
                                         <div className="d-flex flex-wrap gap-2 mb-3">
                                             {existingLabs.map(lab => (
                                                 <div key={lab.request_id} className="position-relative d-inline-block me-2 mb-2">
                                                     <button
                                                         type="button"
                                                         onClick={() => lab.status === 'completed' ? setSelectedLabRequest(lab) : null}
                                                         className={`badge border px-3 py-2 rounded-pill d-inline-flex align-items-center gap-1 ${lab.status === 'completed' ? 'bg-success-subtle text-success border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border-warning-subtle'}`}
                                                         style={lab.status === 'completed' ? { cursor: 'pointer', transition: 'all 0.2s' } : {}}
                                                         title={lab.status === 'completed' ? 'Click to view results' : 'Results pending'}
                                                     >
                                                         <i className={`fas ${lab.status === 'completed' ? 'fa-check-double' : 'fa-clock'} me-1`}></i>
                                                         {lab.test_type?.test_name || 'Lab Test'}
                                                         {lab.status === 'completed' && <i className="fas fa-external-link-alt ms-1" style={{ fontSize: '0.6rem' }}></i>}
                                                     </button>
                                                     {lab.status === 'pending' && (
                                                         <button 
                                                             type="button"
                                                             onClick={(e) => {
                                                                 e.stopPropagation();
                                                                 if (confirm('Are you sure you want to remove this pending lab request?')) {
                                                                     router.delete(route('lab.requests.destroy', lab.request_id), {
                                                                         preserveScroll: true,
                                                                         onSuccess: () => {
                                                                             toast.success('Lab request removed');
                                                                         }
                                                                     });
                                                                 }
                                                             }}
                                                             className="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger border border-white p-1"
                                                             style={{ width: '18px', height: '18px', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 10, cursor: 'pointer' }}
                                                             title="Remove pending request"
                                                         >
                                                             <i className="fas fa-times text-white" style={{ fontSize: '0.5rem' }}></i>
                                                         </button>
                                                     )}
                                                 </div>
                                             ))}
                                         </div>
                                     </div>
                                )}

                                <DashboardSelect 
                                    options={lab_test_types.map(l => ({ value: l.test_type_id, label: l.test_name, sublabel: l.category }))}
                                    placeholder="Add more labs..."
                                    onChange={val => val && addLab(val)}
                                />
                                {data.requested_labs.map(l => (
                                    <div key={l.test_type_id} className="d-flex justify-content-between p-2 bg-light mb-1 rounded small">
                                        {l.test_name}
                                        <button type="button" onClick={() => removeLab(l.test_type_id)} className="btn btn-sm text-danger p-0 px-1"><i className="fas fa-times"></i></button>
                                    </div>
                                ))}
                            </div>
                            {/* Services & Procedures Column */}
                            <div className="col-lg-4 border-end">
                                <h6 className="fw-bold mb-3 text-secondary small text-uppercase">Services & Procedures</h6>
                                <select
                                    className="form-select bg-light border-0 mb-3"
                                    onChange={(e) => { addServiceItem(e.target.value); e.target.value = ""; }}
                                    defaultValue=""
                                >
                                    <option value="" disabled>Add a Service/Procedure...</option>
                                    {Object.entries(servicesByCategory).map(([cat, svcs]) => (
                                        <optgroup key={cat} label={cat}>
                                            {svcs.map(s => (
                                                <option key={s.test_type_id} value={s.test_type_id}>{s.test_name}</option>
                                            ))}
                                        </optgroup>
                                    ))}
                                </select>
                                <ul className="list-group list-group-flush mb-0">
                                    {data.requested_service_items.map(s => (
                                        <li key={s.test_type_id} className="list-group-item d-flex justify-content-between align-items-center bg-light mb-2 rounded border-0 py-2">
                                            <div>
                                                <span className="fw-bold text-gray-800 d-block small">{s.test_name}</span>
                                                <span className="text-secondary" style={{ fontSize: '.7rem' }}>{s.category}</span>
                                            </div>
                                            <button type="button" onClick={() => removeServiceItem(s.test_type_id)} className="btn btn-sm btn-light text-danger"><i className="fas fa-times"></i></button>
                                        </li>
                                    ))}
                                    {data.requested_service_items.length === 0 && <li className="list-group-item border-0 bg-transparent text-muted small px-0"><em>No services selected.</em></li>}
                                </ul>
                                
                                <h6 className="fw-bold mb-3 mt-4 text-secondary small text-uppercase border-top pt-3">Surgeries</h6>
                                <select
                                    className="form-select bg-light border-0 mb-3"
                                    onChange={(e) => { addProcedure(e.target.value); e.target.value = ""; }}
                                    defaultValue=""
                                >
                                    <option value="" disabled>Add a Surgery...</option>
                                    {medical_procedures.map(p => (
                                        <option key={p.procedure_id} value={p.procedure_id}>{p.name}</option>
                                    ))}
                                </select>
                                <ul className="list-group list-group-flush mb-0">
                                    {data.requested_procedures.map(p => (
                                        <li key={p.procedure_id} className="list-group-item d-flex justify-content-between align-items-center bg-light mb-2 rounded border-0 py-2">
                                            <div>
                                                <span className="fw-bold text-gray-800 d-block small">{p.name}</span>
                                                <span className="text-secondary" style={{ fontSize: '.7rem' }}>{p.category}</span>
                                            </div>
                                            <button type="button" onClick={() => removeProcedure(p.procedure_id)} className="btn btn-sm btn-light text-danger"><i className="fas fa-times"></i></button>
                                        </li>
                                    ))}
                                    {data.requested_procedures.length === 0 && <li className="list-group-item border-0 bg-transparent text-muted small px-0"><em>No surgeries selected.</em></li>}
                                </ul>
                            </div>

                            {/* Prescriptions Column */}
                            <div className="col-lg-4">
                                <h6 className="fw-bold mb-3 text-secondary small text-uppercase">Prescriptions</h6>
                                <div className="mb-3">
                                    <DashboardSelect
                                        options={medications.map(m => ({ value: m.medication_id, label: `${m.medication_name} ${m.strength || ''}`, sublabel: m.medication_type }))}
                                        placeholder="Search medications..."
                                        onChange={val => val && setNewRx(prev => ({ ...prev, medication_id: val }))}
                                    />
                                    {newRx.medication_id && (
                                        <div className="card border-0 bg-light rounded-3 p-3 mt-2">
                                            <div className="row g-2 mb-2">
                                                <div className="col-6">
                                                    <input type="text" className="form-control form-control-sm" placeholder="Dosage" value={newRx.dosage} onChange={e => setNewRx(p => ({...p, dosage: e.target.value}))} />
                                                </div>
                                                <div className="col-6">
                                                    <select className="form-select form-select-sm" value={newRx.frequency} onChange={e => setNewRx(p => ({...p, frequency: e.target.value}))}>
                                                        <option value="">Frequency</option>
                                                        <option value="OD">Once Daily (OD)</option>
                                                        <option value="BD">Twice Daily (BD)</option>
                                                        <option value="TDS">Three times (TDS)</option>
                                                        <option value="QDS">Four times (QDS)</option>
                                                        <option value="PRN">As Needed (PRN)</option>
                                                        <option value="STAT">Immediately (STAT)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div className="row g-2 mb-2">
                                                <div className="col-6">
                                                    <input type="text" className="form-control form-control-sm" placeholder="Duration (e.g. 5 days)" value={newRx.duration} onChange={e => setNewRx(p => ({...p, duration: e.target.value}))} />
                                                </div>
                                                <div className="col-6">
                                                    <input type="text" className="form-control form-control-sm" placeholder="Instructions" value={newRx.instructions} onChange={e => setNewRx(p => ({...p, instructions: e.target.value}))} />
                                                </div>
                                            </div>
                                            <button type="button" onClick={addPrescription} className="btn btn-sm btn-primary w-100 rounded-pill">
                                                <i className="fas fa-plus me-1"></i> Add to Prescription
                                            </button>
                                        </div>
                                    )}
                                </div>
                                <ul className="list-group list-group-flush mb-0">
                                    {(data.requested_prescriptions || []).map((rx, idx) => (
                                        <li key={idx} className="list-group-item d-flex justify-content-between align-items-center bg-light mb-2 rounded border-0 py-2">
                                            <div>
                                                <span className="fw-bold text-gray-800 d-block small">{rx.medication_name}</span>
                                                <span className="text-secondary" style={{ fontSize: '.7rem' }}>{rx.dosage} | {rx.frequency} | {rx.duration}</span>
                                            </div>
                                            <button type="button" onClick={() => removePrescription(idx)} className="btn btn-sm btn-light text-danger"><i className="fas fa-times"></i></button>
                                        </li>
                                    ))}
                                    {(!data.requested_prescriptions || data.requested_prescriptions.length === 0) && <li className="list-group-item border-0 bg-transparent text-muted small px-0"><em>No prescriptions added.</em></li>}
                                </ul>
                            </div>
                         </div>
                    </FormSection>
                </div>

                {/* Impression & Plan */}
                <div className="col-12">
                    <FormSection title="Impression & Management Plan" className="border-start border-5 border-success" headerClassName="bg-success-subtle text-success-emphasis p-3">
                        <FormField label="Impression / Diagnosis" error={errors.diagnosis} className="mb-3"><textarea className="form-control form-control-lg bg-light" rows="2" value={data.diagnosis} onChange={e => setData('diagnosis', e.target.value)} /></FormField>
                        <FormField label="Treatment Plan" className="mb-3"><textarea className="form-control" rows="4" value={data.treatment_plan} onChange={e => setData('treatment_plan', e.target.value)} /></FormField>
                        <div className="row">
                            <FormField label="Follow-up Instructions" className="col-md-6"><input type="text" className="form-control" value={data.follow_up_instructions} onChange={e => setData('follow_up_instructions', e.target.value)} /></FormField>
                            <FormField label="Internal Notes" className="col-md-6"><input type="text" className="form-control" value={data.notes} onChange={e => setData('notes', e.target.value)} /></FormField>
                        </div>
                    </FormSection>
                </div>

                {/* Actions */}
                <div className="col-12 text-end">
                    <div className="d-flex justify-content-between align-items-center">
                        <Link href={route('consultations.index')} className="btn btn-link text-muted"><i className="fas fa-arrow-left me-2"></i>Back to List</Link>
                        <div className="d-flex gap-3">
                            <button type="button" onClick={e => submit(e, 'in_progress')} disabled={processing} className="btn btn-outline-primary px-4 py-2 rounded-pill fw-bold">
                                {processing ? <span className="spinner-border spinner-border-sm me-2"></span> : <i className="fas fa-save me-2"></i>}
                                Save Changes
                            </button>
                            <button type="submit" disabled={processing} className="btn btn-success px-5 py-2 rounded-pill fw-bold shadow-sm">
                                {processing ? <span className="spinner-border spinner-border-sm me-2"></span> : <i className="fas fa-check-circle me-2"></i>}
                                Conclude Consultation
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            {/* Toast / Modals */}
            {toast && (
                <div className={`position-fixed top-0 end-0 m-4 z-3 alert alert-${toast.type} shadow-lg animate-fade-in`}>
                    {toast.message}
                    <button onClick={() => setToast(null)} className="btn-close ms-3"></button>
                </div>
            )}

            <Modal show={showLabConfirmModal} onClose={() => setShowLabConfirmModal(false)}>
                <div className="p-4">
                    <h5 className="fw-bold mb-3"><i className="fas fa-question-circle text-primary me-2"></i>Move Services to Labs?</h5>
                    <p>You have items in your Services list. Should these be officially requested as lab tests and billed to the patient now?</p>
                    <div className="d-flex justify-content-end gap-2">
                        <button className="btn btn-light rounded-pill px-4" onClick={() => setShowLabConfirmModal(false)}>Cancel</button>
                        <button className="btn btn-primary rounded-pill px-4" onClick={confirmMoveToLabs}>Yes, Move & Save</button>
                    </div>
                </div>
            </Modal>

            {/* Lab Result Detail Modal */}
            {selectedLabRequest && (
                <InfoModalComp
                    show={!!selectedLabRequest}
                    onClose={() => setSelectedLabRequest(null)}
                    title={selectedLabRequest.test_type?.test_name || 'Lab Result'}
                    subtitle="Laboratory Results"
                    tabs={getLabResultTabs(selectedLabRequest)}
                />
            )}

            <ConsultationDraftSwitcher drafts={consultationDrafts.data || []} />
        </AuthenticatedLayout>
    );
}
