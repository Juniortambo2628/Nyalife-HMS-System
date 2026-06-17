import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState, useMemo, useEffect } from 'react';
import DashboardSearch from '@/Components/DashboardSearch';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import StatusBadge from '@/Components/StatusBadge';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import TableActions from '@/Components/TableActions';
import { RefBadge, TableCellPrimary } from '@/Components/TableCells';
import PatientTableCell from '@/Components/PatientTableCell';
import { formatCurrency } from '@/Utils/formatUtils';
import StatCardGrid from '@/Components/StatCardGrid';

export default function Index({ payments, filters, stats, paymentMethods, auth }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [method, setMethod] = useState(filters.method || '');

    const applyFilters = (searchValue, statusValue = status, methodValue = method) => {
        router.get(route('payments.index'), {
            search: searchValue,
            status: statusValue,
            method: methodValue,
        }, { preserveState: true, replace: true });
    };

    const methodOptions = useMemo(() =>
        Object.entries(paymentMethods || {}).map(([value, label]) => ({ label, value })),
    [paymentMethods]);

    const columns = useMemo(() => [
        {
            header: 'Payment #',
            accessorKey: 'payment_id',
            cell: info => <RefBadge variant="info">PAY-{info.getValue()}</RefBadge>,
        },
        {
            header: 'Invoice',
            accessorKey: 'invoice.invoice_number',
            cell: info => (
                <TableCellPrimary>
                    <a href={route('invoices.show', info.row.original.invoice_id)} className="text-decoration-none">
                        {info.getValue()}
                    </a>
                </TableCellPrimary>
            ),
        },
        {
            header: 'Patient',
            id: 'patient',
            cell: info => (
                <PatientTableCell
                    patient={info.row.original.invoice?.patient}
                    patientId={info.row.original.invoice?.patient_id}
                />
            ),
        },
        {
            header: 'Amount',
            accessorKey: 'amount',
            cell: info => (
                <TableCellPrimary>{formatCurrency(info.getValue())}</TableCellPrimary>
            ),
        },
        {
            header: 'Method',
            accessorKey: 'payment_method_label',
            cell: info => <TableCellPrimary className="text-uppercase text-muted">{info.getValue()}</TableCellPrimary>,
        },
        {
            header: 'Status',
            accessorKey: 'payment_status',
            cell: info => <StatusBadge status={info.getValue()} />,
        },
        {
            header: 'Date',
            accessorKey: 'payment_date',
            cell: info => <TableCellPrimary className="text-muted">{info.getValue()}</TableCellPrimary>,
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: info => (
                <TableActions actions={[
                    {
                        icon: 'fa-eye',
                        label: 'View receipt',
                        href: route('payments.show', info.row.original.payment_id),
                    },
                    {
                        icon: 'fa-print',
                        label: 'Print receipt',
                        href: route('payments.print', info.row.original.payment_id),
                        target: '_blank',
                    },
                ]} />
            ),
        },
    ], []);

    const statItems = useMemo(() => [
        {
            label: 'Total Collected',
            value: formatCurrency(stats?.total_collected || 0),
            icon: 'fa-money-bill-wave',
            color: 'success',
        },
        {
            label: 'Completed Payments',
            value: stats?.payment_count ?? 0,
            icon: 'fa-check-circle',
            color: 'primary',
        },
        {
            label: 'Pending',
            value: stats?.pending_count ?? 0,
            icon: 'fa-clock',
            color: 'warning',
        },
    ], [stats]);

    return (
        <AuthenticatedLayout headerTitle="Payment Registry">
            <Head title="Payments" />

            <StatCardGrid items={statItems} cols={3} />

            <UnifiedToolbar
                filterGroups={[
                    {
                        id: 'status',
                        label: 'Status',
                        emptyLabel: 'All statuses',
                        value: status,
                        onChange: (val) => { setStatus(val || ''); applyFilters(search, val || '', method); },
                        options: [
                            { label: 'Completed', value: 'completed' },
                            { label: 'Pending', value: 'pending' },
                            { label: 'Failed', value: 'failed' },
                            { label: 'Refunded', value: 'refunded' },
                        ],
                    },
                    {
                        id: 'method',
                        label: 'Method',
                        emptyLabel: 'All methods',
                        value: method,
                        onChange: (val) => { setMethod(val || ''); applyFilters(search, status, val || ''); },
                        options: methodOptions.filter((o) => o.value !== ''),
                    },
                ]}
                actions={[
                    ['admin', 'receptionist', 'doctor'].includes(auth.user.role) && {
                        label: 'RECORD PAYMENT',
                        icon: 'fa-plus-circle',
                        href: route('payments.create'),
                        color: 'success',
                    },
                    {
                        label: 'EXPORT CSV',
                        icon: 'fa-file-csv',
                        href: route('payments.export.csv', filters),
                        color: 'gray',
                    },
                ].filter(Boolean)}
            />

            <DashboardSearch
                placeholder="Search by invoice #, patient, or reference..."
                value={search}
                onChange={setSearch}
                onSubmit={(val) => applyFilters(val, status, method)}
            />

            <RegistryTablePanel
                title="Payment registry"
                icon="fa-credit-card"
                data={payments.data}
                columns={columns}
                pagination={payments}
                emptyMessage="No payments recorded yet."
                idField="payment_id"
            />
        </AuthenticatedLayout>
    );
}
