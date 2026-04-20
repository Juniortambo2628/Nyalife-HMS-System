import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { FilePond, registerPlugin } from 'react-filepond';
import 'filepond/dist/filepond.min.css';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';

registerPlugin(FilePondPluginImagePreview);

export default function Edit({ insurance }) {
    const { data, setData, post, processing, errors } = useForm({
        name: insurance.name || '',
        logo: null,
        link: insurance.link || '',
        is_active: insurance.is_active,
        sort_order: insurance.sort_order || 0,
        _method: 'PUT', // Method spoofing for multipart/form-data
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        // Laravel has issues with PUT + Multipart, so we use POST with _method spoofing
        // Our controller update method expects insurance_id passed in route
        post(route('insurances.update', insurance.insurance_id));
    };

    return (
        <AuthenticatedLayout>
            <Head title={`Edit ${insurance.name}`} />
            
            <PageHeader 
                title="Edit Insurance Provider"
                breadcrumbs={[
                    { label: 'Dashboard', url: '/dashboard' },
                    { label: 'Insurances', url: route('insurances.index') },
                    { label: 'Edit Provider', active: true }
                ]}
            />

            <div className="container-fluid pb-5">
                <div className="row justify-content-center">
                    <div className="col-lg-6 col-md-8">
                        <div className="card shadow-sm border-0 rounded-4 overflow-hidden">
                            <div className="bg-primary-gradient p-4 text-white d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 className="fw-bold mb-0">Modify Provider</h5>
                                    <p className="small mb-0 opacity-75">Update the details for <strong>{insurance.name}</strong>.</p>
                                </div>
                                {insurance.logo_url && (
                                    <div className="bg-white p-2 rounded-3 shadow-sm">
                                        <img src={insurance.logo_url} alt="Current Logo" style={{ height: '40px', maxWidth: '80px', objectFit: 'contain' }} />
                                    </div>
                                )}
                            </div>
                            <div className="card-body p-4 text-dark">
                                <form onSubmit={handleSubmit}>
                                    <div className="mb-3">
                                        <label className="form-label small fw-bold">Insurance Name</label>
                                        <input 
                                            type="text" 
                                            className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                                            value={data.name}
                                            onChange={e => setData('name', e.target.value)}
                                            placeholder="e.g. NHIF, AAR, Jubilee"
                                            required
                                        />
                                        {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                                    </div>

                                    <div className="mb-4">
                                        <label className="form-label small fw-bold">Update Logo <span className="text-muted fw-normal small ms-1">(Leave empty to keep current)</span></label>
                                        <div className="bg-light rounded-3 p-2 border border-dashed text-center">
                                            <FilePond
                                                onupdatefiles={fileItems => setData('logo', fileItems[0]?.file)}
                                                allowMultiple={false}
                                                maxFiles={1}
                                                labelIdle='Drop new logo or <span class="filepond--label-action">Browse</span>'
                                                acceptedFileTypes={['image/*']}
                                            />
                                        </div>
                                        {errors.logo && <div className="text-danger small mt-2 fw-bold">{errors.logo}</div>}
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label small fw-bold">Website Link (Optional)</label>
                                        <div className="input-group">
                                            <span className="input-group-text bg-light border-end-0 text-muted"><i className="fas fa-globe"></i></span>
                                            <input 
                                                type="url" 
                                                className="form-control border-start-0 ps-0"
                                                value={data.link}
                                                onChange={e => setData('link', e.target.value)}
                                                placeholder="https://www.provider.com"
                                            />
                                        </div>
                                    </div>

                                    <div className="row g-3 mb-4 pt-2 border-top mt-4">
                                        <div className="col-6">
                                            <label className="form-label small fw-bold">Display Priority</label>
                                            <input 
                                                type="number" 
                                                className="form-control"
                                                value={data.sort_order}
                                                onChange={e => setData('sort_order', e.target.value)}
                                            />
                                        </div>
                                        <div className="col-6 d-flex align-items-center justify-content-end">
                                            <div className="form-check form-switch mt-3">
                                                <input 
                                                    className="form-check-input" 
                                                    type="checkbox" 
                                                    id="isActive" 
                                                    checked={data.is_active}
                                                    onChange={e => setData('is_active', e.target.checked)}
                                                />
                                                <label className="form-check-label small fw-bold ms-2" htmlFor="isActive">Active/Visible</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="d-flex gap-3 mt-5">
                                        <Link 
                                            href={route('insurances.index')} 
                                            className="btn btn-light px-4 py-2 border fw-bold text-muted flex-fill"
                                        >
                                            <i className="fas fa-times me-2"></i>Cancel
                                        </Link>
                                        <button 
                                            type="submit" 
                                            className="btn btn-primary px-5 py-2 fw-bold flex-fill shadow-sm" 
                                            disabled={processing}
                                        >
                                            {processing ? (
                                                <><span className="spinner-border spinner-border-sm me-2"></span>Updating...</>
                                            ) : (
                                                <><i className="fas fa-check-circle me-2"></i>Save Changes</>
                                            )}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>{`
                .extra-small { font-size: 0.75rem; }
                .bg-primary-gradient {
                    background: linear-gradient(135deg, #e91e63 0%, #ff4081 100%);
                }
                .form-control:focus {
                    box-shadow: 0 0 0 0.25rem rgba(233, 30, 99, 0.1);
                    border-color: #e91e63;
                }
                .input-group-text {
                    color: #e91e63 !important;
                }
            `}</style>
        </AuthenticatedLayout>
    );
}
