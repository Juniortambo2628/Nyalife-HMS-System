import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';

export default function Admin({ auth, stats, recentActivity }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
        >
            <Head title="Admin Dashboard" />

            <PageHeader 
                title="Administrator Overview"
                breadcrumbs={[{ label: 'Dashboard', active: true }]}
                showBack={false}
            />

            <div className="container-fluid dashboard-page px-0 h-auto">
                <div className="row g-4 mb-8 h-auto">
                    <div className="col-md-3">
                        <div className="card shadow-sm border-0 p-4 bg-primary text-white">
                            <div className="d-flex justify-content-between align-items-center">
                                <div>
                                    <div className="small opacity-75 fw-bold text-uppercase">Total Users</div>
                                    <h2 className="fw-bold mb-0">{stats.total_users || 0}</h2>
                                </div>
                                <i className="fas fa-users-cog fa-3x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-3">
                        <div className="card shadow-sm border-0 p-4 bg-success text-white">
                            <div className="d-flex justify-content-between align-items-center">
                                <div>
                                    <div className="small opacity-75 fw-bold text-uppercase">Active Patients</div>
                                    <h2 className="fw-bold mb-0">{stats.active_patients || 0}</h2>
                                </div>
                                <i className="fas fa-user-injured fa-3x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-3">
                        <div className="card shadow-sm border-0 p-4 bg-info text-white">
                            <div className="d-flex justify-content-between align-items-center">
                                <div>
                                    <div className="small opacity-75 fw-bold text-uppercase">Today's Visits</div>
                                    <h2 className="fw-bold mb-0">{stats.today_appointments || 0}</h2>
                                </div>
                                <i className="fas fa-calendar-day fa-3x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-3">
                        <div className="card shadow-sm border-0 p-4 bg-warning text-dark">
                            <div className="d-flex justify-content-between align-items-center">
                                <div>
                                    <div className="small opacity-75 fw-bold text-uppercase">System Alerts</div>
                                    <h2 className="fw-bold mb-0">3</h2>
                                </div>
                                <i className="fas fa-exclamation-triangle fa-3x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="row">
                    <div className="col-lg-6">
                        <div className="card shadow-sm border-0 mb-4 h-100">
                            <div className="card-header bg-white py-3">
                                <h6 className="mb-0 fw-bold">Recent System Activity</h6>
                            </div>
                            <div className="card-body">
                                <div className="list-group list-group-flush">
                                    {recentActivity && recentActivity.length > 0 ? (
                                        recentActivity.map((act, index) => (
                                            <div key={index} className="list-group-item px-0 border-0 mb-3 d-flex align-items-center animate-in" style={{ animationDelay: `${index * 100}ms` }}>
                                                <div className={`bg-light p-2 rounded text-${act.color} me-3`}>
                                                    <i className={`fas ${act.icon}`}></i>
                                                </div>
                                                <div>
                                                    <div className="fw-bold small">{act.title}</div>
                                                    <div className="text-muted extra-small">{act.time}</div>
                                                </div>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="text-center py-4 text-muted small">No recent activity detected.</div>
                                    )}
                                </div>
                            </div>
                            <div className="card-footer bg-white border-top-0 py-3 text-center d-none">
                                <Link href="#" className="small text-decoration-none">View System Logs</Link>
                            </div>
                        </div>
                    </div>
                    
                    <div className="col-lg-6">
                        <div className="card shadow-sm border-0 mb-4 h-100">
                            <div className="card-header bg-white py-3">
                                <h6 className="mb-0 fw-bold">Hospital Performance (Weekly)</h6>
                            </div>
                            <div className="card-body">
                                <div className="d-flex align-items-end justify-content-between pt-4" style={{ height: '200px' }}>
                                    {stats.performance?.data?.map((val, idx) => {
                                        const max = Math.max(...stats.performance.data, 5);
                                        const height = (val / max) * 100;
                                        return (
                                            <div key={idx} className="flex-grow-1 text-center d-flex flex-column align-items-center h-100">
                                                <div className="flex-grow-1 d-flex align-items-end w-100 px-2">
                                                    <div 
                                                        className="w-100 bg-primary rounded-top animate-grow" 
                                                        style={{ 
                                                            height: `${height}%`, 
                                                            opacity: 0.6 + (height / 250),
                                                            transition: 'height 1s ease-out'
                                                        }}
                                                        title={`${val} appointments`}
                                                    ></div>
                                                </div>
                                                <div className="mt-3 small fw-bold text-gray-400">{stats.performance.labels[idx]}</div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <style>{`
                .extra-small {
                    font-size: 0.75rem;
                }
            `}</style>
        </AuthenticatedLayout>
    );
}
