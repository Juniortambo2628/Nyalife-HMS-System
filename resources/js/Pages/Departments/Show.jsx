import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Show({ department }) {
    const staff = department.staff_members?.data || department.staff_members || [];

    const handleDelete = () => {
        if (confirm(`Delete department "${department.department_name}"? This cannot be undone.`)) {
            router.delete(route('departments.destroy', department.department_id));
        }
    };

    const toggleActive = () => {
        router.post(route('departments.toggle', department.department_id));
    };

    return (
        <AuthenticatedLayout
            headerTitle={department.department_name}
            breadcrumbs={[
                { label: 'Departments', url: route('departments.index') },
                { label: department.department_name, active: true },
            ]}
        >
            <Head title={department.department_name} />

            <div className="row g-4">
                <div className="col-lg-8">
                    <div className="card border-0 shadow-sm rounded-4">
                        <div className="card-body p-5">
                            <div className="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    {department.code && (
                                        <span className="badge bg-light text-primary font-mono mb-2">
                                            {department.code}
                                        </span>
                                    )}
                                    <h3 className="fw-extrabold text-gray-900 mb-1">{department.department_name}</h3>
                                    <span className="badge bg-primary-subtle text-primary">
                                        {department.type_label}
                                    </span>
                                </div>
                                <span
                                    className={`badge rounded-pill px-3 py-2 ${department.is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'}`}
                                >
                                    {department.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </div>

                            {department.description && <p className="text-muted mb-4">{department.description}</p>}

                            {(department.head_name || department.head_position) && (
                                <div className="p-4 rounded-4 bg-gray-50 border mb-4">
                                    <h6 className="extra-small fw-extrabold text-muted text-uppercase mb-2">
                                        Department Head
                                    </h6>
                                    <div className="fw-bold">{department.head_name || '—'}</div>
                                    {department.head_position && (
                                        <div className="small text-muted">{department.head_position}</div>
                                    )}
                                </div>
                            )}

                            <h6 className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-3">
                                Assigned Staff ({staff.length})
                            </h6>
                            {staff.length === 0 ? (
                                <p className="text-muted small mb-0">No staff assigned to this department yet.</p>
                            ) : (
                                <ul className="list-group list-group-flush">
                                    {staff.map((member) => (
                                        <li
                                            key={member.staff_id}
                                            className="list-group-item px-0 d-flex justify-content-between"
                                        >
                                            <span className="fw-bold">
                                                {member.user?.first_name} {member.user?.last_name}
                                            </span>
                                            <span className="text-muted small">
                                                {member.position || member.specialization || 'Staff'}
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            <UnifiedToolbar
                actions={[
                    {
                        label: department.is_active ? 'DEACTIVATE' : 'ACTIVATE',
                        icon: 'fa-power-off',
                        onClick: toggleActive,
                        color: department.is_active ? 'warning' : 'success',
                    },
                    { label: 'EDIT', icon: 'fa-edit', href: route('departments.edit', department.department_id) },
                    staff.length === 0 && { label: 'DELETE', icon: 'fa-trash', onClick: handleDelete, color: 'danger' },
                    { label: 'ALL DEPARTMENTS', icon: 'fa-list', href: route('departments.index'), color: 'gray' },
                ].filter(Boolean)}
            />
        </AuthenticatedLayout>
    );
}
