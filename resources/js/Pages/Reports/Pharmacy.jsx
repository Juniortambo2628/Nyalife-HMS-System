import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import ReportsNav, { ReportDateFilter } from '@/Components/ReportsNav';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import DashboardSelect from '@/Components/DashboardSelect';
import StatusBadge from '@/Components/StatusBadge';
import StatCardGrid from '@/Components/StatCardGrid';
import { buildBreakdownStats } from '@/Utils/statUtils';
import { useMemo, useState } from 'react';

export default function Pharmacy({ prescriptions, stats, filters }) {
    const [from, setFrom] = useState(filters.from || '');
    const [to, setTo] = useState(filters.to || '');
    const [status, setStatus] = useState(filters.status || '');

    const applyFilters = (statusValue = status) => {
        router.get(route('reports.pharmacy'), { from, to, status: statusValue }, { preserveState: true });
    };

    const columns = useMemo(
        () => [
            {
                header: 'Prescription',
                accessorKey: 'prescription_id',
                cell: (info) => (
                    <Link
                        href={route('prescriptions.show', info.getValue())}
                        className="fw-bold text-primary text-decoration-none"
                    >
                        RX-{info.getValue()}
                    </Link>
                ),
            },
            {
                header: 'Patient',
                id: 'patient',
                cell: (info) => {
                    const u = info.row.original.patient?.user;
                    return u ? `${u.first_name} ${u.last_name}` : '—';
                },
            },
            {
                header: 'Medications',
                id: 'items',
                cell: (info) => (info.row.original.items || []).map((i) => i.medicine_name).join(', ') || '—',
            },
            { header: 'Date', accessorKey: 'prescription_date' },
            {
                header: 'Status',
                accessorKey: 'status',
                cell: (info) => <StatusBadge status={info.getValue()} />,
            },
        ],
        [],
    );

    const statItems = useMemo(
        () =>
            buildBreakdownStats({
                total: stats.total,
                totalLabel: 'Total prescriptions',
                totalIcon: 'fa-prescription-bottle-alt',
                byKey: stats.by_status,
                icon: 'fa-pills',
                color: 'warning',
            }),
        [stats],
    );

    return (
        <AuthenticatedLayout
            headerTitle="Pharmacy Report"
            breadcrumbs={[
                { label: 'Reports', url: route('reports.index') },
                { label: 'Pharmacy', active: true },
            ]}
        >
            <Head title="Pharmacy Report" />
            <ReportsNav active="pharmacy" />
            <ReportDateFilter
                from={from}
                to={to}
                onFromChange={setFrom}
                onToChange={setTo}
                onApply={() => applyFilters()}
                exportType="pharmacy"
            />

            <StatCardGrid items={statItems} gap={3} cols={4} />

            <div className="mb-3">
                <DashboardSelect
                    options={Object.keys(stats.by_status || {}).map((s) => ({ label: s, value: s }))}
                    value={status}
                    onChange={(val) => {
                        setStatus(val || '');
                        applyFilters(val || '');
                    }}
                    placeholder="Filter by status..."
                />
            </div>

            <RegistryTablePanel
                title="Prescriptions in period"
                icon="fa-pills"
                data={prescriptions.data}
                columns={columns}
                pagination={prescriptions}
                emptyMessage="No prescriptions in this period."
                idField="prescription_id"
            />
        </AuthenticatedLayout>
    );
}
