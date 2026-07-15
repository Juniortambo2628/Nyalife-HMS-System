import Pagination from '@/Components/Pagination';

export default function PaginationFooter({ pagination, className = '' }) {
    if (!pagination?.links?.length || pagination.links.length <= 3) return null;

    return (
        <div className={`px-4 py-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 border-top border-light card-footer bg-white border-0 ${className}`}>
            <div className="text-muted small fw-medium">
                Showing <span className="fw-bold text-gray-900">{pagination.from || 0}</span> to <span className="fw-bold text-gray-900">{pagination.to || 0}</span> of <span className="fw-bold text-gray-900">{pagination.total}</span> entries
            </div>
            <Pagination links={pagination.links} />
        </div>
    );
}
