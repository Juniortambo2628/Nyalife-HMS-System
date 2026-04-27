import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import DashboardHero from '@/Components/DashboardHero';
import StatCard from '@/Components/StatCard';

export default function Admin({ auth, stats, recentActivity }) {
    const statItems = [
        { label: 'Total Users', value: stats.total_users || 0, icon: 'fa-users-cog', color: 'primary', trend: '+12%' },
        { label: 'Active Patients', value: stats.active_patients || 0, icon: 'fa-user-injured', color: 'success', trend: '+5.4%' },
        { label: "Today's Visits", value: stats.today_appointments || 0, icon: 'fa-calendar-day', color: 'info', trend: 'Steady' },
        { label: 'System Alerts', value: 3, icon: 'fa-exclamation-triangle', color: 'warning', trend: 'Low' }
    ];

    return (
        <AuthenticatedLayout 
            header="Administrator Dashboard"
            toolbarActions={
                <div className="d-flex align-items-center gap-2">
                    <Link href={route('users.create')} className="btn btn-primary rounded-pill px-4 py-2 fw-bold small shadow-sm">
                        <i className="fas fa-user-plus me-1"></i> New Staff
                    </Link>
                    <Link href={route('reports.index')} className="btn btn-outline-light rounded-pill px-4 py-2 fw-bold small">
                        <i className="fas fa-chart-line me-1"></i> Analytics
                    </Link>
                </div>
            }
        >
            <Head title="Admin Dashboard" />

            <PageHeader 
                title="System Overview"
                breadcrumbs={[{ label: 'Dashboard', active: true }]}
                showBack={false}
            />

            <div className="px-0">
                <DashboardHero 
                    title="Admin Command Center"
                    subtitle={`Hospital systems are fully operational. You have ${stats.today_appointments || 0} appointments scheduled for today.`}
                    icon="fa-shield-alt"
                />


                <div className="row g-4 mb-4">
                    {statItems.map((s, i) => (
                        <div key={i} className="col-md-6 col-lg-3">
                            <StatCard {...s} />
                        </div>
                    ))}
                </div>

                <div className="row g-4 mb-4">
                    <div className="col-lg-7">
                        <div className="card shadow-sm border-0 rounded-2xl h-100 bg-white">
                            <div className="card-header bg-white py-4 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                                <h6 className="mb-0 fw-extrabold text-gray-900"><i className="fas fa-history text-pink-500 me-2"></i>Recent System Activity</h6>
                                <button className="btn btn-light btn-sm rounded-pill px-3 fw-bold text-muted border">View Logs</button>
                            </div>
                            <div className="card-body px-4 pt-0">
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
                            </div>
                        </div>
                    </div>
                    
                    <div className="col-lg-5">
                        <div className="card shadow-sm border-0 rounded-2xl h-100 bg-white overflow-hidden">
                            <div className="card-header bg-white py-4 px-4 border-bottom-0">
                                <h6 className="mb-0 fw-extrabold text-gray-900"><i className="fas fa-chart-bar text-info me-2"></i>Facility Performance</h6>
                                <div className="extra-small text-muted fw-bold mt-1">Weekly Appointment Volume</div>
                            </div>
                            <div className="card-body p-4 pt-0">
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
