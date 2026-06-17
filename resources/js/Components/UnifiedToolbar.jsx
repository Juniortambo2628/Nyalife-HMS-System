import React, { useState, useEffect } from 'react';
import { createPortal } from 'react-dom';
import ToolbarViewToggle from '@/Components/ToolbarViewToggle';
import ToolbarFilterMenu from '@/Components/ToolbarFilterMenu';

/** Toolbar button styles — tuned for the pink/dark floating pill (never use Bootstrap `text-light`). */
const TOOLBAR_BUTTON_VARIANTS = {
    primary: 'btn-primary',
    secondary: 'btn-white',
    light: 'btn-white',
    white: 'btn-white',
    gray: 'btn-white nyl-tb-btn-muted',
    success: 'btn-success',
    danger: 'btn-danger',
    warning: 'btn-warning',
    info: 'btn-info',
};

/** Dropdown menu item colors — readable on white menu background. */
const DROPDOWN_ITEM_COLORS = {
    primary: { icon: 'text-primary', text: 'text-gray-800' },
    secondary: { icon: 'text-pink-500', text: 'text-gray-800' },
    light: { icon: 'text-pink-500', text: 'text-gray-800' },
    white: { icon: 'text-pink-500', text: 'text-gray-800' },
    gray: { icon: 'text-gray-400', text: 'text-gray-600' },
    success: { icon: 'text-success', text: 'text-gray-800' },
    danger: { icon: 'text-danger', text: 'text-gray-800' },
    warning: { icon: 'text-warning', text: 'text-gray-800' },
    info: { icon: 'text-info', text: 'text-gray-800' },
};

const DEFAULT_DROPDOWN_COLORS = { icon: 'text-gray-400', text: 'text-gray-700' };

const resolveToolbarButtonClass = (action, fallbackVariant = 'primary') => {
    const key = action?.color || fallbackVariant;
    return TOOLBAR_BUTTON_VARIANTS[key] || TOOLBAR_BUTTON_VARIANTS.primary;
};

const resolveDropdownItemColors = (action) => {
    if (!action?.color) {
        return DEFAULT_DROPDOWN_COLORS;
    }
    if (DROPDOWN_ITEM_COLORS[action.color]) {
        return DROPDOWN_ITEM_COLORS[action.color];
    }
    if (action.color.includes('-')) {
        return { icon: `text-${action.color}`, text: 'text-gray-800' };
    }
    return DEFAULT_DROPDOWN_COLORS;
};

const MAX_INLINE_ACTIONS = 3;

/**
 * UnifiedToolbar - The core floating action hub for the Nyalife design system.
 * Three-section layout: View Options | Filters | Page Actions
 * Selection mode: replaces center with bulk actions dropdown (count embedded in button).
 */
const UnifiedToolbar = ({ 
    actions, 
    filters,
    filterGroups,
    viewOptions,
    viewMode,
    onViewModeChange,
    bulkActions, 
    selectionCount = 0,
    autosaveStatus,
    drafts = [],
    children 
}) => {
    const [isMinimized, setIsMinimized] = useState(false);
    const [isVisible, setIsVisible] = useState(false);
    const [isMounted, setIsMounted] = useState(false);

    useEffect(() => {
        setIsMounted(true);
        const timer = setTimeout(() => setIsVisible(true), 100);
        return () => clearTimeout(timer);
    }, []);

    if (!isMounted || !isVisible) return null;

    const hasSelection = selectionCount > 0;

    const showViewToggle = typeof onViewModeChange === 'function';
    const resolvedFilterGroups = filterGroups?.filter(Boolean) ?? [];
    const showFilterMenu = resolvedFilterGroups.length > 0;
    const showLegacyFilters = !showFilterMenu && !!filters;

    // Render a single action button or link
    const renderSingleAction = (action, fallbackVariant = 'primary') => {
        const variantClass = resolveToolbarButtonClass(action, fallbackVariant);
        const btnClass = `btn ${variantClass} rounded-pill px-3 px-md-4 fw-extrabold nyl-tb-text tracking-widest shadow-sm d-inline-flex align-items-center gap-2 nyl-tb-btn nyl-tb-action-btn`;
        const inner = <>{action.icon && <i className={`fas ${action.icon}`}></i>}<span className="d-none d-md-inline">{action.label}</span></>;
        if (action.href) return <a key={action.label} href={action.href} className={btnClass} onClick={action.onClick}>{inner}</a>;
        return <button key={action.label} className={btnClass} onClick={action.onClick} type="button">{inner}</button>;
    };

    const renderDropdownItem = (action, idx) => {
        const itemCls = 'dropdown-item py-2 px-3 d-flex align-items-center gap-2 cursor-pointer';
        const colors = resolveDropdownItemColors(action);
        const inner = (
            <>
                {action.icon && <i className={`fas ${action.icon} ${colors.icon}`} style={{ width: 16, textAlign: 'center' }}></i>}
                <span className={`fw-bold ${colors.text}`}>{action.label}</span>
            </>
        );

        return (
            <li key={idx}>
                {action.href
                    ? <a href={action.href} className={itemCls} onClick={action.onClick}>{inner}</a>
                    : <button className={itemCls} onClick={action.onClick} type="button">{inner}</button>}
            </li>
        );
    };

    // Render an array of actions as either inline buttons or a dropdown
    const renderActionGroup = (items, { variant = 'primary', dropdownLabel, dropdownIcon = 'fa-layer-group', forceDropdown = false } = {}) => {
        if (!items) return null;
        if (!Array.isArray(items)) return items;
        const valid = items.filter(Boolean);
        if (valid.length === 0) return null;

        if (valid.length === 1 && !forceDropdown) return renderSingleAction(valid[0], variant);

        if (valid.length <= MAX_INLINE_ACTIONS && !forceDropdown) {
            return (
                <div className="d-flex align-items-center gap-2 nyl-tb-inline-actions">
                    {valid.map((action) => renderSingleAction(action, variant))}
                </div>
            );
        }

        const cls = resolveToolbarButtonClass({ color: variant === 'light' ? 'white' : variant }, variant);

        return (
            <div className="dropdown">
                <button 
                    className={`btn ${cls} rounded-pill px-3 px-md-4 fw-extrabold nyl-tb-text tracking-widest shadow-sm d-inline-flex align-items-center gap-2 dropdown-toggle nyl-tb-btn nyl-tb-action-btn`}
                    type="button" data-bs-toggle="dropdown" aria-expanded="false"
                >
                    <i className={`fas ${dropdownIcon}`}></i>
                    <span className="d-none d-md-inline">{dropdownLabel}</span>
                </button>
                <ul className="dropdown-menu dropdown-menu-end shadow-2xl border-0 rounded-2xl py-2 mt-2 animate-in fade-in zoom-in-95" style={{ zIndex: 1100 }}>
                    {valid.map((action, idx) => renderDropdownItem(action, idx))}
                </ul>
            </div>
        );
    };

    return createPortal(
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
                                            <i className="fas fa-check-square text-primary"></i>
                                            <span className="badge rounded-pill bg-pink-500 text-white nyl-tb-count">{selectionCount}</span>
                                            <span>BULK ACTIONS</span>
                                        </button>
                                        <ul className="dropdown-menu dropdown-menu-end shadow-2xl border-0 rounded-2xl py-2 mt-2 animate-in fade-in zoom-in-95" style={{ zIndex: 1100 }}>
                                            {bulkActions.filter(Boolean).map((action, idx) => {
                                                const colors = resolveDropdownItemColors(action);
                                                return (
                                                <li key={idx}>
                                                    <button 
                                                        className="dropdown-item py-2 px-3 d-flex align-items-center gap-2 cursor-pointer"
                                                        onClick={action.onClick} type="button"
                                                    >
                                                        {action.icon && <i className={`fas ${action.icon} ${colors.icon}`} style={{ width: 16, textAlign: 'center' }}></i>}
                                                        <span className={`fw-bold ${colors.text}`}>{action.label}</span>
                                                    </button>
                                                </li>
                                                );
                                            })}
                                        </ul>
                                    </div>
                                )}

                                {/* Page actions stay visible even during selection */}
                                {actions && (
                                    <>
                                        <div className="vr opacity-30 bg-white mx-1 nyl-tb-divider"></div>
                                        {renderActionGroup(actions, { variant: 'primary', dropdownLabel: 'PAGE', dropdownIcon: 'fa-layer-group' })}
                                    </>
                                )}
                            </div>
                        ) : (
                            /* === NORMAL MODE === */
                            <div className="d-flex align-items-center gap-2 flex-grow-1">
                                {/* View options — icon toggle */}
                                {showViewToggle && (
                                    <>
                                        <ToolbarViewToggle value={viewMode || 'list'} onChange={onViewModeChange} />
                                        <div className="vr opacity-30 bg-white mx-1 nyl-tb-divider"></div>
                                    </>
                                )}

                                {/* Legacy viewOptions fallback (deprecated) */}
                                {!showViewToggle && viewOptions && (
                                    <>
                                        <div className="d-flex align-items-center gap-1">
                                            {renderActionGroup(viewOptions, { variant: 'white', dropdownLabel: 'VIEW', dropdownIcon: 'fa-th-large', forceDropdown: true })}
                                        </div>
                                        <div className="vr opacity-30 bg-white mx-1 nyl-tb-divider"></div>
                                    </>
                                )}

                                {/* Unified filter menu */}
                                {showFilterMenu && (
                                    <div className="d-flex align-items-center flex-grow-1 justify-content-center toolbar-filters-slot">
                                        <ToolbarFilterMenu groups={resolvedFilterGroups} dropup />
                                    </div>
                                )}

                                {/* Legacy filters slot */}
                                {showLegacyFilters && (
                                    <div className="d-flex align-items-center gap-2 flex-grow-1 justify-content-center toolbar-dark-theme toolbar-filters">
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
                :root {
                    --nyl-tb-font-size: 0.7rem;
                    --nyl-tb-height: 34px;
                    --nyl-tb-toggle-size: 30px;
                    --nyl-tb-divider-height: 22px;
                    --nyl-tb-bg-default: linear-gradient(135deg, #e91e63 0%, #d81b60 100%);
                    --nyl-tb-bg-selection: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
                }
                .nyl-tb-text { font-size: var(--nyl-tb-font-size); }
                .nyl-tb-btn { height: var(--nyl-tb-height); white-space: nowrap; }
                .nyl-tb-toggle { width: var(--nyl-tb-toggle-size); height: var(--nyl-tb-toggle-size); }
                .nyl-tb-count { font-size: 0.65rem; min-width: 20px; padding: 2px 6px; }
                .nyl-tb-divider { height: var(--nyl-tb-divider-height); }
                .nyl-tb-default-bg { background: var(--nyl-tb-bg-default); }
                .nyl-tb-selection-bg { background: var(--nyl-tb-bg-selection); }
                .nyl-tb-minimized { transform: translateY(10px); opacity: 0.7; }
                .hover-bg-white-10:hover { background: rgba(255, 255, 255, 0.1); }
                .hover-scale { transition: transform 0.2s; }
                .hover-scale:hover { transform: scale(1.05); }
                .btn-white { background: white; color: #e91e63; border: none; }
                .btn-white:hover { background: #f8f9fa; color: #d81b60; }
                .nyl-toolbar-pill .nyl-tb-action-btn.btn-white {
                    background: #fff !important;
                    color: #e91e63 !important;
                    border: none !important;
                }
                .nyl-toolbar-pill .nyl-tb-action-btn.btn-white:hover {
                    background: #f8f9fa !important;
                    color: #d81b60 !important;
                }
                .nyl-toolbar-pill .nyl-tb-action-btn.btn-white.nyl-tb-btn-muted {
                    color: #374151 !important;
                }
                .nyl-toolbar-pill .nyl-tb-action-btn.btn-primary {
                    color: #fff !important;
                }
                .nyl-toolbar-pill .nyl-tb-action-btn.btn-primary i,
                .nyl-toolbar-pill .nyl-tb-action-btn.btn-primary span {
                    color: inherit !important;
                }
                .nyl-tb-inline-actions .nyl-tb-action-btn span,
                .nyl-tb-inline-actions .nyl-tb-action-btn i {
                    color: inherit !important;
                }
                .animate-in { animation-duration: 0.3s; animation-fill-mode: both; }
                @keyframes slideInFromBottom {
                    from { transform: translateY(10px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
                .slide-in-from-bottom-2 { animation-name: slideInFromBottom; }
                .toolbar-filters .dashboard-select-container {
                    width: 130px !important;
                    flex-shrink: 0 !important;
                    position: relative !important;
                    height: 34px !important;
                    display: inline-flex !important;
                    align-items: center !important;
                }
                .toolbar-dark-theme .nyl-select-trigger {
                    height: 34px !important;
                    display: inline-flex !important;
                    align-items: center !important;
                }
                .toolbar-filters .nyl-select-trigger {
                    min-width: 0 !important;
                    width: 100% !important;
                    background: rgba(255, 255, 255, 0.15) !important;
                    color: white !important;
                    border: 1px solid rgba(255, 255, 255, 0.2) !important;
                    transition: all 0.3s ease !important;
                }
                .toolbar-filters .nyl-select-trigger i,
                .toolbar-filters .nyl-select-trigger span {
                    color: white !important;
                    transition: all 0.3s ease !important;
                }
                .toolbar-filters .dashboard-select-container:not(.is-open) .nyl-select-trigger:hover {
                    background: rgba(255, 255, 255, 0.25) !important;
                    border-color: rgba(255, 255, 255, 0.4) !important;
                }
                .toolbar-filters .dashboard-select-container.is-open .nyl-select-trigger {
                    background: white !important;
                    color: #e91e63 !important;
                    border-color: white !important;
                }
                .toolbar-filters .dashboard-select-container.is-open .nyl-select-trigger i,
                .toolbar-filters .dashboard-select-container.is-open .nyl-select-trigger span {
                    color: #e91e63 !important;
                }
                .toolbar-filters .dashboard-select-container.has-value:not(.is-open) .nyl-select-trigger {
                    background: rgba(255, 255, 255, 0.3) !important;
                    border-color: rgba(255, 255, 255, 0.5) !important;
                    box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.2) !important;
                }
                .toolbar-filters .nyl-select-dropdown {
                    width: 280px !important;
                    min-width: 280px !important;
                    border-radius: 12px !important;
                    border: 1px solid rgba(0, 0, 0, 0.05) !important;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
                }
                .toolbar-filters .dashboard-select-container:last-child .nyl-select-dropdown {
                    left: auto !important;
                    right: 0 !important;
                }
                @media (max-width: 768px) {
                    .nyl-toolbar-pill { padding: 6px 8px !important; gap: 4px !important; }
                    .nyl-tb-btn { padding-left: 10px !important; padding-right: 10px !important; height: 30px; font-size: 0.6rem; }
                    .nyl-tb-toggle { width: 26px; height: 26px; }
                    .nyl-tb-divider { height: 18px; }
                    .toolbar-filters .dashboard-select-container { width: 100px !important; }
                }
            `}</style>
        </div>,
        document.body
    );
};

export default UnifiedToolbar;
