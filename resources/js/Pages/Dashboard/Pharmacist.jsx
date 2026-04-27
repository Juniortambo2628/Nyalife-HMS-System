import DashboardTable from '@/Components/DashboardTable';
import StatCard from '@/Components/StatCard';
import { useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { formatDateTime } from '@/Utils/dateUtils';
import DashboardHero from '@/Components/DashboardHero';

export default function Pharmacist({ auth, stats }) {
    const columns = useMemo(() => [
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
            header: 'Prescribing Physician',
            accessorKey: 'doctor',
            cell: ({ row }) => (
                <div className="small text-muted fw-medium">
                    Dr. {row.original.doctor.first_name} {row.original.doctor.last_name}
                </div>
            )
        },
        {
            header: 'Prescription Date',
            accessorKey: 'prescription_date',
            cell: ({ row }) => <span className="extra-small fw-bold text-gray-500 text-uppercase">{formatDateTime(row.original.prescription_date)}</span>
        },
        {
            header: 'Action',
            id: 'actions',
            cell: ({ row }) => (
                <div className="text-end">
                    <Link href={route('prescriptions.show', row.original.prescription_id)} className="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-sm hover-scale">
                        Dispense Meds
                    </Link>
                </div>
            )
        }
    ], []);

    const statItems = [
        { label: 'Pending Dispensing', value: stats.pending_prescriptions || 0, icon: 'fa-prescription', color: 'warning' },
        { label: 'Dispensed Today', value: stats.dispensed_today || 14, icon: 'fa-check-circle', color: 'success' },
        { label: 'Low Stock Alerts', value: stats.low_stock || 5, icon: 'fa-exclamation-triangle', color: 'danger' }
    ];

    return (
        <AuthenticatedLayout 
            header="Pharmacy Dashboard"
            toolbarActions={
                <div className="d-flex align-items-center gap-2">
                    <Link href={route('pharmacy.inventory')} className="btn btn-light border rounded-pill px-4 py-2 fw-bold small shadow-sm">
                        <i className="fas fa-boxes me-1"></i> Inventory
                    </Link>
                    <Link href={route('pharmacy.medicines')} className="btn btn-primary rounded-pill px-4 py-2 fw-bold small shadow-sm">
                        <i className="fas fa-pills me-1"></i> Medicine Registry
                    </Link>
                </div>
            }
        >
            <Head title="Pharmacy Dashboard" />

            <PageHeader 
                title={`Dispensing Station - ${auth.user.first_name}`}
                breadcrumbs={[{ label: 'Dashboard', active: true }]}
                showBack={false}
            />

            <div className="px-0">
                <DashboardHero 
                    title="Pharmacy Management Station"
                    subtitle={`Oversee prescriptions and inventory. You have ${stats.pending_prescriptions || 0} pending orders awaiting dispensing.`}
                    icon="fa-pills"
                />


                <div className="row g-4 mb-4">
                    {statItems.map((s, i) => (
                        <div key={i} className="col-md-4">
                            <StatCard {...s} />
                        </div>
                    ))}
                </div>

                <div className="card shadow-sm border-0 rounded-2xl mb-4 bg-white overflow-hidden shadow-hover">
                    <div className="card-header bg-white py-4 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                        <h6 className="mb-0 fw-extrabold text-gray-900"><i className="fas fa-history text-pink-500 me-2"></i>Active Prescription Queue</h6>
                        <Link href={route('prescriptions.index')} className="btn btn-light btn-sm rounded-pill px-3 fw-bold border text-muted">Full Registry</Link>
                    </div>
                    <div className="card-body p-0">
                        <DashboardTable 
                            columns={columns}
                            data={stats.recent_prescriptions || []}
                            emptyMessage="No pending prescriptions in the queue."
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
