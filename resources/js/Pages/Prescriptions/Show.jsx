import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Show({ prescription, auth }) {
    return (
        <AuthenticatedLayout
            header="Prescription Details"
        >
            <Head title={`Prescription - RX-${String(prescription.prescription_id).padStart(5, '0')}`} />

            <PageHeader 
                title={`RX-${String(prescription.prescription_id).padStart(5, '0')}`}
                breadcrumbs={[
                    { label: 'Pharmacy', url: route('prescriptions.index') },
                    { label: 'Prescription Details', active: true }
                ]}
            />

            <div className="px-0 py-0">

                <div className="row">
                    <div className="col-lg-4">
                        <div className="card shadow-sm border-0 mb-4 text-center p-4">
                            <div className="mb-3">
                                <span className={`badge px-3 py-2 ${prescription.status === 'dispensed' ? 'bg-success' : 'bg-warning text-dark'}`}>
                                    {prescription.status.toUpperCase()}
                                </span>
                            </div>
                            <h5 className="mb-1">{prescription.patient?.user?.first_name || 'Unknown'} {prescription.patient?.user?.last_name || 'Patient'}</h5>
                            <p className="text-muted small mb-0">Patient ID: {prescription.patient_id}</p>
                            <hr className="my-4" />
                            <div className="text-start mb-4">
                                <div className="mb-2"><i className="fas fa-calendar text-primary me-2"></i><strong>Prescribed On:</strong> {prescription.prescription_date}</div>
                                <div className="mb-0"><i className="fas fa-user-md text-primary me-2"></i><strong>Doctor:</strong> Dr. {prescription.doctor?.first_name || prescription.doctor?.user?.first_name || 'Staff'} {prescription.doctor?.last_name || prescription.doctor?.user?.last_name || ''}</div>
                            </div>
                            
                            {prescription.consultation_id && (
                                <div className="text-start p-3 bg-light rounded mb-4">
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
                                <button className="btn btn-success w-100 shadow-sm">
                                    <i className="fas fa-check-circle me-2"></i>Mark as Dispensed
                                </button>
                            )}
                        </div>
                    </div>

                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0">
                            <div className="card-header bg-white py-3 border-bottom-0">
                                <h6 className="mb-0 fw-bold"><i className="fas fa-pills me-2 text-primary"></i>Medications List</h6>
                            </div>
                            <div className="card-body p-0">
                                <div className="table-responsive">
                                    <table className="table table-hover align-middle mb-0">
                                        <thead className="bg-light">
                                            <tr>
                                                <th className="ps-4">Medicine</th>
                                                <th>Dosage</th>
                                                <th>Frequency</th>
                                                <th className="pe-4">Duration</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {prescription.items.map((item, idx) => (
                                                <tr key={idx}>
                                                    <td className="ps-4 fw-bold">{item.medication?.medication_name || item.medicine_name || 'N/A'}</td>
                                                    <td>{item.dosage}</td>
                                                    <td>{item.frequency}</td>
                                                    <td className="pe-4">{item.duration}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            {prescription.notes && (
                                <div className="card-footer bg-light border-0 p-4">
                                    <h6 className="fw-bold small text-uppercase text-muted mb-2">Instructions / Notes</h6>
                                    <p className="mb-0 small">{prescription.notes}</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            <UnifiedToolbar 
                actions={
                    <div className="d-flex align-items-center gap-2">
                        <button onClick={() => window.print()} className="btn btn-primary rounded-pill px-4 py-2 fw-extrabold small shadow-sm">
                            <i className="fas fa-print me-1"></i> Print RX
                        </button>
                    </div>
                }
            />
        </AuthenticatedLayout>
    );
}
