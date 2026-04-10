import React from 'react';
import { Link } from '@inertiajs/react';

export default function HeroSection({ cms, isLoggedIn }) {
    const slides = [
        cms.hero_slide_1 || '/assets/img/slider/slider-1.jpg',
        cms.hero_slide_2 || '/assets/img/slider/slider-2.jpg',
        cms.hero_slide_3 || '/assets/img/slider/slider-3.jpg',
        cms.hero_slide_4 || '/assets/img/slider/slider-4.jpg'
    ];

    const cards = [
        { title: cms.hero_card_1_title || 'Obstetrics', text: cms.hero_card_1_text, icon: 'fa-user-md' },
        { title: cms.hero_card_2_title || 'Gynecology', text: cms.hero_card_2_text, icon: 'fa-heartbeat' },
        { title: cms.hero_card_3_title || 'Laboratory', text: cms.hero_card_3_text, icon: 'fa-microscope' },
        { title: cms.hero_card_4_title || 'Pharmacy', text: cms.hero_card_4_text, icon: 'fa-pills' }
    ];

    const getImageUrl = (path) => {
        if (!path) return '';
        if (path.startsWith('cms/') || !path.startsWith('/')) {
            const cleanPath = path.replace(/^\/storage\//, '').replace(/^cms\//, 'cms/');
            return `/storage/${cleanPath}`;
        }
        return path;
    };

    return (
        <section className="hero hero-image">
            {slides.map((slide, idx) => (
                <div 
                    key={`slide-${idx}`} 
                    className={`hero-slide slide-${idx + 1} ${idx === 0 ? 'active' : ''}`}
                    style={{ backgroundImage: `url(${getImageUrl(slide)})` }}
                ></div>
            ))}
            
            <div className="hero-overlay overlay-slide-1"></div>
            
            <div className="container position-relative h-100">
                <div className="row h-100 align-items-center">
                    <div className="col-lg-9 text-dark h-auto">
                        <div className="hero-content w-100">
                            <h1 className="hero-title">{cms.hero_title || 'Nyalife Hospital Management System'}</h1>
                            <h2 className="hero-subtitle mb-4">{cms.hero_subtitle || 'Specialized Obstetrics & Gynecology Care'}</h2>
                            
                            <div className="why-join mt-2">
                                <h3 className="h4 border-bottom border-white border-opacity-25 pb-2 mb-4 text-white d-none d-md-block">Our Core Services</h3>
                                <div className="row g-3 h-auto d-none d-md-flex">
                                    {cards.map((card, idx) => (
                                        <div key={`card-${idx}`} className="col-lg-3 col-md-3 col-sm-6 col-6 mb-3 text-dark h-auto">
                                            <div className="why-join-item blur-bg tooltip-trigger" title={card.text}>
                                                <a href="#services" className="text-decoration-none text-white">
                                                    <i className={`fas ${card.icon}`}></i>
                                                    <p className="mb-0 small fw-bold">{card.title}</p>
                                                </a>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                                <div className="mt-4 d-flex flex-wrap justify-content-center justify-content-md-start h-auto">
                                    {!isLoggedIn ? (
                                        <>
                                            <a href="#appointment" className="btn btn-secondary btn-hero me-2 mb-2">
                                                <i className="fas fa-calendar-check me-1"></i> Book Appointment
                                            </a>
                                            <Link href={route('login')} className="btn btn-light btn-hero mb-2">
                                                <i className="fas fa-sign-in-alt me-1"></i> Patient Login
                                            </Link>
                                        </>
                                    ) : (
                                        <Link href={route('dashboard')} className="btn btn-secondary btn-hero me-2 mb-2">
                                            <i className="fas fa-tachometer-alt me-1"></i> Go to Dashboard
                                        </Link>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div className="hero-controls">
                <div className="hero-arrow prev" id="prev-slide">
                    <i className="fas fa-chevron-left"></i>
                </div>
                <div className="hero-dots">
                    {slides.map((_, idx) => (
                        <div key={`dot-${idx}`} className={`hero-dot ${idx === 0 ? 'active' : ''}`}></div>
                    ))}
                </div>
                <div className="hero-arrow next" id="next-slide">
                    <i className="fas fa-chevron-right"></i>
                </div>
            </div>
            <style>{`
                .tooltip-trigger { cursor: help; }
                .hero-slide { transition: opacity 1s ease-in-out; background-size: cover; background-position: center; }
                .hero-title { font-size: 4rem; font-weight: 800; line-height: 1.1; margin-bottom: 1.5rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
                @media (max-width: 991px) { .hero-title { font-size: 3rem; } }
            `}</style>
        </section>
    );
}
