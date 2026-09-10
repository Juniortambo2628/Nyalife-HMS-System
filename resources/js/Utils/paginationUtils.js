/**
 * Normalizes pagination data from either:
 * 1. Laravel LengthAwarePaginator (e.g. { data, from, to, total, current_page, last_page, links: [...] })
 * 2. Laravel JsonResource::collection() (e.g. { data, links: { first, last, prev, next }, meta: { from, to, total, current_page, last_page, links: [...] } })
 * 3. Raw links array
 */

export function extractPaginationLinks(paginationOrLinks) {
    if (!paginationOrLinks) return [];

    if (Array.isArray(paginationOrLinks)) {
        return paginationOrLinks;
    }

    if (Array.isArray(paginationOrLinks.meta?.links)) {
        return paginationOrLinks.meta.links;
    }

    if (Array.isArray(paginationOrLinks.links)) {
        return paginationOrLinks.links;
    }

    return [];
}

export function normalizePagination(pagination) {
    if (!pagination || typeof pagination !== 'object') return null;

    const links = extractPaginationLinks(pagination);
    const meta = pagination.meta || {};

    const from = pagination.from ?? meta.from ?? 0;
    const to = pagination.to ?? meta.to ?? 0;
    const total = pagination.total ?? meta.total ?? 0;
    const currentPage = pagination.current_page ?? meta.current_page ?? 1;
    const lastPage = pagination.last_page ?? meta.last_page ?? 1;

    // Has multiple pages if lastPage > 1, or links array has > 3 items (Previous, 1, 2..., Next),
    // or total > 0 and more than one page exists
    const hasPages = lastPage > 1 || links.length > 3 || (total > 0 && (to < total || currentPage > 1));

    return {
        from,
        to,
        total,
        currentPage,
        lastPage,
        links,
        hasPages,
    };
}
