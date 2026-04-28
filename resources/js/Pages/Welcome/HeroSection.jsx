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
        { title: 'Obstetrics Care', icon: 'fa-user-md' },
        { title: 'Gynecology', icon: 'fa-heartbeat' },
        { title: 'Laboratory', icon: 'fa-microscope' },
        { title: 'Pharmacy', icon: 'fa-pills' }
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
        <section className="hero-modern min-vh-50 w-100 position-relative d-flex align-items-center section-rhythm-sm">
            {/* Background Slides with Stronger Overlay */}
            <div className="hero-background-wrapper position-absolute w-100 h-100 top-0 start-0 overflow-hidden">
                {slides.map((slide, idx) => (
                    <div 
                        key={`slide-${idx}`} 
                        className={`hero-slide position-absolute w-100 h-100 ${idx === 0 ? 'active' : ''}`}
                        style={{ 
                            backgroundImage: `url(${getImageUrl(slide)})`,
                        }}
                    ></div>
                ))}
                {/* Contrast Gradient Overlay */}
                <div className="hero-contrast-overlay position-absolute w-100 h-100 top-0 start-0"></div>
            </div>
            
            <div className="container position-relative py-4 px-3 px-md-5 z-10">
                <div className="row align-items-center">
                    <div className="col-lg-10 text-start text-white">
                        <div className="hero-content animate-in fade-in slide-in-from-left-8 duration-700">
                            <div className="mb-3">
                                <span className="badge bg-pink-100 text-pink-600 px-3 py-2 rounded-pill fw-medium d-inline-block shadow-sm">
                                    Specialized Obstetrics & Gynecology Care
                                </span>
                            </div>
                            
                            <h1 className="hero-main-title fw-extrabold text-white mb-3 display-3 tracking-tightest">
                                {cms.hero_title || 'NYALIFE HMS'}
                            </h1>
                            
                            <p className="text-white opacity-75 mb-4 max-w-2xl fw-normal fs-5">
                                Providing exceptional, compassionate care for every stage of your life. Experience medical excellence with a heart.
                            </p>
                            
                            {/* Compact Grid with Semi-Transparent White Cards */}
                            <div className="row g-2 mb-4 border-top border-bottom border-white border-opacity-10 py-3">
                                {cards.map((card, idx) => (
                                    <div key={`card-${idx}`} className="col-6 col-md-3">
                                        <div className="hero-feature-card rounded-4 p-1 hover-lift transition-all">
                                            <div className="d-flex flex-column align-items-center justify-content-center text-center py-3 px-2">
                                                <div className="avatar-md bg-white bg-opacity-10 text-white rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-inner">
                                                    <i className={`fas ${card.icon} fs-3`}></i>
                                                </div>
                                                <span className="badge bg-pink-500 text-white px-4 py-2 rounded-pill fw-extrabold extra-small tracking-widest d-inline-block shadow-sm">
                                                    {card.title.toUpperCase()}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                            
                            <div className="d-flex flex-wrap gap-3">
                                {!isLoggedIn ? (
                                    <>
                                        <a href="#appointment" className="btn btn-primary px-4 py-2.5 fw-medium rounded-pill shadow-sm hover-lift">
                                            <i className="fas fa-calendar-check me-2"></i> Book Appointment
                                        </a>
                                        <Link href={route('login.patient')} className="btn btn-white bg-white text-gray-900 border-0 px-4 py-2.5 fw-medium rounded-pill shadow-sm hover-lift">
                                            <i className="fas fa-sign-in-alt me-2"></i> Patient Portal
                                        </Link>
                                    </>
                                ) : (
                                    <Link href={route('dashboard')} className="btn btn-primary px-4 py-2.5 fw-medium rounded-pill shadow-sm hover-lift">
                                        <i className="fas fa-tachometer-alt me-2"></i> Access Dashboard
                                    </Link>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
