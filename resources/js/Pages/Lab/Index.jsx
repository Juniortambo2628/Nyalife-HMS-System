import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';
import PageHeaderComp from '@/Components/PageHeader';
import { useState, useMemo } from 'react';

export default function LabRequestsIndex({ requests, filters, auth }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');

    const applyFilters = (searchValue, statusValue = status) => {
        router.get(route('lab.index'), { search: searchValue, status: statusValue }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleStatusChange = (e) => {
        const newStatus = e.target.value;
        setStatus(newStatus);
        applyFilters(search, newStatus);
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
            cell: ({ row }) => <span className="fw-bold">LAB-{row.original.request_id}</span>
        },
        {
            header: 'Date',
            accessorKey: 'request_date',
            cell: ({ row }) => <span>{row.original.request_date}</span>
        },
        {
            header: 'Patient',
            accessorKey: 'patient_id',
            cell: ({ row }) => (
                <div>
                    <div className="fw-semibold">
                        {row.original.patient?.user?.first_name} {row.original.patient?.user?.last_name}
                    </div>
                    <small className="text-muted">ID: {row.original.patient_id}</small>
                </div>
            )
        },
        {
            header: 'Test Type',
            accessorKey: 'test_type.test_name',
            cell: ({ row }) => <span>{row.original.test_type?.test_name || 'N/A'}</span>
        },
        {
            header: 'Priority',
            accessorKey: 'priority',
            cell: ({ row }) => {
                const p = row.original.priority;
                const badgeClass = p === 'urgent' ? 'bg-danger' : (p === 'stat' ? 'bg-dark' : 'bg-info');
                return (
                    <span className={`badge ${badgeClass}`}>
                        {p.toUpperCase()}
                    </span>
                );
            }
        },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: ({ row }) => {
                const s = row.original.status;
                const badgeClass = s === 'completed' ? 'bg-success' : (s === 'processing' ? 'bg-primary' : 'bg-warning text-dark');
                return (
                    <span className={`badge ${badgeClass}`}>
                        {s.toUpperCase()}
                    </span>
                );
            }
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => (
                <div className="d-flex justify-content-end gap-2">
                    <Link href={route('lab.show', row.original.request_id)} className="btn btn-sm btn-outline-primary shadow-sm" title="View Details">
                        <i className="fas fa-eye"></i>
                    </Link>
                    {auth.user.role === 'lab_technician' && row.original.status === 'pending' && (
                        <button 
                            onClick={() => handleProcess(row.original.request_id)}
                            className="btn btn-sm btn-info shadow-sm"
                            title="Start Processing"
                        >
                            <i className="fas fa-flask"></i>
                        </button>
                    )}
                </div>
            )
        }
    ], []);

    return (
        <AuthenticatedLayout
            user={auth.user}
        >
            <Head title="Laboratory" />

            <PageHeaderComp 
                title="Laboratory Requests"
                breadcrumbs={[
                    { label: 'Dashboard', url: route('dashboard') },
                    { label: 'Lab Requests', active: true }
                ]}
                actions={
                    <div className="d-flex align-items-center gap-2">
                        <select 
                            className="form-select rounded-pill shadow-sm" 
                            style={{width: 'auto'}}
                            value={status} 
                            onChange={handleStatusChange}
                        >
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                }
            />

            <div className="container-fluid lab-page px-0 h-auto">
                <DashboardSearch 
                    placeholder="Search by patient name or request ID..." 
                    value={search}
                    onChange={setSearch}
                    onSubmit={applyFilters}
                />

                {/* Table */}
                <DashboardTable 
                    columns={columns}
                    data={requests.data}
                    pagination={requests}
                    emptyMessage="No lab requests found."
                />
            </div>
        </AuthenticatedLayout>
    );
}
