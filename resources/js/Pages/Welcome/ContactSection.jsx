import React, { useState } from 'react';
import axios from 'axios';
import { toast } from 'react-hot-toast';

const CONTACT_CHANNELS = (cms) => [
    {
        id: 'location',
        icon: 'fa-map-marker-alt',
        tone: 'pink',
        label: 'Visit us',
        value: cms.contact_address || 'JemPark Complex, Suite A5, Sabaki, Athi River, Machakos',
        action: cms.contact_maps_url || 'https://maps.google.com/?q=Nyalife+Women%27s+Clinic+Athi+River',
        actionLabel: 'Get directions',
        external: true,
    },
    {
        id: 'phone',
        icon: 'fa-phone-alt',
        tone: 'teal',
        label: 'Call the clinic',
        value: cms.contact_phone || '+254 746 516 514',
        action: `tel:${(cms.contact_phone || '+254746516514').replace(/\s/g, '')}`,
        actionLabel: 'Call now',
    },
    {
        id: 'email',
        icon: 'fa-envelope',
        tone: 'pink',
        label: 'Email us',
        value: cms.contact_email || 'info@nyalifewomensclinic.com',
        action: `mailto:${cms.contact_email || 'info@nyalifewomensclinic.com'}`,
        actionLabel: 'Send email',
    },
    {
        id: 'hours',
        icon: 'fa-clock',
        tone: 'teal',
        label: 'Clinic hours',
        value: cms.contact_hours || 'Mon – Sat: 8:00 AM – 6:00 PM',
        action: '#appointment',
        actionLabel: 'Book visit',
    },
];

export default function ContactSection({ cms }) {
    const [formData, setFormData] = useState({ name: '', email: '', message: '' });
    const [loading, setLoading] = useState(false);
    const channels = CONTACT_CHANNELS(cms);

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);

        try {
            const response = await axios.post(route('contact.store'), formData);
            toast.success(response.data.message || 'Message sent successfully!');
            setFormData({ name: '', email: '', message: '' });
        } catch (error) {
            toast.error(error.response?.data?.message || 'Something went wrong. Please try again.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <section className="section-rhythm-md bg-white border-top border-gray-100" id="contact">
            <div className="container">
                <div className="text-center mb-12 mb-lg-16 max-w-3xl mx-auto">
                    <span className="badge bg-pink-100 text-pink-600 px-3 py-2 rounded-pill mb-3 font-bold text-uppercase tracking-wider">
                        Connect
                    </span>
                    <h2 className="display-5 fw-bold text-gray-900 mb-4 section-title-main">Reach Out to Us</h2>
                    <p className="lead text-gray-600 mb-0">
                        Questions about our services, appointments, or your care journey? Our team is ready to help.
                    </p>
                </div>

                <div className="row g-4 g-lg-5 align-items-start">
                    <div className="col-lg-5 d-flex flex-column gap-3">
                        {channels.map((channel) => (
                            <div key={channel.id} className="landing-contact-card card border-0 shadow-sm rounded-3xl">
                                <div className="card-body p-4 d-flex gap-3 align-items-start">
                                    <div className={`landing-contact-icon landing-contact-icon--${channel.tone}`}>
                                        <i className={`fas ${channel.icon}`}></i>
                                    </div>
                                    <div className="landing-contact-card-content flex-grow-1 min-w-0">
                                        <div className="extra-small fw-bold text-uppercase tracking-widest text-muted mb-2">
                                            {channel.label}
                                        </div>
                                        <p
                                            className={`landing-contact-card-value fw-semibold text-gray-800 mb-3 leading-relaxed${channel.id === 'location' ? ' landing-contact-card-value--address' : ''}`}
                                        >
                                            {channel.value}
                                        </p>
                                        {channel.external ? (
                                            <a
                                                href={channel.action}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="btn btn-sm btn-outline-primary rounded-pill px-4 fw-bold"
                                            >
                                                {channel.actionLabel} <i className="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        ) : channel.action.startsWith('#') ? (
                                            <a
                                                href={channel.action}
                                                className="btn btn-sm btn-outline-primary rounded-pill px-4 fw-bold"
                                            >
                                                {channel.actionLabel} <i className="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        ) : (
                                            <a
                                                href={channel.action}
                                                className="btn btn-sm btn-outline-primary rounded-pill px-4 fw-bold"
                                            >
                                                {channel.actionLabel} <i className="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ))}

                        {(cms.instagram_url || cms.facebook_url || cms.linkedin_url) && (
                            <div className="d-flex flex-wrap gap-2 pt-2">
                                {cms.instagram_url && (
                                    <a
                                        href={cms.instagram_url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="landing-social-chip"
                                    >
                                        <i className="fab fa-instagram me-2"></i> Instagram
                                    </a>
                                )}
                                {cms.facebook_url && (
                                    <a
                                        href={cms.facebook_url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="landing-social-chip"
                                    >
                                        <i className="fab fa-facebook-f me-2"></i> Facebook
                                    </a>
                                )}
                                {cms.linkedin_url && (
                                    <a
                                        href={cms.linkedin_url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="landing-social-chip"
                                    >
                                        <i className="fab fa-linkedin-in me-2"></i> LinkedIn
                                    </a>
                                )}
                            </div>
                        )}
                    </div>

                    <div className="col-lg-7">
                        <div className="card border-0 shadow-lg rounded-3xl overflow-hidden landing-contact-form-card">
                            <div className="card-header border-0 bg-gradient-pink-teal text-white px-4 px-md-5 py-4">
                                <h3 className="h5 fw-bold mb-1">Send us a message</h3>
                                <p className="mb-0 small opacity-90">We typically respond within one business day.</p>
                            </div>
                            <div className="card-body p-4 p-md-5 bg-gray-50">
                                <form className="row g-4" onSubmit={handleSubmit}>
                                    <div className="col-md-6">
                                        <label
                                            htmlFor="contact-name"
                                            className="form-label fw-bold text-gray-700 small"
                                        >
                                            Full name
                                        </label>
                                        <input
                                            id="contact-name"
                                            type="text"
                                            name="name"
                                            value={formData.name}
                                            onChange={handleChange}
                                            required
                                            className="form-control form-control-lg rounded-pill border-gray-200 shadow-none"
                                            placeholder="Jane Doe"
                                        />
                                    </div>
                                    <div className="col-md-6">
                                        <label
                                            htmlFor="contact-email"
                                            className="form-label fw-bold text-gray-700 small"
                                        >
                                            Email address
                                        </label>
                                        <input
                                            id="contact-email"
                                            type="email"
                                            name="email"
                                            value={formData.email}
                                            onChange={handleChange}
                                            required
                                            className="form-control form-control-lg rounded-pill border-gray-200 shadow-none"
                                            placeholder="you@example.com"
                                        />
                                    </div>
                                    <div className="col-12">
                                        <label
                                            htmlFor="contact-message"
                                            className="form-label fw-bold text-gray-700 small"
                                        >
                                            How can we help?
                                        </label>
                                        <textarea
                                            id="contact-message"
                                            name="message"
                                            value={formData.message}
                                            onChange={handleChange}
                                            required
                                            className="form-control rounded-3xl border-gray-200 shadow-none py-3"
                                            rows="5"
                                            placeholder="Tell us about your question or appointment request..."
                                        ></textarea>
                                    </div>
                                    <div className="col-12">
                                        <button
                                            type="submit"
                                            disabled={loading}
                                            className="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm hover-lift d-flex align-items-center justify-content-center gap-2"
                                        >
                                            {loading ? (
                                                <>
                                                    <span className="spinner-border spinner-border-sm"></span>
                                                    Sending...
                                                </>
                                            ) : (
                                                <>
                                                    <i className="fas fa-paper-plane"></i>
                                                    Send message
                                                </>
                                            )}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="text-center mt-10 pt-2">
                    <p className="text-muted small mb-3">Prefer to book directly?</p>
                    <a href="#appointment" className="btn btn-outline-primary rounded-pill px-5 py-2 fw-bold">
                        <i className="fas fa-calendar-check me-2"></i>
                        Schedule an appointment
                    </a>
                </div>
            </div>
        </section>
    );
}
