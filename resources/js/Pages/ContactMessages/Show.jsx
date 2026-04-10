import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';

export default function Show({ message, auth }) {
    const deleteMessage = () => {
        if (confirm('Are you sure you want to delete this message?')) {
            router.delete(route('admin.messages.destroy', message.contact_message_id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header="View Message"
        >
            <Head title="View Message" />

            <PageHeader 
                title="Message Details"
                breadcrumbs={[
                    { label: 'Dashboard', url: route('dashboard') },
                    { label: 'Messages', url: route('admin.messages.index') },
                    { label: 'View', active: true }
                ]}
            />

            <div className="row justify-content-center">
                <div className="col-lg-8">
                    <div className="card shadow-sm border-0 rounded-xl overflow-hidden bg-white">
                        <div className="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                            <div className="d-flex align-items-center gap-3">
                                <div className="avatar-md bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center">
                                    <i className="fas fa-user fa-lg"></i>
                                </div>
                                <div>
                                    <h5 className="fw-bold mb-0 text-gray-900">{message.name}</h5>
                                    <div className="text-muted small">{message.email}</div>
                                </div>
                            </div>
                            <div className="text-end">
                                <span className={`badge rounded-pill px-3 py-2 mb-1 ${message.status === 'pending' ? 'bg-warning text-dark' : 'bg-light text-muted'}`}>
                                    {message.status === 'pending' ? 'New Message' : 'Read'}
                                </span>
                                <div className="extra-small text-muted">
                                    {new Date(message.created_at).toLocaleString()}
                                </div>
                            </div>
                        </div>
                        <div className="card-body p-4">
                            <div className="bg-light rounded-3 p-4 mb-4" style={{ minHeight: '200px' }}>
                                <div className="text-gray-700 whitespace-pre-wrap lead font-sans" style={{ whiteSpace: 'pre-wrap' }}>
                                    {message.message}
                                </div>
                            </div>

                            <div className="d-flex justify-content-between align-items-center pt-2">
                                <Link 
                                    href={route('admin.messages.index')}
                                    className="btn btn-light rounded-pill px-4"
                                >
                                    <i className="fas fa-arrow-left me-2"></i> Back to List
                                </Link>
                                <div className="d-flex gap-2">
                                    <a 
                                        href={`mailto:${message.email}?subject=Re: Inquiry from Nyalife Website`}
                                        className="btn btn-primary rounded-pill px-4"
                                    >
                                        <i className="fas fa-reply me-2"></i> Reply by Email
                                    </a>
                                    <button 
                                        onClick={deleteMessage}
                                        className="btn btn-outline-danger rounded-pill px-4"
                                    >
                                        <i className="fas fa-trash me-2"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>{`
                .extra-small { font-size: 0.75rem; }
                .rounded-xl { border-radius: 1rem; }
                .avatar-md { width: 48px; height: 48px; }
            `}</style>
        </AuthenticatedLayout>
    );
}
