import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import PageHeader from '@/Components/PageHeader';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';
import TableActions from '@/Components/TableActions';
import StatusBadge from '@/Components/StatusBadge';

export default function Index({ medications, filters, auth }) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('inventory.index'), { search }, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <AuthenticatedLayout header="Pharmacy Inventory">
            <Head title="Inventory Stock" />

            <PageHeader 
                title="Clinical Inventory"
                breadcrumbs={[{ label: 'Pharmacy', active: false }, { label: 'Inventory Stock', active: true }]}
            />

            <UnifiedToolbar 
                actions={[
                    auth.user.role === 'pharmacist' && { 
                        label: 'ADD NEW DRUG', 
                        icon: 'fa-plus', 
                        href: '#' 
                    }
                ]}
            />

            <div className="px-0">
                <DashboardSearch 
                    placeholder="Search catalog by medication name or generic identity..." 
                    value={search}
                    onChange={setSearch}
                    onSubmit={() => router.get(route('inventory.index'), { search }, { preserveState: true, replace: true })}
                />

                <DashboardTable 
                    data={medications.data}
                    pagination={medications}
                    columns={[
                        {
                            header: 'Medication Name',
                            accessorKey: 'medication_name',
                            cell: info => <span className="fw-extrabold text-gray-900">{info.getValue()}</span>
                        },
                        {
                            header: 'Type',
                            accessorKey: 'medication_type',
                            cell: info => <span className="text-muted extra-small fw-bold text-uppercase">{info.getValue()}</span>
                        },
                        {
                            header: 'Strength',
                            accessorKey: 'strength',
                            cell: info => (
                                <span className="text-muted extra-small fw-bold">
                                    {info.getValue()} {info.row.original.unit}
                                </span>
                            )
                        },
                        {
                            header: 'In Stock',
                            accessorKey: 'stock_quantity',
                            cell: info => (
                                <div className="text-center">
                                    <span className="fw-extrabold text-gray-900">{info.getValue()}</span>
                                </div>
                            )
                        },
                        {
                            header: 'Status',
                            accessorKey: 'stock_quantity',
                            id: 'stock_status',
                            cell: info => {
                                const qty = info.getValue();
                                let status = 'in-stock';
                                if (qty <= 0) status = 'out-of-stock';
                                else if (qty <= 20) status = 'low-stock';
                                
                                return (
                                    <div className="text-center">
                                        <StatusBadge status={status} />
                                    </div>
                                );
                            }
                        },
                        {
                            header: 'Actions',
                            id: 'actions',
                            cell: info => (
                                <TableActions actions={[
                                    { 
                                        icon: 'fa-eye', 
                                        label: 'View Inventory', 
                                        href: route('inventory.show', info.row.original.medication_id),
                                        color: 'primary'
                                    },
                                    { 
                                        icon: 'fa-edit', 
                                        label: 'Edit Stock', 
                                        onClick: () => {/* handleEdit */},
                                        color: 'warning'
                                    }
                                ]} />
                            )
                        }
                    ]}
                    emptyMessage="No medical stock found in the repository."
                />
            </div>
        </AuthenticatedLayout>
    );
}
