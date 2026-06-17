import StatCardGrid from '@/Components/StatCardGrid';
import QuickActionCard from '@/Components/QuickActionCard';
import DashboardPanel from '@/Components/DashboardPanel';
import PatientTableCell from '@/Components/PatientTableCell';
import RoleDashboardShell from '@/Components/RoleDashboardShell';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { useMemo, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import DashboardTable from '@/Components/DashboardTable';
import TableActions from '@/Components/TableActions';
import StatusBadge from '@/Components/StatusBadge';
import { TableCellPrimary } from '@/Components/TableCells';
import { formatTime } from '@/Utils/dateUtils';

export default function Doctor({ auth, stats }) {
    const [activeTab, setActiveTab] = useState('start'); // 'start', 'waiting', 'completed'

    const statItems = [
        { label: "Today's Visits", value: stats.today_appointments?.length || 0, icon: 'fa-calendar-check', color: 'info' },
        { label: 'Pending Reviews', value: (stats.in_progress_consultations?.length || 0) + (stats.released_labs?.length || 0), icon: 'fa-clock', color: 'warning' },
        { label: 'Completed (Today)', value: stats.completed_today?.length || 0, icon: 'fa-check-double', color: 'success' }
    ];

    const quickActions = [
        { label: 'Patient Registry', sub: 'Search and view records', icon: 'fa-users', color: 'primary', url: route('patients.index') },
        { label: 'Lab Results', sub: 'Check processed reports', icon: 'fa-flask', color: 'success', url: route('lab.results') },
        { label: 'Prescription Logs', sub: 'Previous patient meds', icon: 'fa-prescription', color: 'warning', url: route('prescriptions.index') }
    ];

    // Columns for Start Assessment
    const startColumns = useMemo(() => [
        {
            header: 'Time',
            accessorKey: 'appointment_time',
            cell: ({ row }) => <TableCellPrimary>{row.original.appointment_time || 'Walk-in'}</TableCellPrimary>
        },
        {
            header: 'Patient',
            accessorKey: 'patient',
            cell: ({ row }) => (
                <PatientTableCell patient={row.original.patient} patientId={row.original.patient_id} />
            )
        },
        {
            header: 'Type',
            accessorKey: 'appointment_type',
            cell: ({ row }) => (
                <StatusBadge status={row.original.appointment_type || 'scheduled'} className="text-capitalize" />
            )
        },
        {
            header: 'Action',
            id: 'actions',
            cell: ({ row }) => (
                <TableActions actions={[
                    {
                        icon: 'fa-stethoscope',
                        label: 'Start assessment',
                        href: route('consultations.create', {
                            appointment_id: row.original.appointment_id,
                            patient_id: row.original.patient_id,
                        }),
                    },
                ]} />
            )
        }
    ], []);

    // Columns for Waiting Investigation / Drafts
    const waitingColumns = useMemo(() => [
        {
            header: 'Patient',
            accessorKey: 'patient',
            cell: ({ row }) => (
                <PatientTableCell patient={row.original.patient} patientId={row.original.patient_id} />
            )
        },
        {
            header: 'Status',
            accessorKey: 'consultation_status',
            cell: ({ row }) => (
                <StatusBadge status={row.original.type === 'lab_ready' ? 'verified' : 'in_progress'} />
            )
        },
        {
            header: 'Action',
            id: 'actions',
            cell: ({ row }) => (
                <TableActions actions={[
                    {
                        icon: 'fa-edit',
                        label: 'Resume assessment',
                        href: route('consultations.edit', row.original.consultation_id),
                        color: 'warning',
                    },
                ]} />
            )
        }
    ], []);

    // Columns for Completed
    const completedColumns = useMemo(() => [
        {
            header: 'Time Completed',
            accessorKey: 'updated_at',
            cell: ({ row }) => <span className="fw-bold text-gray-900">{formatTime(row.original.updated_at)}</span>
        },
        {
            header: 'Patient',
            accessorKey: 'patient',
            cell: ({ row }) => (
                <PatientTableCell patient={row.original.patient} patientId={row.original.patient_id} />
            )
        },
        {
            header: 'Diagnosis summary',
            accessorKey: 'diagnosis_summary',
            cell: ({ row }) => (
                <TableCellPrimary className="text-muted">
                    {row.original.diagnosis_summary || 'No summary recorded'}
                </TableCellPrimary>
            )
        },
        {
            header: 'Action',
            id: 'actions',
            cell: ({ row }) => (
                <TableActions actions={[
                    {
                        icon: 'fa-eye',
                        label: 'View records',
                        href: route('consultations.show', row.original.consultation_id),
                    },
                ]} />
            )
        }
    ], []);

    // Prep waiting list: combine in-progress drafts with released labs
    const waitingList = useMemo(() => {
        const drafts = (stats.in_progress_consultations || []).map(c => ({ ...c, type: 'draft' }));
        const labs = (stats.released_labs || []).map(c => ({ ...c, type: 'lab_ready' }));
        return [...drafts, ...labs];
    }, [stats.in_progress_consultations, stats.released_labs]);

    return (
        <AuthenticatedLayout
            headerTitle={`Medical center — Dr. ${auth.user.first_name}`}
            breadcrumbs={[{ label: 'Dashboard', active: true }]}
        >
            <Head title="Doctor Dashboard" />

            <UnifiedToolbar
                viewOptions={[
                    {
                        label: `Start assessment (${stats.today_appointments?.length || 0})`,
                        icon: 'fa-user-md',
                        onClick: () => setActiveTab('start'),
                        color: activeTab === 'start' ? 'pink-500' : 'gray-400',
                    },
                    {
                        label: `Waiting investigation (${waitingList.length})`,
                        icon: 'fa-hourglass-half',
                        onClick: () => setActiveTab('waiting'),
                        color: activeTab === 'waiting' ? 'pink-500' : 'gray-400',
                    },
                    {
                        label: `Completed (${stats.completed_today?.length || 0})`,
                        icon: 'fa-check-double',
                        onClick: () => setActiveTab('completed'),
                        color: activeTab === 'completed' ? 'pink-500' : 'gray-400',
                    },
                ]}
                actions={[
                    {
                        label: 'Patient registry',
                        icon: 'fa-search',
                        href: route('patients.index'),
                    },
                ]}
            />

            <RoleDashboardShell
                hero={{
                    title: 'Clinician command center',
                    subtitle: `Manage your patients and reviews. You have ${stats.today_appointments?.length || 0} consultations scheduled for today.`,
                    icon: 'fa-user-md',
                }}
                statItems={statItems}
                statCols={3}
            >
                <div className="row g-4">
                    <div className="col-lg-8">
                        <DashboardPanel
                            title="Patient schedule tracker"
                            icon="fa-calendar-alt"
                            className="mb-4"
                        >
                            {activeTab === 'start' && (
                                <DashboardTable 
                                    columns={startColumns}
                                    data={stats.today_appointments || []}
                                    emptyMessage="No appointments scheduled for today."
                                    headerBgClassName="bg-pink-500"
                                />
                            )}
                            {activeTab === 'waiting' && (
                                <DashboardTable 
                                    columns={waitingColumns}
                                    data={waitingList}
                                    emptyMessage="No patients currently waiting on investigations or drafts."
                                    headerBgClassName="bg-pink-500"
                                />
                            )}
                            {activeTab === 'completed' && (
                                <DashboardTable 
                                    columns={completedColumns}
                                    data={stats.completed_today || []}
                                    emptyMessage="No consultations completed today."
                                    headerBgClassName="bg-pink-500"
                                />
                            )}
                        </DashboardPanel>
                    </div>

                    <div className="col-lg-4">
                        <DashboardPanel
                            title="Quick clinical actions"
                            icon="fa-bolt"
                            iconClassName="text-warning"
                            className="mb-4 h-100"
                            bodyClassName="p-4 pt-0 d-grid gap-3"
                        >
                            {quickActions.map((a, i) => (
                                <QuickActionCard key={i} {...a} />
                            ))}
                        </DashboardPanel>
                    </div>
                </div>
            </RoleDashboardShell>
        </AuthenticatedLayout>
    );
}
