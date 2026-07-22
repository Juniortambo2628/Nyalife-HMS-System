import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import PrimaryButton from '@/Components/PrimaryButton';

describe('PrimaryButton', () => {
    it('renders its children', () => {
        render(<PrimaryButton>Click me</PrimaryButton>);
        expect(screen.getByRole('button', { name: /click me/i })).toBeInTheDocument();
    });

    it('respects the disabled prop', () => {
        render(<PrimaryButton disabled>Cannot click</PrimaryButton>);
        expect(screen.getByRole('button', { name: /cannot click/i })).toBeDisabled();
    });

    it('calls onClick when clicked', async () => {
        const handleClick = vi.fn();
        const user = userEvent.setup();

        render(<PrimaryButton onClick={handleClick}>Submit</PrimaryButton>);
        await user.click(screen.getByRole('button', { name: /submit/i }));

        expect(handleClick).toHaveBeenCalledTimes(1);
    });
});
