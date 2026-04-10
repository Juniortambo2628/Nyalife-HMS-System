import React from 'react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';

export default function FormField({ 
    label, 
    error, 
    children, 
    required = false, 
    className = "col-md-6",
    labelClassName = "small text-muted text-uppercase fw-bold"
}) {
    return (
        <div className={className}>
            {label && (
                <InputLabel value={label} className={labelClassName}>
                    {required && <span className="text-danger ms-1">*</span>}
                </InputLabel>
            )}
            {children}
            <InputError message={error} className="mt-2" />
        </div>
    );
}
