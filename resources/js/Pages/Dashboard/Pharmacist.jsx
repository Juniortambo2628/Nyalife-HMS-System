import DashboardTable from '@/Components/DashboardTable';
import TableActions from '@/Components/TableActions';
import { TableCellPrimary, TableCellStack } from '@/Components/TableCells';
import DashboardPanel from '@/Components/DashboardPanel';
import PatientTableCell from '@/Components/PatientTableCell';
import RoleDashboardShell from '@/Components/RoleDashboardShell';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { formatDateTime } from '@/Utils/dateUtils';

export default function Pharmacist({ auth, stats }) {
    const columns = useMemo(() => [
        {
            header: 'Patient',
            accessorKey: 'patient',
            cell: ({ row }) => (
                <PatientTableCell patient={row.original.patient} patientId={row.original.patient_id} />
            )
        },
        {
            header: 'Prescribing physician',
            accessorKey: 'doctor',
            cell: ({ row }) => (
                <TableCellStack
                    primary={`Dr. ${row.original.doctor?.user?.first_name || row.original.doctor?.first_name || ''} ${row.original.doctor?.user?.last_name || row.original.doctor?.last_name || ''}`.trim()}
                />
            )
        },
        {
            header: 'Prescription date',
            accessorKey: 'prescription_date',
            cell: ({ row }) => (
                <TableCellPrimary className="text-muted">{formatDateTime(row.original.prescription_date)}</TableCellPrimary>
            )
        },
        {
            header: 'Action',
            id: 'actions',
            cell: ({ row }) => (
                <TableActions actions={[
                    {
                        icon: 'fa-prescription-bottle',
                        label: 'Dispense meds',
                        href: route('prescriptions.show', row.original.prescription_id),
                        color: 'success',
                    },
                ]} />
            )
        }
    ], []);

    const statItems = [
        { label: 'Pending dispensing', value: stats.pending_prescriptions || 0, icon: 'fa-prescription', color: 'warning' },
        { label: 'Dispensed today', value: stats.dispensed_today || 14, icon: 'fa-check-circle', color: 'success' },
        { label: 'Low stock alerts', value: stats.low_stock || 5, icon: 'fa-exclamation-triangle', color: 'danger' }
    ];

    return (
        <AuthenticatedLayout
            headerTitle={`Dispensing station — ${auth.user.first_name}`}
            breadcrumbs={[{ label: 'Dashboard', active: true }]}
        >
            <Head title="Pharmacy Dashboard" />

            <UnifiedToolbar
                actions={[
                    {
                        label: 'Inventory',
                        icon: 'fa-boxes',
                        href: route('pharmacy.inventory'),
                        color: 'gray',
                    },
                    {
                        label: 'Medicine registry',
                        icon: 'fa-pills',
                        href: route('pharmacy.medicines'),
                    },
                ]}
            />

            <RoleDashboardShell
                hero={{
                    title: 'Pharmacy management station',
                    subtitle: `Oversee prescriptions and inventory. You have ${stats.pending_prescriptions || 0} pending orders awaiting dispensing.`,
                    icon: 'fa-pills',
                }}
                statItems={statItems}
                statCols={3}
            >
                <DashboardPanel
                    title="Active prescription queue"
                    icon="fa-history"
                    className="mb-4"
                    actions={
                        <Link href={route('prescriptions.index')} className="btn btn-light btn-sm rounded-pill px-3 fw-bold border text-muted">Full registry</Link>
                    }
                >
                    <DashboardTable 
                        columns={columns}
                        data={stats.recent_prescriptions || []}
                        emptyMessage="No pending prescriptions in the queue."
                    />
                </DashboardPanel>
            </RoleDashboardShell>
        </AuthenticatedLayout>
    );
}
