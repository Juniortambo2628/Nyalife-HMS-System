import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';

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
        <AuthenticatedLayout
            header="Notifications"
        >
            <Head title="Notifications" />

            <PageHeader 
                title="Your Notifications"
                breadcrumbs={[{ label: 'Notifications', active: true }]}
                actions={
                    <button onClick={markAllAsRead} className="btn btn-outline-primary rounded-pill px-4 font-bold shadow-sm">
                        <i className="fas fa-check-double me-2"></i>Mark All as Read
                    </button>
                }
            />

            <div className="py-0">
                <div className="card shadow-sm border-0 rounded-2xl overflow-hidden">
                    <div className="list-group list-group-flush">
                        {notifications.data.length > 0 ? (
                            notifications.data.map((n) => (
                                <div key={n.id} className={`list-group-item p-4 hover:bg-gray-50 transition-colors ${!n.read_at ? 'bg-blue-50/30' : ''}`}>
                                    <div className="d-flex justify-content-between align-items-start">
                                        <div className="d-flex gap-3">
                                            <div className={`rounded-xl p-3 flex items-center justify-center ${!n.read_at ? 'bg-pink-100 text-pink-500' : 'bg-gray-100 text-gray-400'}`} style={{ width: '48px', height: '48px' }}>
                                                <i className={`fas ${n.data.icon || 'fa-bell'} fa-lg`}></i>
                                            </div>
                                            <div>
                                                <h6 className={`mb-1 ${!n.read_at ? 'fw-bold' : ''}`}>{n.data.title || 'Notification'}</h6>
                                                <p className="text-muted small mb-0">{n.data.message || 'You have a new update.'}</p>
                                                <small className="text-gray-400 mt-2 d-block">{new Date(n.created_at).toLocaleString()}</small>
                                            </div>
                                        </div>
                                        <div className="d-flex gap-2">
                                            {!n.read_at && (
                                                <button onClick={() => markAsRead(n.id)} className="btn btn-sm btn-light rounded-pill px-3" title="Mark as Read">
                                                    Mark Read
                                                </button>
                                            )}
                                            <button onClick={() => deleteNotification(n.id)} className="btn btn-sm text-danger hover:bg-danger/10 rounded-circle" style={{ width: '32px', height: '32px' }}>
                                                <i className="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="text-center py-16">
                                <i className="fas fa-bell-slash text-gray-200 text-6xl mb-4"></i>
                                <h4 className="text-gray-400 font-bold">No notifications yet</h4>
                                <p className="text-gray-300">We'll let you know when something important happens.</p>
                            </div>
                        )}
                    </div>
                </div>

                {/* Pagination */}
                {notifications.links && notifications.links.length > 3 && (
                    <div className="mt-4 flex justify-center">
                        {/* Standard Pagination Component could go here */}
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
