import { render, screen } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';
import Edit from '../Edit';

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
        url: '/consultations/1/edit',
        props: {
            auth: { user: { role: 'doctor' } },
            flash: {},
        }
    })
}));

describe('Consultation Edit Component', () => {
    const mockConsultation = {
        consultation_id: 1,
        patient_id: 1,
        doctor_id: 1,
        vital_signs: { blood_pressure: '120/80' },
        status: 'in_progress',
    };

    it('renders without crashing with existing data', () => {
        render(<Edit consultation={mockConsultation} patients={[]} doctors={[]} />);
        expect(screen.getByText(/Edit Consultation/i)).toBeInTheDocument();
    });

    it('displays existing vital signs', () => {
        render(<Edit consultation={mockConsultation} patients={[]} doctors={[]} />);
        // Usually there is an input with value 120/80
        const bpInput = screen.getByDisplayValue('120/80');
        expect(bpInput).toBeInTheDocument();
    });
});
