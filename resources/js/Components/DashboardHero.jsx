import React from 'react';

export default function DashboardHero({ 
    title, 
    subtitle, 
    icon = "fa-heartbeat", 
    children, 
    className = "hero-premium-gradient",
    iconOpacity = "0.15",
    iconSize = "15rem"
}) {
    return (
        <div className={`card shadow-2xl border-0 ${className} text-white overflow-hidden mb-5 position-relative nyl-hero`}>
            {/* Animated Background Elements */}
            <div className="position-absolute top-0 start-0 w-100 h-100 overflow-hidden nyl-hero-bg">
                <div className="hero-circle-1"></div>
                <div className="hero-circle-2"></div>
            </div>

            <div className="card-body p-5 d-flex align-items-center position-relative h-100 nyl-hero-body">
                <div className="w-100">
                    <h1 className="display-5 fw-extrabold text-white mb-3 tracking-tighter">
                        {title}
                    </h1>
                    {subtitle && (
                        <p className="lead mb-0 opacity-90 fw-medium max-w-2xl">
                            {subtitle}
                        </p>
                    )}
                    {children && <div className="mt-4">{children}</div>}
                </div>
                
                <div className="ms-auto d-none d-lg-block">
                    <div className="hero-icon-container">
                        <i 
                            className={`fas ${icon} nyl-hero-icon`} 
                            style={{ fontSize: iconSize, opacity: iconOpacity }}
                        ></i>
                    </div>
                </div>
            </div>
        </div>
    );
}
