import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Log in" />

            <div className="col-12 col-md-6 col-lg-5">
                <div className="card shadow border-0">
                    <div className="card-header">
                        <h4 className="mb-0 text-white">Login to Nyalife HMS</h4>
                    </div>
                    <div className="card-body p-4">
                        
                        {status && (
                            <div className="alert alert-success alert-dismissible fade show" role="alert">
                                {status}
                                <button type="button" className="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        )}

                        <form onSubmit={submit}>
                            <div className="mb-4 mt-3">
                                <label htmlFor="email" className="form-label">Email Address</label>
                                <input 
                                    type="email" 
                                    className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                                    id="email" 
                                    name="email" 
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    required 
                                    autoFocus
                                />
                                {errors.email && <div className="invalid-feedback">{errors.email}</div>}
                            </div>
                            
                            <div className="mb-4">
                                <label htmlFor="password" className="form-label">Password</label>
                                <input 
                                    type="password" 
                                    className={`form-control ${errors.password ? 'is-invalid' : ''}`}
                                    id="password" 
                                    name="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    required 
                                    autoComplete="current-password"
                                />
                                {errors.password && <div className="invalid-feedback">{errors.password}</div>}
                            </div>
                            
                            <div className="mb-3 form-check">
                                <input 
                                    type="checkbox" 
                                    className="form-check-input" 
                                    id="remember" 
                                    name="remember"
                                    checked={data.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                />
                                <label className="form-check-label" htmlFor="remember">Remember me</label>
                            </div>
                            
                            <div className="d-grid gap-2">
                                <button type="submit" className="btn btn-primary" disabled={processing}>
                                    {processing ? 'Logging in...' : 'Login'}
                                </button>
                            </div>
                        </form>
                        
                        <div className="mt-4 text-center">
                            <p className="mb-2">Don't have an account? <Link href={route('register')}>Register here</Link></p>
                            {canResetPassword && (
                                <p className="mb-0"><Link href={route('password.request')}>Forgot your password?</Link></p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </GuestLayout>
    );
}
