import React, { useState } from 'react';
import axios from 'axios';
import { toast } from 'react-hot-toast';

export default function ContactSection({ cms }) {
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        message: ''
    });
    const [loading, setLoading] = useState(false);

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

    const getImageUrl = (path) => {
        if (!path) return '';
        if (path.startsWith('cms/') || !path.startsWith('/')) {
            const cleanPath = path.replace(/^\/storage\//, '').replace(/^cms\//, 'cms/');
            return `/storage/${cleanPath}`;
        }
        return path;
    };

    const bgImage = getImageUrl(cms.contact_bg_image || '/assets/img/slider/footer-bg1.jpg');
    const overlayOpacity = cms.contact_overlay_opacity || '0.85';

    return (
        <section 
            className="section-rhythm-md position-relative border-top border-bottom bg-cover bg-center bg-fixed" 
            id="contact"
            style={{ backgroundImage: `url(${bgImage})` }}
        >
            {/* Custom Pink Overlay */}
            <div 
                className="position-absolute top-0 start-0 w-100 h-100 z-1" 
                style={{ 
                    backgroundColor: '#ec4899', 
                    opacity: overlayOpacity
                }}
            ></div>

            <div className="container position-relative z-10">
                <div className="row g-10 align-items-center h-auto">
                    <div className="col-lg-6 text-dark h-auto">
                        <span className="badge bg-white text-pink-600 px-3 py-2 rounded-pill mb-3 font-bold text-uppercase tracking-wider shadow-sm">Connect</span>
                        <h2 className="display-4 fw-bold mb-8 text-white">Reach Out to Us</h2>
                        
                        <div className="d-flex flex-column gap-6 mb-10 h-auto">
                            <div className="d-flex align-items-start gap-4 h-auto">
                                <div className="avatar-md bg-white text-pink-500 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm">
                                    <i className="fas fa-map-marker-alt fa-lg"></i>
                                </div>
                                <div className="leading-relaxed">
                                    <h5 className="fw-bold mb-1 text-white small text-uppercase opacity-75">Location</h5>
                                    <p className="text-white mb-0 pe-lg-10 fs-6 fw-semibold">{cms.contact_address || 'JemPark Complex building suite A5, Sabaki, Athi River.'}</p>
                                </div>
                            </div>

                            <div className="d-flex align-items-start gap-4 h-auto">
                                <div className="avatar-md bg-white text-pink-500 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm">
                                    <i className="fas fa-phone-alt fa-lg"></i>
                                </div>
                                <div className="h-auto">
                                    <h5 className="fw-bold mb-1 text-white small text-uppercase opacity-75">Phone</h5>
                                    <p className="fs-6 font-bold text-white mb-0">{cms.contact_phone || '+254746516514'}</p>
                                </div>
                            </div>

                            <div className="d-flex align-items-start gap-4 h-auto">
                                <div className="avatar-md bg-white text-pink-500 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm">
                                    <i className="fas fa-envelope fa-lg"></i>
                                </div>
                                <div className="leading-relaxed">
                                    <h5 className="fw-bold mb-1 text-white small text-uppercase opacity-75">Email</h5>
                                    <p className="text-white mb-0 fs-6 font-bold">{cms.contact_email || 'info@nyalifewomensclinic.com'}</p>
                                </div>
                            </div>
                        </div>

                        <div className="social-links d-flex gap-3 h-auto">
                            <a href="#" className="btn btn-white btn-icon rounded-circle shadow-sm"><i className="fab fa-facebook-f text-pink-500"></i></a>
                            <a href="#" className="btn btn-white btn-icon rounded-circle shadow-sm"><i className="fab fa-instagram text-pink-500"></i></a>
                            <a href="#" className="btn btn-white btn-icon rounded-circle shadow-sm"><i className="fab fa-twitter text-pink-500"></i></a>
                        </div>
                    </div>
                    
                    <div className="col-lg-6 h-auto">
                        <div className="contact-form-container">
                            <form className="row g-4 h-auto" onSubmit={handleSubmit}>
                                <div className="col-12 text-dark h-auto">
                                    <input 
                                        type="text" 
                                        name="name"
                                        value={formData.name}
                                        onChange={handleChange}
                                        required
                                        className="form-control bg-white bg-opacity-10 border-white border-opacity-20 rounded-pill py-4 px-5 text-white placeholder-white-50 shadow-none focus-ring-pink" 
                                        placeholder="FullName" 
                                    />
                                </div>
                                <div className="col-12 text-dark h-auto">
                                    <input 
                                        type="email" 
                                        name="email"
                                        value={formData.email}
                                        onChange={handleChange}
                                        required
                                        className="form-control bg-white bg-opacity-10 border-white border-opacity-20 rounded-pill py-4 px-5 text-white placeholder-white-50 shadow-none focus-ring-pink" 
                                        placeholder="Your Email" 
                                    />
                                </div>
                                <div className="col-12 text-dark h-auto">
                                    <textarea 
                                        name="message"
                                        value={formData.message}
                                        onChange={handleChange}
                                        required
                                        className="form-control bg-white bg-opacity-10 border-white border-opacity-20 rounded-3xl py-4 px-5 text-white placeholder-white-50 shadow-none focus-ring-pink" 
                                        rows="5" 
                                        placeholder="Your Message"
                                    ></textarea>
                                </div>
                                <div className="col-12 mt-5 text-dark h-auto">
                                    <button 
                                        disabled={loading}
                                        className="btn btn-primary w-100 rounded-pill py-4 font-bold shadow-lg transform hover-lift d-flex align-items-center justify-content-center"
                                    >
                                        {loading ? (
                                            <span className="spinner-border spinner-border-sm me-2"></span>
                                        ) : (
                                            <i className="fas fa-paper-plane me-2"></i>
                                        )}
                                        {loading ? 'Transmitting...' : 'Send Message'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <style>{`
                .avatar-md { width: 56px; height: 56px; }
                .btn-icon { width: 44px; height: 44px; padding: 0; display: flex; align-items: center; justify-content: center; background: white; }
                .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
                .btn-white:hover { background: #fdf2f8; }
                .placeholder-white-50::placeholder { color: rgba(255, 255, 255, 0.6); }
                .focus-ring-pink:focus { background-color: rgba(255, 255, 255, 0.15) !important; border-color: #f472b6 !important; box-shadow: 0 0 0 0.25rem rgba(244, 114, 182, 0.25) !important; outline: 0; color: white !important; }
            `}</style>
        </section>
    );
}
