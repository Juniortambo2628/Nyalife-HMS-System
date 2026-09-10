import { Link } from '@inertiajs/react';
import { extractPaginationLinks } from '@/Utils/paginationUtils';

export default function Pagination({ links, className = '' }) {
    const linkItems = extractPaginationLinks(links);

    if (!linkItems || linkItems.length <= 3) return null;

    return (
        <nav aria-label="Page navigation" className={className}>
            <ul className="pagination pagination-sm justify-content-center mb-0">
                {linkItems
                    .filter((link) => link !== null)
                    .map((link, i) => (
                        <li
                            key={i}
                            className={`page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`}
                        >
                            {link.url ? (
                                <Link
                                    className="page-link rounded-circle mx-1"
                                    href={link.url}
                                    preserveScroll
                                    dangerouslySetInnerHTML={{ __html: link.alias || link.label || '' }}
                                />
                            ) : (
                                <span
                                    className="page-link rounded-circle mx-1"
                                    dangerouslySetInnerHTML={{ __html: link.alias || link.label || '' }}
                                />
                            )}
                        </li>
                    ))}
            </ul>
        </nav>
    );
}
