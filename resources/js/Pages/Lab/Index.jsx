import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';
import StatusBadge from '@/Components/StatusBadge';
import DashboardSelect from '@/Components/DashboardSelect';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { useState, useMemo, useEffect } from 'react';

export default function LabRequestsIndex({ requests, filters, auth }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [quickFilter, setQuickFilter] = useState(filters.quick_filter || '');
    const [selectedIds, setSelectedIds] = useState([]);

    useEffect(() => {
        const handleClear = () => setSelectedIds([]);
        window.addEventListener('toolbar-clear-selection', handleClear);
        return () => window.removeEventListener('toolbar-clear-selection', handleClear);
    }, []);

    const applyFilters = (searchValue, statusValue = status, quickFilterValue = quickFilter) => {
        router.get(route('lab.index'), { search: searchValue, status: statusValue, quick_filter: quickFilterValue }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleStatusChange = (val) => {
        setStatus(val || '');
        applyFilters(search, val || '', quickFilter);
    };

    const handleQuickFilterChange = (val) => {
        setQuickFilter(val);
        applyFilters(search, status, val);
    };

    const handleProcess = (id) => {
        if (confirm('Start processing this lab request?')) {
            router.post(route('lab.update-status', id), {
                status: 'processing'
            }, {
                preserveScroll: true
            });
        }
    };

    const columns = useMemo(() => [
        {
            header: 'Req ID',
            accessorKey: 'request_id',
            cell: ({ row }) => (
                <span className="badge bg-light text-pink-500 fw-extrabold extra-small tracking-widest p-2 border border-pink-100 shadow-sm">LAB-{row.original.request_id}</span>
            )
        },
        {
            header: 'Patient',
            accessorKey: 'patient_id',
            cell: ({ row }) => (
                <div>
                    <div className="fw-bold text-gray-900">
                        {row.original.patient?.user?.first_name} {row.original.patient?.user?.last_name}
                    </div>
                    <div className="extra-small text-muted fw-bold text-uppercase opacity-75">ID: PAT-{row.original.patient_id}</div>
                </div>
            )
        },
        {
            header: 'Test Type',
            accessorKey: 'test_type.test_name',
            cell: ({ row }) => <span className="fw-semibold text-gray-700">{row.original.test_type?.test_name || 'N/A'}</span>
        },
        {
            header: 'Priority',
            accessorKey: 'priority',
            cell: ({ row }) => {
                const p = (row.original.priority || 'normal').toLowerCase();
                const colors = {
                    urgent: 'bg-danger-subtle text-danger border-danger-subtle',
                    stat: 'bg-dark text-white border-dark',
                    emergency: 'bg-danger text-white border-danger animate-pulse-custom',
                    high: 'bg-orange-subtle text-orange-600 border-orange-200',
                    normal: 'bg-info-subtle text-info border-info-subtle'
                };
                return (
                    <span className={`badge rounded-pill px-3 py-2 fw-bold border nyl-badge-sm ${colors[p] || colors.normal}`}>
                        <i className="fas fa-bolt me-1"></i>{p.toUpperCase()}
                    </span>
                );
            }
        },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: ({ row }) => <StatusBadge status={row.original.status} />
        },
        {
            header: 'Requested By',
            accessorKey: 'doctor.user.last_name',
            cell: ({ row }) => (
                <div className="small text-muted fw-medium">
                    Dr. {row.original.doctor?.user?.last_name || 'System'}
                </div>
            )
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => (
                <div className="d-flex justify-content-end gap-2">
                    <Link href={route('lab.show', row.original.request_id)} className="btn btn-sm btn-light border text-pink-500 rounded-circle p-2 shadow-sm avatar-sm d-flex align-items-center justify-content-center" title="View Details">
                        <i className="fas fa-eye extra-small"></i>
                    </Link>
                    {auth.user.role === 'lab_technician' && row.original.status === 'pending' && (
                        <button 
                            onClick={() => handleProcess(row.original.request_id)}
                            className="btn btn-sm btn-light border text-info rounded-circle p-2 shadow-sm avatar-sm d-flex align-items-center justify-content-center"
                            title="Start Processing"
                        >
                            <i className="fas fa-vial extra-small"></i>
                        </button>
                    )}
                </div>
            )
        }
    ], []);

    return (
        <AuthenticatedLayout 
            headerTitle={auth.user.role === 'patient' ? 'Laboratory Results' : 'Laboratory Registry'}
            breadcrumbs={
                auth.user.role === 'patient'
                    ? [{ label: 'Dashboard', url: route('dashboard') }, { label: 'Lab Results', active: true }]
                    : [{ label: 'Dashboard', url: route('dashboard') }, { label: 'Lab Requests', active: true }]
            }
        >
            <Head title={auth.user.role === 'patient' ? 'My Labs' : 'Laboratory'} />

            <UnifiedToolbar 
                viewOptions={[
                    { label: 'LIST VIEW', icon: 'fa-list-ul', onClick: () => {} },
                    { label: 'GRID VIEW', icon: 'fa-th-large', onClick: () => {} }
                ]}
                filters={
                    <>
                        <DashboardSelect 
                            options={[
                                { label: 'Pending', value: 'pending' },
                                { label: 'Processing', value: 'processing' },
                                { label: 'Completed', value: 'completed' },
                                { label: 'Cancelled', value: 'cancelled' },
                            ]}
                            value={status}
                            onChange={handleStatusChange}
                            placeholder="Status..."
                            theme="dark"
                            dropup={true}
                        />
                        <DashboardSelect 
                            options={[
                                { label: 'Urgent', value: 'urgent' },
                                { label: 'Normal', value: 'normal' },
                            ]}
                            value={quickFilter}
                            onChange={handleQuickFilterChange}
                            placeholder="Priority..."
                            theme="dark"
                            dropup={true}
                        />
                    </>
                }
                actions={[
                    auth.user.role === 'lab_technician' && { 
                        label: 'TEST CATALOG', 
                        icon: 'fa-vials', 
                        href: route('lab.tests') 
                    }
                ]}
                bulkActions={[
                    { label: 'MARK COMPLETE', icon: 'fa-check-circle', onClick: () => { if(confirm(`Complete ${selectedIds.length} requests?`)) { selectedIds.forEach(id => router.post(route('lab.update-status', id), { status: 'completed' }, { preserveScroll: true })); setSelectedIds([]); } } },
                    { label: 'CANCEL SELECTED', icon: 'fa-times-circle', onClick: () => console.log('Cancel', selectedIds), color: 'danger' }
                ]}
                selectionCount={selectedIds.length}
            />

            <div className="px-0">
                <DashboardSearch 
                    placeholder="Search by patient name or request ID..." 
                    value={search}
                    onChange={setSearch}
                    onSubmit={(val) => applyFilters(val, status, quickFilter)}
                    onFilterChange={handleQuickFilterChange}
                    filters={[
                        { label: 'Pending', value: 'pending' },
                        { label: 'Completed', value: 'completed' },
                        { label: 'Urgent', value: 'urgent' },
                    ]}
                />

                <DashboardTable 
                    columns={columns}
                    data={requests.data}
                    pagination={requests}
                    emptyMessage="No lab requests found matching your search."
                    selectable={true}
                    selectedIds={selectedIds}
                    onSelectionChange={setSelectedIds}
                    idField="request_id"
                />

            </div>
        </AuthenticatedLayout>
    );
}
