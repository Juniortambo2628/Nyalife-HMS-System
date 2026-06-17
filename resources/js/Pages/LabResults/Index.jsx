import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import DashboardSearch from '@/Components/DashboardSearch';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import PatientTableCell from '@/Components/PatientTableCell';
import { RefBadge } from '@/Components/TableCells';
import StatusBadge from '@/Components/StatusBadge';
import TableActions from '@/Components/TableActions';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { formatDateOnly } from '@/Utils/dateUtils';

export default function Index({ results, filters }) {
    const { auth } = usePage().props;
    const [search, setSearch] = useState(filters.search || '');

    const applyFilters = (searchValue) => {
        router.get(route('lab.results'), { search: searchValue }, { preserveState: true, replace: true });
    };

    const columns = useMemo(() => [
        {
            header: 'Request #',
            accessorKey: 'request_number',
            cell: info => (
                <RefBadge>
                    {info.getValue() || `LAB-${info.row.original.request_id}`}
                </RefBadge>
            ),
        },
        {
            header: 'Test',
            id: 'test',
            cell: info => info.row.original.test_type?.test_name || '—',
        },
        {
            header: 'Patient',
            id: 'patient',
            cell: info => (
                <PatientTableCell
                    patient={info.row.original.patient}
                    patientId={info.row.original.patient_id}
                />
            ),
        },
        {
            header: 'Completed',
            accessorKey: 'completed_at',
            cell: info => info.getValue()
                ? formatDateOnly(info.getValue())
                : '—',
        },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: info => <StatusBadge status={info.getValue()} />,
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: info => (
                <TableActions
                    actions={[
                        {
                            label: 'View results',
                            icon: 'fa-eye',
                            href: route('lab.results.show', info.row.original.request_id),
                        },
                        {
                            label: 'Print / download',
                            icon: 'fa-print',
                            href: route('lab.results.download', info.row.original.request_id),
                            as: 'a',
                            target: '_blank',
                        },
                    ]}
                />
            ),
        },
    ], []);

    const isPatient = auth?.user?.role === 'patient';

    return (
        <AuthenticatedLayout
            headerTitle={isPatient ? 'My Lab Results' : 'Lab Results'}
            breadcrumbs={[
                { label: 'Dashboard', url: route('dashboard') },
                { label: 'Lab Results', active: true },
            ]}
        >
            <Head title="Lab Results" />

            <UnifiedToolbar
                actions={[
                    !isPatient && ['admin', 'lab_technician', 'nurse'].includes(auth.user.role) && {
                        label: 'Register sample',
                        icon: 'fa-vial',
                        href: route('lab.samples.register'),
                        color: 'success',
                    },
                    !isPatient && {
                        label: 'Lab requests',
                        icon: 'fa-flask',
                        href: route('lab.index'),
                    },
                ].filter(Boolean)}
            />

            <DashboardSearch
                placeholder="Search by patient name..."
                value={search}
                onChange={setSearch}
                onSubmit={applyFilters}
            />

            <RegistryTablePanel
                title="Verified lab results"
                icon="fa-file-medical-alt"
                data={results.data}
                columns={columns}
                pagination={results}
                emptyMessage={isPatient
                    ? 'No completed lab results available yet.'
                    : 'No verified lab results found.'}
                idField="request_id"
            />
        </AuthenticatedLayout>
    );
}
