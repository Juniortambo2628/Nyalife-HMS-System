import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { FilePond, registerPlugin } from 'react-filepond';
import 'filepond/dist/filepond.min.css';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';
import { useState } from 'react';

registerPlugin(FilePondPluginImagePreview);

export default function Index({ insurances }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        logo: null,
        link: '',
        is_active: true,
        sort_order: 0,
    });

    const [editMode, setEditMode] = useState(false);
    const [editId, setEditId] = useState(null);

    const handleSubmit = (e) => {
        e.preventDefault();
        if (editMode) {
            // Laravel has issues with PUT + Multipart, so we use POST with _method spoofing if needed
            // or just use POST if to a specific update route that handles POST
            post(route('insurances.update', editId), {
                onSuccess: () => {
                    reset();
                    setEditMode(false);
                    setEditId(null);
                }
            });
        } else {
            post(route('insurances.store'), {
                onSuccess: () => reset()
            });
        }
    };

    const handleEdit = (insurance) => {
        setData({
            name: insurance.name,
            logo: null, // Reset logo on edit unless they want to change it
            link: insurance.link || '',
            is_active: insurance.is_active,
            sort_order: insurance.sort_order,
        });
        setEditMode(true);
        setEditId(insurance.insurance_id);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const cancelEdit = () => {
        setEditMode(false);
        setEditId(null);
        reset();
    };

    const toggleStatus = (id) => {
        router.post(route('insurances.toggle', id));
    };

    const handleDelete = (id) => {
        if (confirm('Are you sure you want to remove this insurance provider?')) {
            router.delete(route('insurances.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title="Health Insurances" />
            
            <PageHeader 
                title="Manage Accepted Insurances"
                breadcrumbs={[
                    { label: 'Dashboard', url: '/dashboard' },
                    { label: 'Insurances', active: true }
                ]}
            />

            <div className="row g-4 h-auto">
                {/* Form Side */}
                <div className="col-lg-4 text-dark h-auto">
                    <div className="card shadow-sm border-0 rounded-4 p-4 sticky-top" style={{ top: '100px', zIndex: 10 }}>
                        <h5 className="fw-bold mb-4">{editMode ? 'Edit Insurance' : 'Add New Insurance'}</h5>
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

                            <div className="mb-3">
                                <label className="form-label small fw-bold">Logo</label>
                                <FilePond
                                    onupdatefiles={fileItems => setData('logo', fileItems[0]?.file)}
                                    allowMultiple={false}
                                    maxFiles={1}
                                    labelIdle='Drop logo or <span class="filepond--label-action">Browse</span>'
                                    acceptedFileTypes={['image/*']}
                                />
                                {errors.logo && <div className="text-danger small mt-1">{errors.logo}</div>}
                            </div>

                            <div className="mb-3">
                                <label className="form-label small fw-bold">Website Link (Optional)</label>
                                <input 
                                    type="url" 
                                    className="form-control"
                                    value={data.link}
                                    onChange={e => setData('link', e.target.value)}
                                    placeholder="https://"
                                />
                            </div>

                            <div className="row g-2 mb-4">
                                <div className="col-6">
                                    <label className="form-label small fw-bold">Sort Order</label>
                                    <input 
                                        type="number" 
                                        className="form-control"
                                        value={data.sort_order}
                                        onChange={e => setData('sort_order', e.target.value)}
                                    />
                                </div>
                                <div className="col-6 d-flex align-items-end">
                                    <div className="form-check form-switch mb-2">
                                        <input 
                                            className="form-check-input" 
                                            type="checkbox" 
                                            id="isActive" 
                                            checked={data.is_active}
                                            onChange={e => setData('is_active', e.target.checked)}
                                        />
                                        <label className="form-check-label small" htmlFor="isActive">Visible</label>
                                    </div>
                                </div>
                            </div>

                            <div className="d-grid gap-2">
                                <button type="submit" className="btn btn-primary fw-bold" disabled={processing}>
                                    {editMode ? 'Update Insurance' : 'Save Insurance'}
                                </button>
                                {editMode && (
                                    <button type="button" className="btn btn-light" onClick={cancelEdit}>
                                        Cancel
                                    </button>
                                )}
                            </div>
                        </form>
                    </div>
                </div>

                {/* List Side */}
                <div className="col-lg-8 text-dark h-auto">
                    <div className="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div className="table-responsive">
                            <table className="table table-hover align-middle mb-0">
                                <thead className="bg-light">
                                    <tr>
                                        <th className="px-4 py-3 text-muted small text-uppercase">Logo</th>
                                        <th className="py-3 text-muted small text-uppercase">Provider Name</th>
                                        <th className="py-3 text-muted small text-uppercase">Status</th>
                                        <th className="py-3 text-muted small text-uppercase">Order</th>
                                        <th className="px-4 py-3 text-end text-muted small text-uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {insurances.map(ins => (
                                        <tr key={ins.insurance_id}>
                                            <td className="px-4 py-3">
                                                <img src={ins.logo_url} alt={ins.name} style={{ height: '40px', width: '60px', objectFit: 'contain' }} className="rounded bg-light p-1" />
                                            </td>
                                            <td className="py-3">
                                                <div className="fw-bold">{ins.name}</div>
                                                {ins.link && <small className="text-muted text-truncate d-block" style={{ maxWidth: '150px' }}>{ins.link}</small>}
                                            </td>
                                            <td className="py-3">
                                                <button 
                                                    onClick={() => toggleStatus(ins.insurance_id)}
                                                    className={`badge rounded-pill border-0 px-3 py-2 ${ins.is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'}`}
                                                >
                                                    {ins.is_active ? 'Visible' : 'Hidden'}
                                                </button>
                                            </td>
                                            <td className="py-3">
                                                <span className="badge bg-secondary-subtle text-secondary">{ins.sort_order}</span>
                                            </td>
                                            <td className="px-4 py-3 text-end">
                                                <div className="btn-group shadow-sm rounded-3">
                                                    <button onClick={() => handleEdit(ins)} className="btn btn-white btn-sm px-3 border-end">
                                                        <i className="fas fa-edit text-primary"></i>
                                                    </button>
                                                    <button onClick={() => handleDelete(ins.insurance_id)} className="btn btn-white btn-sm px-3">
                                                        <i className="fas fa-trash text-danger"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                    {insurances.length === 0 && (
                                        <tr>
                                            <td colSpan="5" className="text-center py-5 text-muted">
                                                No insurance providers added yet.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
