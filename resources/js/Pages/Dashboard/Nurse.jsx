import DashboardTable from '@/Components/DashboardTable';
import TableActions from '@/Components/TableActions';
import StatusBadge from '@/Components/StatusBadge';
import { TableCellPrimary } from '@/Components/TableCells';
import Modal from '@/Components/Modal';
import QuickActionCard from '@/Components/QuickActionCard';
import DashboardPanel from '@/Components/DashboardPanel';
import PatientTableCell from '@/Components/PatientTableCell';
import RoleDashboardShell from '@/Components/RoleDashboardShell';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { useMemo, useState, useEffect, useRef } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import DashboardSelect from '@/Components/DashboardSelect';

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

    const pollingRef = useRef(null);
    const [liveSince] = useState(() => new Date().toISOString());

    useEffect(() => {
        pollingRef.current = setInterval(() => {
            router.reload({ only: ['stats'], preserveState: true, preserveScroll: true });
        }, 30000);
        return () => clearInterval(pollingRef.current);
    }, []);

    const columns = useMemo(() => [
        {
            header: 'Time',
            accessorKey: 'appointment_time',
            cell: ({ row }) => <TableCellPrimary>{row.original.appointment_time}</TableCellPrimary>
        },
        {
            header: 'Patient',
            accessorKey: 'patient',
            cell: ({ row }) => (
                <PatientTableCell patient={row.original.patient} patientId={row.original.patient_id} />
            )
        },
        {
            header: 'Doctor',
            accessorKey: 'doctor',
            cell: ({ row }) => <TableCellPrimary className="text-muted">Dr. {row.original.doctor.user.last_name}</TableCellPrimary>
        },
        {
            header: 'Action',
            id: 'actions',
            cell: ({ row }) => {
                if (row.original.status === 'vitals_recorded') {
                    return <StatusBadge status="vitals_recorded" />;
                }
                return (
                    <TableActions actions={[
                        {
                            icon: row.original.status === 'arrived' ? 'fa-heartbeat' : 'fa-door-open',
                            label: row.original.status === 'arrived' ? 'Record vitals' : 'Check in',
                            onClick: () => handleCheckIn(row.original),
                            color: row.original.status === 'arrived' ? 'success' : 'info',
                        },
                    ]} />
                );
            },
        }
    ], []);

    const statItems = [
        { label: 'Checked-in today', value: stats.checked_in_patients || 0, icon: 'fa-user-check', color: 'info' },
        { label: 'Triage queue', value: stats.triage_queue || 0, icon: 'fa-stethoscope', color: 'primary' }
    ];

    const quickActions = [
        { label: 'Register patient', sub: 'New registry intake', icon: 'fa-user-plus', color: 'primary', url: route('patients.create') },
        { label: 'Emergency triage', sub: 'Immediate assessment', icon: 'fa-notes-medical', color: 'danger', onClick: () => setIsEmergencyModalOpen(true) },
        { label: 'View schedule', sub: "Today's appointments", icon: 'fa-calendar-alt', color: 'info', url: route('appointments.index') }
    ];

    return (
        <AuthenticatedLayout
            headerTitle={`Care hub — ${auth.user.first_name}`}
            breadcrumbs={[{ label: 'Dashboard', active: true }]}
        >
            <Head title="Nurse Dashboard" />

            <UnifiedToolbar
                actions={[
                    {
                        label: 'Emergency triage',
                        icon: 'fa-notes-medical',
                        onClick: () => setIsEmergencyModalOpen(true),
                        color: 'danger',
                    },
                    {
                        label: 'Register walk-in',
                        icon: 'fa-user-plus',
                        href: route('patients.create'),
                    },
                ]}
            />

            <RoleDashboardShell
                hero={{
                    title: 'Nurse station command',
                    subtitle: `Manage triage and patient intake. There are ${stats.triage_queue || 0} patients currently in the triage queue.`,
                    icon: 'fa-heartbeat',
                }}
                statItems={statItems}
                statCols={2}
            >
                <div className="row g-4">
                    <div className="col-lg-8">
                        <DashboardPanel
                            title="Daily triage queue"
                            icon="fa-clock"
                            className="mb-4 h-100"
                            actions={
                                <span className="badge rounded-pill bg-success text-white border px-3 py-1 fw-bold extra-small d-flex align-items-center gap-1">
                                    <span className="rounded-circle bg-white" style={{ width: 6, height: 6, animation: 'pulse 2s infinite' }}></span>
                                    Live
                                </span>
                            }
                        >
                            <DashboardTable 
                                columns={columns}
                                data={stats.upcoming_appointments || []}
                                emptyMessage="No pending appointments in queue."
                            />
                        </DashboardPanel>
                    </div>

                    <div className="col-lg-4">
                        <DashboardPanel
                            title="Nursing quick actions"
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

            <Modal show={isEmergencyModalOpen} onClose={() => setIsEmergencyModalOpen(false)} maxWidth="md">
                <div className="bg-white rounded-2xl shadow-2xl overflow-hidden border-0">
                    <div className="bg-danger text-white p-4 d-flex justify-content-between align-items-center">
                        <h5 className="mb-0 fw-extrabold"><i className="fas fa-exclamation-triangle me-2"></i>Emergency triage</h5>
                        <button type="button" className="btn-close btn-close-white" onClick={() => setIsEmergencyModalOpen(false)}></button>
                    </div>
                    <div className="p-5">
                        <p className="text-muted small fw-medium mb-4">Select a patient to initiate an immediate emergency consultation. This action will bypass the standard queue.</p>
                        <div className="mb-4">
                            <label className="form-label extra-small fw-bold text-muted mb-2">Search patient registry</label>
                            <DashboardSelect 
                                asyncUrl="/patients/search"
                                placeholder="Start typing name or ID..."
                                onChange={(val) => handleEmergencyTriage(val)}
                            />
                        </div>
                        <div className="text-end">
                            <button type="button" className="btn btn-light rounded-pill px-4 py-2 fw-bold border text-muted" onClick={() => setIsEmergencyModalOpen(false)}>Cancel</button>
                        </div>
                    </div>
                </div>
            </Modal>
            <style>{`@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }`}</style>
        </AuthenticatedLayout>
    );
}
