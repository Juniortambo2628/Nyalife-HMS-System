import React, { useState } from 'react';

export default function ServicesSection({ serviceTabs }) {
    const [activeTab, setActiveTab] = useState(serviceTabs[0]?.id || null);

    return (
        <section className="section-rhythm-md bg-gray-50" id="services">
            <div className="container">
                <div className="text-center mb-16">
                    <span className="badge bg-pink-100 text-pink-600 px-3 py-2 rounded-pill mb-3 font-bold text-uppercase tracking-wider">Specialties</span>
                    <h2 className="display-5 fw-bold text-gray-900 section-title-main">Our Specialized Services</h2>
                </div>

                <div className="card border-0 shadow-lg rounded-3xl overflow-hidden bg-white">
                    <div className="row g-0">
                        <div className="col-lg-4 bg-light border-end">
                            <ul className="nav nav-pills flex-column p-4 gap-2" id="v-pills-tab" role="tablist">
                                {serviceTabs.map((tab) => (
                                    <li key={tab.id} className="nav-item">
                                        <button 
                                            className={`nav-link border-0 text-start w-100 py-3 px-4 rounded-2xl transition-all ${activeTab === tab.id ? 'active shadow-sm' : 'text-gray-600'}`}
                                            onClick={() => setActiveTab(tab.id)}
                                            type="button"
                                        >
                                            <i className={`fas ${tab.icon || 'fa-check-circle'} me-3`}></i>
                                            <span className="fw-extrabold fs-5 text-uppercase tracking-wider">{tab.title}</span>
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </div>
                        <div className="col-lg-8">
                            <div className="tab-content p-4 p-md-5" id="v-pills-tabContent">
                                {serviceTabs.map((tab) => (
                                    <div 
                                        key={tab.id}
                                        className={`tab-pane fade ${activeTab === tab.id ? 'show active' : ''}`}
                                        role="tabpanel"
                                    >
                                        <div className="row g-5 align-items-center">
                                            <div className="col-md-7">
                                                <h3 className="h2 fw-bold text-gray-900 mb-4">{tab.content_title}</h3>
                                                <p className="lead text-pink-500 fw-bold mb-6">{tab.content_lead}</p>
                                                <div className="service-body text-gray-600 leading-relaxed mb-8">
                                                    {tab.content_body?.split('\n').map((line, i) => (
                                                        <p key={i} className="mb-2">{line}</p>
                                                    ))}
                                                </div>
                                                <a href="#appointment" className="btn btn-primary rounded-pill px-6">Enquire Now</a>
                                            </div>
                                            <div className="col-md-5">
                                                <div className="rounded-2xl overflow-hidden shadow-lg border-4 border-white">
                                                    <img 
                                                        src={tab.image_path || "/assets/img/service-tabs/doctor-1.jpg"} 
                                                        className="img-fluid" 
                                                        alt={tab.title} 
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <style>{`
                .nav-pills .nav-link.active,
                .nav-pills .nav-link.active:hover { 
                    background-color: #d7056a !important; 
                    color: white !important; 
                }
                .nav-pills .nav-link:hover:not(.active) { 
                    background-color: #d7056a; 
                    color: white !important; 
                }
            `}</style>
        </section>
    );
}
