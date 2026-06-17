import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import ReportsNav, { ReportDateFilter } from '@/Components/ReportsNav';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import StatusBadge from '@/Components/StatusBadge';
import StatCardGrid from '@/Components/StatCardGrid';
import { formatCurrency } from '@/Utils/formatUtils';
import { useMemo, useState } from 'react';

export default function Financial({ invoices, stats, filters }) {
    const [from, setFrom] = useState(filters.from || '');
    const [to, setTo] = useState(filters.to || '');

    const applyFilters = () => router.get(route('reports.financial'), { from, to }, { preserveState: true });

    const columns = useMemo(() => [
        {
            header: 'Invoice',
            accessorKey: 'invoice_number',
            cell: info => (
                <Link href={route('invoices.show', info.row.original.invoice_id)} className="fw-bold text-primary text-decoration-none">
                    {info.getValue()}
                </Link>
            ),
        },
        {
            header: 'Patient',
            id: 'patient',
            cell: info => {
                const u = info.row.original.patient?.user;
                return u ? `${u.first_name} ${u.last_name}` : '—';
            },
        },
        { header: 'Date', accessorKey: 'invoice_date' },
        {
            header: 'Amount',
            accessorKey: 'total_amount',
            cell: info => <span className="fw-extrabold">{formatCurrency(info.getValue())}</span>,
        },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: info => <StatusBadge status={info.getValue()} />,
        },
    ], []);

    const statItems = useMemo(() => [
        { label: 'Total invoiced', value: formatCurrency(stats.total_invoiced), icon: 'fa-file-invoice-dollar', color: 'primary' },
        { label: 'Paid', value: formatCurrency(stats.paid_amount), icon: 'fa-check-circle', color: 'success' },
        { label: 'Pending', value: formatCurrency(stats.pending_amount), icon: 'fa-clock', color: 'warning' },
        { label: 'Payments received', value: formatCurrency(stats.payments_received), icon: 'fa-money-bill-wave', color: 'info' },
    ], [stats]);

    return (
        <AuthenticatedLayout
            headerTitle="Financial Report"
            breadcrumbs={[{ label: 'Reports', url: route('reports.index') }, { label: 'Financial', active: true }]}
        >
            <Head title="Financial Report" />
            <ReportsNav active="financial" />
            <ReportDateFilter from={from} to={to} onFromChange={setFrom} onToChange={setTo} onApply={applyFilters} exportType="financial" />

            <StatCardGrid items={statItems} gap={3} />

            <RegistryTablePanel title="Invoices in period" icon="fa-file-invoice" data={invoices.data} columns={columns} pagination={invoices} emptyMessage="No invoices in this period." idField="invoice_id" />
        </AuthenticatedLayout>
    );
}
