import React from 'react';

const ICON_COLOR_CLASSES = {
    primary: 'bg-primary-subtle text-primary',
    success: 'bg-success-subtle text-success',
    info: 'bg-info-subtle text-info',
    warning: 'bg-warning-subtle text-warning',
    danger: 'bg-danger-subtle text-danger',
    secondary: 'bg-secondary-subtle text-secondary',
    purple: 'bg-purple-subtle text-purple',
    pink: 'bg-pink-50 text-pink-500',
    teal: 'bg-info-subtle text-info',
};

/**
 * StatCard — single source of truth for KPI / summary cards across dashboards and reports.
 *
 * @param {string} label
 * @param {number|string} value
 * @param {string} [icon] – FontAwesome class, e.g. "fa-calendar-check"
 * @param {string} [color] – primary|success|info|warning|danger|secondary|purple|pink|teal
 * @param {string} [iconClassName] – override icon container classes
 * @param {string} [trend] – optional trend badge, e.g. "+12%"
 * @param {string} [sub] – optional subtitle below the value
 */
export default function StatCard({
    label,
    value,
    icon = 'fa-chart-bar',
    color = 'primary',
    iconClassName,
    trend,
    sub,
}) {
    const iconClasses = iconClassName || ICON_COLOR_CLASSES[color] || ICON_COLOR_CLASSES.primary;

    return (
        <div className="card nyl-stat-card shadow-sm border-0 h-100">
            <div className="d-flex justify-content-between align-items-center">
                <div className="min-w-0">
                    {trend && (
                        <span className={`badge rounded-pill bg-${color}-subtle text-${color} px-3 py-1 fw-bold extra-small border border-${color}-subtle mb-2 d-inline-block`}>
                            {trend}
                        </span>
                    )}
                    <div className="nyl-stat-label text-muted">{label}</div>
                    <h2 className="nyl-stat-value text-gray-900">{value}</h2>
                    {sub && <div className="nyl-stat-sub text-muted">{sub}</div>}
                </div>
                {icon && (
                    <div className={`nyl-stat-icon ${iconClasses} shadow-inner`}>
                        <i className={`fas ${icon} fs-4`}></i>
                    </div>
                )}
            </div>
        </div>
    );
}
