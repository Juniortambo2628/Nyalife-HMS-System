import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';

export default function Dashboard({ auth }) {
    const user = auth?.user || {};
    
    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />

            <PageHeader 
                title={`Welcome back, ${user.first_name || 'User'}!`}
                breadcrumbs={[{ label: 'Dashboard', active: true }]}
                showBack={false}
            />

            <div className="py-0">
                <div className="card shadow-sm border-0 rounded-2xl overflow-hidden bg-white p-6">
                    <div className="p-6 text-gray-900">
                        <div className="d-flex align-items-center gap-4 mb-8 h-auto">
                            <div className="avatar-lg bg-pink-100 text-pink-500 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style={{ width: '80px', height: '80px' }}>
                                <i className="fas fa-hand-sparkles fa-3x"></i>
                            </div>
                            <div>
                                <h1 className="fw-bold text-gray-900 mb-1">Hello, {user.first_name}!</h1>
                                <p className="text-muted mb-0">You're logged in to the Nyalife HMS Dashboard.</p>
                            </div>
                        </div>

                        <div className="row g-4 h-auto">
                            <div className="col-md-4 h-auto">
                                <div className="card border-0 bg-light-blue p-4 rounded-xl h-100">
                                    <h5 className="fw-bold mb-3"><i className="fas fa-calendar-check text-blue-500 me-2"></i>Appointments</h5>
                                    <p className="small text-muted mb-4">View and manage upcoming medical consultations.</p>
                                    <Link href="/appointments" className="btn btn-primary btn-sm rounded-pill px-4">View All</Link>
                                </div>
                            </div>
                            
                            {user.role_name !== 'receptionist' && (
                                <>
                                    <div className="col-md-4 h-auto">
                                        <div className="card border-0 bg-light-pink p-4 rounded-xl h-100">
                                            <h5 className="fw-bold mb-3"><i className="fas fa-file-prescription text-pink-500 me-2"></i>Prescriptions</h5>
                                            <p className="small text-muted mb-4">Access prescribed medications and health plans.</p>
                                            <Link href="/prescriptions" className="btn btn-primary btn-sm rounded-pill px-4">View All</Link>
                                        </div>
                                    </div>
                                    <div className="col-md-4 h-auto">
                                        <div className="card border-0 bg-light-green p-4 rounded-xl h-100">
                                            <h5 className="fw-bold mb-3"><i className="fas fa-flask text-success me-2"></i>Lab Results</h5>
                                            <p className="small text-muted mb-4">Check the status and results of laboratory tests.</p>
                                            <Link href="/lab-results" className="btn btn-primary btn-sm rounded-pill px-4">View All</Link>
                                        </div>
                                    </div>
                                </>
                            )}
                            
                            {user.role_name === 'receptionist' && (
                                <div className="col-md-4 h-auto">
                                    <div className="card border-0 bg-light-pink p-4 rounded-xl h-100">
                                        <h5 className="fw-bold mb-3"><i className="fas fa-users text-pink-500 me-2"></i>Patients</h5>
                                        <p className="small text-muted mb-4">Register new patients and manage records.</p>
                                        <Link href="/patients" className="btn btn-primary btn-sm rounded-pill px-4">View All</Link>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
            <style>{`
                .bg-light-blue { background-color: #f0f7ff; }
                .bg-light-pink { background-color: #fff1f2; }
                .bg-light-green { background-color: #f0fdf4; }
                .rounded-xl { border-radius: 1rem; }
            `}</style>
        </AuthenticatedLayout>
    );
}
