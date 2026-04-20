import React, { useRef, useState } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import { Transition } from '@headlessui/react';
import PrimaryButton from '@/Components/PrimaryButton';
import InputError from '@/Components/InputError';
import { Camera, Upload, User as UserIcon, X } from 'lucide-react';

export default function UpdateProfileImageForm({ className = '' }) {
    const user = usePage().props.auth.user;
    const fileInputRef = useRef();
    const [previewUrl, setPreviewUrl] = useState(null);

    const { data, setData, post, processing, errors, recentlySuccessful, reset } = useForm({
        image: null,
    });

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setData('image', file);
            const reader = new FileReader();
            reader.onloadend = () => {
                setPreviewUrl(reader.result);
            };
            reader.readAsDataURL(file);
        }
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('profile.image.update'), {
            forceFormData: true,
            onSuccess: () => {
                setPreviewUrl(null);
                reset();
            },
        });
    };

    const triggerFileInput = () => {
        fileInputRef.current.click();
    };

    const clearPreview = () => {
        setPreviewUrl(null);
        setData('image', null);
        if (fileInputRef.current) fileInputRef.current.value = '';
    };

    return (
        <section className={`${className} bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 transition-all duration-300 hover:shadow-md`}>
            <header className="flex items-center gap-4 mb-10">
                <div className="p-3 bg-pink-50 dark:bg-pink-900/20 rounded-xl text-pink-600 dark:text-pink-400 shadow-sm border border-pink-100/50">
                    <Camera size={24} />
                </div>
                <div>
                    <h2 className="text-2xl font-bold text-gray-900 dark:text-white">Profile Photo</h2>
                    <p className="text-sm text-gray-500 dark:text-gray-400">Manage your profile visibility and identity.</p>
                </div>
            </header>

            <form onSubmit={submit} className="flex flex-col items-center gap-8">
                <div className="relative group">
                    <div className="w-40 h-40 rounded-full overflow-hidden border-4 border-white dark:border-gray-700 shadow-xl relative group-hover:border-pink-100 transition-all duration-300">
                        {previewUrl ? (
                            <img src={previewUrl} alt="Preview" className="w-full h-full object-cover scale-110 group-hover:scale-100 transition-transform duration-500" />
                        ) : user.profile_image ? (
                            <img src={`/storage/${user.profile_image}`} alt={user.first_name} className="w-full h-full object-cover" />
                        ) : (
                            <div className="w-full h-full bg-pink-50 dark:bg-gray-700 flex items-center justify-center text-pink-200 dark:text-gray-500">
                                <UserIcon size={64} />
                            </div>
                        )}
                        
                        <div 
                            onClick={triggerFileInput}
                            className="absolute inset-0 bg-pink-600/40 opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col items-center justify-center cursor-pointer backdrop-blur-[2px]"
                        >
                            <Camera className="text-white mb-2" size={32} />
                            <span className="text-white text-xs font-bold uppercase tracking-tighter">Change Photo</span>
                        </div>
                    </div>

                    {previewUrl && (
                        <button
                            type="button"
                            onClick={clearPreview}
                            className="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full shadow-md hover:bg-red-600 transition-colors"
                        >
                            <X size={16} />
                        </button>
                    )}
                </div>

                <input
                    type="file"
                    ref={fileInputRef}
                    onChange={handleFileChange}
                    className="hidden"
                    accept="image/*"
                />

                <div className="text-center">
                    <p className="text-xs text-gray-500 dark:text-gray-400 mb-4 uppercase tracking-wider font-semibold">
                        Recommended: Square JPG or PNG, max 2MB
                    </p>
                    
                    <div className="flex items-center justify-center gap-4">
                        <button
                            type="button"
                            onClick={triggerFileInput}
                            className="px-6 py-3 text-sm font-bold text-pink-600 dark:text-pink-400 bg-pink-50 dark:bg-pink-900/20 border border-pink-100 dark:border-pink-800 rounded-xl hover:bg-pink-100 dark:hover:bg-pink-900/40 transition-all flex items-center gap-2 shadow-sm"
                        >
                            <Upload size={18} />
                            Choose New File
                        </button>

                        {previewUrl && (
                            <PrimaryButton disabled={processing} className="relative !py-2.5 !px-6 overflow-hidden">
                                <span className={processing ? 'opacity-0' : 'opacity-100'}>Update Avatar</span>
                                {processing && (
                                    <div className="absolute inset-0 flex items-center justify-center">
                                        <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                                    </div>
                                )}
                            </PrimaryButton>
                        )}
                    </div>
                </div>

                <InputError message={errors.image} className="mt-2" />

                <Transition
                    show={recentlySuccessful}
                    enter="transition ease-in-out duration-300"
                    enterFrom="opacity-0 translate-y-1"
                    enterTo="opacity-100 translate-y-0"
                    leave="transition ease-in-out duration-300"
                    leaveFrom="opacity-100 translate-y-0"
                    leaveTo="opacity-0 translate-y-1"
                >
                    <div className="p-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 rounded-lg flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
                        <div className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <p className="text-sm font-medium">Avatar updated successfully!</p>
                    </div>
                </Transition>
            </form>
        </section>
    );
}

