import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import Pagination from '@/Components/Pagination';
import Modal from '@/Components/Modal';
import DashboardSearch from '@/Components/DashboardSearch';
import { useState } from 'react';

export default function Inventory({ inventory, filters, auth }) {
    const [search, setSearch] = useState(filters?.search || '');
    const [showModal, setShowModal] = useState(false);
    const [selectedMed, setSelectedMed] = useState(null);

    const { data, setData, post, processing, reset } = useForm({
        medication_id: '',
        quantity: '',
        type: 'add',
        notes: '',
    });

    const handleSearch = (searchValue) => {
        router.get(route('pharmacy.inventory'), { search: searchValue }, { preserveState: true });
    };

    const openUpdateModal = (item) => {
        setSelectedMed(item);
        setData('medication_id', item.medication_id);
        setShowModal(true);
    };

    const closeModal = () => {
        setShowModal(false);
        setSelectedMed(null);
        reset();
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('pharmacy.inventory.update-stock'), {
            onSuccess: () => closeModal(),
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header="Pharmacy Inventory"
        >
            <Head title="Pharmacy Inventory" />

            <PageHeader 
                title="Stock & Supply"
                breadcrumbs={[{ label: 'Pharmacy', active: true }, { label: 'Inventory', active: true }]}
            />

            <DashboardSearch 
                placeholder="Search inventory (e.g. Paracetamol, Cough Syrup...)" 
                value={search}
                onChange={setSearch}
                onSubmit={handleSearch}
            />

            <div className="py-0">
                <div className="card shadow-sm border-0 rounded-xl overflow-hidden bg-white">
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead className="bg-light">
                                <tr className="text-uppercase small tracking-wider">
                                    <th className="px-4 py-3 text-muted">Medicine Name</th>
                                    <th className="py-3 text-muted">Type</th>
                                    <th className="py-3 text-muted">Stock Level</th>
                                    <th className="py-3 text-muted">Status</th>
                                    <th className="pe-4 py-3 text-end text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {inventory.data && inventory.data.length > 0 ? (
                                    inventory.data.filter(item => item !== null).map((item) => (
                                        <tr key={item.medication_id || `temp-${Math.random()}`}>
                                            <td className="px-4 fw-bold">{item.medication_name || 'N/A'}</td>
                                            <td className="text-muted">{item.medication_type || 'General'}</td>
                                            <td className="font-semibold">{item.stock_quantity ?? 0} {item.unit || 'units'}</td>
                                            <td>
                                                <span className={`badge rounded-pill px-3 py-1 ${(item.stock_quantity ?? 0) < 50 ? 'bg-danger' : 'bg-success'}`}>
                                                    {(item.stock_quantity ?? 0) < 50 ? 'Low Stock' : 'In Stock'}
                                                </span>
                                            </td>
                                            <td className="pe-4 text-end">
                                                <button onClick={() => openUpdateModal(item)} className="btn btn-sm btn-outline-primary rounded-pill px-3">Update Stock</button>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="5" className="text-center py-5 text-muted">
                                            No inventory items found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                <div className="mt-4">
                    <Pagination links={inventory.links} />
                </div>
            </div>

            <Modal show={showModal} onClose={closeModal} maxWidth="md">
                <form onSubmit={submit} className="p-4">
                    <h5 className="fw-bold mb-4 text-primary">Update Stock: {selectedMed?.medication_name}</h5>
                    
                    <div className="mb-3">
                        <label className="form-label small fw-bold text-muted">Action Type</label>
                        <div className="d-flex gap-3">
                            <div className="form-check">
                                <input className="form-check-input" type="radio" name="type" id="addType" value="add" checked={data.type === 'add'} onChange={e => setData('type', e.target.value)} />
                                <label className="form-check-label" htmlFor="addType">Add to Current</label>
                            </div>
                            <div className="form-check">
                                <input className="form-check-input" type="radio" name="type" id="setType" value="set" checked={data.type === 'set'} onChange={e => setData('type', e.target.value)} />
                                <label className="form-check-label" htmlFor="setType">Set Absolute Value</label>
                            </div>
                        </div>
                    </div>

                    <div className="mb-3">
                        <label className="form-label small fw-bold text-muted">Quantity ({selectedMed?.unit})</label>
                        <input 
                            type="number" 
                            className="form-control"
                            value={data.quantity}
                            onChange={e => setData('quantity', e.target.value)}
                            required
                        />
                    </div>

                    <div className="mb-4">
                        <label className="form-label small fw-bold text-muted">Notes / Reason</label>
                        <textarea 
                            className="form-control"
                            rows="2"
                            placeholder="e.g. New stock delivery, Correction..."
                            value={data.notes}
                            onChange={e => setData('notes', e.target.value)}
                        />
                    </div>

                    <div className="d-flex justify-content-end gap-2">
                        <button type="button" onClick={closeModal} className="btn btn-light rounded-pill px-4">Cancel</button>
                        <button type="submit" disabled={processing} className="btn btn-primary rounded-pill px-4 fw-bold">
                            {processing ? 'Updating...' : 'Confirm Update'}
                        </button>
                    </div>
                </form>
            </Modal>
        </AuthenticatedLayout>
    );
}
