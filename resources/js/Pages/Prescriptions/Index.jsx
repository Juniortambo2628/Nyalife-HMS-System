import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import DashboardSelect from '@/Components/DashboardSelect';
import { useState, useMemo } from 'react';

export default function Index({ prescriptions, filters, auth }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');

    const applyFilters = (searchValue, statusValue = status, quickFilterValue = filters?.quick_filter) => {
        router.get(route('prescriptions.index'), { search: searchValue, status: statusValue, quick_filter: quickFilterValue }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleQuickFilterChange = (val) => {
        applyFilters(search, status, val);
    };

    const handleStatusChange = (val) => {
        setStatus(val || '');
        applyFilters(search, val || '');
    };

    const columns = useMemo(() => [
        {
            header: 'RX ID',
            accessorKey: 'prescription_id',
            cell: ({ row }) => <span className="fw-bold text-primary">RX-{String(row.original.prescription_id).padStart(5, '0')}</span>
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
                        {row.original.items?.map(i => i.medication?.medication_name || 'Unknown').join(', ') || 'No items'}
                    </div>
                    <small className="text-muted">{row.original.items?.length || 0} items</small>
                </div>
            )
        },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: ({ row }) => (
                <StatusBadge status={row.original.status} />
            )
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
            header={auth.user.role === 'patient' ? 'My Prescriptions' : 'Pharmacy - Prescriptions'}
        >
            <Head title={auth.user.role === 'patient' ? 'My Prescriptions' : 'Pharmacy'} />

            <PageHeader 
                title={auth.user.role === 'patient' ? 'Prescription History' : 'Prescription Registry'}
                breadcrumbs={[{ label: 'Pharmacy', url: route('pharmacy.inventory') }, { label: 'Prescriptions', active: true }]}
            />

            <div className="px-0 py-0">
                <DashboardSearch 
                    placeholder="Search by patient name or RX number..." 
                    value={search}
                    onChange={setSearch}
                    onSubmit={applyFilters}
                    onFilterChange={handleQuickFilterChange}
                    filters={[
                        { label: 'Today', value: 'today' },
                        { label: 'Pending', value: 'pending' },
                        { label: 'Dispensed', value: 'dispensed' },
                    ]}
                />

                <UnifiedToolbar 
                    filters={
                        <div className="d-flex align-items-center gap-2">
                            <DashboardSelect 
                                options={[
                                    { label: 'Pending', value: 'pending' },
                                    { label: 'Dispensed', value: 'dispensed' },
                                    { label: 'Cancelled', value: 'cancelled' },
                                ]}
                                value={status}
                                onChange={handleStatusChange}
                                placeholder="All Status"
                                theme="dark"
                                dropup={true}
                                style={{ width: '150px' }}
                            />
                        </div>
                    }
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
