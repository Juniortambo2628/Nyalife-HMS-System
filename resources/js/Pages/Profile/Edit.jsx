import React, { useMemo, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdatePersonalInformationForm from './Partials/UpdatePersonalInformationForm';
import UpdateProfessionalProfileForm from './Partials/UpdateProfessionalProfileForm';
import UpdateProfileImageForm from './Partials/UpdateProfileImageForm';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import DashboardPanel from '@/Components/DashboardPanel';
import StatCardGrid from '@/Components/StatCardGrid';
import UserAvatar from '@/Components/UserAvatar';
import { formatDateOnly } from '@/Utils/dateUtils';

const formatRole = (role) => (role || 'user').replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());

export default function Edit({ mustVerifyEmail, status, staff }) {
    const { auth } = usePage().props;
    const user = auth.user;
    const [activeTab, setActiveTab] = useState('personal');

    const tabs = [
        { id: 'personal', label: 'Personal', icon: 'fa-user', show: true },
        { id: 'professional', label: 'Professional', icon: 'fa-briefcase', show: !!staff },
        { id: 'security', label: 'Security', icon: 'fa-lock', show: true },
        { id: 'danger', label: 'Danger zone', icon: 'fa-shield-alt', show: true, danger: true },
    ].filter((tab) => tab.show !== false);

    const statItems = useMemo(
        () => [
            {
                label: 'Role',
                value: formatRole(user.role),
                icon: 'fa-id-badge',
                color: 'pink',
            },
            {
                label: 'Email',
                value: user.email_verified_at ? 'Verified' : 'Unverified',
                icon: user.email_verified_at ? 'fa-check-circle' : 'fa-envelope-open',
                color: user.email_verified_at ? 'success' : 'warning',
                sub: user.email,
            },
            {
                label: 'Phone',
                value: user.phone || '—',
                icon: 'fa-phone',
                color: 'teal',
            },
            {
                label: staff ? 'Department' : 'Member since',
                value: staff
                    ? staff.departmentRelation?.department_name || staff.department || '—'
                    : formatDateOnly(user.created_at),
                icon: staff ? 'fa-hospital' : 'fa-calendar-check',
                color: 'info',
            },
        ],
        [user, staff],
    );

    return (
        <AuthenticatedLayout
            headerTitle="Account settings"
            breadcrumbs={[
                { label: 'Dashboard', url: route('dashboard') },
                { label: 'Profile', active: true },
            ]}
        >
            <Head title="Profile" />

            <div className="pb-5">
                <StatCardGrid items={statItems} cols={4} />

                <div className="row g-4">
                    <div className="col-lg-4">
                        <DashboardPanel
                            title="Your profile"
                            icon="fa-user-circle"
                            headerVariant="gradient"
                            className="mb-4 nyl-detail-panel"
                            bodyClassName="p-4"
                        >
                            <div className="text-center mb-4">
                                <UserAvatar user={user} size="2xl" className="mx-auto mb-3 shadow-sm" />
                                <h5 className="fw-extrabold text-gray-900 mb-1">
                                    {user.first_name} {user.last_name}
                                </h5>
                                <p className="text-muted small mb-3">{user.email}</p>
                                <span className="badge bg-pink-50 text-pink-600 rounded-pill px-3 py-2 extra-small fw-bold uppercase">
                                    {formatRole(user.role)}
                                </span>
                                {staff?.specialization && (
                                    <div className="extra-small text-muted fw-bold mt-2">{staff.specialization}</div>
                                )}
                            </div>

                            <div className="nyl-meta-grid mb-4">
                                <div className="nyl-meta-item">
                                    <div className="nyl-meta-item__label">Phone</div>
                                    <div className="nyl-meta-item__value">{user.phone || '—'}</div>
                                </div>
                                <div className="nyl-meta-item">
                                    <div className="nyl-meta-item__label">Gender</div>
                                    <div className="nyl-meta-item__value text-capitalize">{user.gender || '—'}</div>
                                </div>
                                {user.date_of_birth && (
                                    <div className="nyl-meta-item">
                                        <div className="nyl-meta-item__label">Date of birth</div>
                                        <div className="nyl-meta-item__value">{formatDateOnly(user.date_of_birth)}</div>
                                    </div>
                                )}
                                {staff?.license_number && (
                                    <div className="nyl-meta-item">
                                        <div className="nyl-meta-item__label">License</div>
                                        <div className="nyl-meta-item__value">{staff.license_number}</div>
                                    </div>
                                )}
                            </div>

                            <nav className="d-grid gap-2">
                                {tabs.map((tab) => {
                                    const isActive = activeTab === tab.id;
                                    return (
                                        <button
                                            key={tab.id}
                                            type="button"
                                            onClick={() => setActiveTab(tab.id)}
                                            className={`btn rounded-pill fw-bold extra-small text-uppercase tracking-wider d-flex align-items-center gap-2 ${
                                                isActive
                                                    ? tab.danger
                                                        ? 'btn-danger shadow-sm'
                                                        : 'btn-primary shadow-sm'
                                                    : tab.danger
                                                      ? 'btn-outline-danger'
                                                      : 'btn-light text-muted'
                                            }`}
                                        >
                                            <i className={`fas ${tab.icon}`} style={{ width: '16px' }} />
                                            {tab.label}
                                        </button>
                                    );
                                })}
                            </nav>
                        </DashboardPanel>

                        {status === 'profile-updated' && (
                            <div className="alert alert-success border-0 rounded-2xl shadow-sm extra-small fw-bold mb-0">
                                <i className="fas fa-check-circle me-2" />
                                Profile updated successfully.
                            </div>
                        )}
                        {status === 'image-updated' && (
                            <div className="alert alert-success border-0 rounded-2xl shadow-sm extra-small fw-bold mb-0 mt-3">
                                <i className="fas fa-check-circle me-2" />
                                Profile photo updated.
                            </div>
                        )}
                    </div>

                    <div className="col-lg-8">
                        {activeTab === 'personal' && (
                            <div className="animate-in fade-in slide-in-from-bottom-4 duration-500">
                                <UpdateProfileImageForm className="mb-4" />
                                <UpdatePersonalInformationForm mustVerifyEmail={mustVerifyEmail} status={status} />
                            </div>
                        )}

                        {activeTab === 'professional' && staff && (
                            <div className="animate-in fade-in slide-in-from-bottom-4 duration-500">
                                <UpdateProfessionalProfileForm />
                            </div>
                        )}

                        {activeTab === 'security' && (
                            <div className="animate-in fade-in slide-in-from-bottom-4 duration-500">
                                <UpdatePasswordForm />
                            </div>
                        )}

                        {activeTab === 'danger' && (
                            <div className="animate-in fade-in slide-in-from-bottom-4 duration-500">
                                <DeleteUserForm />
                            </div>
                        )}
                    </div>
                </div>
            </div>

            <UnifiedToolbar
                actions={[
                    {
                        label: 'Dashboard',
                        icon: 'fa-home',
                        href: route('dashboard'),
                        color: 'gray',
                    },
                    {
                        label: 'Back to top',
                        icon: 'fa-arrow-up',
                        onClick: () => window.scrollTo({ top: 0, behavior: 'smooth' }),
                    },
                ]}
            />
        </AuthenticatedLayout>
    );
}
