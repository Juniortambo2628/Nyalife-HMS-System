import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';

export default function Index({ stats }) {
    return (
        <AuthenticatedLayout
            header="Reports & Analytics"
        >
            <Head title="Reports" />

            <PageHeader 
                title="Management Reports"
                breadcrumbs={[{ label: 'Reports', active: true }]}
                actions={
                    <button className="btn btn-outline-primary rounded-pill px-4 font-bold shadow-sm">
                        <i className="fas fa-download me-2"></i>Export Summary
                    </button>
                }
            />

            <div className="py-0">
                <div className="row g-4 mb-4">
                    <div className="col-md-3">
                        <div className="card shadow-sm border-0 rounded-2xl bg-white p-4 text-center">
                            <div className="avatar-lg bg-soft-primary text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style={{ width: '64px', height: '64px' }}>
                                <i className="fas fa-users fs-3"></i>
                            </div>
                            <h3 className="fw-bold mb-1">{stats.total_patients}</h3>
                            <p className="text-gray-400 font-bold uppercase text-xs mb-0">Total Patients</p>
                        </div>
                    </div>
                    <div className="col-md-3">
                        <div className="card shadow-sm border-0 rounded-2xl bg-white p-4 text-center">
                            <div className="avatar-lg bg-soft-success text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style={{ width: '64px', height: '64px' }}>
                                <i className="fas fa-calendar-check fs-3"></i>
                            </div>
                            <h3 className="fw-bold mb-1">{stats.total_appointments}</h3>
                            <p className="text-gray-400 font-bold uppercase text-xs mb-0">Appointments</p>
                        </div>
                    </div>
                    <div className="col-md-3">
                        <div className="card shadow-sm border-0 rounded-2xl bg-white p-4 text-center">
                            <div className="avatar-lg bg-soft-info text-info rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style={{ width: '64px', height: '64px' }}>
                                <i className="fas fa-user-tie fs-3"></i>
                            </div>
                            <h3 className="fw-bold mb-1">{stats.total_staff}</h3>
                            <p className="text-gray-400 font-bold uppercase text-xs mb-0">Total Staff</p>
                        </div>
                    </div>
                    <div className="col-md-3">
                        <div className="card shadow-sm border-0 rounded-2xl bg-white p-4 text-center">
                            <div className="avatar-lg bg-soft-warning text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style={{ width: '64px', height: '64px' }}>
                                <i className="fas fa-wallet fs-3"></i>
                            </div>
                            <h3 className="fw-bold mb-1">{new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 0 }).format(stats.total_revenue)}</h3>
                            <p className="text-gray-400 font-bold uppercase text-xs mb-0">Revenue</p>
                        </div>
                    </div>
                </div>

                <div className="card shadow-sm border-0 rounded-xl bg-white p-5">
                    <div className="row align-items-center">
                        <div className="col-md-7">
                            <h4 className="fw-bold text-gray-900 mb-3">Enterprise Analytics Dashboard</h4>
                            <p className="text-muted mb-4">View comprehensive clinical and financial reports. Filter by department, doctor, or date range to gain actionable insights into hospital operations.</p>
                            <div className="d-flex flex-wrap gap-2">
                                <button className="btn btn-primary rounded-pill px-4 shadow-sm">Daily Census</button>
                                <button className="btn btn-outline-info rounded-pill px-4">Financial Summary</button>
                                <button className="btn btn-outline-warning rounded-pill px-4">Medication Usage</button>
                            </div>
                        </div>
                        <div className="col-md-5 text-center d-none d-md-block">
                            <i className="fas fa-chart-pie text-gray-100" style={{ fontSize: '10rem' }}></i>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
