import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import Pagination from '@/Components/Pagination';
import Modal from '@/Components/Modal';
import DashboardSearch from '@/Components/DashboardSearch';
import { useState } from 'react';

export default function Medicines({ medicines, filters, auth }) {
    const [search, setSearch] = useState(filters?.search || '');
    const [showModal, setShowModal] = useState(false);
    const [editingMed, setEditingMed] = useState(null);

    const { data, setData, post, put, processing, errors, reset } = useForm({
        medication_name: '',
        medication_type: 'Tablet',
        strength: '',
        unit: 'Pills',
        price_per_unit: '',
        description: '',
    });

    const handleSearch = (searchValue) => {
        router.get(route('pharmacy.medicines'), { search: searchValue }, { preserveState: true });
    };

    const openCreateModal = () => {
        setEditingMed(null);
        reset();
        setShowModal(true);
    };

    const openEditModal = (med) => {
        setEditingMed(med);
        setData({
            medication_name: med.medication_name,
            medication_type: med.medication_type || 'Tablet',
            strength: med.strength || '',
            unit: med.unit || 'Pills',
            price_per_unit: med.price_per_unit || 0,
            description: med.description || '',
        });
        setShowModal(true);
    };

    const closeModal = () => {
        setShowModal(false);
        setEditingMed(null);
        reset();
    };

    const submit = (e) => {
        e.preventDefault();
        if (editingMed) {
            put(route('pharmacy.medicines.update', editingMed.medication_id), {
                onSuccess: () => closeModal(),
            });
        } else {
            post(route('pharmacy.medicines.store'), {
                onSuccess: () => closeModal(),
            });
        }
    };

    const deleteMed = (id) => {
        if (confirm('Are you sure you want to remove this medicine?')) {
            router.delete(route('pharmacy.medicines.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header="Medicine List"
        >
            <Head title="Medicines" />

            <PageHeader 
                title="Medication Catalog"
                breadcrumbs={[{ label: 'Pharmacy', url: route('pharmacy.inventory') }, { label: 'Medicines', active: true }]}
                actions={
                    <button onClick={openCreateModal} className="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                        <i className="fas fa-plus me-2"></i>Add Medicine
                    </button>
                }
            />

            <DashboardSearch 
                placeholder="Search medication catalog..." 
                value={search}
                onChange={setSearch}
                onSubmit={handleSearch}
            />

            <div className="py-0">
                <div className="row g-4">
                    {medicines.data.length > 0 ? (
                        medicines.data.map((med) => (
                            <div key={med.medication_id} className="col-md-3">
                                <div className="card h-100 shadow-sm border-0 rounded-2xl bg-white overflow-hidden hover-lift p-4 text-center">
                                    <div className="avatar-lg bg-soft-success text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3 mx-auto" style={{ width: '48px', height: '48px' }}>
                                        <i className="fas fa-pills fs-5"></i>
                                    </div>
                                    <h6 className="fw-bold mb-1">{med.medication_name}</h6>
                                    <p className="text-gray-400 font-bold uppercase text-xs mb-2">{med.medication_type || 'General'}</p>
                                    <div className="mt-auto">
                                        <small className="text-muted d-block mb-2">{med.strength} {med.unit}</small>
                                        <div className="text-primary fw-bold mb-2">Ksh {med.price_per_unit || 0}</div>
                                        <div className="d-flex gap-2">
                                            <button onClick={() => openEditModal(med)} className="btn btn-sm btn-light w-100 rounded-pill font-bold border">Edit</button>
                                            <button onClick={() => deleteMed(med.medication_id)} className="btn btn-sm btn-light w-100 rounded-pill font-bold border text-danger">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="col-12 text-center py-5">
                            <p className="text-muted">No medicines available in the catalog.</p>
                        </div>
                    )}
                </div>

                <div className="mt-5">
                    <Pagination links={medicines.links} />
                </div>
            </div>

            <Modal show={showModal} onClose={closeModal} maxWidth="lg">
                <form onSubmit={submit} className="p-4">
                    <h5 className="fw-bold mb-4">{editingMed ? 'Edit Medicine' : 'Add New Medicine'}</h5>
                    
                    <div className="mb-3">
                        <label className="form-label small fw-bold text-muted">Medicine Name</label>
                        <input 
                            type="text" 
                            className={`form-control ${errors.medication_name ? 'is-invalid' : ''}`}
                            value={data.medication_name}
                            onChange={e => setData('medication_name', e.target.value)}
                            required
                        />
                        {errors.medication_name && <div className="invalid-feedback">{errors.medication_name}</div>}
                    </div>

                    <div className="row g-3 mb-3">
                        <div className="col-md-6">
                            <label className="form-label small fw-bold text-muted">Type</label>
                            <select 
                                className="form-select"
                                value={data.medication_type}
                                onChange={e => setData('medication_type', e.target.value)}
                            >
                                <option value="Tablet">Tablet</option>
                                <option value="Capsule">Capsule</option>
                                <option value="Syrup">Syrup</option>
                                <option value="Injection">Injection</option>
                                <option value="Ointment">Ointment</option>
                                <option value="General">General</option>
                            </select>
                        </div>
                        <div className="col-md-6">
                            <label className="form-label small fw-bold text-muted">Price per Unit (Ksh)</label>
                            <input 
                                type="number" 
                                step="0.01"
                                className="form-control"
                                value={data.price_per_unit}
                                onChange={e => setData('price_per_unit', e.target.value)}
                                required
                            />
                        </div>
                    </div>

                    <div className="row g-3 mb-3">
                        <div className="col-md-6">
                            <label className="form-label small fw-bold text-muted">Strength</label>
                            <input 
                                type="text" 
                                className="form-control"
                                placeholder="e.g. 500mg"
                                value={data.strength}
                                onChange={e => setData('strength', e.target.value)}
                                required
                            />
                        </div>
                        <div className="col-md-6">
                            <label className="form-label small fw-bold text-muted">Unit</label>
                            <input 
                                type="text" 
                                className="form-control"
                                placeholder="e.g. Pills, ml"
                                value={data.unit}
                                onChange={e => setData('unit', e.target.value)}
                                required
                            />
                        </div>
                    </div>

                    <div className="mb-4">
                        <label className="form-label small fw-bold text-muted">Description (Optional)</label>
                        <textarea 
                            className="form-control"
                            rows="2"
                            value={data.description}
                            onChange={e => setData('description', e.target.value)}
                        />
                    </div>

                    <div className="d-flex justify-content-end gap-2">
                        <button type="button" onClick={closeModal} className="btn btn-light rounded-pill px-4">Cancel</button>
                        <button type="submit" disabled={processing} className="btn btn-primary rounded-pill px-4 fw-bold">
                            {processing ? 'Saving...' : (editingMed ? 'Update Medicine' : 'Add to Catalog')}
                        </button>
                    </div>
                </form>
            </Modal>
        </AuthenticatedLayout>
    );
}
