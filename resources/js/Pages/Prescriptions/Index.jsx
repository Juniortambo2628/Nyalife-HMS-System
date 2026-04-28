import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';
import StatusBadge from '@/Components/StatusBadge';
import DashboardSelect from '@/Components/DashboardSelect';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import TableActions from '@/Components/TableActions';
import { useState, useMemo, useEffect } from 'react';

export default function Index({ prescriptions, filters, auth }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [selectedIds, setSelectedIds] = useState([]);

    useEffect(() => {
        const handleClear = () => setSelectedIds([]);
        window.addEventListener('toolbar-clear-selection', handleClear);
        return () => window.removeEventListener('toolbar-clear-selection', handleClear);
    }, []);

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
            cell: ({ row }) => <span className="badge bg-light text-pink-500 fw-extrabold extra-small tracking-widest p-2 border border-pink-100 shadow-sm">RX-{String(row.original.prescription_id).padStart(5, '0')}</span>
        },
        {
            header: 'Date',
            accessorKey: 'prescription_date',
            cell: ({ row }) => <span className="extra-small fw-bold text-gray-500 text-uppercase">{row.original.prescription_date}</span>
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
                    <Link href={route('prescriptions.show', row.original.prescription_id)} className="btn btn-sm btn-light border text-pink-500 rounded-circle p-2 shadow-sm avatar-sm d-flex align-items-center justify-content-center" title="View Details">
                        <i className="fas fa-eye extra-small"></i>
                    </Link>
                    {auth.user.role === 'pharmacist' && row.original.status === 'pending' && (
                        <button 
                            className="btn btn-sm btn-light border text-success rounded-circle p-2 shadow-sm avatar-sm d-flex align-items-center justify-content-center"
                            title="Dispense Medication"
                        >
                            <i className="fas fa-check-circle extra-small"></i>
                        </button>
                    )}
                </div>
            )
        }
    ], []);

    return (
        <AuthenticatedLayout 
            headerTitle={auth.user.role === 'patient' ? 'Prescription History' : 'Prescription Registry'}
            breadcrumbs={[{ label: 'Pharmacy', url: route('pharmacy.inventory') }, { label: 'Prescriptions', active: true }]}
        >
            <Head title={auth.user.role === 'patient' ? 'My Prescriptions' : 'Pharmacy'} />

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
                                { label: 'Dispensed', value: 'dispensed' },
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
                                { label: 'All RX', value: '' },
                                { label: 'Inpatient', value: 'inpatient' },
                                { label: 'Outpatient', value: 'outpatient' },
                            ]}
                            value={filters?.quick_filter}
                            onChange={handleQuickFilterChange}
                            placeholder="Patient Type..."
                            theme="dark"
                            dropup={true}
                        />
                    </>
                }
                bulkActions={[
                    auth.user.role === 'pharmacist' && { label: 'DISPENSE ALL', icon: 'fa-check-double', onClick: () => console.log('Dispense', selectedIds) },
                    { label: 'PRINT BATCH', icon: 'fa-print', onClick: () => console.log('Print', selectedIds) },
                    { label: 'CANCEL SELECTED', icon: 'fa-times-circle', onClick: () => console.log('Cancel', selectedIds), color: 'danger' }
                ]}
                selectionCount={selectedIds.length}
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

                <DashboardTable 
                    columns={columns}
                    data={prescriptions.data}
                    pagination={prescriptions}
                    emptyMessage="No prescriptions found."
                    selectable={true}
                    selectedIds={selectedIds}
                    onSelectionChange={setSelectedIds}
                    idField="prescription_id"
                />
            </div>
        </AuthenticatedLayout>
    );
}
