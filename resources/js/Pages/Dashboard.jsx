import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import DashboardHero from '@/Components/DashboardHero';
import QuickActionCard from '@/Components/QuickActionCard';
import RoleDashboardShell from '@/Components/RoleDashboardShell';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Dashboard({ auth }) {
    const user = auth?.user || {};
    const roleLabel = user.role?.replace(/_/g, ' ').toUpperCase() || 'GENERAL';

    const quickActions = [
        {
            label: 'Appointments',
            sub: 'View and manage upcoming visits',
            icon: 'fa-calendar-check',
            color: 'info',
            url: route('appointments.index'),
        },
        ...(user.role !== 'receptionist'
            ? [
                  {
                      label: 'Prescriptions',
                      sub: 'Medications and therapeutic plans',
                      icon: 'fa-file-prescription',
                      color: 'primary',
                      url: route('prescriptions.index'),
                  },
                  {
                      label: 'Lab results',
                      sub: 'Diagnostic tests and reports',
                      icon: 'fa-flask',
                      color: 'success',
                      url: route('lab.results'),
                  },
              ]
            : [
                  {
                      label: 'Patient records',
                      sub: 'Register and manage demographics',
                      icon: 'fa-users',
                      color: 'primary',
                      url: route('patients.index'),
                  },
              ]),
        {
            label: 'Patient registry',
            sub: 'Search and open patient records',
            icon: 'fa-user-injured',
            color: 'pink',
            url: route('patients.index'),
        },
    ];

    return (
        <AuthenticatedLayout
            headerTitle={`Welcome back, ${user.first_name || 'User'}!`}
            breadcrumbs={[{ label: 'Dashboard', active: true }]}
        >
            <Head title="Dashboard" />

            <UnifiedToolbar
                actions={[
                    {
                        label: 'View schedule',
                        icon: 'fa-calendar-alt',
                        href: route('appointments.index'),
                    },
                    {
                        label: 'Patient registry',
                        icon: 'fa-users',
                        href: route('patients.index'),
                        color: 'pink',
                    },
                ]}
            />

            <RoleDashboardShell
                hero={{
                    title: `Hello, ${user.first_name}!`,
                    subtitle: `Welcome to the Nyalife HMS command center. Access level: ${roleLabel}`,
                    icon: 'fa-hospital',
                }}
            >
                <div className="card shadow-sm border-0 rounded-2xl bg-white p-4 p-md-5 shadow-hover">
                    <div className="d-flex align-items-center gap-3 mb-4">
                        <div className="avatar-lg bg-primary-subtle text-primary rounded-2xl d-flex align-items-center justify-content-center flex-shrink-0">
                            <i className="fas fa-hand-sparkles fa-2x" />
                        </div>
                        <div>
                            <h2 className="fw-extrabold text-gray-900 mb-1">Quick access</h2>
                            <p className="text-muted mb-0 small">Jump to the modules you use most.</p>
                        </div>
                    </div>

                    <div className="d-grid gap-2">
                        {quickActions.map((action) => (
                            <QuickActionCard key={action.label} {...action} />
                        ))}
                    </div>
                </div>
            </RoleDashboardShell>
        </AuthenticatedLayout>
    );
}
