import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import React, { useRef, useEffect, useState } from 'react';
import { Toaster, toast } from 'react-hot-toast';
import { formatDateTime } from '@/Utils/dateUtils';

export default function Show({ consent, doctors, auth }) {
    const isDoctor = auth.user.role === 'doctor';

    const { data, setData, post, processing, errors } = useForm({
        doctor_name: consent.doctor_name || `Dr. ${auth.user.last_name}`,
        doctor_signature: '',
        verbal_consent_obtained: consent.verbal_consent_obtained || false,
    });

    const canvasRef = useRef(null);
    const isDrawingRef = useRef(false);
    const lastPosRef = useRef({ x: 0, y: 0 });

    useEffect(() => {
        if (!isDoctor || consent.doctor_signature_path) return;
        const canvas = canvasRef.current;
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        ctx.strokeStyle = '#1e293b';
        ctx.lineWidth = 3;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
    }, [isDoctor, consent.doctor_signature_path]);

    const getCoordinates = (e) => {
        const canvas = canvasRef.current;
        if (!canvas) return { x: 0, y: 0 };
        const rect = canvas.getBoundingClientRect();
        if (e.touches && e.touches.length > 0) {
            return {
                x: e.touches[0].clientX - rect.left,
                y: e.touches[0].clientY - rect.top,
            };
        }
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top,
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
        const canvas = canvasRef.current;
        const dataUrl = canvas.toDataURL();
        setData('doctor_signature', dataUrl);
    };

    const clearCanvas = () => {
        const canvas = canvasRef.current;
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        setData('doctor_signature', '');
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('telehealth.admin.sign-doctor', consent.id), {
            onSuccess: () => {
                toast.success('Consent successfully counter-signed / updated!');
                clearCanvas();
            },
            onError: () => {
                toast.error('Failed to save doctor signature.');
            },
        });
    };

    return (
        <AuthenticatedLayout
            headerTitle="Telehealth Consent Sheet"
            breadcrumbs={[
                { label: 'Telehealth', url: route('telehealth.admin.index') },
                { label: `Consent #${consent.id}`, active: true },
            ]}
        >
            <Head title={`Telehealth Consent - ${consent.patient_name}`} />
            <Toaster position="top-right" />

            <div className="container-fluid py-4">
                <div className="row">
                    <div className="col-lg-8">
                        <div className="card border-0 shadow-sm rounded-4 bg-white p-4 p-lg-5 mb-4">
                            <div className="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                                <div>
                                    <h4 className="fw-extrabold text-gray-900 mb-1">Telehealth Consent Form</h4>
                                    <p className="text-muted small mb-0">
                                        Form ID: THC-{consent.id} | Signed on {formatDateTime(consent.signed_at)}
                                    </p>
                                </div>
                                <span
                                    className={`badge rounded-pill px-3 py-2 fw-bold ${consent.doctor_signature_path ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'}`}
                                >
                                    {consent.doctor_signature_path ? 'Fully Executed' : 'Doctor Sign Off Pending'}
                                </span>
                            </div>

                            <div className="row g-4 mb-4">
                                <div className="col-md-4">
                                    <div className="small text-muted fw-bold text-uppercase tracking-wider">
                                        Patient Name
                                    </div>
                                    <div className="fw-bold text-gray-900 fs-5 mt-1">{consent.patient_name}</div>
                                </div>
                                <div className="col-md-4">
                                    <div className="small text-muted fw-bold text-uppercase tracking-wider">
                                        Email Address
                                    </div>
                                    <div className="fw-bold text-gray-900 fs-5 mt-1">{consent.patient_email}</div>
                                </div>
                                <div className="col-md-4">
                                    <div className="small text-muted fw-bold text-uppercase tracking-wider">
                                        Phone Number
                                    </div>
                                    <div className="fw-bold text-gray-900 fs-5 mt-1">{consent.patient_phone}</div>
                                </div>
                            </div>

                            <div className="bg-light rounded-4 p-4 mb-4">
                                <h6 className="fw-bold text-slate-800 mb-3 border-bottom pb-2">
                                    Accepted Consent Terms
                                </h6>
                                <div className="small text-muted">
                                    All 10 legal telehealth consent clauses were read, verified, and accepted by the
                                    patient on submission of this document.
                                </div>
                            </div>

                            <div className="border rounded-4 p-4 text-center bg-light" style={{ maxWidth: '300px' }}>
                                <div className="small text-muted fw-bold text-uppercase tracking-wider mb-2">
                                    Patient Digital Signature
                                </div>
                                {consent.patient_signature_path ? (
                                    <img
                                        src={consent.patient_signature_path}
                                        alt="Patient Signature"
                                        className="img-fluid border rounded bg-white p-2"
                                    />
                                ) : (
                                    <div className="text-muted italic">No signature recorded</div>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="col-lg-4">
                        <div className="card border-0 shadow-sm rounded-4 bg-white p-4">
                            <h5 className="fw-bold mb-3">Staff / Doctor Verification</h5>

                            {consent.doctor_name && consent.doctor_signature_path ? (
                                <div className="space-y-4">
                                    <div className="alert alert-success-subtle text-success border-0 rounded-3 p-3">
                                        This telehealth consent form has been verified and fully signed off.
                                    </div>
                                    <div>
                                        <div className="small text-muted fw-bold">Verifying Clinician</div>
                                        <div className="fw-bold text-slate-800">{consent.doctor_name}</div>
                                    </div>
                                    <div className="border rounded bg-light p-2 mt-2" style={{ maxWidth: '250px' }}>
                                        <img
                                            src={consent.doctor_signature_path}
                                            alt="Doctor Signature"
                                            className="img-fluid"
                                        />
                                    </div>
                                </div>
                            ) : isDoctor ? (
                                <form onSubmit={handleSubmit}>
                                    <div className="mb-3">
                                        <label className="form-label small fw-bold text-muted">Clinician Name</label>
                                        <input
                                            type="text"
                                            className="form-control rounded-pill"
                                            value={data.doctor_name}
                                            onChange={(e) => setData('doctor_name', e.target.value)}
                                            required
                                        />
                                    </div>

                                    <div className="form-check mb-4">
                                        <input
                                            className="form-check-input"
                                            type="checkbox"
                                            id="verbalConsent"
                                            checked={data.verbal_consent_obtained}
                                            onChange={(e) => setData('verbal_consent_obtained', e.target.checked)}
                                        />
                                        <label
                                            className="form-check-label small fw-semibold text-slate-700"
                                            htmlFor="verbalConsent"
                                        >
                                            Verbal Consent Confirmed & Checked
                                        </label>
                                    </div>

                                    <div className="mb-4">
                                        <label className="form-label small fw-bold text-muted mb-2">
                                            Draw Counter-Signature
                                        </label>
                                        <div className="border rounded bg-light p-2 position-relative">
                                            <canvas
                                                ref={canvasRef}
                                                width="250"
                                                height="120"
                                                style={{
                                                    border: '1px dotted #cbd5e1',
                                                    borderRadius: '8px',
                                                    background: '#fff',
                                                    cursor: 'crosshair',
                                                    display: 'block',
                                                    width: '100%',
                                                    touchAction: 'none',
                                                }}
                                                onMouseDown={startDrawing}
                                                onMouseMove={draw}
                                                onMouseUp={stopDrawing}
                                                onMouseLeave={stopDrawing}
                                                onTouchStart={startDrawing}
                                                onTouchMove={draw}
                                                onTouchEnd={stopDrawing}
                                            />
                                            <div className="d-flex justify-content-end mt-2">
                                                <button
                                                    type="button"
                                                    className="btn btn-2xs btn-outline-secondary rounded-pill px-2.5 py-1"
                                                    onClick={clearCanvas}
                                                >
                                                    Clear
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <button
                                        type="submit"
                                        disabled={processing || !data.doctor_signature}
                                        className="btn btn-primary rounded-pill w-100 fw-bold shadow-sm"
                                    >
                                        {processing ? 'Saving...' : 'Counter-Sign & Complete'}
                                    </button>
                                </form>
                            ) : (
                                <div className="text-muted small">
                                    Only users logged in as Doctors can counter-sign telehealth consent agreements.
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
