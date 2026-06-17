import React from 'react';
import { useForm, usePage, Link } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import FormField from '@/Components/FormField';
import DashboardPanel from '@/Components/DashboardPanel';

export default function UpdatePersonalInformationForm({ mustVerifyEmail, status, className = '' }) {
    const user = usePage().props.auth.user;

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
        first_name: user.first_name || '',
        last_name: user.last_name || '',
        email: user.email || '',
        phone: user.phone || '',
        gender: user.gender || '',
        date_of_birth: user.date_of_birth || '',
        address: user.address || '',
    });

    const submit = (e) => {
        e.preventDefault();
        patch(route('profile.update'), { preserveScroll: true });
    };

    return (
        <DashboardPanel
            title="Personal details"
            icon="fa-user"
            headerVariant="gradient"
            className={`nyl-detail-panel ${className}`.trim()}
            bodyClassName="p-4"
        >
            <p className="text-muted small mb-4">
                Keep your personal information up to date for accurate clinical records and communications.
            </p>

            {mustVerifyEmail && user.email_verified_at === null && (
                <div className="nyl-content-box nyl-content-box--muted mb-4">
                    <div className="nyl-content-box__title mb-2">Email verification required</div>
                    <p className="small text-muted mb-2">
                        Your email address is not verified. Check your inbox or request a new link.
                    </p>
                    <Link
                        href={route('verification.send')}
                        method="post"
                        as="button"
                        className="btn btn-sm btn-outline-primary rounded-pill fw-bold"
                    >
                        Resend verification email
                    </Link>
                    {status === 'verification-link-sent' && (
                        <p className="text-success extra-small fw-bold mt-2 mb-0">
                            A new verification link has been sent.
                        </p>
                    )}
                </div>
            )}

            <form onSubmit={submit}>
                <div className="row g-4">
                    <FormField label="First name" error={errors.first_name} required>
                        <TextInput
                            id="first_name"
                            className="form-control bg-light border-0 rounded-xl"
                            value={data.first_name}
                            onChange={(e) => setData('first_name', e.target.value)}
                            required
                        />
                    </FormField>

                    <FormField label="Last name" error={errors.last_name} required>
                        <TextInput
                            id="last_name"
                            className="form-control bg-light border-0 rounded-xl"
                            value={data.last_name}
                            onChange={(e) => setData('last_name', e.target.value)}
                            required
                        />
                    </FormField>

                    <FormField label="Email address" error={errors.email} required>
                        <TextInput
                            id="email"
                            type="email"
                            className="form-control bg-light border-0 rounded-xl"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            required
                        />
                    </FormField>

                    <FormField label="Phone number" error={errors.phone}>
                        <TextInput
                            id="phone"
                            className="form-control bg-light border-0 rounded-xl"
                            value={data.phone}
                            onChange={(e) => setData('phone', e.target.value)}
                            placeholder="+254 …"
                        />
                    </FormField>

                    <FormField label="Gender" error={errors.gender}>
                        <select
                            id="gender"
                            className="form-select bg-light border-0 rounded-xl"
                            value={data.gender}
                            onChange={(e) => setData('gender', e.target.value)}
                        >
                            <option value="">Select gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </FormField>

                    <FormField label="Date of birth" error={errors.date_of_birth}>
                        <TextInput
                            id="date_of_birth"
                            type="date"
                            className="form-control bg-light border-0 rounded-xl"
                            value={data.date_of_birth}
                            onChange={(e) => setData('date_of_birth', e.target.value)}
                        />
                    </FormField>

                    <FormField label="Home address" error={errors.address} className="col-12">
                        <textarea
                            id="address"
                            className="form-control bg-light border-0 rounded-xl"
                            rows="3"
                            value={data.address}
                            onChange={(e) => setData('address', e.target.value)}
                            placeholder="Street, city, region"
                        />
                    </FormField>
                </div>

                <div className="d-flex flex-wrap align-items-center gap-3 pt-4 mt-2 border-top">
                    <PrimaryButton disabled={processing} className="rounded-pill px-4 fw-bold">
                        {processing ? 'Saving…' : 'Save changes'}
                    </PrimaryButton>
                    {recentlySuccessful && (
                        <span className="text-success extra-small fw-bold">
                            <i className="fas fa-check-circle me-1" />
                            Saved successfully
                        </span>
                    )}
                </div>
            </form>
        </DashboardPanel>
    );
}
