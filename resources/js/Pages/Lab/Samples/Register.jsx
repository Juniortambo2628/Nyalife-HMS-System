import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import FormSection from '@/Components/FormSection';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import FormField from '@/Components/FormField';
import DashboardSelect from '@/Components/DashboardSelect';
import { useEffect, useMemo } from 'react';
import { formatPatientId } from '@/Components/PatientTableCell';

export default function Register({
    prefillRequest,
    pendingRequests,
    testTypes,
    sampleTypes,
    preselected_lab_request_id,
}) {
    const requests = pendingRequests?.data || pendingRequests || [];

    const requestOptions = useMemo(() =>
        requests.map((r) => ({
            value: String(r.request_id),
            label: `LAB-${r.request_id} — ${r.patient?.user?.first_name || ''} ${r.patient?.user?.last_name || ''} (${r.test_type?.test_name || 'Test'})`,
            patient_id: r.patient_id,
            test_type_id: r.test_type_id,
        })),
    [requests]);

    const testTypeOptions = useMemo(() =>
        (testTypes || []).map((t) => ({
            value: String(t.test_type_id),
            label: `${t.test_name}${t.category ? ` (${t.category})` : ''}`,
        })),
    [testTypes]);

    const sampleTypeOptions = useMemo(() =>
        Object.entries(sampleTypes || {}).map(([value, label]) => ({ label, value })),
    [sampleTypes]);

    const today = new Date().toISOString().slice(0, 10);
    const nowLocal = new Date().toISOString().slice(0, 16);

    const { data, setData, post, processing, errors } = useForm({
        lab_request_id: preselected_lab_request_id || prefillRequest?.request_id || '',
        patient_id: prefillRequest?.patient_id || '',
        test_type_id: prefillRequest?.test_type_id ? String(prefillRequest.test_type_id) : '',
        sample_type: 'blood',
        collected_date: today,
        collected_at: nowLocal,
        notes: '',
        urgent: false,
    });

    useEffect(() => {
        if (prefillRequest) {
            setData('lab_request_id', prefillRequest.request_id);
            setData('patient_id', prefillRequest.patient_id);
            setData('test_type_id', String(prefillRequest.test_type_id));
        }
    }, [prefillRequest]);

    const handleRequestChange = (requestId) => {
        const found = requests.find((r) => String(r.request_id) === String(requestId));
        setData('lab_request_id', requestId);
        if (found) {
            setData('patient_id', found.patient_id);
            setData('test_type_id', String(found.test_type_id));
        }
    };

    return (
        <AuthenticatedLayout
            headerTitle="Register Lab Sample"
            breadcrumbs={[
                { label: 'Samples', url: route('lab.samples.index') },
                { label: 'Register', active: true },
            ]}
        >
            <Head title="Register Lab Sample" />

            <form onSubmit={(e) => { e.preventDefault(); post(route('lab.samples.store')); }}>
                <div className="row g-4">
                    <div className="col-lg-8">
                        <FormSection title="Sample Details" icon="fas fa-vial" headerClassName="bg-success text-white p-3">
                            <div className="row g-3">
                                <FormField label="Link to Lab Request (optional)" className="col-12" error={errors.lab_request_id}>
                                    <DashboardSelect
                                        options={requestOptions}
                                        value={data.lab_request_id ? String(data.lab_request_id) : ''}
                                        onChange={handleRequestChange}
                                        placeholder="Select pending lab request..."
                                    />
                                </FormField>

                                <FormField label="Test Type *" className="col-md-6" error={errors.test_type_id}>
                                    <DashboardSelect
                                        options={testTypeOptions}
                                        value={data.test_type_id}
                                        onChange={(val) => setData('test_type_id', val)}
                                        placeholder="Select test..."
                                    />
                                </FormField>

                                <FormField label="Sample Type *" className="col-md-6" error={errors.sample_type}>
                                    <DashboardSelect
                                        options={sampleTypeOptions}
                                        value={data.sample_type}
                                        onChange={(val) => setData('sample_type', val)}
                                    />
                                </FormField>

                                <FormField label="Collection Date *" className="col-md-6" error={errors.collected_date}>
                                    <input
                                        type="date"
                                        className="form-control"
                                        value={data.collected_date}
                                        onChange={(e) => setData('collected_date', e.target.value)}
                                        required
                                    />
                                </FormField>

                                <FormField label="Collection Time" className="col-md-6" error={errors.collected_at}>
                                    <input
                                        type="datetime-local"
                                        className="form-control"
                                        value={data.collected_at}
                                        onChange={(e) => setData('collected_at', e.target.value)}
                                    />
                                </FormField>

                                <FormField label="Notes" className="col-12" error={errors.notes}>
                                    <textarea
                                        className="form-control"
                                        rows="3"
                                        value={data.notes}
                                        onChange={(e) => setData('notes', e.target.value)}
                                        placeholder="Collection notes, fasting status, etc."
                                    />
                                </FormField>

                                <div className="col-12">
                                    <div className="form-check">
                                        <input
                                            type="checkbox"
                                            className="form-check-input"
                                            id="urgent"
                                            checked={data.urgent}
                                            onChange={(e) => setData('urgent', e.target.checked)}
                                        />
                                        <label className="form-check-label fw-bold" htmlFor="urgent">
                                            Mark as urgent
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </FormSection>
                    </div>

                    <div className="col-lg-4">
                        <FormSection title="Patient" icon="fas fa-user" headerClassName="bg-primary text-white p-3">
                            {data.patient_id ? (
                                <p className="mb-0 text-muted small">
                                    Patient ID: <span className="fw-bold text-dark">{formatPatientId(data.patient_id)}</span>
                                    {prefillRequest?.patient?.user && (
                                        <span className="d-block mt-2 fw-bold text-dark">
                                            {prefillRequest.patient.user.first_name} {prefillRequest.patient.user.last_name}
                                        </span>
                                    )}
                                </p>
                            ) : (
                                <p className="mb-0 text-muted small">
                                    Select a lab request above to auto-fill the patient, or ensure patient_id is set via a linked request.
                                </p>
                            )}
                            {errors.patient_id && <div className="text-danger small mt-2">{errors.patient_id}</div>}
                        </FormSection>
                    </div>
                </div>

                <UnifiedToolbar
                    actions={[
                        { label: processing ? 'SAVING...' : 'REGISTER SAMPLE', icon: 'fa-check', type: 'submit', color: 'success' },
                        { label: 'CANCEL', icon: 'fa-times', href: route('lab.samples.index'), color: 'gray' },
                    ]}
                />
            </form>
        </AuthenticatedLayout>
    );
}
