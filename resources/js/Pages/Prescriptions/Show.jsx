import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { PatientIdLabel } from '@/Components/PatientTableCell';
import DashboardTable from '@/Components/DashboardTable';
import { useState } from 'react';

export default function Show({ prescription, auth }) {
    const isDoctorOrAdmin = ['doctor', 'admin'].includes(auth.user.role);
    const [isVoidModalOpen, setIsVoidModalOpen] = useState(false);

    const columns = [
        {
            header: 'Medicine',
            id: 'medicine',
            cell: ({ row }) => {
                const item = row.original;
                return <span className="fw-extrabold text-gray-900">{item.medication?.medication_name || item.medicine_name || 'N/A'}</span>;
            }
        },
        {
            header: 'Dosage',
            accessorKey: 'dosage',
            cell: info => <span className="text-gray-700 fw-bold small">{info.getValue()}</span>
        },
        {
            header: 'Frequency',
            accessorKey: 'frequency',
            cell: info => <span className="text-gray-700 fw-bold small">{info.getValue()}</span>
        },
        {
            header: 'Duration',
            accessorKey: 'duration',
            cell: info => <span className="text-muted fw-bold extra-small text-uppercase">{info.getValue()}</span>
        }
    ];

    const { data: voidData, setData: setVoidData, delete: destroyPrescription, processing: voidProcessing, reset: resetVoid } = useForm({
        void_reason: ''
    });

    const handleVoid = (e) => {
        e.preventDefault();
        destroyPrescription(route('prescriptions.destroy', prescription.prescription_id), {
            onSuccess: () => {
                setIsVoidModalOpen(false);
                resetVoid();
            }
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            headerTitle={`RX-${String(prescription.prescription_id).padStart(5, '0')}`}
            breadcrumbs={[
                { label: 'Pharmacy', url: route('prescriptions.index') },
                { label: 'Prescription Details', active: true },
            ]}
        >
            <Head title={`Prescription - RX-${String(prescription.prescription_id).padStart(5, '0')}`} />

            <div className="px-0 py-0 pb-5">
                <div className="row">
                    <div className="col-lg-4">
                        <div className="card shadow-sm border-0 mb-4 text-center p-5 rounded-4 bg-white shadow-hover">
                            <div className="mb-4">
                                <span className={`badge rounded-pill px-3 py-2 fw-extrabold extra-small tracking-widest ${prescription.status === 'dispensed' ? 'bg-success text-white' : 'bg-warning text-dark'}`}>
                                    {prescription.status.toUpperCase()}
                                </span>
                            </div>
                            <div className="avatar-xl mx-auto mb-3 bg-pink-50 text-pink-500 fw-extrabold shadow-inner rounded-circle d-flex align-items-center justify-content-center tracking-tightest fs-2">
                                {prescription.patient?.user?.first_name?.charAt(0) || 'P'}
                            </div>
                            <h5 className="mb-1 fw-extrabold text-gray-900 tracking-tighter">{prescription.patient?.user?.first_name || 'Unknown'} {prescription.patient?.user?.last_name || 'Patient'}</h5>
                            <PatientIdLabel
                                id={prescription.patient_id}
                                variant="pat-id"
                                className="extra-small font-bold text-muted opacity-50 tracking-widest uppercase mb-4"
                            />
                            
                            <div className="space-y-3 pt-4 border-top border-gray-50 text-start">
                                <div className="d-flex justify-content-between align-items-center">
                                    <span className="extra-small fw-bold text-muted text-uppercase tracking-widest">Prescribed</span>
                                    <span className="fw-extrabold text-gray-800 small">{prescription.prescription_date}</span>
                                </div>
                                <div className="d-flex justify-content-between align-items-center">
                                    <span className="extra-small fw-bold text-muted text-uppercase tracking-widest">Doctor</span>
                                    <span className="fw-extrabold text-gray-800 small">Dr. {prescription.doctor?.last_name || prescription.doctor?.user?.last_name || 'Staff'}</span>
                                </div>
                            </div>
                            
                            {prescription.consultation_id && (
                                <div className="text-start p-3 bg-light rounded my-4">
                                    <h6 className="fw-bold small text-muted text-uppercase mb-2">Associated Consultation</h6>
                                    <div className="mb-2"><strong>ID:</strong> #{prescription.consultation_id}</div>
                                    <div className="mb-3 small text-truncate" title={prescription.consultation?.diagnosis || prescription.consultation?.notes || 'No summary available'}>
                                        <strong>Summary:</strong> {prescription.consultation?.diagnosis || prescription.consultation?.notes || 'No summary available'}
                                    </div>
                                    <Link href={route('consultations.show', prescription.consultation_id)} className="btn btn-outline-primary btn-sm w-100">
                                        <i className="fas fa-external-link-alt me-1"></i> View Consultation
                                    </Link>
                                </div>
                            )}

                            {auth.user.role === 'pharmacist' && prescription.status === 'pending' && (
                                <button onClick={() => router.post(route('prescriptions.dispense', prescription.prescription_id))} className="btn btn-success w-100 shadow-sm mt-4">
                                    <i className="fas fa-check-circle me-2"></i>Mark as Dispensed
                                </button>
                            )}
                        </div>
                    </div>

                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 rounded-4 overflow-hidden bg-white shadow-hover">
                            <div className="card-header bg-white py-4 px-4 border-bottom-0">
                                <h6 className="mb-0 fw-extrabold text-pink-500 extra-small text-uppercase tracking-widest">
                                    <i className="fas fa-pills me-2"></i>Medication Schedule
                                </h6>
                            </div>
                            <div className="card-body p-0">
                                <DashboardTable 
                                    columns={columns}
                                    data={prescription.items || []}
                                    emptyMessage="No medication schedule items found."
                                    noCard={true}
                                    headerBgClassName="bg-pink-500"
                                />
                            </div>
                            {prescription.notes && (
                                <div className="p-4 bg-gray-50 rounded-xl m-4 mt-0 border border-gray-100 shadow-inner">
                                    <h6 className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-2 opacity-50">Clinical Notes / Instructions</h6>
                                    <p className="mb-0 text-gray-700 font-medium small italic">"{prescription.notes}"</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            <UnifiedToolbar 
                actions={[
                    isDoctorOrAdmin && prescription.status === 'pending' && {
                        label: 'EDIT RX',
                        icon: 'fa-edit',
                        href: route('prescriptions.edit', prescription.prescription_id),
                        color: 'primary'
                    },
                    isDoctorOrAdmin && prescription.status === 'pending' && {
                        label: 'VOID RX',
                        icon: 'fa-ban',
                        onClick: () => setIsVoidModalOpen(true),
                        color: 'danger'
                    },
                    { 
                        label: 'PRINT RX', 
                        icon: 'fa-print', 
                        onClick: () => window.open(route('prescriptions.print', prescription.prescription_id), '_blank'),
                    },
                    { 
                        label: 'BACK TO REGISTRY', 
                        icon: 'fa-layer-group', 
                        href: route('prescriptions.index'),
                        color: 'gray'
                    }
                ].filter(Boolean)}
            />

            {/* Void Prescription Modal */}
            {isVoidModalOpen && (
                <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 shadow-lg rounded-4">
                            <div className="modal-header border-bottom-0 p-4">
                                <h5 className="modal-title fw-bold text-danger">
                                    <i className="fas fa-exclamation-triangle me-2"></i> Void Prescription
                                </h5>
                                <button type="button" className="btn-close" onClick={() => {
                                    setIsVoidModalOpen(false);
                                    resetVoid();
                                }}></button>
                            </div>
                            <div className="modal-body p-4 pt-0">
                                <p className="text-muted mb-4">Are you sure you want to void this prescription <strong>RX-{String(prescription.prescription_id).padStart(5, '0')}</strong>? This action will mark it as voided but keep it in the system for auditing purposes.</p>
                                
                                <div className="mb-3">
                                    <label className="form-label fw-bold text-muted extra-small text-uppercase tracking-widest">Reason for Voiding</label>
                                    <textarea 
                                        className="form-control bg-light border-0" 
                                        rows="3"
                                        placeholder="e.g., Change of treatment plan, entry mistake..."
                                        value={voidData.void_reason}
                                        onChange={e => setVoidData('void_reason', e.target.value)}
                                        required
                                    ></textarea>
                                </div>
                            </div>
                            <div className="modal-footer border-top-0 p-4">
                                <button 
                                    type="button" 
                                    className="btn btn-light rounded-pill px-4 fw-bold" 
                                    onClick={() => {
                                        setIsVoidModalOpen(false);
                                        resetVoid();
                                    }}
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="button" 
                                    className="btn btn-danger rounded-pill px-4 fw-bold shadow-sm"
                                    disabled={!voidData.void_reason || voidProcessing}
                                    onClick={handleVoid}
                                >
                                    {voidProcessing ? 'Voiding...' : 'Void Prescription'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
