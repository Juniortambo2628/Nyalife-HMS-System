import { useState, useEffect } from 'react';

export default function DashboardSearch({ 
    placeholder = "Search anything...", 
    value = "", 
    onChange, 
    onSubmit,
    className = ""
}) {
    const [localValue, setLocalValue] = useState(value);

    // Sync local state when external value changes
    useEffect(() => {
        setLocalValue(value);
    }, [value]);

    const handleChange = (e) => {
        const newValue = e.target.value;
        setLocalValue(newValue);
        if (onChange) onChange(newValue);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (onSubmit) onSubmit(localValue);
    };

    return (
        <div className={`dashboard-search-container mb-4 ${className}`}>
            <form onSubmit={handleSubmit} className="card border-0 shadow-sm rounded-2xl bg-white p-1 shadow-hover transition-all duration-300" style={{ overflow: 'visible' }}>
                <div className="input-group input-group-lg">
                    <span className="input-group-text bg-transparent border-0 ps-4 pe-2 py-3">
                        <i className="fas fa-search text-gray-300 fs-4"></i>
                    </span>
                    <input 
                        type="text" 
                        className="form-control border-0 bg-transparent fs-5 ps-2 py-3 shadow-none no-focus-outline" 
                        placeholder={placeholder}
                        value={localValue}
                        onChange={handleChange}
                        style={{ height: '60px' }}
                    />
                    <div className="p-2 d-flex align-items-center">
                        <button 
                            type="submit" 
                            className="btn btn-primary rounded-xl px-5 h-100 font-bold shadow-sm d-flex align-items-center gap-3 transition-all hover-scale"
                            style={{ minWidth: '160px' }}
                        >
                            <span className="fs-5">Search</span>
                            <i className="fas fa-arrow-right opacity-50"></i>
                        </button>
                    </div>
                </div>
            </form>
            
            {/* Quick Filters / Shortcuts suggestions could go here */}
            <div className="d-flex flex-wrap gap-2 mt-2 px-2 overflow-auto no-scrollbar">
                <small className="text-gray-400 font-bold uppercase tracking-wider align-middle pt-1 me-2" style={{fontSize: '0.65rem'}}>Quick Filters:</small>
                {/* These could be passed as props later, hardcoding for now as examples or leaving empty context */}
                <span className="badge rounded-pill nyl-filter-badge border px-3 py-2 cursor-pointer transition-all font-semibold">Recently Added</span>
                <span className="badge rounded-pill nyl-filter-badge border px-3 py-2 cursor-pointer transition-all font-semibold">Active Only</span>
                <span className="badge rounded-pill nyl-filter-badge border px-3 py-2 cursor-pointer transition-all font-semibold">Archived</span>
            </div>
        </div>
    );
}

            <style>{`
                .nyl-filter-badge {
                    background-color: #fff;
                    color: #e91e63;
                    border-color: #e91e6333 !important;
                }
                .nyl-filter-badge:hover {
                    background-color: #e91e63 !important;
                    color: #fff !important;
                }
                .no-focus-outline:focus { outline: none !important; box-shadow: none !important; }
                .shadow-hover:hover { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important; }
                .hover-scale { transition: transform 0.2s; }
                .hover-scale:hover { transform: scale(1.02); }
                .hover-scale:active { transform: scale(0.98); }
            `}</style>
