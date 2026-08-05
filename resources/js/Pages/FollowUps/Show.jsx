import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import StatusBadge from '@/Components/StatusBadge';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Show({ followUp, auth }) {
    const updateStatus = (status) => {
        if (confirm(`Mark this follow-up as ${status.replace('_', ' ')}?`)) {
            router.post(route('follow-ups.update-status', followUp.follow_up_id), { status });
        }
    };

    const deleteFollowUp = () => {
        if (confirm('Remove this follow-up?')) {
            router.delete(route('follow-ups.destroy', followUp.follow_up_id));
        }
    };

    const canManage = ['admin', 'doctor', 'nurse'].includes(auth.user.role);

    return (
        <AuthenticatedLayout
            headerTitle="Follow-up Details"
            breadcrumbs={[
                { label: 'Follow-ups', url: route('follow-ups.index') },
                { label: `#${followUp.follow_up_id}`, active: true },
            ]}
        >
            <Head title={`Follow-up #${followUp.follow_up_id}`} />

            <div className="row g-4">
                <div className="col-lg-8">
                    <div className="card border-0 shadow-sm rounded-4 bg-white">
                        <div className="card-body p-5">
                            <div className="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    <div className="extra-small fw-bold text-muted text-uppercase tracking-widest mb-1">
                                        Scheduled Date
                                    </div>
                                    <div className="h3 fw-extrabold text-gray-900">{followUp.follow_up_date}</div>
                                </div>
                                <StatusBadge status={followUp.status} />
                            </div>

                            <div className="row g-4 mb-4">
                                <div className="col-md-6">
                                    <div className="p-4 rounded-4 bg-gray-50 border">
                                        <h6 className="extra-small fw-extrabold text-muted text-uppercase mb-2">
                                            Patient
                                        </h6>
                                        <div className="fw-bold">
                                            {followUp.patient?.user?.first_name} {followUp.patient?.user?.last_name}
                                        </div>
                                    </div>
                                </div>
                                <div className="col-md-6">
                                    <div className="p-4 rounded-4 bg-gray-50 border">
                                        <h6 className="extra-small fw-extrabold text-muted text-uppercase mb-2">
                                            Type
                                        </h6>
                                        <div className="fw-bold">{followUp.follow_up_type_label}</div>
                                    </div>
                                </div>
                            </div>

                            <div className="mb-4">
                                <h6 className="extra-small fw-extrabold text-muted text-uppercase mb-2">Reason</h6>
                                <p className="mb-0 text-gray-800">{followUp.reason}</p>
                            </div>

                            {followUp.notes && (
                                <div>
                                    <h6 className="extra-small fw-extrabold text-muted text-uppercase mb-2">Notes</h6>
                                    <p className="mb-0 text-muted">{followUp.notes}</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                <div className="col-lg-4">
                    {followUp.consultation_id && (
                        <div className="card border-0 shadow-sm rounded-4 mb-4">
                            <div className="card-body p-4">
                                <h6 className="fw-extrabold text-muted extra-small text-uppercase tracking-widest mb-3">
                                    Linked Consultation
                                </h6>
                                <Link
                                    href={route('consultations.show', { consultation: followUp.consultation_id })}
                                    className="btn btn-outline-primary w-100 rounded-pill fw-bold"
                                >
                                    View Consultation #{followUp.consultation_id}
                                </Link>
                            </div>
                        </div>
                    )}
                    {followUp.created_by_user && (
                        <div className="card border-0 shadow-sm rounded-4">
                            <div className="card-body p-4 text-muted small">
                                Scheduled by {followUp.created_by_user.first_name} {followUp.created_by_user.last_name}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {canManage && (
                <UnifiedToolbar
                    actions={[
                        followUp.status === 'scheduled' && {
                            label: 'MARK COMPLETED',
                            icon: 'fa-check-circle',
                            onClick: () => updateStatus('completed'),
                            color: 'success',
                        },
                        followUp.status === 'scheduled' && {
                            label: 'NO SHOW',
                            icon: 'fa-user-times',
                            onClick: () => updateStatus('no_show'),
                            color: 'warning',
                        },
                        followUp.status === 'scheduled' && {
                            label: 'CANCEL',
                            icon: 'fa-ban',
                            onClick: () => updateStatus('cancelled'),
                            color: 'danger',
                        },
                        { label: 'EDIT', icon: 'fa-edit', href: route('follow-ups.edit', followUp.follow_up_id) },
                        followUp.status !== 'completed' && {
                            label: 'DELETE',
                            icon: 'fa-trash',
                            onClick: deleteFollowUp,
                            color: 'danger',
                        },
                        { label: 'ALL FOLLOW-UPS', icon: 'fa-list', href: route('follow-ups.index'), color: 'gray' },
                    ].filter(Boolean)}
                />
            )}
        </AuthenticatedLayout>
    );
}
