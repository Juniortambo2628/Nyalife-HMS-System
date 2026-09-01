import { render, screen, waitFor } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';
import Create from '../Create';

// Mock Inertia's useForm hook
vi.mock('@inertiajs/react', () => ({
    Head: ({ children }) => <>{children}</>,
    Link: ({ children, ...props }) => <a {...props}>{children}</a>,
    router: { visit: vi.fn() },
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
        url: '/consultations/create',
        props: {
            auth: { user: { role: 'doctor' } },
            flash: {},
        }
    })
}));

describe('Consultation Create Component', () => {
    it('renders without crashing', () => {
        render(<Create patients={[]} doctors={[]} />);
        expect(screen.getByText(/Record Consultation/i)).toBeInTheDocument();
    });

    it('has SAVE VITALS button', async () => {
        render(<Create patients={[]} doctors={[]} auth={{ user: { role: 'nurse' } }} />);
        await waitFor(() => expect(screen.getByText(/SAVE VITALS/i)).toBeInTheDocument(), { timeout: 2000 });
    });
});
