import RegistryTablePanel from '@/Components/RegistryTablePanel';
import { useMemo, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import Modal from '@/Components/Modal';
import DashboardSearch from '@/Components/DashboardSearch';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import TableActions from '@/Components/TableActions';
import StatusBadge from '@/Components/StatusBadge';
import { TableCellPrimary } from '@/Components/TableCells';

export default function Inventory({ inventory, filters, auth }) {
    const [search, setSearch] = useState(filters?.search || '');
    const [showModal, setShowModal] = useState(false);
    const [selectedMed, setSelectedMed] = useState(null);

    const { data, setData, post, processing, reset } = useForm({
        medication_id: '',
        quantity: '',
        type: 'add',
        notes: '',
        expiry_date: '',
    });

    const handleSearch = (searchValue, quickFilterValue = filters?.quick_filter) => {
        router.get(
            route('pharmacy.inventory'),
            { search: searchValue, quick_filter: quickFilterValue },
            { preserveState: true },
        );
    };

    const handleQuickFilterChange = (val) => {
        handleSearch(search, val);
    };

    const openUpdateModal = (item) => {
        setSelectedMed(item);
        setData({
            medication_id: item.medication_id,
            quantity: '',
            type: 'add',
            notes: '',
            expiry_date: item.expiry_date || '',
        });
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

    const columns = useMemo(
        () => [
            {
                header: 'Medicine Name',
                accessorKey: 'medication_name',
                cell: ({ row }) => <TableCellPrimary>{row.original.medication_name || 'N/A'}</TableCellPrimary>,
            },
            {
                header: 'Type',
                accessorKey: 'medication_type',
                cell: ({ row }) => (
                    <TableCellPrimary className="text-muted">
                        {row.original.medication_type || 'General'}
                    </TableCellPrimary>
                ),
            },
            {
                header: 'Stock Level',
                accessorKey: 'stock_quantity',
                cell: ({ row }) => (
                    <TableCellPrimary>
                        {row.original.stock_quantity ?? 0} {row.original.unit || 'units'}
                    </TableCellPrimary>
                ),
            },
            {
                header: 'Expiry Date',
                accessorKey: 'expiry_date',
                cell: ({ row }) => {
                    const expiry = row.original.expiry_date;
                    if (!expiry) return <TableCellPrimary className="text-muted">—</TableCellPrimary>;

                    const expDate = new Date(expiry);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    expDate.setHours(0, 0, 0, 0);

                    const diffDays = Math.ceil((expDate - today) / (1000 * 60 * 60 * 24));
                    let label = expiry;
                    if (diffDays < 0) label = `${expiry} (Expired)`;
                    else if (diffDays <= 90) label = `${expiry} (${diffDays} days left)`;

                    return (
                        <TableCellPrimary
                            className={diffDays < 0 ? 'text-danger' : diffDays <= 90 ? 'text-warning' : ''}
                        >
                            {label}
                        </TableCellPrimary>
                    );
                },
            },
            {
                header: 'Status',
                accessorKey: 'status',
                cell: ({ row }) => {
                    const isLow = (row.original.stock_quantity ?? 0) < 50;
                    return <StatusBadge status={isLow ? 'low_stock' : 'in_stock'} />;
                },
            },
            {
                header: 'Actions',
                id: 'actions',
                cell: ({ row }) => (
                    <TableActions
                        actions={[
                            {
                                icon: 'fa-boxes',
                                label: 'Update stock',
                                onClick: () => openUpdateModal(row.original),
                            },
                        ]}
                    />
                ),
            },
        ],
        [],
    );

    return (
        <AuthenticatedLayout
            headerTitle="Stock & Supply"
            breadcrumbs={[
                { label: 'Pharmacy', active: false },
                { label: 'Inventory', active: true },
            ]}
        >
            <Head title="Pharmacy Inventory" />

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
                <RegistryTablePanel
                    title="Inventory registry"
                    icon="fa-boxes"
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
                                <input
                                    className="form-check-input"
                                    type="radio"
                                    name="type"
                                    id="addType"
                                    value="add"
                                    checked={data.type === 'add'}
                                    onChange={(e) => setData('type', e.target.value)}
                                />
                                <label className="form-check-label" htmlFor="addType">
                                    Add to Current
                                </label>
                            </div>
                            <div className="form-check">
                                <input
                                    className="form-check-input"
                                    type="radio"
                                    name="type"
                                    id="setType"
                                    value="set"
                                    checked={data.type === 'set'}
                                    onChange={(e) => setData('type', e.target.value)}
                                />
                                <label className="form-check-label" htmlFor="setType">
                                    Set Absolute Value
                                </label>
                            </div>
                        </div>
                    </div>

                    <div className="mb-3">
                        <label className="form-label small fw-bold text-muted">Quantity ({selectedMed?.unit})</label>
                        <input
                            type="number"
                            className="form-control"
                            value={data.quantity}
                            onChange={(e) => setData('quantity', e.target.value)}
                            required
                        />
                    </div>

                    <div className="mb-3">
                        <label className="form-label small fw-bold text-muted">Expiry Date (Optional)</label>
                        <input
                            type="date"
                            className="form-control"
                            value={data.expiry_date || ''}
                            onChange={(e) => setData('expiry_date', e.target.value)}
                        />
                    </div>

                    <div className="mb-4">
                        <label className="form-label small fw-bold text-muted">Notes / Reason</label>
                        <textarea
                            className="form-control"
                            rows="2"
                            placeholder="e.g. New stock delivery, Correction..."
                            value={data.notes}
                            onChange={(e) => setData('notes', e.target.value)}
                        />
                    </div>

                    <div className="d-flex justify-content-end gap-2">
                        <button type="button" onClick={closeModal} className="btn btn-light rounded-pill px-4">
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="btn btn-primary rounded-pill px-4 fw-bold"
                        >
                            {processing ? 'Updating...' : 'Confirm Update'}
                        </button>
                    </div>
                </form>
            </Modal>
        </AuthenticatedLayout>
    );
}
