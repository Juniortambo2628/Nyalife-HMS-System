import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import FormSection from '@/Components/FormSection';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import FormField from '@/Components/FormField';
import DashboardSelect from '@/Components/DashboardSelect';

export default function Form({ department, departmentTypes }) {
    const isEdit = !!department;

    const { data, setData, post, put, processing, errors } = useForm({
        department_name: department?.department_name || '',
        code: department?.code || '',
        type: department?.type || 'clinical',
        description: department?.description || '',
        head_name: department?.head_name || '',
        head_position: department?.head_position || '',
        is_active: department?.is_active ?? true,
    });

    const typeOptions = Object.entries(departmentTypes || {}).map(([value, label]) => ({ label, value }));

    const submit = (e) => {
        e.preventDefault();
        if (isEdit) {
            put(route('departments.update', department.department_id));
        } else {
            post(route('departments.store'));
        }
    };

    return (
        <AuthenticatedLayout
            headerTitle={isEdit ? 'Edit Department' : 'Create Department'}
            breadcrumbs={[
                { label: 'Departments', url: route('departments.index') },
                { label: isEdit ? department.department_name : 'Create', active: true },
            ]}
        >
            <Head title={isEdit ? 'Edit Department' : 'Create Department'} />

            <form onSubmit={submit}>
                <FormSection title="Department Information" icon="fas fa-building" headerClassName="bg-primary text-white p-3">
                    <div className="row g-3">
                        <FormField label="Department Name *" className="col-md-8" error={errors.department_name}>
                            <input type="text" className="form-control" value={data.department_name}
                                onChange={(e) => setData('department_name', e.target.value)} required />
                        </FormField>
                        <FormField label="Code" className="col-md-4" error={errors.code}>
                            <input type="text" className="form-control font-mono" maxLength={10} value={data.code}
                                onChange={(e) => setData('code', e.target.value.toUpperCase())} placeholder="e.g. OPD" />
                        </FormField>
                        <FormField label="Type" className="col-md-6" error={errors.type}>
                            <DashboardSelect options={typeOptions} value={data.type} onChange={(val) => setData('type', val)} />
                        </FormField>
                        <FormField label="Status" className="col-md-6">
                            <div className="form-check form-switch mt-2">
                                <input className="form-check-input" type="checkbox" id="is_active" checked={data.is_active}
                                    onChange={(e) => setData('is_active', e.target.checked)} />
                                <label className="form-check-label fw-bold" htmlFor="is_active">Active department</label>
                            </div>
                        </FormField>
                        <FormField label="Description" className="col-12" error={errors.description}>
                            <textarea className="form-control" rows="3" value={data.description}
                                onChange={(e) => setData('description', e.target.value)} />
                        </FormField>
                        <FormField label="Department Head Name" className="col-md-6" error={errors.head_name}>
                            <input type="text" className="form-control" value={data.head_name}
                                onChange={(e) => setData('head_name', e.target.value)} />
                        </FormField>
                        <FormField label="Head Position" className="col-md-6" error={errors.head_position}>
                            <input type="text" className="form-control" value={data.head_position}
                                onChange={(e) => setData('head_position', e.target.value)} placeholder="e.g. Head of Obstetrics" />
                        </FormField>
                    </div>
                </FormSection>

                <UnifiedToolbar
                    actions={[
                        { label: isEdit ? 'SAVE CHANGES' : 'CREATE DEPARTMENT', icon: 'fa-check', onClick: submit, color: 'success', disabled: processing },
                        { label: 'CANCEL', icon: 'fa-times', href: route('departments.index'), color: 'gray' },
                    ]}
                />
            </form>
        </AuthenticatedLayout>
    );
}
