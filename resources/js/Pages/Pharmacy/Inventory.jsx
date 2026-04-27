import DashboardTable from '@/Components/DashboardTable';
import { useMemo, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import Modal from '@/Components/Modal';
import DashboardSearch from '@/Components/DashboardSearch';

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

    const handleSearch = (searchValue, quickFilterValue = filters?.quick_filter) => {
        router.get(route('pharmacy.inventory'), { search: searchValue, quick_filter: quickFilterValue }, { preserveState: true });
    };

    const handleQuickFilterChange = (val) => {
        handleSearch(search, val);
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

    const columns = useMemo(() => [
        {
            header: 'Medicine Name',
            accessorKey: 'medication_name',
            cell: ({ row }) => <span className="fw-bold">{row.original.medication_name || 'N/A'}</span>
        },
        {
            header: 'Type',
            accessorKey: 'medication_type',
            cell: ({ row }) => <span className="text-muted">{row.original.medication_type || 'General'}</span>
        },
        {
            header: 'Stock Level',
            accessorKey: 'stock_quantity',
            cell: ({ row }) => <span className="fw-semibold">{row.original.stock_quantity ?? 0} {row.original.unit || 'units'}</span>
        },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: ({ row }) => {
                const isLow = (row.original.stock_quantity ?? 0) < 50;
                return (
                    <span className={`badge rounded-pill px-3 py-1 ${isLow ? 'bg-danger' : 'bg-success'}`}>
                        {isLow ? 'Low Stock' : 'In Stock'}
                    </span>
                );
            }
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => (
                <div className="text-end">
                    <button onClick={() => openUpdateModal(row.original)} className="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm transition-all hover-scale">Update Stock</button>
                </div>
            )
        }
    ], []);

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
                onFilterChange={handleQuickFilterChange}
                filters={[
                    { label: 'Low Stock', value: 'low_stock' },
                    { label: 'Out of Stock', value: 'out_of_stock' },
                ]}
            />

            <div className="py-0">
                <DashboardTable 
                    columns={columns}
                    data={inventory.data || []}
                    pagination={inventory}
                    emptyMessage="No inventory items found."
                />
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
