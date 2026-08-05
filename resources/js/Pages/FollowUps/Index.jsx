import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import DashboardSearch from '@/Components/DashboardSearch';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import StatusBadge from '@/Components/StatusBadge';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import TableActions from '@/Components/TableActions';
import PatientTableCell from '@/Components/PatientTableCell';
import StatCardGrid from '@/Components/StatCardGrid';
import { TableCellPrimary } from '@/Components/TableCells';

export default function Index({ followUps, filters, stats, followUpTypes, auth }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [type, setType] = useState(filters.type || '');
    const isUpcoming = filters.view === 'upcoming';

    const applyFilters = (searchValue, statusValue = status, typeValue = type, view = filters.view) => {
        router.get(
            route(isUpcoming ? 'follow-ups.upcoming' : 'follow-ups.index'),
            {
                search: searchValue,
                status: statusValue,
                type: typeValue,
                view,
            },
            { preserveState: true, replace: true },
        );
    };

    const typeOptions = useMemo(
        () => Object.entries(followUpTypes || {}).map(([value, label]) => ({ label, value })),
        [followUpTypes],
    );

    const columns = useMemo(
        () => [
            {
                header: 'Date',
                accessorKey: 'follow_up_date',
                cell: (info) => <TableCellPrimary>{info.getValue()}</TableCellPrimary>,
            },
            {
                header: 'Patient',
                id: 'patient',
                cell: (info) => (
                    <PatientTableCell patient={info.row.original.patient} patientId={info.row.original.patient_id} />
                ),
            },
            {
                header: 'Type',
                accessorKey: 'follow_up_type_label',
                cell: (info) => <TableCellPrimary className="text-uppercase">{info.getValue()}</TableCellPrimary>,
            },
            {
                header: 'Reason',
                accessorKey: 'reason',
                cell: (info) => (
                    <TableCellPrimary className="text-muted text-truncate" style={{ maxWidth: '220px' }}>
                        {info.getValue()}
                    </TableCellPrimary>
                ),
            },
            {
                header: 'Status',
                accessorKey: 'status',
                cell: (info) => <StatusBadge status={info.getValue()} />,
            },
            {
                header: 'Actions',
                id: 'actions',
                cell: (info) => (
                    <TableActions
                        actions={[
                            {
                                icon: 'fa-eye',
                                label: 'View follow-up',
                                href: route('follow-ups.show', info.row.original.follow_up_id),
                            },
                        ]}
                    />
                ),
            },
        ],
        [],
    );

    const statItems = useMemo(
        () => [
            {
                label: 'Scheduled this month',
                value: stats?.scheduled_month ?? 0,
                icon: 'fa-calendar-check',
                color: 'primary',
            },
            {
                label: 'Completed this month',
                value: stats?.completed_month ?? 0,
                icon: 'fa-check-circle',
                color: 'success',
            },
            {
                label: 'Due within 7 days',
                value: stats?.upcoming_week ?? 0,
                icon: 'fa-calendar-day',
                color: 'warning',
            },
        ],
        [stats],
    );

    return (
        <AuthenticatedLayout
            headerTitle={isUpcoming ? 'Upcoming Follow-ups' : 'Follow-ups'}
            breadcrumbs={[{ label: 'Follow-ups', active: true }]}
        >
            <Head title="Follow-ups" />

            <StatCardGrid items={statItems} cols={3} />

            <UnifiedToolbar
                filterGroups={[
                    {
                        id: 'status',
                        label: 'Status',
                        emptyLabel: 'All statuses',
                        value: status,
                        onChange: (val) => {
                            setStatus(val || '');
                            applyFilters(search, val || '', type);
                        },
                        options: [
                            { label: 'Scheduled', value: 'scheduled' },
                            { label: 'Completed', value: 'completed' },
                            { label: 'Cancelled', value: 'cancelled' },
                            { label: 'No Show', value: 'no_show' },
                        ],
                    },
                    {
                        id: 'type',
                        label: 'Type',
                        emptyLabel: 'All types',
                        value: type,
                        onChange: (val) => {
                            setType(val || '');
                            applyFilters(search, status, val || '');
                        },
                        options: typeOptions.filter((o) => o.value !== ''),
                    },
                ]}
                actions={[
                    ['admin', 'doctor', 'nurse'].includes(auth.user.role) && {
                        label: 'SCHEDULE FOLLOW-UP',
                        icon: 'fa-plus-circle',
                        href: route('follow-ups.create'),
                        color: 'success',
                    },
                    {
                        label: isUpcoming ? 'ALL FOLLOW-UPS' : 'UPCOMING',
                        icon: 'fa-calendar-day',
                        href: isUpcoming ? route('follow-ups.index') : route('follow-ups.upcoming'),
                    },
                ].filter(Boolean)}
            />

            <DashboardSearch
                placeholder="Search by patient or reason..."
                value={search}
                onChange={setSearch}
                onSubmit={(val) => applyFilters(val, status, type)}
            />

            <RegistryTablePanel
                title="Follow-up registry"
                icon="fa-clipboard-list"
                data={followUps.data}
                columns={columns}
                pagination={followUps}
                emptyMessage="No follow-ups found."
                idField="follow_up_id"
            />
        </AuthenticatedLayout>
    );
}
