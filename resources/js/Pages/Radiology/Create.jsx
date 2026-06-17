import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import DashboardSelect from '@/Components/DashboardSelect';

export default function Create({ preselected_patient_id, preselected_patient_label, consultation_id, auth }) {
    const { data, setData, post, processing, errors } = useForm({
        patient_id: preselected_patient_id || '',
        consultation_id: consultation_id || '',
        scan_type: '',
        priority: 'routine',
        clinical_indication: '',
        scan_details: '',
    });

    const submit = (e) => {
        if (e) e.preventDefault();
        post(route('radiology.store'));
    };

    const scanTypes = [
        'Pelvic Ultrasound (TAS)',
        'Pelvic Ultrasound (TVS)',
        'Obstetric Ultrasound (Dating/Early Pregnancy)',
        'Obstetric Ultrasound (Growth & Doppler)',
        'Obstetric Ultrasound (Anomaly Scan)',
        'Follicular Tracking Ultrasound',
        'Mammography / Breast Ultrasound',
        'Hysterosalpingography (HSG)',
        'Abdominal Ultrasound',
        'Chest X-Ray',
        'Other Diagnostic Imaging'
    ];

    return (
        <AuthenticatedLayout
            user={auth.user}
            headerTitle="Radiology & Imaging Order"
            breadcrumbs={[
                { label: 'Radiology Registry', url: route('radiology.index') },
                { label: 'New Request', active: true },
            ]}
        >
            <Head title="New Radiology Request" />

            <UnifiedToolbar 
                actions={[
                    { 
                        label: 'SUBMIT REQUEST', 
                        icon: 'fa-check-circle', 
                        onClick: submit,
                        color: 'success',
                        disabled: processing
                    },
                    { 
                        label: 'BACK TO REGISTRY', 
                        icon: 'fa-layer-group', 
                        href: route('radiology.index'),
                        color: 'gray'
                    }
                ]}
            />

            <div className="row justify-content-center">
                <div className="col-lg-8">
                    <div className="card shadow-sm border-0 rounded-3xl bg-white overflow-hidden shadow-hover">
                        <div className="card-body p-4 p-md-5">
                            <form onSubmit={submit}>
                                <div className="row g-4">
                                    <div className="col-md-12">
                                        <label className="extra-small fw-extrabold text-pink-500 text-uppercase tracking-widest mb-2 d-block">Patient Target <span className="text-danger">*</span></label>
                                        <DashboardSelect 
                                            asyncUrl="/patients/search"
                                            value={data.patient_id}
                                            onChange={val => setData('patient_id', val)}
                                            initialLabel={preselected_patient_label}
                                            disabled={!!preselected_patient_id}
                                            className={errors.patient_id ? 'is-invalid' : ''}
                                        />
                                        {errors.patient_id && <div className="invalid-feedback d-block">{errors.patient_id}</div>}
                                    </div>

                                    <div className="col-md-12 mt-4">
                                        <label className="extra-small fw-extrabold text-pink-500 text-uppercase tracking-widest mb-2 d-block">Scan / Imaging Type <span className="text-danger">*</span></label>
                                        <select 
                                            className={`form-select form-select-lg bg-light border-0 rounded-xl fw-bold ${errors.scan_type ? 'is-invalid' : ''}`}
                                            value={data.scan_type}
                                            onChange={e => setData('scan_type', e.target.value)}
                                            required
                                        >
                                            <option value="">Select Scan Type</option>
                                            {scanTypes.map(t => (
                                                <option key={t} value={t}>
                                                    {t}
                                                </option>
                                            ))}
                                        </select>
                                        {errors.scan_type && <div className="invalid-feedback">{errors.scan_type}</div>}
                                    </div>

                                    <div className="col-md-6 mt-4">
                                        <label className="extra-small fw-extrabold text-pink-500 text-uppercase tracking-widest mb-2 d-block">Priority Level <span className="text-danger">*</span></label>
                                        <div className="d-flex gap-4 align-items-center mt-2">
                                            <div className="form-check">
                                                <input className="form-check-input" type="radio" name="priority" id="routine" value="routine" checked={data.priority === 'routine'} onChange={e => setData('priority', e.target.value)} />
                                                <label className="form-check-label fw-bold text-muted" htmlFor="routine">Routine</label>
                                            </div>
                                            <div className="form-check">
                                                <input className="form-check-input" type="radio" name="priority" id="urgent" value="urgent" checked={data.priority === 'urgent'} onChange={e => setData('priority', e.target.value)} />
                                                <label className="form-check-label text-danger fw-bold" htmlFor="urgent">Urgent</label>
                                            </div>
                                            <div className="form-check">
                                                <input className="form-check-input" type="radio" name="priority" id="emergency" value="emergency" checked={data.priority === 'emergency'} onChange={e => setData('priority', e.target.value)} />
                                                <label className="form-check-label text-danger-emphasis fw-extrabold" htmlFor="emergency">Emergency</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="col-md-12 mt-4">
                                        <label className="extra-small fw-extrabold text-pink-500 text-uppercase tracking-widest mb-2 d-block">Clinical Indications</label>
                                        <textarea 
                                            className="form-control bg-light border-0 rounded-2xl p-4 fw-medium" 
                                            rows="3" 
                                            value={data.clinical_indication}
                                            onChange={e => setData('clinical_indication', e.target.value)}
                                            placeholder="Chief complaint, gestational age, history or clinical findings indicating this scan..."
                                        />
                                        {errors.clinical_indication && <div className="text-danger mt-1 small">{errors.clinical_indication}</div>}
                                    </div>

                                    <div className="col-md-12 mt-4">
                                        <label className="extra-small fw-extrabold text-pink-500 text-uppercase tracking-widest mb-2 d-block">Scan details / Special Instructions</label>
                                        <textarea 
                                            className="form-control bg-light border-0 rounded-2xl p-4 fw-medium" 
                                            rows="3" 
                                            value={data.scan_details}
                                            onChange={e => setData('scan_details', e.target.value)}
                                            placeholder="Specific structures to evaluate, follicular scan days, etc."
                                        />
                                        {errors.scan_details && <div className="text-danger mt-1 small">{errors.scan_details}</div>}
                                    </div>
                                </div>

                                <p className="mt-5 text-center text-muted extra-small fw-bold text-uppercase opacity-50">
                                    <i className="fas fa-info-circle me-1"></i> A notification will be dispatched to the radiology & imaging team.
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
