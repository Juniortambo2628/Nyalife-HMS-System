import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import React, { useState } from 'react';
import PageHeader from '@/Components/PageHeader';

export default function Index({ procedures, auth }) {
    const [isEditing, setIsEditing] = useState(false);
    const [showModal, setShowModal] = useState(false);

    const { data, setData, post, put, delete: destroy, processing, errors, reset } = useForm({
        procedure_id: null,
        name: '',
        description: '',
        category: 'surgery',
        standard_fee: '',
    });

    const openModal = (procedure = null) => {
        if (procedure) {
            setIsEditing(true);
            setData({
                procedure_id: procedure.procedure_id,
                name: procedure.name,
                description: procedure.description || '',
                category: procedure.category,
                standard_fee: procedure.standard_fee,
            });
        } else {
            setIsEditing(false);
            reset();
        }
        setShowModal(true);
    };

    const closeModal = () => {
        setShowModal(false);
        reset();
        setIsEditing(false);
    };

    const submitForm = (e) => {
        e.preventDefault();
        if (isEditing) {
            put(route('medical-procedures.update', data.procedure_id), {
                onSuccess: () => closeModal(),
            });
        } else {
            post(route('medical-procedures.store'), {
                onSuccess: () => closeModal(),
            });
        }
    };

    const toggleStatus = (id) => {
        router.post(route('medical-procedures.toggle', id));
    };

    const deleteProcedure = (id) => {
        if (confirm('Are you sure you want to delete this procedure?')) {
            destroy(route('medical-procedures.destroy', id));
        }
    };

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount);
    };

    return (
        <AuthenticatedLayout header="Medical Procedures Catalog">
            <Head title="Service & Procedure Catalog" />

            <PageHeader 
                title="Service & Procedure Catalog"
                breadcrumbs={[{ label: 'Admin CMS', active: false }, { label: 'Procedures Catalog', active: true }]}
                actions={
                    <button onClick={() => openModal()} className="btn btn-primary shadow-sm fw-bold rounded-pill px-4">
                        <i className="fas fa-plus me-2"></i>Add Procedure
                    </button>
                }
            />

            <div className="card shadow-sm border-0 rounded-2xl mb-4">
                <div className="card-header bg-white border-bottom-0 py-4 px-4 rounded-top-2xl">
                    <h5 className="fw-bold mb-0 text-gray-800">Master Service Price List</h5>
                    <p className="text-muted small mb-0 mt-1">Manage standard surgical fees, imaging, and consultation baselines.</p>
                </div>
                <div className="table-responsive">
                    <table className="table table-hover align-middle mb-0">
                        <thead className="bg-light">
                            <tr>
                                <th className="px-4 py-3 text-muted text-uppercase small fw-bold">Name & Description</th>
                                <th className="px-4 py-3 text-muted text-uppercase small fw-bold">Category</th>
                                <th className="px-4 py-3 text-muted text-uppercase small fw-bold">Standard Fee</th>
                                <th className="px-4 py-3 text-muted text-uppercase small fw-bold">Status</th>
                                <th className="px-4 py-3 text-end text-muted text-uppercase small fw-bold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {procedures.map((proc) => (
                                <tr key={proc.procedure_id}>
                                    <td className="px-4 py-3">
                                        <div className="fw-bold text-gray-900">{proc.name}</div>
                                        <div className="text-muted small text-truncate" style={{maxWidth: '300px'}}>{proc.description || 'No description provided'}</div>
                                    </td>
                                    <td className="px-4 py-3">
                                        <span className="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill uppercase tracking-wider text-xs border border-secondary-subtle">
                                            {proc.category}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3 fw-bold text-gray-700">
                                        {formatCurrency(proc.standard_fee)}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="form-check form-switch ms-2">
                                            <input 
                                                className="form-check-input" 
                                                type="checkbox" 
                                                checked={proc.is_active} 
                                                onChange={() => toggleStatus(proc.procedure_id)}
                                                style={{cursor: 'pointer'}}
                                            />
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 text-end">
                                        <div className="d-flex justify-content-end gap-2">
                                            <button onClick={() => openModal(proc)} className="btn btn-sm btn-light border text-primary rounded-circle" style={{width: 32, height: 32}} title="Edit">
                                                <i className="fas fa-edit"></i>
                                            </button>
                                            <button onClick={() => deleteProcedure(proc.procedure_id)} className="btn btn-sm btn-light border text-danger rounded-circle" style={{width: 32, height: 32}} title="Delete">
                                                <i className="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {procedures.length === 0 && (
                                <tr>
                                    <td colSpan="5" className="text-center py-5 text-muted">No procedures found in the catalog.</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Modal */}
            {showModal && (
                <div className="modal fade show" style={{ display: 'block', backgroundColor: 'rgba(0,0,0,0.5)' }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content rounded-3xl border-0 shadow-lg">
                            <div className="modal-header border-bottom-0 pb-0 px-4 pt-4">
                                <h5 className="modal-title fw-bold">{isEditing ? 'Edit Procedure' : 'Add New Procedure'}</h5>
                                <button type="button" className="btn-close" onClick={closeModal}></button>
                            </div>
                            <form onSubmit={submitForm}>
                                <div className="modal-body p-4 space-y-4">
                                    <div className="space-y-1">
                                        <label className="text-xs font-bold text-gray-500 uppercase tracking-wider">Procedure Name</label>
                                        <input 
                                            type="text" 
                                            className="form-control bg-light border-0 rounded-xl px-4 py-2.5 font-medium" 
                                            value={data.name}
                                            onChange={e => setData('name', e.target.value)}
                                            required
                                        />
                                        {errors.name && <div className="text-danger small mt-1">{errors.name}</div>}
                                    </div>
                                    <div className="space-y-1">
                                        <label className="text-xs font-bold text-gray-500 uppercase tracking-wider">Description</label>
                                        <textarea 
                                            className="form-control bg-light border-0 rounded-xl px-4 py-2.5 font-medium" 
                                            rows="2"
                                            value={data.description}
                                            onChange={e => setData('description', e.target.value)}
                                        ></textarea>
                                    </div>
                                    <div className="row g-3">
                                        <div className="col-md-6 space-y-1">
                                            <label className="text-xs font-bold text-gray-500 uppercase tracking-wider">Category</label>
                                            <select 
                                                className="form-select bg-light border-0 rounded-xl px-4 py-2.5 font-medium"
                                                value={data.category}
                                                onChange={e => setData('category', e.target.value)}
                                                required
                                            >
                                                <option value="surgery">Surgery</option>
                                                <option value="consultation">Consultation</option>
                                                <option value="imaging">Imaging/Radiology</option>
                                                <option value="lab">Lab Services</option>
                                                <option value="maternity">Maternity</option>
                                                <option value="nursing">Nursing Care</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div className="col-md-6 space-y-1">
                                            <label className="text-xs font-bold text-gray-500 uppercase tracking-wider">Standard Fee (KES)</label>
                                            <input 
                                                type="number" 
                                                step="0.01"
                                                className="form-control bg-light border-0 rounded-xl px-4 py-2.5 font-medium" 
                                                value={data.standard_fee}
                                                onChange={e => setData('standard_fee', e.target.value)}
                                                required
                                            />
                                            {errors.standard_fee && <div className="text-danger small mt-1">{errors.standard_fee}</div>}
                                        </div>
                                    </div>
                                </div>
                                <div className="modal-footer border-top-0 pt-0 px-4 pb-4">
                                    <button type="button" className="btn btn-light fw-bold rounded-pill px-4" onClick={closeModal}>Cancel</button>
                                    <button type="submit" className="btn btn-primary fw-bold rounded-pill px-4 shadow-sm" disabled={processing}>
                                        {isEditing ? 'Save Changes' : 'Create Procedure'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
