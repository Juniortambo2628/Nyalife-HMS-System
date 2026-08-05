import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import DashboardSearch from '@/Components/DashboardSearch';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import StatusBadge from '@/Components/StatusBadge';
import PatientTableCell from '@/Components/PatientTableCell';
import PriorityBadge from '@/Components/PriorityBadge';
import TableActions from '@/Components/TableActions';
import { RefBadge, TableCellPrimary, TableCellSub } from '@/Components/TableCells';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import useSelectionState from '@/Hooks/useSelectionState';
import useBulkAction from '@/Hooks/useBulkAction';
import { useState, useMemo } from 'react';

export default function RadiologyRequestsIndex({ requests, filters, auth }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [quickFilter, setQuickFilter] = useState(filters.quick_filter || '');
    const { selectedIds, setSelectedIds } = useSelectionState({ idField: 'request_id' });
    const { handleBulkAction } = useBulkAction({
        routeName: 'radiology.bulk-action',
        selectedIds,
        clearSelection: () => setSelectedIds([]),
    });

    const applyFilters = (searchValue, statusValue = status, quickFilterValue = quickFilter) => {
        router.get(
            route('radiology.index'),
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
        if (confirm('Start processing this radiology request?')) {
            router.post(
                route('radiology.update-status', id),
                {
                    status: 'processing',
                },
                {
                    preserveScroll: true,
                },
            );
        }
    };

    const handleDelete = (id) => {
        if (confirm('Are you sure you want to remove this pending radiology request?')) {
            router.delete(route('radiology.destroy', id), {
                preserveScroll: true,
            });
        }
    };

    const columns = useMemo(
        () => [
            {
                header: 'Order Ref',
                accessorKey: 'request_number',
                cell: ({ row }) => <RefBadge>{row.original.request_number}</RefBadge>,
            },
            {
                header: 'Patient',
                accessorKey: 'patient',
                cell: ({ row }) => (
                    <PatientTableCell
                        patient={row.original.patient}
                        patientId={row.original.patient_id}
                        idVariant="patid"
                    />
                ),
            },
            {
                header: 'Scan / Imaging Type',
                accessorKey: 'scan_type',
                cell: ({ row }) => (
                    <div>
                        <TableCellPrimary>{row.original.scan_type}</TableCellPrimary>
                        {row.original.clinical_indication && (
                            <TableCellSub className="text-truncate nyl-text-constrained">
                                {row.original.clinical_indication}
                            </TableCellSub>
                        )}
                    </div>
                ),
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
                header: 'Ordered By',
                accessorKey: 'requestedBy',
                cell: ({ row }) => <TableCellSub>Dr. {row.original.requestedBy?.last_name || 'System'}</TableCellSub>,
            },
            {
                header: 'Actions',
                id: 'actions',
                cell: ({ row }) => (
                    <TableActions
                        actions={[
                            {
                                label: 'View & process',
                                icon: 'fa-eye',
                                href: route('radiology.show', row.original.request_id),
                            },
                            (auth.user.role === 'lab_technician' || auth.user.role === 'admin') &&
                                row.original.status === 'pending' && {
                                    label: 'Start processing',
                                    icon: 'fa-play',
                                    onClick: () => handleProcess(row.original.request_id),
                                },
                            (auth.user.role === 'admin' || auth.user.role === 'doctor') &&
                                row.original.status === 'pending' && {
                                    label: 'Delete request',
                                    icon: 'fa-trash',
                                    onClick: () => handleDelete(row.original.request_id),
                                    color: 'danger',
                                },
                        ].filter(Boolean)}
                    />
                ),
            },
        ],
        [auth.user.role],
    );

    return (
        <AuthenticatedLayout
            headerTitle={auth.user.role === 'patient' ? 'My Radiology & Imaging' : 'Radiology & Imaging Registry'}
            breadcrumbs={
                auth.user.role === 'patient'
                    ? [
                          { label: 'Dashboard', url: route('dashboard') },
                          { label: 'My Scans', active: true },
                      ]
                    : [
                          { label: 'Dashboard', url: route('dashboard') },
                          { label: 'Radiology Requests', active: true },
                      ]
            }
        >
            <Head title={auth.user.role === 'patient' ? 'My Radiology Scans' : 'Radiology & Imaging'} />

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
                            { label: 'Pending Verification', value: 'pending_verification' },
                            { label: 'Verified', value: 'verified' },
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
                            { label: 'Routine', value: 'routine' },
                        ],
                    },
                ]}
                actions={[
                    (auth.user.role === 'doctor' || auth.user.role === 'admin') && {
                        label: 'ORDER SCAN',
                        icon: 'fa-plus-circle',
                        href: route('radiology.create'),
                        color: 'pink',
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
                <DashboardSearch
                    placeholder="Search by patient name or request ID..."
                    value={search}
                    onChange={setSearch}
                    onSubmit={(val) => applyFilters(val, status, quickFilter)}
                    onFilterChange={handleQuickFilterChange}
                    filters={[
                        { label: 'Pending', value: 'pending' },
                        { label: 'Pending Verification', value: 'pending_verification' },
                        { label: 'Verified', value: 'verified' },
                        { label: 'Urgent', value: 'urgent' },
                    ]}
                />

                <RegistryTablePanel
                    title="Radiology request registry"
                    icon="fa-x-ray"
                    columns={columns}
                    data={requests.data}
                    pagination={requests}
                    emptyMessage="No radiology requests found matching your search."
                    selectable={auth.user.role !== 'patient'}
                    selectedIds={selectedIds}
                    onSelectionChange={setSelectedIds}
                    idField="request_id"
                />
            </div>
        </AuthenticatedLayout>
    );
}
