import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Show({ medication, auth }) {
    return (
        <AuthenticatedLayout header="Medication Details">
            <Head title={`Inventory - ${medication.medication_name}`} />

            <PageHeader 
                title="Stock Detail"
                breadcrumbs={[
                    { label: 'Pharmacy', url: route('inventory.index') },
                    { label: 'Inventory Stock', url: route('inventory.index') },
                    { label: medication.medication_name, active: true }
                ]}
            />

            <UnifiedToolbar 
                actions={[
                    { 
                        label: 'ADD STOCK', 
                        icon: 'fa-plus-circle', 
                        onClick: () => console.log('Add stock'),
                        color: 'primary'
                    },
                    { 
                        label: 'EDIT DETAILS', 
                        icon: 'fa-edit', 
                        onClick: () => console.log('Edit'),
                        color: 'gray'
                    },
                    { 
                        label: 'STOCK REGISTRY', 
                        icon: 'fa-arrow-left', 
                        href: route('inventory.index'),
                        color: 'gray'
                    }
                ]}
            />

            <div className="px-0 pb-5">

                <div className="row">
                    <div className="col-lg-4">
                        <div className="card shadow-sm border-0 mb-4 h-100 rounded-3xl overflow-hidden bg-white shadow-hover">
                            <div className="card-header bg-gradient-primary-to-secondary p-5 border-0 text-center">
                                <div className="mb-4 bg-white bg-opacity-20 rounded-circle w-20 h-20 d-flex align-items-center justify-content-center mx-auto shadow-sm">
                                    <i className="fas fa-pills text-white fa-3x"></i>
                                </div>
                                <h4 className="mb-1 text-white fw-extrabold tracking-tighter">{medication.medication_name}</h4>
                                <div className="extra-small font-bold text-white opacity-50 tracking-widest uppercase mb-3">{medication.medication_type}</div>
                            </div>
                            <div className="card-body p-4 pt-5">
                                <div className={`p-4 rounded-2xl mb-4 text-center border-2 ${medication.stock_quantity > 0 ? 'bg-success-subtle border-success border-opacity-10 text-success' : 'bg-danger-subtle border-danger border-opacity-10 text-danger'}`}>
                                    <div className="extra-small text-uppercase fw-extrabold tracking-widest mb-1">Current Stock</div>
                                    <div className="h2 mb-0 fw-extrabold">{medication.stock_quantity} <span className="h6 opacity-50 fw-bold">{medication.unit}</span></div>
                                </div>

                                <div className="space-y-4 pt-4 border-top border-gray-100 mt-4">
                                    <div className="d-flex justify-content-between align-items-center">
                                        <div className="d-flex align-items-center gap-2">
                                            <i className="fas fa-tag text-primary opacity-30"></i>
                                            <span className="extra-small fw-bold text-muted text-uppercase tracking-widest">Strength</span>
                                        </div>
                                        <span className="fw-extrabold text-gray-900 small">{medication.strength} {medication.unit}</span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center">
                                        <div className="d-flex align-items-center gap-2">
                                            <i className="fas fa-box text-primary opacity-30"></i>
                                            <span className="extra-small fw-bold text-muted text-uppercase tracking-widest">Category</span>
                                        </div>
                                        <span className="fw-extrabold text-gray-900 small text-uppercase">{medication.category || 'N/A'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 mb-4 rounded-3xl bg-white shadow-hover overflow-hidden">
                            <div className="card-header bg-white border-bottom-0 pt-4 pb-0 px-5">
                                <h6 className="mb-0 extra-small fw-extrabold text-muted text-uppercase tracking-widest opacity-50">Clinical Information & Batches</h6>
                            </div>
                            <div className="card-body p-5">
                                <div className="space-y-6 mb-5">
                                    <div>
                                        <h6 className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-3">Pharmacology Description</h6>
                                        <div className="p-4 bg-light rounded-2xl border-l-4 border-primary shadow-inner fw-bold text-gray-800 leading-relaxed">
                                            {medication.description || 'No detailed pharmacology description provided.'}
                                        </div>
                                    </div>
                                    <div>
                                        <h6 className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-3">Usage Instructions</h6>
                                        <div className="p-4 bg-gray-50 rounded-2xl border border-gray-100 text-muted small leading-relaxed font-medium">
                                            {medication.usage_instructions || 'N/A'}
                                        </div>
                                    </div>
                                </div>

                                <div className="pt-4 border-top border-gray-50">
                                    <h6 className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-4">Active Batches</h6>
                                    <div className="table-responsive rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
                                        <table className="table table-hover align-middle mb-0">
                                            <thead className="bg-pink-500 border-0">
                                                <tr>
                                                    <th className="px-4 py-3 extra-small fw-extrabold text-white text-uppercase tracking-widest border-0">Batch Reference</th>
                                                    <th className="px-4 py-3 extra-small fw-extrabold text-white text-uppercase tracking-widest border-0">Manufacturing</th>
                                                    <th className="px-4 py-3 extra-small fw-extrabold text-white text-uppercase tracking-widest border-0">Expiry</th>
                                                    <th className="px-4 py-3 text-center extra-small fw-extrabold text-white text-uppercase tracking-widest border-0">Quantity</th>
                                                    <th className="px-4 py-3 text-center extra-small fw-extrabold text-white text-uppercase tracking-widest border-0">State</th>
                                                </tr>
                                            </thead>
                                            <tbody className="border-0">
                                                <tr className="border-bottom border-gray-50">
                                                    <td className="px-4 py-3 fw-extrabold text-gray-900 small">B-99812</td>
                                                    <td className="px-4 py-3 text-muted extra-small fw-bold">2024-01-15</td>
                                                    <td className="px-4 py-3 text-danger extra-small fw-bold">2026-01-15</td>
                                                    <td className="px-4 py-3 text-center fw-extrabold text-gray-900 small">{medication.stock_quantity}</td>
                                                    <td className="px-4 py-3 text-center">
                                                        <span className="badge rounded-pill bg-success px-3 py-1.5 extra-small fw-extrabold text-uppercase">Active</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
