import React, { useState, useEffect, useRef, useMemo } from 'react';
import axios from 'axios';
import Modal from '@/Components/Modal';

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
    const [modalOpen, setModalOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [asyncOptions, setAsyncOptions] = useState([]);
    const [loading, setLoading] = useState(false);
    const [selectedDisplay, setSelectedDisplay] = useState(initialLabel || '');
    const searchInputRef = useRef(null);
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

    // Focus search input when modal opens
    useEffect(() => {
        if (modalOpen && searchInputRef.current) {
            setTimeout(() => searchInputRef.current.focus(), 100);
        }
    }, [modalOpen]);

    // Reset search when modal opens/closes
    useEffect(() => {
        if (!modalOpen) {
            setSearchTerm('');
        }
    }, [modalOpen]);

    const openModal = () => setModalOpen(true);
    const closeModal = () => setModalOpen(false);

    const selectOption = (opt) => {
        onChange(opt[valueField], opt);
        setSelectedDisplay(opt[labelField]);
        closeModal();
    };

    const clearValue = (e) => {
        e.stopPropagation();
        onChange(null, null);
        setSelectedDisplay('');
        setSearchTerm('');
    };

    const triggerTextColor = (value) ? 'text-pink-500' : 'text-pink-500';
    const triggerBgColor = (value) ? 'bg-white' : (isDark ? 'bg-white bg-opacity-20 hover-bg-opacity-30' : 'bg-white');

    const hasValue = !!value;

    return (
        <div className={`dashboard-select-container ${className}`} style={style}>
            {/* Trigger Area */}
            <div 
                className={`form-control border-0 rounded-pill py-2 px-4 d-flex justify-content-between align-items-center cursor-pointer transition-all shadow-sm nyl-select-trigger ${triggerBgColor} ${triggerTextColor} ${!isDark && !value ? 'border' : ''}`}
                onClick={openModal}
                role="button"
                tabIndex={0}
                onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') openModal(); }}
            >
                <div className="d-flex align-items-center gap-2 overflow-hidden flex-grow-1">
                    <i className="fas fa-search text-pink-500 extra-small"></i>
                    <span className="fw-extrabold extra-small tracking-widest text-uppercase text-truncate">
                        {selectedDisplay || placeholder}
                    </span>
                </div>
                <div className="d-flex align-items-center gap-2">
                    {value && (
                        <button 
                            type="button"
                            className="btn btn-link p-0 border-0 shadow-none nyl-select-clear"
                            onClick={clearValue}
                        >
                            <i className="fas fa-times-circle"></i>
                        </button>
                    )}
                    <i className="fas fa-chevron-down text-pink-500 fs-xs opacity-40"></i>
                </div>
            </div>

            {/* Search Modal */}
            <Modal show={modalOpen} onClose={closeModal} maxWidth="lg">
                <div className="p-4" style={{ minHeight: '400px', maxHeight: '80vh', display: 'flex', flexDirection: 'column' }}>
                    {/* Search Input */}
                    <div className="mb-3">
                        <div className="input-group input-group-lg rounded-pill shadow-sm border bg-white overflow-hidden">
                            <span className="input-group-text bg-transparent border-0 px-3">
                                <i className="fas fa-search text-pink-400"></i>
                            </span>
                            <input 
                                ref={searchInputRef}
                                type="text" 
                                className="form-control border-0 shadow-none py-3 fw-bold text-dark" 
                                placeholder={searchPlaceholder}
                                value={searchTerm}
                                onChange={e => setSearchTerm(e.target.value)}
                            />
                            {loading && (
                                <span className="input-group-text bg-transparent border-0 px-3">
                                    <div className="spinner-border spinner-border-sm text-pink-500" role="status"></div>
                                </span>
                            )}
                        </div>
                    </div>

                    {/* Results */}
                    <div className="flex-grow-1 overflow-auto custom-scrollbar" style={{ flex: '1 1 auto', minHeight: 0 }}>
                        {asyncUrl && searchTerm.length < 2 && (
                            <div className="p-5 text-center text-muted">
                                <i className="fas fa-search fs-2 opacity-25 mb-3 d-block"></i>
                                <p className="fw-bold extra-small text-uppercase tracking-widest opacity-50 mb-0">
                                    Type at least 2 characters to search
                                </p>
                            </div>
                        )}

                        {loading && (
                            <div className="p-5 text-center">
                                <div className="spinner-border text-pink-500" role="status">
                                    <span className="visually-hidden">Searching...</span>
                                </div>
                                <p className="mt-3 fw-bold extra-small text-uppercase tracking-widest text-muted opacity-50 mb-0">
                                    Searching...
                                </p>
                            </div>
                        )}

                        {!loading && filteredOptions.length > 0 && (
                            <div className="list-group list-group-flush">
                                {filteredOptions.map((opt, i) => (
                                    <button
                                        key={opt[valueField] || i}
                                        type="button"
                                        className={`list-group-item list-group-item-action border-0 rounded-lg py-3 px-4 text-start d-flex justify-content-between align-items-center ${value == opt[valueField] ? 'bg-pink-50 text-pink-600 fw-extrabold' : 'text-dark fw-bold'}`}
                                        onClick={() => selectOption(opt)}
                                    >
                                        <div>
                                            <div className="extra-small text-uppercase tracking-tight">{opt[labelField]}</div>
                                            {opt.sublabel && <small className="text-muted extra-small opacity-75 d-block mt-1">{opt.sublabel}</small>}
                                        </div>
                                        {value == opt[valueField] && <i className="fas fa-check-circle text-pink-500"></i>}
                                    </button>
                                ))}
                            </div>
                        )}

                        {!loading && filteredOptions.length === 0 && !(asyncUrl && searchTerm.length < 2) && (
                            <div className="p-5 text-center">
                                <div className="bg-gray-50 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style={{ width: '70px', height: '70px' }}>
                                    <i className={`${searchTerm ? 'fas fa-search-minus text-gray-300' : 'fas fa-inbox text-gray-300'} fs-3`}></i>
                                </div>
                                <p className="fw-bold extra-small text-uppercase tracking-widest text-muted opacity-50 mb-0">
                                    {searchTerm ? 'No matching results found' : 'No options available'}
                                </p>
                                {searchTerm && <p className="extra-small text-muted mt-2 opacity-50">Try a different search term</p>}
                                {!searchTerm && onAddNew && <p className="extra-small text-muted mt-2 opacity-50">Add a new option below</p>}
                            </div>
                        )}
                    </div>

                    {/* Add New Footer */}
                    {onAddNew && (
                        <div className="border-top pt-3 mt-3">
                            <button 
                                type="button" 
                                className="btn btn-outline-pink btn-lg w-100 rounded-pill fw-extrabold extra-small tracking-widest d-flex align-items-center justify-content-center gap-2 py-3"
                                onClick={() => {
                                    closeModal();
                                    onAddNew();
                                }}
                            >
                                <i className="fas fa-plus-circle"></i>
                                <span>{addNewLabel.toUpperCase()}</span>
                            </button>
                        </div>
                    )}
                </div>
            </Modal>
        </div>
    );
}
