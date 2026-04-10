import { Link, Head, useForm } from '@inertiajs/react';
import React, { useEffect } from 'react';
import HeroSection from './Welcome/HeroSection';
import AppointmentSection from './Welcome/AppointmentSection';
import AboutSection from './Welcome/AboutSection';
import ServicesSection from './Welcome/ServicesSection';
import BlogSection from './Welcome/BlogSection';
import ContactSection from './Welcome/ContactSection';

export default function Welcome({ auth, laravelVersion, phpVersion, blogs = [], cms = {}, serviceTabs = [] }) {
    const sectionOrder = (cms.landing_page_order || 'hero,appointment,about,services,blog,contact').split(',');
    const displayBlogs = blogs.slice(0, 3);
    
    useEffect(() => {
        document.body.classList.add('landing');
        return () => document.body.classList.remove('landing');
    }, []);
    
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        phone: '',
        date: '',
        time: '',
        reason: '',
    });

    const [showSuccessModal, setShowSuccessModal] = React.useState(false);
    const [guestData, setGuestData] = React.useState(null);

    const handleSubmit = (e) => {
        e.preventDefault();
        const submittedData = { ...data };
        post(route('appointments.guest.store'), {
            onSuccess: () => {
                reset();
                setGuestData(submittedData);
                setShowSuccessModal(true);
            },
        });
    };

    useEffect(() => {
        if (!sectionOrder.includes('hero')) return;

        const slides = document.querySelectorAll('.hero-slide');
        const dots = document.querySelectorAll('.hero-dot');
        const overlay = document.querySelector('.hero-overlay');
        let currentSlide = 0;
        const slideCount = slides.length;
        if (slideCount === 0) return;
        
        let slideInterval;

        const showSlide = (n) => {
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            currentSlide = (n + slideCount) % slideCount;
            if (slides[currentSlide]) slides[currentSlide].classList.add('active');
            if (dots[currentSlide]) dots[currentSlide].classList.add('active');
            if (overlay) overlay.className = `hero-overlay overlay-slide-${currentSlide + 1}`;
        };

        const nextSlide = () => showSlide(currentSlide + 1);
        const startSlideShow = () => { slideInterval = setInterval(nextSlide, 5000); };
        const stopSlideShow = () => { clearInterval(slideInterval); };

        const nextBtn = document.getElementById('next-slide');
        const prevBtn = document.getElementById('prev-slide');

        if (nextBtn) nextBtn.addEventListener('click', () => { stopSlideShow(); nextSlide(); startSlideShow(); });
        if (prevBtn) prevBtn.addEventListener('click', () => { stopSlideShow(); showSlide(currentSlide - 1); startSlideShow(); });

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => { stopSlideShow(); showSlide(index); startSlideShow(); });
        });

        startSlideShow();
        return () => stopSlideShow();
    }, [sectionOrder]);

    return (
        <>
            <Head title="Nyalife Women's Clinic - Modern Healthcare" />
            
            <nav className="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
                <div className="container">
                    <Link className="navbar-brand d-flex align-items-center" href="/">
                        <img src="/assets/img/logo/Logo2-transparent.png" alt="Nyalife HMS" height="40" className="me-2 bg-white rounded-2 p-1" />
                        <span className="fw-bold fs-4 text-white">Nyalife HMS</span>
                    </Link>
                    <button className="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span className="navbar-toggler-icon"></span>
                    </button>
                    <div className="collapse navbar-collapse" id="navbarNav">
                        <ul className="navbar-nav mx-auto">
                            <li className="nav-item"><a className="nav-link px-3" href="/">Home</a></li>
                            <li className="nav-item"><a className="nav-link px-3" href="#about">About</a></li>
                            <li className="nav-item"><a className="nav-link px-3" href="#services">Services</a></li>
                            <li className="nav-item"><a className="nav-link px-3" href="#blog">Blog</a></li>
                            <li className="nav-item"><a className="nav-link px-3" href="#contact">Contact</a></li>
                        </ul>
                        
                        <div className="d-flex flex-column flex-lg-row gap-2 mt-3 mt-lg-0 navbar-actions">
                            {!auth.user && (
                                <a href="#appointment" className="btn btn-light rounded-pill px-4 text-primary fw-bold shadow-sm">
                                    <i className="fas fa-calendar-plus me-2"></i>Book Visit
                                </a>
                            )}
                            {auth.user ? (
                                <Link href={route('dashboard')} className="btn btn-outline-light rounded-pill px-4 fw-bold">
                                    Dashboard
                                </Link>
                            ) : (
                                <Link href={route('login')} className="btn btn-outline-light rounded-pill px-4 fw-bold">
                                    Login
                                </Link>
                            )}
                        </div>
                    </div>
                </div>
            </nav>

            <div className="container-fluid p-0 m-0 overflow-hidden h-auto">
                {sectionOrder.map((sectionName) => {
                    const name = sectionName.trim();
                    if (name === 'hero') return <HeroSection key="hero" cms={cms} isLoggedIn={!!auth.user} />;
                    if (name === 'appointment' && !auth.user) return <AppointmentSection key="appointment" data={data} setData={setData} handleSubmit={handleSubmit} processing={processing} errors={errors} />;
                    if (name === 'about') return <AboutSection key="about" cms={cms} />;
                    if (name === 'services') return <ServicesSection key="services" serviceTabs={serviceTabs} />;
                    if (name === 'blog') return <BlogSection key="blog" blogs={displayBlogs} />;
                    if (name === 'contact') return <ContactSection key="contact" cms={cms} />;
                    return null;
                })}
            </div>

            {/* Success Modal */}
            {showSuccessModal && (
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
                                <div className="avatar-xl bg-success-subtle text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style={{ width: '80px', height: '80px' }}>
                                    <i className="fas fa-check fa-2x"></i>
                                </div>
                                <h3 className="fw-bold mb-3">Appointment Requested!</h3>
                                <p className="text-muted mb-4 lead">
                                    Your visit has been requested. We will contact you at <strong>{guestData?.email}</strong> to confirm.
                                </p>
                                <div className="card bg-pink-50 border-0 rounded-4 p-4 mb-4" style={{ pointerEvents: 'auto' }}>
                                    <h6 className="fw-bold text-pink-600 mb-2">Want to manage your visits?</h6>
                                    <p className="small text-gray-600 mb-3">Create an account now for faster bookings and access to your medical records.</p>
                                    <div className="d-grid gap-2">
                                        <Link 
                                            href={`/register?name=${encodeURIComponent(guestData?.name)}&email=${encodeURIComponent(guestData?.email)}&phone=${encodeURIComponent(guestData?.phone)}`} 
                                            className="btn btn-primary rounded-pill fw-bold"
                                            style={{ pointerEvents: 'auto', position: 'relative', zIndex: 10004 }}
                                        >
                                            <i className="fas fa-user-plus me-2"></i>Create My Account
                                        </Link>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    className="btn btn-link text-muted text-decoration-none small" 
                                    onClick={() => setShowSuccessModal(false)}
                                    style={{ pointerEvents: 'auto', position: 'relative', zIndex: 10004 }}
                                >
                                    Maybe Later
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            <footer className="footer bg-primary text-white pt-16 h-auto">
                <div className="container pb-10 h-auto">
                    <div className="row g-8 h-auto">
                        <div className="col-lg-4 text-dark h-auto pe-lg-5">
                            <Link href="/" className="d-flex align-items-center mb-6 text-decoration-none text-white h-auto">
                                <img src="/assets/img/logo/Logo2-transparent.png" alt="Nyalife HMS" height="60" className="bg-white rounded-3 p-2" />
                            </Link>
                            <p className="opacity-75 small mb-8 text-white">
                                Pioneering womens health with advanced technology and compassionate care. Our clinic is dedicated to your well-being at every stage of life.
                            </p>
                        </div>
                        <div className="col-lg-8 text-dark h-auto">
                            <div className="row g-4 justify-content-between h-auto">
                                <div className="col-sm-4 text-dark h-auto">
                                    <h5 className="fw-bold mb-4 text-white">Navigation</h5>
                                    <ul className="list-unstyled opacity-75 d-grid gap-2">
                                        <li><a href="#about" className="text-white text-decoration-none">About Us</a></li>
                                        <li><a href="#services" className="text-white text-decoration-none">Our Services</a></li>
                                        <li><a href="#blog" className="text-white text-decoration-none">Journal</a></li>
                                        <li><a href="#contact" className="text-white text-decoration-none">Contact</a></li>
                                    </ul>
                                </div>
                                <div className="col-sm-4 text-dark h-auto">
                                    <h5 className="fw-bold mb-4 text-white">Patient Portal</h5>
                                    <ul className="list-unstyled opacity-75 d-grid gap-2">
                                        <li><Link href={route('login')} className="text-white text-decoration-none">Login</Link></li>
                                        {!auth.user && <li><a href="#appointment" className="text-white text-decoration-none">Book Visit</a></li>}
                                        <li><Link href="#" className="text-white text-decoration-none">Privacy Policy</Link></li>
                                    </ul>
                                </div>
                                <div className="col-sm-4 text-dark h-auto">
                                    <h5 className="fw-bold mb-4 text-white">Newsletter</h5>
                                    <div className="input-group mb-3">
                                        <input type="text" className="form-control bg-white bg-opacity-10 border-0 text-white placeholder-white opacity-75 rounded-start-pill" placeholder="Email" />
                                        <button className="btn btn-light rounded-end-pill px-3"><i className="fas fa-paper-plane text-primary"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <div className="py-3 border-top border-white border-opacity-10" style={{ backgroundColor: '#058b7c' }}>
                <div className="container">
                    <div className="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                        <div className="text-white opacity-75 small">
                            © {new Date().getFullYear()} Nyalife Womens Clinic. All Rights Reserved.
                        </div>
                        <a href="https://www.okjtech.co.ke" target="_blank" rel="noopener noreferrer" className="d-flex align-items-center gap-2 text-white text-decoration-none opacity-75 hover-opacity-100 small transition-all">
                            <span style={{ fontSize: '0.7rem' }}>Powered by</span>
                            <img src="/assets/img/OKJTechLogo-White_Transparent.png" alt="OKJTech" style={{ height: '20px', width: 'auto', display: 'inline-block', filter: 'brightness(1.5)' }} />
                        </a>
                    </div>
                </div>
            </div>

            <style>{`
                :root { --bs-primary: #ec4899; --bs-primary-rgb: 236, 72, 153; }
                .text-gray-900 { color: #111827; }
                .text-gray-700 { color: #374151; }
                .text-gray-600 { color: #4b5563; }
                .bg-pink-50 { background-color: #fdf2f8; }
                .rounded-3xl { border-radius: 1.5rem; }
                .section-title-main { font-size: 3.5rem; line-height: 1.2; position: relative; margin-bottom: 2rem; }
                @media (max-width: 768px) { .section-title-main { font-size: 2.5rem; } }
                .hover-lift:hover { transform: translateY(-10px); }
                .transition-all { transition: all 0.3s ease; }
                
                /* Header Visibility & Sticky Fixes */
                .navbar { position: sticky !important; top: 0 !important; z-index: 10000 !important; background-color: #058b7c !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
                .navbar .nav-link, .navbar .navbar-brand { color: #ffffff !important; opacity: 1 !important; visibility: visible !important; }
                .navbar .nav-link:hover, .navbar .nav-link.active { color: #ffffff !important; text-decoration: underline !important; }
                .navbar .btn { opacity: 1 !important; visibility: visible !important; }
                .navbar-actions { display: flex !important; }
                
                @media (max-width: 991px) {
                    .navbar-collapse { 
                        background: #058b7c !important; 
                        padding: 1.5rem !important; 
                        border-radius: 0 0 1.5rem 1.5rem !important; 
                        margin-top: 0.5rem !important; 
                        box-shadow: 0 15px 30px rgba(0,0,0,0.2) !important;
                    }
                    .navbar-actions { border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem; }
                }
            `}</style>
        </>
    );
}
