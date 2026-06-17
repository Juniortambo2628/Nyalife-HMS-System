import React from 'react';
import { useForm, usePage } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import FormField from '@/Components/FormField';
import DashboardSelect from '@/Components/DashboardSelect';
import DashboardPanel from '@/Components/DashboardPanel';

export default function UpdateProfessionalProfileForm({ className = '' }) {
    const { staff, departments = [] } = usePage().props;

    if (!staff) return null;

    const departmentOptions = [
        { label: '— Not assigned —', value: '' },
        ...departments.map((d) => ({
            label: d.department_name,
            value: String(d.department_id),
        })),
    ];

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
        specialization: staff.specialization || '',
        department_id: staff.department_id ? String(staff.department_id) : '',
        license_number: staff.license_number || '',
    });

    const submit = (e) => {
        e.preventDefault();
        patch(route('profile.update'), { preserveScroll: true });
    };

    return (
        <DashboardPanel
            title="Professional profile"
            icon="fa-briefcase"
            headerVariant="gradient"
            className={`nyl-detail-panel ${className}`.trim()}
            bodyClassName="p-4"
        >
            <p className="text-muted small mb-4">
                Update your clinical credentials and department assignment visible to colleagues.
            </p>

            <form onSubmit={submit}>
                <div className="row g-4">
                    <FormField label="Specialization" error={errors.specialization}>
                        <TextInput
                            id="specialization"
                            className="form-control bg-light border-0 rounded-xl"
                            value={data.specialization}
                            onChange={(e) => setData('specialization', e.target.value)}
                            placeholder="e.g. Obstetrics, General practice"
                        />
                    </FormField>

                    <FormField label="Department" error={errors.department_id}>
                        <DashboardSelect
                            options={departmentOptions}
                            value={data.department_id}
                            onChange={(val) => setData('department_id', val || '')}
                            placeholder="Select department…"
                        />
                    </FormField>

                    <FormField label="Medical license number" error={errors.license_number} className="col-md-6">
                        <TextInput
                            id="license_number"
                            className="form-control bg-light border-0 rounded-xl"
                            value={data.license_number}
                            onChange={(e) => setData('license_number', e.target.value)}
                            placeholder="MED-123456"
                        />
                    </FormField>
                </div>

                <div className="d-flex flex-wrap align-items-center gap-3 pt-4 mt-2 border-top">
                    <PrimaryButton disabled={processing} className="rounded-pill px-4 fw-bold">
                        {processing ? 'Updating…' : 'Update credentials'}
                    </PrimaryButton>
                    {recentlySuccessful && (
                        <span className="text-success extra-small fw-bold">
                            <i className="fas fa-check-circle me-1" />
                            Staff profile updated
                        </span>
                    )}
                </div>
            </form>
        </DashboardPanel>
    );
}
