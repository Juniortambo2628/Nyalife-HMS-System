/**
 * Resolves a stored image path to a browser-ready URL.
 * Public assets (/assets/...) and absolute URLs are returned as-is.
 * CMS/upload paths are prefixed with /storage/.
 */
export function resolvePublicImageUrl(path, fallback = '') {
    if (!path) return fallback;

    if (path.startsWith('http://') || path.startsWith('https://')) {
        return path;
    }

    // Fix mistaken /storage//assets/... prefixes from legacy data or bad joins
    if (path.startsWith('/storage/')) {
        const withoutStorage = path.replace(/^\/storage\/+/, '/');
        if (withoutStorage.startsWith('/assets/')) {
            return withoutStorage;
        }
        return path.replace(/\/+/g, '/');
    }

    if (path.startsWith('/assets/') || path.includes('/assets/')) {
        return path.startsWith('/') ? path : `/${path}`;
    }

    if (path.startsWith('/')) {
        return path;
    }

    const clean = path.replace(/^\/storage\//, '').replace(/^\//, '');
    return `/storage/${clean}`;
}
