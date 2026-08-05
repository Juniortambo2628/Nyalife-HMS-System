import { useState, useEffect, useCallback } from 'react';

/**
 * Reusable selection state for registry/table pages.
 *
 * @param {object} options
 * @param {string} [options.idField='id'] Field to use as unique ID.
 * @param {boolean} [options.listenForClear=true] Listen for 'toolbar-clear-selection'.
 * @returns {{ selectedIds: any[], setSelectedIds: Function, toggleSelection: Function, selectAll: Function, selectNone: Function, isSelected: Function, clearSelection: Function }}
 */
export default function useSelectionState({ idField = 'id', listenForClear = true } = {}) {
    const [selectedIds, setSelectedIds] = useState([]);

    const clearSelection = useCallback(() => setSelectedIds([]), []);
    const selectNone = clearSelection;

    const toggleSelection = useCallback((id) => {
        setSelectedIds((prev) => (prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]));
    }, []);

    const isSelected = useCallback((id) => selectedIds.includes(id), [selectedIds]);

    const selectAll = useCallback(
        (items) => {
            if (!Array.isArray(items)) return;
            setSelectedIds(items.map((item) => (item && typeof item === 'object' ? item[idField] : item)));
        },
        [idField],
    );

    useEffect(() => {
        if (!listenForClear) return;

        const handleClear = () => clearSelection();
        window.addEventListener('toolbar-clear-selection', handleClear);
        return () => window.removeEventListener('toolbar-clear-selection', handleClear);
    }, [listenForClear, clearSelection]);

    return {
        selectedIds,
        setSelectedIds,
        toggleSelection,
        selectAll,
        selectNone,
        isSelected,
        clearSelection,
    };
}
