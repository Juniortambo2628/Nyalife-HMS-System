import TableActions from '@/Components/TableActions';

/**
 * GridCardActions — three-dot menu footer for grid/card views (matches table action pattern).
 */
export default function GridCardActions({ actions = [], className = '' }) {
    if (!actions?.filter(Boolean).length) return null;

    return (
        <div className={`d-flex justify-content-end border-top border-gray-50 pt-3 mt-auto ${className}`.trim()}>
            <TableActions actions={actions} />
        </div>
    );
}
