import { describe, it, expect } from 'vitest';
import {
    toLocalISO,
    formatDateTime,
    formatDateOnly,
    formatDateLong,
    formatTime,
    calculateAge,
} from '@/Utils/dateUtils';

describe('dateUtils', () => {
    it('toLocalISO converts a known UTC date to local ISO (YYYY-MM-DDTHH:mm)', () => {
        const result = toLocalISO('2026-04-13T22:45:00.000Z');
        expect(result).toHaveLength(16);
        expect(result).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/);
    });

    it('formatDateTime returns a readable string for a valid ISO date', () => {
        expect(formatDateTime('2026-04-13T22:45:00.000Z')).toContain('Apr');
        expect(formatDateTime('2026-04-13T22:45:00.000Z')).toContain('2026');
    });

    it('formatDateTime returns "N/A" for falsy values', () => {
        expect(formatDateTime(null)).toBe('N/A');
        expect(formatDateTime('')).toBe('N/A');
    });

    it('formatDateOnly returns a date without time', () => {
        const result = formatDateOnly('2026-04-13T22:45:00.000Z');
        expect(result).toContain('Apr');
        expect(result).toContain('2026');
        expect(result).not.toContain(':');
    });

    it('formatDateLong returns a long-form date', () => {
        const result = formatDateLong('2026-04-13T22:45:00.000Z');
        expect(result).toContain('2026');
        expect(result.length).toBeGreaterThan(10);
    });

    it('formatTime returns a formatted time', () => {
        const result = formatTime('2026-04-13T22:45:00.000Z');
        expect(result).toMatch(/\d{1,2}:\d{2}/);
        expect(result).toMatch(/AM|PM/);
    });

    it('calculateAge returns the correct age from a date of birth', () => {
        const today = new Date();
        const dob = `${today.getFullYear() - 30}-01-01`;
        expect(calculateAge(dob)).toBe(30);
    });

    it('calculateAge returns the fallback when dob is missing or invalid', () => {
        expect(calculateAge(null)).toBeNull();
        expect(calculateAge('')).toBeNull();
        expect(calculateAge('not-a-date', 'unknown')).toBe('unknown');
    });
});
