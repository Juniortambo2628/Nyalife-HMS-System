import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { useEffect } from 'react';

export default function Show({ request, auth }) {
    const hasTemplate = request.test_type?.template && Array.isArray(request.test_type.template) && request.test_type.template.length > 0;
    
    // Initialize results object
    const initialResults = {};
    if (request.results) {
        Object.assign(initialResults, request.results);
    } else if (hasTemplate) {
        request.test_type.template.forEach(item => {
            initialResults[item.label] = '';
        });
    } else {
        initialResults.general = '';
    }

    const { data, setData, post, processing, errors } = useForm({
        status: 'completed',
        results: initialResults
    });

    const isLabTech = auth.user.role === 'lab_technician' || auth.user.role === 'admin';

    const submit = (e) => {
        e.preventDefault();
        post(route('lab.update-status', request.request_id));
    };

    const handleResultChange = (key, value) => {
        setData('results', { ...data.results, [key]: value });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={`Lab Request details - LAB-${request.request_id}`}
        >
            <Head title={`Lab Request LAB-${request.request_id}`} />

            <PageHeader 
                title={`Lab Request Details`}
                breadcrumbs={[
                    { label: 'Lab', url: route('lab.index') }, 
                    { label: 'Requests', url: route('lab.index') },
                    { label: `LAB-${request.request_id}`, active: true }
                ]}
            />

            <div className="py-0">
                <div className="row g-4">
                    <div className="col-lg-4">
                        <div className="card shadow-sm border-0 rounded-xl overflow-hidden mb-4">
                            <div className="card-header bg-white py-3 border-bottom">
                                <h6 className="mb-0 fw-bold">Patient Information</h6>
                            </div>
                            <div className="card-body">
                                <div className="d-flex align-items-center mb-4">
                                    <div className="avatar-circle me-3" style={{ width: '50px', height: '50px', borderRadius: '50%', background: 'linear-gradient(135deg, #e91e63, #c2185b)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'white', fontWeight: 'bold' }}>
                                        {request.patient.user.first_name.charAt(0)}
                                    </div>
                                    <div>
                                        <div className="fw-bold text-gray-900">{request.patient.user.first_name} {request.patient.user.last_name}</div>
                                        <div className="text-muted small">PAT-{request.patient_id}</div>
                                    </div>
                                </div>
                                <div className="space-y-3">
                                    <div className="d-flex justify-content-between">
                                        <span className="text-muted small">Gender</span>
                                        <span className="small fw-semibold text-capitalize">{request.patient.user.gender || 'Not set'}</span>
                                    </div>
                                    <div className="d-flex justify-content-between">
                                        <span className="text-muted small">Blood Group</span>
                                        <span className="small fw-semibold">{request.patient.blood_group || 'Not set'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="card shadow-sm border-0 rounded-xl overflow-hidden">
                            <div className="card-header bg-white py-3 border-bottom">
                                <h6 className="mb-0 fw-bold">Request Meta</h6>
                            </div>
                            <div className="card-body">
                                <div className="space-y-3">
                                    <div className="d-flex justify-content-between">
                                        <span className="text-muted small">Requested By</span>
                                        <span className="small fw-semibold">Dr. {request.doctor?.user?.last_name || 'System'}</span>
                                    </div>
                                    <div className="d-flex justify-content-between">
                                        <span className="text-muted small">Date</span>
                                        <span className="small fw-semibold">{new Date(request.created_at).toLocaleString()}</span>
                                    </div>
                                    <div className="d-flex justify-content-between">
                                        <span className="text-muted small">Status</span>
                                        <span className={`badge rounded-pill ${request.status === 'completed' ? 'bg-success' : 'bg-warning text-dark'}`}>
                                            {request.status}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 rounded-xl overflow-hidden mb-4">
                            <div className="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 className="mb-0 fw-bold">Test Details</h6>
                                <span className="badge bg-soft-info text-info rounded-pill px-3">
                                    {request.test_type?.category || 'General'}
                                </span>
                            </div>
                            <div className="card-body">
                                <h4 className="fw-bold mb-3">{request.test_type?.test_name || 'Standard Investigation'}</h4>
                                <p className="text-muted small mb-4">{request.test_type?.description || 'No detailed description available for this test type.'}</p>
                                
                                <div className="bg-light p-4 rounded-xl">
                                    <div className="row text-center g-4">
                                        <div className="col-md-4">
                                            <div className="small text-muted mb-1 uppercase tracking-wider font-bold">Normal Range</div>
                                            <div className="fw-bold">{request.test_type?.normal_range || 'N/A'}</div>
                                        </div>
                                        <div className="col-md-4">
                                            <div className="small text-muted mb-1 uppercase tracking-wider font-bold">Units</div>
                                            <div className="fw-bold">{request.test_type?.units || 'N/A'}</div>
                                        </div>
                                        <div className="col-md-4">
                                            <div className="small text-muted mb-1 uppercase tracking-wider font-bold">Priority</div>
                                            <div className="fw-bold text-danger">Stat</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="card shadow-sm border-0 rounded-xl overflow-hidden">
                            <div className="card-header bg-white py-3 border-bottom">
                                <h6 className="mb-0 fw-bold">Test Results</h6>
                            </div>
                            <div className="card-body py-4 text-center">
                                {request.status === 'completed' ? (
                                    <div className="animate-in">
                                        <i className="fas fa-check-circle text-success fa-3x mb-3"></i>
                                        <h5 className="fw-bold">Results are Ready</h5>
                                        <div className="text-start mt-4 bg-light p-4 rounded-xl">
                                            {hasTemplate ? (
                                                <div className="table-responsive">
                                                    <table className="table table-sm table-borderless">
                                                        <thead>
                                                            <tr>
                                                                <th>Parameter</th>
                                                                <th>Result</th>
                                                                <th>Normal Range</th>
                                                                <th>Unit</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {request.test_type.template.map((item, idx) => (
                                                                <tr key={idx}>
                                                                    <td className="fw-bold">{item.label}</td>
                                                                    <td>{request.results?.[item.label] || '-'}</td>
                                                                    <td className="text-muted small">{item.normalRange || '-'}</td>
                                                                    <td className="text-muted small">{item.unit || '-'}</td>
                                                                </tr>
                                                            ))}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            ) : (
                                                <p className="mb-0 whitespace-pre-wrap">{request.results?.general || 'No detailed results recorded.'}</p>
                                            )}
                                        </div>
                                        <button className="btn btn-primary rounded-pill px-4 mt-4">
                                            <i className="fas fa-print me-2"></i>Print Report
                                        </button>
                                    </div>
                                ) : isLabTech ? (
                                    <form onSubmit={submit} className="text-start">
                                        <h6 className="fw-bold mb-3 text-primary border-bottom pb-2">Enter Lab Results</h6>
                                        
                                        {hasTemplate ? (
                                            <div className="row g-3 mb-4">
                                                {request.test_type.template.map((item, idx) => (
                                                    <div className="col-md-6" key={idx}>
                                                        <label className="form-label small fw-bold mb-1">{item.label} <span className="text-muted fw-normal ms-1">({item.normalRange ? `${item.normalRange} ` : ''}{item.unit || ''})</span></label>
                                                        <input 
                                                            type="text" 
                                                            className="form-control bg-light" 
                                                            value={data.results[item.label] || ''}
                                                            onChange={e => handleResultChange(item.label, e.target.value)}
                                                            placeholder={`Enter ${item.label}`}
                                                        />
                                                    </div>
                                                ))}
                                            </div>
                                        ) : (
                                            <div className="mb-4">
                                                <label className="form-label small fw-bold mb-1">Results Narrative</label>
                                                <textarea 
                                                    className="form-control bg-light" 
                                                    rows="6"
                                                    value={data.results.general || ''}
                                                    onChange={e => handleResultChange('general', e.target.value)}
                                                    placeholder="Enter general results, observations, or conclusions..."
                                                ></textarea>
                                            </div>
                                        )}

                                        <div className="d-grid mt-4">
                                            <button type="submit" disabled={processing} className="btn btn-success btn-lg rounded-pill shadow-sm">
                                                <i className="fas fa-check-circle me-2"></i>Save & Finalize Results
                                            </button>
                                        </div>
                                    </form>
                                ) : (
                                    <div className="opacity-50 py-4">
                                        <i className="fas fa-microscope text-gray-400 fa-3x mb-3"></i>
                                        <h5 className="fw-bold">Results Pending</h5>
                                        <p className="text-muted small px-5">The laboratory investigation is currently in the {request.status} phase. Waiting for a Lab Technician to submit results.</p>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>{`
                .rounded-xl { border-radius: 1rem; }
                .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
                .space-y-3 > * + * { margin-top: 0.75rem; }
            `}</style>
        </AuthenticatedLayout>
    );
}
