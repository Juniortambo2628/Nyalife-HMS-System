import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ medications, filters, auth }) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('inventory.index'), { search }, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header="Pharmacy Inventory"
        >
            <Head title="Inventory" />

            <div className="container-fluid inventory-page px-0">
                <div className="d-flex justify-content-between align-items-center mb-4">
                    <h2 className="mb-0">Medication Stock</h2>
                    {auth.user.role === 'pharmacist' && (
                        <Link href="#" className="btn btn-primary shadow-sm">
                            <i className="fas fa-plus me-2"></i>Add New Drug
                        </Link>
                    )}
                </div>

                {/* Search */}
                <div className="card shadow-sm border-0 mb-4">
                    <div className="card-body p-4">
                        <form onSubmit={handleSearch} className="row g-3">
                            <div className="col-md-10">
                                <div className="input-group">
                                    <span className="input-group-text bg-white border-end-0">
                                        <i className="fas fa-search text-muted"></i>
                                    </span>
                                    <input 
                                        type="text" 
                                        className="form-control border-start-0 ps-0" 
                                        placeholder="Search by medication name or generic name..." 
                                        value={search}
                                        onChange={e => setSearch(e.target.value)}
                                    />
                                </div>
                            </div>
                            <div className="col-md-2">
                                <button type="submit" className="btn btn-primary w-100">Search</button>
                            </div>
                        </form>
                    </div>
                </div>

                {/* Table */}
                <div className="card shadow-sm border-0 overflow-hidden">
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead className="bg-light">
                                <tr>
                                    <th className="px-4 py-3">Medication Name</th>
                                    <th className="py-3">Type</th>
                                    <th className="py-3">Strength</th>
                                    <th className="py-3 text-center">In Stock</th>
                                    <th className="py-3 text-center">Status</th>
                                    <th className="pe-4 py-3 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {medications.data.length > 0 ? (
                                    medications.data.map((m) => (
                                        <tr key={m.medication_id}>
                                            <td className="px-4 fw-bold text-primary">{m.medication_name}</td>
                                            <td>{m.medication_type}</td>
                                            <td>{m.strength} {m.unit}</td>
                                            <td className="text-center fw-bold">{m.stock_quantity}</td>
                                            <td className="text-center">
                                                <span className={`badge ${m.stock_quantity > 20 ? 'bg-success' : (m.stock_quantity > 0 ? 'bg-warning text-dark' : 'bg-danger')}`}>
                                                    {m.stock_quantity > 20 ? 'In Stock' : (m.stock_quantity > 0 ? 'Low Stock' : 'Out of Stock')}
                                                </span>
                                            </td>
                                            <td className="pe-4 text-end">
                                                <Link href={route('inventory.show', m.medication_id)} className="btn btn-sm btn-outline-primary shadow-sm">
                                                    <i className="fas fa-eye me-1"></i> View
                                                </Link>
                                                <button className="btn btn-sm btn-outline-secondary shadow-sm ms-2">
                                                    <i className="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="6" className="text-center py-5 text-muted">No medications found.</td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
