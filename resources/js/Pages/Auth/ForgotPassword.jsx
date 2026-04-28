import AuthLayout from '@/Layouts/AuthLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('password.email'));
    };

    return (
        <AuthLayout 
            image="/assets/img/auth/patient-auth-image.jpg" 
            title="Password Recovery" 
            subtitle="Regain access to your account securely"
        >
            <Head title="Forgot Password" />

            <div className="login-form-container">
                <h2 className="fw-extrabold text-gray-900 mb-1 tracking-tighter">Reset Password</h2>
                <p className="text-muted mb-4 fw-medium">
                    Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
                </p>
                
                {status && (
                    <div className="alert alert-success border-0 shadow-sm extra-small fw-bold mb-4" role="alert">
                        {status}
                    </div>
                )}

                <form onSubmit={submit}>
                    <div className="mb-4">
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
                    
                    <div className="d-grid gap-3">
                        <button type="submit" className="btn btn-primary btn-premium-lg fw-medium shadow-sm hover-lift" disabled={processing}>
                            {processing ? 'Sending...' : 'Email Password Reset Link'}
                        </button>
                    </div>
                </form>

                <div className="mt-5 text-center">
                    <p className="text-muted small fw-medium mb-0">
                        Remembered your password? <Link href={route('login')} className="text-primary fw-medium text-decoration-none">Back to login</Link>
                    </p>
                </div>
            </div>
        </AuthLayout>
    );
}
