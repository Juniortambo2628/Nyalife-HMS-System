import React from 'react';

/**
 * Icon-only list/grid toggle for UnifiedToolbar.
 */
export default function ToolbarViewToggle({ value = 'list', onChange }) {
    if (!onChange) {
        return null;
    }

    return (
        <div className="nyl-tb-view-toggle" role="group" aria-label="View mode">
            <button
                type="button"
                className={`nyl-tb-view-btn ${value === 'list' ? 'is-active' : ''}`}
                onClick={() => onChange('list')}
                title="List view"
                aria-label="List view"
                aria-pressed={value === 'list'}
            >
                <i className="fas fa-list-ul" aria-hidden="true"></i>
            </button>
            <button
                type="button"
                className={`nyl-tb-view-btn ${value === 'grid' ? 'is-active' : ''}`}
                onClick={() => onChange('grid')}
                title="Grid view"
                aria-label="Grid view"
                aria-pressed={value === 'grid'}
            >
                <i className="fas fa-th-large" aria-hidden="true"></i>
            </button>
        </div>
    );
}
