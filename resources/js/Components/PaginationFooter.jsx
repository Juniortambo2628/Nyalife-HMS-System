import Pagination from '@/Components/Pagination';
import { normalizePagination } from '@/Utils/paginationUtils';

export default function PaginationFooter({ pagination, className = '' }) {
    const normalized = normalizePagination(pagination);

    if (!normalized || !normalized.hasPages) return null;

    return (
        <div
            className={`px-4 py-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 border-top border-light card-footer bg-white border-0 ${className}`}
        >
            <div className="text-muted small fw-medium">
                Showing <span className="fw-bold text-gray-900">{normalized.from || 0}</span> to{' '}
                <span className="fw-bold text-gray-900">{normalized.to || 0}</span> of{' '}
                <span className="fw-bold text-gray-900">{normalized.total}</span> entries
            </div>
            <Pagination links={normalized.links} />
        </div>
    );
}
