import React from 'react';
import { Link } from '@inertiajs/react';

export default function AppointmentSection({ data, setData, handleSubmit, processing, errors }) {
    return (
        <section className="py-24 bg-pink-50/30" id="appointment">
            <div className="container">
                <div className="max-w-3xl mx-auto">
                    <div className="text-center mb-12 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        <span className="badge bg-pink-100 text-pink-600 px-4 py-3 rounded-pill mb-4 fw-extrabold text-uppercase tracking-widest shadow-sm">
                            Reservation Registry
                        </span>
                        <h2 className="display-4 fw-extrabold text-gray-900 mb-4 tracking-tightest">Begin Your Healthcare Journey</h2>
                        <p className="lead text-gray-600 max-w-2xl mx-auto fw-medium">
                            Our clinical coordinators are ready to assist you. Secure your consultation slot within minutes.
                        </p>
                    </div>

                    <div className="bg-white rounded-3xl shadow-2xl p-8 md:p-12 border border-pink-100 animate-in fade-in slide-in-from-bottom-8 duration-700 delay-150">
                        <form onSubmit={handleSubmit} className="row g-4">
                            <div className="col-12">
                                <label className="form-label fw-extrabold text-gray-400 extra-small text-uppercase tracking-widest mb-2 ps-1">Full Legal Name</label>
                                <input
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    className="form-control rounded-pill py-3 px-4 border-gray-100 bg-gray-50 focus:bg-white transition-all shadow-none"
                                    required
                                    placeholder="e.g. Jane Doe"
                                />
                                {errors.name && <div className="text-danger extra-small fw-bold mt-2 ps-3">{errors.name}</div>}
                            </div>

                            <div className="col-md-6">
                                <label className="form-label fw-extrabold text-gray-400 extra-small text-uppercase tracking-widest mb-2 ps-1">Email Address</label>
                                <input
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className="form-control rounded-pill py-3 px-4 border-gray-100 bg-gray-50 focus:bg-white transition-all shadow-none"
                                    required
                                    placeholder="jane.doe@example.com"
                                />
                            </div>
                            <div className="col-md-6">
                                <label className="form-label fw-extrabold text-gray-400 extra-small text-uppercase tracking-widest mb-2 ps-1">Mobile Phone</label>
                                <input
                                    type="tel"
                                    value={data.phone}
                                    onChange={(e) => setData('phone', e.target.value)}
                                    className="form-control rounded-pill py-3 px-4 border-gray-100 bg-gray-50 focus:bg-white transition-all shadow-none"
                                    required
                                    placeholder="+254 XXX XXX XXX"
                                />
                            </div>

                            <div className="col-md-6">
                                <label className="form-label fw-extrabold text-gray-400 extra-small text-uppercase tracking-widest mb-2 ps-1">Preferred Date</label>
                                <input
                                    type="date"
                                    value={data.date}
                                    onChange={(e) => setData('date', e.target.value)}
                                    className="form-control rounded-pill py-3 px-4 border-gray-100 bg-gray-50 focus:bg-white transition-all shadow-none"
                                    min={new Date().toISOString().split('T')[0]}
                                    required
                                />
                            </div>
                            <div className="col-md-6">
                                <label className="form-label fw-extrabold text-gray-400 extra-small text-uppercase tracking-widest mb-2 ps-1">Preferred Time</label>
                                <input
                                    type="time"
                                    value={data.time}
                                    onChange={(e) => setData('time', e.target.value)}
                                    className="form-control rounded-pill py-3 px-4 border-gray-100 bg-gray-50 focus:bg-white transition-all shadow-none"
                                    required
                                />
                            </div>

                            <div className="col-12">
                                <label className="form-label fw-extrabold text-gray-400 extra-small text-uppercase tracking-widest mb-2 ps-1">Nature of Visit</label>
                                <textarea
                                    value={data.reason}
                                    onChange={(e) => setData('reason', e.target.value)}
                                    className="form-control rounded-3xl py-3 px-4 border-gray-100 bg-gray-50 focus:bg-white transition-all shadow-none"
                                    rows="4"
                                    placeholder="Briefly describe your symptoms or reason for the consultation..."
                                ></textarea>
                            </div>

                            <div className="col-12 text-center mt-5">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="btn btn-primary rounded-pill px-12 py-3.5 fw-extrabold extra-small tracking-widest shadow-2xl hover-lift"
                                >
                                    {processing ? 'TRANSMITTING...' : 'SECURE MY APPOINTMENT'}
                                </button>
                                <div className="mt-5 border-top border-gray-50 pt-4">
                                    <p className="text-muted extra-small fw-bold text-uppercase tracking-widest opacity-50 mb-0">
                                        Existing Patient? <Link href={route('login.patient')} className="text-primary hover-opacity-100 transition-all text-decoration-none">ACCESS PORTAL HERE</Link>
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    );
}
