/**
 * Shared card header for dashboard panels — sentence-case titles, no uppercase styling.
 */
export default function DashboardCardHeader({
    title,
    icon,
    iconClassName = 'text-pink-500',
    variant = 'gradient',
    actions,
    className = '',
}) {
    const isPink = variant === 'pink';
    const isWhite = variant === 'white';
    const isSection = variant === 'section';
    const isSubtleSuccess = variant === 'subtle-success';
    const isSubtleInfo = variant === 'subtle-info';
    const isSubtlePurple = variant === 'subtle-purple';

    let headerBg = '';
    let titleClass = 'text-white';
    let iconClass = iconClassName || 'text-white opacity-75';
    let headerPadding = 'py-4 px-4';

    if (isPink) {
        headerBg = 'bg-pink-500';
    } else if (isWhite) {
        headerBg = 'bg-white';
        titleClass = 'text-gray-900';
        iconClass = iconClassName;
    } else if (isSection) {
        headerBg = 'bg-white border-bottom';
        titleClass = 'text-pink-500 extra-small text-uppercase tracking-widest';
        iconClass = iconClassName || 'text-pink-500';
        headerPadding = 'py-3 px-4';
    } else if (isSubtleSuccess) {
        headerBg = 'bg-success-subtle border-bottom';
        titleClass = 'text-success-emphasis extra-small text-uppercase tracking-widest';
        iconClass = iconClassName || 'text-success';
        headerPadding = 'py-3 px-4';
    } else if (isSubtleInfo) {
        headerBg = 'bg-blue-50 border-bottom';
        titleClass = 'text-blue-700 extra-small text-uppercase tracking-widest';
        iconClass = iconClassName || 'text-blue-600';
        headerPadding = 'py-3 px-4';
    } else if (isSubtlePurple) {
        headerBg = 'bg-purple-50 border-bottom';
        titleClass = 'text-purple-700 extra-small text-uppercase tracking-widest';
        iconClass = iconClassName || 'text-purple-600';
        headerPadding = 'py-3 px-4';
    }

    return (
        <div
            className={`card-header ${headerBg} ${headerPadding} border-bottom-0 d-flex justify-content-between align-items-center rounded-top-2xl ${className}`}
        >
            <h6 className={`mb-0 fw-extrabold ${titleClass}`}>
                {icon && <i className={`fas ${icon} ${iconClass} me-2`}></i>}
                {title}
            </h6>
            {actions}
        </div>
    );
}
