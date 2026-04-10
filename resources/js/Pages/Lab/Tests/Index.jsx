import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import DashboardTable from '@/Components/DashboardTable';

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
                title="Laboratory Test Catalog"
                breadcrumbs={[{ label: 'Lab', url: route('lab.index') }, { label: 'Manage Tests', active: true }]}
                actions={
                    <Link href={route('lab-tests.create')} className="btn btn-primary rounded-pill px-4 font-bold shadow-sm">
                        <i className="fas fa-plus me-2"></i>New Test Type
                    </Link>
                }
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
                                <span className="badge bg-soft-primary text-primary rounded-pill px-3">
                                    {info.getValue()}
                                </span>
                            )
                        },
                        {
                            header: 'Price',
                            accessorKey: 'price',
                            cell: info => (
                                <span className="fw-semibold">
                                    Ksh. {new Intl.NumberFormat('en-KE').format(info.getValue())}
                                </span>
                            )
                        },
                        {
                            header: 'Normal Range',
                            accessorKey: 'normal_range',
                            cell: info => (
                                <span>
                                    {info.getValue()} <small className="text-muted">{info.row.original.units}</small>
                                </span>
                            )
                        },
                        {
                            header: 'Status',
                            accessorKey: 'is_active',
                            cell: info => (
                                info.getValue() ? (
                                    <span className="badge bg-success rounded-pill">Active</span>
                                ) : (
                                    <span className="badge bg-secondary rounded-pill">Inactive</span>
                                )
                            )
                        },
                        {
                            header: 'Actions',
                            id: 'actions',
                            cell: info => (
                                <div className="text-end">
                                    <div className="d-flex justify-content-end gap-2">
                                        <Link 
                                            href={route('lab-tests.edit', info.row.original.test_type_id)}
                                            className="btn btn-sm btn-outline-info"
                                            title="Edit"
                                        >
                                            <i className="fas fa-edit"></i>
                                        </Link>
                                        <button 
                                            onClick={() => handleToggleStatus(info.row.original.test_type_id)}
                                            className={`btn btn-sm ${info.row.original.is_active ? 'btn-outline-danger' : 'btn-outline-success'}`}
                                            title={info.row.original.is_active ? 'Deactivate' : 'Activate'}
                                        >
                                            <i className={`fas fa-${info.row.original.is_active ? 'eye-slash' : 'eye'}`}></i>
                                        </button>
                                    </div>
                                </div>
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
