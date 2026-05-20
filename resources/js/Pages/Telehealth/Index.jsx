import React, { useState, useRef, useEffect } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import { Toaster, toast } from 'react-hot-toast';

export default function Telehealth({ isLoggedIn, user, flash }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        patient_name: user ? `${user.first_name} ${user.last_name}` : '',
        patient_email: user ? user.email : '',
        patient_phone: user ? (user.phone || '') : '',
        doctor_name: '',
        patient_signature: '',
        verbal_consent: false,
    });

    const [clausesChecked, setClausesChecked] = useState(Array(10).fill(false));
    const [allChecked, setAllChecked] = useState(false);
    const canvasRef = useRef(null);
    const isDrawingRef = useRef(false);
    const lastPosRef = useRef({ x: 0, y: 0 });

    const consentClauses = [
        "I understand that telehealth involves the use of electronic communications to enable consultation between me and my healthcare provider at a distance.",
        "I understand that the laws that protect the privacy and confidentiality of health information also apply to telehealth.",
        "I understand that I have the right to withhold or withdraw my consent to telehealth at any time without affecting my right to future care or treatment.",
        "I understand that I have the right to inspect all information obtained and recorded during the telehealth interaction.",
        "I understand that a variety of alternative methods of care may be available to me, and I may choose one or more of these at any time.",
        "I understand that telehealth may involve electronic communication of my personal medical information to other medical practitioners who may be located in other areas.",
        "I understand that it is my duty to inform my healthcare provider of electronic interactions regarding my care with any other healthcare provider.",
        "I understand that I may expect the anticipated benefits from the use of telehealth in my care, but that no results can be guaranteed or assured.",
        "I understand that my healthcare information may be shared with other individuals for scheduling and billing purposes.",
        "I have read and understand the information provided above regarding telehealth, have discussed it with my healthcare provider and all my questions have been answered to my satisfaction."
    ];

    const toggleClause = (index) => {
        const updated = [...clausesChecked];
        updated[index] = !updated[index];
        setClausesChecked(updated);
        setAllChecked(updated.every(v => v));
    };

    const toggleAll = () => {
        const target = !allChecked;
        setAllChecked(target);
        setClausesChecked(Array(10).fill(target));
    };

    // Canvas signature logic
    useEffect(() => {
        const canvas = canvasRef.current;
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        ctx.strokeStyle = '#1e293b';
        ctx.lineWidth = 3;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
    }, []);

    const getCoordinates = (e) => {
        const canvas = canvasRef.current;
        if (!canvas) return { x: 0, y: 0 };
        const rect = canvas.getBoundingClientRect();
        
        // Touch events
        if (e.touches && e.touches.length > 0) {
            return {
                x: e.touches[0].clientX - rect.left,
                y: e.touches[0].clientY - rect.top
            };
        }
        // Mouse events
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
    };

    const startDrawing = (e) => {
        e.preventDefault();
        isDrawingRef.current = true;
        const pos = getCoordinates(e);
        lastPosRef.current = pos;
    };

    const draw = (e) => {
        if (!isDrawingRef.current) return;
        e.preventDefault();
        const canvas = canvasRef.current;
        const ctx = canvas.getContext('2d');
        const pos = getCoordinates(e);

        ctx.beginPath();
        ctx.moveTo(lastPosRef.current.x, lastPosRef.current.y);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();

        lastPosRef.current = pos;
    };

    const stopDrawing = () => {
        if (!isDrawingRef.current) return;
        isDrawingRef.current = false;
        
        // Save current canvas to form data
        const canvas = canvasRef.current;
        const dataUrl = canvas.toDataURL();
        setData('patient_signature', dataUrl);
    };

    const clearCanvas = () => {
        const canvas = canvasRef.current;
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        setData('patient_signature', '');
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        
        if (!allChecked) {
            toast.error("Please read and accept all telehealth consent clauses.");
            return;
        }

        if (!data.patient_signature) {
            toast.error("Please draw your signature in the canvas box.");
            return;
        }

        post(route('telehealth.store'), {
            onSuccess: () => {
                toast.success("Telehealth Consent successfully submitted!");
                reset('doctor_name');
                clearCanvas();
                setClausesChecked(Array(10).fill(false));
                setAllChecked(false);
            },
            onError: () => {
                toast.error("Please fill all required patient details.");
            }
        });
    };

    return (
        <div className="bg-light min-vh-100 pb-5">
            <Head title="Nyalife Women's Clinic - Telehealth Specialist Consultation" />
            <Toaster position="top-right" />

            {/* Premium Header */}
            <div className="py-5 text-white" style={{ background: 'linear-gradient(135deg, #1e293b 0%, #831843 100%)' }}>
                <div className="container">
                    <div className="d-flex align-items-center justify-content-between mb-4">
                        <Link href="/" className="d-flex align-items-center text-white text-decoration-none">
                            <div className="bg-white rounded-xl p-1 shadow-sm me-3 border border-pink-100">
                                <img src="/assets/img/logo/Logo2-transparent.png" alt="Nyalife" height="42" />
                            </div>
                            <span className="fw-extrabold fs-3 tracking-tightest">NYALIFE <span className="fw-light opacity-75">HMS</span></span>
                        </Link>
                        {isLoggedIn ? (
                            <Link href={route('dashboard')} className="btn btn-outline-light rounded-pill px-4">Dashboard</Link>
                        ) : (
                            <Link href={route('login.patient')} className="btn btn-outline-light rounded-pill px-4">Patient Login</Link>
                        )}
                    </div>

                    <div className="row align-items-center mt-5">
                        <div className="col-lg-8">
                            <h1 className="fw-extrabold display-5 mb-3">🩺 Telehealth Specialist Consultation</h1>
                            <p className="lead opacity-75 mb-0">Convenient Care, Wherever You Are! Connect with our clinical specialists online with confidentiality and security.</p>
                        </div>
                    </div>
                </div>
            </div>

            {/* Booking steps and form */}
            <div className="container mt-5">
                <h3 className="fw-bold mb-4 text-slate-800">5 Easy Booking Steps</h3>
                <div className="row g-4 mb-5">
                    {[
                        { title: "1. Call or Text", desc: "Call or text us to request a telehealth appointment.", icon: "fa-phone", color: "bg-primary-subtle text-primary" },
                        { title: "2. Preferred Slot", desc: "Share your preferred Day & Time - we will match your schedule.", icon: "fa-calendar-alt", color: "bg-info-subtle text-info" },
                        { title: "3. Pay via Till", desc: "Pay via our clinic Till Number (details will be texted to you).", icon: "fa-credit-card", color: "bg-warning-subtle text-warning" },
                        { title: "4. Sign Consent", desc: "Read and sign the digital Consent Form below.", icon: "fa-file-signature", color: "bg-danger-subtle text-danger" },
                        { title: "5. Join Meeting", desc: "Receive your booking confirmation & meeting link via email/text.", icon: "fa-video", color: "bg-success-subtle text-success" }
                    ].map((step, idx) => (
                        <div key={idx} className="col-lg-2-4 col-md-4 col-sm-6">
                            <div className="card border-0 shadow-sm rounded-4 p-4 h-100 text-center">
                                <div className={`rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3 ${step.color}`} style={{ width: '60px', height: '60px' }}>
                                    <i className={`fas ${step.icon} fa-lg`}></i>
                                </div>
                                <h6 className="fw-bold mb-2">{step.title}</h6>
                                <p className="small text-muted mb-0">{step.desc}</p>
                            </div>
                        </div>
                    ))}
                </div>

                <div className="row">
                    <div className="col-lg-12">
                        <div className="card border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-white">
                            <h3 className="fw-bold mb-4 text-center">Telehealth Patient Consent Form</h3>
                            <p className="text-muted text-center mb-5">Please read the terms below carefully and sign using the digital pad before submitting.</p>

                            <form onSubmit={handleSubmit}>
                                <div className="row g-4 mb-4">
                                    <div className="col-md-4">
                                        <label className="form-label small fw-bold text-muted">Full Name</label>
                                        <input 
                                            type="text" 
                                            className="form-control rounded-pill py-2.5 px-4" 
                                            placeholder="Enter your full name" 
                                            value={data.patient_name} 
                                            onChange={e => setData('patient_name', e.target.value)} 
                                            required
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <label className="form-label small fw-bold text-muted">Email Address</label>
                                        <input 
                                            type="email" 
                                            className="form-control rounded-pill py-2.5 px-4" 
                                            placeholder="name@example.com" 
                                            value={data.patient_email} 
                                            onChange={e => setData('patient_email', e.target.value)} 
                                            required
                                        />
                                    </div>
                                    <div className="col-md-4">
                                        <label className="form-label small fw-bold text-muted">Phone Number</label>
                                        <input 
                                            type="text" 
                                            className="form-control rounded-pill py-2.5 px-4" 
                                            placeholder="e.g. +2547..." 
                                            value={data.patient_phone} 
                                            onChange={e => setData('patient_phone', e.target.value)} 
                                            required
                                        />
                                    </div>
                                </div>

                                <div className="card bg-light border-0 rounded-4 p-4 mb-4" style={{ maxHeight: '350px', overflowY: 'auto' }}>
                                    <div className="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                        <h6 className="fw-bold text-slate-800 mb-0">Legal Consent Clauses</h6>
                                        <div className="form-check">
                                            <input 
                                                className="form-check-input" 
                                                type="checkbox" 
                                                id="checkAll" 
                                                checked={allChecked} 
                                                onChange={toggleAll}
                                            />
                                            <label className="form-check-label small fw-bold text-slate-700" htmlFor="checkAll">Select All</label>
                                        </div>
                                    </div>

                                    <div className="d-grid gap-3">
                                        {consentClauses.map((clause, idx) => (
                                            <div key={idx} className="form-check d-flex align-items-start gap-2">
                                                <input 
                                                    className="form-check-input mt-1" 
                                                    type="checkbox" 
                                                    id={`clause-${idx}`} 
                                                    checked={clausesChecked[idx]} 
                                                    onChange={() => toggleClause(idx)}
                                                />
                                                <label className="form-check-label small text-slate-700 leading-normal" htmlFor={`clause-${idx}`}>
                                                    <span className="fw-bold text-slate-900 me-2">{idx + 1}.</span>
                                                    {clause}
                                                </label>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                <div className="row g-4 align-items-end">
                                    <div className="col-md-6">
                                        <label className="form-label small fw-bold text-muted mb-2">Digital Signature Pad</label>
                                        <div className="border rounded-4 bg-light p-2 position-relative" style={{ maxWidth: '420px' }}>
                                            <canvas 
                                                ref={canvasRef}
                                                width="400" 
                                                height="200" 
                                                style={{ border: '2px dotted #cbd5e1', borderRadius: '12px', background: '#fff', cursor: 'crosshair', display: 'block', width: '100%', touchAction: 'none' }}
                                                onMouseDown={startDrawing}
                                                onMouseMove={draw}
                                                onMouseUp={stopDrawing}
                                                onMouseLeave={stopDrawing}
                                                onTouchStart={startDrawing}
                                                onTouchMove={draw}
                                                onTouchEnd={stopDrawing}
                                            />
                                            <div className="d-flex justify-content-end gap-2 mt-2">
                                                <button type="button" className="btn btn-sm btn-outline-secondary rounded-pill px-3" onClick={clearCanvas}>Clear</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="col-md-6 text-md-end">
                                        <button 
                                            type="submit" 
                                            disabled={processing || !allChecked || !data.patient_signature}
                                            className="btn btn-primary rounded-pill btn-lg px-5 fw-bold shadow"
                                        >
                                            {processing ? 'Submitting...' : 'Sign & Submit Consent'}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
