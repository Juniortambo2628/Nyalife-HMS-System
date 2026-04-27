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
                <h2 className="fw-extrabold text-gray-900 mb-1 tracking-tighter">Create Account</h2>
                <p className="text-muted mb-4 fw-medium">Step {step} of 2: {step === 1 ? 'Personal Details' : 'Account Setup'}</p>
                
                <div className="progress mb-4" style={{ height: '6px', borderRadius: '3px' }}>
                    <div 
                        className="progress-bar bg-primary" 
                        role="progressbar" 
                        style={{ width: step === 1 ? '50%' : '100%', transition: 'width 0.4s ease' }}
                    ></div>
                </div>

                <form onSubmit={submit}>
                    {step === 1 ? (
                        <div className="step-1 animate-in fade-in slide-in-from-right-4 duration-500">
                            <div className="row g-3 mb-3">
                                <div className="col-md-6">
                                    <label className="form-label extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-1">First Name</label>
                                    <input type="text" className={`form-control form-control-premium ${errors.first_name ? 'is-invalid' : ''}`} name="first_name" value={data.first_name} onChange={onHandleChange} required />
                                    {errors.first_name && <div className="invalid-feedback extra-small fw-bold">{errors.first_name}</div>}
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-1">Last Name</label>
                                    <input type="text" className={`form-control form-control-premium ${errors.last_name ? 'is-invalid' : ''}`} name="last_name" value={data.last_name} onChange={onHandleChange} required />
                                    {errors.last_name && <div className="invalid-feedback extra-small fw-bold">{errors.last_name}</div>}
                                </div>
                            </div>

                            <div className="mb-3">
                                <label className="form-label extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-1">Email</label>
                                <div className="position-relative">
                                    <input type="email" className={`form-control form-control-premium ${errors.email ? 'is-invalid' : ''}`} name="email" value={data.email} onChange={onHandleChange} onBlur={handleEmailBlur} required />
                                    {checkingEmail && <div className="position-absolute end-0 top-50 translate-middle-y me-3 spinner-border spinner-border-sm text-primary"></div>}
                                </div>
                                {errors.email && <div className="invalid-feedback d-block extra-small fw-bold">{errors.email}</div>}
                            </div>

                            <div className="row g-3 mb-3">
                                <div className="col-md-6">
                                    <label className="form-label extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-1">Phone</label>
                                    <input type="text" className={`form-control form-control-premium ${errors.phone ? 'is-invalid' : ''}`} name="phone" value={data.phone} onChange={onHandleChange} required />
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-1">Gender</label>
                                    <select className={`form-select form-control-premium ${errors.gender ? 'is-invalid' : ''}`} name="gender" value={data.gender} onChange={onHandleChange}>
                                        <option value="">Select</option>
                                        <option value="female">Female</option>
                                        <option value="male">Male</option>
                                    </select>
                                </div>
                            </div>

                            <div className="d-grid mt-4">
                                <button type="button" className="btn btn-primary btn-premium-lg fw-extrabold shadow-sm hover-lift" onClick={() => setStep(2)}>
                                    CONTINUE TO NEXT STEP <i className="fas fa-arrow-right ms-2 small"></i>
                                </button>
                            </div>
                        </div>
                    ) : (
                        <div className="step-2 animate-in fade-in slide-in-from-right-4 duration-500">
                            <div className="mb-3">
                                <label className="form-label extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-1">Username</label>
                                <input type="text" className={`form-control form-control-premium ${errors.username ? 'is-invalid' : ''}`} name="username" value={data.username} onChange={onHandleChange} required />
                                {errors.username && <div className="invalid-feedback d-block extra-small fw-bold">{errors.username}</div>}
                            </div>

                            <div className="row g-3 mb-4">
                                <div className="col-md-6">
                                    <label className="form-label extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-1">Password</label>
                                    <input type="password" className={`form-control form-control-premium ${errors.password ? 'is-invalid' : ''}`} name="password" value={data.password} onChange={onHandleChange} required />
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-1">Confirm</label>
                                    <input type="password" className={`form-control form-control-premium ${errors.password_confirmation ? 'is-invalid' : ''}`} name="password_confirmation" value={data.password_confirmation} onChange={onHandleChange} required />
                                </div>
                            </div>

                            <div className="mb-4 form-check d-flex align-items-center gap-2">
                                <input type="checkbox" className="form-check-input mt-0" id="terms" name="terms" checked={data.terms} onChange={onHandleChange} required />
                                <label className="form-check-label extra-small text-muted fw-bold mb-0" htmlFor="terms">
                                    I AGREE TO THE <Link href={route('terms-of-service')} className="fw-extrabold text-primary text-decoration-none">TERMS</Link> & <Link href={route('privacy-policy')} className="fw-extrabold text-primary text-decoration-none">PRIVACY POLICY</Link>
                                </label>
                            </div>

                            <div className="d-flex gap-2">
                                <button type="button" className="btn btn-outline-light border text-gray-600 btn-premium-lg flex-grow-1 fw-bold" onClick={() => setStep(1)}>BACK</button>
                                <button type="submit" className="btn btn-primary btn-premium-lg flex-grow-1 fw-extrabold shadow-sm hover-lift" disabled={processing}>
                                    COMPLETE REGISTRATION
                                </button>
                            </div>
                        </div>
                    )}
                </form>

                <div className="mt-5 text-center">
                    <p className="text-muted extra-small fw-bold mb-0">
                        ALREADY HAVE AN ACCOUNT? <Link href={route('login.patient')} className="text-primary fw-extrabold text-decoration-none">SIGN IN HERE</Link>
                    </p>
                </div>
            </div>

            {showGuestModal && (
                <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1050 }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 rounded-2xl shadow-2xl overflow-hidden">
                            <div className="modal-body p-5 text-center animate-in zoom-in duration-300">
                                <div className="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-4 shadow-inner" style={{ width: '64px', height: '64px' }}>
                                    <i className="fas fa-user-check fa-lg"></i>
                                </div>
                                <h3 className="fw-extrabold text-gray-900 mb-2 tracking-tighter">Welcome Back!</h3>
                                <p className="text-muted mb-4 fw-medium">We found an existing record. Auto-fill details for <span className="text-dark fw-bold">{guestData?.first_name} {guestData?.last_name}</span>?</p>
                                <div className="d-grid gap-2">
                                    <button type="button" className="btn btn-primary rounded-pill py-2.5 fw-extrabold shadow-sm hover-lift" onClick={applyGuestData}>Yes, please</button>
                                    <button type="button" className="btn btn-light rounded-pill py-2.5 text-muted fw-bold" onClick={() => setShowGuestModal(false)}>No, thanks</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AuthLayout>
    );
}
