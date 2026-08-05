import { Link, Head, useForm, usePage } from '@inertiajs/react';
import React, { useEffect } from 'react';
import HeroSection from './Welcome/HeroSection';
import AppointmentSection from './Welcome/AppointmentSection';
import AboutSection from './Welcome/AboutSection';
import ServicesSection from './Welcome/ServicesSection';
import BlogSection from './Welcome/BlogSection';
import ContactSection from './Welcome/ContactSection';
import FooterSection from './Welcome/FooterSection';
import InsuranceCarousel from '@/Components/InsuranceCarousel';
import { Toaster, toast } from 'react-hot-toast';

export default function Welcome({ auth, laravelVersion, phpVersion, blogs = [], cms = {}, serviceTabs = [] }) {
    const { flash } = usePage().props;
    const sectionOrder = (cms.landing_page_order || 'hero,appointment,about,services,blog,contact').split(',');
    const displayBlogs = blogs.slice(0, 3);

    useEffect(() => {
        document.body.classList.add('landing');
        return () => document.body.classList.remove('landing');
    }, []);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        phone: '',
        date: '',
        time: '',
        type: 'in_person',
        reason: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('appointments.guest.store'));
    };

    const newsletterForm = useForm({
        email: '',
        name: '',
    });

    const handleNewsletterSubmit = (e) => {
        e.preventDefault();
        newsletterForm.post(route('newsletter.subscribe'), {
            preserveScroll: true,
            onSuccess: () => newsletterForm.reset(),
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
            <Head title="Nyalife Women's Clinic - Specialized OBGYN Care" />

            {/* Elegant Navbar */}
            <nav className="navbar navbar-expand-lg sticky-top landing-navbar">
                <div className="container d-flex align-items-center justify-content-between">
                    <Link className="navbar-brand d-flex align-items-center m-0" href="/">
                        <div className="bg-white rounded-xl p-1 shadow-sm me-3 border border-pink-100">
                            <img src="/assets/img/logo/Logo2-transparent.png" alt="Nyalife" height="42" />
                        </div>
                        <span className="fw-extrabold fs-3 text-white tracking-tightest">
                            NYALIFE <span className="fw-light opacity-75">HMS</span>
                        </span>
                    </Link>

                    <button
                        className="navbar-toggler border-0 shadow-none text-white ms-auto me-2"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#navbarNav"
                    >
                        <i className="fas fa-bars"></i>
                    </button>

                    <div className="collapse navbar-collapse justify-content-center" id="navbarNav">
                        <ul className="navbar-nav mx-auto mb-2 mb-lg-0 align-items-center gap-3">
                            {['Home', 'About', 'Services', 'Journal', 'Contact'].map((item) => (
                                <li className="nav-item" key={item}>
                                    <a
                                        className="nav-link px-3 text-white fw-medium header-nav-link"
                                        href={item === 'Home' ? '/' : `#${item.toLowerCase()}`}
                                    >
                                        {item}
                                    </a>
                                </li>
                            ))}

                            <li className="nav-item d-lg-none mt-4 border-top border-white border-opacity-10 pt-4 w-100">
                                <div className="d-flex flex-column gap-3 px-2 pb-3">
                                    {auth.user ? (
                                        <Link
                                            href={route('dashboard')}
                                            className="btn btn-outline-light rounded-pill px-4 py-3 fw-medium w-100 shadow-sm"
                                        >
                                            <i className="fas fa-tachometer-alt me-2"></i>Dashboard
                                        </Link>
                                    ) : (
                                        <>
                                            <Link
                                                href={route('login.patient')}
                                                className="btn btn-outline-light rounded-pill px-4 py-3 fw-medium w-100 shadow-sm"
                                            >
                                                <i className="fas fa-sign-in-alt me-2"></i>Patient Login
                                            </Link>
                                            <Link
                                                href={route('login.staff')}
                                                className="btn btn-outline-light rounded-pill px-4 py-3 fw-medium w-100 shadow-sm"
                                            >
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
                            <Link
                                href={route('dashboard')}
                                className="btn btn-outline-light rounded-pill px-4 py-2.5 fw-medium shadow-sm hover-lift"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={route('login.patient')}
                                    className="btn btn-outline-light rounded-pill px-4 py-2.5 fw-medium shadow-sm hover-lift"
                                >
                                    Patient Login
                                </Link>
                                <Link
                                    href={route('login.staff')}
                                    className="btn btn-outline-light rounded-pill px-4 py-2.5 fw-medium shadow-sm hover-lift"
                                >
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
                    if (name === 'appointment' && !auth.user)
                        return (
                            <AppointmentSection
                                key="appointment"
                                data={data}
                                setData={setData}
                                handleSubmit={handleSubmit}
                                processing={processing}
                                errors={errors}
                            />
                        );
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

            <FooterSection
                cms={cms}
                serviceTabs={serviceTabs}
                newsletterForm={newsletterForm}
                onNewsletterSubmit={handleNewsletterSubmit}
            />
        </div>
    );
}
