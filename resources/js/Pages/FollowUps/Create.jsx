import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import FormSection from '@/Components/FormSection';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import FormField from '@/Components/FormField';
import DashboardSelect from '@/Components/DashboardSelect';
import { useEffect, useMemo, useState } from 'react';
import { formatDateOnly } from '@/Utils/dateUtils';

export default function Create({ consultation, recentConsultations, followUpTypes, preselected_consultation_id, default_reason }) {
    const consultations = recentConsultations?.data || recentConsultations || [];

    const consultationOptions = useMemo(() =>
        consultations.map((c) => ({
            value: String(c.consultation_id),
            label: `#${c.consultation_id} — ${c.patient?.user?.first_name || ''} ${c.patient?.user?.last_name || ''} (${formatDateOnly(c.consultation_date)})`,
            patient_id: c.patient_id,
        })),
    [consultations]);

    const typeOptions = Object.entries(followUpTypes || {}).map(([value, label]) => ({ label, value }));

    const { data, setData, post, processing, errors } = useForm({
        patient_id: consultation?.patient_id || '',
        consultation_id: preselected_consultation_id || consultation?.consultation_id || '',
        follow_up_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10),
        follow_up_type: 'general',
        reason: default_reason || '',
        status: 'scheduled',
        notes: '',
    });

    const [selectedConsultation, setSelectedConsultation] = useState(consultation);

    useEffect(() => {
        if (consultation) {
            setSelectedConsultation(consultation);
            setData('patient_id', consultation.patient_id);
            setData('consultation_id', consultation.consultation_id);
            if (default_reason) {
                setData('reason', default_reason);
            }
        }
    }, [consultation, default_reason]);

    const handleConsultationChange = (consultationId) => {
        const found = consultations.find((c) => String(c.consultation_id) === String(consultationId));
        setSelectedConsultation(found || null);
        setData('consultation_id', consultationId);
        setData('patient_id', found?.patient_id || '');
    };

    return (
        <AuthenticatedLayout
            headerTitle="Schedule Follow-up"
            breadcrumbs={[
                { label: 'Follow-ups', url: route('follow-ups.index') },
                { label: 'Schedule', active: true },
            ]}
        >
            <Head title="Schedule Follow-up" />

            <form onSubmit={(e) => { e.preventDefault(); post(route('follow-ups.store')); }}>
                <div className="row g-4">
                    <div className="col-lg-8">
                        <FormSection title="Follow-up Details" icon="fas fa-calendar-check" headerClassName="bg-primary text-white p-3">
                            <div className="row g-3">
                                <FormField label="Consultation *" className="col-12" error={errors.consultation_id}>
                                    <DashboardSelect
                                        options={consultationOptions}
                                        value={String(data.consultation_id)}
                                        onChange={handleConsultationChange}
                                        placeholder="Select consultation..."
                                    />
                                </FormField>

                                <FormField label="Follow-up Date *" className="col-md-6" error={errors.follow_up_date}>
                                    <input type="date" className="form-control" value={data.follow_up_date}
                                        onChange={(e) => setData('follow_up_date', e.target.value)} required />
                                </FormField>

                                <FormField label="Type" className="col-md-6" error={errors.follow_up_type}>
                                    <DashboardSelect
                                        options={typeOptions}
                                        value={data.follow_up_type}
                                        onChange={(val) => setData('follow_up_type', val)}
                                    />
                                </FormField>

                                <FormField label="Reason *" className="col-12" error={errors.reason}>
                                    <textarea className="form-control" rows="3" value={data.reason}
                                        onChange={(e) => setData('reason', e.target.value)} required
                                        placeholder="Clinical reason for follow-up visit..." />
                                </FormField>

                                <FormField label="Notes" className="col-12" error={errors.notes}>
                                    <textarea className="form-control" rows="2" value={data.notes}
                                        onChange={(e) => setData('notes', e.target.value)} />
                                </FormField>
                            </div>
                        </FormSection>
                    </div>

                    {selectedConsultation && (
                        <div className="col-lg-4">
                            <div className="card border-0 shadow-sm rounded-4">
                                <div className="card-body p-4">
                                    <h6 className="fw-extrabold text-muted extra-small text-uppercase tracking-widest mb-3">Consultation</h6>
                                    <div className="fw-bold mb-1">
                                        {selectedConsultation.patient?.user?.first_name} {selectedConsultation.patient?.user?.last_name}
                                    </div>
                                    <div className="small text-muted mb-2">#{selectedConsultation.consultation_id}</div>
                                    {selectedConsultation.diagnosis && (
                                        <div className="small"><span className="text-muted">Diagnosis:</span> {selectedConsultation.diagnosis}</div>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                <UnifiedToolbar
                    actions={[
                        { label: 'SCHEDULE', icon: 'fa-check', onClick: () => post(route('follow-ups.store')), color: 'success', disabled: processing },
                        { label: 'CANCEL', icon: 'fa-times', href: route('follow-ups.index'), color: 'gray' },
                    ]}
                />
            </form>
        </AuthenticatedLayout>
    );
}
