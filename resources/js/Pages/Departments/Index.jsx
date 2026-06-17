import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import DashboardSearch from '@/Components/DashboardSearch';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import TableActions from '@/Components/TableActions';
import StatusBadge from '@/Components/StatusBadge';
import { TableCellPrimary, TableCellStack } from '@/Components/TableCells';

export default function Index({ departments, filters, stats, departmentTypes }) {
    const [search, setSearch] = useState(filters.search || '');
    const [type, setType] = useState(filters.type || '');
    const [status, setStatus] = useState(filters.status || '');

    const applyFilters = (searchValue, typeValue = type, statusValue = status) => {
        router.get(route('departments.index'), {
            search: searchValue,
            type: typeValue,
            status: statusValue,
        }, { preserveState: true, replace: true });
    };

    const typeOptions = useMemo(() =>
        Object.entries(departmentTypes || {}).map(([value, label]) => ({ label, value })),
    [departmentTypes]);

    const toggleActive = (id) => {
        router.post(route('departments.toggle', id), {}, { preserveScroll: true });
    };

    const columns = useMemo(() => [
        {
            header: 'Department',
            accessorKey: 'department_name',
            cell: info => (
                <TableCellStack
                    primary={info.getValue()}
                    secondary={info.row.original.code || null}
                />
            ),
        },
        {
            header: 'Type',
            accessorKey: 'type_label',
            cell: info => <TableCellPrimary className="text-uppercase">{info.getValue()}</TableCellPrimary>,
        },
        {
            header: 'Staff',
            accessorKey: 'staff_count',
            cell: info => <TableCellPrimary>{info.getValue() ?? 0}</TableCellPrimary>,
        },
        {
            header: 'Head',
            accessorKey: 'head_name',
            cell: info => info.getValue()
                ? <TableCellPrimary>{info.getValue()}</TableCellPrimary>
                : <TableCellPrimary className="text-muted">—</TableCellPrimary>,
        },
        {
            header: 'Status',
            accessorKey: 'is_active',
            cell: info => (
                <button
                    type="button"
                    onClick={() => toggleActive(info.row.original.department_id)}
                    className="btn btn-link p-0 border-0 text-decoration-none"
                >
                    <StatusBadge status={info.getValue() ? 'active' : 'inactive'} />
                </button>
            ),
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: info => (
                <TableActions actions={[
                    {
                        icon: 'fa-eye',
                        label: 'View department',
                        href: route('departments.show', info.row.original.department_id),
                    },
                    {
                        icon: 'fa-edit',
                        label: 'Edit department',
                        href: route('departments.edit', info.row.original.department_id),
                    },
                ]} />
            ),
        },
    ], []);

    return (
        <AuthenticatedLayout headerTitle="Departments">
            <Head title="Departments" />

            <div className="row g-3 mb-4">
                <div className="col-md-4">
                    <div className="card border-0 shadow-sm rounded-4 bg-primary text-white">
                        <div className="card-body p-4">
                            <div className="extra-small fw-bold opacity-75 text-uppercase tracking-widest mb-1">Total Departments</div>
                            <div className="h3 fw-extrabold mb-0">{stats?.total ?? 0}</div>
                        </div>
                    </div>
                </div>
                <div className="col-md-4">
                    <div className="card border-0 shadow-sm rounded-4 bg-white">
                        <div className="card-body p-4">
                            <div className="extra-small fw-bold text-muted text-uppercase tracking-widest mb-1">Active</div>
                            <div className="h3 fw-extrabold text-success mb-0">{stats?.active ?? 0}</div>
                        </div>
                    </div>
                </div>
                <div className="col-md-4">
                    <div className="card border-0 shadow-sm rounded-4 bg-white">
                        <div className="card-body p-4">
                            <div className="extra-small fw-bold text-muted text-uppercase tracking-widest mb-1">Clinical</div>
                            <div className="h3 fw-extrabold text-gray-900 mb-0">{stats?.clinical ?? 0}</div>
                        </div>
                    </div>
                </div>
            </div>

            <UnifiedToolbar
                filterGroups={[
                    {
                        id: 'type',
                        label: 'Type',
                        emptyLabel: 'All types',
                        value: type,
                        onChange: (val) => { setType(val || ''); applyFilters(search, val || '', status); },
                        options: typeOptions.filter((o) => o.value !== ''),
                    },
                    {
                        id: 'status',
                        label: 'Status',
                        emptyLabel: 'All statuses',
                        value: status,
                        onChange: (val) => { setStatus(val || ''); applyFilters(search, type, val || ''); },
                        options: [
                            { label: 'Active', value: 'active' },
                            { label: 'Inactive', value: 'inactive' },
                        ],
                    },
                ]}
                actions={[
                    { label: 'ADD DEPARTMENT', icon: 'fa-plus-circle', href: route('departments.create'), color: 'success' },
                ]}
            />

            <DashboardSearch
                placeholder="Search departments..."
                value={search}
                onChange={setSearch}
                onSubmit={(val) => applyFilters(val, type, status)}
            />

            <RegistryTablePanel
                title="Department registry"
                icon="fa-building"
                data={departments.data}
                columns={columns}
                pagination={departments}
                emptyMessage="No departments configured yet."
                idField="department_id"
            />
        </AuthenticatedLayout>
    );
}
