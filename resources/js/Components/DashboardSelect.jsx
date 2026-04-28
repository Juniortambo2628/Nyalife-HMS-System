import React, { useState, useEffect, useRef, useMemo } from 'react';
import axios from 'axios';

/**
 * DashboardSelect - A unified, robust searchable select component.
 * Refactored for premium clinical aesthetic and high contrast interaction.
 */
export default function DashboardSelect({ 
    options = [], 
    asyncUrl = null,
    value, 
    onChange, 
    placeholder = 'Search or select...', 
    searchPlaceholder = 'Type to search...',
    labelField = 'label',
    valueField = 'value',
    onAddNew = null,
    addNewLabel = 'Add New',
    className = "",
    initialLabel = null,
    theme = 'light',
    dropup = false,
    style = {}
}) {
    const [isOpen, setIsOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [asyncOptions, setAsyncOptions] = useState([]);
    const [loading, setLoading] = useState(false);
    const [selectedDisplay, setSelectedDisplay] = useState(initialLabel || '');
    
    const dropdownRef = useRef(null);
    const debounceTimer = useRef(null);

    const isDark = theme === 'dark';

    const filteredOptions = useMemo(() => {
        if (asyncUrl) return asyncOptions;
        if (!searchTerm) return options;
        return options.filter(opt => 
            String(opt[labelField]).toLowerCase().includes(searchTerm.toLowerCase())
        );
    }, [options, searchTerm, asyncOptions, asyncUrl]);

    useEffect(() => {
        if (value) {
            const found = options.find(opt => opt[valueField] == value);
            if (found) {
                setSelectedDisplay(found[labelField]);
            } else if (initialLabel) {
                setSelectedDisplay(initialLabel);
            }
        } else {
            setSelectedDisplay(initialLabel || '');
        }
    }, [value, options, initialLabel, labelField, valueField]);

    useEffect(() => {
        if (!asyncUrl || searchTerm.length < 2) {
            setAsyncOptions([]);
            return;
        }

        setLoading(true);
        if (debounceTimer.current) clearTimeout(debounceTimer.current);

        debounceTimer.current = setTimeout(async () => {
            try {
                const response = await axios.get(`${asyncUrl}${asyncUrl.includes('?') ? '&' : '?'}q=${searchTerm}`);
                setAsyncOptions(response.data);
            } catch (err) {
                console.error("DashboardSelect Search Error:", err);
            } finally {
                setLoading(false);
            }
        }, 300);

        return () => clearTimeout(debounceTimer.current);
    }, [searchTerm, asyncUrl]);

    useEffect(() => {
        const handleClickOutside = (e) => {
            if (dropdownRef.current && !dropdownRef.current.contains(e.target)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    // Determine font color for trigger based on state
    // "change the font color of the Doctor and patient input boxes to black when state=active"
    const triggerTextColor = (isOpen || value) ? 'text-pink-500' : 'text-pink-500';
    const triggerBgColor = (isOpen || value) ? 'bg-white' : (isDark ? 'bg-white bg-opacity-20 hover-bg-opacity-30' : 'bg-white');

    return (
        <div className={`dashboard-select-container position-relative ${className}`} ref={dropdownRef} style={style}>
            {/* Trigger Area */}
            <div 
                className={`form-control border-0 rounded-pill py-2 px-4 d-flex justify-content-between align-items-center cursor-pointer transition-all shadow-sm nyl-select-trigger ${isOpen ? 'ring-2 ring-primary ring-opacity-20' : ''} ${triggerBgColor} ${triggerTextColor} ${!isDark && !isOpen && !value ? 'border' : ''}`}
                onClick={() => setIsOpen(!isOpen)}
            >
                <div className="d-flex align-items-center gap-2 overflow-hidden flex-grow-1">
                    {loading ? (
                        <div className="spinner-border spinner-border-sm text-pink-500 opacity-50 nyl-select-spinner" role="status"></div>
                    ) : (
                        <i className={`fas fa-filter text-pink-500 extra-small`}></i>
                    )}
                    <span className="fw-extrabold extra-small tracking-widest text-uppercase text-truncate">
                        {selectedDisplay || placeholder}
                    </span>
                </div>
                <div className="d-flex align-items-center gap-2">
                    {value && (
                        <button 
                            type="button"
                            className={`btn btn-link p-0 border-0 shadow-none hover-opacity-100 nyl-select-clear ${isOpen || value ? 'text-gray-400' : (isDark ? 'text-gray-400' : 'text-muted')}`}
                            onClick={(e) => {
                                e.stopPropagation();
                                onChange(null, null);
                                setSelectedDisplay('');
                                setSearchTerm('');
                            }}
                        >
                            <i className="fas fa-times-circle"></i>
                        </button>
                    )}
                    <i className={`fas fa-chevron-${isOpen ? (dropup ? 'up' : 'down') : (dropup ? 'down' : 'up')} text-pink-500 fs-xs transition-all duration-500 ${isOpen ? 'opacity-100' : 'opacity-40'}`}></i>
                </div>
            </div>

            {/* Dropdown Menu - Submenu Border Radius Removed as per request */}
            {isOpen && (
                <div 
                    className={`position-absolute start-0 w-100 shadow-2xl border-0 overflow-hidden nyl-select-dropdown bg-white border border-gray-100 ${dropup ? 'bottom-100 mb-3' : 'top-100 mt-3'}`}
                    style={{ borderRadius: '0', zIndex: 1100 }}
                >
                    {/* Search Input Area - Fixed Light Theme */}
                    <div className="p-3 border-bottom sticky-top bg-white">
                        <div className="input-group input-group-sm rounded-pill px-3 bg-gray-50 border">
                            <span className="input-group-text bg-transparent border-0 px-0 me-2">
                                <i className="fas fa-search text-gray-400"></i>
                            </span>
                            <input 
                                type="text" 
                                className="form-control bg-transparent border-0 shadow-none py-2 fw-extrabold extra-small text-dark" 
                                placeholder={searchPlaceholder}
                                value={searchTerm}
                                onChange={e => setSearchTerm(e.target.value)}
                                autoFocus
                            />
                        </div>
                    </div>

                    {/* Options List - Fixed Light Theme with Dark Text */}
                    <div className="overflow-auto custom-scrollbar" style={{ maxHeight: '300px' }}>
                        {loading && asyncUrl && (
                            <div className="p-4 text-center">
                                <div className="spinner-border text-primary spinner-border-sm opacity-25" role="status"></div>
                                <div className="mt-2 extra-small text-muted fw-extrabold text-uppercase tracking-widest">Scanning Catalog...</div>
                            </div>
                        )}

                        {!loading && filteredOptions.length > 0 ? (
                            filteredOptions.map((opt, i) => (
                                <div 
                                    key={opt[valueField] || i}
                                    className={`px-4 py-3 cursor-pointer transition-all d-flex justify-content-between align-items-center hover-bg-gray-50 ${value == opt[valueField] ? 'bg-primary-subtle text-primary fw-extrabold' : 'text-dark'}`}
                                    onClick={() => {
                                        onChange(opt[valueField], opt);
                                        setSelectedDisplay(opt[labelField]);
                                        setIsOpen(false);
                                        setSearchTerm('');
                                    }}
                                >
                                    <div className="d-flex flex-column">
                                        <span className="fw-bold extra-small text-uppercase tracking-tight">{opt[labelField]}</span>
                                        {opt.sublabel && <small className="text-muted extra-small opacity-75">{opt.sublabel}</small>}
                                    </div>
                                    {value == opt[valueField] && <i className="fas fa-check-circle text-primary"></i>}
                                </div>
                            ))
                        ) : !loading && (
                            <div className="p-5 text-center">
                                <div className="bg-gray-50 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style={{ width: '60px', height: '60px' }}>
                                    <i className="fas fa-search-minus text-gray-200 fs-4"></i>
                                </div>
                                <p className="mb-0 fw-extrabold extra-small text-muted text-uppercase tracking-widest opacity-50">Zero matches detected</p>
                            </div>
                        )}
                    </div>

                    {/* Action Footer */}
                    {onAddNew && (
                        <div className="p-3 border-top bg-gray-50">
                            <button 
                                type="button" 
                                className="btn btn-primary btn-sm w-100 rounded-pill fw-extrabold extra-small tracking-widest shadow-sm d-flex align-items-center justify-content-center gap-2 py-2"
                                onClick={() => {
                                    setIsOpen(false);
                                    onAddNew();
                                }}
                            >
                                <i className="fas fa-plus-circle"></i>
                                <span>{addNewLabel.toUpperCase()}</span>
                            </button>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
