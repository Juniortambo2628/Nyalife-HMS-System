import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import DashboardSelect from '@/Components/DashboardSelect';
import PageHeader from '@/Components/PageHeader';
import FormSection from '@/Components/FormSection';
import FormField from '@/Components/FormField';
import FormSelect from '@/Components/FormSelect';
import QuickPatientModal from '@/Components/QuickPatientModal';
import { useState, useEffect, useRef, useCallback } from 'react';

import { toLocalISO } from '@/Utils/dateUtils';

const AUTOSAVE_KEY = 'nyalife_consultation_draft';
const AUTOSAVE_INTERVAL = 15000; // 15 seconds

export default function Create({ patients, doctors, medical_procedures = [], lab_test_types = [], procedure_services = [], appointment_id, preselected_patient_id, preselected_patient_label, preselected_patient_gender, priority = 'normal', auth }) {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [quickPatientLabel, setQuickPatientLabel] = useState(preselected_patient_label || "");
    const [showLabConfirmModal, setShowLabConfirmModal] = useState(false);
    const [toast, setToast] = useState(null);
    const [patientGender, setPatientGender] = useState(preselected_patient_gender || 'unknown');
    const [isPartnerContext, setIsPartnerContext] = useState(false);
    const [skipRepro, setSkipRepro] = useState(false);
    const [autosaveStatus, setAutosaveStatus] = useState('');
    const autosaveTimerRef = useRef(null);
    
    // Try to restore draft from localStorage
    const loadDraft = () => {
        try {
            const saved = localStorage.getItem(AUTOSAVE_KEY);
            if (saved) {
                const draft = JSON.parse(saved);
                // Only restore if it's for the same appointment/patient context
                if (draft.appointment_id == (appointment_id || '') && draft.patient_id == (preselected_patient_id || '')) {
                    return draft;
                }
            }
        } catch (e) { /* ignore corrupt data */ }
        return null;
    };

    const draft = loadDraft();

    const { data, setData, post, processing, errors, reset } = useForm({
        patient_id: draft?.patient_id || preselected_patient_id || '',
        doctor_id: draft?.doctor_id || (auth.user.role === 'doctor' && auth.user.staff ? auth.user.staff.staff_id : ''),
        appointment_id: appointment_id || '',
        consultation_date: draft?.consultation_date || toLocalISO(),
        priority: draft?.priority || priority || 'normal',
        is_walk_in: !appointment_id,
        status: 'pending',
        
        // Vitals
        vital_signs: draft?.vital_signs || {
            blood_pressure: '',
            temperature: '',
            heart_rate: '',
            respiratory_rate: '',
            oxygen_saturation: '',
            weight: '',
            height: '',
            bmi: '',
        },

        // Chief Complaint
        chief_complaint: draft?.chief_complaint || '',
        history_present_illness: draft?.history_present_illness || '',
        
        // Gynaecological History
        menstrual_history: draft?.menstrual_history || {
            last_period_date: '',
            regularity: 'regular',
            flow_duration: '',
            dysmenorrhea: 'none',
        },
        cervical_screening: draft?.cervical_screening || '',
        contraceptive_history: draft?.contraceptive_history || '',
        sexual_history: draft?.sexual_history || '',
        
        // Obstetric History
        parity: draft?.parity || '',
        current_pregnancy: draft?.current_pregnancy || '',
        past_obstetric: draft?.past_obstetric || [],
        
        // Medical & Surgical
        past_medical_history: draft?.past_medical_history || '',
        surgical_history: draft?.surgical_history || '',
        
        // Family & Social
        family_history: draft?.family_history || '',
        social_history: draft?.social_history || '',
        
        // System Review & Examination
        review_of_systems: draft?.review_of_systems || '',
        general_examination: draft?.general_examination || '',
        systems_examination: draft?.systems_examination || '',
        
        // Assessment & Plan
        diagnosis: draft?.diagnosis || '',
        treatment_plan: draft?.treatment_plan || '',
        follow_up_instructions: draft?.follow_up_instructions || '',
        notes: draft?.notes || '',

        // Clinical Services & Billing
        requested_procedures: draft?.requested_procedures || [],
        requested_labs: draft?.requested_labs || [],
        requested_service_items: draft?.requested_service_items || [],
    });

    // ====== AUTOSAVE ======
    const saveDraft = useCallback(() => {
        try {
            localStorage.setItem(AUTOSAVE_KEY, JSON.stringify(data));
            setAutosaveStatus('Draft saved');
            setTimeout(() => setAutosaveStatus(''), 3000);
        } catch (e) { /* storage full */ }
    }, [data]);

    useEffect(() => {
        autosaveTimerRef.current = setInterval(saveDraft, AUTOSAVE_INTERVAL);
        return () => clearInterval(autosaveTimerRef.current);
    }, [saveDraft]);

    // Save on page unload
    useEffect(() => {
        const handleUnload = () => saveDraft();
        window.addEventListener('beforeunload', handleUnload);
        return () => window.removeEventListener('beforeunload', handleUnload);
    }, [saveDraft]);

    const clearDraft = () => {
        localStorage.removeItem(AUTOSAVE_KEY);
    };

    // Helper for nested state updates
    const setNestedData = (parent, key, value) => {
        setData(parent, {
            ...data[parent],
            [key]: value
        });
    };

    // Obstetric History repeater
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

    // ====== PROCEDURES (MedicalProcedure model) ======
    const addProcedure = (procId) => {
        if (!procId) return;
        const proc = medical_procedures.find(p => p.procedure_id == procId);
        if (proc && !data.requested_procedures.find(p => p.procedure_id == procId)) {
            setData('requested_procedures', [...data.requested_procedures, { procedure_id: proc.procedure_id, name: proc.name, category: proc.category }]);
        }
    };

    const removeProcedure = (procId) => {
        setData('requested_procedures', data.requested_procedures.filter(p => p.procedure_id != procId));
    };

    // ====== LAB TESTS (LabTestType model - lab categories only) ======
    const addLab = (labId) => {
        if (!labId) return;
        const lab = lab_test_types.find(l => l.test_type_id == labId);
        if (lab && !data.requested_labs.find(l => l.test_type_id == labId)) {
            setData('requested_labs', [...data.requested_labs, { test_type_id: lab.test_type_id, test_name: lab.test_name, category: lab.category }]);
        }
    };

    const removeLab = (labId) => {
        setData('requested_labs', data.requested_labs.filter(l => l.test_type_id != labId));
    };

    // ====== SERVICE ITEMS (LabTestType model - procedure/service categories) ======
    const addServiceItem = (svcId) => {
        if (!svcId) return;
        const svc = procedure_services.find(s => s.test_type_id == svcId);
        if (svc && !data.requested_service_items.find(s => s.test_type_id == svcId)) {
            setData('requested_service_items', [...data.requested_service_items, { test_type_id: svc.test_type_id, test_name: svc.test_name, category: svc.category }]);
        }
    };

    const removeServiceItem = (svcId) => {
        setData('requested_service_items', data.requested_service_items.filter(s => s.test_type_id != svcId));
    };

    // ====== SUBMIT ======
    const submit = (e, targetStatus = 'completed') => {
        if (e) e.preventDefault();

        // 1. If requesting labs and there are items in "Services" that might be labs
        if (targetStatus === 'in_progress' && data.requested_service_items.length > 0 && !showLabConfirmModal) {
            setShowLabConfirmModal(true);
            return;
        }

        // Set the status first, then post with a callback
        data.status = targetStatus;
        
        post(route('consultations.store'), {
            onSuccess: () => {
                clearDraft();
                if (targetStatus === 'in_progress') {
                    setToast({ message: "Progress saved and Lab Request filed!", type: "success" });
                    setTimeout(() => {
                        router.visit(route('lab.index'));
                    }, 1500);
                } else {
                    router.visit(route('consultations.index'));
                }
            },
            preserveScroll: true,
        });
    };

    const confirmMoveToLabs = () => {
        // Move all items from requested_service_items to requested_labs
        const newLabs = [...data.requested_labs, ...data.requested_service_items];
        setData(d => ({
            ...d,
            requested_labs: newLabs,
            requested_service_items: []
        }));
        setShowLabConfirmModal(false);
        
        // Wait for state to settle then submit
        setTimeout(() => {
            submit(null, 'in_progress');
        }, 100);
    };

    const skipMoveToLabs = () => {
        setShowLabConfirmModal(false);
        setTimeout(() => {
            submit(null, 'in_progress');
        }, 100);
    };

    // Group lab tests by category for better dropdown UX
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

    return (
        <AuthenticatedLayout
            user={auth.user}
            header="New Consultation"
        >
            <Head title="New Consultation" />

            <PageHeader 
                title="Record Consultation"
                breadcrumbs={[
                    { label: 'Dashboard', url: route('dashboard') },
                    { label: 'Consultations', url: route('consultations.index') },
                    { label: 'New Record', active: true }
                ]}
            />

            {/* Autosave indicator */}
            {autosaveStatus && (
                <div className="position-fixed bottom-0 end-0 m-3 z-3">
                    <div className="badge bg-success-subtle text-success px-3 py-2 rounded-pill shadow-sm">
                        <i className="fas fa-check-circle me-1"></i>{autosaveStatus}
                    </div>
                </div>
            )}

            {draft && (
                <div className="alert alert-info border-0 shadow-sm rounded-3 d-flex align-items-center mb-4">
                    <i className="fas fa-history fa-lg me-3 text-info"></i>
                    <div className="flex-grow-1">
                        <strong>Draft restored.</strong> Your previous unsaved consultation data has been recovered.
                    </div>
                    <button type="button" className="btn btn-sm btn-outline-info ms-3" onClick={() => { clearDraft(); window.location.reload(); }}>
                        <i className="fas fa-trash me-1"></i>Discard Draft
                    </button>
                </div>
            )}

            <form onSubmit={e => submit(e, 'completed')} className="row g-4 pb-5">
                {/* 1. Patient Biodata & Vitals */}
                <FormSection 
                    title="Patient Biodata & Vitals" 
                    icon="fas fa-user-injured"
                    headerClassName="bg-gradient-primary-to-secondary text-white p-4"
                >
                    <div className="row g-3 mb-4">
                        <FormField label="Patient" required error={errors.patient_id} className="col-md-4">
                            <DashboardSelect 
                                asyncUrl="/patients/search"
                                value={data.patient_id}
                                onChange={(val, opt) => {
                                    setData('patient_id', val);
                                    if (opt) {
                                        setQuickPatientLabel(opt.label);
                                        if (opt.gender) setPatientGender(opt.gender.toLowerCase());
                                    }
                                }}
                                initialLabel={quickPatientLabel || preselected_patient_label}
                                disabled={!!preselected_patient_id}
                                placeholder="Select Patient..."
                                onAddNew={() => setIsModalOpen(true)}
                                addNewLabel="Quick Register Walk-in"
                            />
                        </FormField>

                        <FormField label="Attending Doctor" required error={errors.doctor_id} className="col-md-4">
                            <FormSelect 
                                className={`form-select form-select-lg bg-light border-0 ${errors.doctor_id ? 'is-invalid' : ''}`}
                                value={data.doctor_id}
                                onChange={e => setData('doctor_id', e.target.value)}
                                options={doctors}
                            />
                        </FormField>

                        <FormField label="Date & Time" required className="col-md-4">
                            <div className="input-group">
                                <input 
                                    type="datetime-local" 
                                    className="form-control form-control-lg bg-light border-0 shadow-none"
                                    value={data.consultation_date}
                                    onChange={e => setData('consultation_date', e.target.value)}
                                    required
                                />
                                <button 
                                    type="button" 
                                    className="btn btn-outline-primary border-0 bg-light-subtle px-3" 
                                    title="Set to Current Time"
                                    onClick={() => setData('consultation_date', toLocalISO())}
                                >
                                    <i className="fas fa-clock"></i>
                                </button>
                            </div>
                        </FormField>
                    </div>

                    <div className="row g-3 mb-4">
                        <FormField label="Priority Level" className="col-md-4">
                            <div className="d-flex gap-2">
                                <button 
                                    type="button" 
                                    className={`btn rounded-pill px-4 flex-fill fw-bold transition-all ${data.priority === 'normal' ? 'btn-primary shadow' : 'btn-light border text-muted'}`}
                                    onClick={() => setData('priority', 'normal')}
                                >
                                    Normal
                                </button>
                                <button 
                                    type="button" 
                                    className={`btn rounded-pill px-4 flex-fill fw-bold transition-all ${data.priority === 'emergency' ? 'btn-danger shadow' : 'btn-light border text-muted'}`}
                                    onClick={() => setData('priority', 'emergency')}
                                >
                                    <i className="fas fa-bolt me-2"></i>Emergency
                                </button>
                            </div>
                        </FormField>
                    </div>
                    
                    <h6 className="text-primary fw-bold mb-3 border-bottom pb-2">Vital Signs</h6>
                    <div className="row g-3">
                        <FormField label="BP (mmHg)" className="col-md-3">
                            <input type="text" className="form-control" placeholder="120/80" value={data.vital_signs.blood_pressure} onChange={e => setNestedData('vital_signs', 'blood_pressure', e.target.value)} />
                        </FormField>
                        <FormField label="Heart Rate (bpm)" className="col-md-3">
                            <input type="number" className="form-control" placeholder="72" value={data.vital_signs.heart_rate} onChange={e => setNestedData('vital_signs', 'heart_rate', e.target.value)} />
                        </FormField>
                        <FormField label="Temp (°C)" className="col-md-3">
                            <input type="number" step="0.1" className="form-control" placeholder="36.5" value={data.vital_signs.temperature} onChange={e => setNestedData('vital_signs', 'temperature', e.target.value)} />
                        </FormField>
                        <FormField label="SpO2 (%)" className="col-md-3">
                            <input type="number" className="form-control" placeholder="98" value={data.vital_signs.oxygen_saturation} onChange={e => setNestedData('vital_signs', 'oxygen_saturation', e.target.value)} />
                        </FormField>
                        <FormField label="Weight (kg)" className="col-md-3">
                            <input type="number" step="0.1" className="form-control" placeholder="60.0" value={data.vital_signs.weight} onChange={e => setNestedData('vital_signs', 'weight', e.target.value)} />
                        </FormField>
                        <FormField label="Height (cm)" className="col-md-3">
                            <input type="number" className="form-control" placeholder="165" value={data.vital_signs.height} onChange={e => setNestedData('vital_signs', 'height', e.target.value)} />
                        </FormField>
                    </div>
                </FormSection>

                {auth?.user?.role !== 'nurse' && (
                    <>
                        {/* 2. Complaints & History of Present Illness */}
                <div className="col-lg-6">
                    <FormSection title="Chief Complaints & HPI" className="h-100" headerClassName="bg-white border-bottom text-primary p-3">
                        <FormField label="Chief Complaints" error={errors.chief_complaint} className="mb-3">
                            <textarea 
                                className={`form-control bg-light border-0 ${errors.chief_complaint ? 'is-invalid' : ''}`}
                                rows="3" 
                                value={data.chief_complaint}
                                onChange={e => setData('chief_complaint', e.target.value)}
                                placeholder="Key symptoms reported by patient..."
                            />
                        </FormField>
                        <FormField label="History of Present Illness" className="mb-0">
                            <textarea 
                                className="form-control bg-light border-0" 
                                rows="5" 
                                value={data.history_present_illness}
                                onChange={e => setData('history_present_illness', e.target.value)}
                                placeholder="Detailed narrative of the illness..."
                            />
                        </FormField>
                    </FormSection>
                </div>

                {/* 3. Medical & Surgical History */}
                <div className="col-lg-6">
                    <FormSection title="Medical & Surgical History" className="h-100" headerClassName="bg-white border-bottom text-primary p-3">
                        <FormField label="Past Medical History" className="mb-3">
                            <textarea className="form-control" rows="2" value={data.past_medical_history} onChange={e => setData('past_medical_history', e.target.value)} placeholder="Chronic conditions, allergies, past illnesses..." />
                        </FormField>
                        <FormField label="Surgical History" className="mb-3">
                            <textarea className="form-control" rows="2" value={data.surgical_history} onChange={e => setData('surgical_history', e.target.value)} placeholder="Past surgeries and procedures..." />
                        </FormField>
                        <div className="row">
                            <FormField label="Family History" className="col-md-6">
                                <textarea className="form-control" rows="2" value={data.family_history} onChange={e => setData('family_history', e.target.value)} />
                            </FormField>
                            <FormField label="Social History" className="col-md-6">
                                <textarea className="form-control" rows="2" value={data.social_history} onChange={e => setData('social_history', e.target.value)} />
                            </FormField>
                        </div>
                    </FormSection>
                </div>

                {/* 4. Gynaecological History */}
                {((patientGender === 'female' || isPartnerContext) && !skipRepro) && (
                <div className="col-12 animate-fade-in">
                    <FormSection 
                        title="Gynaecological History" 
                        icon="fas fa-venus" 
                        headerClassName="bg-pink-50 text-pink-700 p-3"
                        actions={
                            <div className="d-flex gap-3 align-items-center me-2">
                                <div className="form-check form-switch mb-0">
                                    <input className="form-check-input" type="checkbox" id="skipRepro" checked={skipRepro} onChange={e => setSkipRepro(e.target.checked)} />
                                    <label className="form-check-label text-xs fw-bold uppercase" htmlFor="skipRepro">Does Not Apply</label>
                                </div>
                            </div>
                        }
                    >
                        <div className="row g-4 mb-4">
                            <div className="col-lg-6 border-end">
                                <h6 className="text-secondary small fw-bold text-uppercase mb-3">Menstrual History</h6>
                                <div className="row g-3">
                                    <FormField label="LMP Date" className="col-md-6">
                                        <input type="date" className="form-control shadow-none border-light bg-light" value={data.menstrual_history.last_period_date} onChange={e => setNestedData('menstrual_history', 'last_period_date', e.target.value)} />
                                    </FormField>
                                    <FormField label="Regularity" className="col-md-6">
                                        <FormSelect 
                                            value={data.menstrual_history.regularity} 
                                            onChange={e => setNestedData('menstrual_history', 'regularity', e.target.value)}
                                            options={[
                                                { value: 'regular', label: 'Regular' },
                                                { value: 'irregular', label: 'Irregular' }
                                            ]}
                                        />
                                    </FormField>
                                    <FormField label="Duration (Days)" className="col-md-6">
                                        <input type="number" className="form-control" placeholder="e.g. 5" value={data.menstrual_history.flow_duration} onChange={e => setNestedData('menstrual_history', 'flow_duration', e.target.value)} />
                                    </FormField>
                                    <FormField label="Dysmenorrhea" className="col-md-6">
                                        <FormSelect 
                                            value={data.menstrual_history.dysmenorrhea} 
                                            onChange={e => setNestedData('menstrual_history', 'dysmenorrhea', e.target.value)}
                                            options={[
                                                { value: 'none', label: 'None' },
                                                { value: 'mild', label: 'Mild' },
                                                { value: 'moderate', label: 'Moderate' },
                                                { value: 'severe', label: 'Severe' }
                                            ]}
                                        />
                                    </FormField>
                                </div>
                            </div>
                            <div className="col-lg-6">
                                <FormField label="Cervical Cancer Screening / Pap Smear" className="mb-3">
                                    <textarea className="form-control" rows="2" placeholder="Date of last test, results..." value={data.cervical_screening} onChange={e => setData('cervical_screening', e.target.value)} />
                                </FormField>
                                <div className="row g-2">
                                    <FormField label="Contraceptive Method" className="col-md-7 mb-3">
                                        <FormSelect 
                                            value={data.contraceptive_history} 
                                            onChange={e => setData('contraceptive_history', e.target.value)}
                                            options={[
                                                { value: '', label: 'Select Method...' },
                                                { value: 'none', label: 'None / Barrier' },
                                                { value: 'pill', label: 'Oral Combined Pill' },
                                                { value: 'injection', label: 'Depo Injection' },
                                                { value: 'implant', label: 'Hormonal Implant' },
                                                { value: 'iud', label: 'IUD (Coil)' },
                                                { value: 'tubal', label: 'Tubal Ligation' },
                                                { value: 'vasectomy', label: 'Vasectomy' },
                                                { value: 'natural', label: 'Natural / Calendar' },
                                                { value: 'emergency', label: 'Emergency Pill' }
                                            ]}
                                        />
                                    </FormField>
                                    <FormField label="Sexual Health Notes" className="col-md-5 mb-3">
                                        <input type="text" className="form-control" value={data.sexual_history} onChange={e => setData('sexual_history', e.target.value)} />
                                    </FormField>
                                </div>
                            </div>
                        </div>
                    </FormSection>
                </div>
                )}

                {/* 5. Obstetric History */}
                {((patientGender === 'female' || isPartnerContext) && !skipRepro) && (
                <div className="col-12 animate-fade-in">
                    <FormSection title="Obstetric History" icon="fas fa-baby-carriage" headerClassName="bg-purple-50 text-purple-700 p-3">
                        <div className="row g-3 mb-4">
                            <FormField label="Parity (No. of Pregnancies)" className="col-md-4">
                                <input type="number" className="form-control" value={data.parity} onChange={e => setData('parity', e.target.value)} />
                            </FormField>
                            <FormField label="Current Pregnancy Notes" className="col-md-8">
                                <input type="text" className="form-control" placeholder="Any details on current pregnancy..." value={data.current_pregnancy} onChange={e => setData('current_pregnancy', e.target.value)} />
                            </FormField>
                        </div>
                        
                        <h6 className="text-secondary small fw-bold text-uppercase border-bottom pb-2 mb-3">
                            Past Pregnancies 
                            <button type="button" onClick={addObstetricRecord} className="btn btn-sm btn-outline-primary ms-3 rounded-pill">
                                <i className="fas fa-plus me-1"></i> Add Record
                            </button>
                        </h6>
                        
                        {data.past_obstetric.length === 0 && (
                            <p className="text-muted small italic">No past pregnancy records added.</p>
                        )}

                        {data.past_obstetric.map((rec, idx) => (
                            <div key={idx} className="bg-light p-3 rounded mb-3 position-relative border">
                                <button type="button" onClick={() => removeObstetricRecord(idx)} className="btn btn-sm btn-light text-danger position-absolute top-0 end-0 m-2 rounded-circle" title="Remove">
                                    <i className="fas fa-times"></i>
                                </button>
                                <div className="row g-2">
                                    <div className="col-md-2">
                                        <input type="text" className="form-control form-control-sm" placeholder="Year" value={rec.year} onChange={e => updateObstetricRecord(idx, 'year', e.target.value)} />
                                    </div>
                                    <div className="col-md-3">
                                        <input type="text" className="form-control form-control-sm" placeholder="Place of Birth" value={rec.place_of_birth} onChange={e => updateObstetricRecord(idx, 'place_of_birth', e.target.value)} />
                                    </div>
                                    <div className="col-md-2">
                                        <input type="text" className="form-control form-control-sm" placeholder="Duration" value={rec.duration} onChange={e => updateObstetricRecord(idx, 'duration', e.target.value)} />
                                    </div>
                                    <div className="col-md-3">
                                        <input type="text" className="form-control form-control-sm" placeholder="Mode of Delivery" value={rec.mode_of_delivery} onChange={e => updateObstetricRecord(idx, 'mode_of_delivery', e.target.value)} />
                                    </div>
                                    <div className="col-md-2">
                                        <input type="text" className="form-control form-control-sm" placeholder="Outcome" value={rec.outcome} onChange={e => updateObstetricRecord(idx, 'outcome', e.target.value)} />
                                    </div>
                                    <div className="col-md-2">
                                        <input type="text" className="form-control form-control-sm" placeholder="Sex" value={rec.sex} onChange={e => updateObstetricRecord(idx, 'sex', e.target.value)} />
                                    </div>
                                    <div className="col-md-2">
                                        <input type="text" className="form-control form-control-sm" placeholder="Weight" value={rec.weight} onChange={e => updateObstetricRecord(idx, 'weight', e.target.value)} />
                                    </div>
                                    <div className="col-md-8">
                                        <input type="text" className="form-control form-control-sm" placeholder="Complications" value={rec.complications} onChange={e => updateObstetricRecord(idx, 'complications', e.target.value)} />
                                    </div>
                                </div>
                            </div>
                        ))}
                    </FormSection>
                </div>
                )}

                {/* Male/Non-Female Context Trigger */}
                {(patientGender !== 'female' && !isPartnerContext) && (
                    <div className="col-12">
                        <div className="card border-0 bg-light p-4 rounded-2xl text-center shadow-sm">
                            <p className="text-muted mb-3 italic small">Reproductive & Obstetric sections are hidden for {patientGender} patients.</p>
                            <div className="d-flex justify-content-center">
                                <div className="form-check form-check-inline">
                                    <input className="form-check-input" type="checkbox" id="partnerContext" checked={isPartnerContext} onChange={e => setIsPartnerContext(e.target.checked)} />
                                    <label className="form-check-label fw-bold text-primary" htmlFor="partnerContext">
                                        Enable Partner/Reproductive Context
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
                
                {/* 6. Examination & System Review */}
                <div className="col-12">
                    <FormSection title="Examination & Review of Systems" headerClassName="bg-white border-bottom text-primary p-3">
                        <div className="row g-4">
                            <FormField label="General Examination" className="col-md-6">
                                <textarea className="form-control" rows="3" placeholder="General appearance..." value={data.general_examination} onChange={e => setData('general_examination', e.target.value)} />
                            </FormField>
                            <FormField label="Review of Systems" className="col-md-6">
                                <textarea className="form-control" rows="3" placeholder="Systematic review..." value={data.review_of_systems} onChange={e => setData('review_of_systems', e.target.value)} />
                            </FormField>
                            <FormField label="Specific Systems Examination" className="col-12">
                                <textarea className="form-control" rows="3" placeholder="Detailed findings..." value={data.systems_examination} onChange={e => setData('systems_examination', e.target.value)} />
                            </FormField>
                        </div>
                    </FormSection>
                </div>

                {/* 7. Services, Procedures & Diagnostics — NO PRICING */}
                <div className="col-12">
                    <FormSection 
                        title="Services, Procedures & Diagnostics" 
                        icon="fas fa-microscope" 
                        headerClassName="bg-blue-50 text-blue-700 p-3"
                    >
                        <div className="row g-4">
                            {/* Lab Tests Column */}
                            <div className="col-lg-4 border-end">
                                <h6 className="fw-bold mb-3 text-secondary small text-uppercase">Laboratory Tests</h6>
                                <select 
                                    className="form-select bg-light border-0 mb-3"
                                    onChange={(e) => { addLab(e.target.value); e.target.value = ""; }}
                                    defaultValue=""
                                >
                                    <option value="" disabled>Add a Lab Test...</option>
                                    {Object.entries(labsByCategory).map(([cat, labs]) => (
                                        <optgroup key={cat} label={cat}>
                                            {labs.map(l => (
                                                <option key={l.test_type_id} value={l.test_type_id}>{l.test_name}</option>
                                            ))}
                                        </optgroup>
                                    ))}
                                </select>
                                <ul className="list-group list-group-flush mb-0">
                                    {data.requested_labs.map(l => (
                                        <li key={l.test_type_id} className="list-group-item d-flex justify-content-between align-items-center bg-light mb-2 rounded border-0 py-2">
                                            <div>
                                                <span className="fw-bold text-gray-800 d-block small">{l.test_name}</span>
                                                <span className="text-secondary" style={{ fontSize: '.7rem' }}>{l.category}</span>
                                            </div>
                                            <button type="button" onClick={() => removeLab(l.test_type_id)} className="btn btn-sm btn-light text-danger"><i className="fas fa-times"></i></button>
                                        </li>
                                    ))}
                                    {data.requested_labs.length === 0 && <li className="list-group-item border-0 bg-transparent text-muted small italic px-0">No lab tests selected.</li>}
                                </ul>
                            </div>

                            {/* Services/Procedures Column */}
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
                                    {data.requested_service_items.length === 0 && <li className="list-group-item border-0 bg-transparent text-muted small italic px-0">No services selected.</li>}
                                </ul>
                            </div>
                            
                            {/* Medical Procedures Column (from MedicalProcedure model) */}
                            <div className="col-lg-4">
                                <h6 className="fw-bold mb-3 text-secondary small text-uppercase">Surgeries</h6>
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
                                    {data.requested_procedures.length === 0 && <li className="list-group-item border-0 bg-transparent text-muted small italic px-0">No surgeries selected.</li>}
                                </ul>
                            </div>
                        </div>
                        
                        <div className="mt-4 pt-3 border-top text-muted small">
                            <i className="fas fa-info-circle me-1"></i>
                            Selected items will be automatically billed on the patient invoice upon consultation completion.
                        </div>
                    </FormSection>
                </div>

                {/* 8. Impression & Plan */}
                <div className="col-12">
                    <FormSection 
                        title="Impression & Management Plan" 
                        icon="fas fa-clipboard-check" 
                        className="border-start border-5 border-success"
                        headerClassName="bg-success-subtle text-success-emphasis p-3"
                    >
                        <FormField label="Impression / Diagnosis" error={errors.diagnosis} className="mb-3">
                            <textarea className="form-control form-control-lg bg-light" rows="2" value={data.diagnosis} onChange={e => setData('diagnosis', e.target.value)} />
                        </FormField>
                        <FormField label="Treatment Plan" className="mb-3">
                            <textarea className="form-control" rows="4" placeholder="Medications, general advice..." value={data.treatment_plan} onChange={e => setData('treatment_plan', e.target.value)} />
                        </FormField>
                        <div className="row">
                            <FormField label="Follow-up Instructions" className="col-md-6">
                                <input type="text" className="form-control" value={data.follow_up_instructions} onChange={e => setData('follow_up_instructions', e.target.value)} />
                            </FormField>
                            <FormField label="Internal Notes" className="col-md-6">
                                <input type="text" className="form-control" value={data.notes} onChange={e => setData('notes', e.target.value)} />
                            </FormField>
                        </div>
                    </FormSection>
                </div>
                </>
                )}

                {/* Actions */}
                <div className="col-12 text-end">
                    <button type="button" onClick={() => { reset(); clearDraft(); }} className="btn btn-light rounded-pill px-4 me-2">Clear</button>
                    {auth?.user?.role === 'nurse' ? (
                        <button type="submit" onClick={(e) => submit(e, 'in_progress')} disabled={processing} className="btn btn-primary rounded-pill px-5 btn-lg shadow fw-bold">
                            <i className="fas fa-heartbeat me-2"></i>Save Vitals
                        </button>
                    ) : (
                        <>
                            <button type="button" onClick={(e) => submit(e, 'in_progress')} disabled={processing} className="btn btn-outline-primary rounded-pill px-4 me-3 shadow-sm fw-bold">
                                <i className="fas fa-save me-2"></i>Save Progress & Request Labs
                            </button>
                            <button type="submit" disabled={processing} className="btn btn-primary rounded-pill px-5 btn-lg shadow fw-bold">
                                <i className="fas fa-check-circle me-2"></i>Conclude Consultation
                            </button>
                        </>
                    )}
                </div>
            </form>

            <QuickPatientModal 
                isOpen={isModalOpen} 
                onClose={() => setIsModalOpen(false)}
                onSuccess={(patient) => {
                    setData('patient_id', patient.value);
                    setQuickPatientLabel(patient.label);
                }}
            />

            {/* Lab Confirmation Modal */}
            {showLabConfirmModal && (
                <div className="modal show d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1060 }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 shadow-lg rounded-4">
                            <div className="modal-header bg-primary text-white p-4 rounded-top-4">
                                <h5 className="modal-title fw-bold">
                                    <i className="fas fa-microscope me-2"></i>Register Lab Requests?
                                </h5>
                                <button type="button" className="btn-close btn-close-white" onClick={() => setShowLabConfirmModal(false)}></button>
                            </div>
                            <div className="modal-body p-4 text-center">
                                <p className="mb-4 fs-5">
                                    You have <strong>{data.requested_service_items.length} item(s)</strong> selected in the 
                                    <em> Services, Procedures & Diagnostics</em> section.
                                </p>
                                <p className="text-muted">Would you like to register these as <strong>official Lab Requests</strong> so they appear on the laboratory dashboard?</p>
                            </div>
                            <div className="modal-footer p-3 bg-light rounded-bottom-4 border-0 justify-content-center gap-2">
                                <button type="button" className="btn btn-outline-secondary px-4 rounded-pill" onClick={skipMoveToLabs}>Keep as Services only</button>
                                <button type="button" className="btn btn-primary px-5 rounded-pill fw-bold shadow-sm" onClick={confirmMoveToLabs}>Yes, Move to Labs & Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Simple Toast Notification */}
            {toast && (
                <div className="position-fixed top-0 start-50 translate-middle-x mt-4 z-3 animate__animated animate__fadeInDown" style={{ zIndex: 9999 }}>
                    <div className={`alert alert-${toast.type} shadow-lg rounded-pill px-5 py-3 border-0 d-flex align-items-center gap-3`}>
                        <i className={`fas fa-${toast.type === 'success' ? 'check-circle' : 'exclamation-circle'} fs-4 text-${toast.type}`}></i>
                        <span className="fw-bold">{toast.message}</span>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
