import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Create({ roles }) {
    const { data, setData, post, processing, errors } = useForm({
        first_name: '',
        last_name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('users.store'));
    };

    return (
        <AuthenticatedLayout
            header="Create User"
        >
            <Head title="Create User" />

            <PageHeader
                title="Create User"
                breadcrumbs={[
                    { label: 'Dashboard', url: route('dashboard') },
                    { label: 'Users', url: route('users.index') },
                    { label: 'Create User', active: true }
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
                                <label className="form-label font-bold text-gray-500">Password</label>
                                <input 
                                    type="password" 
                                    className={`form-control border-0 bg-light rounded-xl ${errors.password ? 'is-invalid' : ''}`}
                                    value={data.password}
                                    onChange={e => setData('password', e.target.value)}
                                    placeholder="Leave blank for random temporary password"
                                />
                                {errors.password && <div className="invalid-feedback">{errors.password}</div>}
                            </div>

                            <div className="mt-4">
                                <label className="form-label font-bold text-gray-500">Confirm Password</label>
                                <input 
                                    type="password" 
                                    className={`form-control border-0 bg-light rounded-xl ${errors.password_confirmation ? 'is-invalid' : ''}`}
                                    value={data.password_confirmation}
                                    onChange={e => setData('password_confirmation', e.target.value)}
                                />
                                {errors.password_confirmation && <div className="invalid-feedback">{errors.password_confirmation}</div>}
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
                                        <option key={r.role_id} value={r.role_name}>{r.role_name.charAt(0).toUpperCase() + r.role_name.slice(1)}</option>
                                    ))}
                                </select>
                            </div>

                            <div className="mt-5 text-end">
                                <button type="submit" className="btn btn-primary rounded-pill px-5 py-2 font-bold shadow-lg" disabled={processing}>
                                    {processing ? 'Saving...' : 'Create User Account'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <UnifiedToolbar 
                actions={
                    <div className="d-flex align-items-center gap-2">
                        <Link href={route('users.index')} className="btn btn-light rounded-pill px-4 py-2 fw-extrabold small border shadow-sm">
                            <i className="fas fa-list me-1"></i> Back to Registry
                        </Link>
                    </div>
                }
            />
        </AuthenticatedLayout>
    );
}
