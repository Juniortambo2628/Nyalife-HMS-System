import StatCard from '@/Components/StatCard';

const COL_CLASSES = {
    2: 'col-md-6',
    3: 'col-md-6 col-lg-4',
    4: 'col-md-6 col-lg-3',
    6: 'col-md-6 col-xl-4',
};

/**
 * Responsive grid wrapper for StatCard items.
 *
 * @param {Array} items – props passed to each StatCard
 * @param {2|3|4|6} [cols=4] – columns at large breakpoints
 * @param {number} [gap=4] – Bootstrap g-* spacing
 */
export default function StatCardGrid({ items = [], cols = 4, gap = 4, className = 'mb-4' }) {
    if (!items.length) return null;

    const colClass = COL_CLASSES[cols] || COL_CLASSES[4];

    return (
        <div className={`row g-${gap} ${className}`}>
            {items.map((item, index) => (
                <div key={item.id ?? item.label ?? index} className={item.colClass || colClass}>
                    <StatCard {...item} />
                </div>
            ))}
        </div>
    );
}
