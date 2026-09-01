import '@testing-library/jest-dom';

const route = (name, params = {}, absolute = true) => {
    const base = typeof name === 'string' ? name.replace(/\./g, '/') : '/';
    const sanitizedBase = `/${base.replace(/^\//, '').replace(/\/$/, '')}`;
    const hasParams = params && typeof params === 'object' && Object.keys(params).length > 0;

    if (!hasParams) {
        return absolute ? sanitizedBase || '/' : sanitizedBase || '/';
    }

    const query = new URLSearchParams(params).toString();
    return `${sanitizedBase || '/'}?${query}`;
};

globalThis.route = route;
window.route = route;

// Polyfill for matchMedia if jsdom doesn't provide it.
Object.defineProperty(window, 'matchMedia', {
    writable: true,
    value: vi.fn().mockImplementation((query) => ({
        matches: false,
        media: query,
        onchange: null,
        addListener: vi.fn(),
        removeListener: vi.fn(),
        addEventListener: vi.fn(),
        removeEventListener: vi.fn(),
        dispatchEvent: vi.fn(),
    })),
});
