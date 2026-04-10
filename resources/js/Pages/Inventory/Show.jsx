import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Show({ medication, auth }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header="Medication Details"
        >
            <Head title={`Inventory - ${medication.medication_name}`} />

            <div className="container-fluid inventory-page px-0">
                <div className="d-flex justify-content-between align-items-center mb-4">
                    <h2 className="mb-0">{medication.medication_name}</h2>
                    <Link href={route('inventory.index')} className="btn btn-outline-secondary">
                        <i className="fas fa-arrow-left me-2"></i>Back to Inventory
                    </Link>
                </div>

                <div className="row">
                    <div className="col-lg-4">
                        <div className="card shadow-sm border-0 mb-4 h-100 p-4 text-center">
                            <div className="mb-3">
                                <i className="fas fa-pills text-primary" style={{ fontSize: '4rem' }}></i>
                            </div>
                            <h4 className="mb-1">{medication.medication_name}</h4>
                            <p className="text-muted fw-semibold mb-3">{medication.medication_type}</p>
                            
                            <div className={`p-3 rounded mb-4 ${medication.stock_quantity > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'}`}>
                                <div className="small text-uppercase fw-bold">Current Stock</div>
                                <div className="h2 mb-0 fw-bold">{medication.stock_quantity} <small className="h6">{medication.unit}</small></div>
                            </div>

                            <div className="text-start mb-4">
                                <div className="mb-2"><i className="fas fa-tag text-muted me-2"></i><strong>Strength:</strong> {medication.strength} {medication.unit}</div>
                                <div className="mb-2"><i className="fas fa-box text-muted me-2"></i><strong>Category:</strong> {medication.category || 'N/A'}</div>
                            </div>
                            
                            <div className="d-grid gap-2">
                                <button className="btn btn-primary shadow-sm"><i className="fas fa-plus-circle me-1"></i> Add Stock</button>
                                <button className="btn btn-outline-secondary btn-sm">Edit Details</button>
                            </div>
                        </div>
                    </div>

                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 mb-4 h-100">
                            <div className="card-header bg-white py-3">
                                <h6 className="mb-0 fw-bold">Clinical Information & Batches</h6>
                            </div>
                            <div className="card-body p-4">
                                <h6 className="text-primary fw-bold small text-uppercase mb-2">Description</h6>
                                <p className="text-muted mb-4">{medication.description || 'No description provided.'}</p>

                                <h6 className="text-primary fw-bold small text-uppercase mb-2">Usage Instructions</h6>
                                <p className="text-muted mb-4">{medication.usage_instructions || 'N/A'}</p>

                                <hr className="my-4" />

                                <h6 className="fw-bold mb-3">Stock Batches</h6>
                                <div className="table-responsive">
                                    <table className="table table-hover align-middle border">
                                        <thead className="bg-light">
                                            <tr>
                                                <th>Batch #</th>
                                                <th>Mfg Date</th>
                                                <th>Expiry Date</th>
                                                <th className="text-center">Qty</th>
                                                <th className="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td className="fw-bold">B-99812</td>
                                                <td>2024-01-15</td>
                                                <td className="text-danger">2026-01-15</td>
                                                <td className="text-center">{medication.stock_quantity}</td>
                                                <td className="text-center"><span className="badge bg-success">Active</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
