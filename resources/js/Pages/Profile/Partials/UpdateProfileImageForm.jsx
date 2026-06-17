import React, { useRef, useState } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import DashboardPanel from '@/Components/DashboardPanel';
import UserAvatar from '@/Components/UserAvatar';

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
            reader.onloadend = () => setPreviewUrl(reader.result);
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

    const triggerFileInput = () => fileInputRef.current?.click();

    const clearPreview = () => {
        setPreviewUrl(null);
        setData('image', null);
        if (fileInputRef.current) fileInputRef.current.value = '';
    };

    return (
        <DashboardPanel
            title="Profile photo"
            icon="fa-camera"
            headerVariant="section"
            className={`nyl-detail-panel ${className}`.trim()}
            bodyClassName="p-4"
        >
            <form onSubmit={submit}>
                <div className="d-flex flex-column flex-md-row align-items-center gap-4">
                    <div className="position-relative flex-shrink-0">
                        {previewUrl ? (
                            <div
                                className="avatar-xxl rounded-circle overflow-hidden border border-3 border-white shadow-lg"
                                style={{ width: '120px', height: '120px' }}
                            >
                                <img src={previewUrl} alt="Preview" className="w-100 h-100 object-fit-cover" />
                            </div>
                        ) : (
                            <UserAvatar user={user} size="2xl" className="shadow-lg" />
                        )}
                        {previewUrl && (
                            <button
                                type="button"
                                onClick={clearPreview}
                                className="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 shadow-sm"
                                style={{ width: '28px', height: '28px', padding: 0 }}
                            >
                                <i className="fas fa-times extra-small" />
                            </button>
                        )}
                    </div>

                    <div className="flex-grow-1 text-center text-md-start">
                        <p className="text-muted small mb-3 mb-md-2">
                            Upload a square JPG or PNG, maximum 2 MB. This photo appears across the system.
                        </p>
                        <input
                            type="file"
                            ref={fileInputRef}
                            onChange={handleFileChange}
                            className="d-none"
                            accept="image/*"
                        />
                        <div className="d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                            <button
                                type="button"
                                onClick={triggerFileInput}
                                className="btn btn-outline-primary btn-sm rounded-pill fw-bold"
                            >
                                <i className="fas fa-upload me-2" />
                                Choose photo
                            </button>
                            {previewUrl && (
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="btn btn-primary btn-sm rounded-pill fw-bold"
                                >
                                    {processing ? (
                                        <span className="spinner-border spinner-border-sm me-2" />
                                    ) : (
                                        <i className="fas fa-save me-2" />
                                    )}
                                    Save photo
                                </button>
                            )}
                        </div>
                        <InputError message={errors.image} className="mt-2" />
                        {recentlySuccessful && (
                            <div className="text-success extra-small fw-bold mt-2">
                                <i className="fas fa-check-circle me-1" />
                                Photo updated successfully.
                            </div>
                        )}
                    </div>
                </div>
            </form>
        </DashboardPanel>
    );
}
