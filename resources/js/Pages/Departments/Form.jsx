import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import FormSection from '@/Components/FormSection';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import FormField from '@/Components/FormField';
import DashboardSelect from '@/Components/DashboardSelect';

export default function Form({ department, departmentTypes, staffUsers = [] }) {
    const isEdit = !!department;

    const { data, setData, post, put, processing, errors } = useForm({
        department_name: department?.department_name || '',
        code: department?.code || '',
        type: department?.type || 'clinical',
        description: department?.description || '',
        head_name: department?.head_name || '',
        head_position: department?.head_position || '',
        is_active: department?.is_active ?? true,
        assigned_user_ids: department?.staff_members?.map((sm) => sm.user_id) || [],
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

    // Filter staff members currently checked/assigned to this department
    const assignedStaff = staffUsers.filter((u) => data.assigned_user_ids.includes(u.user_id));

    return (
        <AuthenticatedLayout
            headerTitle={isEdit ? 'Edit Department' : 'Create Department'}
            breadcrumbs={[
                { label: 'Departments', url: route('departments.index') },
                { label: isEdit ? department.department_name : 'Create', active: true },
            ]}
        >
            <Head title={isEdit ? 'Edit Department' : 'Create Department'} />

            <form onSubmit={submit} className="pb-5">
                <FormSection
                    title="Department Information"
                    icon="fas fa-building"
                    headerClassName="bg-primary text-white p-3"
                >
                    <div className="row g-3">
                        <FormField label="Department Name *" className="col-md-8" error={errors.department_name}>
                            <input
                                type="text"
                                className="form-control"
                                value={data.department_name}
                                onChange={(e) => setData('department_name', e.target.value)}
                                required
                            />
                        </FormField>
                        <FormField label="Code" className="col-md-4" error={errors.code}>
                            <input
                                type="text"
                                className="form-control font-mono"
                                maxLength={10}
                                value={data.code}
                                onChange={(e) => setData('code', e.target.value.toUpperCase())}
                                placeholder="e.g. OPD"
                            />
                        </FormField>
                        <FormField label="Type" className="col-md-6" error={errors.type}>
                            <DashboardSelect
                                options={typeOptions}
                                value={data.type}
                                onChange={(val) => setData('type', val)}
                            />
                        </FormField>
                        <FormField label="Status" className="col-md-6">
                            <div className="form-check form-switch mt-2">
                                <input
                                    className="form-check-input"
                                    type="checkbox"
                                    id="is_active"
                                    checked={data.is_active}
                                    onChange={(e) => setData('is_active', e.target.checked)}
                                />
                                <label className="form-check-label fw-bold" htmlFor="is_active">
                                    Active department
                                </label>
                            </div>
                        </FormField>
                        <FormField label="Description" className="col-12" error={errors.description}>
                            <textarea
                                className="form-control"
                                rows="3"
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                            />
                        </FormField>

                        <FormField label="Department Head" className="col-md-6" error={errors.head_name}>
                            <select
                                className="form-select"
                                value={data.head_name}
                                onChange={(e) => {
                                    const name = e.target.value;
                                    const matchedUser = staffUsers.find((u) => u.name === name);
                                    setData((d) => ({
                                        ...d,
                                        head_name: name,
                                        head_position: matchedUser
                                            ? matchedUser.role.replace('_', ' ').charAt(0).toUpperCase() +
                                              matchedUser.role.replace('_', ' ').slice(1)
                                            : d.head_position,
                                    }));
                                }}
                            >
                                <option value="">Select Department Head...</option>
                                {assignedStaff.map((u) => (
                                    <option key={u.user_id} value={u.name}>
                                        {u.name} ({u.role.replace('_', ' ')})
                                    </option>
                                ))}
                            </select>
                            {assignedStaff.length === 0 && (
                                <div className="form-text text-muted small mt-1">
                                    Assign staff members below to choose a head.
                                </div>
                            )}
                        </FormField>

                        <FormField label="Head Position" className="col-md-6" error={errors.head_position}>
                            <input
                                type="text"
                                className="form-control"
                                value={data.head_position}
                                onChange={(e) => setData('head_position', e.target.value)}
                                placeholder="e.g. Head of Obstetrics"
                            />
                        </FormField>
                    </div>
                </FormSection>

                <FormSection
                    title="Assign Staff Members"
                    icon="fas fa-users"
                    className="mt-4"
                    headerClassName="bg-primary text-white p-3"
                >
                    <div className="row g-3">
                        <div className="col-12">
                            <label className="form-label font-bold text-gray-500 mb-3">
                                Select the staff members to assign to this department:
                            </label>
                            <div className="row g-3">
                                {staffUsers.length > 0 ? (
                                    staffUsers.map((u) => {
                                        const isChecked = data.assigned_user_ids.includes(u.user_id);
                                        const isAssignedElsewhere =
                                            u.department_id && u.department_id !== department?.department_id;
                                        return (
                                            <div key={u.user_id} className="col-md-6 col-lg-4">
                                                <div
                                                    className={`card p-3 border rounded-3 bg-white h-100 transition-all ${isChecked ? 'border-primary shadow-sm bg-light' : ''}`}
                                                >
                                                    <div className="form-check d-flex align-items-start gap-2">
                                                        <input
                                                            className="form-check-input mt-1"
                                                            type="checkbox"
                                                            id={`user-${u.user_id}`}
                                                            checked={isChecked}
                                                            onChange={(e) => {
                                                                const checked = e.target.checked;
                                                                setData((d) => {
                                                                    const ids = checked
                                                                        ? [...d.assigned_user_ids, u.user_id]
                                                                        : d.assigned_user_ids.filter(
                                                                              (id) => id !== u.user_id,
                                                                          );
                                                                    // If unchecked user was the head, clear head_name
                                                                    const headName =
                                                                        d.head_name === u.name && !checked
                                                                            ? ''
                                                                            : d.head_name;
                                                                    return {
                                                                        ...d,
                                                                        assigned_user_ids: ids,
                                                                        head_name: headName,
                                                                    };
                                                                });
                                                            }}
                                                        />
                                                        <label
                                                            className="form-check-label d-flex flex-column"
                                                            htmlFor={`user-${u.user_id}`}
                                                            style={{ cursor: 'pointer' }}
                                                        >
                                                            <span className="fw-bold text-gray-800">{u.name}</span>
                                                            <span
                                                                className="extra-small text-muted text-uppercase fw-semibold"
                                                                style={{ fontSize: '0.75rem' }}
                                                            >
                                                                {u.role.replace('_', ' ')}
                                                            </span>
                                                            {isAssignedElsewhere && (
                                                                <span
                                                                    className="extra-small text-warning font-bold mt-1"
                                                                    style={{ fontSize: '0.7rem' }}
                                                                >
                                                                    <i className="fas fa-exclamation-triangle me-1"></i>{' '}
                                                                    Currently in another department
                                                                </span>
                                                            )}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })
                                ) : (
                                    <div className="col-12 py-4 text-center text-muted">
                                        No staff members found in the system.
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </FormSection>

                <UnifiedToolbar
                    actions={[
                        {
                            label: isEdit ? 'SAVE CHANGES' : 'CREATE DEPARTMENT',
                            icon: 'fa-check',
                            onClick: submit,
                            color: 'success',
                            disabled: processing,
                        },
                        { label: 'CANCEL', icon: 'fa-times', href: route('departments.index'), color: 'gray' },
                    ]}
                />
            </form>
        </AuthenticatedLayout>
    );
}
