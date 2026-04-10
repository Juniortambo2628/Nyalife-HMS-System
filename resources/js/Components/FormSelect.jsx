import React from 'react';

export default function FormSelect({ options = [], className = "form-select", ...props }) {
    return (
        <select className={className} {...props}>
            <option value="">Select Option</option>
            {options.map((opt, idx) => (
                <option key={opt.value || idx} value={opt.value}>
                    {opt.label}
                </option>
            ))}
        </select>
    );
}
