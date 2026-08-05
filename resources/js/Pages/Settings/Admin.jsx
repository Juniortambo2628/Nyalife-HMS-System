import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import StatCardGrid from '@/Components/StatCardGrid';

export default function Admin({ stats, settings }) {
    const grouped = settings.reduce((acc, s) => {
        const group = s.group || 'general';
        if (!acc[group]) acc[group] = [];
        acc[group].push(s);
        return acc;
    }, {});

    const statItems = [
        { label: 'Registered users', value: stats.users, icon: 'fa-users', color: 'primary' },
        { label: 'Patients', value: stats.patients, icon: 'fa-user-injured', color: 'info' },
        { label: 'Appointments today', value: stats.appointments_today, icon: 'fa-calendar-day', color: 'success' },
        { label: 'Consultations', value: stats.consultations, icon: 'fa-stethoscope', color: 'purple' },
        { label: 'Pending invoices', value: stats.invoices_pending, icon: 'fa-file-invoice', color: 'warning' },
        {
            label: 'Pending prescriptions',
            value: stats.prescriptions_pending,
            icon: 'fa-prescription',
            color: 'danger',
        },
        {
            label: 'Newsletter subscribers',
            value: stats.newsletter_subscribers,
            icon: 'fa-envelope',
            color: 'secondary',
        },
    ];

    return (
        <AuthenticatedLayout
            headerTitle="System Overview"
            breadcrumbs={[
                { label: 'Admin', url: '/dashboard' },
                { label: 'Settings', active: true },
            ]}
        >
            <Head title="Admin Settings" />

            <div className="py-0">
                <StatCardGrid items={statItems} cols={6} gap={4} className="mb-5" />

                <div className="row g-4">
                    <div className="col-lg-8">
                        <div className="card border-0 shadow-sm rounded-3xl">
                            <div className="card-header bg-white border-0 pt-4 px-4">
                                <h5 className="fw-bold mb-0">Configuration keys</h5>
                                <p className="text-muted small mb-0">
                                    Read-only snapshot of stored settings. Edit landing page content via CMS.
                                </p>
                            </div>
                            <div className="card-body px-4 pb-4">
                                {Object.keys(grouped).length === 0 ? (
                                    <p className="text-muted mb-0">No settings stored yet.</p>
                                ) : (
                                    Object.entries(grouped).map(([group, items]) => (
                                        <div key={group} className="mb-4">
                                            <h6 className="extra-small fw-bold text-muted mb-3 text-capitalize">
                                                {group}
                                            </h6>
                                            <div className="table-responsive">
                                                <table className="table table-sm align-middle mb-0">
                                                    <thead>
                                                        <tr className="text-muted extra-small">
                                                            <th>Key</th>
                                                            <th>Label</th>
                                                            <th>Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {items.map((s) => (
                                                            <tr key={s.key}>
                                                                <td className="font-monospace small">{s.key}</td>
                                                                <td className="small">{s.label || '—'}</td>
                                                                <td
                                                                    className="small text-truncate"
                                                                    style={{ maxWidth: 280 }}
                                                                >
                                                                    {s.value || '—'}
                                                                </td>
                                                            </tr>
                                                        ))}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="col-lg-4">
                        <div className="card border-0 shadow-sm rounded-3xl">
                            <div className="card-body p-4">
                                <h5 className="fw-bold mb-3">Quick links</h5>
                                <div className="d-grid gap-2">
                                    <Link
                                        href={route('admin.api-tokens.index')}
                                        className="btn btn-outline-primary rounded-pill text-start"
                                    >
                                        <i className="fas fa-key me-2"></i> API Tokens
                                    </Link>
                                    <Link
                                        href={route('cms.index')}
                                        className="btn btn-outline-primary rounded-pill text-start"
                                    >
                                        <i className="fas fa-laptop-code me-2"></i> Landing Page CMS
                                    </Link>
                                    <Link
                                        href={route('users.index')}
                                        className="btn btn-outline-secondary rounded-pill text-start"
                                    >
                                        <i className="fas fa-user-cog me-2"></i> User Management
                                    </Link>
                                    <Link
                                        href={route('reports.index')}
                                        className="btn btn-outline-secondary rounded-pill text-start"
                                    >
                                        <i className="fas fa-chart-bar me-2"></i> Reports
                                    </Link>
                                    <Link
                                        href={route('admin.messages.index')}
                                        className="btn btn-outline-secondary rounded-pill text-start"
                                    >
                                        <i className="fas fa-envelope-open-text me-2"></i> Website Messages
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
