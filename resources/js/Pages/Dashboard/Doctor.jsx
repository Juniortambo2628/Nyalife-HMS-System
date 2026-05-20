import DashboardHero from '@/Components/DashboardHero';
import StatCard from '@/Components/StatCard';
import QuickActionCard from '@/Components/QuickActionCard';
import { useMemo, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import DashboardTable from '@/Components/DashboardTable';

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
            cell: ({ row }) => <span className="fw-bold text-gray-900">{row.original.appointment_time || 'Walk-in'}</span>
        },
        {
            header: 'Patient',
            accessorKey: 'patient',
            cell: ({ row }) => (
                <div>
                    <div className="fw-bold text-gray-900">{row.original.patient.user.first_name} {row.original.patient.user.last_name}</div>
                    <div className="extra-small text-muted fw-bold text-uppercase opacity-75">ID: PAT-{row.original.patient_id}</div>
                </div>
            )
        },
        {
            header: 'Type',
            accessorKey: 'appointment_type',
            cell: ({ row }) => (
                <span className="badge rounded-pill bg-light text-dark border px-3 py-1 fw-bold extra-small text-capitalize">
                    {row.original.appointment_type}
                </span>
            )
        },
        {
            header: 'Action',
            id: 'actions',
            cell: ({ row }) => (
                <div className="text-end">
                    <Link href={route('consultations.create', { appointment_id: row.original.appointment_id, patient_id: row.original.patient_id })} className="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm hover-scale">
                        Start Assessment
                    </Link>
                </div>
            )
        }
    ], []);

    // Columns for Waiting Investigation / Drafts
    const waitingColumns = useMemo(() => [
        {
            header: 'Patient',
            accessorKey: 'patient',
            cell: ({ row }) => (
                <div>
                    <div className="fw-bold text-gray-900">{row.original.patient?.user?.first_name} {row.original.patient?.user?.last_name}</div>
                    <div className="extra-small text-muted fw-bold text-uppercase opacity-75">ID: PAT-{row.original.patient_id}</div>
                </div>
            )
        },
        {
            header: 'Status',
            accessorKey: 'consultation_status',
            cell: ({ row }) => (
                <span className={`badge rounded-pill px-3 py-1 fw-bold extra-small ${row.original.type === 'lab_ready' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'}`}>
                    {row.original.type === 'lab_ready' ? 'Labs Released' : 'In Progress Draft'}
                </span>
            )
        },
        {
            header: 'Action',
            id: 'actions',
            cell: ({ row }) => (
                <div className="text-end">
                    <Link href={route('consultations.edit', row.original.consultation_id)} className="btn btn-sm btn-warning text-dark rounded-pill px-4 fw-bold shadow-sm hover-scale">
                        Resume Assessment
                    </Link>
                </div>
            )
        }
    ], []);

    // Columns for Completed
    const completedColumns = useMemo(() => [
        {
            header: 'Time Completed',
            accessorKey: 'updated_at',
            cell: ({ row }) => <span className="fw-bold text-gray-900">{new Date(row.original.updated_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
        },
        {
            header: 'Patient',
            accessorKey: 'patient',
            cell: ({ row }) => (
                <div>
                    <div className="fw-bold text-gray-900">{row.original.patient?.user?.first_name} {row.original.patient?.user?.last_name}</div>
                    <div className="extra-small text-muted fw-bold text-uppercase opacity-75">ID: PAT-{row.original.patient_id}</div>
                </div>
            )
        },
        {
            header: 'Diagnosis Summary',
            accessorKey: 'diagnosis_summary',
            cell: ({ row }) => <span className="small text-muted">{row.original.diagnosis_summary || 'No summary recorded'}</span>
        },
        {
            header: 'Action',
            id: 'actions',
            cell: ({ row }) => (
                <div className="text-end">
                    <Link href={route('consultations.show', row.original.consultation_id)} className="btn btn-sm btn-light border rounded-pill px-4 fw-bold shadow-sm hover-scale">
                        View Records
                    </Link>
                </div>
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
            header="Clinician Dashboard"
            auth={auth}
            toolbarActions={
                <div className="d-flex align-items-center gap-2">
                    <Link href={route('patients.index')} className="btn btn-primary rounded-pill px-4 py-2 fw-bold small shadow-sm">
                        <i className="fas fa-search me-1"></i> Registry
                    </Link>
                </div>
            }
        >
            <Head title="Doctor Dashboard" />

            <PageHeader 
                title={`Medical Center - Dr. ${auth.user.first_name}`}
                breadcrumbs={[{ label: 'Dashboard', active: true }]}
                showBack={false}
            />

            <div className="px-0">
                <DashboardHero 
                    title="Clinician Command Center"
                    subtitle={`Manage your patients and reviews. You have ${stats.today_appointments?.length || 0} consultations scheduled for today.`}
                    icon="fa-user-md"
                />

                <div className="row g-4 mb-4">
                    {statItems.map((s, i) => (
                        <div key={i} className="col-md-4">
                            <StatCard {...s} />
                        </div>
                    ))}
                </div>

                <div className="row g-4">
                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 rounded-2xl mb-4 bg-white overflow-hidden shadow-hover">
                            <div className="card-header bg-white py-3 px-4 border-bottom-0 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                                <h6 className="mb-0 fw-extrabold text-gray-900">
                                    <i className="fas fa-calendar-alt text-pink-500 me-2"></i>Patient Schedule Tracker
                                </h6>
                                
                                {/* Premium Tabs */}
                                <ul className="nav nav-pills bg-light p-1 rounded-pill" style={{ fontSize: '0.8rem' }}>
                                    <li className="nav-item">
                                        <button 
                                            className={`nav-link rounded-pill px-3 py-1.5 fw-bold ${activeTab === 'start' ? 'active bg-primary text-white shadow-sm' : 'text-muted'}`}
                                            onClick={() => setActiveTab('start')}
                                        >
                                            Start Assessment ({stats.today_appointments?.length || 0})
                                        </button>
                                    </li>
                                    <li className="nav-item">
                                        <button 
                                            className={`nav-link rounded-pill px-3 py-1.5 fw-bold ${activeTab === 'waiting' ? 'active bg-primary text-white shadow-sm' : 'text-muted'}`}
                                            onClick={() => setActiveTab('waiting')}
                                        >
                                            Waiting Investigation ({waitingList.length})
                                        </button>
                                    </li>
                                    <li className="nav-item">
                                        <button 
                                            className={`nav-link rounded-pill px-3 py-1.5 fw-bold ${activeTab === 'completed' ? 'active bg-primary text-white shadow-sm' : 'text-muted'}`}
                                            onClick={() => setActiveTab('completed')}
                                        >
                                            Completed ({stats.completed_today?.length || 0})
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            
                            <div className="card-body p-0">
                                {activeTab === 'start' && (
                                    <DashboardTable 
                                        columns={startColumns}
                                        data={stats.today_appointments || []}
                                        emptyMessage="No appointments scheduled for today."
                                    />
                                )}
                                {activeTab === 'waiting' && (
                                    <DashboardTable 
                                        columns={waitingColumns}
                                        data={waitingList}
                                        emptyMessage="No patients currently waiting on investigations or drafts."
                                    />
                                )}
                                {activeTab === 'completed' && (
                                    <DashboardTable 
                                        columns={completedColumns}
                                        data={stats.completed_today || []}
                                        emptyMessage="No consultations completed today."
                                    />
                                )}
                            </div>
                        </div>
                    </div>
                    
                    <div className="col-lg-4">
                        <div className="card shadow-sm border-0 rounded-2xl mb-4 bg-white h-100 shadow-hover">
                            <div className="card-header bg-white py-4 px-4 border-bottom-0">
                                <h6 className="mb-0 fw-extrabold text-gray-900"><i className="fas fa-bolt text-warning me-2"></i>Quick Clinical Actions</h6>
                            </div>
                            <div className="card-body p-4 pt-0 d-grid gap-3">
                                {quickActions.map((a, i) => (
                                    <QuickActionCard key={i} {...a} />
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
