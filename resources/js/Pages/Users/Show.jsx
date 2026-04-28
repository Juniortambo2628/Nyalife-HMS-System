import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Show({ user }) {
    return (
        <AuthenticatedLayout
            header="User Profile"
        >
            <Head title={`User: ${user.first_name || 'Profile'}`} />

            <PageHeader 
                title={`${user.first_name} ${user.last_name}`}
                breadcrumbs={[
                    { label: 'Admin', url: route('dashboard') },
                    { label: 'Users Registry', url: route('users.index') },
                    { label: 'Staff Profile', active: true }
                ]}
            />

            <div className="py-4 h-auto">
                <div className="row g-4">
                    <div className="col-md-4">
                        <div className="card shadow-sm border-0 rounded-2xl bg-white p-5 text-center">
                            <div className="avatar-xl bg-pink-100 text-pink-500 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style={{ width: '100px', height: '100px' }}>
                                <span className="display-4 fw-bold">{user.first_name?.charAt(0)}</span>
                            </div>
                            <h4 className="fw-bold mb-1">{user.first_name} {user.last_name}</h4>
                            <div className="text-pink-500 font-bold text-xs uppercase tracking-widest mb-4">{user.role}</div>
                            <div className="badge bg-light text-success border border-success-subtle rounded-pill px-3 py-2 mb-4">Account Active</div>
                        </div>
                    </div>
                    <div className="col-md-8">
                        <div className="card shadow-sm border-0 rounded-2xl bg-white p-5">
                            <h5 className="fw-bold mb-4">Account Information</h5>
                            <div className="row g-4">
                                <div className="col-6">
                                    <small className="text-gray-400 font-bold uppercase text-xs d-block mb-1">Email</small>
                                    <p className="fw-medium">{user.email}</p>
                                </div>
                                <div className="col-6">
                                    <small className="text-gray-400 font-bold uppercase text-xs d-block mb-1">Joined Date</small>
                                    <p className="fw-medium">{new Date(user.created_at).toLocaleDateString()}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <UnifiedToolbar 
                actions={[
                    { 
                        label: 'EDIT PERMISSIONS', 
                        icon: 'fa-user-shield', 
                        href: route('users.edit', user.user_id),
                        color: 'primary'
                    },
                    { 
                        label: 'USERS REGISTRY', 
                        icon: 'fa-arrow-left', 
                        href: route('users.index'),
                        color: 'gray'
                    }
                ]}
            />
        </AuthenticatedLayout>
    );
}
