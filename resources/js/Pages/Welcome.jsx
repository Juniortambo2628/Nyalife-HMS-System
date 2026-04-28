import { Link, Head, useForm } from '@inertiajs/react';
import React, { useEffect } from 'react';
import HeroSection from './Welcome/HeroSection';
import AppointmentSection from './Welcome/AppointmentSection';
import AboutSection from './Welcome/AboutSection';
import ServicesSection from './Welcome/ServicesSection';
import BlogSection from './Welcome/BlogSection';
import ContactSection from './Welcome/ContactSection';
import InsuranceCarousel from '@/Components/InsuranceCarousel';
import { Toaster } from 'react-hot-toast';

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
        <div className="landing-wrapper">
            <Toaster 
                position="top-right" 
                reverseOrder={false} 
                toastOptions={{
                    className: 'premium-toast',
                    style: {
                        borderRadius: '16px',
                        background: '#333',
                        color: '#fff',
                        boxShadow: '0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1)',
                        padding: '16px 24px',
                        fontWeight: '600',
                        fontSize: '14px',
                        letterSpacing: '0.025em',
                    },
                    success: {
                        style: { background: '#10b981' },
                        iconTheme: { primary: '#fff', secondary: '#10b981' },
                    },
                    error: {
                        style: { background: '#ef4444' },
                        iconTheme: { primary: '#fff', secondary: '#ef4444' },
                    },
                }}
            />
            <Head title="Nyalife Women's Clinic - Specialized O&G Care" />
            
            {/* Elegant Navbar */}
            <nav className="navbar navbar-expand-lg sticky-top landing-navbar">
                <div className="container d-flex align-items-center justify-content-between">
                    <Link className="navbar-brand d-flex align-items-center m-0" href="/">
                        <div className="bg-white rounded-xl p-1 shadow-sm me-3 border border-pink-100">
                            <img src="/assets/img/logo/Logo2-transparent.png" alt="Nyalife" height="42" />
                        </div>
                        <span className="fw-extrabold fs-3 text-white tracking-tightest">NYALIFE <span className="fw-light opacity-75">HMS</span></span>
                    </Link>
                    
                    <button className="navbar-toggler border-0 shadow-none text-white ms-auto me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <i className="fas fa-bars"></i>
                    </button>
                    
                    <div className="collapse navbar-collapse justify-content-center" id="navbarNav">
                        <ul className="navbar-nav mx-auto mb-2 mb-lg-0 align-items-center gap-3">
                            {['Home', 'About', 'Services', 'Journal', 'Contact'].map((item) => (
                                <li className="nav-item" key={item}>
                                    <a className="nav-link px-3 text-white fw-medium header-nav-link" href={item === 'Home' ? '/' : `#${item.toLowerCase()}`}>{item}</a>
                                </li>
                            ))}
                            
                            <li className="nav-item d-lg-none mt-4 border-top border-white border-opacity-10 pt-4 w-100">
                                <div className="d-flex flex-column gap-3 px-2 pb-3">
                                    {auth.user ? (
                                        <Link href={route('dashboard')} className="btn btn-outline-light rounded-pill px-4 py-3 fw-medium w-100 shadow-sm">
                                            <i className="fas fa-tachometer-alt me-2"></i>Dashboard
                                        </Link>
                                    ) : (
                                        <>
                                            <Link href={route('login.patient')} className="btn btn-outline-light rounded-pill px-4 py-3 fw-medium w-100 shadow-sm">
                                                <i className="fas fa-sign-in-alt me-2"></i>Patient Login
                                            </Link>
                                            <Link href={route('login.staff')} className="btn btn-outline-light rounded-pill px-4 py-3 fw-medium w-100 shadow-sm">
                                                <i className="fas fa-user-md me-2"></i>Staff Portal
                                            </Link>
                                        </>
                                    )}
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div className="d-none d-lg-flex gap-3 align-items-center">
                        {auth.user ? (
                            <Link href={route('dashboard')} className="btn btn-outline-light rounded-pill px-4 py-2.5 fw-medium shadow-sm hover-lift">
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link href={route('login.patient')} className="btn btn-outline-light rounded-pill px-4 py-2.5 fw-medium shadow-sm hover-lift">
                                    Patient Login
                                </Link>
                                <Link href={route('login.staff')} className="btn btn-outline-light rounded-pill px-4 py-2.5 fw-medium shadow-sm hover-lift">
                                    Staff Portal
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </nav>

            <main className="landing-main overflow-hidden">
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

                <div className="section-rhythm-sm bg-gray-50 border-top border-gray-100">
                    <InsuranceCarousel />
                </div>
            </main>

            {/* Success Modal */}
            {showSuccessModal && (
                <div className="modal show d-block landing-modal-backdrop">
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 rounded-4 shadow-2xl p-4 overflow-hidden position-relative">
                            <div className="modal-body text-center py-5 position-relative z-10">
                                <div className="bg-success-subtle text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-5 shadow-sm border border-success-subtle w-[100px] h-[100px]">
                                    <i className="fas fa-check-circle fa-3x"></i>
                                </div>
                                <h3 className="fw-bold text-gray-900 mb-3 h2">Request Transmitted!</h3>
                                <p className="text-muted mb-5 leading-relaxed font-medium">
                                    Our clinical coordinators will contact you at <span className="text-primary fw-bold">{guestData?.email}</span> to finalize your consultation schedule.
                                </p>
                                <div className="d-grid gap-3">
                                    <Link 
                                        href={`/register?name=${encodeURIComponent(guestData?.name)}&email=${encodeURIComponent(guestData?.email)}&phone=${encodeURIComponent(guestData?.phone)}`} 
                                        className="btn btn-primary btn-lg rounded-pill fw-semibold py-3 shadow-lg"
                                    >
                                        <i className="fas fa-user-plus me-2"></i>Complete Profile Registration
                                    </Link>
                                    <button type="button" className="btn btn-light btn-lg rounded-pill text-muted fw-medium py-3" onClick={() => setShowSuccessModal(false)}>Dismiss</button>
                                </div>
                            </div>
                            <div className="position-absolute top-0 end-0 p-5 opacity-5">
                                <i className="fas fa-calendar-check fa-10x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Footer */}
            <footer className="footer-elegant py-24 text-white overflow-hidden" style={{ minHeight: '600px' }}>
                <div className="container pb-5">
                    <div className="row g-5 mb-16">
                        <div className="col-lg-4 pe-lg-16">
                            <div className="bg-white rounded-2xl p-2 d-inline-block shadow-sm mb-5">
                                <img src="/assets/img/logo/Logo2-transparent.png" alt="Logo" height="50" />
                            </div>
                            <p className="opacity-75 mb-8 leading-relaxed font-medium">
                                Delivering specialized healthcare with clinical excellence and compassionate innovation. Your wellness journey, guided by expertise.
                            </p>
                                <div className="d-flex gap-3">
                                    <a href="https://www.instagram.com/nyalife_womenshealth" target="_blank" rel="noopener noreferrer" className="social-link"><i className="fab fa-instagram"></i></a>
                                    <a href="https://www.linkedin.com/company/nyalife-women-s-health/" target="_blank" rel="noopener noreferrer" className="social-link"><i className="fab fa-linkedin-in"></i></a>
                                </div>
                            </div>
                        
                        <div className="col-lg-2 col-md-6">
                            <h6 className="fw-extrabold mb-5 extra-small text-uppercase tracking-widest opacity-50">CLINICAL SERVICES</h6>
                            <ul className="list-unstyled footer-links space-y-3">
                                <li><a href="#services">Prenatal Diagnostics</a></li>
                                <li><a href="#services">Gynaecological Care</a></li>
                                <li><a href="#services">Family Planning</a></li>
                                <li><a href="#services">Fertility Solutions</a></li>
                            </ul>
                        </div>

                        <div className="col-lg-2 col-md-6">
                            <h6 className="fw-extrabold mb-5 extra-small text-uppercase tracking-widest opacity-50">DIGITAL PORTAL</h6>
                            <ul className="list-unstyled footer-links space-y-3">
                                <li><Link href={route('login.patient')}>Patient Access</Link></li>
                                <li><Link href={route('login.staff')}>Staff Registry</Link></li>
                                <li><Link href={route('privacy-policy')}>Privacy & Data</Link></li>
                                <li><Link href={route('terms-of-service')}>Terms of Care</Link></li>
                            </ul>
                        </div>

                        <div className="col-lg-4 col-md-12">
                            <h6 className="fw-extrabold mb-5 extra-small text-uppercase tracking-widest opacity-50">COORDINATION CENTER</h6>
                            <div className="space-y-6">
                                <div>
                                    <div className="extra-small fw-bold opacity-50 mb-2 uppercase tracking-widest">Electronic Reach</div>
                                    <p className="mb-1 font-bold">info@nyalifewomensclinic.net</p>
                                    <p className="mb-0 font-bold">nyalifewomenshealth@gmail.com</p>
                                </div>
                                <div>
                                    <div className="extra-small fw-bold opacity-50 mb-2 uppercase tracking-widest">Clinical Hotline</div>
                                    <p className="mb-0 h5 fw-extrabold tracking-tighter">0746 516514</p>
                                </div>
                                <div>
                                    <div className="extra-small fw-bold opacity-50 mb-2 uppercase tracking-widest">Primary Facility</div>
                                    <p className="mb-0 small fw-medium opacity-75">A104, Mlolongo, Mombasa Road, Jempark Complex, Athi River, Kenya</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div className="pt-10 border-top border-white border-opacity-10 d-flex flex-column flex-md-row justify-content-between align-items-center gap-4">
                        <p className="extra-small fw-bold text-uppercase tracking-widest opacity-50 mb-0">© {new Date().getFullYear()} NYALIFE WOMEN'S CLINIC. SYSTEM CLOUD v2.0</p>
                        <a href="https://www.okjtech.co.ke" target="_blank" className="d-flex align-items-center gap-3 text-white text-decoration-none opacity-50 hover-opacity-100 transition-all">
                            <span className="extra-small fw-bold text-uppercase tracking-widest">Engineered by</span>
                            <img 
                                src="/assets/img/OKJTechLogo-White_Transparent.png" 
                                alt="OKJTech" 
                                className="footer-logo-fixed"
                            />
                        </a>
                    </div>
                </div>
            </footer>
        </div>
    );
}
