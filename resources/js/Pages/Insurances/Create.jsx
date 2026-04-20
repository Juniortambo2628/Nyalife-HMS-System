import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { FilePond, registerPlugin } from 'react-filepond';
import 'filepond/dist/filepond.min.css';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';

registerPlugin(FilePondPluginImagePreview);

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        logo: null,
        link: '',
        is_active: true,
        sort_order: 0,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('insurances.store'));
    };

    return (
        <AuthenticatedLayout>
            <Head title="Add Insurance Provider" />
            
            <PageHeader 
                title="Add Insurance Provider"
                breadcrumbs={[
                    { label: 'Dashboard', url: '/dashboard' },
                    { label: 'Insurances', url: route('insurances.index') },
                    { label: 'Add New', active: true }
                ]}
            />

            <div className="container-fluid pb-5">
                <div className="row justify-content-center">
                    <div className="col-lg-6 col-md-8">
                        <div className="card shadow-sm border-0 rounded-4 overflow-hidden">
                            <div className="bg-primary-gradient p-4 text-white">
                                <h5 className="fw-bold mb-0">Provider Details</h5>
                                <p className="small mb-0 opacity-75">Fill in the information below to register a new insurance partner.</p>
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
                                        <label className="form-label small fw-bold">Provider Logo</label>
                                        <div className="bg-light rounded-3 p-2 border border-dashed text-center">
                                            <FilePond
                                                onupdatefiles={fileItems => setData('logo', fileItems[0]?.file)}
                                                allowMultiple={false}
                                                maxFiles={1}
                                                labelIdle='Drop logo or <span class="filepond--label-action">Browse</span>'
                                                acceptedFileTypes={['image/*']}
                                            />
                                        </div>
                                        {errors.logo && <div className="text-danger small mt-2 fw-bold">{errors.logo}</div>}
                                        <div className="small text-muted mt-2">Recommended size: 300x200px. formats: PNG, JPG, WebP.</div>
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
                                                placeholder="0"
                                            />
                                            <div className="extra-small text-muted mt-1">Lower numbers appear first.</div>
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
                                            <i className="fas fa-arrow-left me-2"></i>Back to List
                                        </Link>
                                        <button 
                                            type="submit" 
                                            className="btn btn-primary px-5 py-2 fw-bold flex-fill shadow-sm" 
                                            disabled={processing}
                                        >
                                            {processing ? (
                                                <><span className="spinner-border spinner-border-sm me-2"></span>Saving...</>
                                            ) : (
                                                <><i className="fas fa-save me-2"></i>Save Provider</>
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
                .form-control:focus, .input-group-text:focus {
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
