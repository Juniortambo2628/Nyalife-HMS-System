import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import DashboardPanel from '@/Components/DashboardPanel';
import DashboardCardHeader from '@/Components/DashboardCardHeader';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import RoleDashboardShell from '@/Components/RoleDashboardShell';
import { useMemo } from 'react';
import DashboardTable from '@/Components/DashboardTable';
import TableActions from '@/Components/TableActions';
import StatusBadge from '@/Components/StatusBadge';
import { TableCellStack, TableDoctorCell } from '@/Components/TableCells';
import { formatCurrency } from '@/Utils/formatUtils';

export default function Patient({ auth, stats, recentActivity }) {
    const appointmentColumns = useMemo(
        () => [
            {
                header: 'Date & Time',
                accessorKey: 'appointment_date',
                cell: ({ row }) => (
                    <TableCellStack primary={row.original.appointment_date} secondary={row.original.appointment_time} />
                ),
            },
            {
                header: 'Doctor',
                accessorKey: 'doctor',
                cell: ({ row }) => (
                    <TableDoctorCell doctor={row.original.doctor} fallback={row.original.doctor?.user?.last_name} />
                ),
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
                        actions={[
                            {
                                icon: 'fa-eye',
                                label: 'View details',
                                href: route('appointments.show', row.original.appointment_id),
                            },
                        ]}
                    />
                ),
            },
        ],
        [],
    );

    return (
        <AuthenticatedLayout
            headerTitle={`Welcome back, ${auth.user.first_name}`}
            breadcrumbs={[{ label: 'Dashboard', active: true }]}
        >
            <Head title="Patient Dashboard" />

            <UnifiedToolbar
                actions={[
                    ...(recentActivity || []).map((activity, i) => ({
                        label: activity.subtitle || activity.btnText || 'Medical report',
                        icon: activity.icon || 'fa-file-medical',
                        href: activity.url,
                        color: i === 0 ? 'primary' : 'gray',
                    })),
                    {
                        label: 'Book visit',
                        icon: 'fa-calendar-plus',
                        href: route('appointments.create'),
                    },
                ]}
            />

            <RoleDashboardShell
                hero={{
                    title: 'Your health, simplified',
                    subtitle: `Welcome to your personal health portal. You have ${stats.my_appointments?.length || 0} upcoming visits scheduled.`,
                    icon: 'fa-heartbeat',
                }}
            >
                <div className="row g-4 mb-4">
                    <div className="col-lg-4">
                        <div className="card shadow-sm border-0 rounded-2xl bg-white h-100 shadow-hover overflow-hidden border-top border-4 border-pink-500">
                            <DashboardCardHeader title="Billing overview" icon="fa-file-invoice-dollar" />
                            <div className="card-body p-4 pt-0">
                                <div className="text-center py-4 bg-light rounded-2xl mb-4 border border-gray-100 shadow-inner">
                                    <div className="extra-small text-muted fw-bold mb-1">Total outstanding</div>
                                    <div className="fs-3 fw-extrabold text-gray-900">
                                        {formatCurrency(stats.dynamic_billing?.actual_cost || 0)}
                                    </div>
                                </div>
                                <div className="d-grid gap-3">
                                    <div className="d-flex justify-content-between align-items-center p-3 rounded-xl border border-gray-50 bg-white">
                                        <span className="extra-small fw-bold text-gray-500">Unpaid invoices</span>
                                        <span className="badge rounded-pill bg-primary-subtle text-primary px-3 py-2 fw-bold">
                                            {stats.dynamic_billing?.pending_invoices_count || 0}
                                        </span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center p-3 rounded-xl border border-gray-50 bg-white">
                                        <span className="extra-small fw-bold text-gray-500">Estimated total</span>
                                        <span className="fw-bold text-gray-700">
                                            {formatCurrency(stats.dynamic_billing?.recommended_cost || 0)}
                                        </span>
                                    </div>
                                </div>
                                <Link
                                    href={route('invoices.index')}
                                    className="btn btn-outline-pink w-100 mt-4 rounded-pill fw-bold py-2 border-2"
                                >
                                    Review all billing
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div className="col-lg-8">
                        <DashboardPanel
                            title="My appointments"
                            icon="fa-calendar-check"
                            iconClassName="text-info"
                            className="h-100"
                            actions={
                                <Link
                                    href={route('appointments.index')}
                                    className="btn btn-light btn-sm rounded-pill px-4 fw-bold border text-muted"
                                >
                                    View history
                                </Link>
                            }
                        >
                            <DashboardTable
                                columns={appointmentColumns}
                                data={stats.my_appointments || []}
                                emptyMessage="You have no upcoming appointments scheduled."
                            />
                        </DashboardPanel>
                    </div>
                </div>
            </RoleDashboardShell>
        </AuthenticatedLayout>
    );
}
