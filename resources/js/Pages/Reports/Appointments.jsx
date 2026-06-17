import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import ReportsNav, { ReportDateFilter } from '@/Components/ReportsNav';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import DashboardSelect from '@/Components/DashboardSelect';
import StatusBadge from '@/Components/StatusBadge';
import StatCardGrid from '@/Components/StatCardGrid';
import { buildBreakdownStats } from '@/Utils/statUtils';
import { useMemo, useState } from 'react';

export default function Appointments({ appointments, stats, filters }) {
    const [from, setFrom] = useState(filters.from || '');
    const [to, setTo] = useState(filters.to || '');
    const [status, setStatus] = useState(filters.status || '');

    const applyFilters = (statusValue = status) => {
        router.get(route('reports.appointments'), { from, to, status: statusValue }, { preserveState: true });
    };

    const columns = useMemo(() => [
        { header: 'ID', accessorKey: 'appointment_id' },
        {
            header: 'Patient',
            id: 'patient',
            cell: info => {
                const u = info.row.original.patient?.user;
                return u ? `${u.first_name} ${u.last_name}` : '—';
            },
        },
        {
            header: 'Doctor',
            id: 'doctor',
            cell: info => {
                const u = info.row.original.doctor?.user;
                return u ? `Dr. ${u.last_name}` : '—';
            },
        },
        { header: 'Date', accessorKey: 'appointment_date' },
        { header: 'Time', accessorKey: 'appointment_time' },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: info => <StatusBadge status={info.getValue()} />,
        },
    ], []);

    const statItems = useMemo(() => buildBreakdownStats({
        total: stats.total,
        totalLabel: 'Total appointments',
        totalIcon: 'fa-calendar-check',
        byKey: stats.by_status,
        labelFormatter: (key) => key.replace(/_/g, ' '),
        icon: 'fa-circle',
        color: 'info',
    }), [stats]);

    return (
        <AuthenticatedLayout
            headerTitle="Appointments Report"
            breadcrumbs={[{ label: 'Reports', url: route('reports.index') }, { label: 'Appointments', active: true }]}
        >
            <Head title="Appointments Report" />
            <ReportsNav active="appointments" />
            <ReportDateFilter from={from} to={to} onFromChange={setFrom} onToChange={setTo} onApply={() => applyFilters()} exportType="appointments" />

            <StatCardGrid items={statItems} gap={3} cols={4} />

            <div className="mb-3">
                <DashboardSelect
                    options={Object.keys(stats.by_status || {}).map((s) => ({ label: s.replace('_', ' '), value: s }))}
                    value={status}
                    onChange={(val) => { setStatus(val || ''); applyFilters(val || ''); }}
                    placeholder="Filter by status..."
                />
            </div>

            <RegistryTablePanel title="Appointments in period" icon="fa-calendar-check" data={appointments.data} columns={columns} pagination={appointments} emptyMessage="No appointments in this period." idField="appointment_id" />
        </AuthenticatedLayout>
    );
}
