import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';
import ViewToggle from '@/Components/ViewToggle';
import DashboardSelect from '@/Components/DashboardSelect';
import { useState, useMemo } from 'react';

export default function TestsCatalog({ tests, auth, filters, categories }) {
    const [search, setSearch] = useState(filters.search || '');
    const [quickFilter, setQuickFilter] = useState(filters.category || '');
    const [viewMode, setViewMode] = useState(() => localStorage.getItem('lab_tests_view') || 'list'); 
    const [selectedTests, setSelectedTests] = useState([]);

    const isAdmin = auth.user.role === 'admin' || auth.user.role === 'lab_technician';

    const handleSort = (column) => {
        const direction = filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc';
        router.get(route('lab.tests'), { ...filters, sort: column, direction }, { preserveState: true, preserveScroll: true });
    };

    const handleSelectAll = (e) => {
        if (e.target.checked) {
            setSelectedTests(tests.data.map(t => t.test_type_id));
        } else {
            setSelectedTests([]);
        }
    };

    const handleSelectOne = (id) => {
        setSelectedTests(prev => 
            prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]
        );
    };

    const handleToggleStatus = (id) => {
        if (confirm('Change visibility of this test type?')) {
            router.delete(route('lab-tests.destroy', id), {
                preserveScroll: true
            });
        }
    };

    const handleViewChange = (newView) => {
        setViewMode(newView);
        localStorage.setItem('lab_tests_view', newView);
    };

    const handleCategoryChange = (val) => {
        setQuickFilter(val || '');
        router.get(route('lab.tests'), { ...filters, category: val || '' }, { preserveState: true, preserveScroll: true });
    };

    const columns = useMemo(() => {
        const cols = [
            {
                id: 'select',
                header: () => (
                    <div className="form-check ms-1">
                        <input 
                            type="checkbox" 
                            className="form-check-input shadow-none" 
                            onChange={handleSelectAll}
                            checked={selectedTests.length === tests.data.length && tests.data.length > 0}
                        />
                    </div>
                ),
                cell: ({ row }) => (
                    <div className="form-check ms-1">
                        <input 
                            type="checkbox" 
                            className="form-check-input shadow-none" 
                            checked={selectedTests.includes(row.original.test_type_id)}
                            onChange={() => handleSelectOne(row.original.test_type_id)}
                        />
                    </div>
                )
            },
            {
                header: 'Test Name',
                accessorKey: 'test_name',
                enableSorting: true,
                cell: ({ row }) => (
                    <div className="d-flex align-items-center">
                        <div className="avatar-xs bg-pink-50 text-pink-500 rounded-circle d-flex align-items-center justify-content-center me-3 border border-pink-100 shadow-sm" style={{ width: '36px', height: '36px' }}>
                            <i className="fas fa-flask text-xs"></i>
                        </div>
                        <div>
                            <div className="fw-bold text-gray-900">{row.original.test_name}</div>
                            <div className="extra-small text-muted font-bold text-uppercase opacity-75">{row.original.category}</div>
                        </div>
                    </div>
                )
            },
            {
                header: 'Description',
                accessorKey: 'description',
                cell: ({ row }) => <span className="small text-muted line-clamp-1 fw-medium">{row.original.description || 'Standard diagnostic protocol.'}</span>
            },
            {
                header: 'Price',
                accessorKey: 'price',
                enableSorting: true,
                cell: ({ row }) => (
                    <div className="fw-bold text-gray-900">
                        <span className="text-muted extra-small me-1">Ksh.</span>
                        {new Intl.NumberFormat('en-KE').format(row.original.price || 0)}
                    </div>
                )
            },
            {
                header: 'Status',
                accessorKey: 'is_active',
                enableSorting: true,
                cell: ({ row }) => (
                    <span className={`badge rounded-pill px-3 py-2 fw-bold border ${row.original.is_active ? 'bg-success-subtle text-success border-success-subtle' : 'bg-secondary-subtle text-secondary border-secondary-subtle'}`} style={{ fontSize: '0.7rem' }}>
                        <i className={`fas fa-${row.original.is_active ? 'check-circle' : 'times-circle'} me-1`}></i>
                        {row.original.is_active ? 'Active' : 'Inactive'}
                    </span>
                )
            }
        ];

        if (isAdmin) {
            cols.push({
                header: 'Actions',
                id: 'actions',
                cell: ({ row }) => (
                    <div className="dropdown">
                        <button className="btn btn-sm btn-light border-0 rounded-circle shadow-none p-2" data-bs-toggle="dropdown" style={{ width: '32px', height: '32px' }}>
                            <i className="fas fa-ellipsis-v text-muted"></i>
                        </button>
                        <ul className="dropdown-menu dropdown-menu-end shadow-2xl border-0 rounded-2xl py-2 mt-2 animate-in fade-in zoom-in-95 duration-200" style={{ borderRadius: '0' }}>
                            <li>
                                <Link className="dropdown-item py-2 px-3 d-flex align-items-center gap-3" href={route('lab-tests.edit', row.original.test_type_id)}>
                                    <div className="bg-primary-subtle text-primary rounded-lg p-2 d-flex align-items-center justify-content-center" style={{width: '32px', height: '32px'}}>
                                        <i className="fas fa-edit text-xs"></i>
                                    </div>
                                    <span className="fw-bold text-gray-700">Edit Protocol</span>
                                </Link>
                            </li>
                            <li>
                                <button 
                                    onClick={() => handleToggleStatus(row.original.test_type_id)}
                                    className="dropdown-item py-2 px-3 d-flex align-items-center gap-3"
                                >
                                    <div className={`bg-${row.original.is_active ? 'danger' : 'success'}-subtle text-${row.original.is_active ? 'danger' : 'success'} rounded-lg p-2 d-flex align-items-center justify-content-center`} style={{width: '32px', height: '32px'}}>
                                        <i className={`fas fa-${row.original.is_active ? 'eye-slash' : 'eye'} text-xs`}></i>
                                    </div>
                                    <span className={`fw-bold text-${row.original.is_active ? 'danger' : 'success'}`}>
                                        {row.original.is_active ? 'Deactivate' : 'Activate'}
                                    </span>
                                </button>
                            </li>
                            <li><hr className="dropdown-divider opacity-10 mx-3" /></li>
                            <li>
                                <Link className="dropdown-item py-2 px-3 d-flex align-items-center gap-3" href={route('lab.tests')}>
                                    <div className="bg-info-subtle text-info rounded-lg p-2 d-flex align-items-center justify-content-center" style={{width: '32px', height: '32px'}}>
                                        <i className="fas fa-history text-xs"></i>
                                    </div>
                                    <span className="fw-bold text-gray-700">Audit Logs</span>
                                </Link>
                            </li>
                        </ul>
                    </div>
                )
            });
        }

        return cols;
    }, [tests.data, selectedTests, isAdmin]);

    const handleSearch = (val) => {
        setSearch(val);
        router.get(route('lab.tests'), { ...filters, search: val }, { preserveState: true, preserveScroll: true });
    };

    return (
        <AuthenticatedLayout
            header="Laboratory Tests"
            toolbarFilters={
                <div className="d-flex align-items-center gap-2">
                    <DashboardSelect 
                        options={categories.map(c => ({ label: c, value: c }))}
                        value={quickFilter}
                        onChange={handleCategoryChange}
                        placeholder="Category..."
                        theme="dark"
                        dropup={true}
                        style={{ width: '180px' }}
                    />
                </div>
            }
            toolbarActions={
                <div className="d-flex align-items-center gap-2">
                    <ViewToggle view={viewMode} setView={handleViewChange} />
                    {isAdmin && (
                        <Link href={route('lab-tests.create')} className="btn btn-primary rounded-pill px-4 py-2 fw-bold small shadow-sm">
                            <i className="fas fa-plus me-1"></i> New Protocol
                        </Link>
                    )}
                </div>
            }
        >
            <Head title="Lab Tests" />

            <PageHeader 
                title="Laboratory Test Catalog"
                breadcrumbs={[{ label: 'Lab', url: route('lab.index') }, { label: 'Tests', active: true }]}
            />

            <div className="container-fluid px-0">
                <DashboardSearch 
                    placeholder="Search test catalog by name or description..."
                    value={search}
                    onChange={setSearch}
                    onSubmit={handleSearch}
                    onFilterChange={handleCategoryChange}
                    filters={categories.map(c => ({ label: c, value: c }))}
                />
                
                {selectedTests.length > 0 && (
                    <div className="bulk-actions-bar animate-in fade-in slide-in-from-top-2 bg-primary-gradient text-white p-3 rounded-2xl mb-4 d-flex justify-content-between align-items-center shadow-lg border border-white border-opacity-10">
                        <div className="d-flex align-items-center gap-4 ps-2">
                            <div className="bg-white bg-opacity-20 rounded-xl p-2 d-flex align-items-center justify-content-center" style={{ width: '48px', height: '48px' }}>
                                <i className="fas fa-check-double fs-4 text-white"></i>
                            </div>
                            <div>
                                <span className="fw-bold fs-5">{selectedTests.length} tests selected</span>
                                <div className="extra-small opacity-75 font-bold text-uppercase tracking-wider">Bulk management active</div>
                            </div>
                        </div>
                        <div className="d-flex gap-2">
                            <button className="btn btn-white btn-sm rounded-pill px-4 py-2 fw-bold shadow-sm transition-all hover-translate-up">
                                <i className="fas fa-print me-2"></i> Print Batch
                            </button>
                            <button className="btn btn-white btn-sm rounded-pill px-4 py-2 fw-bold shadow-sm transition-all hover-translate-up">
                                <i className="fas fa-tag me-2"></i> Update Prices
                            </button>
                            <button className="btn btn-danger btn-sm rounded-pill px-4 py-2 fw-bold shadow-sm transition-all hover-translate-up border border-white border-opacity-20">
                                <i className="fas fa-trash-alt me-2"></i> Remove
                            </button>
                        </div>
                    </div>
                )}

                {viewMode === 'list' ? (
                    <DashboardTable 
                        columns={columns}
                        data={tests.data}
                        pagination={tests}
                        onSort={handleSort}
                        sortColumn={filters.sort}
                        sortDirection={filters.direction}
                        emptyMessage="No lab tests match your search criteria."
                    />
                ) : (
                    <div className="row g-4">
                        {tests.data.length > 0 ? (
                            <>
                                {tests.data.map((test) => (
                                    <div key={test.test_type_id} className="col-md-6 col-lg-4">
                                        <div className={`card h-100 shadow-sm border-0 rounded-2xl overflow-hidden hover-lift transition-all bg-white shadow-hover ${selectedTests.includes(test.test_type_id) ? 'ring-2 ring-primary ring-opacity-50' : ''}`}>
                                            <div className="card-body p-4 position-relative">
                                                <div className="form-check position-absolute top-0 end-0 m-4">
                                                    <input 
                                                        type="checkbox" 
                                                        className="form-check-input shadow-none border-gray-300" 
                                                        checked={selectedTests.includes(test.test_type_id)}
                                                        onChange={() => handleSelectOne(test.test_type_id)}
                                                    />
                                                </div>
                                                <div className="d-flex align-items-center mb-4">
                                                    <div className="flex-shrink-0 avatar-sm bg-pink-50 text-pink-500 rounded-2xl d-flex align-items-center justify-content-center me-3 shadow-inner border border-pink-100" style={{ width: '52px', height: '52px' }}>
                                                        <i className="fas fa-flask fa-lg"></i>
                                                    </div>
                                                    <div>
                                                        <h6 className="mb-0 fw-bold text-gray-900">{test.test_name}</h6>
                                                        <span className="badge bg-light text-muted rounded-pill extra-small px-2 border mt-1">{test.category || 'General'}</span>
                                                    </div>
                                                </div>
                                                <div className="bg-gray-50 p-3 rounded-xl mb-4 border border-light">
                                                    <p className="small text-gray-600 mb-0 line-clamp-2 italic">"{test.description || 'Standard laboratory diagnostic protocol.'}"</p>
                                                </div>
                                                <div className="d-flex justify-content-between align-items-center mt-auto pt-2">
                                                    <div className="d-flex flex-column">
                                                        <span className="extra-small text-muted font-bold text-uppercase tracking-wider">Protocol Fee</span>
                                                        <span className="fw-bold text-gray-900 fs-5">Ksh. {new Intl.NumberFormat('en-KE').format(test.price || 0)}</span>
                                                    </div>
                                                    <Link href={route('lab-tests.edit', test.test_type_id)} className="btn btn-light border fw-bold rounded-pill px-4">Edit</Link>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                                
                                {/* Pagination for Grid View */}
                                <div className="col-12 mt-4">
                                    <DashboardTable 
                                        data={[]} 
                                        columns={[]} 
                                        pagination={tests}
                                        className="bg-transparent shadow-none"
                                    />
                                </div>
                            </>
                        ) : (
                            <div className="col-12 text-center py-16 bg-white rounded-3xl border border-dashed">
                                <i className="fas fa-search-minus text-gray-200 text-5xl mb-4"></i>
                                <h4 className="text-gray-400 fw-bold">No results found</h4>
                                <p className="text-gray-300">Try adjusting your search filters.</p>
                            </div>
                        )}
                    </div>
                )}

            </div>
            
            <style>{`
                .extra-small { font-size: 0.7rem; }
                .hover-lift:hover { transform: translateY(-5px); }
                .shadow-hover:hover { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important; }
                .bulk-actions-bar {
                    background: linear-gradient(135deg, #e91e63 0%, #d81b60 100%);
                }
                .btn-white {
                    background: white;
                    color: #e91e63;
                    border: none;
                }
                .btn-white:hover {
                    background: #f8f9fa;
                    color: #d81b60;
                }
                .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15); }
                .ring-primary { --tw-ring-color: #e91e63; }
                .ring-2 { box-shadow: 0 0 0 2px var(--tw-ring-color); }
                .hover-translate-up:hover { transform: translateY(-2px); }
                .line-clamp-2 {
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                    overflow: hidden;
                }
            `}</style>
        </AuthenticatedLayout>
    );
}
