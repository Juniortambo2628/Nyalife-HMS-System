import { render, screen, fireEvent } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';
import Create from '../Create';

// Mock Inertia's useForm hook
vi.mock('@inertiajs/react', () => ({
    useForm: (initialValues) => {
        return {
            data: initialValues,
            setData: vi.fn(),
            post: vi.fn(),
            put: vi.fn(),
            processing: false,
            errors: {},
            transform: vi.fn((cb) => { cb(initialValues); }),
        };
    },
    usePage: () => ({
        props: {
            auth: { user: { role: 'doctor' } }
        }
    })
}));

describe('Consultation Create Component', () => {
    it('renders without crashing', () => {
        render(<Create patients={[]} doctors={[]} />);
        expect(screen.getByText(/Create Consultation/i)).toBeInTheDocument();
    });

    it('has SAVE VITALS button', () => {
        render(<Create patients={[]} doctors={[]} />);
        expect(screen.getByText(/SAVE VITALS/i)).toBeInTheDocument();
    });
});
