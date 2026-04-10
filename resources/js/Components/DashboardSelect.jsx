import React, { useState, useEffect, useRef, useMemo } from 'react';
import axios from 'axios';

/**
 * DashboardSelect - A unified, robust searchable select component.
 * 
 * Supports:
 * - Static options: pass an array of objects.
 * - Async search: pass an asyncUrl.
 * - Custom labels/values: labelField, valueField.
 * - Add New Action: onAddNew function.
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
    initialLabel = null
}) {
    const [isOpen, setIsOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [asyncOptions, setAsyncOptions] = useState([]);
    const [loading, setLoading] = useState(false);
    const [selectedDisplay, setSelectedDisplay] = useState(initialLabel || '');
    
    const dropdownRef = useRef(null);
    const debounceTimer = useRef(null);

    // Filter static options if provided
    const filteredOptions = useMemo(() => {
        if (asyncUrl) return asyncOptions;
        if (!searchTerm) return options;
        return options.filter(opt => 
            String(opt[labelField]).toLowerCase().includes(searchTerm.toLowerCase())
        );
    }, [options, searchTerm, asyncOptions, asyncUrl]);

    // Update selected display when value or options change
    useEffect(() => {
        if (value) {
            const found = options.find(opt => opt[valueField] == value);
            if (found) {
                setSelectedDisplay(found[labelField]);
            } else if (!initialLabel && !asyncUrl) {
                setSelectedDisplay('');
            }
        } else {
            setSelectedDisplay('');
        }
    }, [value, options]);

    // Handle Async Search
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

    // Close on click outside
    useEffect(() => {
        const handleClickOutside = (e) => {
            if (dropdownRef.current && !dropdownRef.current.contains(e.target)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    return (
        <div className={`dashboard-select-container position-relative w-100 ${className}`} ref={dropdownRef} style={{ overflow: 'visible' }}>
            {/* Trigger Area */}
            <div 
                className={`form-control border-0 bg-light rounded-xl py-2 px-3 d-flex justify-content-between align-items-center cursor-pointer transition-all ${isOpen ? 'shadow-sm ring-2 ring-primary ring-opacity-20' : ''}`}
                onClick={() => setIsOpen(!isOpen)}
                style={{ cursor: 'pointer', minHeight: '45px' }}
            >
                <div className="d-flex align-items-center gap-2 overflow-hidden">
                    {loading && <div className="spinner-border spinner-border-sm text-primary opacity-50" role="status"></div>}
                    <span className={`text-truncate ${value ? 'text-gray-900 fw-bold' : 'text-muted'}`}>
                        {selectedDisplay || placeholder}
                    </span>
                </div>
                <i className={`fas fa-chevron-${isOpen ? 'up' : 'down'} text-primary fs-xs transition-all duration-300 ${isOpen ? 'opacity-100' : 'opacity-40'}`}></i>
            </div>

            {/* Dropdown Menu */}
            {isOpen && (
                <div 
                    className="position-absolute top-100 start-0 w-100 mt-2 card shadow-lg border-0 rounded-2xl overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200"
                    style={{ zIndex: 9999, minWidth: '280px' }}
                >
                    {/* Search Input Area */}
                    <div className="p-3 border-bottom bg-white sticky-top">
                        <div className="input-group input-group-sm bg-light rounded-pill px-2">
                            <span className="input-group-text bg-transparent border-0 px-2">
                                <i className="fas fa-search text-gray-300"></i>
                            </span>
                            <input 
                                type="text" 
                                className="form-control bg-transparent border-0 shadow-none py-2" 
                                placeholder={searchPlaceholder}
                                value={searchTerm}
                                onChange={e => setSearchTerm(e.target.value)}
                                autoFocus
                            />
                        </div>
                    </div>

                    {/* Options List */}
                    <div className="overflow-auto custom-scrollbar" style={{ maxHeight: '300px' }}>
                        {loading && asyncUrl && (
                            <div className="p-4 text-center">
                                <div className="spinner-border text-primary opacity-25" role="status"></div>
                                <div className="mt-2 small text-muted font-bold uppercase tracking-widest">Searching...</div>
                            </div>
                        )}

                        {!loading && filteredOptions.length > 0 ? (
                            filteredOptions.map((opt, i) => (
                                <div 
                                    key={opt[valueField] || i}
                                    className={`px-4 py-3 cursor-pointer hover-bg-light transition-all d-flex justify-content-between align-items-center ${value == opt[valueField] ? 'bg-primary-subtle text-primary fw-bold' : 'text-gray-700'}`}
                                    onClick={() => {
                                        onChange(opt[valueField], opt);
                                        setSelectedDisplay(opt[labelField]);
                                        setIsOpen(false);
                                        setSearchTerm('');
                                    }}
                                >
                                    <div className="d-flex flex-column">
                                        <span className="fs-6">{opt[labelField]}</span>
                                        {opt.sublabel && <small className="text-muted opacity-75">{opt.sublabel}</small>}
                                    </div>
                                    {value == opt[valueField] && <i className="fas fa-check-circle text-primary animate-in zoom-in duration-300"></i>}
                                </div>
                            ))
                        ) : !loading && (
                            <div className="p-5 text-center text-muted">
                                <i className="fas fa-search-minus fs-1 opacity-10 mb-3"></i>
                                <p className="mb-0 fw-medium small">No matches found.</p>
                                {asyncUrl && searchTerm.length < 2 && (
                                    <p className="extra-small opacity-50">Type at least 2 characters...</p>
                                )}
                            </div>
                        )}
                    </div>

                    {/* Action Footer */}
                    {onAddNew && (
                        <div className="p-3 bg-light border-top">
                            <button 
                                type="button" 
                                className="btn btn-primary w-100 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2"
                                onClick={() => {
                                    setIsOpen(false);
                                    onAddNew();
                                }}
                            >
                                <i className="fas fa-plus-circle"></i>
                                <span>{addNewLabel}</span>
                            </button>
                        </div>
                    )}
                </div>
            )}

            <style>{`
                .hover-bg-light:hover { background-color: #f8f9fa; }
                .rounded-xl { border-radius: 0.85rem; }
                .rounded-2xl { border-radius: 1.25rem; }
                .extra-small { font-size: 0.65rem; }
                .fs-xs { font-size: 0.75rem; }
                .ring-primary { --tw-ring-color: #0d6efd; }
                .ring-2 { box-shadow: 0 0 0 2px var(--tw-ring-color); }
                .custom-scrollbar::-webkit-scrollbar { width: 4px; }
                .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
                .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
                .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
            `}</style>
        </div>
    );
}
