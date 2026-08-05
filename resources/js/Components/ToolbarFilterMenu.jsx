import React, { useEffect, useRef, useState } from 'react';

/**
 * Single filter toggler with configurable filter types in the submenu.
 *
 * @param {Array<{ id: string, label: string, options: Array<{label: string, value: string}>, value: string, onChange: (value: string) => void, allowEmpty?: boolean, emptyLabel?: string }>} groups
 */
export default function ToolbarFilterMenu({ groups = [], dropup = true }) {
    const [isOpen, setIsOpen] = useState(false);
    const [activeGroupId, setActiveGroupId] = useState(null);
    const rootRef = useRef(null);

    const validGroups = groups.filter(Boolean);
    const activeCount = validGroups.filter((g) => g.value !== '' && g.value != null).length;

    useEffect(() => {
        if (validGroups.length === 0) {
            return undefined;
        }
        if (!activeGroupId || !validGroups.some((g) => g.id === activeGroupId)) {
            setActiveGroupId(validGroups[0].id);
        }
    }, [validGroups, activeGroupId]);

    useEffect(() => {
        const handleClickOutside = (e) => {
            if (rootRef.current && !rootRef.current.contains(e.target)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    if (validGroups.length === 0) {
        return null;
    }

    const activeGroup = validGroups.find((g) => g.id === activeGroupId) || validGroups[0];

    const getOptions = (group) => {
        const base = group.options || [];
        if (group.allowEmpty !== false && !base.some((o) => o.value === '' || o.value == null)) {
            return [{ label: group.emptyLabel || `All ${group.label}`, value: '' }, ...base];
        }
        return base;
    };

    const clearAll = () => {
        validGroups.forEach((g) => g.onChange?.(''));
    };

    return (
        <div className={`dropdown toolbar-filter-menu ${isOpen ? 'show' : ''}`} ref={rootRef}>
            <button
                type="button"
                className={`btn nyl-tb-filter-trigger ${activeCount > 0 ? 'has-value' : ''}`}
                onClick={() => setIsOpen((open) => !open)}
                aria-expanded={isOpen}
                aria-haspopup="true"
            >
                <i className="fas fa-filter" aria-hidden="true"></i>
                <span className="d-none d-sm-inline">Filters</span>
                {activeCount > 0 && <span className="nyl-tb-filter-count">{activeCount}</span>}
                <i
                    className={`fas fa-chevron-${isOpen ? (dropup ? 'up' : 'down') : dropup ? 'down' : 'up'} nyl-tb-filter-chevron`}
                    aria-hidden="true"
                ></i>
            </button>

            <div
                className={`dropdown-menu shadow-2xl border-0 rounded-2xl p-0 nyl-tb-filter-panel ${isOpen ? 'show' : ''}`}
                style={{
                    zIndex: 1100,
                    position: 'absolute',
                    left: '50%',
                    transform: 'translateX(-50%)',
                    ...(dropup ? { bottom: 'calc(100% + 0.5rem)' } : { top: 'calc(100% + 0.5rem)' }),
                }}
            >
                {validGroups.length > 1 && (
                    <div className="nyl-tb-filter-tabs">
                        {validGroups.map((group) => {
                            const hasValue = group.value !== '' && group.value != null;
                            return (
                                <button
                                    key={group.id}
                                    type="button"
                                    className={`nyl-tb-filter-tab ${activeGroupId === group.id ? 'is-active' : ''}`}
                                    onClick={() => setActiveGroupId(group.id)}
                                >
                                    {group.label}
                                    {hasValue && <span className="nyl-tb-filter-tab-dot" />}
                                </button>
                            );
                        })}
                    </div>
                )}

                <div className="nyl-tb-filter-options">
                    {validGroups.length === 1 && <div className="nyl-tb-filter-section-label">{activeGroup.label}</div>}
                    {getOptions(activeGroup).map((opt) => {
                        const isSelected = activeGroup.value == opt.value;
                        return (
                            <button
                                key={`${activeGroup.id}-${opt.value}`}
                                type="button"
                                className={`nyl-tb-filter-option ${isSelected ? 'is-selected' : ''}`}
                                onClick={() => {
                                    activeGroup.onChange?.(opt.value);
                                }}
                            >
                                <span>{opt.label}</span>
                                {isSelected && <i className="fas fa-check text-primary" aria-hidden="true"></i>}
                            </button>
                        );
                    })}
                </div>

                {activeCount > 0 && (
                    <div className="nyl-tb-filter-footer">
                        <button type="button" className="nyl-tb-filter-clear" onClick={clearAll}>
                            Clear all filters
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}
