/** Format a number as Kenyan Shillings (no decimal places by default). */
export function formatCurrency(value, { maximumFractionDigits = 0 } = {}) {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        maximumFractionDigits,
    }).format(value || 0);
}

/** Format a plain number with locale grouping. */
export function formatNumber(value) {
    return new Intl.NumberFormat('en-KE').format(value || 0);
}
