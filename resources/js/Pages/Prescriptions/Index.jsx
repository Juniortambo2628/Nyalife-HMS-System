import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';
import PageHeader from '@/Components/PageHeader';
import { useState, useMemo } from 'react';

export default function Index({ prescriptions, filters, auth }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');

    const applyFilters = (searchValue, statusValue = status) => {
        router.get(route('prescriptions.index'), { search: searchValue, status: statusValue }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleStatusChange = (e) => {
        const newStatus = e.target.value;
        setStatus(newStatus);
        applyFilters(search, newStatus);
    };

    const columns = useMemo(() => [
        {
            header: 'RX Number',
            accessorKey: 'prescription_number',
            cell: ({ row }) => <span className="fw-bold text-primary">{row.original.prescription_number}</span>
        },
        {
            header: 'Date',
            accessorKey: 'prescription_date',
            cell: ({ row }) => <span>{row.original.prescription_date}</span>
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
            header: 'Medications',
            accessorKey: 'items',
            cell: ({ row }) => (
                <div>
                    <div className="text-truncate" style={{ maxWidth: '300px' }}>
                        {row.original.items?.map(i => i.medicine_name).join(', ') || 'No items'}
                    </div>
                    <small className="text-muted">{row.original.items?.length || 0} items</small>
                </div>
            )
        },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: ({ row }) => {
                const s = row.original.status;
                const badgeClass = s === 'dispensed' ? 'bg-success' : (s === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark');
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
                    <Link href={route('prescriptions.show', row.original.prescription_id)} className="btn btn-sm btn-outline-primary shadow-sm">
                        <i className="fas fa-eye me-1"></i> View
                    </Link>
                    {auth.user.role === 'pharmacist' && row.original.status === 'pending' && (
                        <button className="btn btn-sm btn-success shadow-sm ms-2">
                            <i className="fas fa-check-circle me-1"></i> Dispense
                        </button>
                    )}
                </div>
            )
        }
    ], []);

    return (
        <AuthenticatedLayout
            header="Pharmacy - Prescriptions"
        >
            <Head title="Pharmacy" />

            <PageHeader 
                title="Prescription Registry"
                breadcrumbs={[{ label: 'Pharmacy', active: true }, { label: 'Prescriptions', active: true }]}
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
                            <option value="dispensed">Dispensed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                }
            />

            <div className="px-0 py-0">
                <DashboardSearch 
                    placeholder="Search by patient name or RX number..." 
                    value={search}
                    onChange={setSearch}
                    onSubmit={applyFilters}
                />

                {/* Table */}
                <DashboardTable 
                    columns={columns}
                    data={prescriptions.data}
                    pagination={prescriptions}
                    emptyMessage="No prescriptions found."
                />
            </div>
        </AuthenticatedLayout>
    );
}
