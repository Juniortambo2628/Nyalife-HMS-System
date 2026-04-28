import { Head, Link, useForm, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
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

            <PageHeader 
                title="Laboratory Order"
                breadcrumbs={[
                    { label: 'Lab Registry', url: route('lab.index') },
                    { label: 'New Request', active: true }
                ]}
            />

            <UnifiedToolbar 
                actions={[
                    { 
                        label: 'SUBMIT REQUEST', 
                        icon: 'fa-check-circle', 
                        onClick: submit,
                        color: 'success'
                    },
                    { 
                        label: 'BACK TO REGISTRY', 
                        icon: 'fa-layer-group', 
                        href: route('lab.index'),
                        color: 'gray'
                    }
                ]}
            />

                <div className="row justify-content-center">
                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0">
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
                                            <label className="extra-small fw-extrabold text-pink-500 text-uppercase tracking-widest mb-2 d-block">Investigation Type <span className="text-danger">*</span></label>
                                            <select 
                                                className={`form-select form-select-lg bg-light border-0 rounded-xl fw-bold ${errors.test_type_id ? 'is-invalid' : ''}`}
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

                                        <div className="col-md-6 mt-4">
                                            <label className="extra-small fw-extrabold text-pink-500 text-uppercase tracking-widest mb-2 d-block">Priority Level <span className="text-danger">*</span></label>
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

                                        <div className="col-md-12 mt-4">
                                            <label className="extra-small fw-extrabold text-pink-500 text-uppercase tracking-widest mb-2 d-block">Clinical Indications / Notes</label>
                                            <textarea 
                                                className="form-control bg-light border-0 rounded-2xl p-4 fw-medium" 
                                                rows="4" 
                                                value={data.notes}
                                                onChange={e => setData('notes', e.target.value)}
                                                placeholder="Enter clinical reasons or specific parameters to check..."
                                            />
                                        </div>
                                    </div>

                                    <p className="mt-4 text-center text-muted extra-small fw-bold text-uppercase opacity-50">
                                        <i className="fas fa-info-circle me-1"></i> A notification will be dispatched to the laboratory team.
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
        </AuthenticatedLayout>
    );
}
