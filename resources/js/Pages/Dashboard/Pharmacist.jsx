import DashboardTable from '@/Components/DashboardTable';
import { useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { formatDateTime } from '@/Utils/dateUtils';

export default function Pharmacist({ auth, stats }) {
    const columns = useMemo(() => [
        {
            header: 'Date',
            accessorKey: 'prescription_date',
            cell: ({ row }) => <span className="text-muted small">{formatDateTime(row.original.prescription_date)}</span>
        },
        {
            header: 'Patient',
            accessorKey: 'patient',
            cell: ({ row }) => <span className="fw-semibold">{row.original.patient.user.first_name} {row.original.patient.user.last_name}</span>
        },
        {
            header: 'Doctor',
            accessorKey: 'doctor',
            cell: ({ row }) => <span className="text-muted">Dr. {row.original.doctor.first_name} {row.original.doctor.last_name}</span>
        },
        {
            header: 'Action',
            id: 'actions',
            cell: ({ row }) => (
                <div className="text-end pe-2">
                    <Link href={route('prescriptions.show', row.original.prescription_id)} className="btn btn-sm btn-success rounded-pill px-3 shadow-sm transition-all hover-scale">
                        Dispense
                    </Link>
                </div>
            )
        }
    ], []);

    return (
        <AuthenticatedLayout
            user={auth.user}
        >
            <Head title="Pharmacy Dashboard" />

            <PageHeader 
                title={`Pharmacy Station - ${auth.user.first_name}`}
                breadcrumbs={[{ label: 'Dashboard', active: true }]}
                showBack={false}
            />

            <div className="container-fluid dashboard-page px-0 h-auto">
                <div className="row g-4 mb-8 h-auto">
                    <div className="col-md-4">
                        <div className="card shadow-sm border-0 h-100 p-4">
                            <div className="d-flex justify-content-between align-items-center">
                                <div>
                                    <div className="text-muted small fw-bold text-uppercase">Pending Dispensing</div>
                                    <h2 className="fw-bold mb-0">{stats.pending_prescriptions || 0}</h2>
                                </div>
                                <div className="bg-warning-subtle p-3 rounded text-warning">
                                    <i className="fas fa-prescription fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-4">
                        <div className="card shadow-sm border-0 h-100 p-4">
                            <div className="d-flex justify-content-between align-items-center">
                                <div>
                                    <div className="text-muted small fw-bold text-uppercase">Dispensed Today</div>
                                    <h2 className="fw-bold mb-0">14</h2>
                                </div>
                                <div className="bg-success-subtle p-3 rounded text-success">
                                    <i className="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-4">
                        <div className="card shadow-sm border-0 h-100 p-4">
                            <div className="d-flex justify-content-between align-items-center">
                                <div>
                                    <div className="text-muted small fw-bold text-uppercase">Low Stock Alerts</div>
                                    <h2 className="fw-bold mb-0">5</h2>
                                </div>
                                <div className="bg-danger-subtle p-3 rounded text-danger">
                                    <i className="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="row">
                    <div className="col-lg-12">
                        <div className="card shadow-sm border-0 h-100">
                            <div className="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h6 className="mb-0 fw-bold">Recent Pending Prescriptions</h6>
                                <Link href={route('prescriptions.index')} className="small text-decoration-none">View All</Link>
                            </div>
                            <div className="card-body p-0">
                                <DashboardTable 
                                    columns={columns}
                                    data={stats.recent_prescriptions || []}
                                    emptyMessage="No pending prescriptions."
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
