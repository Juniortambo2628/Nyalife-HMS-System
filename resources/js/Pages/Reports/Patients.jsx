import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import ReportsNav, { ReportDateFilter } from '@/Components/ReportsNav';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import DashboardSelect from '@/Components/DashboardSelect';
import StatCardGrid from '@/Components/StatCardGrid';
import { buildBreakdownStats } from '@/Utils/statUtils';
import { useMemo, useState } from 'react';

export default function Patients({ patients, stats, filters }) {
    const [from, setFrom] = useState(filters.from || '');
    const [to, setTo] = useState(filters.to || '');
    const [gender, setGender] = useState(filters.gender || '');

    const applyFilters = (genderValue = gender) => {
        router.get(route('reports.patients'), { from, to, gender: genderValue }, { preserveState: true });
    };

    const columns = useMemo(
        () => [
            {
                header: 'Patient',
                id: 'name',
                cell: (info) => (
                    <Link
                        href={route('patients.show', info.row.original.patient_id)}
                        className="fw-bold text-primary text-decoration-none"
                    >
                        {info.row.original.user?.first_name} {info.row.original.user?.last_name}
                    </Link>
                ),
            },
            {
                header: 'Gender',
                accessorKey: 'gender',
                cell: (info) => <span className="text-capitalize">{info.getValue() || '—'}</span>,
            },
            { header: 'Phone', accessorKey: 'user.phone', cell: (info) => info.getValue() || '—' },
            { header: 'Blood Group', accessorKey: 'blood_group', cell: (info) => info.getValue() || '—' },
            {
                header: 'Registered',
                accessorKey: 'created_at',
                cell: (info) => info.getValue()?.slice?.(0, 10) || info.getValue(),
            },
        ],
        [],
    );

    const statItems = useMemo(
        () =>
            buildBreakdownStats({
                total: stats.total,
                totalLabel: 'New registrations',
                totalIcon: 'fa-user-plus',
                byKey: stats.by_gender,
                labelFormatter: (key) => key || 'Unknown',
                icon: 'fa-venus-mars',
                color: 'info',
            }),
        [stats],
    );

    return (
        <AuthenticatedLayout
            headerTitle="New Patient Registrations"
            breadcrumbs={[
                { label: 'Reports', url: route('reports.index') },
                { label: 'Patients', active: true },
            ]}
        >
            <Head title="Patients Report" />
            <ReportsNav active="patients" />
            <ReportDateFilter
                from={from}
                to={to}
                onFromChange={setFrom}
                onToChange={setTo}
                onApply={() => applyFilters()}
                exportType="patients"
            />

            <StatCardGrid items={statItems} gap={3} cols={4} />

            <div className="mb-3">
                <DashboardSelect
                    options={[
                        { label: 'Male', value: 'male' },
                        { label: 'Female', value: 'female' },
                    ]}
                    value={gender}
                    onChange={(val) => {
                        setGender(val || '');
                        applyFilters(val || '');
                    }}
                    placeholder="Filter by gender..."
                />
            </div>

            <RegistryTablePanel
                title="New patients in period"
                icon="fa-users"
                data={patients.data}
                columns={columns}
                pagination={patients}
                emptyMessage="No new patients in this period."
                idField="patient_id"
            />
        </AuthenticatedLayout>
    );
}
