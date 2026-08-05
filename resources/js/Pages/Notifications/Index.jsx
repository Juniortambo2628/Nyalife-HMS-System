import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import TableActions from '@/Components/TableActions';
import Pagination from '@/Components/Pagination';
import { formatDateTime } from '@/Utils/dateUtils';

export default function Index({ notifications }) {
    const markAsRead = (id) => {
        router.post(route('notifications.mark-read', id));
    };

    const markAllAsRead = () => {
        router.post(route('notifications.read-all'));
    };

    const deleteNotification = (id) => {
        if (confirm('Delete this notification?')) {
            router.delete(route('notifications.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout headerTitle="Your Notifications" breadcrumbs={[{ label: 'Notifications', active: true }]}>
            <Head title="Notifications" />

            <UnifiedToolbar
                actions={[
                    {
                        label: 'MARK ALL READ',
                        icon: 'fa-check-double',
                        onClick: markAllAsRead,
                    },
                ]}
            />

            <div className="py-0">
                <div className="card shadow-sm border-0 rounded-2xl overflow-hidden">
                    <div className="list-group list-group-flush">
                        {notifications.data.length > 0 ? (
                            notifications.data.map((n) => (
                                <div
                                    key={n.id}
                                    className={`list-group-item p-4 nyl-table-row ${!n.read_at ? 'bg-blue-50/30' : ''}`}
                                >
                                    <div className="d-flex justify-content-between align-items-start gap-3">
                                        <div className="d-flex gap-3 flex-grow-1 min-w-0">
                                            <div
                                                className={`rounded-xl d-flex align-items-center justify-content-center flex-shrink-0 ${!n.read_at ? 'bg-pink-100 text-pink-500' : 'bg-gray-100 text-gray-400'}`}
                                                style={{ width: '48px', height: '48px' }}
                                            >
                                                <i className={`fas ${n.data.icon || 'fa-bell'} fa-lg`}></i>
                                            </div>
                                            <div className="min-w-0">
                                                <h6
                                                    className={`nyl-table-cell-primary mb-1 ${!n.read_at ? 'fw-bold' : ''}`}
                                                >
                                                    {n.data.title || 'Notification'}
                                                </h6>
                                                <p className="nyl-table-cell-sub mb-0">
                                                    {n.data.message || 'You have a new update.'}
                                                </p>
                                                <small className="nyl-table-cell-sub d-block mt-2">
                                                    {formatDateTime(n.created_at)}
                                                </small>
                                            </div>
                                        </div>
                                        <TableActions
                                            actions={[
                                                !n.read_at && {
                                                    icon: 'fa-check',
                                                    label: 'Mark as read',
                                                    onClick: () => markAsRead(n.id),
                                                },
                                                {
                                                    icon: 'fa-trash-alt',
                                                    label: 'Delete',
                                                    color: 'danger',
                                                    onClick: () => deleteNotification(n.id),
                                                },
                                            ].filter(Boolean)}
                                        />
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="text-center py-16">
                                <i className="fas fa-bell-slash text-gray-200 text-6xl mb-4"></i>
                                <h4 className="text-gray-400 fw-bold">No notifications yet</h4>
                                <p className="text-gray-300">
                                    We&apos;ll let you know when something important happens.
                                </p>
                            </div>
                        )}
                    </div>
                </div>

                {notifications.links && notifications.links.length > 3 && (
                    <div className="mt-4 d-flex justify-content-center">
                        <Pagination links={notifications.links} />
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
