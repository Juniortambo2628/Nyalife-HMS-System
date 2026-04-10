import React from 'react';

export default function FormSection({ title, icon, children, className = "", headerClassName = "bg-primary text-white", bodyClassName = "p-4" }) {
    return (
        <div className={`card border-0 shadow-sm rounded-4 overflow-hidden mb-4 ${className}`}>
            <div className={`card-header ${headerClassName} p-3`}>
                <h6 className="mb-0 fw-bold">
                    {icon && <i className={`${icon} me-2`}></i>}
                    {title}
                </h6>
            </div>
            <div className={`card-body ${bodyClassName}`}>
                {children}
            </div>
        </div>
    );
}
