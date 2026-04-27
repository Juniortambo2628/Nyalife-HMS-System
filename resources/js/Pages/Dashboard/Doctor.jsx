import DashboardTable from '@/Components/DashboardTable';
import DashboardHero from '@/Components/DashboardHero';
import StatCard from '@/Components/StatCard';
import QuickActionCard from '@/Components/QuickActionCard';
import { useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Doctor({ auth, stats }) {
    const columns = useMemo(() => [
        {
            header: 'Time',
            accessorKey: 'appointment_time',
            cell: ({ row }) => <span className="fw-bold text-gray-900">{row.original.appointment_time}</span>
        },
        {
            header: 'Patient',
            accessorKey: 'patient',
            cell: ({ row }) => (
                <div>
                    <div className="fw-bold text-gray-900">{row.original.patient.user.first_name} {row.original.patient.user.last_name}</div>
                    <div className="extra-small text-muted fw-bold text-uppercase opacity-75">ID: PAT-{row.original.patient_id}</div>
                </div>
            )
        },
        {
            header: 'Type',
            accessorKey: 'appointment_type',
            cell: ({ row }) => (
                <span className="badge rounded-pill bg-light text-dark border px-3 py-1 fw-bold extra-small text-capitalize">
                    {row.original.appointment_type}
                </span>
            )
        },
        {
            header: 'Action',
            id: 'actions',
            cell: ({ row }) => (
                <div className="text-end">
                    <Link href={route('consultations.create', { appointment_id: row.original.appointment_id, patient_id: row.original.patient_id })} className="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm hover-scale">
                        Start Assessment
                    </Link>
                </div>
            )
        }
    ], []);

    const statItems = [
        { label: "Today's Visits", value: stats.today_appointments?.length || 0, icon: 'fa-calendar-check', color: 'info' },
        { label: 'Pending Reviews', value: stats.pending_appointments || 0, icon: 'fa-clock', color: 'warning' },
        { label: 'Completed (Week)', value: stats.completed_this_week || 0, icon: 'fa-check-double', color: 'success' }
    ];

    const quickActions = [
        { label: 'Patient Registry', sub: 'Search and view records', icon: 'fa-users', color: 'primary', url: route('patients.index') },
        { label: 'Lab Results', sub: 'Check processed reports', icon: 'fa-flask', color: 'success', url: route('lab.results') },
        { label: 'Prescription Logs', sub: 'Previous patient meds', icon: 'fa-prescription', color: 'warning', url: route('prescriptions.index') }
    ];

    return (
        <AuthenticatedLayout header="Clinician Dashboard">
            <Head title="Doctor Dashboard" />

            <PageHeader 
                title={`Medical Center - Dr. ${auth.user.first_name}`}
                breadcrumbs={[{ label: 'Dashboard', active: true }]}
                showBack={false}
            />

            <div className="px-0">
                <DashboardHero 
                    title="Clinician Command Center"
                    subtitle={`Manage your patients and reviews. You have ${stats.today_appointments?.length || 0} consultations scheduled for today.`}
                    icon="fa-user-md"
                />

                <UnifiedToolbar 
                    actions={
                        <div className="d-flex align-items-center gap-2">
                            {(stats.in_progress_consultations?.length > 0 || stats.released_labs?.length > 0) && (
                                <div className="dropdown">
                                    <button 
                                        className="btn btn-light rounded-pill px-4 py-2 fw-bold small d-flex align-items-center gap-2"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                    >
                                        <i className="fas fa-history text-warning"></i>
                                        Resume Tasks
                                        <span className="badge bg-warning text-white rounded-circle ms-1 text-2xs">
                                            {(stats.in_progress_consultations?.length || 0) + (stats.released_labs?.length || 0)}
                                        </span>
                                    </button>
                                    <ul className="dropdown-menu dropdown-menu-end shadow-2xl border-0 rounded-2xl p-2 mb-3">
                                        <div className="px-3 py-2 extra-small fw-bold text-muted text-uppercase tracking-widest opacity-50">Active Consultations</div>
                                        {stats.in_progress_consultations?.map((c, i) => (
                                            <li key={`ip-${i}`}>
                                                <Link className="dropdown-item rounded-xl py-2 px-3 d-flex align-items-center gap-3 text-dark" href={route('consultations.edit', c.consultation_id)}>
                                                    <div className="avatar-xs bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center">
                                                        <i className="fas fa-spinner fa-spin extra-small"></i>
                                                    </div>
                                                    <span className="fw-semibold small">{c.patient.user.first_name}</span>
                                                </Link>
                                            </li>
                                        ))}
                                        {stats.released_labs?.length > 0 && (
                                            <div className="px-3 py-2 extra-small fw-bold text-muted text-uppercase tracking-widest opacity-50 mt-2">Ready for Review</div>
                                        )}
                                        {stats.released_labs?.map((l, i) => (
                                            <li key={`rl-${i}`}>
                                                <Link className="dropdown-item rounded-xl py-2 px-3 d-flex align-items-center gap-3 text-dark" href={route('consultations.show', l.consultation_id)}>
                                                    <div className="avatar-xs bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center">
                                                        <i className="fas fa-flask extra-small"></i>
                                                    </div>
                                                    <span className="fw-semibold small">{l.patient.user.first_name}</span>
                                                </Link>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}
                            <Link href={route('patients.index')} className="btn btn-primary rounded-pill px-4 py-2 fw-bold small shadow-sm">
                                <i className="fas fa-search me-1"></i> Registry
                            </Link>
                        </div>
                    }
                />

                <div className="row g-4 mb-4">
                    {statItems.map((s, i) => (
                        <div key={i} className="col-md-4">
                            <StatCard {...s} />
                        </div>
                    ))}
                </div>

                <div className="row g-4">
                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 rounded-2xl mb-4 h-100 bg-white overflow-hidden shadow-hover">
                            <div className="card-header bg-white py-4 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                                <h6 className="mb-0 fw-extrabold text-gray-900"><i className="fas fa-calendar-alt text-pink-500 me-2"></i>Daily Schedule</h6>
                                <Link href={route('appointments.index')} className="btn btn-light btn-sm rounded-pill px-3 fw-bold border text-muted">Full List</Link>
                            </div>
                            <div className="card-body p-0">
                                <DashboardTable 
                                    columns={columns}
                                    data={stats.today_appointments || []}
                                    emptyMessage="No appointments scheduled for today."
                                />
                            </div>
                        </div>
                    </div>
                    <div className="col-lg-4">
                        <div className="card shadow-sm border-0 rounded-2xl mb-4 bg-white h-100 shadow-hover">
                            <div className="card-header bg-white py-4 px-4 border-bottom-0">
                                <h6 className="mb-0 fw-extrabold text-gray-900"><i className="fas fa-bolt text-warning me-2"></i>Quick Clinical Actions</h6>
                            </div>
                            <div className="card-body p-4 pt-0 d-grid gap-3">
                                {quickActions.map((a, i) => (
                                    <QuickActionCard key={i} {...a} />
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
