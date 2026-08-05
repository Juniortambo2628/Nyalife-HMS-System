import DashboardHero from '@/Components/DashboardHero';
import StatCardGrid from '@/Components/StatCardGrid';

/**
 * Shared layout for role dashboards: hero banner + optional stat grid + page content.
 */
export default function RoleDashboardShell({ hero, statItems, statCols = 4, children }) {
    return (
        <div className="px-0">
            {hero && <DashboardHero title={hero.title} subtitle={hero.subtitle} icon={hero.icon} />}

            {statItems?.length > 0 && <StatCardGrid items={statItems} cols={statCols} />}

            {children}
        </div>
    );
}
