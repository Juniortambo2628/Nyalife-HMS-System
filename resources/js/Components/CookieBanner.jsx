import React, { useState, useEffect } from 'react';
import { Link } from '@inertiajs/react';

export default function CookieBanner() {
    const [isVisible, setIsVisible] = useState(false);

    useEffect(() => {
        const consent = localStorage.getItem('cookie-consent');
        if (!consent) {
            const timer = setTimeout(() => {
                setIsVisible(true);
            }, 2000);
            return () => clearTimeout(timer);
        }
    }, []);

    const handleAccept = () => {
        localStorage.setItem('cookie-consent', 'accepted');
        setIsVisible(false);
    };

    const handleNecessary = () => {
        localStorage.setItem('cookie-consent', 'necessary');
        setIsVisible(false);
    };

    if (!isVisible) return null;

    return (
        <div 
            className="cookie-banner fixed-bottom p-4 animate-slide-up"
            style={{ 
                zIndex: 10000,
                background: 'rgba(255, 255, 255, 0.98)',
                backdropFilter: 'blur(15px)',
                boxShadow: '0 -15px 50px rgba(0,0,0,0.15)',
                borderTop: '1px solid rgba(0,0,0,0.1)'
            }}
        >
            <div className="container">
                <div className="row align-items-center">
                    <div className="col-lg-7 mb-3 mb-lg-0 text-start">
                        <div className="d-flex align-items-center">
                            <div className="bg-primary-dark text-white p-3 rounded-circle me-4 shadow-sm d-none d-md-flex align-items-center justify-content-center" style={{ width: '56px', height: '56px' }}>
                                <i className="fas fa-cookie-bite fa-lg"></i>
                            </div>
                            <div>
                                <h5 className="fw-bold mb-1 text-dark">We value your privacy</h5>
                                <p className="mb-0 text-muted small">
                                    We use cookies to enhance your browsing experience and analyze our traffic. 
                                    By clicking "Accept All", you consent to our use of cookies. 
                                    Review our <Link href={route('cookie-policy')} className="text-decoration-none fw-bold text-primary hover-underline">Cookie Policy</Link> for details.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div className="col-lg-5 text-lg-end">
                        <div className="d-flex flex-column flex-sm-row justify-content-lg-end gap-2">
                            <Link 
                                href={route('cookie-policy')}
                                className="btn btn-outline-secondary rounded-pill px-4 btn-sm fw-bold"
                            >
                                Settings
                            </Link>
                            <button 
                                onClick={handleNecessary}
                                className="btn btn-light rounded-pill px-4 btn-sm fw-bold"
                            >
                                Necessary Only
                            </button>
                            <button 
                                onClick={handleAccept}
                                className="btn btn-primary rounded-pill px-4 shadow-sm btn-sm fw-bold"
                            >
                                Accept All
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <style>{`
                .cookie-banner {
                    animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
                }
                .bg-primary-dark { background-color: #058b7c; }
                .hover-underline:hover { text-decoration: underline !important; }
                @keyframes slideUp {
                    from { transform: translateY(100%); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
            `}</style>
        </div>
    );
}
