import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import FormSection from '@/Components/FormSection';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import FormField from '@/Components/FormField';
import DashboardSelect from '@/Components/DashboardSelect';

export default function Edit({ followUp, followUpTypes }) {
    const typeOptions = Object.entries(followUpTypes || {}).map(([value, label]) => ({ label, value }));
    const statusOptions = [
        { label: 'Scheduled', value: 'scheduled' },
        { label: 'Completed', value: 'completed' },
        { label: 'Cancelled', value: 'cancelled' },
        { label: 'No Show', value: 'no_show' },
    ];

    const { data, setData, put, processing, errors } = useForm({
        follow_up_date: followUp.follow_up_date,
        follow_up_type: followUp.follow_up_type || 'general',
        reason: followUp.reason,
        status: followUp.status,
        notes: followUp.notes || '',
    });

    return (
        <AuthenticatedLayout
            headerTitle="Edit Follow-up"
            breadcrumbs={[
                { label: 'Follow-ups', url: route('follow-ups.index') },
                { label: `#${followUp.follow_up_id}`, url: route('follow-ups.show', followUp.follow_up_id) },
                { label: 'Edit', active: true },
            ]}
        >
            <Head title="Edit Follow-up" />

            <form onSubmit={(e) => { e.preventDefault(); put(route('follow-ups.update', followUp.follow_up_id)); }}>
                <FormSection title="Update Follow-up" icon="fas fa-edit" headerClassName="bg-primary text-white p-3">
                    <div className="row g-3">
                        <FormField label="Follow-up Date *" className="col-md-6" error={errors.follow_up_date}>
                            <input type="date" className="form-control" value={data.follow_up_date}
                                onChange={(e) => setData('follow_up_date', e.target.value)} required />
                        </FormField>
                        <FormField label="Status *" className="col-md-6" error={errors.status}>
                            <DashboardSelect options={statusOptions} value={data.status} onChange={(val) => setData('status', val)} />
                        </FormField>
                        <FormField label="Type" className="col-md-6" error={errors.follow_up_type}>
                            <DashboardSelect options={typeOptions} value={data.follow_up_type} onChange={(val) => setData('follow_up_type', val)} />
                        </FormField>
                        <FormField label="Reason *" className="col-12" error={errors.reason}>
                            <textarea className="form-control" rows="3" value={data.reason}
                                onChange={(e) => setData('reason', e.target.value)} required />
                        </FormField>
                        <FormField label="Notes" className="col-12" error={errors.notes}>
                            <textarea className="form-control" rows="2" value={data.notes}
                                onChange={(e) => setData('notes', e.target.value)} />
                        </FormField>
                    </div>
                </FormSection>

                <UnifiedToolbar
                    actions={[
                        { label: 'SAVE CHANGES', icon: 'fa-save', onClick: () => put(route('follow-ups.update', followUp.follow_up_id)), color: 'success', disabled: processing },
                        { label: 'CANCEL', icon: 'fa-times', href: route('follow-ups.show', followUp.follow_up_id), color: 'gray' },
                    ]}
                />
            </form>
        </AuthenticatedLayout>
    );
}
