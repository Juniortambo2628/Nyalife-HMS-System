import { Head } from '@inertiajs/react';
import { useEffect } from 'react';

export default function Print({ request, clinic_name, clinic_address, clinic_phone }) {
    useEffect(() => {
        // Auto-trigger print dialog when page loads
        window.print();
    }, []);

    const hasTemplate = request.test_type?.template && Array.isArray(request.test_type.template) && request.test_type.template.length > 0;
    const results = request.results || {};

    return (
        <div className="print-container p-5 bg-white">
            <Head title={`Lab Report - LAB-${request.request_id}`} />

            {/* Letterhead */}
            <div className="d-flex justify-content-between align-items-center mb-5 border-bottom pb-4">
                <div className="d-flex align-items-center">
                    <div className="me-3" style={{ height: '80px' }}>
                        <img 
                            src="/assets/logo/Logo2-transparent.png" 
                            alt="Nyalife HMS" 
                            style={{ height: '100%', width: 'auto', objectFit: 'contain' }} 
                        />
                    </div>
                    <div>
                        <h2 className="fw-bold mb-0 text-primary">{clinic_name}</h2>
                        <div className="text-muted small">{clinic_address} | {clinic_phone}</div>
                    </div>
                </div>
                <div className="text-end">
                    <h4 className="fw-bold mb-0 text-uppercase tracking-widest">Laboratory Report</h4>
                    <div className="text-muted small">REF: LAB-{request.request_id}</div>
                </div>
            </div>

            {/* Patient & Request Info */}
            <div className="row mb-5 g-0 border rounded-3 overflow-hidden">
                <div className="col-6 border-end p-4 bg-light">
                    <h6 className="text-muted text-uppercase extra-small fw-bold mb-3">Patient Information</h6>
                    <div className="mb-1 fw-bold fs-5">{request.patient?.user?.first_name} {request.patient?.user?.last_name}</div>
                    <div className="text-muted small mb-1">ID: PAT-{request.patient_id}</div>
                    <div className="text-muted small">Gender: {request.patient?.gender || request.patient?.user?.gender || 'N/A'} | Age: {request.patient?.age ?? 'N/A'}</div>
                </div>
                <div className="col-6 p-4">
                    <h6 className="text-muted text-uppercase extra-small fw-bold mb-3">Request Details</h6>
                    <div className="row small">
                        <div className="col-6 text-muted">Requested By:</div>
                        <div className="col-6 fw-bold text-end">Dr. {request.doctor?.user?.last_name || 'System'}</div>
                        
                        <div className="col-6 text-muted mt-2">Request Date:</div>
                        <div className="col-6 fw-bold text-end mt-2">{new Date(request.created_at).toLocaleDateString()}</div>
                        
                        <div className="col-6 text-muted mt-2">Report Date:</div>
                        <div className="col-6 fw-bold text-end mt-2">{new Date().toLocaleDateString()}</div>
                    </div>
                </div>
            </div>

            {/* Test Investigation */}
            <div className="mb-5">
                <h5 className="fw-bold border-bottom pb-2 mb-4">
                    <i className="fas fa-microscope me-2 text-primary"></i>
                    Investigation: {request.test_type?.test_name}
                </h5>

                {hasTemplate ? (
                    <table className="table table-bordered align-middle">
                        <thead className="bg-light">
                            <tr>
                                <th className="small fw-bold">PARAMETER</th>
                                <th className="small fw-bold">RESULT</th>
                                <th className="small fw-bold">UNIT</th>
                                <th className="small fw-bold">REFERENCE RANGE</th>
                            </tr>
                        </thead>
                        <tbody>
                            {request.test_type.template.map((item, idx) => (
                                <tr key={idx}>
                                    <td className="fw-bold">{item.label}</td>
                                    <td className="fw-extrabold text-primary">{results.lab_results?.[item.label] || '—'}</td>
                                    <td>{item.unit || '—'}</td>
                                    <td className="text-muted small">{item.normalRange || '—'}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                ) : (
                    <div className="p-4 border rounded-3 bg-light-blue mb-4">
                        <h6 className="extra-small fw-bold text-uppercase text-muted mb-2">Findings Narrative</h6>
                        <div className="whitespace-pre-wrap leading-relaxed">{results.lab_results || 'No quantitative results recorded.'}</div>
                    </div>
                )}
            </div>

            {/* Observations & Conclusion */}
            <div className="row g-4 mb-5">
                <div className="col-6">
                    <div className="p-4 border rounded-3 h-100">
                        <h6 className="extra-small fw-bold text-uppercase text-muted mb-3 border-bottom pb-2">Clinical Observations</h6>
                        <div className="small italic text-gray-700 leading-relaxed">
                            {results.observations || 'No specific clinical observations noted.'}
                        </div>
                    </div>
                </div>
                <div className="col-6">
                    <div className="p-4 border border-primary-subtle bg-primary-subtle rounded-3 h-100">
                        <h6 className="extra-small fw-bold text-uppercase text-primary mb-3 border-bottom border-primary-subtle pb-2">Professional Conclusion</h6>
                        <div className="fw-bold text-primary-emphasis leading-relaxed">
                            {results.conclusions || 'No final conclusion recorded.'}
                        </div>
                    </div>
                </div>
            </div>

            {/* Certification */}
            <div className="mt-5 pt-5 d-flex justify-content-between align-items-end">
                <div className="text-center" style={{ width: '200px' }}>
                    <div className="border-bottom mb-2"></div>
                    <div className="extra-small text-muted">Laboratory Technician Signature</div>
                </div>
                <div className="text-center" style={{ width: '200px' }}>
                    <div className="border-bottom mb-2"></div>
                    <div className="extra-small text-muted">Clinical Director Approval</div>
                </div>
            </div>

            {/* Footer */}
            <div className="mt-5 pt-4 text-center border-top">
                <div className="extra-small text-muted italic">
                    This is a computer-generated medical report. Electronic signatures are valid and binding.
                </div>
            </div>

            <style>{`
                @media print {
                    .btn, nav, .sidebar, .header { display: none !important; }
                    body { background: white !important; }
                    .print-container { padding: 0 !important; }
                }
                .extra-small { font-size: 0.7rem; }
                .bg-light-blue { background-color: #f0f7ff; }
                .whitespace-pre-wrap { white-space: pre-wrap; }
                .leading-relaxed { line-height: 1.6; }
                .text-primary { color: #e91e63 !important; }
                .bg-primary { background-color: #e91e63 !important; }
                .border-primary-subtle { border-color: #fbcfe8 !important; }
                .bg-primary-subtle { background-color: #fce7f3 !important; }
                .text-primary-emphasis { color: #be185d !important; }
            `}</style>
        </div>
    );
}
