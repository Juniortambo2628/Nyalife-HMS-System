import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import PageHeader from '@/Components/PageHeader';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';

export default function Index({ invoices, filters, auth }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');

    const applyFilters = (searchValue, statusValue = status) => {
        // Apply filters to the invoice list
        router.get(route('invoices.index'), { search: searchValue, status: statusValue }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleStatusChange = (e) => {
        const newStatus = e.target.value;
        setStatus(newStatus);
        applyFilters(search, newStatus);
    };

    return (
        <AuthenticatedLayout
            header="Billing & Invoices"
        >
            <Head title="Billing" />

            <PageHeader 
                title="Financial Registry"
                breadcrumbs={[{ label: 'Billing', active: true }, { label: 'Invoices', active: true }]}
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
                            <option value="paid">Paid</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="overdue">Overdue</option>
                        </select>
                        {auth.user.role === 'admin' && (
                            <Link href={route('invoices.create')} className="btn btn-primary rounded-pill px-4 font-bold shadow-sm">
                                <i className="fas fa-plus me-2"></i>Generate Invoice
                            </Link>
                        )}
                    </div>
                }
            />

            <div className="px-0 py-0">
                <DashboardSearch 
                    placeholder="Search by INV-XXXX or patient name..." 
                    value={search}
                    onChange={setSearch}
                    onSubmit={applyFilters}
                />

                <DashboardTable 
                    data={invoices.data}
                    columns={[
                        {
                            header: 'Invoice #',
                            accessorKey: 'invoice_number',
                            cell: info => <span className="fw-bold text-primary">{info.getValue()}</span>
                        },
                        {
                            header: 'Date',
                            accessorKey: 'invoice_date',
                        },
                        {
                            header: 'Patient',
                            accessorKey: 'patient.user.first_name',
                            cell: info => `${info.row.original.patient.user.first_name} ${info.row.original.patient.user.last_name}`
                        },
                        {
                            header: 'Amount',
                            accessorKey: 'total_amount',
                            cell: info => <span className="fw-bold">Ksh {info.getValue()}</span>
                        },
                        {
                            header: 'Status',
                            accessorKey: 'status',
                            cell: info => (
                                <span className={`badge ${info.getValue() === 'paid' ? 'bg-success' : (info.getValue() === 'overdue' ? 'bg-danger' : 'bg-warning text-dark')}`}>
                                    {info.getValue().toUpperCase()}
                                </span>
                            ),
                            enableSorting: false
                        },
                        {
                            header: 'Actions',
                            id: 'actions',
                            cell: info => (
                                <div className="text-end">
                                    <Link href={route('invoices.show', info.row.original.invoice_id)} className="btn btn-sm btn-outline-primary shadow-sm">
                                        <i className="fas fa-eye me-1"></i> View
                                    </Link>
                                    {info.row.original.status !== 'paid' && (
                                        <button className="btn btn-sm btn-success shadow-sm ms-2">
                                            <i className="fas fa-money-bill-wave me-1"></i> Pay
                                        </button>
                                    )}
                                </div>
                            )
                        }
                    ]}
                    pagination={invoices}
                    emptyMessage="No invoices found."
                />
            </div>
        </AuthenticatedLayout>
    );
}
