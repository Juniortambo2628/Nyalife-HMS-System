import React from 'react';

/**
 * StatCard — Single source of truth for dashboard KPI cards.
 *
 * Used across Admin, Doctor, Nurse, LabTechnician, and Pharmacist dashboards.
 *
 * @param {string} label   – e.g. "Today's Visits"
 * @param {number|string} value – the numeric value to display
 * @param {string} icon    – FontAwesome class, e.g. "fa-calendar-check"
 * @param {string} color   – Bootstrap contextual name: primary|success|info|warning|danger
 * @param {string} [trend] – optional trend badge text, e.g. "+12%"
 */
export default function StatCard({ label, value, icon, color = 'primary', trend }) {
    return (
        <div className="card nyl-stat-card shadow-sm border-0 h-100">
            <div className="d-flex justify-content-between align-items-center">
                <div>
                    {trend && (
                        <span className={`badge rounded-pill bg-${color}-subtle text-${color} px-3 py-1 fw-bold extra-small border border-${color}-subtle mb-2 d-inline-block`}>
                            {trend}
                        </span>
                    )}
                    <div className="nyl-stat-label text-muted">{label}</div>
                    <h2 className="nyl-stat-value text-gray-900">{value}</h2>
                </div>
                <div className={`nyl-stat-icon bg-${color}-subtle text-${color} shadow-inner`}>
                    <i className={`fas ${icon} fs-4`}></i>
                </div>
            </div>
        </div>
    );
}
