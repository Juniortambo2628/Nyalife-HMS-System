import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import DashboardSelect from '@/Components/DashboardSelect';
import QuickPatientModal from '@/Components/QuickPatientModal';
import { useState } from 'react';

export default function Record({ preselected_patient_id, preselected_patient_label, auth }) {
    const [showQuickModal, setShowQuickModal] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        patient_id: preselected_patient_id || '',
        temperature: '',
        blood_pressure: '',
        heart_rate: '',
        respiratory_rate: '',
        weight: '',
        height: '',
        oxygen_saturation: '',
        notes: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('vitals.store'));
    };

    const handleQuickSuccess = (newPatient) => {
        setData('patient_id', newPatient.patient_id);
    };

    const labelClass = "form-label font-bold text-gray-500 mb-2 small text-uppercase tracking-wider";
    const inputClass = "form-control border-0 bg-light rounded-xl py-3 px-4 focus:bg-white focus:ring-2 focus:ring-pink-100 transition-all";

    return (
        <AuthenticatedLayout user={auth.user} header="Record Vitals">
            <Head title="Record Vitals" />

            <PageHeader 
                title="Capture Vital Signs"
                breadcrumbs={[
                    { label: 'Dashboard', url: route('dashboard') },
                    { label: 'Vitals', active: true }
                ]}
                actions={
                    <button 
                        onClick={() => setShowQuickModal(true)}
                        className="btn btn-pink-light rounded-pill px-4 fw-bold shadow-sm"
                    >
                        <i className="fas fa-plus-circle me-2"></i> Register New Patient
                    </button>
                }
            />

            <div className="row justify-content-center">
                <div className="col-lg-8">
                    <div className="card shadow-sm border-0 rounded-2xl bg-white">
                        <div className="card-header bg-white border-bottom p-4">
                            <h5 className="fw-bold mb-0 text-gray-900">
                                <i className="fas fa-heartbeat text-pink-500 me-2"></i>
                                Assessment Form
                            </h5>
                        </div>
                        <div className="card-body p-4 p-md-5">
                            <form onSubmit={handleSubmit}>
                                <div className="mb-5">
                                    <label className={labelClass}>Select Patient</label>
                                    <DashboardSelect 
                                        asyncUrl="/patients/search"
                                        value={data.patient_id}
                                        onChange={val => setData('patient_id', val)}
                                        initialLabel={preselected_patient_label}
                                        placeholder="Search by name or ID..."
                                        className={errors.patient_id ? 'is-invalid' : ''}
                                        onAddNew={() => setShowQuickModal(true)}
                                        addNewLabel="New Patient Registry"
                                    />
                                    {errors.patient_id && <div className="text-danger small mt-2">{errors.patient_id}</div>}
                                </div>

                                <div className="row g-4 mb-4">
                                    <div className="col-md-6">
                                        <label className={labelClass}>Temperature (°C)</label>
                                        <div className="input-group">
                                            <input type="text" className={inputClass} placeholder="e.g. 36.5" value={data.temperature} onChange={e => setData('temperature', e.target.value)} />
                                            <span className="input-group-text bg-light border-0 rounded-end-xl"><i className="fas fa-thermometer-half text-muted"></i></span>
                                        </div>
                                        {errors.temperature && <div className="text-danger small mt-1">{errors.temperature}</div>}
                                    </div>
                                    <div className="col-md-6">
                                        <label className={labelClass}>Blood Pressure (mmHg)</label>
                                        <div className="input-group">
                                            <input type="text" className={inputClass} placeholder="e.g. 120/80" value={data.blood_pressure} onChange={e => setData('blood_pressure', e.target.value)} />
                                            <span className="input-group-text bg-light border-0 rounded-end-xl"><i className="fas fa-tint text-muted"></i></span>
                                        </div>
                                        {errors.blood_pressure && <div className="text-danger small mt-1">{errors.blood_pressure}</div>}
                                    </div>
                                </div>

                                <div className="row g-4 mb-4">
                                    <div className="col-md-6">
                                        <label className={labelClass}>Heart Rate (bpm)</label>
                                        <div className="input-group">
                                            <input type="text" className={inputClass} placeholder="e.g. 72" value={data.heart_rate} onChange={e => setData('heart_rate', e.target.value)} />
                                            <span className="input-group-text bg-light border-0 rounded-end-xl"><i className="fas fa-heart text-muted"></i></span>
                                        </div>
                                        {errors.heart_rate && <div className="text-danger small mt-1">{errors.heart_rate}</div>}
                                    </div>
                                    <div className="col-md-6">
                                        <label className={labelClass}>Respiratory Rate (bpm)</label>
                                        <div className="input-group">
                                            <input type="text" className={inputClass} placeholder="e.g. 16" value={data.respiratory_rate} onChange={e => setData('respiratory_rate', e.target.value)} />
                                            <span className="input-group-text bg-light border-0 rounded-end-xl"><i className="fas fa-lungs text-muted"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <div className="row g-4 mb-4">
                                    <div className="col-md-4">
                                        <label className={labelClass}>Weight (kg)</label>
                                        <input type="text" className={inputClass} placeholder="e.g. 70" value={data.weight} onChange={e => setData('weight', e.target.value)} />
                                    </div>
                                    <div className="col-md-4">
                                        <label className={labelClass}>Height (cm)</label>
                                        <input type="text" className={inputClass} placeholder="e.g. 175" value={data.height} onChange={e => setData('height', e.target.value)} />
                                    </div>
                                    <div className="col-md-4">
                                        <label className={labelClass}>SPO2 (%)</label>
                                        <input type="text" className={inputClass} placeholder="e.g. 98" value={data.oxygen_saturation} onChange={e => setData('oxygen_saturation', e.target.value)} />
                                    </div>
                                </div>

                                <div className="mb-4">
                                    <label className={labelClass}>Notes / Observations</label>
                                    <textarea 
                                        className={inputClass} 
                                        rows="3" 
                                        placeholder="Any additional observations..."
                                        value={data.notes}
                                        onChange={e => setData('notes', e.target.value)}
                                    ></textarea>
                                </div>

                                <div className="text-end mt-5">
                                    <button type="submit" className="btn btn-primary rounded-pill px-5 py-3 font-bold shadow-lg transition-all hover-lift" disabled={processing}>
                                        {processing ? (
                                            <span className="spinner-border spinner-border-sm me-2"></span>
                                        ) : (
                                            <i className="fas fa-save me-2"></i>
                                        )}
                                        Complete Entry
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <QuickPatientModal 
                show={showQuickModal} 
                onClose={() => setShowQuickModal(false)} 
                onSuccess={handleQuickSuccess}
            />

            <style>{`
                .btn-pink-light { background-color: #fdf2f8; color: #ec4899; }
                .btn-pink-light:hover { background-color: #fce7f3; color: #db2777; }
                .rounded-end-xl { border-top-right-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }
                .hover-lift:hover { transform: translateY(-3px); }
            `}</style>
        </AuthenticatedLayout>
    );
}
