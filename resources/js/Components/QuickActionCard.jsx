import React from 'react';
import { Link } from '@inertiajs/react';

/**
 * QuickActionCard — Single source of truth for sidebar quick-action links.
 *
 * Used across Doctor, Nurse, and other dashboards for navigation shortcuts.
 *
 * @param {string} label   – e.g. "Find Patient"
 * @param {string} sub     – e.g. "Registry and records"
 * @param {string} icon    – FontAwesome class, e.g. "fa-users"
 * @param {string} color   – Bootstrap contextual name: primary|success|info|warning|danger
 * @param {string} [url]   – Inertia route. If omitted, renders a <button>.
 * @param {function} [onClick] – click handler (used when url is omitted)
 */
export default function QuickActionCard({ label, sub, icon, color = 'primary', url, onClick }) {
    const inner = (
        <>
            <div className={`nyl-action-icon bg-${color}-subtle text-${color}`}>
                <i className={`fas ${icon}`}></i>
            </div>
            <div className="flex-1">
                <div className="nyl-action-label">{label}</div>
                <div className="nyl-action-sub">{sub}</div>
            </div>
            <i className="fas fa-chevron-right nyl-action-chevron"></i>
        </>
    );

    if (url) {
        return (
            <Link href={url} className="nyl-action-card">
                {inner}
            </Link>
        );
    }

    return (
        <button type="button" onClick={onClick} className="nyl-action-card w-100 text-start border-0">
            {inner}
        </button>
    );
}
