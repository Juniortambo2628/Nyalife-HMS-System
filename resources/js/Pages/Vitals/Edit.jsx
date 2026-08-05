import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import FormSection from '@/Components/FormSection';
import FormField from '@/Components/FormField';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { PatientIdLabel } from '@/Components/PatientTableCell';

/**
 * Record Vitals Page - Standardized to match the clinical premium design system.
 * Aligned with the Consultations workflow for consistency across the HMS.
 */
export default function Edit({ vital, auth }) {
    const { data, setData, put, processing, errors } = useForm({
        patient_id: vital.patient_id,
        consultation_id: vital.consultation_id || '',
        temperature: vital.temperature || '',
        blood_pressure: vital.blood_pressure || '',
        heart_rate: vital.heart_rate || '',
        respiratory_rate: vital.respiratory_rate || '',
        weight: vital.weight || '',
        height: vital.height || '',
        oxygen_saturation: vital.oxygen_saturation || '',
        pain_level: vital.pain_level ?? '',
        priority: vital.priority || 'normal',
        notes: vital.notes || '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('vitals.update', vital.vital_id));
    };

    return (
        <AuthenticatedLayout
            headerTitle="Edit Clinical Vitals"
            breadcrumbs={[
                { label: 'Dashboard', url: route('dashboard') },
                { label: 'Vitals Ledger', url: route('vitals.index') },
                { label: 'Edit Vitals', active: true },
            ]}
        >
            <Head title="Edit Vital Signs" />

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
                                <div className="col-12">
                                    <h5 className="fw-bold mb-0">
                                        {vital.patient?.user?.first_name} {vital.patient?.user?.last_name}
                                    </h5>
                                    <PatientIdLabel
                                        id={vital.patient_id}
                                        variant="pat-id"
                                        className="text-muted small mb-0"
                                    />
                                </div>
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
                                            onChange={(e) => setData('temperature', e.target.value)}
                                        />
                                        <span className="input-group-text bg-light border-0 text-muted px-4">
                                            <i className="fas fa-thermometer-half"></i>
                                        </span>
                                    </div>
                                </FormField>

                                <FormField
                                    label="Blood Pressure (mmHg)"
                                    error={errors.blood_pressure}
                                    className="col-md-6"
                                >
                                    <div className="input-group">
                                        <input
                                            type="text"
                                            className="form-control form-control-lg bg-light border-0 fw-bold px-4"
                                            placeholder="e.g. 120/80"
                                            value={data.blood_pressure}
                                            onChange={(e) => setData('blood_pressure', e.target.value)}
                                        />
                                        <span className="input-group-text bg-light border-0 text-muted px-4">
                                            <i className="fas fa-tint"></i>
                                        </span>
                                    </div>
                                </FormField>

                                <FormField label="Heart Rate (bpm)" error={errors.heart_rate} className="col-md-6">
                                    <div className="input-group">
                                        <input
                                            type="text"
                                            className="form-control form-control-lg bg-light border-0 fw-bold px-4"
                                            placeholder="e.g. 72"
                                            value={data.heart_rate}
                                            onChange={(e) => setData('heart_rate', e.target.value)}
                                        />
                                        <span className="input-group-text bg-light border-0 text-muted px-4">
                                            <i className="fas fa-heart"></i>
                                        </span>
                                    </div>
                                </FormField>

                                <FormField
                                    label="Respiratory Rate (bpm)"
                                    error={errors.respiratory_rate}
                                    className="col-md-6"
                                >
                                    <div className="input-group">
                                        <input
                                            type="text"
                                            className="form-control form-control-lg bg-light border-0 fw-bold px-4"
                                            placeholder="e.g. 16"
                                            value={data.respiratory_rate}
                                            onChange={(e) => setData('respiratory_rate', e.target.value)}
                                        />
                                        <span className="input-group-text bg-light border-0 text-muted px-4">
                                            <i className="fas fa-lungs"></i>
                                        </span>
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
                                        onChange={(e) => setData('weight', e.target.value)}
                                    />
                                </FormField>

                                <FormField label="Height (cm)" error={errors.height} className="col-md-4">
                                    <input
                                        type="text"
                                        className="form-control form-control-lg bg-light border-0 fw-bold px-4"
                                        placeholder="0"
                                        value={data.height}
                                        onChange={(e) => setData('height', e.target.value)}
                                    />
                                </FormField>

                                <FormField label="SPO2 (%)" error={errors.oxygen_saturation} className="col-md-4">
                                    <input
                                        type="text"
                                        className="form-control form-control-lg bg-light border-0 fw-bold px-4"
                                        placeholder="98"
                                        value={data.oxygen_saturation}
                                        onChange={(e) => setData('oxygen_saturation', e.target.value)}
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
                                <label className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-3 d-block">
                                    Urgency Level
                                </label>
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
                                    onChange={(e) => setData('notes', e.target.value)}
                                ></textarea>
                            </FormField>
                        </FormSection>

                        <UnifiedToolbar
                            actions={[
                                {
                                    label: 'SAVE RECORDS',
                                    icon: 'fa-save',
                                    onClick: handleSubmit,
                                    color: 'success',
                                },
                                {
                                    label: 'DISCARD',
                                    icon: 'fa-times',
                                    href: route('vitals.index'),
                                    color: 'gray',
                                },
                            ]}
                        />
                    </form>
                </div>
            </div>

            <style>{`
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
