import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import StatusBadge from '@/Components/StatusBadge';
import TableActions from '@/Components/TableActions';
import PatientTableCell from '@/Components/PatientTableCell';
import { TableCellPrimary, TableCellStack } from '@/Components/TableCells';
import { formatDateOnly, formatDateTime } from '@/Utils/dateUtils';
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
            headerTitle="Website Inquiries"
            breadcrumbs={[
                { label: 'Dashboard', url: route('dashboard') },
                { label: 'Messages', active: true },
            ]}
        >
            <Head title="Contact Messages" />

            <div className="py-0">
                <RegistryTablePanel
                    title="Contact messages"
                    icon="fa-envelope"
                    data={messages}
                    columns={[
                        {
                            header: 'Status',
                            accessorKey: 'status',
                            cell: info => <StatusBadge status={info.getValue()} />,
                        },
                        {
                            header: 'From',
                            accessorKey: 'name',
                            cell: info => (
                                <TableCellStack
                                    primary={info.getValue()}
                                    secondary={info.row.original.email}
                                />
                            ),
                        },
                        {
                            header: 'Message Preview',
                            accessorKey: 'message',
                            cell: info => (
                                <TableCellPrimary className="text-muted text-truncate" style={{ maxWidth: '300px' }}>
                                    {info.getValue()}
                                </TableCellPrimary>
                            ),
                        },
                        {
                            header: 'Received',
                            accessorKey: 'created_at',
                            cell: info => (
                                <TableCellStack
                                    primary={formatDateOnly(info.getValue())}
                                    secondary={formatDateTime(info.getValue())}
                                />
                            ),
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
