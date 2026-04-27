import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';
import PageHeaderComp from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import DashboardSelect from '@/Components/DashboardSelect';
import { useState, useMemo } from 'react';

export default function LabRequestsIndex({ requests, filters, auth }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [quickFilter, setQuickFilter] = useState(filters.quick_filter || '');

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
                <span className="badge bg-light text-primary fw-bold p-2 border border-light-subtle shadow-sm">LAB-{row.original.request_id}</span>
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
                    <Link href={route('lab.show', row.original.request_id)} className="btn btn-sm btn-light border text-primary rounded-circle p-2 shadow-sm avatar-sm d-flex align-items-center justify-content-center" title="View Details">
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
        <AuthenticatedLayout header={auth.user.role === 'patient' ? 'My Lab Results' : 'Laboratory'}>
            <Head title={auth.user.role === 'patient' ? 'My Labs' : 'Laboratory'} />

            <PageHeaderComp 
                title={auth.user.role === 'patient' ? 'Laboratory Results' : 'Laboratory Registry'}
                breadcrumbs={
                    auth.user.role === 'patient'
                        ? [{ label: 'Dashboard', url: route('dashboard') }, { label: 'Lab Results', active: true }]
                        : [{ label: 'Dashboard', url: route('dashboard') }, { label: 'Lab Requests', active: true }]
                }
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
                />

                <UnifiedToolbar 
                    filters={
                        <div className="d-flex align-items-center gap-2">
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
                        </div>
                    }
                    actions={
                        auth.user.role === 'lab_technician' && (
                            <Link href={route('lab.tests')} className="btn btn-outline-light rounded-pill px-3 py-2 fw-bold small">
                                <i className="fas fa-vials me-1"></i> Test Catalog
                            </Link>
                        )
                    }
                />
            </div>
        </AuthenticatedLayout>
    );
}
