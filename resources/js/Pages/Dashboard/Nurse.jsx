import DashboardTable from '@/Components/DashboardTable';
import Modal from '@/Components/Modal';
import StatCard from '@/Components/StatCard';
import QuickActionCard from '@/Components/QuickActionCard';
import { useMemo, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import DashboardSelect from '@/Components/DashboardSelect';
import DashboardHero from '@/Components/DashboardHero';

export default function Nurse({ auth, stats }) {
    const [isEmergencyModalOpen, setIsEmergencyModalOpen] = useState(false);

    const handleCheckIn = (appointment) => {
        if (confirm(`Mark ${appointment.patient.user.first_name} as ARRIVED and proceed to record vitals?`)) {
            router.post(route('appointments.check-in', appointment.appointment_id), {}, {
                onSuccess: () => {
                   router.get(route('consultations.create'), { 
                       patient_id: appointment.patient_id,
                       appointment_id: appointment.appointment_id 
                   });
                }
            });
        }
    };

    const handleEmergencyTriage = (patientId) => {
        if (patientId) {
            router.get(route('consultations.create'), { 
                patient_id: patientId,
                priority: 'emergency',
                is_walk_in: 1
            });
        }
    };

    const columns = useMemo(() => [
        {
            header: 'Time',
            accessorKey: 'appointment_time',
            cell: ({ row }) => <span className="fw-bold text-gray-900">{row.original.appointment_time}</span>
        },
        {
            header: 'Patient',
            accessorKey: 'patient',
            cell: ({ row }) => (
                <div>
                    <span className="fw-bold text-gray-900">{row.original.patient.user.first_name} {row.original.patient.user.last_name}</span>
                    <div className="extra-small text-muted fw-bold text-uppercase opacity-75">ID: PAT-{row.original.patient_id}</div>
                </div>
            )
        },
        {
            header: 'Doctor',
            accessorKey: 'doctor',
            cell: ({ row }) => <span className="text-muted small fw-medium">Dr. {row.original.doctor.user.last_name}</span>
        },
        {
            header: 'Action',
            id: 'actions',
            cell: ({ row }) => (
                <div className="text-end">
                    {row.original.status === 'vitals_recorded' ? (
                        <span className="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-bold extra-small">
                            <i className="fas fa-check-circle me-1"></i>Vitals Taken
                        </span>
                    ) : (
                        <button 
                            onClick={() => handleCheckIn(row.original)}
                            className={`btn btn-sm rounded-pill px-4 fw-bold shadow-sm hover-scale text-white ${row.original.status === 'arrived' ? 'btn-success' : 'btn-info'}`}
                        >
                            {row.original.status === 'arrived' ? 'Record Vitals' : 'Check In'}
                        </button>
                    )}
                </div>
            )
        }
    ], []);

    const statItems = [
        { label: 'Checked-In Today', value: stats.checked_in_patients || 0, icon: 'fa-user-check', color: 'info' },
        { label: 'Triage Queue', value: stats.triage_queue || 0, icon: 'fa-stethoscope', color: 'primary' }
    ];

    const quickActions = [
        { label: 'Register Patient', sub: 'New registry intake', icon: 'fa-user-plus', color: 'primary', url: route('patients.create') },
        { label: 'Emergency Triage', sub: 'Immediate assessment', icon: 'fa-notes-medical', color: 'danger', onClick: () => setIsEmergencyModalOpen(true) },
        { label: 'View Schedule', sub: "Today's appointments", icon: 'fa-calendar-alt', color: 'info', url: route('appointments.index') }
    ];

    return (
        <AuthenticatedLayout 
            header="Nurse Station"
            toolbarActions={
                <div className="d-flex align-items-center gap-2">
                    <button 
                        onClick={() => setIsEmergencyModalOpen(true)}
                        className="btn btn-danger rounded-pill px-4 py-2 fw-bold small shadow-sm animate-pulse-custom"
                    >
                        <i className="fas fa-notes-medical me-1"></i> Emergency Triage
                    </button>
                    <Link href={route('patients.create')} className="btn btn-primary rounded-pill px-4 py-2 fw-bold small shadow-sm">
                        <i className="fas fa-user-plus me-1"></i> Register Walk-in
                    </Link>
                </div>
            }
        >
            <Head title="Nurse Dashboard" />

            <PageHeader 
                title={`Care Hub - ${auth.user.first_name}`}
                breadcrumbs={[{ label: 'Dashboard', active: true }]}
                showBack={false}
            />

            <div className="px-0">
                <DashboardHero 
                    title="Nurse Station Command"
                    subtitle={`Manage triage and patient intake. There are ${stats.triage_queue || 0} patients currently in the triage queue.`}
                    icon="fa-heartbeat"
                />


                <div className="row g-4 mb-4">
                    {statItems.map((s, i) => (
                        <div key={i} className="col-md-6">
                            <StatCard {...s} />
                        </div>
                    ))}
                </div>

                <div className="row g-4">
                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 rounded-2xl mb-4 h-100 bg-white overflow-hidden shadow-hover">
                            <div className="card-header bg-white py-4 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                                <h6 className="mb-0 fw-extrabold text-gray-900"><i className="fas fa-clock text-pink-500 me-2"></i>Daily Triage Queue</h6>
                                <span className="badge rounded-pill bg-light text-muted border px-3 py-1 fw-bold extra-small">Live Updates</span>
                            </div>
                            <div className="card-body p-0">
                                <DashboardTable 
                                    columns={columns}
                                    data={stats.upcoming_appointments || []}
                                    emptyMessage="No pending appointments in queue."
                                />
                            </div>
                        </div>
                    </div>
                    
                    <div className="col-lg-4">
                        <div className="card shadow-sm border-0 rounded-2xl mb-4 bg-white h-100 shadow-hover">
                            <div className="card-header bg-white py-4 px-4 border-bottom-0">
                                <h6 className="mb-0 fw-extrabold text-gray-900"><i className="fas fa-bolt text-warning me-2"></i>Nursing Quick Actions</h6>
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

            <Modal show={isEmergencyModalOpen} onClose={() => setIsEmergencyModalOpen(false)} maxWidth="md">
                <div className="bg-white rounded-2xl shadow-2xl overflow-hidden border-0">
                    <div className="bg-danger text-white p-4 d-flex justify-content-between align-items-center">
                        <h5 className="mb-0 fw-extrabold"><i className="fas fa-exclamation-triangle me-2"></i>Emergency Triage</h5>
                        <button type="button" className="btn-close btn-close-white" onClick={() => setIsEmergencyModalOpen(false)}></button>
                    </div>
                    <div className="p-5">
                        <p className="text-muted small fw-medium mb-4">Select a patient to initiate an immediate emergency consultation. This action will bypass the standard queue.</p>
                        <div className="mb-4">
                            <label className="form-label extra-small fw-extrabold text-gray-400 text-uppercase tracking-widest mb-2">Search Patient Registry</label>
                            <DashboardSelect 
                                asyncUrl="/patients/search"
                                placeholder="Start typing name or ID..."
                                onChange={(val) => handleEmergencyTriage(val)}
                            />
                        </div>
                        <div className="text-end">
                            <button type="button" className="btn btn-light rounded-pill px-4 py-2 fw-bold border text-muted" onClick={() => setIsEmergencyModalOpen(false)}>Cancel Action</button>
                        </div>
                    </div>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
