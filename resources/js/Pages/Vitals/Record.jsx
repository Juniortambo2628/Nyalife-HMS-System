import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import DashboardSelect from '@/Components/DashboardSelect';
import FormSection from '@/Components/FormSection';
import FormField from '@/Components/FormField';
import QuickPatientModal from '@/Components/QuickPatientModal';
import { useState, useEffect } from 'react';
import axios from 'axios';

/**
 * Record Vitals Page - Standardized to match the clinical premium design system.
 * Aligned with the Consultations workflow for consistency across the HMS.
 */
export default function Record({ preselected_patient_id, preselected_patient_label, latest_height, auth, ...props }) {
    const [showQuickModal, setShowQuickModal] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        patient_id: preselected_patient_id || '',
        patient_label: preselected_patient_label || '',
        temperature: '',
        blood_pressure: '',
        heart_rate: '',
        respiratory_rate: '',
        weight: '',
        height: latest_height || '',
        oxygen_saturation: '',
        priority: 'normal',
        notes: '',
    });

    useEffect(() => {
        if (data.patient_id) {
            axios.get(`/patients/${data.patient_id}/latest-vitals`)
                .then(res => {
                    if (res.data && res.data.height) {
                        setData('height', res.data.height);
                    }
                })
                .catch(err => console.error(err));
        }
    }, [data.patient_id]);

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('vitals.store'), {
            onSuccess: () => {
                // Clear state if needed or redirect
            }
        });
    };

    const handleQuickSuccess = (newPatient) => {
        setData(d => ({
            ...d,
            patient_id: newPatient.patient_id,
            patient_label: `${newPatient.first_name} ${newPatient.last_name}`
        }));
    };

    return (
        <AuthenticatedLayout user={auth.user} header="Clinical Assessment">
            <Head title="Capture Vital Signs" />

            <PageHeader 
                title="Record Clinical Vitals"
                breadcrumbs={[
                    { label: 'Dashboard', url: route('dashboard') },
                    { label: 'Vitals Ledger', url: route('vitals.index') },
                    { label: 'Capture New', active: true }
                ]}
            />

            <div className="row justify-content-center pb-5">
                <div className="col-lg-10 col-xl-9">
                    <form onSubmit={handleSubmit}>
                        {/* 1. Patient Selection Section */}
                        <FormSection 
                            title="Patient Identification" 
                            icon="fas fa-id-card"
                            headerClassName="bg-gradient-primary-to-secondary text-white p-4"
                        >
                            <div className="row g-3">
                                <FormField 
                                    label="Select Registered Patient" 
                                    required 
                                    error={errors.patient_id} 
                                    className="col-12"
                                    labelClassName="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-2"
                                >
                                    <DashboardSelect 
                                        asyncUrl="/patients/search"
                                        value={data.patient_id}
                                        onChange={(val, opt) => {
                                            setData(d => ({
                                                ...d,
                                                patient_id: val,
                                                patient_label: opt ? opt.label : ''
                                            }));
                                        }}
                                        initialLabel={data.patient_label || preselected_patient_label}
                                        placeholder="Scan catalog or search by name/ID..."
                                        onAddNew={() => setShowQuickModal(true)}
                                        addNewLabel="Quick Register New Patient"
                                    />
                                </FormField>
                            </div>
                        </FormSection>

                        {/* 2. Vital Signs Section */}
                        <FormSection 
                            title="Clinical Measurements" 
                            icon="fas fa-heartbeat"
                            headerClassName="bg-white border-bottom text-primary p-4"
                        >
                            <div className="row g-4">
                                <FormField label="Temperature (°C)" error={errors.temperature} className="col-md-6">
                                    <div className="input-group">
                                        <input 
                                            type="text" 
                                            className="form-control form-control-lg bg-light border-0 fw-bold px-4" 
                                            placeholder="e.g. 36.5" 
                                            value={data.temperature} 
                                            onChange={e => setData('temperature', e.target.value)} 
                                        />
                                        <span className="input-group-text bg-light border-0 text-muted px-4"><i className="fas fa-thermometer-half"></i></span>
                                    </div>
                                </FormField>

                                <FormField label="Blood Pressure (mmHg)" error={errors.blood_pressure} className="col-md-6">
                                    <div className="input-group">
                                        <input 
                                            type="text" 
                                            className="form-control form-control-lg bg-light border-0 fw-bold px-4" 
                                            placeholder="e.g. 120/80" 
                                            value={data.blood_pressure} 
                                            onChange={e => setData('blood_pressure', e.target.value)} 
                                        />
                                        <span className="input-group-text bg-light border-0 text-muted px-4"><i className="fas fa-tint"></i></span>
                                    </div>
                                </FormField>

                                <FormField label="Heart Rate (bpm)" error={errors.heart_rate} className="col-md-6">
                                    <div className="input-group">
                                        <input 
                                            type="text" 
                                            className="form-control form-control-lg bg-light border-0 fw-bold px-4" 
                                            placeholder="e.g. 72" 
                                            value={data.heart_rate} 
                                            onChange={e => setData('heart_rate', e.target.value)} 
                                        />
                                        <span className="input-group-text bg-light border-0 text-muted px-4"><i className="fas fa-heart"></i></span>
                                    </div>
                                </FormField>

                                <FormField label="Respiratory Rate (bpm)" error={errors.respiratory_rate} className="col-md-6">
                                    <div className="input-group">
                                        <input 
                                            type="text" 
                                            className="form-control form-control-lg bg-light border-0 fw-bold px-4" 
                                            placeholder="e.g. 16" 
                                            value={data.respiratory_rate} 
                                            onChange={e => setData('respiratory_rate', e.target.value)} 
                                        />
                                        <span className="input-group-text bg-light border-0 text-muted px-4"><i className="fas fa-lungs"></i></span>
                                    </div>
                                </FormField>

                                <div className="col-12 py-2">
                                    <hr className="opacity-10" />
                                </div>

                                <FormField label="Weight (kg)" error={errors.weight} className="col-md-4">
                                    <input 
                                        type="text" 
                                        className="form-control form-control-lg bg-light border-0 fw-bold px-4" 
                                        placeholder="0.0" 
                                        value={data.weight} 
                                        onChange={e => setData('weight', e.target.value)} 
                                    />
                                </FormField>

                                <FormField label="Height (cm)" error={errors.height} className="col-md-4">
                                    <input 
                                        type="text" 
                                        className="form-control form-control-lg bg-light border-0 fw-bold px-4" 
                                        placeholder="0" 
                                        value={data.height} 
                                        onChange={e => setData('height', e.target.value)} 
                                    />
                                </FormField>

                                <FormField label="SPO2 (%)" error={errors.oxygen_saturation} className="col-md-4">
                                    <input 
                                        type="text" 
                                        className="form-control form-control-lg bg-light border-0 fw-bold px-4" 
                                        placeholder="98" 
                                        value={data.oxygen_saturation} 
                                        onChange={e => setData('oxygen_saturation', e.target.value)} 
                                    />
                                </FormField>
                            </div>
                        </FormSection>

                        {/* 3. Triage & Notes */}
                        <FormSection 
                            title="Triage & Observations" 
                            icon="fas fa-clipboard-check"
                            headerClassName="bg-light text-dark p-4 border-bottom"
                        >
                            <div className="mb-4">
                                <label className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-3 d-block">Urgency Level</label>
                                <div className="d-flex gap-3">
                                    <button 
                                        type="button" 
                                        className={`btn rounded-pill px-5 py-3 fw-extrabold transition-all shadow-sm ${data.priority === 'normal' ? 'btn-primary border-0' : 'btn-light border text-muted'}`}
                                        onClick={() => setData('priority', 'normal')}
                                    >
                                        <i className="fas fa-check-circle me-2"></i> Normal
                                    </button>
                                    <button 
                                        type="button" 
                                        className={`btn rounded-pill px-5 py-3 fw-extrabold transition-all shadow-sm ${data.priority === 'emergency' ? 'btn-danger border-0' : 'btn-light border text-muted'}`}
                                        onClick={() => setData('priority', 'emergency')}
                                    >
                                        <i className="fas fa-bolt me-2"></i> Emergency
                                    </button>
                                </div>
                            </div>

                            <FormField label="Clinical Observations" error={errors.notes} className="col-12">
                                <textarea 
                                    className="form-control bg-light border-0 rounded-2xl p-4 fw-medium" 
                                    rows="4"
                                    value={data.notes}
                                    onChange={e => setData('notes', e.target.value)}
                                    placeholder="Enter any additional clinical observations or patient comments..."
                                ></textarea>
                            </FormField>
                        </FormSection>

                        {/* Submission */}
                        <div className="d-flex justify-content-between align-items-center mt-5">
                            <button type="button" onClick={() => router.visit(route('vitals.index'))} className="btn btn-link text-muted fw-bold text-decoration-none">
                                <i className="fas fa-arrow-left me-2"></i> Back to Ledger
                            </button>
                            <button 
                                type="submit" 
                                className="btn btn-primary rounded-pill px-5 py-3 fw-extrabold shadow-lg transition-all hover-lift" 
                                disabled={processing}
                            >
                                {processing ? (
                                    <span className="spinner-border spinner-border-sm me-2"></span>
                                ) : (
                                    <i className="fas fa-save me-2"></i>
                                )}
                                SAVE CLINICAL RECORDS
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <QuickPatientModal 
                show={showQuickModal} 
                onClose={() => setShowQuickModal(false)} 
                onSuccess={handleQuickSuccess}
            />
            
            <style>{`
                .extra-small { font-size: 0.7rem; }
                .fw-extrabold { font-weight: 800; }
                .tracking-widest { letter-spacing: 0.1em; }
                .bg-gradient-primary-to-secondary {
                    background: linear-gradient(135deg, #e91e63 0%, #d81b60 100%);
                }
                .hover-lift:hover { transform: translateY(-3px); box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important; }
            `}</style>
        </AuthenticatedLayout>
    );
}
