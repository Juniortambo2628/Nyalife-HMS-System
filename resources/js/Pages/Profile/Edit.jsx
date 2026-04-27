import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage, Link } from '@inertiajs/react';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdatePersonalInformationForm from './Partials/UpdatePersonalInformationForm';
import UpdateProfessionalProfileForm from './Partials/UpdateProfessionalProfileForm';
import UpdateProfileImageForm from './Partials/UpdateProfileImageForm';
import PageHeader from '@/Components/PageHeader';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { User, Lock, Briefcase, ShieldX } from 'lucide-react';

export default function Edit({ mustVerifyEmail, status, staff }) {
    const { auth } = usePage().props;
    const [activeTab, setActiveTab] = useState('personal');

    const tabs = [
        { id: 'personal', label: 'Personal Info', icon: User, color: 'text-pink-600', activeBg: 'bg-pink-600', activeText: 'text-white', show: true },
        { id: 'professional', label: 'Professional', icon: Briefcase, color: 'text-pink-600', activeBg: 'bg-pink-600', activeText: 'text-white', show: !!staff },
        { id: 'security', label: 'Security', icon: Lock, color: 'text-pink-600', activeBg: 'bg-pink-600', activeText: 'text-white', show: true },
        { id: 'danger', label: 'Danger Zone', icon: ShieldX, color: 'text-red-600', activeBg: 'bg-red-600', activeText: 'text-white', show: true },
    ].filter(tab => tab.show !== false);

    return (
        <AuthenticatedLayout>
            <Head title="Profile Management" />

            <PageHeader 
                title="Account Settings" 
                breadcrumbs={[
                    { label: 'Dashboard', url: route('dashboard') }, 
                    { label: 'Settings', url: '#' }, 
                    { label: 'Profile', active: true }
                ]}
            />

            <div className="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="lg:grid lg:grid-cols-12 lg:gap-8">
                    {/* Sidebar Tabs */}
                    <aside className="lg:col-span-3 mb-8 lg:mb-0">
                        <nav className="space-y-2">
                            {tabs.map((tab) => {
                                const Icon = tab.icon;
                                const isActive = activeTab === tab.id;
                                return (
                                    <button
                                        key={tab.id}
                                        onClick={() => setActiveTab(tab.id)}
                                        className={`w-full flex items-center gap-3 px-4 py-3.5 text-sm font-semibold rounded-2xl transition-all duration-300 ${
                                            isActive 
                                                ? `${tab.activeBg} ${tab.activeText} shadow-lg shadow-pink-200 dark:shadow-none scale-[1.02]`
                                                : 'text-gray-600 dark:text-gray-400 hover:bg-pink-50 dark:hover:bg-gray-800'
                                        }`}
                                    >
                                        <Icon size={20} className={isActive ? 'text-white' : 'text-gray-400'} />
                                        {tab.label}
                                        {isActive && (
                                            <div className="ml-auto w-1.5 h-1.5 rounded-full bg-white animate-pulse"></div>
                                        )}
                                    </button>
                                );
                            })}
                        </nav>
                    </aside>

                    {/* Content Area */}
                    <main className="lg:col-span-9 space-y-6">
                        {activeTab === 'personal' && (
                            <div className="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                                <UpdateProfileImageForm />
                                <UpdatePersonalInformationForm 
                                    mustVerifyEmail={mustVerifyEmail} 
                                    status={status} 
                                />
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
                    </main>
                </div>

                <UnifiedToolbar 
                    actions={
                        <div className="d-flex align-items-center gap-2">
                            <Link href={route('dashboard')} className="btn btn-light rounded-pill px-4 py-2 fw-bold small">
                                <i className="fas fa-home me-1"></i> Dashboard
                            </Link>
                            <button 
                                onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
                                className="btn btn-primary rounded-pill px-4 py-2 fw-bold small"
                            >
                                <i className="fas fa-arrow-up me-1"></i> Back to Top
                            </button>
                        </div>
                    }
                />
            </div>
        </AuthenticatedLayout>
    );
}
