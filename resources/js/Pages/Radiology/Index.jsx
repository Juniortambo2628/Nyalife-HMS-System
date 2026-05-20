import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';
import StatusBadge from '@/Components/StatusBadge';
import DashboardSelect from '@/Components/DashboardSelect';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { useState, useMemo, useEffect } from 'react';

export default function RadiologyRequestsIndex({ requests, filters, auth }) {
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
        router.get(route('radiology.index'), { search: searchValue, status: statusValue, quick_filter: quickFilterValue }, {
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
        if (confirm('Start processing this radiology request?')) {
            router.post(route('radiology.update-status', id), {
                status: 'processing'
            }, {
                preserveScroll: true
            });
        }
    };

    const handleDelete = (id) => {
        if (confirm('Are you sure you want to remove this pending radiology request?')) {
            router.delete(route('radiology.destroy', id), {
                preserveScroll: true
            });
        }
    };

    const columns = useMemo(() => [
        {
            header: 'Order Ref',
            accessorKey: 'request_number',
            cell: ({ row }) => (
                <span className="badge bg-light text-pink-500 fw-extrabold extra-small tracking-widest p-2 border border-pink-100 shadow-sm">
                    {row.original.request_number}
                </span>
            )
        },
        {
            header: 'Patient',
            accessorKey: 'patient',
            cell: ({ row }) => (
                <div>
                    <div className="fw-bold text-gray-900">
                        {row.original.patient?.user?.first_name} {row.original.patient?.user?.last_name}
                    </div>
                    <div className="extra-small text-muted fw-bold text-uppercase opacity-75">
                        PATID: PAT-{row.original.patient_id}
                    </div>
                </div>
            )
        },
        {
            header: 'Scan / Imaging Type',
            accessorKey: 'scan_type',
            cell: ({ row }) => (
                <div>
                    <span className="fw-semibold text-gray-700">{row.original.scan_type}</span>
                    {row.original.clinical_indication && (
                        <div className="extra-small text-muted text-truncate" style={{ maxWidth: '250px' }}>
                            {row.original.clinical_indication}
                        </div>
                    )}
                </div>
            )
        },
        {
            header: 'Priority',
            accessorKey: 'priority',
            cell: ({ row }) => {
                const p = (row.original.priority || 'routine').toLowerCase();
                const colors = {
                    emergency: 'bg-danger text-white border-danger animate-pulse-custom',
                    urgent: 'bg-orange-subtle text-orange-600 border-orange-200',
                    routine: 'bg-info-subtle text-info border-info-subtle'
                };
                return (
                    <span className={`badge rounded-pill px-3 py-2 fw-bold border nyl-badge-sm ${colors[p] || colors.routine}`}>
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
            header: 'Ordered By',
            accessorKey: 'requestedBy',
            cell: ({ row }) => (
                <div className="small text-muted fw-medium">
                    Dr. {row.original.requestedBy?.last_name || 'System'}
                </div>
            )
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => (
                <div className="d-flex justify-content-end gap-2">
                    <Link href={route('radiology.show', row.original.request_id)} className="btn btn-sm btn-light border text-pink-500 rounded-circle p-2 shadow-sm avatar-sm d-flex align-items-center justify-content-center" title="View & Process Results">
                        <i className="fas fa-eye extra-small"></i>
                    </Link>
                    {(auth.user.role === 'lab_technician' || auth.user.role === 'admin') && row.original.status === 'pending' && (
                        <button 
                            onClick={() => handleProcess(row.original.request_id)}
                            className="btn btn-sm btn-light border text-info rounded-circle p-2 shadow-sm avatar-sm d-flex align-items-center justify-content-center"
                            title="Start Scan Processing"
                        >
                            <i className="fas fa-play extra-small"></i>
                        </button>
                    )}
                    {(auth.user.role === 'admin' || auth.user.role === 'doctor') && row.original.status === 'pending' && (
                        <button 
                            onClick={() => handleDelete(row.original.request_id)}
                            className="btn btn-sm btn-light border text-danger rounded-circle p-2 shadow-sm avatar-sm d-flex align-items-center justify-content-center"
                            title="Delete Request"
                        >
                            <i className="fas fa-trash extra-small"></i>
                        </button>
                    )}
                </div>
            )
        }
    ], [auth.user.role]);

    return (
        <AuthenticatedLayout 
            headerTitle={auth.user.role === 'patient' ? 'My Radiology & Imaging' : 'Radiology & Imaging Registry'}
            breadcrumbs={
                auth.user.role === 'patient'
                    ? [{ label: 'Dashboard', url: route('dashboard') }, { label: 'My Scans', active: true }]
                    : [{ label: 'Dashboard', url: route('dashboard') }, { label: 'Radiology Requests', active: true }]
            }
        >
            <Head title={auth.user.role === 'patient' ? 'My Radiology Scans' : 'Radiology & Imaging'} />

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
                                { label: 'Pending Verification', value: 'pending_verification' },
                                { label: 'Verified', value: 'verified' },
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
                                { label: 'Routine', value: 'routine' },
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
                    (auth.user.role === 'doctor' || auth.user.role === 'admin') && { 
                        label: 'ORDER SCAN', 
                        icon: 'fa-plus-circle', 
                        href: route('radiology.create'),
                        color: 'pink'
                    }
                ].filter(Boolean)}
                bulkActions={[
                    { 
                        label: 'CANCEL SELECTED', 
                        icon: 'fa-times-circle', 
                        onClick: () => { 
                            if(confirm(`Cancel ${selectedIds.length} requests?`)) { 
                                selectedIds.forEach(id => router.post(route('radiology.update-status', id), { status: 'cancelled' }, { preserveScroll: true })); 
                                setSelectedIds([]); 
                            } 
                        }, 
                        color: 'danger' 
                    }
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
                        { label: 'Pending Verification', value: 'pending_verification' },
                        { label: 'Verified', value: 'verified' },
                        { label: 'Urgent', value: 'urgent' },
                    ]}
                />

                <DashboardTable 
                    columns={columns}
                    data={requests.data}
                    pagination={requests}
                    emptyMessage="No radiology requests found matching your search."
                    selectable={auth.user.role !== 'patient'}
                    selectedIds={selectedIds}
                    onSelectionChange={setSelectedIds}
                    idField="request_id"
                />

            </div>
        </AuthenticatedLayout>
    );
}
