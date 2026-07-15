import { useState, useCallback } from 'react';
import { router } from '@inertiajs/react';

/**
 * Reusable bulk-action handler for registry pages.
 *
 * @param {object} options
 * @param {string} options.routeName Inertia route name for the bulk endpoint.
 * @param {any[]} [options.selectedIds=[]] Currently selected IDs.
 * @param {Function} [options.clearSelection] Called after successful submission.
 * @param {Function} [options.onSuccess] Optional callback fired on success.
 * @returns {{ handleBulkAction: Function, processing: boolean }}
 */
export default function useBulkAction({
    routeName,
    selectedIds = [],
    clearSelection,
    onSuccess,
}) {
    const [processing, setProcessing] = useState(false);

    const handleBulkAction = useCallback(
        (action, customHandler) => {
            if (typeof customHandler === 'function') {
                const result = customHandler(action, selectedIds);
                if (result !== false && clearSelection) {
                    clearSelection();
                }
                return;
            }

            if (!routeName) return;

            setProcessing(true);
            router.post(
                route(routeName),
                { action, ids: selectedIds },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        if (clearSelection) clearSelection();
                        if (onSuccess) onSuccess(action, selectedIds);
                    },
                    onFinish: () => setProcessing(false),
                }
            );
        },
        [routeName, selectedIds, clearSelection, onSuccess]
    );

    return { handleBulkAction, processing };
}
