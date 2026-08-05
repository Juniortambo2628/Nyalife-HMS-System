import React, { useRef } from 'react';
import { useForm } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import FormField from '@/Components/FormField';
import DashboardPanel from '@/Components/DashboardPanel';

export default function UpdatePasswordForm({ className = '' }) {
    const passwordInput = useRef();
    const currentPasswordInput = useRef();

    const { data, setData, errors, put, reset, processing, recentlySuccessful } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const updatePassword = (e) => {
        e.preventDefault();

        put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => reset(),
            onError: (errs) => {
                if (errs.password) {
                    reset('password', 'password_confirmation');
                    passwordInput.current?.focus();
                }
                if (errs.current_password) {
                    reset('current_password');
                    currentPasswordInput.current?.focus();
                }
            },
        });
    };

    return (
        <DashboardPanel
            title="Security"
            icon="fa-lock"
            headerVariant="gradient"
            className={`nyl-detail-panel ${className}`.trim()}
            bodyClassName="p-4"
        >
            <p className="text-muted small mb-4">
                Use a strong, unique password to protect your account and patient data.
            </p>

            <form onSubmit={updatePassword}>
                <div className="row g-4">
                    <FormField label="Current password" error={errors.current_password} className="col-12">
                        <TextInput
                            id="current_password"
                            ref={currentPasswordInput}
                            value={data.current_password}
                            onChange={(e) => setData('current_password', e.target.value)}
                            type="password"
                            className="form-control bg-light border-0 rounded-xl"
                            autoComplete="current-password"
                        />
                    </FormField>

                    <FormField label="New password" error={errors.password}>
                        <TextInput
                            id="password"
                            ref={passwordInput}
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            type="password"
                            className="form-control bg-light border-0 rounded-xl"
                            autoComplete="new-password"
                            placeholder="Minimum 8 characters"
                        />
                    </FormField>

                    <FormField label="Confirm new password" error={errors.password_confirmation}>
                        <TextInput
                            id="password_confirmation"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            type="password"
                            className="form-control bg-light border-0 rounded-xl"
                            autoComplete="new-password"
                        />
                    </FormField>
                </div>

                <div className="d-flex flex-wrap align-items-center gap-3 pt-4 mt-2 border-top">
                    <PrimaryButton disabled={processing} className="rounded-pill px-4 fw-bold">
                        {processing ? 'Updating…' : 'Update password'}
                    </PrimaryButton>
                    {recentlySuccessful && (
                        <span className="text-success extra-small fw-bold">
                            <i className="fas fa-check-circle me-1" />
                            Password updated
                        </span>
                    )}
                </div>
            </form>
        </DashboardPanel>
    );
}
