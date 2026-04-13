import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import DashboardSearch from '@/Components/DashboardSearch';
import { useState, useMemo } from 'react';

export default function Tests({ tests }) {
    const [search, setSearch] = useState('');

    const filteredTests = useMemo(() => {
        if (!search) return tests;
        const q = search.toLowerCase();
        return tests.filter(t => 
            t.test_name.toLowerCase().includes(q) || 
            (t.description && t.description.toLowerCase().includes(q))
        );
    }, [search, tests]);

    return (
        <AuthenticatedLayout
            header="Laboratory Tests"
        >
            <Head title="Lab Tests" />

            <PageHeader 
                title="Laboratory Test Catalog"
                breadcrumbs={[{ label: 'Lab', url: route('lab.index') }, { label: 'Tests', active: true }]}
                actions={
                    <Link href={route('lab-tests.create')} className="btn btn-primary rounded-pill px-4 font-bold shadow-sm">
                        <i className="fas fa-plus me-2"></i>New Test Type
                    </Link>
                }
            />

            <div className="container-fluid px-0">
                <DashboardSearch 
                    placeholder="Search test catalog by name or description..."
                    value={search}
                    onChange={setSearch}
                />
                
                <div className="row g-4 mt-1">
                    {tests.length > 0 ? (
                        tests.map((test) => (
                            <div key={test.test_type_id} className="col-md-4">
                                <div className="card h-100 shadow-sm border-0 rounded-xl overflow-hidden hover-lift">
                                    <div className="card-body p-4">
                                        <div className="d-flex align-items-center mb-3">
                                            <div className="flex-shrink-0 avatar-sm bg-pink-100 text-pink-500 rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i className="fas fa-flask"></i>
                                            </div>
                                            <h6 className="mb-0 fw-bold">{test.test_name}</h6>
                                        </div>
                                        <p className="small text-muted mb-3">{test.description || 'Standard laboratory diagnostic protocol.'}</p>
                                        <div className="d-flex justify-content-between align-items-center mt-auto">
                                            <span className="fw-bold text-gray-900">Ksh. {test.price || '0.00'}</span>
                                            <span className="badge bg-light text-dark rounded-pill px-2">{test.category || 'General'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="col-12 text-center py-5">
                            <p className="text-muted">No lab tests available at this time.</p>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
