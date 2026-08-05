import { useState, useEffect } from 'react';

export default function DashboardSearch({
    placeholder = 'Search anything...',
    value = '',
    onChange,
    onSubmit,
    className = '',
    filters = [],
    onFilterChange,
}) {
    const [localValue, setLocalValue] = useState(value);
    const [activeFilter, setActiveFilter] = useState(null);

    useEffect(() => {
        setLocalValue(value);
    }, [value]);

    const handleChange = (e) => {
        const newValue = e.target.value;
        setLocalValue(newValue);
        if (onChange) onChange(newValue);
    };

    const handleFilterClick = (filter) => {
        const val = filter.value === activeFilter ? null : filter.value;
        setActiveFilter(val);
        if (onFilterChange) onFilterChange(val);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (onSubmit) onSubmit(localValue);
    };

    return (
        <div className={`dashboard-search-container mb-4 ${className}`}>
            <form
                onSubmit={handleSubmit}
                className="card border-0 shadow-sm rounded-2xl bg-white p-0.5 shadow-hover transition-all"
            >
                <div className="input-group">
                    <span className="input-group-text bg-transparent border-0 ps-3 pe-2 py-2">
                        <i className="fas fa-search text-gray-300 fs-5"></i>
                    </span>
                    <input
                        type="text"
                        className="form-control border-0 bg-transparent fs-6 ps-2 py-2 shadow-none no-focus-outline nyl-search-input"
                        placeholder={placeholder}
                        value={localValue}
                        onChange={handleChange}
                    />
                    <div className="p-1.5 d-flex align-items-center">
                        <button
                            type="submit"
                            className="btn btn-primary rounded-xl px-4 h-100 fw-bold shadow-sm d-flex align-items-center gap-2 hover-scale nyl-search-btn"
                        >
                            <span className="fs-6">Search</span>
                            <i className="fas fa-arrow-right opacity-50 text-xs"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    );
}
