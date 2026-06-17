import DashboardCardHeader from '@/Components/DashboardCardHeader';

/**
 * Standard dashboard card shell: shared header + body (typically a table).
 */
export default function DashboardPanel({
    title,
    icon,
    iconClassName = 'text-pink-500',
    headerVariant = 'gradient',
    actions,
    children,
    className = '',
    bodyClassName = 'p-0',
    headerClassName = '',
    overflowHidden = true,
}) {
    return (
        <div className={`card shadow-sm border-0 rounded-2xl bg-white ${overflowHidden ? 'overflow-hidden' : ''} shadow-hover ${className}`}>
            <DashboardCardHeader
                title={title}
                icon={icon}
                iconClassName={iconClassName}
                variant={headerVariant}
                actions={actions}
                className={headerClassName}
            />
            <div className={`card-body ${bodyClassName}`}>{children}</div>
        </div>
    );
}
