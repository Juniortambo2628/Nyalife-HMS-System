import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
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
        // Parse URL query parameters
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
            // If coming from guest form with prefilled data, mark as already checked
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

    // Check email for existing guest data
    const checkGuestEmail = useCallback(async (email) => {
        if (!email || emailChecked) return;
        
        // Basic email validation
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
            // 404 means no guest data found, which is fine
            if (error.response?.status !== 404) {
                console.error('Error checking guest data:', error);
            }
            setEmailChecked(true);
        } finally {
            setCheckingEmail(false);
        }
    }, [emailChecked]);

    // Handle email field blur with debounce
    const handleEmailBlur = () => {
        if (checkTimeoutRef.current) {
            clearTimeout(checkTimeoutRef.current);
        }
        checkTimeoutRef.current = setTimeout(() => {
            checkGuestEmail(data.email);
        }, 300);
    };

    // Apply guest data to form
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
        
        // Reset email checked state when email changes
        if (name === 'email') {
            setEmailChecked(false);
        }
    };

    const submit = (e) => {
        e.preventDefault();
        
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    const nextStep = () => {
        setStep(2);
    };

    const prevStep = () => {
        setStep(1);
    };

    // Calculate progress width
    const progressWidth = step === 1 ? '50%' : '100%';

    return (
        <GuestLayout>
            <Head title="Register" />

            <div className="col-12 col-md-10 col-lg-8">
                <div className="card shadow border-0">
                    <div className="card-header">
                        <h4 className="mb-0 text-white">Register for Nyalife HMS</h4>
                    </div>
                    <div className="card-body p-4">
                        
                        {/* Step Indicator */}
                        <div className="registration-header d-flex flex-column align-items-stretch mb-3">
                            <div className="step-dots d-flex justify-content-center mb-2">
                                <span className={`step-dot ${step >= 1 ? 'active' : ''} me-2`}>1</span>
                                <span className={`step-dot ${step >= 2 ? 'active' : ''}`}>2</span>
                            </div>
                            <div className="register-progress-wrapper" style={{height: '4px', background: 'rgba(0,0,0,0.04)', borderRadius: '2px', overflow: 'hidden'}}>
                                <div className="register-progress-bar" style={{width: progressWidth, background: 'linear-gradient(90deg,#20c997,#ff1493)', height: '100%', transition: 'width 0.4s ease'}}></div>
                            </div>
                        </div>

                        <form onSubmit={submit}>
                            <div className="registration-steps">
                                {/* Step 1: Personal Information */}
                                <div className={step === 1 ? 'd-block' : 'd-none'}>
                                    <h5 className="mb-3">Personal Information</h5>
                                    <div className="row mb-3">
                                        <div className="col-md-6">
                                            <label htmlFor="first_name" className="form-label">First Name</label>
                                            <input type="text" className={`form-control ${errors.first_name ? 'is-invalid' : ''}`} id="first_name" name="first_name" value={data.first_name} onChange={onHandleChange} required />
                                            {errors.first_name && <div className="invalid-feedback">{errors.first_name}</div>}
                                        </div>
                                        <div className="col-md-6">
                                            <label htmlFor="last_name" className="form-label">Last Name</label>
                                            <input type="text" className={`form-control ${errors.last_name ? 'is-invalid' : ''}`} id="last_name" name="last_name" value={data.last_name} onChange={onHandleChange} required />
                                            {errors.last_name && <div className="invalid-feedback">{errors.last_name}</div>}
                                        </div>
                                    </div>

                                    <div className="row mb-3">
                                        <div className="col-md-6">
                                            <label htmlFor="email" className="form-label">Email</label>
                                            <div className="position-relative">
                                                <input 
                                                    type="email" 
                                                    className={`form-control ${errors.email ? 'is-invalid' : ''}`} 
                                                    id="email" 
                                                    name="email" 
                                                    value={data.email} 
                                                    onChange={onHandleChange} 
                                                    onBlur={handleEmailBlur}
                                                    required 
                                                />
                                                {checkingEmail && (
                                                    <div className="position-absolute end-0 top-50 translate-middle-y me-3">
                                                        <div className="spinner-border spinner-border-sm text-primary" role="status">
                                                            <span className="visually-hidden">Checking...</span>
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                            {errors.email && <div className="invalid-feedback d-block">{errors.email}</div>}
                                        </div>
                                        <div className="col-md-6">
                                            <label htmlFor="phone" className="form-label">Phone</label>
                                            <input type="text" className={`form-control ${errors.phone ? 'is-invalid' : ''}`} id="phone" name="phone" value={data.phone} onChange={onHandleChange} required />
                                            {errors.phone && <div className="invalid-feedback">{errors.phone}</div>}
                                        </div>
                                    </div>

                                    <div className="row mb-3">
                                        <div className="col-md-6">
                                            <label htmlFor="date_of_birth" className="form-label">Date of Birth</label>
                                            <input type="date" className={`form-control ${errors.date_of_birth ? 'is-invalid' : ''}`} id="date_of_birth" name="date_of_birth" value={data.date_of_birth} onChange={onHandleChange} />
                                            {errors.date_of_birth && <div className="invalid-feedback">{errors.date_of_birth}</div>}
                                        </div>
                                        <div className="col-md-6">
                                            <label htmlFor="gender" className="form-label">Gender</label>
                                            <select className={`form-select ${errors.gender ? 'is-invalid' : ''}`} id="gender" name="gender" value={data.gender} onChange={onHandleChange}>
                                                <option value="">Select Gender</option>
                                                <option value="female">Female</option>
                                                <option value="male">Male</option>
                                                <option value="other">Other</option>
                                            </select>
                                            {errors.gender && <div className="invalid-feedback">{errors.gender}</div>}
                                        </div>
                                    </div>

                                    <div className="row mb-3">
                                        <div className="col-md-4">
                                            <label htmlFor="blood_group" className="form-label">Blood Group</label>
                                            <select className={`form-select ${errors.blood_group ? 'is-invalid' : ''}`} id="blood_group" name="blood_group" value={data.blood_group} onChange={onHandleChange}>
                                                <option value="">Select Blood Group</option>
                                                <option value="A+">A+</option>
                                                <option value="A-">A-</option>
                                                <option value="B+">B+</option>
                                                <option value="B-">B-</option>
                                                <option value="AB+">AB+</option>
                                                <option value="AB-">AB-</option>
                                                <option value="O+">O+</option>
                                                <option value="O-">O-</option>
                                            </select>
                                            {errors.blood_group && <div className="invalid-feedback">{errors.blood_group}</div>}
                                        </div>
                                        <div className="col-md-4">
                                            <label htmlFor="height" className="form-label">Height (cm)</label>
                                            <input type="number" step="0.01" className={`form-control ${errors.height ? 'is-invalid' : ''}`} id="height" name="height" value={data.height} onChange={onHandleChange} />
                                            {errors.height && <div className="invalid-feedback">{errors.height}</div>}
                                        </div>
                                        <div className="col-md-4">
                                            <label htmlFor="weight" className="form-label">Weight (kg)</label>
                                            <input type="number" step="0.01" className={`form-control ${errors.weight ? 'is-invalid' : ''}`} id="weight" name="weight" value={data.weight} onChange={onHandleChange} />
                                            {errors.weight && <div className="invalid-feedback">{errors.weight}</div>}
                                        </div>
                                    </div>

                                    <div className="mb-3">
                                        <label htmlFor="address" className="form-label">Address</label>
                                        <textarea className={`form-control ${errors.address ? 'is-invalid' : ''}`} id="address" name="address" rows="2" value={data.address} onChange={onHandleChange}></textarea>
                                        {errors.address && <div className="invalid-feedback">{errors.address}</div>}
                                    </div>

                                    <div className="d-flex justify-content-end">
                                        <button type="button" className="btn btn-outline-secondary me-2" onClick={nextStep}>Next</button>
                                    </div>
                                </div>

                                {/* Step 2: Account Information */}
                                <div className={step === 2 ? 'd-block' : 'd-none'}>
                                    <h5 className="mt-3 mb-3">Account Information</h5>
                                    
                                    <div className="mb-3">
                                        <label htmlFor="username" className="form-label">Username</label>
                                        <input type="text" className={`form-control ${errors.username ? 'is-invalid' : ''}`} id="username" name="username" value={data.username} onChange={onHandleChange} required />
                                        {errors.username && <div className="invalid-feedback">{errors.username}</div>}
                                    </div>

                                    <div className="row mb-3">
                                        <div className="col-md-6">
                                            <label htmlFor="password" className="form-label">Password</label>
                                            <input type="password" className={`form-control ${errors.password ? 'is-invalid' : ''}`} id="password" name="password" value={data.password} onChange={onHandleChange} required autoComplete="new-password" />
                                            <div className="form-text">Password must be at least 8 characters.</div>
                                            {errors.password && <div className="invalid-feedback">{errors.password}</div>}
                                        </div>
                                        <div className="col-md-6">
                                            <label htmlFor="password_confirmation" className="form-label">Confirm Password</label>
                                            <input type="password" className={`form-control ${errors.password_confirmation ? 'is-invalid' : ''}`} id="password_confirmation" name="password_confirmation" value={data.password_confirmation} onChange={onHandleChange} required autoComplete="new-password" />
                                            {errors.password_confirmation && <div className="invalid-feedback">{errors.password_confirmation}</div>}
                                        </div>
                                    </div>

                                    <div className="mb-3 form-check">
                                        <input type="checkbox" className={`form-check-input ${errors.terms ? 'is-invalid' : ''}`} id="terms" name="terms" checked={data.terms} onChange={onHandleChange} />
                                        <label className="form-check-label" htmlFor="terms">I agree to the terms and conditions</label>
                                        {errors.terms && <div className="invalid-feedback">{errors.terms}</div>}
                                    </div>

                                    <div className="d-flex justify-content-between">
                                        <button type="button" className="btn btn-outline-secondary" onClick={prevStep}>Previous</button>
                                        <button type="submit" className="btn btn-primary" disabled={processing}>Register</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <div className="mt-4 text-center">
                            <p className="mb-0">Already have an account? <Link href={route('login')}>Login here</Link></p>
                        </div>
                    </div>
                </div>
            </div>

            {/* Guest Data Found Modal */}
            {showGuestModal && (
                <div 
                    className="modal show" 
                    style={{ 
                        display: 'block', 
                        backgroundColor: 'rgba(0,0,0,0.5)', 
                        zIndex: 10001,
                        position: 'fixed',
                        top: 0,
                        left: 0,
                        width: '100%',
                        height: '100%',
                        overflow: 'auto'
                    }} 
                    tabIndex="-1"
                >
                    <div 
                        className="modal-dialog modal-dialog-centered" 
                        style={{ 
                            position: 'relative', 
                            zIndex: 10002,
                            pointerEvents: 'auto'
                        }}
                    >
                        <div 
                            className="modal-content border-0 rounded-4 shadow-lg" 
                            style={{ 
                                position: 'relative', 
                                zIndex: 10003,
                                pointerEvents: 'auto'
                            }}
                        >
                            <div className="modal-body p-5 text-center">
                                <div className="avatar-xl bg-info-subtle text-info rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style={{ width: '80px', height: '80px' }}>
                                    <i className="fas fa-user-check fa-2x"></i>
                                </div>
                                <h3 className="fw-bold mb-3">Welcome Back!</h3>
                                <p className="text-muted mb-4">
                                    We found an existing booking with this email address. Would you like us to auto-fill your details?
                                </p>
                                <div className="card bg-light border-0 rounded-3 p-3 mb-4 text-start">
                                    <div className="small text-muted mb-1">Your saved details:</div>
                                    <div className="fw-bold">{guestData?.first_name} {guestData?.last_name}</div>
                                    {guestData?.phone && <div className="text-muted small">Phone: {guestData.phone}</div>}
                                </div>
                                <div className="d-grid gap-2">
                                    <button 
                                        type="button" 
                                        className="btn btn-primary rounded-pill fw-bold"
                                        onClick={applyGuestData}
                                        style={{ pointerEvents: 'auto', position: 'relative', zIndex: 10004 }}
                                    >
                                        <i className="fas fa-check me-2"></i>Yes, use my details
                                    </button>
                                    <button 
                                        type="button" 
                                        className="btn btn-outline-secondary rounded-pill" 
                                        onClick={() => setShowGuestModal(false)}
                                        style={{ pointerEvents: 'auto', position: 'relative', zIndex: 10004 }}
                                    >
                                        No, I'll enter new details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </GuestLayout>
    );
}
