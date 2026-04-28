import React from 'react';
import { Link } from '@inertiajs/react';

/**
 * TableActions - Centralized component for rendering table action buttons.
 * Follows DRY strategy:
 * - If <= 2 actions: renders as pink circular outline buttons.
 * - If > 2 actions: renders as a 3-dot dropdown menu.
 * 
 * @param {Array} actions - Array of action objects: { icon, label, onClick, href, method, as, color, isDivider }
 */
export default function TableActions({ actions = [] }) {
    if (!actions || actions.length === 0) return null;

    // Filter out null/undefined actions
    const validActions = actions.filter(Boolean);

    if (validActions.length <= 2) {
        return (
            <div className="d-flex justify-content-end gap-2">
                {validActions.map((action, idx) => {
                    if (action.isDivider) return null;
                    
                    const btnClass = "btn btn-outline-primary rounded-circle shadow-none p-2 avatar-xs d-flex align-items-center justify-content-center border-2 hover-bg-primary hover-text-white transition-all";
                    
                    if (action.href) {
                        return (
                            <Link 
                                key={idx}
                                href={action.href}
                                method={action.method || 'get'}
                                as={action.as || 'a'}
                                className={btnClass}
                                title={action.label}
                            >
                                <i className={`fas ${action.icon} text-xs`}></i>
                            </Link>
                        );
                    }
                    
                    return (
                        <button 
                            key={idx}
                            onClick={action.onClick}
                            className={btnClass}
                            title={action.label}
                            type="button"
                        >
                            <i className={`fas ${action.icon} text-xs`}></i>
                        </button>
                    );
                })}
            </div>
        );
    }

    // Render dropdown for > 2 actions
    return (
        <div className="dropdown d-flex justify-content-end">
            <button 
                className="btn btn-light rounded-circle p-2 border border-light-subtle shadow-sm avatar-sm d-flex align-items-center justify-content-center bg-white" 
                type="button" 
                data-bs-toggle="dropdown" 
                aria-expanded="false"
            >
                <i className="fas fa-ellipsis-v text-gray-400"></i>
            </button>
            <ul className="dropdown-menu dropdown-menu-end shadow-2xl border-0 rounded-2xl py-2 mt-2 animate-in fade-in zoom-in-95 duration-200" style={{ zIndex: 1050 }}>
                {validActions.map((action, idx) => {
                    if (action.isDivider) {
                        return <li key={idx}><hr className="dropdown-divider opacity-10 mx-3" /></li>;
                    }

                    const color = action.color || 'primary';
                    const content = (
                        <>
                            <div className={`avatar-sm rounded-lg bg-${color}-subtle d-flex align-items-center justify-content-center text-${color}`}>
                                <i className={`fas ${action.icon}`}></i>
                            </div>
                            <span className={`fw-bold ${action.color === 'danger' ? 'text-danger' : 'text-gray-700'}`}>{action.label}</span>
                        </>
                    );

                    const itemClass = `dropdown-item py-2 px-3 d-flex align-items-center gap-3 hover-bg-gray-50 transition-colors ${action.color === 'danger' ? 'text-danger' : ''}`;

                    return (
                        <li key={idx}>
                            {action.href ? (
                                <Link 
                                    href={action.href}
                                    method={action.method || 'get'}
                                    as={action.as || 'a'}
                                    className={itemClass}
                                >
                                    {content}
                                </Link>
                            ) : (
                                <button 
                                    onClick={action.onClick}
                                    className={itemClass}
                                    type="button"
                                >
                                    {content}
                                </button>
                            )}
                        </li>
                    );
                })}
            </ul>
        </div>
    );
}
