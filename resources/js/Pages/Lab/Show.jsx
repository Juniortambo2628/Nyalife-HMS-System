import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { useEffect, useState } from 'react';

// FilePond
import { FilePond, registerPlugin } from 'react-filepond';
import 'filepond/dist/filepond.min.css';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';

registerPlugin(FilePondPluginImagePreview);

export default function Show({ request, auth }) {
    const [files, setFiles] = useState([]);
    const [viewingAttachment, setViewingAttachment] = useState(null);
    
    const hasTemplate = request.test_type?.template && Array.isArray(request.test_type.template) && request.test_type.template.length > 0;
    
    // Initialize results structure
    const initialResults = {
        lab_results: request.results?.lab_results || (hasTemplate ? {} : ''),
        observations: request.results?.observations || '',
        conclusions: request.results?.conclusions || '',
        attachments: request.results?.attachments || []
    };

    // If it's a legacy simple string result, migrate it to observations
    if (typeof request.results === 'string' || (request.results && request.results.general)) {
        initialResults.observations = request.results.general || request.results;
    }
    
    if (hasTemplate && typeof initialResults.lab_results !== 'object') {
        initialResults.lab_results = {};
        request.test_type.template.forEach(item => {
            initialResults.lab_results[item.label] = '';
        });
    }

    const { data, setData, post, processing, errors } = useForm({
        status: 'completed',
        results: initialResults
    });

    const isLabTech = auth.user.role === 'lab_technician' || auth.user.role === 'admin';
    const storageKey = `lab_draft_${request.request_id}`;

    // Draft Recovery
    useEffect(() => {
        const savedDraft = localStorage.getItem(storageKey);
        if (savedDraft && request.status !== 'completed') {
            try {
                const parsed = JSON.parse(savedDraft);
                setData('results', {
                    ...data.results,
                    ...parsed.results
                });
            } catch (e) {
                console.error('Failed to recover draft', e);
            }
        }
    }, []);

    // Autosave
    useEffect(() => {
        if (request.status !== 'completed') {
            window.dispatchEvent(new CustomEvent('autosave', { detail: { status: 'saving' } }));
            
            const timeoutId = setTimeout(() => {
                try {
                    localStorage.setItem(storageKey, JSON.stringify({
                        results: data.results,
                        timestamp: new Date().getTime()
                    }));
                    window.dispatchEvent(new CustomEvent('autosave', { detail: { status: 'saved' } }));
                } catch (e) {
                    console.warn('Autosave failed: Storage access blocked', e);
                }
            }, 1000); // Autosave after 1s of inactivity
            return () => clearTimeout(timeoutId);
        }
    }, [data.results]);

    const compressImage = (file) => {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (event) => {
                const img = new Image();
                img.src = event.target.result;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const MAX_WIDTH = 1200;
                    const MAX_HEIGHT = 1200;
                    let width = img.width;
                    let height = img.height;

                    if (width > height) {
                        if (width > MAX_WIDTH) {
                            height *= MAX_WIDTH / width;
                            width = MAX_WIDTH;
                        }
                    } else {
                        if (height > MAX_HEIGHT) {
                            width *= MAX_HEIGHT / height;
                            height = MAX_HEIGHT;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.7);
                    resolve(dataUrl);
                };
            };
        });
    };

    const submit = async (e) => {
        e.preventDefault();
        const filePromises = files.map(async (fileItem) => {
            const file = fileItem.file;
            let fileData = null;
            if (file.type.startsWith('image/')) {
                fileData = await compressImage(file);
            } else {
                fileData = await new Promise((resolve) => {
                    const reader = new FileReader();
                    reader.onloadend = () => resolve(reader.result);
                    reader.readAsDataURL(file);
                });
            }
            return { name: file.name, type: file.type, size: file.size, data: fileData };
        });
        const attachments = await Promise.all(filePromises);
        const finalResults = { ...data.results, attachments: attachments };
        router.post(route('lab.update-status', request.request_id), {
            ...data,
            results: finalResults
        }, {
            onSuccess: () => localStorage.removeItem(storageKey)
        });
    };

    const handlePrint = () => window.open(route('lab.print', request.request_id), '_blank');

    const handleLabResultChange = (key, value) => {
        if (hasTemplate) {
            setData('results', { 
                ...data.results, 
                lab_results: { ...data.results.lab_results, [key]: value } 
            });
        } else {
            setData('results', { ...data.results, lab_results: value });
        }
    };

    const handleFieldChange = (field, value) => setData('results', { ...data.results, [field]: value });

    return (
        <AuthenticatedLayout header={`Lab Request details - LAB-${request.request_id}`}>
            <Head title={`Lab Request LAB-${request.request_id}`} />

            <PageHeader 
                title={`Investigation Details`}
                breadcrumbs={[
                    { label: 'Laboratory', url: route('lab.index') }, 
                    { label: 'Requests', url: route('lab.index') },
                    { label: `LAB-${request.request_id}`, active: true }
                ]}
            />

            <div className="py-0">
                <div className="row g-4">
                    {/* Patient Info Card */}
                    <div className="col-lg-4">
                        <div className="card shadow-sm border-0 rounded-2xl overflow-hidden mb-4 bg-white shadow-hover">
                            <div className="card-header bg-white py-4 border-bottom-0">
                                <h6 className="mb-0 fw-extrabold text-uppercase tracking-widest extra-small text-primary">Patient Identity</h6>
                            </div>
                            <div className="card-body p-4 pt-0">
                                <div className="d-flex align-items-center mb-4 p-3 bg-light rounded-2xl border border-light shadow-inner">
                                    <div className="avatar-md me-3 flex-shrink-0 rounded-lg d-flex align-items-center justify-content-center fw-extrabold text-white" 
                                         style={{ background: 'linear-gradient(135deg, #e91e63, #c2185b)', fontSize: '1.25rem' }}>
                                        {request.patient.user.first_name.charAt(0)}
                                    </div>
                                    <div>
                                        <div className="fw-extrabold text-gray-900 tracking-tighter">{request.patient.user.first_name} {request.patient.user.last_name}</div>
                                        <div className="extra-small font-bold text-muted opacity-50">PAT-ID: {request.patient_id}</div>
                                    </div>
                                </div>
                                <div className="space-y-3">
                                    <div className="d-flex justify-content-between align-items-center p-3 rounded-xl bg-gray-50 border border-gray-100">
                                        <span className="text-muted extra-small fw-extrabold text-uppercase tracking-widest">Gender</span>
                                        <span className="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-extrabold extra-small text-uppercase">
                                            {request.patient.gender || request.patient.user?.gender || 'N/A'}
                                        </span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center p-3 rounded-xl bg-gray-50 border border-gray-100">
                                        <span className="text-muted extra-small fw-extrabold text-uppercase tracking-widest">Age</span>
                                        <span className="badge bg-info-subtle text-info rounded-pill px-3 py-2 fw-extrabold extra-small">
                                            {request.patient.age ? `${request.patient.age} YEARS` : 'N/A'}
                                        </span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center p-3 rounded-xl bg-gray-50 border border-gray-100">
                                        <span className="text-muted extra-small fw-extrabold text-uppercase tracking-widest">Blood Type</span>
                                        <span className="badge bg-danger-subtle text-danger rounded-pill px-3 py-2 fw-extrabold extra-small">
                                            {request.patient.blood_group || 'N/A'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="card shadow-sm border-0 rounded-2xl overflow-hidden bg-white mb-4 shadow-hover">
                            <div className="card-header bg-white py-4 border-bottom-0">
                                <h6 className="mb-0 fw-extrabold text-uppercase tracking-widest extra-small text-primary">Metadata</h6>
                            </div>
                            <div className="card-body p-4 pt-0">
                                <div className="space-y-4">
                                    <div className="d-flex justify-content-between align-items-center border-bottom border-gray-50 pb-2">
                                        <span className="text-muted extra-small fw-bold text-uppercase">Physician</span>
                                        <span className="small fw-extrabold text-gray-900">Dr. {request.doctor?.user?.last_name || 'Staff'}</span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center border-bottom border-gray-50 pb-2">
                                        <span className="text-muted extra-small fw-bold text-uppercase">Ordered</span>
                                        <span className="extra-small fw-extrabold text-gray-900">{new Date(request.created_at).toLocaleDateString()}</span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center">
                                        <span className="text-muted extra-small fw-bold text-uppercase">Status</span>
                                        <span className={`badge rounded-pill px-3 py-1 fw-extrabold extra-small text-uppercase ${request.status === 'completed' ? 'bg-success text-white' : 'bg-warning text-dark'}`}>
                                            {request.status}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {request.consultation_id && (
                            <Link href={route('consultations.show', request.consultation_id)} className="btn btn-outline-primary w-100 rounded-pill py-3 fw-extrabold extra-small tracking-widest shadow-sm shadow-hover">
                                <i className="fas fa-external-link-alt me-2"></i> VIEW CONSULTATION
                            </Link>
                        )}
                    </div>

                    {/* Test Details and Results Form */}
                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 rounded-2xl overflow-hidden mb-4 bg-white shadow-hover">
                            <div className="card-header bg-white py-4 border-bottom-0 d-flex justify-content-between align-items-center">
                                <h6 className="mb-0 fw-extrabold text-uppercase tracking-widest extra-small text-primary">Investigation Parameters</h6>
                                <span className="badge bg-blue-50 text-blue-700 rounded-pill px-3 py-2 border border-blue-100 extra-small fw-bold">
                                    {request.test_type?.category || 'LABORATORY'}
                                </span>
                            </div>
                            <div className="card-body p-4 pt-0">
                                <div className="mb-4">
                                    <h3 className="fw-extrabold text-gray-900 mb-2 tracking-tighter">{request.test_type?.test_name}</h3>
                                    <p className="text-muted small fw-medium">{request.test_type?.description || 'Standard diagnostic investigation protocol.'}</p>
                                </div>
                                
                                <div className="row g-3">
                                    <div className="col-md-4">
                                        <div className="p-4 rounded-2xl bg-gray-50 border border-gray-100 text-center shadow-inner">
                                            <div className="text-muted extra-small fw-extrabold text-uppercase tracking-widest mb-1">Ref. Range</div>
                                            <div className="fw-extrabold text-gray-900">{request.test_type?.normal_range || '—'}</div>
                                        </div>
                                    </div>
                                    <div className="col-md-4">
                                        <div className="p-4 rounded-2xl bg-gray-50 border border-gray-100 text-center shadow-inner">
                                            <div className="text-muted extra-small fw-extrabold text-uppercase tracking-widest mb-1">Units</div>
                                            <div className="fw-extrabold text-gray-900">{request.test_type?.units || '—'}</div>
                                        </div>
                                    </div>
                                    <div className="col-md-4">
                                        <div className="p-4 rounded-2xl bg-gray-50 border border-gray-100 text-center shadow-inner">
                                            <div className="text-muted extra-small fw-extrabold text-uppercase tracking-widest mb-1">Priority</div>
                                            <div className={`fw-extrabold ${request.priority === 'urgent' ? 'text-danger' : 'text-primary'}`}>
                                                {request.priority?.toUpperCase() || 'NORMAL'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="card shadow-sm border-0 rounded-2xl overflow-hidden bg-white shadow-hover">
                            <div className="card-header bg-gradient-primary-to-secondary text-white py-4 border-0">
                                <h6 className="mb-0 fw-extrabold text-uppercase tracking-widest extra-small">Investigation Findings</h6>
                            </div>
                            <div className="card-body p-4">
                                {request.status === 'completed' ? (
                                    <div className="animate-in fade-in slide-in-from-bottom-4 duration-500">
                                        <div className="text-center mb-5">
                                            <div className="bg-success-subtle text-success p-4 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm border border-success-subtle" style={{ width: '100px', height: '100px' }}>
                                                <i className="fas fa-check-double fa-2x"></i>
                                            </div>
                                            <h4 className="fw-extrabold text-gray-900 tracking-tighter">RESULTS CERTIFIED</h4>
                                            <p className="extra-small fw-bold text-muted text-uppercase tracking-widest opacity-50">Analysis verified by laboratory department</p>
                                        </div>

                                        <div className="space-y-8">
                                            <div>
                                                <h6 className="extra-small fw-extrabold text-uppercase text-muted tracking-widest mb-4 border-bottom border-gray-100 pb-2">Quantitative Analysis</h6>
                                                {hasTemplate ? (
                                                    <div className="table-responsive rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
                                                        <table className="table table-hover align-middle mb-0">
                                                            <thead className="bg-gray-50">
                                                                <tr>
                                                                    <th className="px-4 py-3 extra-small fw-extrabold text-muted border-0">PARAMETER</th>
                                                                    <th className="px-4 py-3 extra-small fw-extrabold text-muted border-0">RESULT</th>
                                                                    <th className="px-4 py-3 extra-small fw-extrabold text-muted border-0 text-center">REF. RANGE</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody className="border-0">
                                                                {request.test_type.template.map((item, idx) => (
                                                                    <tr key={idx} className="border-bottom border-gray-50">
                                                                        <td className="px-4 py-3 fw-bold text-gray-800">{item.label}</td>
                                                                        <td className="px-4 py-3 fw-extrabold text-primary">{data.results.lab_results[item.label] || '—'} <small className="text-muted fw-normal ms-1">{item.unit}</small></td>
                                                                        <td className="px-4 py-3 text-center small text-muted font-mono">{item.normalRange || '—'}</td>
                                                                    </tr>
                                                                ))}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                ) : (
                                                    <div className="p-6 rounded-2xl bg-light-blue border border-blue-50 text-gray-800 fw-bold shadow-inner">
                                                        {data.results.lab_results || 'No quantitative results recorded.'}
                                                    </div>
                                                )}
                                            </div>

                                            <div className="row g-4">
                                                <div className="col-md-6">
                                                    <h6 className="extra-small fw-extrabold text-uppercase text-muted tracking-widest mb-4 border-bottom border-gray-100 pb-2">Observations</h6>
                                                    <div className="p-5 rounded-2xl bg-gray-50 border border-gray-100 text-gray-700 italic fw-medium shadow-inner">
                                                        {data.results.observations || 'No specific observations recorded.'}
                                                    </div>
                                                </div>
                                                <div className="col-md-6">
                                                    <h6 className="extra-small fw-extrabold text-uppercase text-muted tracking-widest mb-4 border-bottom border-gray-100 pb-2">Clinical Conclusion</h6>
                                                    <div className="p-5 rounded-2xl bg-success-subtle border border-success-subtle text-success-emphasis fw-extrabold shadow-inner">
                                                        {data.results.conclusions || 'No final conclusion recorded.'}
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            {data.results.attachments?.length > 0 && (
                                                <div>
                                                    <h6 className="extra-small fw-extrabold text-uppercase text-muted tracking-widest mb-4 border-bottom border-gray-100 pb-2">Supporting Evidence</h6>
                                                    <div className="row g-3">
                                                        {data.results.attachments.map((file, idx) => (
                                                            <div key={idx} className="col-md-4">
                                                                <div 
                                                                    className="card h-100 border-0 shadow-sm rounded-xl overflow-hidden bg-gray-50 hover-lift cursor-pointer shadow-hover"
                                                                    onClick={() => setViewingAttachment(file)}
                                                                >
                                                                    <div className="position-relative" style={{ height: '160px' }}>
                                                                        {file.type?.startsWith('image/') ? (
                                                                            <img src={file.data} alt={file.name} className="w-100 h-100 object-fit-cover" />
                                                                        ) : (
                                                                            <div className="w-100 h-100 d-flex align-items-center justify-content-center bg-gray-200">
                                                                                <i className={`fas ${file.type?.includes('pdf') ? 'fa-file-pdf text-danger' : 'fa-file-medical text-primary'} fa-4x opacity-20`}></i>
                                                                            </div>
                                                                        )}
                                                                        <div className="position-absolute bottom-0 start-0 w-100 p-3 bg-dark bg-opacity-70 text-white extra-small fw-bold text-truncate backdrop-blur">
                                                                            {file.name}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        ))}
                                                    </div>
                                                </div>
                                            )}
                                        </div>

                                        <div className="text-center mt-8">
                                            <button onClick={handlePrint} className="btn btn-primary rounded-pill px-5 py-3.5 fw-extrabold shadow-lg transition-all hover-translate-up tracking-widest">
                                                <i className="fas fa-print me-2"></i> GENERATE OFFICIAL REPORT
                                            </button>
                                        </div>
                                    </div>
                                ) : isLabTech ? (
                                    <form onSubmit={submit} className="animate-in fade-in slide-in-from-bottom-4 duration-500">
                                        <div className="space-y-6">
                                            <div>
                                                <h6 className="extra-small fw-extrabold text-uppercase text-primary tracking-widest mb-4 d-flex align-items-center gap-3">
                                                    <span className="avatar-sm bg-primary text-white rounded-lg d-flex align-items-center justify-content-center fw-bold">1</span>
                                                    DATA ENTRY
                                                </h6>
                                                
                                                {hasTemplate ? (
                                                    <div className="row g-4">
                                                        {request.test_type.template.map((item, idx) => (
                                                            <div className="col-md-6" key={idx}>
                                                                <label className="extra-small fw-extrabold text-gray-500 text-uppercase tracking-widest mb-2 d-block">
                                                                    {item.label} 
                                                                    <span className="text-muted fw-bold ms-2 opacity-50 italic">
                                                                        ({item.normalRange || 'REF'}: {item.unit || 'U'})
                                                                    </span>
                                                                </label>
                                                                <div className="input-group">
                                                                    <input 
                                                                        type="text" 
                                                                        className="form-control form-control-lg bg-light border-0 rounded-xl fw-bold" 
                                                                        value={data.results.lab_results[item.label] || ''}
                                                                        onChange={e => handleLabResultChange(item.label, e.target.value)}
                                                                        placeholder={`Enter value`}
                                                                    />
                                                                    {item.unit && <span className="input-group-text bg-gray-100 border-0 text-muted extra-small fw-bold">{item.unit}</span>}
                                                                </div>
                                                            </div>
                                                        ))}
                                                    </div>
                                                ) : (
                                                    <textarea 
                                                        className="form-control form-control-lg bg-light border-0 rounded-2xl fw-bold" 
                                                        rows="3"
                                                        value={data.results.lab_results}
                                                        onChange={e => handleLabResultChange(null, e.target.value)}
                                                        placeholder="Enter quantitative data or detailed results..."
                                                    ></textarea>
                                                )}
                                            </div>

                                            <div className="row g-4">
                                                <div className="col-md-6">
                                                    <h6 className="extra-small fw-extrabold text-uppercase text-primary tracking-widest mb-4 d-flex align-items-center gap-3">
                                                        <span className="avatar-sm bg-primary text-white rounded-lg d-flex align-items-center justify-content-center fw-bold">2</span>
                                                        OBSERVATIONS
                                                    </h6>
                                                    <textarea 
                                                        className="form-control bg-light border-0 rounded-2xl fw-medium" 
                                                        rows="4"
                                                        value={data.results.observations}
                                                        onChange={e => handleFieldChange('observations', e.target.value)}
                                                        placeholder="Record clinical observations..."
                                                    ></textarea>
                                                </div>
                                                <div className="col-md-6">
                                                    <h6 className="extra-small fw-extrabold text-uppercase text-primary tracking-widest mb-4 d-flex align-items-center gap-3">
                                                        <span className="avatar-sm bg-primary text-white rounded-lg d-flex align-items-center justify-content-center fw-bold">3</span>
                                                        CONCLUSION
                                                    </h6>
                                                    <textarea 
                                                        className="form-control bg-success-subtle border-0 rounded-2xl text-success-emphasis fw-extrabold" 
                                                        rows="4"
                                                        value={data.results.conclusions}
                                                        onChange={e => handleFieldChange('conclusions', e.target.value)}
                                                        placeholder="Enter professional conclusion..."
                                                    ></textarea>
                                                </div>
                                            </div>

                                            <div>
                                                <h6 className="extra-small fw-extrabold text-uppercase text-primary tracking-widest mb-4 d-flex align-items-center gap-3">
                                                    <span className="avatar-sm bg-primary text-white rounded-lg d-flex align-items-center justify-content-center fw-bold">4</span>
                                                    EVIDENCE UPLOAD
                                                </h6>
                                                <div className="border-2 border-dashed border-gray-200 rounded-3xl p-4 bg-gray-50 shadow-inner">
                                                    <FilePond
                                                        files={files}
                                                        onupdatefiles={setFiles}
                                                        allowMultiple={true}
                                                        maxFiles={5}
                                                        name="files"
                                                        labelIdle='Drag & Drop images or <span class="filepond--label-action">Browse</span>'
                                                        imagePreviewHeight={170}
                                                    />
                                                </div>
                                            </div>

                                            <div className="d-grid mt-6">
                                                <button type="submit" disabled={processing} className="btn btn-success btn-lg rounded-pill shadow-lg py-3.5 fw-extrabold transition-all hover-translate-up tracking-widest">
                                                    {processing ? <span className="spinner-border spinner-border-sm me-2"></span> : <i className="fas fa-check-circle me-2"></i>}
                                                    RELEASE RESULTS
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                ) : (
                                    <div className="opacity-50 py-16 text-center">
                                        <div className="bg-gray-100 p-5 rounded-circle d-inline-flex mb-4 shadow-inner border border-gray-50">
                                            <i className="fas fa-microscope text-gray-400 fa-4x opacity-20"></i>
                                        </div>
                                        <h5 className="fw-extrabold text-gray-600 tracking-tighter">INVESTIGATION IN PROGRESS</h5>
                                        <p className="text-muted extra-small fw-bold text-uppercase tracking-widest px-5 mx-auto" style={{ maxWidth: '400px' }}>
                                            Results are currently being processed by the laboratory team.
                                        </p>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Attachment Viewer Modal */}
            {viewingAttachment && (
                <div className="fixed inset-0 z-[2000] bg-black bg-opacity-95 d-flex flex-column animate-in fade-in backdrop-blur-sm">
                    <div className="d-flex justify-content-between align-items-center p-5 text-white">
                        <h4 className="mb-0 fw-extrabold tracking-tighter">{viewingAttachment.name}</h4>
                        <div className="d-flex gap-3">
                            <a href={viewingAttachment.data} download={viewingAttachment.name} className="btn btn-outline-light rounded-pill px-4 btn-sm fw-bold">
                                <i className="fas fa-download me-2"></i>DOWNLOAD
                            </a>
                            <button onClick={() => setViewingAttachment(null)} className="btn btn-white rounded-circle d-flex align-items-center justify-content-center" style={{ width: '48px', height: '48px' }}>
                                <i className="fas fa-times fa-lg"></i>
                            </button>
                        </div>
                    </div>
                    <div className="flex-grow-1 d-flex align-items-center justify-content-center p-6 overflow-auto">
                        {viewingAttachment.type?.startsWith('image/') ? (
                            <img src={viewingAttachment.data} alt={viewingAttachment.name} className="img-fluid rounded-2xl shadow-2xl" style={{ maxHeight: '80vh' }} />
                        ) : viewingAttachment.type?.includes('pdf') ? (
                            <iframe src={viewingAttachment.data} className="w-100 h-100 rounded-2xl shadow-2xl bg-white" style={{ maxWidth: '1000px', height: '80vh' }} title={viewingAttachment.name}></iframe>
                        ) : (
                            <div className="text-center text-white p-10 bg-white bg-opacity-10 rounded-3xl border border-white border-opacity-10">
                                <i className="fas fa-file-medical fa-5x mb-4 opacity-20"></i>
                                <h4 className="fw-bold">Preview Unavailable</h4>
                                <a href={viewingAttachment.data} download={viewingAttachment.name} className="btn btn-primary mt-4 rounded-pill px-5 py-3 fw-bold tracking-widest">DOWNLOAD TO VIEW</a>
                            </div>
                        )}
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
