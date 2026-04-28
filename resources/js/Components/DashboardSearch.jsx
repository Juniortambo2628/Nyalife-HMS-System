import { useState, useEffect } from 'react';

export default function DashboardSearch({ 
    placeholder = "Search anything...", 
    value = "", 
    onChange, 
    onSubmit,
    className = "",
    filters = [],
    onFilterChange
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
            <form onSubmit={handleSubmit} className="card border-0 shadow-sm rounded-2xl bg-white p-1 shadow-hover transition-all">
                <div className="input-group input-group-lg">
                    <span className="input-group-text bg-transparent border-0 ps-4 pe-2 py-3">
                        <i className="fas fa-search text-gray-300 fs-4"></i>
                    </span>
                    <input 
                        type="text" 
                        className="form-control border-0 bg-transparent fs-5 ps-2 py-3 shadow-none no-focus-outline nyl-search-input" 
                        placeholder={placeholder}
                        value={localValue}
                        onChange={handleChange}
                    />
                    <div className="p-2 d-flex align-items-center">
                        <button 
                            type="submit" 
                            className="btn btn-primary rounded-xl px-5 h-100 fw-bold shadow-sm d-flex align-items-center gap-3 hover-scale nyl-search-btn"
                        >
                            <span className="fs-5">Search</span>
                            <i className="fas fa-arrow-right opacity-50"></i>
                        </button>
                    </div>
                </div>
            </form>
            
        </div>
    );
}
