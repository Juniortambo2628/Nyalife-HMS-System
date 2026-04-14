import AuthLayout from '@/Layouts/AuthLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState, useEffect, useRef, useCallback } from 'react';
import axios from 'axios';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        date_of_birth: '',
        gender: '',
        blood_group: '',
        height: '',
        weight: '',
        address: '',
        username: '',
        password: '',
        password_confirmation: '',
        terms: false,
    });

    const [step, setStep] = useState(1);
    const [showGuestModal, setShowGuestModal] = useState(false);
    const [guestData, setGuestData] = useState(null);
    const [checkingEmail, setCheckingEmail] = useState(false);
    const [emailChecked, setEmailChecked] = useState(false);
    const checkTimeoutRef = useRef(null);

    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        const name = params.get('name');
        const email = params.get('email');
        const phone = params.get('phone');

        if (name) {
            const parts = name.trim().split(' ');
            setData(prev => ({
                ...prev,
                first_name: parts[0] || '',
                last_name: parts.slice(1).join(' ') || '',
                email: email || prev.email,
                phone: phone || prev.phone,
            }));
            if (email) setEmailChecked(true);
        } else if (email || phone) {
            setData(prev => ({
                ...prev,
                email: email || prev.email,
                phone: phone || prev.phone,
            }));
            if (email) setEmailChecked(true);
        }
    }, []);

    const checkGuestEmail = useCallback(async (email) => {
        if (!email || emailChecked) return;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) return;

        setCheckingEmail(true);
        try {
            const response = await axios.post('/check-guest-data', { email });
            if (response.data.found) {
                setGuestData(response.data.data);
                setShowGuestModal(true);
            }
            setEmailChecked(true);
        } catch (error) {
            if (error.response?.status !== 404) console.error('Error checking guest data:', error);
            setEmailChecked(true);
        } finally {
            setCheckingEmail(false);
        }
    }, [emailChecked]);

    const handleEmailBlur = () => {
        if (checkTimeoutRef.current) clearTimeout(checkTimeoutRef.current);
        checkTimeoutRef.current = setTimeout(() => {
            checkGuestEmail(data.email);
        }, 300);
    };

    const applyGuestData = () => {
        if (guestData) {
            setData(prev => ({
                ...prev,
                first_name: guestData.first_name || prev.first_name,
                last_name: guestData.last_name || prev.last_name,
                phone: guestData.phone || prev.phone,
            }));
        }
        setShowGuestModal(false);
    };

    const onHandleChange = (event) => {
        const { name, value, type, checked } = event.target;
        setData(name, type === 'checkbox' ? checked : value);
        if (name === 'email') setEmailChecked(false);
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout 
            image="/assets/img/auth/patient-auth-image.jpg"
            title="Join Nyalife"
            subtitle="Expert care for every stage of your life"
        >
            <Head title="Create Account" />

            <div className="registration-form-container">
                <h2 className="fw-bold text-dark mb-1">Create Account</h2>
                <p className="text-muted mb-4">Step {step} of 2: {step === 1 ? 'Personal Details' : 'Account Setup'}</p>
                
                <div className="progress mb-4" style={{ height: '6px', borderRadius: '3px' }}>
                    <div 
                        className="progress-bar bg-primary" 
                        role="progressbar" 
                        style={{ width: step === 1 ? '50%' : '100%', transition: 'width 0.4s ease' }}
                    ></div>
                </div>

                <form onSubmit={submit}>
                    {step === 1 ? (
                        <div className="step-1">
                            <div className="row g-3 mb-3">
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold text-muted mb-1">First Name</label>
                                    <input type="text" className={`form-control bg-light border-0 ${errors.first_name ? 'is-invalid' : ''}`} name="first_name" value={data.first_name} onChange={onHandleChange} required />
                                    {errors.first_name && <div className="invalid-feedback">{errors.first_name}</div>}
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold text-muted mb-1">Last Name</label>
                                    <input type="text" className={`form-control bg-light border-0 ${errors.last_name ? 'is-invalid' : ''}`} name="last_name" value={data.last_name} onChange={onHandleChange} required />
                                    {errors.last_name && <div className="invalid-feedback">{errors.last_name}</div>}
                                </div>
                            </div>

                            <div className="mb-3">
                                <label className="form-label small fw-bold text-muted mb-1">Email</label>
                                <div className="position-relative">
                                    <input type="email" className={`form-control bg-light border-0 ${errors.email ? 'is-invalid' : ''}`} name="email" value={data.email} onChange={onHandleChange} onBlur={handleEmailBlur} required />
                                    {checkingEmail && <div className="position-absolute end-0 top-50 translate-middle-y me-3 spinner-border spinner-border-sm text-primary"></div>}
                                </div>
                                {errors.email && <div className="invalid-feedback d-block">{errors.email}</div>}
                            </div>

                            <div className="row g-3 mb-3">
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold text-muted mb-1">Phone</label>
                                    <input type="text" className={`form-control bg-light border-0 ${errors.phone ? 'is-invalid' : ''}`} name="phone" value={data.phone} onChange={onHandleChange} required />
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold text-muted mb-1">Gender</label>
                                    <select className={`form-select bg-light border-0 ${errors.gender ? 'is-invalid' : ''}`} name="gender" value={data.gender} onChange={onHandleChange}>
                                        <option value="">Select</option>
                                        <option value="female">Female</option>
                                        <option value="male">Male</option>
                                    </select>
                                </div>
                            </div>

                            <div className="d-grid mt-4">
                                <button type="button" className="btn btn-primary btn-lg fw-bold shadow-sm" onClick={() => setStep(2)}>
                                    Continue <i className="fas fa-arrow-right ms-2 small"></i>
                                </button>
                            </div>
                        </div>
                    ) : (
                        <div className="step-2">
                            <div className="mb-3">
                                <label className="form-label small fw-bold text-muted mb-1">Username</label>
                                <input type="text" className={`form-control bg-light border-0 ${errors.username ? 'is-invalid' : ''}`} name="username" value={data.username} onChange={onHandleChange} required />
                            </div>

                            <div className="row g-3 mb-4">
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold text-muted mb-1">Password</label>
                                    <input type="password" className={`form-control bg-light border-0 ${errors.password ? 'is-invalid' : ''}`} name="password" value={data.password} onChange={onHandleChange} required />
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold text-muted mb-1">Confirm</label>
                                    <input type="password" className={`form-control bg-light border-0 ${errors.password_confirmation ? 'is-invalid' : ''}`} name="password_confirmation" value={data.password_confirmation} onChange={onHandleChange} required />
                                </div>
                            </div>

                            <div className="mb-4 form-check">
                                <input type="checkbox" className="form-check-input" id="terms" name="terms" checked={data.terms} onChange={onHandleChange} required />
                                <label className="form-check-label small text-muted" htmlFor="terms">
                                    I agree to the <Link href={route('terms-of-service')} className="fw-bold text-primary">Terms</Link> & <Link href={route('privacy-policy')} className="fw-bold text-primary">Privacy</Link>
                                </label>
                            </div>

                            <div className="d-flex gap-2">
                                <button type="button" className="btn btn-outline-secondary btn-lg flex-grow-1" onClick={() => setStep(1)}>Back</button>
                                <button type="submit" className="btn btn-primary btn-lg flex-grow-1 fw-bold shadow-sm" disabled={processing}>
                                    Register
                                </button>
                            </div>
                        </div>
                    )}
                </form>

                <div className="mt-5 text-center">
                    <p className="text-muted small mb-0">
                        Already have an account? <Link href={route('login.patient')} className="text-primary fw-bold text-decoration-none">Sign In</Link>
                    </p>
                </div>
            </div>

            {showGuestModal && (
                <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 rounded-4 shadow">
                            <div className="modal-body p-5 text-center">
                                <div className="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style={{ width: '60px', height: '60px' }}>
                                    <i className="fas fa-user-check fa-lg"></i>
                                </div>
                                <h4 className="fw-bold mb-3">Welcome Back!</h4>
                                <p className="text-muted mb-4 small">We found an existing booking. Auto-fill details for {guestData?.first_name} {guestData?.last_name}?</p>
                                <div className="d-grid gap-2">
                                    <button type="button" className="btn btn-primary rounded-3 fw-bold" onClick={applyGuestData}>Yes, please</button>
                                    <button type="button" className="btn btn-light rounded-3 text-muted" onClick={() => setShowGuestModal(false)}>No, thanks</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            <style>{`
                .form-control, .form-select, .btn { border-radius: 12px; }
                .bg-light { background-color: #f8f9fa !important; }
            `}</style>
        </AuthLayout>
    );
}
