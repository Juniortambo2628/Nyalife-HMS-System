import React, { useRef, useState } from 'react';
import { useForm } from '@inertiajs/react';
import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import DashboardPanel from '@/Components/DashboardPanel';

export default function DeleteUserForm({ className = '' }) {
    const [confirmingUserDeletion, setConfirmingUserDeletion] = useState(false);
    const passwordInput = useRef();

    const {
        data,
        setData,
        delete: destroy,
        processing,
        reset,
        errors,
        clearErrors,
    } = useForm({
        password: '',
    });

    const deleteUser = (e) => {
        e.preventDefault();

        destroy(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.current?.focus(),
            onFinish: () => reset(),
        });
    };

    const closeModal = () => {
        setConfirmingUserDeletion(false);
        clearErrors();
        reset();
    };

    return (
        <DashboardPanel
            title="Delete account"
            icon="fa-user-times"
            headerVariant="section"
            iconClassName="text-danger"
            className={`nyl-detail-panel border border-danger-subtle ${className}`.trim()}
            bodyClassName="p-4"
        >
            <div className="alert alert-danger border-0 rounded-2xl mb-4">
                <div className="d-flex gap-3">
                    <i className="fas fa-exclamation-triangle text-danger flex-shrink-0 mt-1" />
                    <div>
                        <div className="fw-bold text-danger mb-2">Permanent action</div>
                        <p className="small mb-0">
                            Deleting your account removes your profile, files, and associated data permanently. This
                            cannot be undone.
                        </p>
                    </div>
                </div>
            </div>

            <DangerButton onClick={() => setConfirmingUserDeletion(true)} className="rounded-pill px-4 fw-bold">
                <i className="fas fa-trash-alt me-2" />
                Delete my account
            </DangerButton>

            <Modal show={confirmingUserDeletion} onClose={closeModal}>
                <form onSubmit={deleteUser} className="p-4 p-md-5">
                    <div className="d-flex align-items-center gap-3 mb-4">
                        <div className="avatar-md rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center">
                            <i className="fas fa-exclamation-triangle" />
                        </div>
                        <h2 className="h5 fw-extrabold mb-0">Confirm account deletion</h2>
                    </div>

                    <p className="text-muted small mb-4">
                        Enter your password to permanently delete your Nyalife HMS account and all associated data.
                    </p>

                    <InputLabel
                        htmlFor="password"
                        value="Password"
                        className="small text-muted text-uppercase fw-bold"
                    />
                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        ref={passwordInput}
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        className="form-control bg-light border-0 rounded-xl mt-2"
                        isFocused
                        placeholder="Your current password"
                    />
                    <InputError message={errors.password} className="mt-2" />

                    <div className="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <SecondaryButton onClick={closeModal} type="button" className="rounded-pill">
                            Cancel
                        </SecondaryButton>
                        <DangerButton className="rounded-pill px-4" disabled={processing} type="submit">
                            {processing ? 'Deleting…' : 'Delete permanently'}
                        </DangerButton>
                    </div>
                </form>
            </Modal>
        </DashboardPanel>
    );
}
