import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useMemo } from 'react';
import DashboardTable from '@/Components/DashboardTable';
import StatusBadge from '@/Components/StatusBadge';

export default function AdminIndex({ consents, auth }) {
    const columns = useMemo(() => [
        {
            header: 'Patient Name',
            accessorKey: 'patient_name',
            cell: info => (
                <div>
                    <div className="fw-bold text-gray-900">{info.row.original.patient_name}</div>
                    <div className="extra-small text-muted fw-bold text-uppercase opacity-75">{info.row.original.patient_email}</div>
                </div>
            )
        },
        {
            header: 'Phone',
            accessorKey: 'patient_phone',
            cell: info => <span className="small fw-semibold">{info.getValue()}</span>
        },
        {
            header: 'Doctor Verification',
            accessorKey: 'doctor_name',
            cell: info => info.getValue() ? (
                <div>
                    <span className="badge bg-success-subtle text-success fw-bold rounded-pill px-3 py-1 extra-small">
                        Verified
                    </span>
                    <div className="extra-small text-muted fw-semibold mt-1">{info.getValue()}</div>
                </div>
            ) : (
                <span className="badge bg-warning-subtle text-warning fw-bold rounded-pill px-3 py-1 extra-small">
                    Pending Verification
                </span>
            )
        },
        {
            header: 'Verbal Consent',
            accessorKey: 'verbal_consent_obtained',
            cell: info => info.getValue() ? (
                <span className="badge bg-info-subtle text-info fw-bold rounded-pill px-3 py-1 extra-small">Obtained</span>
            ) : (
                <span className="badge bg-light text-muted fw-bold rounded-pill px-3 py-1 extra-small border">Written Only</span>
            )
        },
        {
            header: 'Date Signed',
            accessorKey: 'signed_at',
            cell: info => <span className="extra-small fw-bold text-gray-500 text-uppercase">{info.getValue() ? new Date(info.getValue()).toLocaleDateString() : 'N/A'}</span>
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: info => (
                <div className="d-flex justify-content-end gap-2">
                    <Link href={route('telehealth.admin.show', info.row.original.id)} className="btn btn-sm btn-light border text-pink-500 rounded-circle p-2 shadow-sm avatar-sm d-flex align-items-center justify-content-center" title="View & Counter-Sign">
                        <i className="fas fa-file-contract extra-small"></i>
                    </Link>
                </div>
            )
        }
    ], []);

    return (
        <AuthenticatedLayout 
            header="Telehealth Consents Registry"
            auth={auth}
        >
            <Head title="Telehealth Consents - Admin Portal" />

            <div className="container-fluid py-4">
                <div className="card shadow-sm border-0 rounded-4 bg-white overflow-hidden">
                    <div className="card-header bg-white py-4 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                        <h6 className="mb-0 fw-extrabold text-gray-900"><i className="fas fa-file-signature text-pink-500 me-2"></i>Signed Consent Forms</h6>
                    </div>
                    <div className="card-body p-0">
                        <DashboardTable 
                            columns={columns}
                            data={consents.data || []}
                            emptyMessage="No telehealth consent forms signed yet."
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
