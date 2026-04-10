import { useState, useEffect, useRef } from 'react';
import { createPortal } from 'react-dom';

export default function InfoModal({ show, onClose, title, subtitle, icon, tabs = [], initialTab }) {
    const [activeTab, setActiveTab] = useState(initialTab || tabs[0]?.id);
    const panelRef = useRef(null);

    // Reset activeTab when tabs change or modal opens
    useEffect(() => {
        if (show && tabs.length > 0) {
            if (!activeTab || !tabs.find(t => t.id === activeTab)) {
                setActiveTab(initialTab || tabs[0].id);
            }
        }
    }, [show, tabs, initialTab]);

    // Handle escape key
    useEffect(() => {
        const handleEscape = (e) => {
            if (e.key === 'Escape' && show) {
                onClose();
            }
        };
        document.addEventListener('keydown', handleEscape);
        return () => document.removeEventListener('keydown', handleEscape);
    }, [show, onClose]);

    // Prevent body scroll when modal is open
    useEffect(() => {
        if (show) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
        return () => {
            document.body.style.overflow = '';
        };
    }, [show]);

    // Handle backdrop click (only close if clicking directly on backdrop)
    const handleBackdropClick = (e) => {
        if (e.target === e.currentTarget) {
            onClose();
        }
    };

    if (!show) return null;

    // Inline styles to guarantee z-index works
    const overlayStyle = {
        position: 'fixed',
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        zIndex: 2147483647, // Maximum z-index value
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        padding: '1rem',
        backgroundColor: 'rgba(0, 0, 0, 0.6)',
        backdropFilter: 'blur(4px)',
    };

    const panelStyle = {
        position: 'relative',
        zIndex: 2147483647,
        backgroundColor: 'white',
        borderRadius: '1rem',
        boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.35)',
        width: '100%',
        maxWidth: '80rem',
        maxHeight: '90vh',
        overflow: 'hidden',
    };

    const innerStyle = {
        display: 'flex',
        flexDirection: 'row',
        height: '100%',
        minHeight: '500px',
        maxHeight: '90vh',
    };

    const closeButtonStyle = {
        position: 'absolute',
        right: '1rem',
        top: '1rem',
        background: 'none',
        border: 'none',
        cursor: 'pointer',
        color: '#9ca3af',
        fontSize: '1.25rem',
        zIndex: 10,
        padding: '0.5rem',
    };

    const sidebarStyle = {
        width: '280px',
        backgroundColor: '#f9fafb',
        padding: '2rem',
        borderRight: '1px solid #f3f4f6',
        flexShrink: 0,
        display: 'flex',
        flexDirection: 'column',
    };

    const headerStyle = {
        marginBottom: '2rem',
    };

    const subtitleStyle = {
        color: '#ec4899',
        fontWeight: 700,
        fontSize: '0.75rem',
        textTransform: 'uppercase',
        letterSpacing: '0.1em',
        marginBottom: '0.5rem',
    };

    const titleStyle = {
        fontSize: '1.5rem',
        fontWeight: 700,
        color: '#111827',
        lineHeight: 1.2,
        margin: 0,
    };

    const navStyle = {
        display: 'flex',
        flexDirection: 'column',
        gap: '0.5rem',
    };

    const getTabStyle = (isActive) => ({
        display: 'flex',
        alignItems: 'center',
        gap: '0.75rem',
        padding: '0.75rem 1rem',
        borderRadius: '0.75rem',
        border: 'none',
        cursor: 'pointer',
        fontSize: '0.875rem',
        fontWeight: 600,
        textAlign: 'left',
        width: '100%',
        transition: 'all 0.2s',
        backgroundColor: isActive ? 'white' : 'transparent',
        color: isActive ? '#ec4899' : '#6b7280',
        boxShadow: isActive ? '0 4px 6px -1px rgba(0,0,0,0.1)' : 'none',
    });

    const backContainerStyle = {
        marginTop: 'auto',
        paddingTop: '2rem',
    };

    const backButtonStyle = {
        display: 'flex',
        alignItems: 'center',
        gap: '0.5rem',
        background: 'none',
        border: 'none',
        cursor: 'pointer',
        color: '#9ca3af',
        fontSize: '0.875rem',
        fontWeight: 600,
        padding: 0,
    };

    const contentStyle = {
        flex: 1,
        padding: '2rem 3rem',
        overflowY: 'auto',
        backgroundColor: 'white',
    };

    const modalContent = (
        <div 
            style={overlayStyle}
            onClick={handleBackdropClick}
        >
            <div 
                ref={panelRef}
                style={panelStyle}
                onClick={(e) => e.stopPropagation()}
            >
                <div style={innerStyle}>
                    {/* Close Button */}
                    <button
                        type="button"
                        style={closeButtonStyle}
                        onClick={onClose}
                    >
                        <i className="fas fa-times"></i>
                    </button>

                    {/* Sidebar navigation */}
                    <div style={sidebarStyle}>
                        <div style={headerStyle}>
                            <div style={subtitleStyle}>{subtitle}</div>
                            <h2 style={titleStyle}>{title}</h2>
                        </div>

                        <nav style={navStyle}>
                            {tabs.map((tab) => (
                                <button
                                    key={tab.id}
                                    type="button"
                                    onClick={() => setActiveTab(tab.id)}
                                    style={getTabStyle(activeTab === tab.id)}
                                >
                                    <i className={`fas ${tab.icon}`} style={{ color: activeTab === tab.id ? '#ec4899' : '#9ca3af' }}></i>
                                    {tab.label}
                                </button>
                            ))}
                        </nav>

                        <div style={backContainerStyle}>
                            <button 
                                type="button"
                                onClick={onClose}
                                style={backButtonStyle}
                            >
                                <i className="fas fa-chevron-left" style={{ fontSize: '0.75rem' }}></i>
                                Back to List
                            </button>
                        </div>
                    </div>

                    {/* Content area */}
                    <div style={contentStyle}>
                        {tabs.find(t => t.id === activeTab)?.content}
                    </div>
                </div>
            </div>
        </div>
    );

    // Use portal to render modal at the document body level
    return createPortal(modalContent, document.body);
}
