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
                <h2 className="fw-extrabold text-gray-900 mb-1 tracking-tighter">{config.welcome}</h2>
                <p className="text-muted mb-4 fw-medium">Please enter your details to sign in.</p>
                
                {status && (
                    <div className="alert alert-success border-0 shadow-sm extra-small fw-bold mb-4" role="alert">
                        {status}
                    </div>
                )}

                <form onSubmit={submit}>
                    <div className="mb-3">
                        <label htmlFor="email" className="form-label fw-medium text-muted">Email Address</label>
                        <input 
                            type="email" 
                            className={`form-control form-control-premium ${errors.email ? 'is-invalid' : ''}`}
                            id="email" 
                            name="email" 
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            placeholder="name@example.com"
                            required 
                            autoFocus
                        />
                        {errors.email && <div className="invalid-feedback extra-small fw-bold">{errors.email}</div>}
                    </div>
                    
                    <div className="mb-3">
                        <div className="d-flex justify-content-between align-items-center mb-1">
                            <label htmlFor="password" className="form-label fw-medium text-muted mb-0">Password</label>
                            {canResetPassword && (
                                <Link href={route('password.request')} className="small text-primary text-decoration-none fw-medium">
                                    Forgot password?
                                </Link>
                            )}
                        </div>
                        <input 
                            type="password" 
                            className={`form-control form-control-premium ${errors.password ? 'is-invalid' : ''}`}
                            id="password" 
                            name="password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            placeholder="••••••••"
                            required 
                            autoComplete="current-password"
                        />
                        {errors.password && <div className="invalid-feedback extra-small fw-bold">{errors.password}</div>}
                    </div>
                    
                    <div className="mb-4 form-check d-flex align-items-center gap-2">
                        <input 
                            type="checkbox" 
                            className="form-check-input mt-0" 
                            id="remember" 
                            name="remember"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                        />
                        <label className="form-check-label small text-muted fw-medium mb-0" htmlFor="remember">Remember me</label>
                    </div>
                    
                    <div className="d-grid gap-3">
                        <button type="submit" className="btn btn-primary btn-premium-lg fw-medium shadow-sm hover-lift" disabled={processing}>
                            {processing ? 'Signing in...' : 'Sign in to account'}
                        </button>
                        
                        <div className="auth-divider">
                            <hr />
                            <span className="text-muted fw-medium">or continue with</span>
                        </div>

                        <a 
                            href={route('auth.google', { role: authType })} 
                            className="btn btn-outline-light border text-gray-700 d-flex align-items-center justify-content-center btn-premium-lg hover-lift shadow-sm transition-all"
                        >
                            <img 
                                src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" 
                                alt="Google" 
                                className="me-2" 
                                style={{ width: '20px', height: '20px' }} 
                            />
                            <span className="fw-medium">Sign in with Google</span>
                        </a>
                    </div>
                </form>
                
                <div className="mt-5 text-center">
                    {isStaff ? (
                        <p className="text-muted small fw-medium mb-0">
                            Don't have an account? <Link href="#" className="text-primary fw-medium text-decoration-none">Contact Admin</Link>
                        </p>
                    ) : (
                        <p className="text-muted small fw-medium mb-0">
                            Don't have an account? <Link href={route('register')} className="text-primary fw-medium text-decoration-none">Sign up now</Link>
                        </p>
                    )}
                </div>
            </div>
        </AuthLayout>
    );
}
