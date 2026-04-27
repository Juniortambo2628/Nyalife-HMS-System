import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import PageHeader from '@/Components/PageHeader';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';
import StatusBadge from '@/Components/StatusBadge';
import DashboardSelect from '@/Components/DashboardSelect';

export default function Index({ invoices, filters, auth }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [quickFilter, setQuickFilter] = useState(filters.quick_filter || '');

    const applyFilters = (searchValue, statusValue = status, quickFilterValue = quickFilter) => {
        router.get(route('invoices.index'), { search: searchValue, status: statusValue, quick_filter: quickFilterValue }, {
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

    const columns = useMemo(() => [
        {
            header: 'Invoice #',
            accessorKey: 'invoice_number',
            cell: info => (
                <span className="badge bg-light text-primary fw-bold p-2 border border-light-subtle shadow-sm">
                    {info.getValue()}
                </span>
            )
        },
        {
            header: 'Patient',
            accessorKey: 'patient.user.first_name',
            cell: info => (
                <div>
                    <div className="fw-bold text-gray-900">{info.row.original.patient.user.first_name} {info.row.original.patient.user.last_name}</div>
                    <div className="extra-small text-muted fw-bold text-uppercase opacity-75">ID: PAT-{info.row.original.patient_id}</div>
                </div>
            )
        },
        {
            header: 'Amount',
            accessorKey: 'total_amount',
            cell: info => (
                <div className="fw-extrabold text-gray-900">
                    <span className="text-muted extra-small me-1 fw-bold">KES</span>
                    {Number(info.getValue()).toLocaleString()}
                </div>
            )
        },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: info => <StatusBadge status={info.getValue()} />,
            enableSorting: false
        },
        {
            header: 'Date',
            accessorKey: 'invoice_date',
            cell: info => <span className="extra-small fw-bold text-gray-500 text-uppercase">{info.getValue()}</span>
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: info => (
                <div className="d-flex justify-content-end gap-2">
                    <Link href={route('invoices.show', info.row.original.invoice_id)} className="btn btn-sm btn-light border text-primary rounded-circle p-2 shadow-sm avatar-sm d-flex align-items-center justify-content-center">
                        <i className="fas fa-eye extra-small"></i>
                    </Link>
                    {info.row.original.status !== 'paid' && (
                        <Link href={route('invoices.show', info.row.original.invoice_id)} className="btn btn-sm btn-light border text-success rounded-circle p-2 shadow-sm avatar-sm d-flex align-items-center justify-content-center">
                            <i className="fas fa-money-bill-wave extra-small"></i>
                        </Link>
                    )}
                </div>
            )
        }
    ], []);

    return (
        <AuthenticatedLayout 
            header="Billing & Invoices"
            toolbarFilters={
                <div className="d-flex align-items-center gap-2">
                    <DashboardSelect 
                        options={[
                            { label: 'Pending', value: 'pending' },
                            { label: 'Paid', value: 'paid' },
                            { label: 'Overdue', value: 'overdue' },
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
            toolbarActions={
                (auth.user.role === 'admin' || auth.user.role === 'receptionist') && (
                    <Link href={route('invoices.create')} className="btn btn-primary rounded-pill px-4 py-2 fw-bold small shadow-sm">
                        <i className="fas fa-file-medical me-1"></i> New Invoice
                    </Link>
                )
            }
        >
            <Head title="Billing" />

            <PageHeader 
                title="Financial Registry"
                breadcrumbs={[{ label: 'Billing', active: true }, { label: 'Invoices', active: true }]}
            />

            <div className="px-0">
                <DashboardSearch 
                    placeholder="Search by INV-XXXX or patient name..." 
                    value={search}
                    onChange={setSearch}
                    onSubmit={(val) => applyFilters(val, status, quickFilter)}
                    onFilterChange={handleQuickFilterChange}
                    filters={[
                        { label: 'Unpaid', value: 'unpaid' },
                        { label: 'Paid', value: 'paid' },
                        { label: 'Overdue', value: 'overdue' },
                    ]}
                />

                <DashboardTable 
                    data={invoices.data}
                    columns={columns}
                    pagination={invoices}
                    emptyMessage="No financial records found matching your search."
                />

            </div>
        </AuthenticatedLayout>
    );
}
