import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import TableActions from '@/Components/TableActions';
import GridCardActions from '@/Components/GridCardActions';
import StatusBadge from '@/Components/StatusBadge';
import { TableCellPrimary, TableCellStack } from '@/Components/TableCells';
import { formatNumber } from '@/Utils/formatUtils';
import { useState, useMemo, useEffect } from 'react';

export default function TestsCatalog({ tests, auth, filters, categories }) {
    const [search, setSearch] = useState(filters.search || '');
    const [quickFilter, setQuickFilter] = useState(filters.category || '');
    const [viewMode, setViewMode] = useState(() => localStorage.getItem('lab_tests_view') || 'list'); 
    const [selectedTests, setSelectedTests] = useState([]);

    useEffect(() => {
        const handleClear = () => setSelectedTests([]);
        window.addEventListener('toolbar-clear-selection', handleClear);
        return () => window.removeEventListener('toolbar-clear-selection', handleClear);
    }, []);

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
                        <div className="avatar-36 bg-pink-50 text-pink-500 rounded-circle d-flex align-items-center justify-content-center me-3 border border-pink-100 shadow-sm">
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
                        {formatNumber(row.original.price || 0)}
                    </div>
                )
            },
            {
                header: 'Status',
                accessorKey: 'is_active',
                enableSorting: true,
                cell: ({ row }) => (
                    <StatusBadge status={row.original.is_active ? 'active' : 'inactive'} />
                )
            }
        ];

        if (isAdmin) {
            cols.push({
                header: 'Actions',
                id: 'actions',
                cell: ({ row }) => (
                    <TableActions actions={[
                        {
                            icon: 'fa-edit',
                            label: 'Edit protocol',
                            href: route('lab-tests.edit', row.original.test_type_id),
                        },
                        {
                            icon: row.original.is_active ? 'fa-eye-slash' : 'fa-eye',
                            label: row.original.is_active ? 'Deactivate' : 'Activate',
                            onClick: () => handleToggleStatus(row.original.test_type_id),
                            color: row.original.is_active ? 'danger' : 'success',
                        },
                        { isDivider: true },
                        {
                            icon: 'fa-history',
                            label: 'Audit logs',
                            href: route('lab.tests'),
                            color: 'info',
                        },
                    ]} />
                ),
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
            headerTitle="Laboratory Test Catalog"
            breadcrumbs={[{ label: 'Lab', url: route('lab.index') }, { label: 'Tests', active: true }]}
        >
            <Head title="Lab Tests" />

            <UnifiedToolbar
                viewMode={viewMode}
                onViewModeChange={handleViewChange}
                filterGroups={[
                    {
                        id: 'category',
                        label: 'Category',
                        emptyLabel: 'All categories',
                        value: quickFilter,
                        onChange: handleCategoryChange,
                        options: categories.map((c) => ({ label: c, value: c })),
                    },
                ]}
                actions={[
                    isAdmin && {
                        label: 'NEW PROTOCOL',
                        icon: 'fa-plus',
                        href: route('lab-tests.create'),
                    },
                    {
                        label: 'LAB REQUESTS',
                        icon: 'fa-vial',
                        href: route('lab.index'),
                        color: 'gray',
                    },
                ].filter(Boolean)}
                bulkActions={[
                    { label: 'PRINT BATCH', icon: 'fa-print', onClick: () => {} },
                    { label: 'UPDATE PRICES', icon: 'fa-tag', onClick: () => {} },
                    { label: 'REMOVE SELECTED', icon: 'fa-trash-alt', onClick: () => {}, color: 'danger' },
                ]}
                selectionCount={selectedTests.length}
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
                
                {viewMode === 'list' ? (
                    <RegistryTablePanel
                        title="Test catalog"
                        icon="fa-vials"
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
                                                    <div className="flex-shrink-0 avatar-sm bg-pink-50 text-pink-500 rounded-2xl d-flex align-items-center justify-content-center me-3 shadow-inner border border-pink-100 nyl-test-type-icon">
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
                                                        <span className="fw-bold text-gray-900 fs-5">Ksh. {formatNumber(test.price || 0)}</span>
                                                    </div>
                                                    <GridCardActions actions={[
                                                        { icon: 'fa-edit', label: 'Edit protocol', href: route('lab-tests.edit', test.test_type_id) },
                                                    ]} className="border-0 pt-2" />
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
                .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15); }
                .ring-primary { --tw-ring-color: #e91e63; }
                .ring-2 { box-shadow: 0 0 0 2px var(--tw-ring-color); }
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
