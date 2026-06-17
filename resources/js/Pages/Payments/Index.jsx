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

    return (
        <AuthenticatedLayout headerTitle="Payment Registry">
            <Head title="Payments" />

            <div className="row g-3 mb-4">
                <div className="col-md-4">
                    <div className="card border-0 shadow-sm rounded-4 bg-success text-white">
                        <div className="card-body p-4">
                            <div className="extra-small fw-bold opacity-75 text-uppercase tracking-widest mb-1">Total Collected</div>
                            <div className="h3 fw-extrabold mb-0">{formatCurrency(stats?.total_collected || 0)}</div>
                        </div>
                    </div>
                </div>
                <div className="col-md-4">
                    <div className="card border-0 shadow-sm rounded-4 bg-white">
                        <div className="card-body p-4">
                            <div className="extra-small fw-bold text-muted text-uppercase tracking-widest mb-1">Completed Payments</div>
                            <div className="h3 fw-extrabold text-gray-900 mb-0">{stats?.payment_count ?? 0}</div>
                        </div>
                    </div>
                </div>
                <div className="col-md-4">
                    <div className="card border-0 shadow-sm rounded-4 bg-white">
                        <div className="card-body p-4">
                            <div className="extra-small fw-bold text-muted text-uppercase tracking-widest mb-1">Pending</div>
                            <div className="h3 fw-extrabold text-warning mb-0">{stats?.pending_count ?? 0}</div>
                        </div>
                    </div>
                </div>
            </div>

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
