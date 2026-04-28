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
                            <div className="extra-small font-bold text-muted opacity-50 tracking-widest uppercase mb-4">PAT-ID: {prescription.patient_id}</div>
                            
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
                        <div className="card shadow-sm border-0 rounded-4 overflow-hidden bg-white shadow-hover">
                            <div className="card-header bg-white py-4 px-4 border-bottom-0">
                                <h6 className="mb-0 fw-extrabold text-pink-500 extra-small text-uppercase tracking-widest">
                                    <i className="fas fa-pills me-2"></i>Medication Schedule
                                </h6>
                            </div>
                            <div className="card-body p-0">
                                <div className="table-responsive">
                                    <table className="table table-hover align-middle mb-0">
                                        <thead className="bg-pink-500">
                                            <tr>
                                                <th className="ps-4 py-3 text-white extra-small fw-extrabold text-uppercase border-0">Medicine</th>
                                                <th className="py-3 text-white extra-small fw-extrabold text-uppercase border-0 text-center">Dosage</th>
                                                <th className="py-3 text-white extra-small fw-extrabold text-uppercase border-0 text-center">Frequency</th>
                                                <th className="pe-4 py-3 text-white extra-small fw-extrabold text-uppercase border-0 text-end">Duration</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {prescription.items.map((item, idx) => (
                                                <tr key={idx} className="border-bottom border-gray-50">
                                                    <td className="ps-4 py-3 fw-extrabold text-gray-900">{item.medication?.medication_name || item.medicine_name || 'N/A'}</td>
                                                    <td className="py-3 text-center text-gray-700 fw-bold small">{item.dosage}</td>
                                                    <td className="py-3 text-center text-gray-700 fw-bold small">{item.frequency}</td>
                                                    <td className="pe-4 py-3 text-end text-muted fw-bold extra-small text-uppercase">{item.duration}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
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
                    { 
                        label: 'PRINT RX', 
                        icon: 'fa-print', 
                        onClick: () => window.print() 
                    },
                    { 
                        label: 'BACK TO REGISTRY', 
                        icon: 'fa-layer-group', 
                        href: route('prescriptions.index'),
                        color: 'gray'
                    }
                ]}
            />
        </AuthenticatedLayout>
    );
}
