import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import DashboardHero from '@/Components/DashboardHero';

export default function Dashboard({ auth }) {
    const user = auth?.user || {};
    
    return (
        <AuthenticatedLayout 
            header="HMS Dashboard"
            toolbarActions={
                <div className="d-flex align-items-center gap-2">
                    <Link href="/appointments" className="btn btn-primary rounded-pill px-4 py-2 fw-bold small shadow-sm">
                        <i className="fas fa-calendar-alt me-1"></i> View Schedule
                    </Link>
                    <Link href="/patients" className="btn btn-outline-light rounded-pill px-4 py-2 fw-bold small">
                        <i className="fas fa-users me-1"></i> Patient Registry
                    </Link>
                </div>
            }
        >
            <Head title="Dashboard" />

            <PageHeader 
                title={`Welcome back, ${user.first_name || 'User'}!`}
                breadcrumbs={[{ label: 'Dashboard', active: true }]}
                showBack={false}
            />

            <div className="px-0">
                <DashboardHero 
                    title={`Hello, ${user.first_name}!`}
                    subtitle={`Welcome to the Nyalife HMS Command Center. Your access level: ${user.role?.replace('_', ' ').toUpperCase() || 'GENERAL'}`}
                    icon="fa-hospital"
                />


                <div className="card shadow-sm border-0 rounded-2xl bg-white p-5 shadow-hover">
                    <div className="d-flex align-items-center gap-4 mb-5">
                        <div className="avatar-xl bg-primary-subtle text-primary rounded-2xl d-flex align-items-center justify-content-center flex-shrink-0 shadow-inner">
                            <i className="fas fa-hand-sparkles fa-3x"></i>
                        </div>
                        <div>
                            <h2 className="fw-extrabold text-gray-900 mb-1">Getting Started</h2>
                            <p className="text-muted mb-0 fw-medium">Use the quick access modules below to navigate the system.</p>
                        </div>
                    </div>

                    <div className="row g-4">
                        <div className="col-md-4">
                            <Link href="/appointments" className="card border border-gray-100 bg-blue-50 p-5 rounded-2xl h-100 text-decoration-none shadow-hover transition-all">
                                <div className="bg-white rounded-xl p-3 avatar-lg d-flex align-items-center justify-content-center shadow-sm mb-4">
                                    <i className="fas fa-calendar-check text-info"></i>
                                </div>
                                <h5 className="fw-extrabold text-gray-900 mb-2">Appointments</h5>
                                <p className="small text-muted mb-0 fw-medium">View and manage upcoming medical consultations and patient visits.</p>
                            </Link>
                        </div>
                        
                        {user.role !== 'receptionist' && (
                            <>
                                <div className="col-md-4">
                                    <Link href="/prescriptions" className="card border border-gray-100 bg-pink-50 p-5 rounded-2xl h-100 text-decoration-none shadow-hover transition-all">
                                        <div className="bg-white rounded-xl p-3 avatar-lg d-flex align-items-center justify-content-center shadow-sm mb-4">
                                            <i className="fas fa-file-prescription text-primary"></i>
                                        </div>
                                        <h5 className="fw-extrabold text-gray-900 mb-2">Prescriptions</h5>
                                        <p className="small text-muted mb-0 fw-medium">Access and manage prescribed medications and therapeutic plans.</p>
                                    </Link>
                                </div>
                                <div className="col-md-4">
                                    <Link href="/lab-results" className="card border border-gray-100 bg-green-50 p-5 rounded-2xl h-100 text-decoration-none shadow-hover transition-all">
                                        <div className="bg-white rounded-xl p-3 avatar-lg d-flex align-items-center justify-content-center shadow-sm mb-4">
                                            <i className="fas fa-flask text-success"></i>
                                        </div>
                                        <h5 className="fw-extrabold text-gray-900 mb-2">Lab Results</h5>
                                        <p className="small text-muted mb-0 fw-medium">Monitor the status of diagnostic tests and review clinical reports.</p>
                                    </Link>
                                </div>
                            </>
                        )}
                        
                        {user.role === 'receptionist' && (
                            <div className="col-md-4">
                                <Link href="/patients" className="card border border-gray-100 bg-pink-50 p-5 rounded-2xl h-100 text-decoration-none shadow-hover transition-all">
                                    <div className="bg-white rounded-xl p-3 avatar-lg d-flex align-items-center justify-content-center shadow-sm mb-4">
                                        <i className="fas fa-users text-primary"></i>
                                    </div>
                                    <h5 className="fw-extrabold text-gray-900 mb-2">Patient Records</h5>
                                    <p className="small text-muted mb-0 fw-medium">Register new patients and manage demographic information records.</p>
                                </Link>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
