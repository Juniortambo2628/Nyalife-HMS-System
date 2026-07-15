import { useState, useCallback } from 'react';

/**
 * Reusable view-mode toggle with optional localStorage persistence.
 *
 * @param {object} options
 * @param {string} [options.storageKey] localStorage key; omit to disable persistence.
 * @param {string} [options.defaultView='list'] Default view ('list', 'grid', etc.).
 * @param {string[]} [options.allowedViews] Optional whitelist.
 * @returns {{ viewMode: string, setViewMode: Function, handleViewChange: Function }}
 */
export default function useViewToggle({
    storageKey,
    defaultView = 'list',
    allowedViews,
} = {}) {
    const getInitialView = useCallback(() => {
        if (!storageKey) return defaultView;
        try {
            const stored = localStorage.getItem(storageKey);
            if (stored) {
                if (allowedViews && !allowedViews.includes(stored)) return defaultView;
                return stored;
            }
        } catch (e) {
            // localStorage unavailable (e.g. private mode)
        }
        return defaultView;
    }, [storageKey, defaultView, allowedViews]);

    const [viewMode, setViewMode] = useState(getInitialView);

    const handleViewChange = useCallback(
        (newView) => {
            if (allowedViews && !allowedViews.includes(newView)) return;
            setViewMode(newView);
            if (storageKey) {
                try {
                    localStorage.setItem(storageKey, newView);
                } catch (e) {
                    // ignore storage errors
                }
            }
        },
        [storageKey, allowedViews]
    );

    return { viewMode, setViewMode, handleViewChange };
}
