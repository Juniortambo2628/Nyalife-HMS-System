/**
 * Build stat card items from a total + keyed breakdown (reports subpages).
 */
export function buildBreakdownStats({
    total,
    totalLabel = 'Total',
    totalIcon = 'fa-chart-bar',
    totalColor = 'primary',
    byKey = {},
    labelFormatter = (key) => key.replace(/_/g, ' '),
    icon = 'fa-circle',
    color = 'info',
} = {}) {
    const items = [{ label: totalLabel, value: total, icon: totalIcon, color: totalColor }];

    Object.entries(byKey).forEach(([key, count]) => {
        items.push({
            label: labelFormatter(key),
            value: count,
            icon,
            color,
        });
    });

    return items;
}
