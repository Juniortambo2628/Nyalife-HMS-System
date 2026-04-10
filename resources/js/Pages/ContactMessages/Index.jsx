import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import DashboardTable from '@/Components/DashboardTable';
import { useState } from 'react';

export default function Index({ messages, auth }) {
    const [processing, setProcessing] = useState(null);

    const markAsRead = (id) => {
        setProcessing(id);
        router.post(route('admin.messages.read', id), {}, {
            onFinish: () => setProcessing(null)
        });
    };

    const deleteMessage = (id) => {
        if (confirm('Are you sure you want to delete this message?')) {
            router.delete(route('admin.messages.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header="Contact Messages"
        >
            <Head title="Contact Messages" />

            <PageHeader 
                title="Website Inquiries"
                breadcrumbs={[
                    { label: 'Dashboard', url: route('dashboard') },
                    { label: 'Messages', active: true }
                ]}
            />

            <div className="py-0">
                <DashboardTable 
                    data={messages}
                    columns={[
                        {
                            header: 'Status',
                            accessorKey: 'status',
                            cell: info => (
                                info.getValue() === 'pending' ? (
                                    <span className="badge bg-warning text-dark rounded-pill px-3 py-2">
                                        <i className="fas fa-envelope me-1"></i> New
                                    </span>
                                ) : (
                                    <span className="badge bg-light text-muted rounded-pill px-3 py-2">
                                        <i className="fas fa-envelope-open me-1"></i> Read
                                    </span>
                                )
                            )
                        },
                        {
                            header: 'From',
                            accessorKey: 'name',
                            cell: info => (
                                <div>
                                    <div className="text-gray-900">{info.getValue()}</div>
                                    <div className="extra-small text-muted">{info.row.original.email}</div>
                                </div>
                            )
                        },
                        {
                            header: 'Message Preview',
                            accessorKey: 'message',
                            cell: info => (
                                <div className="text-muted text-truncate" style={{ maxWidth: '300px' }}>
                                    {info.getValue()}
                                </div>
                            )
                        },
                        {
                            header: 'Received',
                            accessorKey: 'created_at',
                            cell: info => (
                                <div className="text-muted small">
                                    {new Date(info.getValue()).toLocaleDateString()}
                                    <div className="extra-small">{new Date(info.getValue()).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</div>
                                </div>
                            )
                        },
                        {
                            header: 'Actions',
                            id: 'actions',
                            cell: info => (
                                <div className="text-end">
                                    <div className="d-flex justify-content-end gap-2">
                                        <Link 
                                            href={route('admin.messages.show', info.row.original.contact_message_id)}
                                            className="btn btn-sm btn-outline-info rounded-circle shadow-sm"
                                            title="View Message"
                                            style={{ width: '32px', height: '32px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                                        >
                                            <i className="fas fa-eye"></i>
                                        </Link>
                                        {info.row.original.status === 'pending' && (
                                            <button 
                                                onClick={() => markAsRead(info.row.original.contact_message_id)}
                                                disabled={processing === info.row.original.contact_message_id}
                                                className="btn btn-sm btn-outline-success rounded-circle shadow-sm"
                                                title="Mark as Read"
                                                style={{ width: '32px', height: '32px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                                            >
                                                {processing === info.row.original.contact_message_id ? (
                                                    <span className="spinner-border spinner-border-sm"></span>
                                                ) : (
                                                    <i className="fas fa-check"></i>
                                                )}
                                            </button>
                                        )}
                                        <button 
                                            onClick={() => deleteMessage(info.row.original.contact_message_id)}
                                            className="btn btn-sm btn-outline-danger rounded-circle shadow-sm"
                                            title="Delete"
                                            style={{ width: '32px', height: '32px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                                        >
                                            <i className="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            )
                        }
                    ]}
                    emptyMessage="No messages yet"
                />
            </div>

            <style>{`
                .extra-small { font-size: 0.75rem; }
                .rounded-xl { border-radius: 1rem; }
            `}</style>
        </AuthenticatedLayout>
    );
}
