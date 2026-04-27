import { Link } from '@inertiajs/react';
import { useEffect } from 'react';
import CookieBanner from '@/Components/CookieBanner';

export default function AuthLayout({ children, image, title, subtitle }) {
    return (
        <div className="auth-layout-container min-vh-100 w-100 overflow-hidden row m-0 p-0">
            {/* Left Side: Background Image */}
            <div 
                className="auth-image-side col-lg-6 d-none d-lg-block position-relative p-0"
                style={{ backgroundImage: `url(${image})` }}
            >
                <div className="auth-overlay-main position-absolute top-0 left-0 w-100 h-100"></div>

                <div className="auth-content-box position-absolute bottom-0 start-0 w-100 p-5 text-white">
                    <h2 className="display-4 fw-extrabold mb-3 text-white tracking-tighter">{title}</h2>
                    <p className="lead fw-bold text-white opacity-90">{subtitle}</p>
                </div>
                
                {/* Back to Home Link */}
                <Link 
                    href="/" 
                    className="position-absolute top-0 start-0 m-4 text-white text-decoration-none d-flex align-items-center fw-bold extra-small tracking-widest hover-opacity-100 transition-all"
                    style={{ zIndex: 10, textShadow: '0 2px 4px rgba(0,0,0,0.5)' }}
                >
                    <i className="fas fa-chevron-left me-2"></i>
                    RETURN TO HOME
                </Link>
            </div>

            {/* Right Side: Authentication Form */}
            <div className="auth-form-side col-12 col-lg-6 bg-white d-flex align-items-center justify-content-center p-4 p-md-5 min-vh-100">
                <div className="auth-form-wrapper w-100">
                    {/* Home Link for Mobile */}
                    <div className="d-lg-none mb-4">    
                        <Link href="/" className="text-primary text-decoration-none fw-bold small">
                            <i className="fas fa-arrow-left me-2"></i> HOME
                        </Link>
                    </div>

                    <div className="auth-form-logo mb-4 text-center">
                        <img src="/assets/img/logo/Logo2-transparent.png" alt="Nyalife HMS" className="mb-5" height="50px" />
                    </div>
                    
                    <div className="auth-form-container">
                        {children}
                    </div>

                    <div className="auth-footer mt-5 text-center text-muted extra-small">
                        <p className="mb-0 fw-bold opacity-75">© {new Date().getFullYear()} Nyalife Women's Clinic.</p>
                        <p className="opacity-40">All rights reserved.</p>
                    </div>
                </div>
            </div>

            <CookieBanner />
        </div>
    );
}
