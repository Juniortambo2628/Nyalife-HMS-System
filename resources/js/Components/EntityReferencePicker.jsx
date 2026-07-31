import React, { useEffect, useMemo, useRef, useState } from 'react';

/**
 * Icon map for entity reference types. Centralised so all entity tags
 * (input chips, message body, dropdown rows) share one source of truth.
 */
export const REFERENCE_TYPE_ICONS = {
    patient: 'fa-user-injured',
    appointment: 'fa-calendar-check',
    consultation: 'fa-stethoscope',
    lab_request: 'fa-vial',
    medication: 'fa-pills',
    prescription: 'fa-prescription',
    invoice: 'fa-file-invoice-dollar',
    payment: 'fa-money-bill-wave',
};

/**
 * Human-readable label for an entity reference type. Replaces underscores
 * with spaces and applies a small set of display overrides.
 */
export const formatReferenceType = (type) => {
    if (!type) return '';
    const overrides = {
        lab_request: 'Lab Request',
    };
    if (overrides[type]) return overrides[type];
    return String(type).replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
};

/**
 * Get the icon class for a reference type, falling back to a generic icon.
 */
export const getReferenceIcon = (type) =>
    REFERENCE_TYPE_ICONS[type] || 'fa-link';

/**
 * EntityReferencePicker — A consistent, accessible entity-reference dropdown.
 *
 * Used wherever users need to attach clinical-record references to a message
 * (or any future context that needs the same UX). Replaces the bespoke
 * dropdown that previously lived inline in Messages/Index.jsx, which used
 * arbitrary Tailwind values, a hard-coded z-index, and a single-replace
 * `_` → ` ` formatter that broke for multi-word types.
 *
 * Design tokens used (defined in nyalife-core.css):
 *   - .nyl-tb-filter-panel    (the dropdown shell)
 *   - .nyl-tb-filter-tabs     (the type-filter row)
 *   - .nyl-tb-filter-tab      (each type chip, including active state)
 *   - .nyl-tb-filter-options  (the scrollable option list)
 *   - .nyl-tb-filter-option   (each selectable row)
 *
 * @param {object} props
 * @param {Array<{ [type: string]: Array<{ id, label, type }> }>} props.entities
 *        Grouped entity map. Keys become the type-filter chips.
 * @param {(entity: { id, label, type }) => void} props.onSelect
 * @param {string} [props.label='Reference Hospital Records']
 * @param {string} [props.placeholder='Search entities...']
 * @param {string} [props.emptyLabel='No results found']
 * @param {boolean} [props.dropup=true]
 * @param {string} [props.className='']
 */
export default function EntityReferencePicker({
    entities = {},
    onSelect,
    label = 'Reference Hospital Records',
    placeholder = 'Search entities...',
    emptyLabel = 'No results found',
    dropup = true,
    className = '',
}) {
    const [isOpen, setIsOpen] = useState(false);
    const [activeType, setActiveType] = useState('all');
    const [search, setSearch] = useState('');
    const rootRef = useRef(null);
    const triggerRef = useRef(null);
    const searchRef = useRef(null);

    const types = useMemo(() => Object.keys(entities), [entities]);
    const totalCount = useMemo(
        () => types.reduce((sum, t) => sum + (entities[t]?.length || 0), 0),
        [entities, types],
    );

    useEffect(() => {
        if (!isOpen) {
            setSearch('');
            return undefined;
        }
        // Focus search input on open for keyboard-first users.
        const t = setTimeout(() => searchRef.current?.focus(), 0);
        return () => clearTimeout(t);
    }, [isOpen]);

    useEffect(() => {
        if (!isOpen) return undefined;
        const handleClickOutside = (e) => {
            if (rootRef.current && !rootRef.current.contains(e.target)) {
                setIsOpen(false);
            }
        };
        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                setIsOpen(false);
                triggerRef.current?.focus();
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        document.addEventListener('keydown', handleEscape);
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
            document.removeEventListener('keydown', handleEscape);
        };
    }, [isOpen]);

    const normalisedSearch = search.trim().toLowerCase();
    const filteredEntities = useMemo(() => {
        const result = {};
        types.forEach((type) => {
            if (activeType !== 'all' && activeType !== type) return;
            const list = entities[type] || [];
            const filtered = !normalisedSearch
                ? list
                : list.filter((item) => String(item.label).toLowerCase().includes(normalisedSearch));
            if (filtered.length > 0) result[type] = filtered;
        });
        return result;
    }, [entities, types, activeType, normalisedSearch]);

    if (types.length === 0 || totalCount === 0) {
        return null;
    }

    const handleSelect = (entity) => {
        onSelect?.(entity);
        setIsOpen(false);
    };

    const handleTriggerKey = (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            setIsOpen((open) => !open);
        }
    };

    return (
        <div
            className={`toolbar-filter-menu nyl-entity-picker ${isOpen ? 'is-open' : ''} ${className}`}
            ref={rootRef}
        >
            <button
                ref={triggerRef}
                type="button"
                className="nyl-tb-filter-trigger nyl-entity-picker-trigger"
                onClick={() => setIsOpen((open) => !open)}
                onKeyDown={handleTriggerKey}
                aria-expanded={isOpen}
                aria-haspopup="dialog"
                aria-label="Add reference"
            >
                <i className="fas fa-plus" aria-hidden="true"></i>
                <span className="d-none d-sm-inline">Reference</span>
                {totalCount > 0 && <span className="nyl-tb-filter-count">{totalCount}</span>}
            </button>

            <div
                className={`dropdown-menu shadow-2xl border-0 rounded-2xl p-0 nyl-tb-filter-panel ${isOpen ? 'show' : ''}`}
                style={{
                    zIndex: 1100,
                    position: 'absolute',
                    right: 0,
                    ...(dropup ? { bottom: 'calc(100% + 0.5rem)' } : { top: 'calc(100% + 0.5rem)' }),
                    minWidth: '320px',
                    maxWidth: '360px',
                }}
                role="dialog"
                aria-label={label}
                hidden={!isOpen}
            >
                <div className="nyl-tb-filter-tabs">
                    <button
                        type="button"
                        className={`nyl-tb-filter-tab ${activeType === 'all' ? 'is-active' : ''}`}
                        onClick={() => setActiveType('all')}
                    >
                        All
                    </button>
                    {types.map((type) => (
                        <button
                            key={type}
                            type="button"
                            className={`nyl-tb-filter-tab ${activeType === type ? 'is-active' : ''}`}
                            onClick={() => setActiveType(type)}
                        >
                            {formatReferenceType(type)}
                        </button>
                    ))}
                </div>

                <div className="nyl-entity-picker-search">
                    <div className="input-group input-group-sm rounded-pill px-3 bg-white border">
                        <span className="input-group-text bg-transparent border-0 px-0 me-2">
                            <i className="fas fa-search text-gray-400"></i>
                        </span>
                        <input
                            ref={searchRef}
                            type="text"
                            className="form-control bg-transparent border-0 shadow-none py-2 fw-extrabold extra-small text-dark"
                            placeholder={placeholder}
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                        />
                    </div>
                </div>

                <div className="nyl-tb-filter-options nyl-entity-picker-options">
                    {Object.keys(filteredEntities).length > 0 ? (
                        Object.entries(filteredEntities).map(([type, list]) => (
                            <div key={type} className="nyl-entity-picker-section">
                                <div className="nyl-tb-filter-section-label">{formatReferenceType(type)}</div>
                                {list.map((ent) => (
                                    <button
                                        key={`${type}-${ent.id}`}
                                        type="button"
                                        className="nyl-tb-filter-option nyl-entity-picker-option"
                                        onClick={() => handleSelect(ent)}
                                        aria-label={`Add reference: ${ent.label}`}
                                    >
                                        <span className="d-flex align-items-center gap-2 text-truncate">
                                            <i className={`fas ${getReferenceIcon(type)} text-pink-500 opacity-75`}></i>
                                            <span className="truncate">{ent.label}</span>
                                        </span>
                                        <i className="fas fa-plus text-pink-500 opacity-50" aria-hidden="true"></i>
                                    </button>
                                ))}
                            </div>
                        ))
                    ) : (
                        <div className="nyl-entity-picker-empty">
                            <i className="fas fa-search-minus d-block mb-2 opacity-50"></i>
                            <span>{emptyLabel}</span>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
