import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import DashboardTable from '@/Components/DashboardTable';
import TableActions from '@/Components/TableActions';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import StatusBadge from '@/Components/StatusBadge';

export default function LabTestsIndex({ tests }) {
    const handleToggleStatus = (id) => {
        if (confirm('Change visibility of this test type?')) {
            router.delete(route('lab-tests.destroy', id), {
                preserveScroll: true
            });
        }
    };

    return (
        <AuthenticatedLayout
            header="Lab Test Management"
        >
            <Head title="Lab Test types" />

            <PageHeader 
                title="Lab Test Catalog"
                breadcrumbs={[{ label: 'Lab Registry', url: route('lab.index') }, { label: 'Manage Tests', active: true }]}
            />

            <UnifiedToolbar 
                actions={[
                    { 
                        label: 'NEW TEST TYPE', 
                        icon: 'fa-plus-circle', 
                        href: route('lab-tests.create') 
                    },
                    { 
                        label: 'BACK TO LAB', 
                        icon: 'fa-arrow-left', 
                        href: route('lab.index'),
                        color: 'gray'
                    }
                ]}
            />

            <div className="py-0">
                <DashboardTable 
                    data={tests}
                    columns={[
                        {
                            header: 'Test Name',
                            accessorKey: 'test_name',
                            cell: info => (
                                <div>
                                    <div className="fw-bold text-gray-900">{info.getValue()}</div>
                                    <div className="text-muted extra-small">{info.row.original.description?.substring(0, 50)}...</div>
                                </div>
                            )
                        },
                         {
                            header: 'Category',
                            accessorKey: 'category',
                            cell: info => (
                                <span className="badge bg-soft-primary text-primary rounded-pill px-3 py-1.5 fw-bold extra-small text-uppercase border border-primary-subtle">
                                    {info.getValue()}
                                </span>
                            )
                        },
                        {
                            header: 'Price',
                            accessorKey: 'price',
                            cell: info => (
                                <span className="fw-extrabold text-gray-900">
                                    Ksh. {new Intl.NumberFormat('en-KE').format(info.getValue())}
                                </span>
                            )
                        },
                        {
                            header: 'Normal Range',
                            accessorKey: 'normal_range',
                            cell: info => (
                                <div className="fw-bold text-gray-700">
                                    {info.getValue() || 'N/A'} <small className="text-muted extra-small opacity-50">{info.row.original.units}</small>
                                </div>
                            )
                        },
                        {
                            header: 'Status',
                            accessorKey: 'is_active',
                            cell: info => (
                                <StatusBadge status={info.getValue() ? 'active' : 'inactive'} />
                            )
                        },
                        {
                            header: 'Actions',
                            id: 'actions',
                            cell: info => (
                                <TableActions actions={[
                                    { 
                                        icon: 'fa-edit', 
                                        label: 'Edit Configuration', 
                                        href: route('lab-tests.edit', info.row.original.test_type_id),
                                        color: 'warning'
                                    },
                                    { 
                                        icon: info.row.original.is_active ? 'fa-eye-slash' : 'fa-eye', 
                                        label: info.row.original.is_active ? 'Deactivate' : 'Activate', 
                                        onClick: () => handleToggleStatus(info.row.original.test_type_id),
                                        color: info.row.original.is_active ? 'danger' : 'success'
                                    },
                                ]} />
                            )
                        }
                    ]}
                    emptyMessage="No test types defined."
                />
            </div>

            <style>{`
                .extra-small { font-size: 0.7rem; }
                .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
            `}</style>
        </AuthenticatedLayout>
    );
}
