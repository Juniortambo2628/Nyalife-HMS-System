import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

import DashboardPanel from '@/Components/DashboardPanel';
import RoleDashboardShell from '@/Components/RoleDashboardShell';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Admin({ auth, stats, recentActivity }) {
    const statItems = [
        { label: 'Total Users', value: stats.total_users || 0, icon: 'fa-users-cog', color: 'primary', trend: '+12%' },
        { label: 'Active Patients', value: stats.active_patients || 0, icon: 'fa-user-injured', color: 'success', trend: '+5.4%' },
        { label: "Today's Visits", value: stats.today_appointments || 0, icon: 'fa-calendar-day', color: 'info', trend: 'Steady' },
        { label: 'System Alerts', value: 3, icon: 'fa-exclamation-triangle', color: 'warning', trend: 'Low' }
    ];

    return (
        <AuthenticatedLayout
            headerTitle="System Overview"
            breadcrumbs={[{ label: 'Dashboard', active: true }]}
        >
            <Head title="Admin Dashboard" />

            <UnifiedToolbar
                actions={[
                    {
                        label: 'New Staff',
                        icon: 'fa-user-plus',
                        href: route('users.create'),
                    },
                    {
                        label: 'Analytics',
                        icon: 'fa-chart-line',
                        href: route('reports.index'),
                        color: 'light',
                    },
                ]}
            />

            <RoleDashboardShell
                hero={{
                    title: 'Admin Command Center',
                    subtitle: `Hospital systems are fully operational. You have ${stats.today_appointments || 0} appointments scheduled for today.`,
                    icon: 'fa-shield-alt',
                }}
                statItems={statItems}
            >
                <div className="row g-4 mb-4">
                    <div className="col-lg-7">
                        <DashboardPanel
                            title="Recent System Activity"
                            icon="fa-history"
                            className="h-100"
                            bodyClassName="px-4 pt-0 pb-4"
                            actions={
                                <button type="button" className="btn btn-light btn-sm rounded-pill px-3 fw-bold text-muted border">
                                    View Logs
                                </button>
                            }
                        >
                            {recentActivity && recentActivity.length > 0 ? (
                                <div className="d-grid gap-3">
                                    {recentActivity.map((act, index) => (
                                        <div key={index} className="d-flex align-items-center gap-3 p-3 rounded-xl border border-light shadow-sm bg-white">
                                            <div className={`avatar-md bg-${act.color || 'gray'}-subtle text-${act.color || 'gray'} rounded-lg d-flex align-items-center justify-content-center flex-shrink-0`}>
                                                <i className={`fas ${act.icon || 'fa-info-circle'}`}></i>
                                            </div>
                                            <div className="flex-grow-1">
                                                <div className="fw-bold text-gray-900 small">{act.title}</div>
                                                <div className="extra-small text-muted fw-bold">{act.time}</div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center py-5 bg-light rounded-2xl">
                                    <i className="fas fa-stream text-gray-200 fs-1 mb-3 d-block"></i>
                                    <p className="text-gray-400 fw-bold mb-0">No recent system activity detected.</p>
                                </div>
                            )}
                        </DashboardPanel>
                    </div>

                    <div className="col-lg-5">
                        <DashboardPanel
                            title="Facility Performance"
                            icon="fa-chart-bar"
                            iconClassName="text-info"
                            className="h-100"
                            bodyClassName="p-4 pt-0"
                        >
                            <div className="extra-small text-muted fw-bold">Weekly Appointment Volume</div>
                            <div className="d-flex align-items-end justify-content-between pt-4" style={{ height: '200px' }}>
                                {stats.performance?.data?.map((val, idx) => {
                                    const max = Math.max(...stats.performance.data, 5);
                                    const height = (val / max) * 100;
                                    return (
                                        <div key={idx} className="flex-grow-1 text-center d-flex flex-column align-items-center h-100">
                                            <div className="flex-grow-1 d-flex align-items-end w-100 px-2">
                                                <div
                                                    className="w-100 bg-primary rounded-top transition-all"
                                                    style={{ height: `${height}%`, opacity: 0.6 + (height / 250) }}
                                                    title={`${val} appointments`}
                                                ></div>
                                            </div>
                                            <div className="mt-3 extra-small fw-bold text-gray-400">{stats.performance.labels[idx]}</div>
                                        </div>
                                    );
                                })}
                            </div>
                        </DashboardPanel>
                    </div>
                </div>
            </RoleDashboardShell>
        </AuthenticatedLayout>
    );
}
