import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import TableActions from '@/Components/TableActions';
import StatusBadge from '@/Components/StatusBadge';
import GridCardActions from '@/Components/GridCardActions';
import { useCallback } from 'react';

export default function Index({ auth, insurances }) {
    const userRole = auth?.user?.role || 'patient';

    const toggleStatus = useCallback((id) => {
        router.post(
            route('insurances.toggle', id),
            {},
            {
                preserveScroll: true,
            },
        );
    }, []);

    const handleDelete = useCallback((id) => {
        if (confirm('Are you sure you want to remove this insurance provider?')) {
            router.delete(route('insurances.destroy', id), {
                preserveScroll: true,
            });
        }
    }, []);

    return (
        <AuthenticatedLayout
            headerTitle="Insurance Partners"
            breadcrumbs={[
                { label: 'Dashboard', url: '/dashboard' },
                { label: 'Insurances', active: true },
            ]}
        >
            <Head title="Health Insurances" />

            {userRole === 'admin' && (
                <UnifiedToolbar
                    actions={[
                        {
                            label: 'ADD PROVIDER',
                            icon: 'fa-plus-circle',
                            href: route('insurances.create'),
                        },
                    ]}
                />
            )}

            <div className="container-fluid pb-5">
                {insurances.length === 0 ? (
                    <div className="text-center py-5 bg-white rounded-4 shadow-sm border">
                        <div className="mb-4">
                            <i className="fas fa-id-card-alt text-light display-1"></i>
                        </div>
                        <h4 className="fw-bold text-dark">No Insurance Partners</h4>
                        <p className="text-muted mb-4 text-dark">
                            You haven't added any insurance providers yet. Start by adding your first partner.
                        </p>
                        <Link href={route('insurances.create')} className="btn btn-primary rounded-pill px-5 fw-bold">
                            Add Provider Now
                        </Link>
                    </div>
                ) : (
                    <div className="row g-4">
                        {insurances.map((insurance) => (
                            <div key={insurance.insurance_id} className="col-xl-3 col-lg-4 col-md-6 text-dark h-auto">
                                <div className="card h-100 border-0 shadow-sm rounded-4 overflow-hidden insurance-card transition-all">
                                    {/* Action Header */}
                                    <div className="p-3 d-flex justify-content-between align-items-center bg-light-subtle border-bottom">
                                        <button
                                            type="button"
                                            onClick={() => toggleStatus(insurance.insurance_id)}
                                            className="btn btn-link p-0 border-0 text-decoration-none"
                                        >
                                            <StatusBadge status={insurance.is_active ? 'active' : 'inactive'} />
                                        </button>
                                        <GridCardActions
                                            className="border-0 pt-0 mt-0"
                                            actions={[
                                                {
                                                    icon: 'fa-edit',
                                                    label: 'Edit details',
                                                    href: route('insurances.edit', insurance.insurance_id),
                                                },
                                                {
                                                    icon: 'fa-trash',
                                                    label: 'Delete provider',
                                                    color: 'danger',
                                                    onClick: () => handleDelete(insurance.insurance_id),
                                                },
                                            ]}
                                        />
                                    </div>

                                    {/* Card Content */}
                                    <div className="card-body p-4 text-center d-flex flex-column h-100">
                                        <div className="logo-wrapper mb-4 mx-auto bg-white rounded-3 shadow-sm d-flex align-items-center justify-content-center p-2 border">
                                            <img
                                                src={insurance.logo_url}
                                                alt={insurance.name}
                                                className="img-fluid rounded-2"
                                                style={{ maxHeight: '80px', objectFit: 'contain' }}
                                            />
                                        </div>
                                        <h5 className="fw-extrabold text-gray-900 mb-2">{insurance.name}</h5>

                                        {insurance.link && (
                                            <a
                                                href={insurance.link}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-pink-500 small font-bold text-decoration-none hover:text-pink-700 mt-auto pt-3"
                                            >
                                                <i className="fas fa-external-link-alt me-1"></i>Visit Website
                                            </a>
                                        )}
                                        {!insurance.link && <div className="mt-auto pt-3">&nbsp;</div>}
                                    </div>

                                    {/* Footer Info */}
                                    <div className="card-footer bg-white border-top-0 pb-4 px-4 text-center">
                                        <div className="badge bg-secondary-subtle text-secondary fw-bold px-3 py-2 border border-secondary-subtle rounded-pill extra-small">
                                            Priority Level: {insurance.sort_order}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            <style>{`
    .insurance-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 1rem 3rem rgba(0,0,0,.1) !important;
                }
                .logo-wrapper {
                    height: 100px;
                    width: 150px;
                }
                .text-pink-500 { color: #e91e63; }
                .text-pink-700 { color: #c2185b; }
                .fw-extrabold { font-weight: 850; }
            `}</style>
        </AuthenticatedLayout>
    );
}
