import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import DashboardSearch from '@/Components/DashboardSearch';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import StatusBadge from '@/Components/StatusBadge';
import StatCardGrid from '@/Components/StatCardGrid';
import TableActions from '@/Components/TableActions';
import PriorityBadge from '@/Components/PriorityBadge';
import { RefBadge, TableCellPrimary, TableCellSub } from '@/Components/TableCells';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import PatientTableCell from '@/Components/PatientTableCell';
import useSelectionState from '@/Hooks/useSelectionState';
import useBulkAction from '@/Hooks/useBulkAction';
import { useState, useMemo } from 'react';

export default function LabRequestsIndex({ requests, filters, stats = {}, auth }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [quickFilter, setQuickFilter] = useState(filters.quick_filter || '');
    const { selectedIds, setSelectedIds } = useSelectionState({ idField: 'request_id' });
    const { handleBulkAction } = useBulkAction({
        routeName: 'lab.bulk-action',
        selectedIds,
        clearSelection: () => setSelectedIds([]),
    });

    const applyFilters = (searchValue, statusValue = status, quickFilterValue = quickFilter) => {
        router.get(
            route('lab.index'),
            { search: searchValue, status: statusValue, quick_filter: quickFilterValue },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const handleStatusChange = (val) => {
        setStatus(val || '');
        applyFilters(search, val || '', quickFilter);
    };

    const handleQuickFilterChange = (val) => {
        setQuickFilter(val);
        applyFilters(search, status, val);
    };

    const handleProcess = (id) => {
        if (confirm('Start processing this lab request?')) {
            router.post(
                route('lab.update-status', id),
                {
                    status: 'processing',
                },
                {
                    preserveScroll: true,
                },
            );
        }
    };

    const columns = useMemo(
        () => [
            {
                header: 'Req ID',
                accessorKey: 'request_id',
                cell: ({ row }) => <RefBadge>LAB-{row.original.request_id}</RefBadge>,
            },
            {
                header: 'Patient',
                accessorKey: 'patient_id',
                cell: ({ row }) => (
                    <PatientTableCell patient={row.original.patient} patientId={row.original.patient_id} />
                ),
            },
            {
                header: 'Test Type',
                accessorKey: 'test_type.test_name',
                cell: ({ row }) => <TableCellPrimary>{row.original.test_type?.test_name || 'N/A'}</TableCellPrimary>,
            },
            {
                header: 'Priority',
                accessorKey: 'priority',
                cell: ({ row }) => <PriorityBadge priority={row.original.priority} />,
            },
            {
                header: 'Status',
                accessorKey: 'status',
                cell: ({ row }) => <StatusBadge status={row.original.status} />,
            },
            {
                header: 'Requested By',
                accessorKey: 'doctor.user.last_name',
                cell: ({ row }) => <TableCellSub>Dr. {row.original.doctor?.user?.last_name || 'System'}</TableCellSub>,
            },
            {
                header: 'Actions',
                id: 'actions',
                cell: ({ row }) => {
                    const actions = [
                        {
                            label: 'View details',
                            icon: 'fa-eye',
                            href: route('lab.show', row.original.request_id),
                        },
                        auth.user.role === 'lab_technician' &&
                            row.original.status === 'pending' && {
                                label: 'Start processing',
                                icon: 'fa-vial',
                                onClick: () => handleProcess(row.original.request_id),
                            },
                    ].filter(Boolean);

                    return <TableActions actions={actions} />;
                },
            },
        ],
        [auth.user.role],
    );

    const statItems = useMemo(
        () => [
            { label: 'Pending', value: stats.pending || 0, icon: 'fa-clock', color: 'warning' },
            { label: 'Processing', value: stats.processing || 0, icon: 'fa-vial', color: 'info' },
            { label: 'Completed', value: stats.completed || 0, icon: 'fa-check-circle', color: 'success' },
            { label: 'Urgent', value: stats.urgent || 0, icon: 'fa-bolt', color: 'danger' },
        ],
        [stats],
    );

    const showStats = auth.user.role !== 'patient';

    return (
        <AuthenticatedLayout
            headerTitle={auth.user.role === 'patient' ? 'Laboratory Results' : 'Laboratory Registry'}
            breadcrumbs={
                auth.user.role === 'patient'
                    ? [
                          { label: 'Dashboard', url: route('dashboard') },
                          { label: 'Lab Results', active: true },
                      ]
                    : [
                          { label: 'Dashboard', url: route('dashboard') },
                          { label: 'Lab Requests', active: true },
                      ]
            }
        >
            <Head title={auth.user.role === 'patient' ? 'My Labs' : 'Laboratory'} />

            <UnifiedToolbar
                filterGroups={[
                    {
                        id: 'status',
                        label: 'Status',
                        emptyLabel: 'All statuses',
                        value: status,
                        onChange: handleStatusChange,
                        options: [
                            { label: 'Pending', value: 'pending' },
                            { label: 'Processing', value: 'processing' },
                            { label: 'Completed', value: 'completed' },
                            { label: 'Cancelled', value: 'cancelled' },
                        ],
                    },
                    {
                        id: 'priority',
                        label: 'Priority',
                        emptyLabel: 'All priorities',
                        value: quickFilter,
                        onChange: handleQuickFilterChange,
                        options: [
                            { label: 'Urgent', value: 'urgent' },
                            { label: 'Normal', value: 'normal' },
                        ],
                    },
                ]}
                actions={[
                    ['admin', 'lab_technician', 'nurse'].includes(auth.user.role) && {
                        label: 'REGISTER SAMPLE',
                        icon: 'fa-vial',
                        href: route('lab.samples.register'),
                        color: 'success',
                    },
                    ['admin', 'lab_technician'].includes(auth.user.role) && {
                        label: 'LAB RESULTS',
                        icon: 'fa-file-medical-alt',
                        href: route('lab.results'),
                    },
                    auth.user.role === 'lab_technician' && {
                        label: 'TEST CATALOG',
                        icon: 'fa-vials',
                        href: route('lab.tests'),
                    },
                ].filter(Boolean)}
                bulkActions={[
                    { label: 'MARK COMPLETE', icon: 'fa-check-circle', onClick: () => handleBulkAction('complete') },
                    {
                        label: 'CANCEL SELECTED',
                        icon: 'fa-times-circle',
                        onClick: () => handleBulkAction('cancel'),
                        color: 'danger',
                    },
                    {
                        label: 'DELETE SELECTED',
                        icon: 'fa-trash-alt',
                        onClick: () => handleBulkAction('delete'),
                        color: 'danger',
                    },
                ]}
                selectionCount={selectedIds.length}
            />

            <div className="px-0">
                {showStats && <StatCardGrid items={statItems} />}

                <DashboardSearch
                    placeholder="Search by patient name or request ID..."
                    value={search}
                    onChange={setSearch}
                    onSubmit={(val) => applyFilters(val, status, quickFilter)}
                    onFilterChange={handleQuickFilterChange}
                    filters={[
                        { label: 'Pending', value: 'pending' },
                        { label: 'Completed', value: 'completed' },
                        { label: 'Urgent', value: 'urgent' },
                    ]}
                />

                <RegistryTablePanel
                    title="Lab request registry"
                    icon="fa-flask"
                    columns={columns}
                    data={requests.data}
                    pagination={requests}
                    emptyMessage="No lab requests found matching your search."
                    selectable={true}
                    selectedIds={selectedIds}
                    onSelectionChange={setSelectedIds}
                    idField="request_id"
                />
            </div>
        </AuthenticatedLayout>
    );
}
