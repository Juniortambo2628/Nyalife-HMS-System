import React from 'react';
import { Link } from '@inertiajs/react';

/**
 * TableActions — always renders a three-dot menu for row actions (consistent across modules).
 *
 * @param {Array} actions - { icon, label, onClick, href, method, as, color, isDivider, target }
 */
export default function TableActions({ actions = [] }) {
    const validActions = (actions || []).filter(Boolean);
    if (validActions.length === 0) return null;

    return (
        <div className="dropdown d-flex justify-content-end nyl-table-actions">
            <button
                className="btn nyl-table-actions-toggle"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                title="Actions"
            >
                <i className="fas fa-ellipsis-v"></i>
            </button>
            <ul className="dropdown-menu dropdown-menu-end nyl-table-actions-menu">
                {validActions.map((action, idx) => {
                    if (action.isDivider) {
                        return (
                            <li key={idx}>
                                <hr className="dropdown-divider opacity-10 mx-3" />
                            </li>
                        );
                    }

                    const color = action.color || 'primary';
                    const content = (
                        <>
                            <div className={`nyl-table-actions-icon bg-${color}-subtle text-${color}`}>
                                <i className={`fas ${action.icon}`}></i>
                            </div>
                            <span className={`fw-bold ${color === 'danger' ? 'text-danger' : 'text-gray-700'}`}>
                                {action.label}
                            </span>
                        </>
                    );

                    const itemClass = `dropdown-item nyl-table-actions-item ${color === 'danger' ? 'text-danger' : ''}`;

                    return (
                        <li key={idx}>
                            {action.href ? (
                                <Link
                                    href={action.href}
                                    method={action.method || 'get'}
                                    as={action.as || 'a'}
                                    className={itemClass}
                                    target={action.target}
                                    rel={action.target === '_blank' ? 'noopener noreferrer' : undefined}
                                >
                                    {content}
                                </Link>
                            ) : (
                                <button onClick={action.onClick} className={itemClass} type="button">
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
