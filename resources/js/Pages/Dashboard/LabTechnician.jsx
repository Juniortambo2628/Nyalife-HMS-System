import DashboardTable from '@/Components/DashboardTable';
import TableActions from '@/Components/TableActions';
import PriorityBadge from '@/Components/PriorityBadge';
import StatusBadge from '@/Components/StatusBadge';
import { TableCellPrimary } from '@/Components/TableCells';
import DashboardPanel from '@/Components/DashboardPanel';
import PatientTableCell from '@/Components/PatientTableCell';
import RoleDashboardShell from '@/Components/RoleDashboardShell';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function LabTechnician({ auth, stats }) {
    const handleProcess = (id) => {
        if (confirm('Start processing this lab request?')) {
            router.post(
                route('lab.update-status', id),
                {
                    status: 'processing',
                },
                {
                    preserveScroll: true,
                    onSuccess: () => router.visit(route('lab.show', id)),
                },
            );
        }
    };

    const columns = useMemo(
        () => [
            {
                header: 'Patient',
                accessorKey: 'patient',
                cell: ({ row }) => (
                    <PatientTableCell patient={row.original.patient} patientId={row.original.patient_id} />
                ),
            },
            {
                header: 'Test type',
                accessorKey: 'test_type',
                cell: ({ row }) => <TableCellPrimary>{row.original.test_type?.test_name || 'N/A'}</TableCellPrimary>,
            },
            {
                header: 'Priority',
                accessorKey: 'priority',
                cell: ({ row }) => <PriorityBadge priority={row.original.priority || 'normal'} />,
            },
            {
                header: 'Status',
                accessorKey: 'status',
                cell: ({ row }) => <StatusBadge status={row.original.status} />,
            },
            {
                header: 'Action',
                id: 'actions',
                cell: ({ row }) => (
                    <TableActions
                        actions={
                            row.original.status === 'pending'
                                ? [
                                      {
                                          icon: 'fa-play',
                                          label: 'Start work',
                                          onClick: () => handleProcess(row.original.request_id),
                                      },
                                  ]
                                : [
                                      {
                                          icon: 'fa-flask',
                                          label: 'Enter results',
                                          href: route('lab.show', row.original.request_id),
                                      },
                                  ]
                        }
                    />
                ),
            },
        ],
        [],
    );

    const statItems = [
        {
            label: 'Awaiting processing',
            value: stats.pending_requests || 0,
            icon: 'fa-hourglass-start',
            color: 'primary',
        },
        { label: 'Completed today', value: stats.completed_today || 0, icon: 'fa-check-double', color: 'success' },
    ];

    return (
        <AuthenticatedLayout
            headerTitle={`Lab station — ${auth.user.first_name}`}
            breadcrumbs={[{ label: 'Dashboard', active: true }]}
        >
            <Head title="Lab Dashboard" />

            <UnifiedToolbar
                actions={[
                    {
                        label: 'Request registry',
                        icon: 'fa-list-ul',
                        href: route('lab.index'),
                        color: 'gray',
                    },
                    {
                        label: 'Test catalog',
                        icon: 'fa-vials',
                        href: route('lab.tests'),
                    },
                ]}
            />

            <RoleDashboardShell
                hero={{
                    title: 'Pathology lab station',
                    subtitle: `Manage diagnostic requests and reports. You have ${stats.pending_requests || 0} pending tests awaiting processing.`,
                    icon: 'fa-flask',
                }}
                statItems={statItems}
                statCols={2}
            >
                <DashboardPanel
                    title="Active diagnostics queue"
                    icon="fa-microscope"
                    className="mb-4"
                    actions={
                        <Link
                            href={route('lab.index')}
                            className="btn btn-light btn-sm rounded-pill px-3 fw-bold border text-muted"
                        >
                            Full registry
                        </Link>
                    }
                >
                    <DashboardTable
                        columns={columns}
                        data={stats.recent_requests || []}
                        emptyMessage="No pending diagnostic requests."
                    />
                </DashboardPanel>
            </RoleDashboardShell>
        </AuthenticatedLayout>
    );
}
