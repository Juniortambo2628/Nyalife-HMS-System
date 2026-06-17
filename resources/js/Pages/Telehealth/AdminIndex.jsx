import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { useMemo } from 'react';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import StatusBadge from '@/Components/StatusBadge';
import TableActions from '@/Components/TableActions';
import { TableCellPrimary, TableCellStack } from '@/Components/TableCells';
import { formatDateOnly } from '@/Utils/dateUtils';

export default function AdminIndex({ consents, auth }) {
    const columns = useMemo(() => [
        {
            header: 'Patient Name',
            accessorKey: 'patient_name',
            cell: info => (
                <TableCellStack
                    primary={info.row.original.patient_name}
                    secondary={info.row.original.patient_email}
                />
            ),
        },
        {
            header: 'Phone',
            accessorKey: 'patient_phone',
            cell: info => <TableCellPrimary>{info.getValue()}</TableCellPrimary>,
        },
        {
            header: 'Doctor Verification',
            accessorKey: 'doctor_name',
            cell: info => info.getValue() ? (
                <TableCellStack primary="Verified" secondary={info.getValue()} />
            ) : (
                <StatusBadge status="pending" />
            ),
        },
        {
            header: 'Verbal Consent',
            accessorKey: 'verbal_consent_obtained',
            cell: info => (
                <TableCellPrimary>{info.getValue() ? 'Obtained' : 'Written only'}</TableCellPrimary>
            ),
        },
        {
            header: 'Date Signed',
            accessorKey: 'signed_at',
            cell: info => (
                <TableCellPrimary className="text-muted">
                    {info.getValue() ? formatDateOnly(info.getValue()) : 'N/A'}
                </TableCellPrimary>
            ),
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: info => (
                <TableActions actions={[
                    {
                        icon: 'fa-file-contract',
                        label: 'View & counter-sign',
                        href: route('telehealth.admin.show', info.row.original.id),
                    },
                ]} />
            ),
        },
    ], []);

    return (
        <AuthenticatedLayout
            headerTitle="Telehealth Consents Registry"
            breadcrumbs={[{ label: 'Telehealth', active: true }]}
        >
            <Head title="Telehealth Consents - Admin Portal" />

            <RegistryTablePanel
                title="Signed consent forms"
                icon="fa-file-signature"
                columns={columns}
                data={consents.data || []}
                emptyMessage="No telehealth consent forms signed yet."
            />
        </AuthenticatedLayout>
    );
}
