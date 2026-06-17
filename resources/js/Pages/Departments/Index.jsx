import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import DashboardSearch from '@/Components/DashboardSearch';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import TableActions from '@/Components/TableActions';
import StatusBadge from '@/Components/StatusBadge';
import { TableCellPrimary, TableCellStack } from '@/Components/TableCells';
import StatCardGrid from '@/Components/StatCardGrid';

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

    const statItems = useMemo(() => [
        {
            label: 'Total Departments',
            value: stats?.total ?? 0,
            icon: 'fa-building',
            color: 'primary',
        },
        {
            label: 'Active',
            value: stats?.active ?? 0,
            icon: 'fa-check-circle',
            color: 'success',
        },
        {
            label: 'Clinical',
            value: stats?.clinical ?? 0,
            icon: 'fa-stethoscope',
            color: 'info',
        },
    ], [stats]);

    return (
        <AuthenticatedLayout headerTitle="Departments">
            <Head title="Departments" />

            <StatCardGrid items={statItems} cols={3} />

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
