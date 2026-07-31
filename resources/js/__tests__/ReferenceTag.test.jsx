import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import ReferenceTag from '@/Components/ReferenceTag';

describe('ReferenceTag', () => {
    const patient = { id: 1, label: 'Jane Doe (P-001)', type: 'patient' };
    const medication = { id: 5, label: 'Paracetamol 500mg', type: 'medication' };

    describe('input variant', () => {
        it('renders the reference label and icon', () => {
            render(<ReferenceTag reference={patient} variant="input" />);

            expect(screen.getByText('Jane Doe (P-001)')).toBeInTheDocument();
            expect(screen.getByTitle('Patient')).toBeInTheDocument();
        });

        it('uses the type-specific icon class', () => {
            const { container } = render(<ReferenceTag reference={medication} variant="input" />);

            expect(container.querySelector('.fa-pills')).toBeInTheDocument();
        });

        it('does not render the remove button when onRemove is omitted', () => {
            render(<ReferenceTag reference={patient} variant="input" />);

            expect(screen.queryByRole('button', { name: /remove/i })).not.toBeInTheDocument();
        });

        it('renders an accessible remove button when onRemove is provided', () => {
            render(<ReferenceTag reference={patient} variant="input" onRemove={() => {}} />);

            expect(
                screen.getByRole('button', { name: 'Remove Jane Doe (P-001) reference' }),
            ).toBeInTheDocument();
        });

        it('calls onRemove with the reference when the remove button is clicked', async () => {
            const handleRemove = vi.fn();
            const user = userEvent.setup();

            render(<ReferenceTag reference={patient} variant="input" onRemove={handleRemove} />);
            await user.click(screen.getByRole('button', { name: /remove/i }));

            expect(handleRemove).toHaveBeenCalledTimes(1);
            expect(handleRemove).toHaveBeenCalledWith(patient);
        });
    });

    describe('message variant', () => {
        it('renders the reference without a remove button', () => {
            render(<ReferenceTag reference={patient} variant="message" />);

            expect(screen.getByText('Jane Doe (P-001)')).toBeInTheDocument();
            expect(screen.queryByRole('button')).not.toBeInTheDocument();
        });

        it('applies the is-own class when isOwnMessage is true', () => {
            const { container } = render(
                <ReferenceTag reference={patient} variant="message" isOwnMessage />,
            );

            expect(container.querySelector('.nyl-ref-tag--message.is-own')).toBeInTheDocument();
        });

        it('applies the is-peer class by default', () => {
            const { container } = render(<ReferenceTag reference={patient} variant="message" />);

            expect(container.querySelector('.nyl-ref-tag--message.is-peer')).toBeInTheDocument();
        });
    });

    it('returns null when reference is missing', () => {
        const { container } = render(<ReferenceTag variant="input" />);

        expect(container.firstChild).toBeNull();
    });
});
