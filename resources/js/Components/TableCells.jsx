/**
 * Shared table cell primitives — consistent typography and badges across modules.
 */

const REF_VARIANTS = {
    primary: 'nyl-ref-badge nyl-ref-badge--primary',
    pink: 'nyl-ref-badge nyl-ref-badge--pink',
    info: 'nyl-ref-badge nyl-ref-badge--info',
};

export function RefBadge({ children, variant = 'pink' }) {
    if (children == null || children === '') return null;
    return (
        <span className={REF_VARIANTS[variant] || REF_VARIANTS.pink}>
            {children}
        </span>
    );
}

export function TableCellPrimary({ children, className = '' }) {
    return <div className={`nyl-table-cell-primary ${className}`.trim()}>{children}</div>;
}

export function TableCellSub({ children, className = '' }) {
    return <div className={`nyl-table-cell-sub ${className}`.trim()}>{children}</div>;
}

export function TableCellStack({ primary, secondary, className = '' }) {
    return (
        <div className={className}>
            <TableCellPrimary>{primary}</TableCellPrimary>
            {secondary != null && secondary !== '' && (
                <TableCellSub>{secondary}</TableCellSub>
            )}
        </div>
    );
}

export function TableDateTimeCell({ date, time }) {
    return (
        <TableCellStack
            primary={date || '—'}
            secondary={time || null}
        />
    );
}

export function TableDoctorCell({ doctor, fallback = 'Staff' }) {
    const user = doctor?.user;
    const name = user
        ? `Dr. ${user.last_name || user.first_name || fallback}`
        : `Dr. ${fallback}`;
    return (
        <TableCellStack
            primary={name}
            secondary={doctor?.specialization || null}
        />
    );
}
