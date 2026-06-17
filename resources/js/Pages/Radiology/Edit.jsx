import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Edit({ radRequest, preselected_patient_id, preselected_patient_label, auth }) {
    const { data, setData, put, processing, errors } = useForm({
        patient_id: radRequest.patient_id,
        scan_type: radRequest.scan_type || '',
        priority: radRequest.priority || 'routine',
        clinical_indication: radRequest.clinical_indication || '',
        scan_details: radRequest.scan_details || '',
    });

    const submit = (e) => {
        if (e) e.preventDefault();
        put(route('radiology.update', radRequest.request_id));
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
            headerTitle="Edit Radiology & Imaging Order"
            breadcrumbs={[
                { label: 'Radiology Registry', url: route('radiology.index') },
                { label: radRequest.request_number, url: route('radiology.show', radRequest.request_id) },
                { label: 'Edit', active: true },
            ]}
        >
            <Head title="Edit Radiology Request" />

            <UnifiedToolbar 
                actions={[
                    { 
                        label: 'SAVE CHANGES', 
                        icon: 'fa-save', 
                        onClick: submit,
                        color: 'success',
                        disabled: processing
                    },
                    { 
                        label: 'DISCARD', 
                        icon: 'fa-times', 
                        href: route('radiology.show', radRequest.request_id),
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
                                        <label className="extra-small fw-extrabold text-pink-500 text-uppercase tracking-widest mb-2 d-block">Patient Target</label>
                                        <input 
                                            type="text" 
                                            className="form-control form-control-lg bg-light border-0 rounded-xl fw-bold text-muted"
                                            value={preselected_patient_label || ''}
                                            disabled
                                        />
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
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
