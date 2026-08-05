import DashboardPanel from '@/Components/DashboardPanel';

function normalizeIcon(icon) {
    if (!icon) return undefined;
    const match = String(icon).match(/fa-[a-z0-9-]+/i);
    return match ? match[0] : icon;
}

function inferHeaderVariant(headerClassName = '') {
    const h = headerClassName || '';

    if (
        h.includes('bg-gradient') ||
        (h.includes('text-white') && (h.includes('bg-primary') || h.includes('gradient')))
    ) {
        return 'gradient';
    }
    if (h.includes('bg-pink-500') || (h.includes('text-white') && !h.includes('bg-white'))) {
        return 'pink';
    }
    if (h.includes('success-subtle') || h.includes('success-emphasis')) {
        return 'subtle-success';
    }
    if (h.includes('bg-blue-50') || h.includes('text-blue')) {
        return 'subtle-info';
    }
    if (h.includes('bg-purple-50') || h.includes('text-purple')) {
        return 'subtle-purple';
    }
    if (h.includes('bg-white') || h.includes('border-bottom')) {
        return 'section';
    }

    return 'section';
}

/**
 * Form section card — uses the same DashboardPanel shell as registry tables.
 */
export default function FormSection({
    title,
    icon,
    children,
    actions,
    className = '',
    headerClassName = '',
    bodyClassName = '',
    headerVariant,
    overflowHidden = false,
}) {
    const variant = headerVariant || inferHeaderVariant(headerClassName);

    return (
        <DashboardPanel
            title={title}
            icon={normalizeIcon(icon)}
            headerVariant={variant}
            actions={actions}
            className={`nyl-form-section mb-4 ${className}`}
            bodyClassName={bodyClassName}
            headerClassName={headerClassName}
            overflowHidden={overflowHidden}
        >
            {children}
        </DashboardPanel>
    );
}
