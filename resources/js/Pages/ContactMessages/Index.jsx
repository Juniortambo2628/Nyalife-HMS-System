import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import DashboardTable from '@/Components/DashboardTable';
import StatusBadge from '@/Components/StatusBadge';
import TableActions from '@/Components/TableActions';
import { useState } from 'react';

export default function Index({ messages, auth }) {
    const [processing, setProcessing] = useState(null);
    const [selectedIds, setSelectedIds] = useState([]);

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
                                <StatusBadge status={info.getValue() === 'pending' ? 'pending' : 'completed'} />
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
                            cell: info => {
                                const actions = [
                                    { icon: 'fa-eye', label: 'View Message', href: route('admin.messages.show', info.row.original.contact_message_id) },
                                ];
                                if (info.row.original.status === 'pending') {
                                    actions.push({ icon: 'fa-check', label: 'Mark as Read', onClick: () => markAsRead(info.row.original.contact_message_id) });
                                }
                                actions.push({ icon: 'fa-trash', label: 'Delete', color: 'danger', onClick: () => deleteMessage(info.row.original.contact_message_id) });
                                return <TableActions actions={actions} />;
                            }
                        }
                    ]}
                    emptyMessage="No messages yet"
                    selectable={true}
                    selectedIds={selectedIds}
                    onSelectionChange={setSelectedIds}
                    idField="contact_message_id"
                />

            </div>
        </AuthenticatedLayout>
    );
}
