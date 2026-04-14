import AuthLayout from '@/Layouts/AuthLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({ status, canResetPassword, authType = 'patient' }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const isStaff = authType === 'staff';
    const config = {
        title: isStaff ? 'Staff Portal' : 'Patient Portal',
        subtitle: isStaff ? 'Secure access for healthcare professionals' : 'Your health journey starts here',
        image: isStaff ? '/assets/img/auth/staff-auth-image.jpg' : '/assets/img/auth/patient-auth-image.jpg',
        welcome: isStaff ? 'Welcome back, Staff' : 'Welcome back, Patient'
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthLayout 
            image={config.image} 
            title={config.title} 
            subtitle={config.subtitle}
        >
            <Head title={`Log in - ${config.title}`} />

            <div className="login-form-container">
                <h2 className="fw-bold text-dark mb-1">{config.welcome}</h2>
                <p className="text-muted mb-4">Please enter your details to sign in.</p>
                
                {status && (
                    <div className="alert alert-success border-0 shadow-sm small mb-4" role="alert">
                        {status}
                    </div>
                )}

                <form onSubmit={submit}>
                    <div className="mb-3">
                        <label htmlFor="email" className="form-label small fw-bold text-muted">Email Address</label>
                        <input 
                            type="email" 
                            className={`form-control form-control-lg bg-light border-0 ${errors.email ? 'is-invalid' : ''}`}
                            id="email" 
                            name="email" 
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            placeholder="name@example.com"
                            required 
                            autoFocus
                        />
                        {errors.email && <div className="invalid-feedback">{errors.email}</div>}
                    </div>
                    
                    <div className="mb-3">
                        <div className="d-flex justify-content-between align-items-center mb-1">
                            <label htmlFor="password" className="form-label small fw-bold text-muted mb-0">Password</label>
                            {canResetPassword && (
                                <Link href={route('password.request')} className="small text-primary text-decoration-none fw-semibold">
                                    Forgot Password?
                                </Link>
                            )}
                        </div>
                        <input 
                            type="password" 
                            className={`form-control form-control-lg bg-light border-0 ${errors.password ? 'is-invalid' : ''}`}
                            id="password" 
                            name="password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            placeholder="••••••••"
                            required 
                            autoComplete="current-password"
                        />
                        {errors.password && <div className="invalid-feedback">{errors.password}</div>}
                    </div>
                    
                    <div className="mb-4 form-check">
                        <input 
                            type="checkbox" 
                            className="form-check-input" 
                            id="remember" 
                            name="remember"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                        />
                        <label className="form-check-label small text-muted" htmlFor="remember">Remember me</label>
                    </div>
                    
                    <div className="d-grid gap-3">
                        <button type="submit" className="btn btn-primary btn-lg fw-bold shadow-sm py-3" disabled={processing}>
                            {processing ? 'Signing in...' : 'Sign In'}
                        </button>
                        
                        <div className="text-center position-relative my-2">
                            <hr className="text-muted opacity-25" />
                            <span className="position-absolute top-50 start-50 translate-middle bg-white px-3 extra-small text-muted text-uppercase tracking-wider">OR</span>
                        </div>

                        <a 
                            href={route('auth.google', { role: authType })} 
                            className="btn btn-outline-light border text-dark d-flex align-items-center justify-content-center py-3 hover-lift shadow-sm transition-all"
                        >
                            <img 
                                src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" 
                                alt="Google" 
                                className="me-2" 
                                style={{ width: '20px', height: '20px' }} 
                            />
                            <span className="fw-semibold">SIGN IN WITH GOOGLE</span>
                        </a>
                    </div>
                </form>
                
                <div className="mt-5 text-center">
                    {isStaff ? (
                        <p className="text-muted small mb-0">
                            Don't have an account? <Link href="#" className="text-primary fw-bold text-decoration-none">Contact Admin</Link>
                        </p>
                    ) : (
                        <p className="text-muted small mb-0">
                            Don't have an account? <Link href={route('register')} className="text-primary fw-bold text-decoration-none">Sign Up</Link>
                        </p>
                    )}
                </div>
            </div>

            <style>{`
                .form-control-lg { font-size: 1rem; border-radius: 12px; }
                .btn-lg { border-radius: 12px; font-size: 0.95rem; }
                .extra-small { font-size: 0.7rem; }
                .tracking-wider { letter-spacing: 0.1em; }
                .hover-lift:hover { transform: translateY(-2px); }
                .bg-light { background-color: #f8f9fa !important; }
            `}</style>
        </AuthLayout>
    );
}
