import React, { useState, useEffect, useRef } from 'react';
import { Link, usePage } from '@inertiajs/react';
import axios from 'axios';

const ContextSwitcher = () => {
    const { url, component, props } = usePage();
    const [isOpen, setIsOpen] = useState(false);
    const [options, setOptions] = useState({ modules: [], subjects: [] });
    const [loading, setLoading] = useState(false);
    const widgetRef = useRef(null);

    const isDashboard = url.startsWith('/dashboard') || 
                      url.startsWith('/appointments') || 
                      url.startsWith('/consultations') || 
                      url.startsWith('/lab') || 
                      url.startsWith('/patients') || 
                      url.startsWith('/pharmacy') || 
                      url.startsWith('/billing');

    useEffect(() => {
        if (isOpen) {
            fetchContextOptions();
        }
    }, [isOpen, url]);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (widgetRef.current && !widgetRef.current.contains(event.target)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const fetchContextOptions = async () => {
        setLoading(true);
        try {
            const response = await axios.get('/api/context-switching', { params: { current_url: url } });
            setOptions(response.data);
        } catch (error) {
            console.error('Failed to fetch context options', error);
        } finally {
            setLoading(false);
        }
    };

    if (!isDashboard) return null;

    return (
        <div className="fixed-bottom d-flex justify-content-end p-4" style={{ zIndex: 1060, pointerEvents: 'none' }} ref={widgetRef}>
            <div className="position-relative" style={{ pointerEvents: 'auto' }}>
                {isOpen && (
                    <div 
                        className="position-absolute bottom-100 end-0 mb-3 bg-white shadow-2xl rounded-2xl border border-gray-100 overflow-hidden animate-in slide-in-from-bottom-4 duration-300"
                        style={{ width: '280px', maxHeight: '450px', overflowY: 'auto' }}
                    >
                        <div className="p-3 border-bottom bg-light">
                            <h6 className="fw-bold mb-0 text-gray-800 small text-uppercase tracking-wider">
                                <i className="fas fa-random me-2 text-primary"></i> Context Switcher
                            </h6>
                        </div>

                        {loading ? (
                            <div className="p-5 text-center">
                                <div className="spinner-border spinner-border-sm text-primary" role="status"></div>
                            </div>
                        ) : (
                            <div className="p-2">
                                {/* Current Context Section */}
                                <div className="px-2 py-1 mb-2">
                                    <div className="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Jump to Module</div>
                                    <div className="grid grid-cols-2 gap-1">
                                        {[
                                            { name: 'Dashboard', icon: 'fa-th-large', color: 'text-primary', url: '/dashboard' },
                                            { name: 'Patients', icon: 'fa-users', color: 'text-pink-500', url: '/patients' },
                                            { name: 'Appts', icon: 'fa-calendar-alt', color: 'text-info', url: '/appointments' },
                                            { name: 'Consults', icon: 'fa-stethoscope', color: 'text-success', url: '/consultations' },
                                            { name: 'Pharmacy', icon: 'fa-pills', color: 'text-purple', url: '/pharmacy' },
                                            { name: 'Billing', icon: 'fa-file-invoice-dollar', color: 'text-warning', url: '/billing' }
                                        ].map(mod => (
                                            <Link 
                                                key={mod.name}
                                                href={mod.url}
                                                className={`d-flex flex-column align-items-center p-2 rounded-xl hover-bg-light text-decoration-none transition-all ${url === mod.url ? 'bg-primary-subtle' : ''}`}
                                                onClick={() => setIsOpen(false)}
                                            >
                                                <i className={`fas ${mod.icon} ${mod.color} mb-1`}></i>
                                                <span className="extra-small font-bold text-gray-600">{mod.name}</span>
                                            </Link>
                                        ))}
                                    </div>
                                </div>

                                {/* Subjects Section (Intelligent Switching) */}
                                {options.subjects && options.subjects.length > 0 && (
                                    <div className="px-2 py-1 mt-3 border-top pt-3">
                                        <div className="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Quick Switch {options.subject_type}</div>
                                        <div className="space-y-1">
                                            {options.subjects.map(subject => (
                                                <Link 
                                                    key={subject.id}
                                                    href={subject.url}
                                                    className={`d-flex align-items-center gap-3 p-2 rounded-xl hover-bg-light text-decoration-none transition-all ${url === subject.url ? 'bg-gray-100' : ''}`}
                                                    onClick={() => setIsOpen(false)}
                                                >
                                                    <div className="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold small" style={{ width: '32px', height: '32px' }}>
                                                        {subject.initials}
                                                    </div>
                                                    <div className="flex-1 overflow-hidden">
                                                        <div className="text-sm font-bold text-gray-800 text-truncate">{subject.name}</div>
                                                        <div className="extra-small text-muted text-truncate">{subject.subtext}</div>
                                                    </div>
                                                    {url === subject.url && <i className="fas fa-check-circle text-success extra-small"></i>}
                                                </Link>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                )}

                <button 
                    onClick={() => setIsOpen(!isOpen)}
                    className={`btn shadow-2xl rounded-circle p-0 d-flex align-items-center justify-content-center transition-all duration-300 ${isOpen ? 'btn-primary scale-110' : 'btn-white hover-bg-primary hover-text-white'}`}
                    style={{ width: '56px', height: '56px', border: '1px solid #f3f4f6' }}
                >
                    <i className={`fas ${isOpen ? 'fa-times' : 'fa-exchange-alt'} fs-5`}></i>
                </button>
            </div>

            <style>{`
                .rounded-xl { border-radius: 0.75rem; }
                .rounded-2xl { border-radius: 1.25rem; }
                .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
                .hover-bg-light:hover { background-color: #f9fafb; }
                .hover-bg-primary:hover { background-color: var(--bs-primary); border-color: var(--bs-primary); }
                .hover-text-white:hover { color: white !important; }
                .fs-xs { font-size: 0.65rem; }
                .extra-small { font-size: 0.7rem; }
                .transition-all { transition: all 0.2s ease-in-out; }
            `}</style>
        </div>
    );
};

export default ContextSwitcher;
