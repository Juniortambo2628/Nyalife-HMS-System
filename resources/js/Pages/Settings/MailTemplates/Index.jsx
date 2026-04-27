import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Index({ templates }) {
    return (
        <AuthenticatedLayout
            header="Email Templates"
        >
            <Head title="Email Templates" />

            <PageHeader 
                title="Email Templates"
                subtitle="Customize automated system emails"
                breadcrumbs={[{ label: 'Settings', url: '/dashboard' }, { label: 'Email Templates', active: true }]}
            />

            <div className="py-4">
                <div className="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div className="card-header bg-white py-3">
                        <h5 className="mb-0 fw-bold">System Templates</h5>
                    </div>
                    <div className="card-body p-0">
                        <div className="table-responsive">
                            <table className="table table-hover align-middle mb-0">
                                <thead className="bg-light">
                                    <tr>
                                        <th className="px-4 py-3">Mailable Class</th>
                                        <th className="py-3">Subject</th>
                                        <th className="py-3 text-end px-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {templates.map((template) => (
                                        <tr key={template.id}>
                                            <td className="px-4 py-3 fw-medium text-dark">
                                                {template.mailable}
                                            </td>
                                            <td className="py-3">
                                                {template.subject}
                                            </td>
                                            <td className="py-3 text-end px-4">
                                                <Link
                                                    href={route('mail-templates.edit', template.id)}
                                                    className="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                >
                                                    <i className="fas fa-edit me-1"></i> Edit
                                                </Link>
                                            </td>
                                        </tr>
                                    ))}
                                    {templates.length === 0 && (
                                        <tr>
                                            <td colSpan="3" className="text-center py-5 text-muted">
                                                No templates found. Run the seeder or create new ones.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <UnifiedToolbar 
                    actions={
                        <button 
                            onClick={() => router.reload({ preserveScroll: true })}
                            className="btn btn-primary rounded-pill px-3 py-2 fw-bold small"
                        >
                            <i className="fas fa-sync-alt me-1"></i> Refresh Templates
                        </button>
                    }
                />
            </div>
        </AuthenticatedLayout>
    );
}
