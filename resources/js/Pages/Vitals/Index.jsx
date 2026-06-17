import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import PatientTableCell from '@/Components/PatientTableCell';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import StatusBadge from '@/Components/StatusBadge';
import TableActions from '@/Components/TableActions';
import { TableCellPrimary } from '@/Components/TableCells';

export default function Index({ appointments }) {
    const columns = [
        {
            header: 'Timestamp',
            accessorKey: 'appointment_time',
            cell: info => (
                <TableCellPrimary>{info.getValue() || 'Walk-in'}</TableCellPrimary>
            )
        },
        {
            header: 'Subject Identity',
            id: 'subject_identity',
            cell: ({ row }) => {
                const apt = row.original;
                return (
                    <PatientTableCell patient={apt.patient} patientId={apt.patient_id} />
                );
            }
        },
        {
            header: 'Monitoring State',
            accessorKey: 'status',
            cell: info => {
                const status = info.getValue();
                return ['scheduled', 'arrived'].includes(status) ? (
                    <StatusBadge status="pending" className="bg-warning-subtle text-warning-emphasis" />
                ) : (
                    <StatusBadge status="completed" />
                );
            }
        },
        {
            header: 'Action',
            id: 'actions',
            cell: ({ row }) => {
                const apt = row.original;
                if (!['scheduled', 'arrived'].includes(apt.status)) {
                    return null;
                }
                return (
                    <TableActions actions={[
                        {
                            label: 'Record vitals',
                            icon: 'fa-stethoscope',
                            href: route('consultations.create', {
                                appointment_id: apt.appointment_id,
                                patient_id: apt.patient_id,
                            }),
                        },
                    ]} />
                );
            }
        }
    ];

    return (
        <AuthenticatedLayout
            headerTitle="Daily Triage Queue"
            breadcrumbs={[
                { label: 'Clinical', active: false },
                { label: 'Vitals', active: true },
            ]}
        >
            <Head title="Vitals & Triage" />

            <UnifiedToolbar 
                actions={[
                    { 
                        label: 'Ad hoc vitals', 
                        icon: 'fa-plus', 
                        href: route('vitals.create') 
                    }
                ]}
            />

            <RegistryTablePanel
                title="Today's triage queue"
                icon="fa-heartbeat"
                columns={columns}
                data={appointments || []}
                emptyMessage="No appointments found for today."
            />
        </AuthenticatedLayout>
    );
}
