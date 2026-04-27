import React, { useState, useEffect } from 'react';

/**
 * UnifiedToolbar - The core floating action hub for the Nyalife design system.
 * Refactored to support bulk actions and enhanced selection states.
 */
const UnifiedToolbar = ({ 
    actions, 
    filters, 
    bulkActions, 
    selectionCount = 0,
    children 
}) => {
    const [isMinimized, setIsMinimized] = useState(false);
    const [isVisible, setIsVisible] = useState(false);

    useEffect(() => {
        const timer = setTimeout(() => setIsVisible(true), 100);
        return () => clearTimeout(timer);
    }, []);

    if (!isVisible) return null;

    const hasSelection = selectionCount > 0;

    return (
        <div 
            className={`fixed-bottom d-flex justify-content-center pb-4 transition-all duration-500 unified-toolbar-wrapper ${isMinimized ? 'opacity-50 hover-opacity-100' : 'opacity-95'}`}
            style={{ zIndex: 1050 }}
        >
            <div 
                className={`nyl-toolbar-pill shadow-2xl rounded-pill border border-gray-800 d-flex align-items-center gap-3 px-4 py-2.5 transition-all duration-500 ${isMinimized ? 'translate-y-12 scale-90' : ''} ${hasSelection ? 'bg-gradient-primary-to-secondary' : ''}`}
            >
                {/* Selection Counter - High Contrast */}
                {hasSelection && !isMinimized && (
                    <div className="d-flex align-items-center gap-2 me-2 animate-in slide-in-from-left-2">
                        <div className="bg-white text-primary rounded-circle fw-extrabold d-flex align-items-center justify-content-center shadow-sm" style={{ width: '32px', height: '32px', fontSize: '0.8rem' }}>
                            {selectionCount}
                        </div>
                        <span className="text-white fw-extrabold extra-small text-uppercase tracking-widest d-none d-md-block">Selected</span>
                        <div className="vr opacity-30 bg-white ms-2" style={{ height: '24px' }}></div>
                    </div>
                )}

                {/* Minimize/Maximize Toggle */}
                <button 
                    onClick={() => setIsMinimized(!isMinimized)}
                    className="btn btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center shadow-none border-0 flex-shrink-0 nyl-toolbar-toggle"
                >
                    <i className={`fas ${isMinimized ? 'fa-chevron-up' : 'fa-chevron-down'} ${hasSelection ? 'text-white' : 'text-gray-400'} extra-small`}></i>
                </button>

                {!isMinimized && (
                    <>
                        <div className={`vr opacity-30 mx-1 ${hasSelection ? 'bg-white' : 'bg-white'} nyl-toolbar-divider`}></div>

                        {/* Bulk Actions take precedence when items are selected */}
                        {hasSelection ? (
                            <div className="d-flex align-items-center gap-2 animate-in fade-in zoom-in-95">
                                {bulkActions}
                            </div>
                        ) : (
                            <>
                                {filters && (
                                    <div className="d-flex align-items-center gap-2 toolbar-dark-theme">
                                        {filters}
                                    </div>
                                )}

                                {filters && actions && <div className="vr opacity-30 mx-1 bg-white nyl-toolbar-divider"></div>}

                                {actions && (
                                    <div className="d-flex align-items-center gap-2 toolbar-dark-theme">
                                        {actions}
                                    </div>
                                )}
                            </>
                        )}

                        {children && <div className="toolbar-dark-theme">{children}</div>}
                    </>
                )}
                
                {isMinimized && (
                    <div className={`small fw-extrabold px-2 cursor-pointer ${hasSelection ? 'text-white' : 'text-gray-300'}`} onClick={() => setIsMinimized(false)}>
                        <i className={`fas ${hasSelection ? 'fa-check-double' : 'fa-tools'} me-2 ${hasSelection ? 'text-white' : 'text-primary'}`}></i> 
                        {hasSelection ? `${selectionCount} READY` : 'TOOLBAR'}
                    </div>
                )}
            </div>
            
            <style>{`
                .extra-small { font-size: 0.7rem; }
                .bg-gradient-primary-to-secondary {
                    background: linear-gradient(135deg, #e91e63 0%, #d81b60 100%);
                }
                .animate-in {
                    animation-duration: 0.3s;
                    animation-fill-mode: both;
                }
                @keyframes slideInFromLeft {
                    from { transform: translateX(-10px); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                .slide-in-from-left-2 { animation-name: slideInFromLeft; }
            `}</style>
        </div>
    );
};

export default UnifiedToolbar;
