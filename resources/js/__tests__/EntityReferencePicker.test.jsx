import { describe, it, expect, vi } from 'vitest';
import { formatReferenceType, getReferenceIcon, REFERENCE_TYPE_ICONS } from '@/Components/EntityReferencePicker';

describe('EntityReferencePicker helpers', () => {
    describe('formatReferenceType', () => {
        it('replaces all underscores with spaces', () => {
            expect(formatReferenceType('medical_procedures')).toBe('Medical Procedures');
            expect(formatReferenceType('lab_request')).toBe('Lab Request');
        });

        it('capitalises each word', () => {
            expect(formatReferenceType('patient')).toBe('Patient');
            expect(formatReferenceType('prescription_items')).toBe('Prescription Items');
        });

        it('returns an empty string for falsy input', () => {
            expect(formatReferenceType('')).toBe('');
            expect(formatReferenceType(null)).toBe('');
            expect(formatReferenceType(undefined)).toBe('');
        });

        it('returns the type unchanged when there are no underscores or lowercase letters', () => {
            expect(formatReferenceType('patient')).toBe('Patient');
        });
    });

    describe('getReferenceIcon', () => {
        it('returns the registered icon for known types', () => {
            expect(getReferenceIcon('patient')).toBe(REFERENCE_TYPE_ICONS.patient);
            expect(getReferenceIcon('appointment')).toBe(REFERENCE_TYPE_ICONS.appointment);
            expect(getReferenceIcon('consultation')).toBe(REFERENCE_TYPE_ICONS.consultation);
            expect(getReferenceIcon('lab_request')).toBe(REFERENCE_TYPE_ICONS.lab_request);
            expect(getReferenceIcon('medication')).toBe(REFERENCE_TYPE_ICONS.medication);
        });

        it('returns a generic fallback icon for unknown types', () => {
            expect(getReferenceIcon('unknown_type')).toBe('fa-link');
            expect(getReferenceIcon('')).toBe('fa-link');
        });
    });
});
