import React, { useRef, useState } from 'react';
import { useForm } from '@inertiajs/react';
import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import { Trash2, AlertTriangle, ShieldX, XCircle } from 'lucide-react';

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

    const confirmUserDeletion = () => {
        setConfirmingUserDeletion(true);
    };

    const deleteUser = (e) => {
        e.preventDefault();

        destroy(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.current.focus(),
            onFinish: () => reset(),
        });
    };

    const closeModal = () => {
        setConfirmingUserDeletion(false);

        clearErrors();
        reset();
    };

    return (
        <section className={`${className} bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700`}>
            <header className="flex items-center gap-4 mb-10">
                <div className="p-3 bg-red-50 dark:bg-red-900/20 rounded-xl text-red-600 dark:text-red-400 shadow-sm border border-red-100/50">
                    <ShieldX size={24} />
                </div>
                <div>
                    <h2 className="text-2xl font-bold text-gray-900 dark:text-white">Delete Account</h2>
                    <p className="text-sm text-gray-500 dark:text-gray-400">Permanently remove your account and all associated data.</p>
                </div>
            </header>

            <div className="bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/30 rounded-lg p-4 mb-6">
                <div className="flex gap-3">
                    <AlertTriangle className="text-red-500 shrink-0" size={20} />
                    <p className="text-sm text-red-700 dark:text-red-400">
                        Once your account is deleted, all of its resources and data will be permanently deleted. This action is irreversible.
                    </p>
                </div>
            </div>

            <DangerButton onClick={confirmUserDeletion} className="flex items-center gap-2 !py-4 !px-10 !rounded-xl text-lg font-bold">
                <Trash2 size={20} />
                Confirm Deletion
            </DangerButton>

            <Modal show={confirmingUserDeletion} onClose={closeModal}>
                <form onSubmit={deleteUser} className="p-8">
                    <div className="flex items-center gap-4 mb-6">
                        <div className="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center text-red-600 dark:text-red-400">
                            <AlertTriangle size={24} />
                        </div>
                        <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
                            Confirm Persistent Deletion
                        </h2>
                    </div>

                    <p className="text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
                        To confirm this action, please enter your password. This will permanently remove your profile, files, and historical data from Nyalife HMS.
                    </p>

                    <div className="space-y-4">
                        <InputLabel htmlFor="password" value="Master Password" className="text-gray-700 font-bold mb-2 ml-1" />
                        <div className="relative group">
                            <TextInput
                                id="password"
                                type="password"
                                name="password"
                                ref={passwordInput}
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                className="block w-full pl-12 h-14 bg-white border-gray-200 focus:bg-white transition-all text-black rounded-xl"
                                isFocused
                                placeholder="Enter your password to confirm"
                            />
                            <ShieldX className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-500 transition-colors" size={20} />
                        </div>
                        <InputError message={errors.password} className="mt-2" />
                    </div>

                    <div className="mt-8 flex justify-end gap-3">
                        <SecondaryButton onClick={closeModal} className="flex items-center gap-2">
                            <XCircle size={18} />
                            Cancel
                        </SecondaryButton>

                        <DangerButton className="flex items-center gap-2" disabled={processing}>
                            {processing ? (
                                <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                            ) : (
                                <Trash2 size={18} />
                            )}
                            Finalize Deletion
                        </DangerButton>
                    </div>
                </form>
            </Modal>
        </section>
    );
}

