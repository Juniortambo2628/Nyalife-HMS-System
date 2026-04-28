import React, { useState, useEffect } from 'react';

/**
 * UnifiedToolbar - The core floating action hub for the Nyalife design system.
 * Three-section layout: View Options | Filters | Page Actions
 * Selection mode: replaces center with bulk actions dropdown (count embedded in button).
 */
const UnifiedToolbar = ({ 
    actions, 
    filters, 
    viewOptions,
    bulkActions, 
    selectionCount = 0,
    autosaveStatus,
    drafts = [],
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

    // Render a single action button or link
    const renderSingleAction = (action, variant = 'primary') => {
        const cls = {
            primary: 'btn-primary',
            white: 'btn-white',
            light: 'btn-light text-pink-500',
        }[variant] || 'btn-primary';
        const btnClass = `btn ${cls} rounded-pill px-3 px-md-4 fw-extrabold nyl-tb-text tracking-widest shadow-sm d-inline-flex align-items-center gap-2 nyl-tb-btn`;
        const inner = <>{action.icon && <i className={`fas ${action.icon}`}></i>}<span className="d-none d-md-inline">{action.label}</span></>;
        if (action.href) return <a key={action.label} href={action.href} className={btnClass} onClick={action.onClick}>{inner}</a>;
        return <button key={action.label} className={btnClass} onClick={action.onClick} type="button">{inner}</button>;
    };

    // Render an array of actions as either inline buttons or a dropdown
    const renderActionGroup = (items, { variant = 'primary', dropdownLabel, dropdownIcon = 'fa-layer-group', forceDropdown = false } = {}) => {
        if (!items) return null;
        if (!Array.isArray(items)) return items;
        const valid = items.filter(Boolean);
        if (valid.length === 0) return null;

        if (valid.length === 1 && !forceDropdown) return renderSingleAction(valid[0], variant);

        const cls = {
            primary: 'btn-primary',
            white: 'btn-white',
            light: 'btn-light text-pink-500',
        }[variant] || 'btn-primary';

        return (
            <div className="dropdown">
                <button 
                    className={`btn ${cls} rounded-pill px-3 px-md-4 fw-extrabold nyl-tb-text tracking-widest shadow-sm d-inline-flex align-items-center gap-2 dropdown-toggle nyl-tb-btn`}
                    type="button" data-bs-toggle="dropdown" aria-expanded="false"
                >
                    <i className={`fas ${dropdownIcon}`}></i>
                    <span className="d-none d-md-inline">{dropdownLabel}</span>
                </button>
                <ul className="dropdown-menu dropdown-menu-end shadow-2xl border-0 rounded-2xl py-2 mt-2 animate-in fade-in zoom-in-95" style={{ zIndex: 1100 }}>
                    {valid.map((action, idx) => {
                        const itemCls = "dropdown-item py-2 px-3 d-flex align-items-center gap-2 cursor-pointer";
                        const inner = (
                            <>
                                {action.icon && <i className={`fas ${action.icon} ${action.color ? 'text-' + action.color : 'text-gray-400'}`} style={{ width: 16, textAlign: 'center' }}></i>}
                                <span className={`fw-bold ${action.color ? 'text-' + action.color : 'text-gray-700'}`}>{action.label}</span>
                            </>
                        );
                        return (
                            <li key={idx}>
                                {action.href
                                    ? <a href={action.href} className={itemCls} onClick={action.onClick}>{inner}</a>
                                    : <button className={itemCls} onClick={action.onClick} type="button">{inner}</button>}
                            </li>
                        );
                    })}
                </ul>
            </div>
        );
    };

    return (
        <div className={`fixed-bottom d-flex flex-column align-items-center pb-3 pb-md-4 transition-all duration-500 unified-toolbar-wrapper ${isMinimized ? 'opacity-50 hover-opacity-100' : 'opacity-100'}`}
            style={{ zIndex: 1050, pointerEvents: 'none' }}
        >
            {/* Autosave & Drafts floating badges */}
            {!isMinimized && (autosaveStatus || (drafts && drafts.length > 0)) && (
                <div className="d-flex align-items-center gap-2 mb-2 animate-in fade-in slide-in-from-bottom-2" style={{ pointerEvents: 'auto' }}>
                    {autosaveStatus && (
                        <div className={`badge px-3 py-2 rounded-pill shadow-lg d-inline-flex align-items-center gap-2 fw-extrabold nyl-tb-text text-uppercase tracking-widest ${autosaveStatus.includes('saving') ? 'bg-info text-white' : 'bg-success text-white'}`}>
                            <i className={`fas ${autosaveStatus.includes('saving') ? 'fa-sync fa-spin' : 'fa-check-circle'}`}></i>
                            {autosaveStatus}
                        </div>
                    )}
                    {drafts && drafts.length > 0 && (
                        <div className="badge bg-white text-pink-500 px-3 py-2 rounded-pill shadow-lg border border-pink-100 d-inline-flex align-items-center gap-2 fw-extrabold nyl-tb-text text-uppercase tracking-widest cursor-pointer hover-scale"
                            onClick={() => window.dispatchEvent(new CustomEvent('show-draft-switcher'))}
                        >
                            <i className="fas fa-history"></i>
                            {drafts.length} Drafts
                        </div>
                    )}
                </div>
            )}

            {/* Main toolbar pill */}
            <div 
                className={`nyl-toolbar-pill shadow-2xl rounded-pill border border-white-10 d-flex align-items-center gap-2 px-2 px-md-3 py-2 transition-all duration-500 ${hasSelection ? 'nyl-tb-selection-bg' : 'nyl-tb-default-bg'} ${isMinimized ? 'nyl-tb-minimized' : ''}`}
                style={{ pointerEvents: 'auto', maxWidth: '95vw' }}
            >
                {/* Visibility toggle (always visible) */}
                <button 
                    onClick={() => setIsMinimized(!isMinimized)}
                    className="btn btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center shadow-none border-0 flex-shrink-0 nyl-tb-toggle"
                    title={isMinimized ? "Show Toolbar" : "Hide Toolbar"}
                >
                    <i className={`fas ${isMinimized ? 'fa-chevron-up' : 'fa-chevron-down'} text-white nyl-tb-text`}></i>
                </button>

                {!isMinimized && (
                    <>
                        {/* === SELECTION MODE === */}
                        {hasSelection ? (
                            <div className="d-flex align-items-center gap-2 flex-grow-1 justify-content-center animate-in fade-in zoom-in-95">
                                {/* Clear selection */}
                                <button 
                                    onClick={() => {
                                        // Dispatch a custom event so pages can clear selection
                                        window.dispatchEvent(new CustomEvent('toolbar-clear-selection'));
                                    }}
                                    className="btn btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center shadow-none border-0 flex-shrink-0 nyl-tb-toggle"
                                    title="Clear Selection"
                                >
                                    <i className="fas fa-times text-white nyl-tb-text"></i>
                                </button>

                                {/* Bulk actions dropdown with count embedded */}
                                {bulkActions && Array.isArray(bulkActions) && bulkActions.filter(Boolean).length > 0 && (
                                    <div className="dropdown">
                                        <button 
                                            className="btn btn-white rounded-pill px-3 px-md-4 fw-extrabold nyl-tb-text tracking-widest shadow-sm d-inline-flex align-items-center gap-2 dropdown-toggle nyl-tb-btn"
                                            type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                        >
                                            <span className="badge rounded-pill bg-pink-500 text-white nyl-tb-count">{selectionCount}</span>
                                            <span>ACTIONS</span>
                                        </button>
                                        <ul className="dropdown-menu dropdown-menu-end shadow-2xl border-0 rounded-2xl py-2 mt-2 animate-in fade-in zoom-in-95" style={{ zIndex: 1100 }}>
                                            {bulkActions.filter(Boolean).map((action, idx) => (
                                                <li key={idx}>
                                                    <button 
                                                        className="dropdown-item py-2 px-3 d-flex align-items-center gap-2 cursor-pointer"
                                                        onClick={action.onClick} type="button"
                                                    >
                                                        {action.icon && <i className={`fas ${action.icon} ${action.color ? 'text-' + action.color : 'text-gray-400'}`} style={{ width: 16, textAlign: 'center' }}></i>}
                                                        <span className={`fw-bold ${action.color ? 'text-' + action.color : 'text-gray-700'}`}>{action.label}</span>
                                                    </button>
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                )}

                                {/* Page actions stay visible even during selection */}
                                {actions && (
                                    <>
                                        <div className="vr opacity-30 bg-white mx-1 nyl-tb-divider"></div>
                                        {renderActionGroup(actions, { variant: 'primary', dropdownLabel: 'ACTIONS', dropdownIcon: 'fa-layer-group' })}
                                    </>
                                )}
                            </div>
                        ) : (
                            /* === NORMAL MODE === */
                            <div className="d-flex align-items-center gap-2 flex-grow-1">
                                {/* View options */}
                                {viewOptions && (
                                    <>
                                        <div className="d-flex align-items-center gap-1">
                                            {renderActionGroup(viewOptions, { variant: 'light', dropdownLabel: 'VIEW', dropdownIcon: 'fa-th-large' })}
                                        </div>
                                        <div className="vr opacity-30 bg-white mx-1 nyl-tb-divider"></div>
                                    </>
                                )}

                                {/* Filters (center) */}
                                {filters && (
                                    <div className="d-flex align-items-center gap-2 flex-grow-1 justify-content-center toolbar-dark-theme toolbar-filters overflow-hidden">
                                        {filters}
                                    </div>
                                )}

                                {/* Page actions (right) */}
                                {actions && (
                                    <>
                                        <div className="vr opacity-30 bg-white mx-1 nyl-tb-divider"></div>
                                        <div className="d-flex align-items-center gap-2 nyl-toolbar-actions flex-shrink-0">
                                            {renderActionGroup(actions, { variant: 'primary', dropdownLabel: 'ACTIONS', dropdownIcon: 'fa-layer-group' })}
                                        </div>
                                    </>
                                )}

                                {children && <div className="toolbar-dark-theme">{children}</div>}
                            </div>
                        )}
                    </>
                )}

                {/* Minimized badge */}
                {isMinimized && hasSelection && (
                    <span className="badge rounded-pill bg-white text-pink-500 fw-extrabold nyl-tb-text px-2 ms-1">{selectionCount}</span>
                )}
            </div>
            
            <style>{`
                .nyl-tb-text { font-size: 0.7rem; }
                .nyl-tb-btn { height: 34px; white-space: nowrap; }
                .nyl-tb-toggle { width: 30px; height: 30px; }
                .nyl-tb-count { font-size: 0.65rem; min-width: 20px; padding: 2px 6px; }
                .nyl-tb-divider { height: 22px; }
                .nyl-tb-default-bg { background: linear-gradient(135deg, #e91e63 0%, #d81b60 100%); }
                .nyl-tb-selection-bg { background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%); }
                .nyl-tb-minimized { transform: translateY(10px); opacity: 0.7; }
                .hover-bg-white-10:hover { background: rgba(255, 255, 255, 0.1); }
                .hover-scale { transition: transform 0.2s; }
                .hover-scale:hover { transform: scale(1.05); }
                .btn-white { background: white; color: #e91e63; border: none; }
                .btn-white:hover { background: #f8f9fa; color: #d81b60; }
                .animate-in { animation-duration: 0.3s; animation-fill-mode: both; }
                @keyframes slideInFromBottom {
                    from { transform: translateY(10px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
                .slide-in-from-bottom-2 { animation-name: slideInFromBottom; }
                .toolbar-filters .nyl-select-trigger {
                    min-width: 120px;
                    background: rgba(255, 255, 255, 0.15) !important;
                    color: white !important;
                    border: 1px solid rgba(255, 255, 255, 0.2) !important;
                }
                .toolbar-filters .nyl-select-trigger i,
                .toolbar-filters .nyl-select-trigger span { color: white !important; }
                .toolbar-filters .dashboard-select-container,
                .toolbar-dark-theme .nyl-select-trigger { 
                    height: 34px !important; display: inline-flex !important; align-items: center !important; 
                }
                @media (max-width: 768px) {
                    .nyl-toolbar-pill { padding: 6px 8px !important; gap: 4px !important; }
                    .nyl-tb-btn { padding-left: 10px !important; padding-right: 10px !important; height: 30px; font-size: 0.6rem; }
                    .nyl-tb-toggle { width: 26px; height: 26px; }
                    .nyl-tb-divider { height: 18px; }
                    .toolbar-filters .nyl-select-trigger { min-width: 100px; }
                }
            `}</style>
        </div>
    );
};

export default UnifiedToolbar;
