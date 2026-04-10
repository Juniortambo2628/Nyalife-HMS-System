import { Link } from '@inertiajs/react';

export default function Pagination({ links, className = '' }) {
    if (links.length <= 3) return null;

    return (
        <nav aria-label="Page navigation" className={className}>
            <ul className="pagination pagination-sm justify-content-center mb-0">
                {links.map((link, i) => (
                    <li key={i} className={`page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`}>
                        <Link
                            className="page-link rounded-circle mx-1"
                            href={link.url}
                            dangerouslySetInnerHTML={{ __html: link.alias || link.label }}
                        />
                    </li>
                ))}
            </ul>
        </nav>
    );
}
