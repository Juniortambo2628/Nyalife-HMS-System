import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import DashboardSearch from '@/Components/DashboardSearch';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import StatusBadge from '@/Components/StatusBadge';
import PriorityBadge from '@/Components/PriorityBadge';
import PatientTableCell from '@/Components/PatientTableCell';
import { RefBadge, TableCellPrimary } from '@/Components/TableCells';
import TableActions from '@/Components/TableActions';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import StatCardGrid from '@/Components/StatCardGrid';

export default function Index({ samples, filters, sampleStatuses, stats }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');

    const statItems = useMemo(
        () => [
            {
                label: 'Total Samples',
                value: stats?.total ?? 0,
                icon: 'fa-vial',
                color: 'primary',
            },
            {
                label: 'Registered',
                value: stats?.registered ?? 0,
                icon: 'fa-clipboard-list',
                color: 'warning',
            },
            {
                label: 'Completed',
                value: stats?.completed ?? 0,
                icon: 'fa-check-circle',
                color: 'success',
            },
        ],
        [stats],
    );

    const applyFilters = (searchValue, statusValue = status) => {
        router.get(
            route('lab.samples.index'),
            {
                search: searchValue,
                status: statusValue,
            },
            { preserveState: true, replace: true },
        );
    };

    const statusOptions = useMemo(
        () => Object.entries(sampleStatuses || {}).map(([value, label]) => ({ label, value })),
        [sampleStatuses],
    );

    const columns = useMemo(
        () => [
            {
                header: 'Sample ID',
                accessorKey: 'sample_id',
                cell: (info) => <RefBadge variant="info">{info.getValue()}</RefBadge>,
            },
            {
                header: 'Patient',
                id: 'patient',
                cell: (info) => (
                    <PatientTableCell patient={info.row.original.patient} patientId={info.row.original.patient_id} />
                ),
            },
            {
                header: 'Test',
                id: 'test',
                cell: (info) => <TableCellPrimary>{info.row.original.test_type?.test_name || '—'}</TableCellPrimary>,
            },
            {
                header: 'Type',
                accessorKey: 'sample_type_label',
                cell: (info) => <TableCellPrimary className="text-muted">{info.getValue()}</TableCellPrimary>,
            },
            {
                header: 'Collected',
                accessorKey: 'collected_at',
                cell: (info) => (
                    <TableCellPrimary>{info.getValue() || info.row.original.collected_date || '—'}</TableCellPrimary>
                ),
            },
            {
                header: 'Status',
                accessorKey: 'status',
                cell: (info) => (
                    <div className="d-flex align-items-center gap-2">
                        <StatusBadge status={info.getValue()} />
                        {info.row.original.urgent && <PriorityBadge priority="urgent" />}
                    </div>
                ),
            },
            {
                header: 'Actions',
                id: 'actions',
                cell: (info) => (
                    <TableActions
                        actions={[
                            {
                                label: 'View sample',
                                icon: 'fa-eye',
                                href: route('lab.samples.show', info.row.original.id),
                            },
                        ]}
                    />
                ),
            },
        ],
        [],
    );

    return (
        <AuthenticatedLayout headerTitle="Lab Samples">
            <Head title="Lab Samples" />

            <StatCardGrid items={statItems} cols={3} />

            <UnifiedToolbar
                actions={[
                    {
                        label: 'REGISTER SAMPLE',
                        icon: 'fa-vial',
                        href: route('lab.samples.register'),
                        color: 'success',
                    },
                    { label: 'LAB REQUESTS', icon: 'fa-flask', href: route('lab.index'), color: 'gray' },
                    { label: 'LAB RESULTS', icon: 'fa-file-medical-alt', href: route('lab.results'), color: 'gray' },
                ]}
                filterGroups={[
                    {
                        id: 'status',
                        label: 'Status',
                        emptyLabel: 'All statuses',
                        value: status,
                        onChange: (val) => {
                            setStatus(val || '');
                            applyFilters(search, val || '');
                        },
                        options: statusOptions.filter((o) => o.value !== ''),
                    },
                ]}
            />

            <DashboardSearch
                placeholder="Search by sample ID or patient name..."
                value={search}
                onChange={setSearch}
                onSubmit={(val) => applyFilters(val, status)}
            />

            <RegistryTablePanel
                title="Sample registry"
                icon="fa-vial"
                data={samples.data}
                columns={columns}
                pagination={samples}
                emptyMessage="No samples registered yet."
                idField="id"
            />
        </AuthenticatedLayout>
    );
}
