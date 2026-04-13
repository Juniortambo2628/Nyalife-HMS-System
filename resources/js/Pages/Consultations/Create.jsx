import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import DashboardSelect from '@/Components/DashboardSelect';
import PageHeader from '@/Components/PageHeader';
import FormSection from '@/Components/FormSection';
import FormField from '@/Components/FormField';
import FormSelect from '@/Components/FormSelect';
import QuickPatientModal from '@/Components/QuickPatientModal';
import { useState } from 'react';

import { toLocalISO } from '@/Utils/dateUtils';

export default function Create({ patients, doctors, appointment_id, preselected_patient_id, preselected_patient_label, priority = 'normal', auth }) {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [quickPatientLabel, setQuickPatientLabel] = useState('');
    
    const { data, setData, post, processing, errors, reset } = useForm({
        patient_id: preselected_patient_id || '',
        doctor_id: auth.user.role === 'doctor' && auth.user.staff ? auth.user.staff.staff_id : '',
        appointment_id: appointment_id || '',
        consultation_date: toLocalISO(),
        priority: priority || 'normal',
        is_walk_in: !appointment_id,
        status: 'pending',
        
        // Vitals
        vital_signs: {
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
        chief_complaint: '',
        history_present_illness: '',
        
        // Gynaecological History
        menstrual_history: {
            last_period_date: '',
            regularity: 'regular',
            flow_duration: '',
            dysmenorrhea: 'none', // none, mild, moderate, severe
        },
        cervical_screening: '', // Pap Smear History
        contraceptive_history: '',
        sexual_history: '',
        
        // Obstetric History
        parity: '', // Number of pregnancies
        current_pregnancy: '', // Notes on current pregnancy if applicable
        past_obstetric: [], // Array of previous pregnancies
        
        // Medical & Surgical
        past_medical_history: '',
        surgical_history: '',
        
        // Family & Social
        family_history: '',
        social_history: '',
        
        // System Review & Examination
        review_of_systems: '',
        general_examination: '', // General appearance etc
        systems_examination: '', // Specific systems
        
        // Assessment & Plan
        diagnosis: '',
        // diagnosis_confidence: '',
        // differential_diagnosis: '',
        treatment_plan: '',
        follow_up_instructions: '',
        notes: '',
    });

    // Helper for nested state updates
    const setNestedData = (parent, key, value) => {
        setData(parent, {
            ...data[parent],
            [key]: value
        });
    };

    // Helper for Obstetric History repeater
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

    const submit = (e, targetStatus = 'completed') => {
        if (e) e.preventDefault();
        setData('status', targetStatus);
        
        // Use a slight timeout to ensure state represents the latest 'status' value before post
        setTimeout(() => {
            post(route('consultations.store'));
        }, 50);
    };

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
                                    if (opt) setQuickPatientLabel(opt.label);
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

                {/* 2. Complaints & History of Present Illness */}
                <div className="col-lg-6">
                    <FormSection title="Chief Complaints & HPI" className="h-100" headerClassName="bg-white border-bottom text-primary p-3">
                        <FormField label="Chief Complaints" required error={errors.chief_complaint} className="mb-3">
                            <textarea 
                                className={`form-control bg-light border-0 ${errors.chief_complaint ? 'is-invalid' : ''}`}
                                rows="3" 
                                value={data.chief_complaint}
                                onChange={e => setData('chief_complaint', e.target.value)}
                                placeholder="Key symptoms reported by patient..."
                                required
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
                            <textarea 
                                className="form-control" 
                                rows="2" 
                                value={data.past_medical_history}
                                onChange={e => setData('past_medical_history', e.target.value)}
                                placeholder="Chronic conditions, allergies, past illnesses..."
                            />
                        </FormField>
                        <FormField label="Surgical History" className="mb-3">
                            <textarea 
                                className="form-control" 
                                rows="2" 
                                value={data.surgical_history}
                                onChange={e => setData('surgical_history', e.target.value)}
                                placeholder="Past surgeries and procedures..."
                            />
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
                <div className="col-12">
                    <FormSection title="Gynaecological History" icon="fas fa-venus" headerClassName="bg-pink-50 text-pink-700 p-3">
                        <div className="row g-4 mb-4">
                            <div className="col-lg-6 border-end">
                                <h6 className="text-secondary small fw-bold text-uppercase mb-3">Menstrual History</h6>
                                <div className="row g-3">
                                    <FormField label="LMP Date" className="col-md-6">
                                        <input type="date" className="form-control" value={data.menstrual_history.last_period_date} onChange={e => setNestedData('menstrual_history', 'last_period_date', e.target.value)} />
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
                                <FormField label="Contraception & Sexual Health" className="mb-3">
                                    <input type="text" className="form-control mb-2" placeholder="Contraception method used..." value={data.contraceptive_history} onChange={e => setData('contraceptive_history', e.target.value)} />
                                    <input type="text" className="form-control" placeholder="Sexual health notes..." value={data.sexual_history} onChange={e => setData('sexual_history', e.target.value)} />
                                </FormField>
                            </div>
                        </div>
                    </FormSection>
                </div>

                {/* 5. Obstetric History */}
                <div className="col-12">
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

                {/* 7. Impression & Plan */}
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
                            <textarea className="form-control" rows="4" placeholder="Medications, procedures, advice..." value={data.treatment_plan} onChange={e => setData('treatment_plan', e.target.value)} />
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

                {/* Actions */}
                <div className="col-12 text-end">
                    <button type="button" onClick={() => reset()} className="btn btn-light rounded-pill px-4 me-2">Clear</button>
                    <button type="button" onClick={(e) => submit(e, 'in_progress')} disabled={processing} className="btn btn-outline-primary rounded-pill px-4 me-3 shadow-sm fw-bold">
                        <i className="fas fa-save me-2"></i>Save Progress & Request Labs
                    </button>
                    <button type="submit" disabled={processing} className="btn btn-primary rounded-pill px-5 btn-lg shadow fw-bold">
                        <i className="fas fa-check-circle me-2"></i>Conclude Consultation
                    </button>
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
        </AuthenticatedLayout>
    );
}
