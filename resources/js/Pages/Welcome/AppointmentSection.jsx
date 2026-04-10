import React from 'react';
import { Link } from '@inertiajs/react';

export default function AppointmentSection({ data, setData, handleSubmit, processing, errors }) {
    return (
        <section className="py-20 bg-pink-50/30" id="appointment">
            <div className="container">
                <div className="max-w-3xl mx-auto">
                    <div className="text-center mb-12">
                        <span className="badge bg-pink-100 text-pink-600 px-3 py-2 rounded-pill mb-3 font-bold text-uppercase tracking-wider">Appointment</span>
                        <h2 className="display-5 fw-bold text-gray-900 mb-4 section-title-main">Ready to Start Your Journey?</h2>
                        <p className="lead text-gray-600">Quickly book your first consultation with our team of specialists.</p>
                    </div>

                    <div className="bg-white rounded-3xl shadow-xl p-8 md:p-12 border border-pink-100">
                        <form onSubmit={handleSubmit} className="row g-4">
                            <div className="col-12">
                                <label className="form-label font-bold text-gray-700">Full Name</label>
                                <input
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    className="form-control rounded-pill py-3 px-4 border-gray-200 focus:ring-pink-500 focus:border-pink-500"
                                    required
                                    placeholder="Enter your full name"
                                />
                                {errors.name && <div className="text-danger small mt-1 ps-3">{errors.name}</div>}
                            </div>

                            <div className="col-md-6">
                                <label className="form-label font-bold text-gray-700">Email Address</label>
                                <input
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className="form-control rounded-pill py-3 px-4 border-gray-200 focus:ring-pink-500 focus:border-pink-500"
                                    required
                                    placeholder="yourname@email.com"
                                />
                            </div>
                            <div className="col-md-6">
                                <label className="form-label font-bold text-gray-700">Phone Number</label>
                                <input
                                    type="tel"
                                    value={data.phone}
                                    onChange={(e) => setData('phone', e.target.value)}
                                    className="form-control rounded-pill py-3 px-4 border-gray-200 focus:ring-pink-500 focus:border-pink-500"
                                    required
                                    placeholder="+254..."
                                />
                            </div>

                            <div className="col-md-6">
                                <label className="form-label font-bold text-gray-700">Preferred Date</label>
                                <input
                                    type="date"
                                    value={data.date}
                                    onChange={(e) => setData('date', e.target.value)}
                                    className="form-control rounded-pill py-3 px-4 border-gray-200 focus:ring-pink-500 focus:border-pink-500"
                                    min={new Date().toISOString().split('T')[0]}
                                    required
                                />
                            </div>
                            <div className="col-md-6">
                                <label className="form-label font-bold text-gray-700">Preferred Time</label>
                                <input
                                    type="time"
                                    value={data.time}
                                    onChange={(e) => setData('time', e.target.value)}
                                    className="form-control rounded-pill py-3 px-4 border-gray-200 focus:ring-pink-500 focus:border-pink-500"
                                    required
                                />
                            </div>

                            <div className="col-12">
                                <label className="form-label font-bold text-gray-700">Reason for Visit</label>
                                <textarea
                                    value={data.reason}
                                    onChange={(e) => setData('reason', e.target.value)}
                                    className="form-control rounded-3xl py-3 px-4 border-gray-200 focus:ring-pink-500 focus:border-pink-500"
                                    rows="4"
                                    placeholder="Brief symptoms or reason for the visit..."
                                ></textarea>
                            </div>

                            <div className="col-12 text-center mt-5">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="btn btn-primary rounded-pill px-10 py-3 font-bold shadow-lg transform transition hover-scale"
                                >
                                    {processing ? 'Submitting...' : 'Request Appointment'}
                                </button>
                                <p className="mt-4 text-muted small">
                                    Already a patient? <Link href={route('login')} className="text-pink-600 fw-bold">Login here</Link>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    );
}
