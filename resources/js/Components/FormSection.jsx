import React from 'react';

export default function FormSection({ title, icon, children, actions, className = "", headerClassName = "bg-primary text-white", bodyClassName = "p-4" }) {
    return (
        <div className={`card border-0 shadow-sm rounded-4 mb-4 ${className}`}>
            <div className={`card-header ${headerClassName} rounded-top-4 p-3 d-flex justify-content-between align-items-center`}>
                <h6 className="mb-0 fw-bold">
                    {icon && <i className={`${icon} me-2`}></i>}
                    {title}
                </h6>
                {actions && <div>{actions}</div>}
            </div>
            <div className={`card-body ${bodyClassName}`}>
                {children}
            </div>
        </div>
    );
}
