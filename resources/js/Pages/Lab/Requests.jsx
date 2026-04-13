import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';
import { useState, useMemo } from 'react';

export default function Requests({ requests, filters }) {
    const handleProcess = (id) => {
        if (confirm('Start processing this lab request?')) {
            router.post(route('lab.update-status', id), {
                status: 'processing'
            }, {
                preserveScroll: true
            });
        }
    };

    return (
        <AuthenticatedLayout
            header="Lab Requests"
        >
            <Head title="Lab Requests" />

            <PageHeader 
                title="Laboratory Requests"
                breadcrumbs={[{ label: 'Lab', url: route('lab.index') }, { label: 'Requests', active: true }]}
                actions={
                    <Link href={route('lab.tests')} className="btn btn-outline-primary rounded-pill px-4 font-bold shadow-sm">
                        <i className="fas fa-list me-2"></i>Test Catalog
                    </Link>
                }
            />

    const [search, setSearch] = useState('');
    
    const columns = useMemo(() => [
        {
            header: 'Req ID',
            accessorKey: 'request_id',
            cell: ({ row }) => <span className="fw-bold">LAB-{row.original.request_id}</span>
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
            cell: ({ row }) => (
                <span className="badge bg-soft-info text-info rounded-pill px-3">
                    {row.original.test_type?.test_name || 'Standard Test'}
                </span>
            )
        },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: ({ row }) => {
                const s = row.original.status;
                const badgeClass = s === 'completed' ? 'bg-success' : 'bg-warning text-dark';
                return <span className={`badge rounded-pill px-3 py-1 ${badgeClass}`}>{s.toUpperCase()}</span>;
            }
        },
        {
            header: 'Date',
            accessorKey: 'created_at',
            cell: ({ row }) => <span>{new Date(row.original.created_at).toLocaleDateString()}</span>
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => (
                <div className="d-flex justify-content-end gap-2">
                    {row.original.status === 'pending' ? (
                        <button 
                            onClick={() => handleProcess(row.original.request_id)}
                            className="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"
                        >
                            Process
                        </button>
                    ) : (
                        <Link 
                            href={route('lab.show', row.original.request_id)}
                            className="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm"
                        >
                            View
                        </Link>
                    )}
                </div>
            )
        }
    ], []);

    const filteredRequests = useMemo(() => {
        if (!search) return requests.data;
        const q = search.toLowerCase();
        return requests.data.filter(r => 
            String(r.request_id).includes(q) || 
            `${r.patient?.user?.first_name} ${r.patient?.user?.last_name}`.toLowerCase().includes(q)
        );
    }, [search, requests.data]);

    return (
        <AuthenticatedLayout
            header="Lab Requests"
        >
            <Head title="Lab Requests" />

            <PageHeader 
                title="Laboratory Requests"
                breadcrumbs={[{ label: 'Lab', url: route('lab.index') }, { label: 'Requests', active: true }]}
                actions={
                    <Link href={route('lab.tests')} className="btn btn-outline-primary rounded-pill px-4 font-bold shadow-sm">
                        <i className="fas fa-list me-2"></i>Test Catalog
                    </Link>
                }
            />

            <div className="container-fluid px-0">
                <DashboardSearch 
                    placeholder="Search requests by patient or ID..."
                    value={search}
                    onChange={setSearch}
                />
                
                <DashboardTable 
                    columns={columns}
                    data={filteredRequests}
                    emptyMessage="No laboratory requests found."
                />
            </div>
        </AuthenticatedLayout>
    );
}
        </AuthenticatedLayout>
    );
}
