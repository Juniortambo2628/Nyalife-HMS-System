import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import { useState, useMemo, useCallback } from 'react';
import PageHeader from '@/Components/PageHeader';
import DashboardTable from '@/Components/DashboardTable';
import { toast } from 'react-hot-toast';

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

    const openModal = useCallback((procedure = null) => {
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
    }, [setData, reset]);

    const closeModal = () => {
        setShowModal(false);
        reset();
        setIsEditing(false);
    };

    const submitForm = (e) => {
        e.preventDefault();
        if (isEditing) {
            put(route('medical-procedures.update', data.procedure_id), {
                onSuccess: () => {
                    closeModal();
                    toast.success('Procedure updated successfully!');
                },
                onError: () => toast.error('Failed to update procedure.')
            });
        } else {
            post(route('medical-procedures.store'), {
                onSuccess: () => {
                    closeModal();
                    toast.success('New procedure created successfully!');
                },
                onError: () => toast.error('Failed to create procedure.')
            });
        }
    };

    const toggleStatus = useCallback((id) => {
        router.post(route('medical-procedures.toggle', id), {}, {
            onSuccess: () => toast.success('Status updated!'),
            onError: () => toast.error('Failed to update status.')
        });
    }, []);

    const deleteProcedure = useCallback((id) => {
        if (confirm('Are you sure you want to delete this procedure?')) {
            destroy(route('medical-procedures.destroy', id), {
                onSuccess: () => toast.success('Procedure deleted successfully!'),
                onError: () => toast.error('Failed to delete procedure.')
            });
        }
    }, [destroy]);

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount);
    };

    const columns = useMemo(() => [
        {
            header: 'Name & Description',
            accessorKey: 'name',
            cell: ({ row }) => (
                <div className="px-1">
                    <div className="fw-extrabold text-gray-900">{row.original.name}</div>
                    <div className="text-muted extra-small text-truncate" style={{maxWidth: '300px'}}>
                        {row.original.description || 'No description provided'}
                    </div>
                </div>
            )
        },
        {
            header: 'Category',
            accessorKey: 'category',
            cell: ({ row }) => (
                <span className="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill fw-bold text-uppercase tracking-wider text-xs border border-secondary-subtle shadow-sm">
                    {row.original.category}
                </span>
            )
        },
        {
            header: 'Standard Fee',
            accessorKey: 'standard_fee',
            cell: ({ row }) => (
                <div className="fw-extrabold text-gray-800">
                    {formatCurrency(row.original.standard_fee)}
                </div>
            )
        },
        {
            header: 'Status',
            accessorKey: 'is_active',
            cell: ({ row }) => (
                <div className="form-check form-switch ms-2">
                    <input 
                        className="form-check-input transition-all" 
                        type="checkbox" 
                        checked={row.original.is_active} 
                        onChange={() => toggleStatus(row.original.procedure_id)}
                        style={{cursor: 'pointer', transform: 'scale(1.2)'}}
                    />
                </div>
            )
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => (
                <div className="text-end">
                    <div className="d-flex justify-content-end gap-2">
                        <button onClick={() => openModal(row.original)} className="btn btn-sm btn-white border shadow-sm rounded-circle d-flex align-items-center justify-content-center transition-all hover-scale" style={{width: 34, height: 34}} title="Edit">
                            <i className="fas fa-edit text-primary"></i>
                        </button>
                        <button onClick={() => deleteProcedure(row.original.procedure_id)} className="btn btn-sm btn-white border shadow-sm rounded-circle d-flex align-items-center justify-content-center transition-all hover-scale" style={{width: 34, height: 34}} title="Delete">
                            <i className="fas fa-trash-alt text-danger"></i>
                        </button>
                    </div>
                </div>
            )
        }
    ], [openModal, deleteProcedure, toggleStatus]);

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

            <div className="py-0">
                <DashboardTable 
                    columns={columns}
                    data={procedures}
                    emptyMessage="No procedures found in the catalog."
                />
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

const styles = `
    .extra-small { font-size: 0.75rem; }
    .hover-scale:hover { transform: scale(1.1); }
    .btn-white { background: white; }
    .btn-white:hover { background: #f8f9fa; }
    .rounded-3xl { border-radius: 1.75rem; }
    .rounded-xl { border-radius: 1rem; }
`;

if (typeof document !== 'undefined') {
    const styleSheet = document.createElement("style");
    styleSheet.innerText = styles;
    document.head.appendChild(styleSheet);
}
