import { Link, Head, useForm } from '@inertiajs/react';
import React, { useEffect } from 'react';
import HeroSection from './Welcome/HeroSection';
import AppointmentSection from './Welcome/AppointmentSection';
import AboutSection from './Welcome/AboutSection';
import ServicesSection from './Welcome/ServicesSection';
import BlogSection from './Welcome/BlogSection';
import ContactSection from './Welcome/ContactSection';
import InsuranceCarousel from '@/Components/InsuranceCarousel';

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

    return (
        <>
            <Head title="Nyalife Women's Clinic - Specialized O&G Care" />
            
            {/* Elegant Navbar - Forced Visibility for Nav Links */}
            <nav className="navbar navbar-expand-lg border-bottom border-white border-opacity-10 sticky-top transition-all py-3 shadow-lg" style={{ backgroundColor: '#d7056aff', backdropFilter: 'blur(10px)', zIndex: 1000 }}>
                <div className="container d-flex align-items-center justify-content-between">
                    <Link className="navbar-brand d-flex align-items-center m-0" href="/">
                        <img src="/assets/img/logo/Logo2-transparent.png" alt="Nyalife HMS" height="42" className="me-2 bg-white rounded-2 p-1" />
                        <span className="fw-bold fs-4 text-white tracking-tight">Womens <span className="fw-light">Health Clinic</span></span>
                    </Link>
                    
                    <button className="navbar-toggler border-0 shadow-none text-white ms-auto me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <i className="fas fa-bars"></i>
                    </button>
                    
                    {/* Centered Navigation Links with Forced Desktop Visibility */}
                    <div className="collapse navbar-collapse justify-content-center" id="navbarNav">
                        <ul className="navbar-nav mx-auto mb-2 mb-lg-0 align-items-center gap-1">
                            <li className="nav-item">
                                <a className="nav-link px-3 text-white fw-bold header-nav-link" href="/">Home</a>
                            </li>
                            <li className="nav-item">
                                <a className="nav-link px-3 text-white fw-bold header-nav-link" href="#about">About</a>
                            </li>
                            <li className="nav-item">
                                <a className="nav-link px-3 text-white fw-bold header-nav-link" href="#services">Services</a>
                            </li>
                            <li className="nav-item">
                                <a className="nav-link px-3 text-white fw-bold header-nav-link" href="#blog">Journal</a>
                            </li>
                            <li className="nav-item">
                                <a className="nav-link px-3 text-white fw-bold header-nav-link" href="#contact">Contact</a>
                            </li>
                            
                            <li className="nav-item d-lg-none mt-3 border-top border-white border-opacity-25 pt-3 w-100">
                                <div className="d-flex flex-column gap-2 px-2 pb-3">
                                    {auth.user ? (
                                        <Link href={route('dashboard')} className="btn btn-outline-light btn-md rounded-pill px-4 py-2 fw-bold w-100">
                                            <i className="fas fa-tachometer-alt me-2"></i>Dashboard
                                        </Link>
                                    ) : (
                                        <>
                                            <Link href={route('login.patient')} className="btn btn-outline-light rounded-pill px-4 py-2 fw-bold w-100">
                                                <i className="fas fa-sign-in-alt me-2"></i>Patient Login
                                            </Link>
                                            <Link href={route('login.staff')} className="btn btn-white bg-white text-primary rounded-pill px-4 py-2 fw-bold w-100">
                                                <i className="fas fa-user-md me-2"></i>Staff Portal
                                            </Link>
                                        </>
                                    )}
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div className="d-none d-lg-flex gap-2 align-items-center">
                        {auth.user ? (
                            <Link href={route('dashboard')} className="btn btn-outline-light rounded-pill px-4 fw-bold btn-md">
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link href={route('login.patient')} className="btn btn-outline-light rounded-pill px-4 fw-bold btn-md">
                                    Patient login
                                </Link>
                                <Link href={route('login.staff')} className="btn btn-outline-light rounded-pill px-4 fw-bold btn-md">
                                    Staff portal
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </nav>

            <div className="landing-main overflow-hidden">
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

                <InsuranceCarousel />
            </div>

            {/* Success Modal */}
            {showSuccessModal && (
                <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.6)', zIndex: 9999 }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 rounded-4 shadow-lg p-4">
                            <div className="modal-body text-center py-5">
                                <div className="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style={{ width: '80px', height: '80px' }}>
                                    <i className="fas fa-check fa-2x"></i>
                                </div>
                                <h3 className="fw-bold mb-3">Request Received!</h3>
                                <p className="text-muted mb-4 lead">Our team will contact you shortly at <strong>{guestData?.email}</strong> to coordinate your visit.</p>
                                <div className="d-grid gap-2">
                                    <Link 
                                        href={`/register?name=${encodeURIComponent(guestData?.name)}&email=${encodeURIComponent(guestData?.email)}&phone=${encodeURIComponent(guestData?.phone)}`} 
                                        className="btn btn-primary btn-lg rounded-pill fw-bold"
                                    >
                                        <i className="fas fa-user-plus me-2"></i>Complete Registration
                                    </Link>
                                    <button type="button" className="btn btn-light btn-lg rounded-pill text-muted fw-bold" onClick={() => setShowSuccessModal(false)}>Done</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Footer */}
            <footer className="footer-elegant pt-5 text-white overflow-hidden">
                <div className="container py-3">
                    <div className="row g-5 mb-5">
                        <div className="col-lg-4 pe-lg-5">
                            <img src="/assets/img/logo/Logo2-transparent.png" alt="Logo" height="50" className="mb-4 bg-white rounded p-1" />
                            <p className="opacity-75 mb-4 footer-text">
                                Providing exceptional obstetrics and gynecology services. Our commitment is to offer compassionate, evidence-based care tailored to your unique journey.
                            </p>
                            <div className="d-flex gap-2">
                                <a href="https://www.instagram.com/nyalife_womenshealth" target="_blank" rel="noopener noreferrer" className="social-link"><i className="fab fa-instagram fa-sm"></i></a>
                                <a href="https://www.linkedin.com/company/nyalife-women-s-health/" target="_blank" rel="noopener noreferrer" className="social-link"><i className="fab fa-linkedin-in fa-sm"></i></a>
                            </div>
                        </div>
                        
                        <div className="col-lg-2 col-md-4">
                            <h6 className="fw-bold mb-4 border-bottom border-white border-opacity-10 pb-2 d-inline-block">Services</h6>
                            <ul className="list-unstyled footer-links">
                                <li><a href="#services">Prenatal care</a></li>
                                <li><a href="#services">Gynecology</a></li>
                                <li><a href="#services">Family planning</a></li>
                                <li><a href="#services">Fertility support</a></li>
                            </ul>
                        </div>

                        <div className="col-lg-2 col-md-4">
                            <h6 className="fw-bold mb-4 border-bottom border-white border-opacity-10 pb-2 d-inline-block">Portal</h6>
                            <ul className="list-unstyled footer-links">
                                <li><Link href={route('login.patient')}>Patient login</Link></li>
                                <li><Link href={route('login.staff')}>Staff login</Link></li>
                                <li><Link href={route('privacy-policy')}>Privacy policy</Link></li>
                                <li><Link href={route('terms-of-service')}>Terms of service</Link></li>
                            </ul>
                        </div>

                        <div className="col-lg-4 col-md-4">
                            <h6 className="fw-bold mb-4 border-bottom border-white border-opacity-10 pb-2 d-inline-block">Contact</h6>
                            <div className="contact-info footer-text">
                                <div className="mb-4 mt-4">
                                    <h6 className="fw-bold opacity-50 mb-1 border-bottom border-white border-opacity-70 pb-2 d-inline-block">Email</h6>
                                    <p className="mb-1 footer-text">info@nyalifewomensclinic.net</p>
                                    <p className="mb-0 footer-text">nyalifewomenshealth@gmail.com</p>
                                </div>
                                <div className="mb-4 mt-4">
                                    <h6 className="fw-bold opacity-50 mb-1 border-bottom border-white border-opacity-70 pb-2 d-inline-block">Phone</h6>
                                    <p className="mb-0 footer-text">0746 516514</p>
                                </div>
                                <div className="mb-4 mt-4">
                                    <h6 className="fw-bold opacity-50 mb-2 border-bottom border-white border-opacity-90 pb-2 d-inline-block">Location</h6>
                                    <p className="mb-0 footer-text">A104, Mlolongo along Mombasa road sidelane at Jempark office complex building, Athi River, Kenya</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div className="py-4 px-4 d-flex flex-column flex-md-row justify-content-between align-items-center">
                        <p className="opacity-50 mb-0 footer-text">© {new Date().getFullYear()} Nyalife Women's Clinic. All rights reserved.</p>
                        <a href="https://www.okjtech.co.ke" target="_blank" className="d-flex align-items-center gap-2 text-white text-decoration-none opacity-50 hover-opacity-100 transition-all">
                            <span className="small footer-text">Made with precision, care and intention by</span>
                            <img 
                                src="/assets/img/OKJTechLogo-White_Transparent.png" 
                                alt="OKJTech" 
                                className="opacity-75 footer-logo-fixed"
                                style={{ height: '35px', width: 'auto', display: 'inline-block', objectFit: 'contain' }}
                            />
                        </a>
                    </div>
                </div>
            </footer>

            <style>{`
                .footer-elegant { 
                    background: linear-gradient(180deg, #058b7c 0%, #036b5e 100%); 
                    position: relative;
                    font-family: inherit;
                }
                .footer-text, .footer-links a{
                    font-family: inherit;
                    color: white !important;
                    font-size: 1rem;
                    line-height: 1.5;
                }

                .footer-elegant h6 {
                    font-family: inherit;
                    color: white !important;
                    font-size: 1.4rem;
                    font-weight: 600;
                }

                .footer-text h6 {
                    font-family: inherit;
                    color: white !important;
                    font-size: 1.25rem;
                    font-weight: 600;
                }

                .footer-text p {
                    font-family: inherit;
                    color: white !important;
                    font-size: 1rem;
                    line-height: 1.5;
                }

                .footer-links li { margin-bottom: 0.3rem; }
                .footer-links a { opacity: 1; text-decoration: none; font-size: .9rem; transition: all 0.2s; }
                .footer-links a:hover { opacity: 1; transform: translateX(3px); }
                .social-link { width: 42px; height: 42px; background: rgba(255,255,255,0.1); color: #fff !important; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.3s; }
                .social-link:hover { background: #fff; color: #058b7c !important; transform: scale(1.1); }
                
                .header-nav-link {
                    color: #ffffff !important;
                    display: block !important;
                    visibility: visible !important;
                    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
                }
                .footer-logo-fixed {
                    height: 34px !important;
                    width: auto !important;
                    max-width: 100px !important;
                }
                .hover-translate-y:hover { transform: translateY(-2px); }
                .transition-all { transition: all 0.3s ease-out; }
            `}</style>
        </>
    );
}
