import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import DashboardSelect from '@/Components/DashboardSelect';

export default function Create({ testTypes, preselected_patient_id, preselected_patient_label, consultation_id, auth }) {
    const { data, setData, post, processing, errors } = useForm({
        patient_id: preselected_patient_id || '',
        consultation_id: consultation_id || '',
        test_type_id: '',
        priority: 'routine',
        notes: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('lab.store'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header="Create Lab Request"
        >
            <Head title="New Lab Request" />

            <div className="container-fluid lab-page px-0">
                <div className="row mb-4">
                    <div className="col-12 d-flex justify-content-between align-items-center">
                        <h2 className="mb-0">New Laboratory Order</h2>
                        <Link href={route('lab.index')} className="btn btn-outline-secondary">
                            <i className="fas fa-arrow-left me-2"></i>Back to Registry
                        </Link>
                    </div>
                </div>

                <div className="row justify-content-center">
                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0">
                            <div className="card-body p-4 p-md-5">
                                <form onSubmit={submit}>
                                    <div className="row g-3">
                                        <div className="col-md-12 mb-3">
                                            <label className="form-label fw-bold text-primary small text-uppercase">Patient <span className="text-danger">*</span></label>
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

                                        <div className="col-md-12 mb-3">
                                            <label className="form-label fw-bold text-primary small text-uppercase">Laboratory Test <span className="text-danger">*</span></label>
                                            <select 
                                                className={`form-select ${errors.test_type_id ? 'is-invalid' : ''}`}
                                                value={data.test_type_id}
                                                onChange={e => setData('test_type_id', e.target.value)}
                                                required
                                            >
                                                <option value="">Select Test Type</option>
                                                {testTypes.map(t => (
                                                    <option key={t.test_type_id} value={t.test_type_id}>
                                                        {t.test_name} - {t.category} (Ksh {t.price})
                                                    </option>
                                                ))}
                                            </select>
                                            {errors.test_type_id && <div className="invalid-feedback">{errors.test_type_id}</div>}
                                        </div>

                                        <div className="col-md-6 mb-3">
                                            <label className="form-label fw-bold text-primary small text-uppercase">Priority <span className="text-danger">*</span></label>
                                            <div className="d-flex gap-3">
                                                <div className="form-check">
                                                    <input className="form-check-input" type="radio" name="priority" id="routine" value="routine" checked={data.priority === 'routine'} onChange={e => setData('priority', e.target.value)} />
                                                    <label className="form-check-label" htmlFor="routine">Routine</label>
                                                </div>
                                                <div className="form-check">
                                                    <input className="form-check-input" type="radio" name="priority" id="urgent" value="urgent" checked={data.priority === 'urgent'} onChange={e => setData('priority', e.target.value)} />
                                                    <label className="form-check-label text-danger" htmlFor="urgent">Urgent</label>
                                                </div>
                                                <div className="form-check">
                                                    <input className="form-check-input" type="radio" name="priority" id="stat" value="stat" checked={data.priority === 'stat'} onChange={e => setData('priority', e.target.value)} />
                                                    <label className="form-check-label text-dark fw-bold" htmlFor="stat">STAT</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="col-md-12 mb-4">
                                            <label className="form-label fw-bold text-primary small text-uppercase">Clinical Notes / Indications</label>
                                            <textarea 
                                                className="form-control" 
                                                rows="4" 
                                                value={data.notes}
                                                onChange={e => setData('notes', e.target.value)}
                                                placeholder="Enter clinical reasons or specific parameters to check..."
                                            />
                                        </div>
                                    </div>

                                    <div className="d-grid">
                                        <button type="submit" disabled={processing} className="btn btn-primary btn-lg shadow">
                                            {processing ? 'Submitting Request...' : 'Submit Laboratory Request'}
                                        </button>
                                    </div>
                                    <p className="mt-3 text-center text-muted small">
                                        <i className="fas fa-info-circle me-1"></i> A notification will be sent to the laboratory technician.
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
