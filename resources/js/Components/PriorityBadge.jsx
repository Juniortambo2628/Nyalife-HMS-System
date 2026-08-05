import StatusBadge from '@/Components/StatusBadge';

const PRIORITY_STATUSES = new Set(['urgent', 'routine', 'emergency', 'stat', 'high', 'normal']);

/**
 * Priority column badge — delegates to StatusBadge for consistent styling.
 */
export default function PriorityBadge({ priority = 'normal' }) {
    const key = (priority || 'normal').toLowerCase();
    const status = PRIORITY_STATUSES.has(key) ? key : 'normal';
    return <StatusBadge status={status} />;
}
