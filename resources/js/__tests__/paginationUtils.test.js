import { describe, it, expect } from 'vitest';
import { extractPaginationLinks, normalizePagination } from '@/Utils/paginationUtils';

describe('paginationUtils', () => {
    describe('extractPaginationLinks', () => {
        it('returns empty array for falsy values', () => {
            expect(extractPaginationLinks(null)).toEqual([]);
            expect(extractPaginationLinks(undefined)).toEqual([]);
        });

        it('returns array directly if an array is passed', () => {
            const links = [{ url: '/?page=1', label: '1', active: true }];
            expect(extractPaginationLinks(links)).toEqual(links);
        });

        it('extracts links from Laravel LengthAwarePaginator shape', () => {
            const paginator = {
                data: [{ id: 1 }],
                links: [
                    { url: null, label: 'Previous', active: false },
                    { url: '/?page=1', label: '1', active: true },
                    { url: '/?page=2', label: '2', active: false },
                    { url: '/?page=2', label: 'Next', active: false },
                ],
            };
            expect(extractPaginationLinks(paginator)).toHaveLength(4);
        });

        it('extracts links from Laravel JsonResource::collection shape (meta.links)', () => {
            const resourceCollection = {
                data: [{ id: 1 }],
                links: {
                    first: '/?page=1',
                    last: '/?page=2',
                    prev: null,
                    next: '/?page=2',
                },
                meta: {
                    current_page: 1,
                    from: 1,
                    to: 15,
                    total: 25,
                    links: [
                        { url: null, label: 'Previous', active: false },
                        { url: '/?page=1', label: '1', active: true },
                        { url: '/?page=2', label: '2', active: false },
                        { url: '/?page=2', label: 'Next', active: false },
                    ],
                },
            };
            expect(extractPaginationLinks(resourceCollection)).toHaveLength(4);
        });
    });

    describe('normalizePagination', () => {
        it('returns null for non-objects', () => {
            expect(normalizePagination(null)).toBeNull();
            expect(normalizePagination(undefined)).toBeNull();
        });

        it('normalizes Laravel LengthAwarePaginator with multiple pages', () => {
            const paginator = {
                current_page: 1,
                from: 1,
                to: 15,
                total: 45,
                last_page: 3,
                links: [
                    { url: null, label: 'Previous', active: false },
                    { url: '/?page=1', label: '1', active: true },
                    { url: '/?page=2', label: '2', active: false },
                    { url: '/?page=3', label: '3', active: false },
                    { url: '/?page=2', label: 'Next', active: false },
                ],
            };

            const norm = normalizePagination(paginator);
            expect(norm).not.toBeNull();
            expect(norm.from).toBe(1);
            expect(norm.to).toBe(15);
            expect(norm.total).toBe(45);
            expect(norm.currentPage).toBe(1);
            expect(norm.lastPage).toBe(3);
            expect(norm.hasPages).toBe(true);
            expect(norm.links).toHaveLength(5);
        });

        it('normalizes Laravel JsonResource::collection with multiple pages', () => {
            const resourceCollection = {
                data: [{ id: 1 }],
                links: {
                    first: '/?page=1',
                    last: '/?page=3',
                    prev: null,
                    next: '/?page=2',
                },
                meta: {
                    current_page: 2,
                    from: 16,
                    to: 30,
                    total: 45,
                    last_page: 3,
                    links: [
                        { url: '/?page=1', label: 'Previous', active: false },
                        { url: '/?page=1', label: '1', active: false },
                        { url: '/?page=2', label: '2', active: true },
                        { url: '/?page=3', label: '3', active: false },
                        { url: '/?page=3', label: 'Next', active: false },
                    ],
                },
            };

            const norm = normalizePagination(resourceCollection);
            expect(norm).not.toBeNull();
            expect(norm.from).toBe(16);
            expect(norm.to).toBe(30);
            expect(norm.total).toBe(45);
            expect(norm.currentPage).toBe(2);
            expect(norm.lastPage).toBe(3);
            expect(norm.hasPages).toBe(true);
            expect(norm.links).toHaveLength(5);
        });

        it('identifies single-page results where hasPages is false', () => {
            const singlePage = {
                current_page: 1,
                from: 1,
                to: 5,
                total: 5,
                last_page: 1,
                links: [
                    { url: null, label: 'Previous', active: false },
                    { url: '/?page=1', label: '1', active: true },
                    { url: null, label: 'Next', active: false },
                ],
            };

            const norm = normalizePagination(singlePage);
            expect(norm.hasPages).toBe(false);
        });
    });
});
