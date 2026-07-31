import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, within, fireEvent } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import EntityReferencePicker from '@/Components/EntityReferencePicker';

const ENTITIES = {
    patients: [
        { id: 1, label: 'Jane Doe (P-001)', type: 'patient' },
        { id: 2, label: 'John Roe (P-002)', type: 'patient' },
    ],
    appointments: [
        { id: 11, label: 'Apt #11: Jane (2026-08-01)', type: 'appointment' },
    ],
    lab_requests: [
        { id: 21, label: 'Lab #21 - Full Blood Count (Jane)', type: 'lab_request' },
        { id: 22, label: 'Lab #22 - Urinalysis (John)', type: 'lab_request' },
    ],
    medications: [
        { id: 31, label: 'Paracetamol 500mg', type: 'medication' },
    ],
};

describe('EntityReferencePicker', () => {
    beforeEach(() => {
        // Ensure the dropdown does not outlive test boundaries.
        document.body.innerHTML = '';
    });

    it('renders a closed trigger by default', () => {
        render(<EntityReferencePicker entities={ENTITIES} onSelect={vi.fn()} />);

        expect(screen.getByRole('button', { name: /^add reference$/i })).toHaveAttribute(
            'aria-expanded',
            'false',
        );
    });

    it('opens the dropdown when the trigger is clicked', async () => {
        const user = userEvent.setup();
        render(<EntityReferencePicker entities={ENTITIES} onSelect={vi.fn()} />);

        await user.click(screen.getByRole('button', { name: /^add reference$/i, expanded: false }));

        expect(screen.getByRole('button', { name: /^add reference$/i })).toHaveAttribute('aria-expanded', 'true');
        expect(screen.getByRole('dialog', { name: /reference hospital records/i })).toBeInTheDocument();
    });

    it('shows all entities grouped by type when "All" is selected', async () => {
        const user = userEvent.setup();
        render(<EntityReferencePicker entities={ENTITIES} onSelect={vi.fn()} />);

        await user.click(screen.getByRole('button', { name: /^add reference$/i, expanded: false }));

        expect(screen.getByText('Jane Doe (P-001)')).toBeInTheDocument();
        expect(screen.getByText('Apt #11: Jane (2026-08-01)')).toBeInTheDocument();
        expect(screen.getByText('Lab #21 - Full Blood Count (Jane)')).toBeInTheDocument();
        expect(screen.getByText('Paracetamol 500mg')).toBeInTheDocument();
    });

    it('filters entities by selected type tab', async () => {
        const user = userEvent.setup();
        render(<EntityReferencePicker entities={ENTITIES} onSelect={vi.fn()} />);

        await user.click(screen.getByRole('button', { name: /^add reference$/i, expanded: false }));
        // Click the "Patients" tab inside the dialog.
        const dialog = screen.getByRole('dialog', { name: /reference hospital records/i });
        await user.click(within(dialog).getByRole('button', { name: /^patients$/i }));

        expect(screen.getByText('Jane Doe (P-001)')).toBeInTheDocument();
        expect(screen.getByText('John Roe (P-002)')).toBeInTheDocument();
        expect(screen.queryByText('Paracetamol 500mg')).not.toBeInTheDocument();
        expect(screen.queryByText('Apt #11: Jane (2026-08-01)')).not.toBeInTheDocument();
    });

    it('filters entities by the search input', async () => {
        const user = userEvent.setup();
        render(<EntityReferencePicker entities={ENTITIES} onSelect={vi.fn()} />);

        await user.click(screen.getByRole('button', { name: /^add reference$/i, expanded: false }));

        const searchInput = screen.getByPlaceholderText(/search entities/i);
        await user.type(searchInput, 'Jane');

        expect(screen.getByText('Jane Doe (P-001)')).toBeInTheDocument();
        expect(screen.queryByText('John Roe (P-002)')).not.toBeInTheDocument();
    });

    it('shows the empty state when no entities match the search', async () => {
        const user = userEvent.setup();
        render(<EntityReferencePicker entities={ENTITIES} onSelect={vi.fn()} />);

        await user.click(screen.getByRole('button', { name: /^add reference$/i, expanded: false }));

        const searchInput = screen.getByPlaceholderText(/search entities/i);
        await user.type(searchInput, 'zzzzz-no-match');

        expect(screen.getByText(/no results found/i)).toBeInTheDocument();
    });

    it('calls onSelect and closes when an entity is chosen', async () => {
        const handleSelect = vi.fn();
        const user = userEvent.setup();

        render(<EntityReferencePicker entities={ENTITIES} onSelect={handleSelect} />);
        await user.click(screen.getByRole('button', { name: /add reference/i }));

        const dialog = screen.getByRole('dialog', { name: /reference hospital records/i });
        await user.click(within(dialog).getByRole('button', { name: /jane doe/i }));

        expect(handleSelect).toHaveBeenCalledTimes(1);
        expect(handleSelect).toHaveBeenCalledWith(ENTITIES.patients[0]);
        // Dropdown closes after selection.
        expect(screen.queryByRole('dialog', { name: /reference hospital records/i })).not.toBeInTheDocument();
    });

    it('closes when clicking outside the dropdown', async () => {
        const user = userEvent.setup();
        render(
            <div>
                <EntityReferencePicker entities={ENTITIES} onSelect={vi.fn()} />
                <button data-testid="outside">Outside</button>
            </div>,
        );

        await user.click(screen.getByRole('button', { name: /add reference/i }));
        expect(screen.getByRole('dialog')).toBeInTheDocument();

        // mousedown on outside element should close the panel.
        fireEvent.mouseDown(screen.getByTestId('outside'));

        expect(screen.queryByRole('dialog', { name: /reference hospital records/i })).not.toBeInTheDocument();
    });

    it('closes when Escape is pressed', async () => {
        const user = userEvent.setup();
        render(<EntityReferencePicker entities={ENTITIES} onSelect={vi.fn()} />);

        await user.click(screen.getByRole('button', { name: /^add reference$/i, expanded: false }));
        expect(screen.getByRole('dialog')).toBeInTheDocument();

        await user.keyboard('{Escape}');

        expect(screen.queryByRole('dialog', { name: /reference hospital records/i })).not.toBeInTheDocument();
    });

    it('shows a count badge when there are entities', () => {
        render(<EntityReferencePicker entities={ENTITIES} onSelect={vi.fn()} />);

        // 2 + 1 + 2 + 1 = 6 entities.
        expect(screen.getByText('6')).toBeInTheDocument();
    });

    it('does not render the trigger when entities is empty', () => {
        const { container } = render(<EntityReferencePicker entities={{}} onSelect={vi.fn()} />);

        expect(container.firstChild).toBeNull();
    });
});
