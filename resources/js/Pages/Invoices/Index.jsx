import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState, useMemo, useEffect } from 'react';

import DashboardSearch from '@/Components/DashboardSearch';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import StatusBadge from '@/Components/StatusBadge';
import TableActions from '@/Components/TableActions';
import { RefBadge, TableCellPrimary, TableCellSub } from '@/Components/TableCells';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import PatientTableCell from '@/Components/PatientTableCell';
import { formatNumber, formatCurrency } from '@/Utils/formatUtils';
import StatCardGrid from '@/Components/StatCardGrid';

export default function Index({ invoices, filters, auth, stats }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [quickFilter, setQuickFilter] = useState(filters.quick_filter || '');
    const [selectedIds, setSelectedIds] = useState([]);

    const statItems = useMemo(() => [
        {
            label: 'Total Invoices',
            value: stats?.total ?? 0,
            icon: 'fa-file-invoice-dollar',
            color: 'primary',
        },
        {
            label: 'Unpaid Invoices',
            value: stats?.unpaid ?? 0,
            icon: 'fa-exclamation-circle',
            color: 'warning',
        },
        {
            label: 'Paid Invoices',
            value: stats?.paid ?? 0,
            icon: 'fa-check-circle',
            color: 'success',
        },
        {
            label: 'Total Invoiced',
            value: formatCurrency(stats?.total_amount ?? 0),
            icon: 'fa-money-bill-wave',
            color: 'info',
        },
    ], [stats]);

    useEffect(() => {
        const handleClear = () => setSelectedIds([]);
        window.addEventListener('toolbar-clear-selection', handleClear);
        return () => window.removeEventListener('toolbar-clear-selection', handleClear);
    }, []);

    const handleBulkAction = (action) => {
        router.post(route('invoices.bulk-action'), {
            action: action,
            ids: selectedIds
        }, {
            onSuccess: () => setSelectedIds([]),
        });
    };

    const applyFilters = (searchValue, statusValue = status, quickFilterValue = quickFilter) => {
        router.get(route('invoices.index'), { search: searchValue, status: statusValue, quick_filter: quickFilterValue }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleStatusChange = (val) => {
        setStatus(val || '');
        applyFilters(search, val || '', quickFilter);
    };

    const handleQuickFilterChange = (val) => {
        setQuickFilter(val);
        applyFilters(search, status, val);
    };

    const columns = useMemo(() => [
        {
            header: 'Invoice #',
            accessorKey: 'invoice_number',
            cell: info => (
                <RefBadge>{info.getValue()}</RefBadge>
            )
        },
        {
            header: 'Patient',
            accessorKey: 'patient.user.first_name',
            cell: info => (
                <PatientTableCell patient={info.row.original.patient} patientId={info.row.original.patient_id} />
            )
        },
        {
            header: 'Amount',
            accessorKey: 'total_amount',
            cell: info => (
                <TableCellPrimary>
                    <span className="text-muted nyl-table-cell-sub d-inline me-1">KES</span>
                    {formatNumber(info.getValue())}
                </TableCellPrimary>
            )
        },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: info => <StatusBadge status={info.getValue()} />,
            enableSorting: false
        },
        {
            header: 'Date',
            accessorKey: 'invoice_date',
            cell: info => (
                <TableCellSub>{info.getValue()}</TableCellSub>
            )
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: info => (
                <TableActions actions={[
                    {
                        label: 'View document',
                        icon: 'fa-eye',
                        href: route('invoices.show', info.row.original.invoice_id),
                    },
                    ['pending', 'partially_paid', 'overdue'].includes(info.row.original.status) && {
                        label: 'Collect payment',
                        icon: 'fa-money-bill-wave',
                        href: route('payments.create', { invoice_id: info.row.original.invoice_id }),
                        color: 'success',
                    },
                ].filter(Boolean)} />
            )
        }
    ], []);

    return (
        <AuthenticatedLayout 
            headerTitle="Financial Registry"
            breadcrumbs={[{ label: 'Billing', url: route('invoices.index') }, { label: 'Revenue Registry', active: true }]}
        >
            <Head title="Billing" />

            <StatCardGrid items={statItems} cols={4} />

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
                            { label: 'Paid', value: 'paid' },
                            { label: 'Overdue', value: 'overdue' },
                            { label: 'Cancelled', value: 'cancelled' },
                        ],
                    },
                    {
                        id: 'payment',
                        label: 'Payment',
                        emptyLabel: 'All types',
                        value: quickFilter,
                        onChange: handleQuickFilterChange,
                        options: [
                            { label: 'Cash', value: 'cash' },
                            { label: 'Insurance', value: 'insurance' },
                        ],
                    },
                ]}
                actions={[
                    (auth.user.role === 'admin' || auth.user.role === 'receptionist') && { 
                        label: 'NEW INVOICE', 
                        icon: 'fa-file-medical', 
                        href: route('invoices.create') 
                    },
                    (auth.user.role === 'admin' || auth.user.role === 'receptionist') && {
                        label: 'EXPORT CSV',
                        icon: 'fa-file-csv',
                        href: route('invoices.export.csv', filters),
                    },
                ]}
                bulkActions={[
                    { label: 'VOID SELECTED', icon: 'fa-ban', onClick: () => handleBulkAction('void'), color: 'danger' },
                    { label: 'DELETE SELECTED', icon: 'fa-trash-alt', onClick: () => handleBulkAction('delete'), color: 'danger' }
                ]}
                selectionCount={selectedIds.length}
            />

            <div className="px-0">
                <DashboardSearch 
                    placeholder="Search by INV-XXXX or patient name..." 
                    value={search}
                    onChange={setSearch}
                    onSubmit={(val) => applyFilters(val, status, quickFilter)}
                    onFilterChange={handleQuickFilterChange}
                    filters={[
                        { label: 'Unpaid', value: 'unpaid' },
                        { label: 'Paid', value: 'paid' },
                        { label: 'Overdue', value: 'overdue' },
                    ]}
                />

                <RegistryTablePanel
                    title="Invoice registry"
                    icon="fa-file-invoice"
                    data={invoices.data}
                    columns={columns}
                    pagination={invoices}
                    emptyMessage="No financial records found matching your search."
                    selectable={true}
                    selectedIds={selectedIds}
                    onSelectionChange={setSelectedIds}
                    idField="invoice_id"
                />

            </div>
        </AuthenticatedLayout>
    );
}
