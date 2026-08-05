import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import DashboardSearch from '@/Components/DashboardSearch';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import StatusBadge from '@/Components/StatusBadge';
import TableActions from '@/Components/TableActions';
import { RefBadge, TableCellStack, TableCellSub } from '@/Components/TableCells';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import PatientTableCell from '@/Components/PatientTableCell';
import StatCardGrid from '@/Components/StatCardGrid';
import useSelectionState from '@/Hooks/useSelectionState';
import useBulkAction from '@/Hooks/useBulkAction';
import { useState, useMemo } from 'react';

export default function Index({ prescriptions, filters, auth, stats }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const { selectedIds, setSelectedIds } = useSelectionState({ idField: 'prescription_id' });

    const statItems = useMemo(
        () => [
            {
                label: 'Total Prescriptions',
                value: stats?.total ?? 0,
                icon: 'fa-prescription',
                color: 'primary',
            },
            {
                label: 'Pending',
                value: stats?.pending ?? 0,
                icon: 'fa-clock',
                color: 'warning',
            },
            {
                label: 'Dispensed',
                value: stats?.dispensed ?? 0,
                icon: 'fa-check-circle',
                color: 'success',
            },
            {
                label: 'Prescribed Today',
                value: stats?.today ?? 0,
                icon: 'fa-calendar-day',
                color: 'info',
            },
        ],
        [stats],
    );

    const { handleBulkAction } = useBulkAction({
        routeName: 'prescriptions.bulk-action',
        selectedIds,
        clearSelection: () => setSelectedIds([]),
    });

    const applyFilters = (searchValue, statusValue = status, quickFilterValue = filters?.quick_filter) => {
        router.get(
            route('prescriptions.index'),
            { search: searchValue, status: statusValue, quick_filter: quickFilterValue },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const handleQuickFilterChange = (val) => {
        applyFilters(search, status, val);
    };

    const handleStatusChange = (val) => {
        setStatus(val || '');
        applyFilters(search, val || '');
    };

    const columns = useMemo(
        () => [
            {
                header: 'RX ID',
                accessorKey: 'prescription_id',
                cell: ({ row }) => <RefBadge>RX-{String(row.original.prescription_id).padStart(5, '0')}</RefBadge>,
            },
            {
                header: 'Date',
                accessorKey: 'prescription_date',
                cell: ({ row }) => <TableCellSub>{row.original.prescription_date}</TableCellSub>,
            },
            {
                header: 'Patient',
                accessorKey: 'patient_id',
                cell: ({ row }) => (
                    <PatientTableCell patient={row.original.patient} patientId={row.original.patient_id} />
                ),
            },
            {
                header: 'Medications',
                accessorKey: 'items',
                cell: ({ row }) => (
                    <TableCellStack
                        primary={
                            <span className="text-truncate d-inline-block nyl-text-constrained">
                                {row.original.items
                                    ?.map((i) => i.medication?.medication_name || 'Unknown')
                                    .join(', ') || 'No items'}
                            </span>
                        }
                        secondary={`${row.original.items?.length || 0} items`}
                    />
                ),
            },
            {
                header: 'Status',
                accessorKey: 'status',
                cell: ({ row }) => <StatusBadge status={row.original.status} />,
            },
            {
                header: 'Actions',
                id: 'actions',
                cell: ({ row }) => (
                    <TableActions
                        actions={[
                            {
                                label: 'View details',
                                icon: 'fa-eye',
                                href: route('prescriptions.show', row.original.prescription_id),
                            },
                            auth.user.role === 'pharmacist' &&
                                row.original.status === 'pending' && {
                                    label: 'Dispense medication',
                                    icon: 'fa-check-circle',
                                    href: route('prescriptions.show', row.original.prescription_id),
                                    color: 'success',
                                },
                        ].filter(Boolean)}
                    />
                ),
            },
        ],
        [],
    );

    return (
        <AuthenticatedLayout
            headerTitle={auth.user.role === 'patient' ? 'Prescription History' : 'Prescription Registry'}
            breadcrumbs={[
                { label: 'Pharmacy', url: route('pharmacy.inventory') },
                { label: 'Prescriptions', active: true },
            ]}
        >
            <Head title={auth.user.role === 'patient' ? 'My Prescriptions' : 'Pharmacy'} />

            {auth.user.role !== 'patient' && <StatCardGrid items={statItems} cols={4} />}

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
                            { label: 'Dispensed', value: 'dispensed' },
                            { label: 'Cancelled', value: 'cancelled' },
                        ],
                    },
                    {
                        id: 'patient_type',
                        label: 'Patient type',
                        emptyLabel: 'All RX',
                        value: filters?.quick_filter || '',
                        onChange: handleQuickFilterChange,
                        options: [
                            { label: 'Inpatient', value: 'inpatient' },
                            { label: 'Outpatient', value: 'outpatient' },
                        ],
                    },
                ]}
                bulkActions={[
                    auth.user.role === 'pharmacist' && {
                        label: 'DISPENSE SELECTED',
                        icon: 'fa-check-double',
                        onClick: () => handleBulkAction('dispense'),
                    },
                    {
                        label: 'VOID SELECTED',
                        icon: 'fa-ban',
                        onClick: () => handleBulkAction('void'),
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

            <div className="px-0 py-0">
                <DashboardSearch
                    placeholder="Search by patient name or RX number..."
                    value={search}
                    onChange={setSearch}
                    onSubmit={applyFilters}
                    onFilterChange={handleQuickFilterChange}
                    filters={[
                        { label: 'Today', value: 'today' },
                        { label: 'Pending', value: 'pending' },
                        { label: 'Dispensed', value: 'dispensed' },
                    ]}
                />

                <RegistryTablePanel
                    title="Prescription registry"
                    icon="fa-pills"
                    columns={columns}
                    data={prescriptions.data}
                    pagination={prescriptions}
                    emptyMessage="No prescriptions found."
                    selectable={true}
                    selectedIds={selectedIds}
                    onSelectionChange={setSelectedIds}
                    idField="prescription_id"
                />
            </div>
        </AuthenticatedLayout>
    );
}
