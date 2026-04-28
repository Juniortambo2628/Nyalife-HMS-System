import React from 'react';

export default function AboutSection({ cms }) {
    return (
        <section className="section-rhythm-md bg-white" id="about">
            <div className="container">
                <div className="row g-5 align-items-center">
                    <div className="col-lg-6">
                        <div className="about-image-wrapper pe-lg-5 position-relative">
                            <div className="overflow-hidden rounded-3xl shadow-2xl skew-bg">
                                <img 
                                    src={cms.about_image || "/assets/img/service-tabs/nyalife-1.JPG"} 
                                    className="img-fluid transition-transform" 
                                    alt="About Clinic" 
                                />
                            </div>
                            <div className="position-absolute bottom-0 start-0 m-4 bg-primary text-white p-4 rounded-2xl shadow-lg d-none d-md-block">
                                <div className="h2 fw-bold mb-0">15+</div>
                                <div className="small opacity-75">Years Experience</div>
                            </div>
                        </div>
                    </div>
                    <div className="col-lg-6">
                        <div className="about-content">
                            <span className="badge bg-pink-100 text-pink-600 px-3 py-2 rounded-pill mb-3 font-bold text-uppercase tracking-wider">About Us</span>
                            <h2 className="display-5 fw-bold text-gray-900 mb-6 section-title-main">{cms.about_title || "About Nyalife Women's Clinic"}</h2>
                            <p className="lead text-gray-700 mb-6 font-semibold">
                                {cms.about_description || "Dedicated to providing high-quality, compassionate obstetrics and gynecology services."}
                            </p>
                            <p className="text-gray-600 mb-8 leading-relaxed">
                                Our facility is designed to provide a comfortable and welcoming environment for women at every stage of their lives. We combine medical expertise with a personal touch to ensure every patient feels heard and cared for.
                            </p>
                            
                            <div className="row g-4 h-auto">
                                <div className="col-sm-6">
                                    <div className="d-flex align-items-center gap-3">
                                        <div className="avatar-sm bg-pink-100 text-pink-500 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                                            <i className="fas fa-user-md"></i>
                                        </div>
                                        <div>
                                            <h6 className="fw-bold mb-0">Expert Doctors</h6>
                                            <small className="text-muted">Specialized care</small>
                                        </div>
                                    </div>
                                </div>
                                <div className="col-sm-6">
                                    <div className="d-flex align-items-center gap-3">
                                        <div className="avatar-sm bg-blue-100 text-blue-500 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                                            <i className="fas fa-heart"></i>
                                        </div>
                                        <div>
                                            <h6 className="fw-bold mb-0">Compassion</h6>
                                            <small className="text-muted">Gentle approach</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
