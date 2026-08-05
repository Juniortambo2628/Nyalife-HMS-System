import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import ReportsNav, { ReportDateFilter } from '@/Components/ReportsNav';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import DashboardSelect from '@/Components/DashboardSelect';
import StatusBadge from '@/Components/StatusBadge';
import StatCardGrid from '@/Components/StatCardGrid';
import { buildBreakdownStats } from '@/Utils/statUtils';
import { useMemo, useState } from 'react';

export default function Laboratory({ requests, stats, filters }) {
    const [from, setFrom] = useState(filters.from || '');
    const [to, setTo] = useState(filters.to || '');
    const [status, setStatus] = useState(filters.status || '');

    const applyFilters = (statusValue = status) => {
        router.get(route('reports.laboratory'), { from, to, status: statusValue }, { preserveState: true });
    };

    const columns = useMemo(
        () => [
            {
                header: 'Request',
                accessorKey: 'request_id',
                cell: (info) => (
                    <Link
                        href={route('lab.show', info.getValue())}
                        className="badge bg-light text-primary fw-bold text-decoration-none"
                    >
                        LAB-{info.getValue()}
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
            { header: 'Test', id: 'test', cell: (info) => info.row.original.test_type?.test_name || '—' },
            { header: 'Date', accessorKey: 'request_date' },
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
                totalLabel: 'Total requests',
                totalIcon: 'fa-flask',
                byKey: stats.by_status,
                labelFormatter: (key) => key.replace(/_/g, ' '),
                icon: 'fa-vial',
                color: 'info',
            }),
        [stats],
    );

    return (
        <AuthenticatedLayout
            headerTitle="Laboratory Report"
            breadcrumbs={[
                { label: 'Reports', url: route('reports.index') },
                { label: 'Laboratory', active: true },
            ]}
        >
            <Head title="Laboratory Report" />
            <ReportsNav active="laboratory" />
            <ReportDateFilter
                from={from}
                to={to}
                onFromChange={setFrom}
                onToChange={setTo}
                onApply={() => applyFilters()}
                exportType="laboratory"
            />

            <StatCardGrid items={statItems} gap={3} cols={4} />

            <div className="mb-3">
                <DashboardSelect
                    options={Object.keys(stats.by_status || {}).map((s) => ({ label: s.replace('_', ' '), value: s }))}
                    value={status}
                    onChange={(val) => {
                        setStatus(val || '');
                        applyFilters(val || '');
                    }}
                    placeholder="Filter by status..."
                />
            </div>

            <RegistryTablePanel
                title="Lab requests in period"
                icon="fa-flask"
                data={requests.data}
                columns={columns}
                pagination={requests}
                emptyMessage="No lab requests in this period."
                idField="request_id"
            />
        </AuthenticatedLayout>
    );
}
