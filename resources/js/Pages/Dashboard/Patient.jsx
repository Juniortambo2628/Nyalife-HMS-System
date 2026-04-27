import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import StatCard from '@/Components/StatCard';
import { useState, useMemo } from 'react';
import DashboardTable from '@/Components/DashboardTable';
import DashboardHero from '@/Components/DashboardHero';
import StatusBadge from '@/Components/StatusBadge';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Patient({ auth, stats, recentActivity }) {
    const appointmentColumns = useMemo(() => [
        {
            header: 'Date & Time',
            accessorKey: 'appointment_date',
            cell: ({ row }) => (
                <div className="px-1">
                    <div className="fw-bold text-gray-900">{row.original.appointment_date}</div>
                    <div className="extra-small text-muted fw-bold opacity-75"><i className="far fa-clock me-1"></i>{row.original.appointment_time}</div>
                </div>
            )
        },
        {
            header: 'Doctor',
            accessorKey: 'doctor',
            cell: ({ row }) => (
                <div className="d-flex align-items-center">
                    <div className="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3 border border-primary-subtle shadow-inner extra-small">
                        {row.original.doctor?.user?.first_name?.charAt(0)}{row.original.doctor?.user?.last_name?.charAt(0)}
                    </div>
                    <span className="fw-semibold text-gray-700">Dr. {row.original.doctor?.user?.first_name} {row.original.doctor?.user?.last_name}</span>
                </div>
            )
        },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: ({ row }) => <StatusBadge status={row.original.status} />
        },
        {
            header: 'Action',
            id: 'actions',
            cell: ({ row }) => (
                <div className="text-end">
                    <Link href={route('appointments.show', row.original.appointment_id)} className="btn btn-sm btn-light border text-primary rounded-pill px-4 fw-bold hover-scale">
                        Details
                    </Link>
                </div>
            )
        }
    ], []);

    return (
        <AuthenticatedLayout header="My Health Portal">
            <Head title="Patient Dashboard" />

            <PageHeader 
                title={`Welcome Back, ${auth.user.first_name}`}
                breadcrumbs={[{ label: 'Dashboard', active: true }]}
                showBack={false}
            />

            <div className="px-0">
                <DashboardHero 
                    title="Your Health, Simplified."
                    subtitle={`Welcome to your personal health portal. You have ${stats.my_appointments?.length || 0} upcoming visits scheduled.`}
                    icon="fa-heartbeat"
                />

                <UnifiedToolbar 
                    actions={
                        <div className="d-flex align-items-center gap-2">
                            {recentActivity && recentActivity.length > 0 && (
                                <div className="dropdown report-dropdown-wrapper">
                                    <button 
                                        className="btn btn-light rounded-pill px-4 py-2 fw-bold small d-flex align-items-center gap-3 border shadow-sm"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                    >
                                        <div className="avatar-xs bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                                            <i className="fas fa-file-medical text-2xs"></i>
                                        </div>
                                        View Medical Reports
                                        <i className="fas fa-chevron-up extra-small opacity-30"></i>
                                    </button>
                                    <ul className="dropdown-menu dropdown-menu-end shadow-2xl border-0 rounded-2xl p-2 mb-3 bg-white">
                                        <div className="px-3 py-2 extra-small fw-extrabold text-muted text-uppercase tracking-widest opacity-50">Latest Test Reports</div>
                                        {recentActivity.map((activity, i) => (
                                            <li key={i}>
                                                <Link className="dropdown-item rounded-xl py-2 px-3 d-flex align-items-center gap-3 text-dark hover-translate-right transition-all" href={activity.url}>
                                                    <div className={`avatar-sm bg-${activity.color || 'primary'}-subtle text-${activity.color || 'primary'} rounded-lg d-flex align-items-center justify-content-center shadow-inner`}>
                                                        <i className={`fas ${activity.icon || 'fa-flask'} extra-small`}></i>
                                                    </div>
                                                    <div>
                                                        <div className="fw-bold small">{activity.subtitle || activity.btnText}</div>
                                                        <div className="extra-small text-muted fw-medium opacity-75">Click to open</div>
                                                    </div>
                                                </Link>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}
                            <Link href={route('appointments.create')} className="btn btn-primary rounded-pill px-4 py-2 fw-bold small shadow-sm">
                                <i className="fas fa-calendar-plus me-1"></i> Book Visit
                            </Link>
                        </div>
                    }
                />

                <div className="row g-4 mb-4">
                    {/* Billing Summary Widget */}
                    <div className="col-lg-4">
                        <div className="card shadow-sm border-0 rounded-2xl bg-white h-100 shadow-hover overflow-hidden border-top border-4 border-pink-500">
                            <div className="card-header bg-white py-4 px-4 border-bottom-0">
                                <h6 className="mb-0 fw-extrabold text-gray-900"><i className="fas fa-file-invoice-dollar text-pink-500 me-2"></i>Billing Overview</h6>
                            </div>
                            <div className="card-body p-4 pt-0">
                                <div className="text-center py-4 bg-light rounded-2xl mb-4 border border-gray-100 shadow-inner">
                                    <div className="extra-small text-muted fw-bold text-uppercase tracking-widest mb-1">Total Outstanding</div>
                                    <div className="fs-3 fw-extrabold text-gray-900">
                                        <span className="text-muted extra-small me-1">KES</span>
                                        {Number(stats.dynamic_billing?.actual_cost || 0).toLocaleString()}
                                    </div>
                                </div>
                                <div className="d-grid gap-3">
                                    <div className="d-flex justify-content-between align-items-center p-3 rounded-xl border border-gray-50 bg-white">
                                        <span className="extra-small fw-bold text-gray-400 text-uppercase tracking-wider">Unpaid Invoices</span>
                                        <span className="badge rounded-pill bg-primary-subtle text-primary px-3 py-2 fw-bold">{stats.dynamic_billing?.pending_invoices_count || 0}</span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center p-3 rounded-xl border border-gray-50 bg-white">
                                        <span className="extra-small fw-bold text-gray-400 text-uppercase tracking-wider">Estimated Total</span>
                                        <span className="fw-bold text-gray-700">KES {Number(stats.dynamic_billing?.recommended_cost || 0).toLocaleString()}</span>
                                    </div>
                                </div>
                                <Link href={route('invoices.index')} className="btn btn-outline-pink w-100 mt-4 rounded-pill fw-bold py-2 border-2">
                                    Review All Billing
                                </Link>
                            </div>
                        </div>
                    </div>

                    {/* Upcoming Appointments Table */}
                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 rounded-2xl h-100 bg-white overflow-hidden shadow-hover">
                            <div className="card-header bg-white py-4 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                                <h6 className="mb-0 fw-extrabold text-gray-900"><i className="fas fa-calendar-check text-info me-2"></i>My Appointments</h6>
                                <Link href={route('appointments.index')} className="btn btn-light btn-sm rounded-pill px-4 fw-bold border text-muted">View History</Link>
                            </div>
                            <div className="card-body p-0">
                                <DashboardTable 
                                    columns={appointmentColumns}
                                    data={stats.my_appointments || []}
                                    emptyMessage="You have no upcoming appointments scheduled."
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}