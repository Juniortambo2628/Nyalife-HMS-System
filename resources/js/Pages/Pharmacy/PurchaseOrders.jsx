import DashboardTable from '@/Components/DashboardTable';
import { useMemo, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import Modal from '@/Components/Modal';

export default function PurchaseOrders({ orders, lowStockMedications, auth }) {
    const [showModal, setShowModal] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        medication_id: lowStockMedications?.[0]?.medication_id || '',
        quantity: '',
        supplier_name: 'Global Pharma Distributors',
        estimated_cost: '',
    });

    const openCreateModal = () => {
        reset();
        setShowModal(true);
    };

    const closeModal = () => {
        setShowModal(false);
        reset();
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('pharmacy.po.store'), {
            onSuccess: () => closeModal(),
        });
    };

    const updateStatus = (id, newStatus) => {
        if (confirm(`Are you sure you want to mark this order as ${newStatus}?`)) {
            router.put(route('pharmacy.po.update-status', id), { status: newStatus });
        }
    };

    const columns = useMemo(() => [
        {
            header: 'PO Number',
            accessorKey: 'order_number',
            cell: ({ row }) => <span className="fw-bold text-primary">{row.original.order_number}</span>
        },
        {
            header: 'Medication',
            accessorKey: 'medication_name',
            cell: ({ row }) => <span className="fw-semibold">{row.original.medication_name}</span>
        },
        {
            header: 'Quantity',
            accessorKey: 'quantity',
            cell: ({ row }) => <span>{row.original.quantity} units</span>
        },
        {
            header: 'Supplier',
            accessorKey: 'supplier_name',
            cell: ({ row }) => <span className="text-muted">{row.original.supplier_name}</span>
        },
        {
            header: 'Est. Cost',
            accessorKey: 'estimated_cost',
            cell: ({ row }) => <span className="fw-semibold">Ksh {Number(row.original.estimated_cost).toLocaleString()}</span>
        },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: ({ row }) => {
                const status = row.original.status;
                let badgeClass = 'bg-soft-secondary text-secondary';
                if (status === 'pending') badgeClass = 'bg-soft-warning text-warning border border-warning-subtle';
                if (status === 'ordered') badgeClass = 'bg-soft-info text-info border border-info-subtle';
                if (status === 'received') badgeClass = 'bg-soft-success text-success border border-success-subtle';
                if (status === 'cancelled') badgeClass = 'bg-soft-danger text-danger border border-danger-subtle';
                
                return (
                    <span className={`badge px-3 py-1 rounded-pill ${badgeClass}`} style={{ textTransform: 'capitalize' }}>
                        {status}
                    </span>
                );
            }
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => {
                const order = row.original;
                if (order.status === 'received' || order.status === 'cancelled') {
                    return <span className="text-muted small">No actions</span>;
                }
                return (
                    <div className="d-flex gap-2 justify-content-end">
                        {order.status === 'pending' && (
                            <button 
                                onClick={() => updateStatus(order.id, 'ordered')} 
                                className="btn btn-xs btn-outline-info rounded-pill px-2"
                                style={{ fontSize: '0.75rem' }}
                            >
                                Mark Ordered
                            </button>
                        )}
                        {order.status === 'ordered' && (
                            <button 
                                onClick={() => updateStatus(order.id, 'received')} 
                                className="btn btn-xs btn-outline-success rounded-pill px-2"
                                style={{ fontSize: '0.75rem' }}
                            >
                                Mark Received
                            </button>
                        )}
                        <button 
                            onClick={() => updateStatus(order.id, 'cancelled')} 
                            className="btn btn-xs btn-outline-danger rounded-pill px-2"
                            style={{ fontSize: '0.75rem' }}
                        >
                            Cancel
                        </button>
                    </div>
                );
            }
        }
    ], [lowStockMedications]);

    return (
        <AuthenticatedLayout
            user={auth.user}
            header="Purchase Orders"
        >
            <Head title="Pharmacy Purchase Orders" />

            <PageHeader 
                title="Medicine Purchase Orders"
                breadcrumbs={[{ label: 'Pharmacy', url: route('pharmacy.inventory') }, { label: 'Purchase Orders', active: true }]}
                actions={
                    <button onClick={openCreateModal} className="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                        <i className="fas fa-plus me-2"></i>Create Purchase Order
                    </button>
                }
            />

            <div className="py-0">
                <div className="row g-4 mb-4">
                    <div className="col-md-12">
                        <div className="card shadow-sm border-0 rounded-2xl bg-white p-4">
                            <h6 className="fw-bold mb-3 text-secondary">Low Stock Alerts (&lt;= 20 units)</h6>
                            {lowStockMedications && lowStockMedications.length > 0 ? (
                                <div className="d-flex flex-wrap gap-2">
                                    {lowStockMedications.map(med => (
                                        <div key={med.medication_id} className="badge bg-soft-danger text-danger border border-danger-subtle rounded-pill p-2 d-flex align-items-center gap-2">
                                            <span>{med.medication_name} ({med.stock_quantity} left)</span>
                                            <button 
                                                onClick={() => {
                                                    setData(prev => ({
                                                        ...prev,
                                                        medication_id: med.medication_id,
                                                        supplier_name: 'Global Pharma Distributors',
                                                        quantity: 100,
                                                        estimated_cost: (med.price_per_unit * 100 * 0.8).toFixed(0) // 20% bulk discount estimate
                                                    }));
                                                    setShowModal(true);
                                                }}
                                                className="btn btn-xs btn-danger rounded-circle p-1 d-flex align-items-center justify-content-center"
                                                style={{ width: '16px', height: '16px', fontSize: '10px' }}
                                                title="Quick order 100 units"
                                            >
                                                +
                                            </button>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-muted mb-0 small">No low stock items. All stock levels look healthy!</p>
                            )}
                        </div>
                    </div>
                </div>

                <DashboardTable 
                    columns={columns}
                    data={orders.data || []}
                    pagination={orders}
                    emptyMessage="No purchase orders found."
                />
            </div>

            <Modal show={showModal} onClose={closeModal} maxWidth="lg">
                <form onSubmit={submit} className="p-4">
                    <h5 className="fw-bold mb-4 text-primary">Create Purchase Order</h5>
                    
                    <div className="mb-3">
                        <label className="form-label small fw-bold text-muted">Select Medication</label>
                        <select 
                            className={`form-select ${errors.medication_id ? 'is-invalid' : ''}`}
                            value={data.medication_id}
                            onChange={e => {
                                const selectedId = e.target.value;
                                const med = lowStockMedications?.find(m => m.medication_id === Number(selectedId));
                                setData(prev => ({
                                    ...prev,
                                    medication_id: selectedId,
                                    estimated_cost: med ? (med.price_per_unit * (prev.quantity || 1) * 0.8).toFixed(0) : prev.estimated_cost
                                }));
                            }}
                            required
                        >
                            <option value="">-- Choose Medication --</option>
                            {lowStockMedications?.map(med => (
                                <option key={med.medication_id} value={med.medication_id}>
                                    {med.medication_name} ({med.strength} {med.unit}) - Stock: {med.stock_quantity}
                                </option>
                            ))}
                        </select>
                        {errors.medication_id && <div className="invalid-feedback">{errors.medication_id}</div>}
                    </div>

                    <div className="row g-3 mb-3">
                        <div className="col-md-6">
                            <label className="form-label small fw-bold text-muted">Quantity to Order</label>
                            <input 
                                type="number" 
                                className={`form-control ${errors.quantity ? 'is-invalid' : ''}`}
                                value={data.quantity}
                                onChange={e => {
                                    const qty = e.target.value;
                                    const med = lowStockMedications?.find(m => m.medication_id === Number(data.medication_id));
                                    setData(prev => ({
                                        ...prev,
                                        quantity: qty,
                                        estimated_cost: med ? (med.price_per_unit * Number(qty) * 0.8).toFixed(0) : prev.estimated_cost
                                    }));
                                }}
                                required
                                min="1"
                            />
                            {errors.quantity && <div className="invalid-feedback">{errors.quantity}</div>}
                        </div>
                        <div className="col-md-6">
                            <label className="form-label small fw-bold text-muted">Estimated Cost (Ksh)</label>
                            <input 
                                type="number" 
                                className={`form-control ${errors.estimated_cost ? 'is-invalid' : ''}`}
                                value={data.estimated_cost}
                                onChange={e => setData('estimated_cost', e.target.value)}
                                required
                                min="0"
                            />
                            {errors.estimated_cost && <div className="invalid-feedback">{errors.estimated_cost}</div>}
                        </div>
                    </div>

                    <div className="mb-4">
                        <label className="form-label small fw-bold text-muted">Supplier Name</label>
                        <input 
                            type="text" 
                            className={`form-control ${errors.supplier_name ? 'is-invalid' : ''}`}
                            value={data.supplier_name}
                            onChange={e => setData('supplier_name', e.target.value)}
                            required
                        />
                        {errors.supplier_name && <div className="invalid-feedback">{errors.supplier_name}</div>}
                    </div>

                    <div className="d-flex justify-content-end gap-2">
                        <button type="button" onClick={closeModal} className="btn btn-light rounded-pill px-4">Cancel</button>
                        <button type="submit" disabled={processing} className="btn btn-primary rounded-pill px-4 fw-bold">
                            {processing ? 'Creating...' : 'Submit Order'}
                        </button>
                    </div>
                </form>
            </Modal>
        </AuthenticatedLayout>
    );
}
