import DashboardPanel from '@/Components/DashboardPanel';
import DashboardTable from '@/Components/DashboardTable';

/**
 * RegistryTablePanel — module list tables inside the standard titled card shell
 * (same pattern as Lab requests / Lab results). Forwards all DashboardTable props.
 */
export default function RegistryTablePanel({
    title,
    icon = 'fa-list',
    iconClassName,
    headerVariant = 'gradient',
    panelActions,
    panelClassName,
    bodyClassName = 'p-0',
    className,
    ...tableProps
}) {
    return (
        <DashboardPanel
            title={title}
            icon={icon}
            iconClassName={iconClassName}
            headerVariant={headerVariant}
            actions={panelActions}
            className={panelClassName}
            bodyClassName={bodyClassName}
        >
            <DashboardTable noCard className={className} {...tableProps} />
        </DashboardPanel>
    );
}
