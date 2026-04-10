import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';

export default function Edit({ user, roles }) {
    const { data, setData, put, processing, errors } = useForm({
        first_name: user.first_name || '',
        last_name: user.last_name || '',
        email: user.email || '',
        role: user.role || '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('users.update', user.user_id));
    };

    return (
        <AuthenticatedLayout
            header="Edit User"
        >
            <Head title={`Edit User: ${user.first_name}`} />

            <PageHeader 
                title={`Edit ${user.first_name} ${user.last_name}`}
                breadcrumbs={[
                    { label: 'Dashboard', url: route('dashboard') },
                    { label: 'Users', url: route('users.index') },
                    { label: 'Edit User', active: true }
                ]}
            />

            <div className="py-4 max-w-2xl mx-auto">
                <div className="card shadow-sm border-0 rounded-2xl bg-white overflow-hidden">
                    <div className="card-body p-5">
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="row g-3">
                                <div className="col-md-6">
                                    <label className="form-label font-bold text-gray-500">First Name</label>
                                    <input 
                                        type="text" 
                                        className={`form-control border-0 bg-light rounded-xl ${errors.first_name ? 'is-invalid' : ''}`}
                                        value={data.first_name}
                                        onChange={e => setData('first_name', e.target.value)}
                                    />
                                    {errors.first_name && <div className="invalid-feedback">{errors.first_name}</div>}
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label font-bold text-gray-500">Last Name</label>
                                    <input 
                                        type="text" 
                                        className={`form-control border-0 bg-light rounded-xl ${errors.last_name ? 'is-invalid' : ''}`}
                                        value={data.last_name}
                                        onChange={e => setData('last_name', e.target.value)}
                                    />
                                    {errors.last_name && <div className="invalid-feedback">{errors.last_name}</div>}
                                </div>
                            </div>

                            <div className="mt-4">
                                <label className="form-label font-bold text-gray-500">Email Address</label>
                                <input 
                                    type="email" 
                                    className={`form-control border-0 bg-light rounded-xl ${errors.email ? 'is-invalid' : ''}`}
                                    value={data.email}
                                    onChange={e => setData('email', e.target.value)}
                                />
                                {errors.email && <div className="invalid-feedback">{errors.email}</div>}
                            </div>

                            <div className="mt-4">
                                <label className="form-label font-bold text-gray-500">Role</label>
                                <select 
                                    className="form-select border-0 bg-light rounded-xl"
                                    value={data.role}
                                    onChange={e => setData('role', e.target.value)}
                                >
                                    <option value="">Select a role...</option>
                                    {roles.map(r => (
                                        <option key={r.role_id} value={r.role_name}>{r.role_name.replace('_', ' ').charAt(0).toUpperCase() + r.role_name.slice(1)}</option>
                                    ))}
                                </select>
                            </div>

                            <div className="mt-5 d-flex justify-content-end gap-2">
                                <Link href={route('users.index')} className="btn btn-light rounded-pill px-4 py-2 font-bold">
                                    Cancel
                                </Link>
                                <button type="submit" className="btn btn-primary rounded-pill px-5 py-2 font-bold shadow-lg" disabled={processing}>
                                    {processing ? 'Saving...' : 'Update User'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
