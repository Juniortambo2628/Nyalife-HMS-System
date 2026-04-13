import DashboardTable from '@/Components/DashboardTable';
import { useMemo, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { formatDateTime } from '@/Utils/dateUtils';
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

    const columns = useMemo(() => [
        {
            header: 'Time',
            accessorKey: 'appointment_time',
            cell: ({ row }) => <span className="fw-bold">{row.original.appointment_time}</span>
        },
        {
            header: 'Patient',
            accessorKey: 'patient',
            cell: ({ row }) => (
                <div>
                    <span className="fw-semibold">{row.original.patient.user.first_name} {row.original.patient.user.last_name}</span>
                    <div className="extra-small text-muted">ID: PAT-{row.original.patient_id}</div>
                </div>
            )
        },
        {
            header: 'Doctor',
            accessorKey: 'doctor',
            cell: ({ row }) => <span className="text-muted small">Dr. {row.original.doctor.user.last_name}</span>
        },
        {
            header: 'Action',
            id: 'actions',
            cell: ({ row }) => (
                <div className="text-end pe-2">
                    <button 
                        onClick={() => handleCheckIn(row.original)}
                        className={`btn btn-sm rounded-pill px-3 shadow-sm transition-all hover-scale text-white ${row.original.status === 'arrived' ? 'btn-success' : 'btn-info'}`}
                    >
                        {row.original.status === 'arrived' ? 'Record Vitals' : 'Check In'}
                    </button>
                </div>
            )
        }
    ], []);

    return (
        <AuthenticatedLayout
            user={auth.user}
        >
            <Head title="Nurse Dashboard" />

            <PageHeader 
                title={`Nurse Station - ${auth.user.first_name}`}
                breadcrumbs={[{ label: 'Dashboard', active: true }]}
                showBack={false}
            />

            <div className="container-fluid dashboard-page px-0 h-auto">
                <div className="row g-4 mb-8 h-auto">
                    <div className="col-md-6">
                        <div className="card shadow-sm border-0 h-100 p-4 border-start border-4 border-info">
                            <div className="d-flex justify-content-between align-items-center">
                                <div>
                                    <div className="text-muted small fw-bold text-uppercase">Checked-In Today</div>
                                    <h2 className="fw-bold mb-0">{stats.checked_in_patients || 0}</h2>
                                </div>
                                <div className="bg-info-subtle p-3 rounded text-info">
                                    <i className="fas fa-user-check fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-6">
                        <div className="card shadow-sm border-0 h-100 p-4 border-start border-4 border-primary">
                            <div className="d-flex justify-content-between align-items-center">
                                <div>
                                    <div className="text-muted small fw-bold text-uppercase">Triage Queue</div>
                                    <h2 className="fw-bold mb-0">{stats.triage_queue || 0}</h2>
                                </div>
                                <div className="bg-primary-subtle p-3 rounded text-primary">
                                    <i className="fas fa-heartbeat fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="row">
                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 mb-4 h-100">
                            <div className="card-header bg-white py-3">
                                <h6 className="mb-0 fw-bold">Daily Appointments & Triage</h6>
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
                        <div className="card shadow-sm border-0 mb-4 bg-white overflow-hidden h-100">
                            <div className="card-header bg-white py-3 border-0">
                                <h6 className="mb-0 fw-bold">Nursing Quick Actions</h6>
                            </div>
                            <div className="card-body p-4 pt-0">
                                <div className="d-grid gap-3">
                                    <Link href={route('patients.create')} className="btn btn-light border text-start p-3 d-flex align-items-center">
                                        <div className="bg-primary-subtle text-primary p-2 rounded me-3">
                                            <i className="fas fa-user-plus"></i>
                                        </div>
                                        <div>
                                            <div className="fw-bold small">New Registration</div>
                                            <div className="text-muted extra-small">Register walk-in patient</div>
                                        </div>
                                    </Link>
                                    <button 
                                        onClick={() => setIsEmergencyModalOpen(true)}
                                        className="btn btn-light border text-start p-3 d-flex align-items-center"
                                    >
                                        <div className="bg-danger-subtle text-danger p-2 rounded me-3">
                                            <i className="fas fa-notes-medical"></i>
                                        </div>
                                        <div>
                                            <div className="fw-bold small">Emergency Triage</div>
                                            <div className="text-muted extra-small">Immediate assessment</div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Emergency Triage Selection Modal */}
            {isEmergencyModalOpen && (
                <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 rounded-4 shadow">
                            <div className="modal-header bg-danger text-white border-0 py-3">
                                <h5 className="modal-title fw-bold">Emergency Patient Selection</h5>
                                <button type="button" className="btn-close btn-close-white" onClick={() => setIsEmergencyModalOpen(false)}></button>
                            </div>
                            <div className="modal-body p-4">
                                <p className="text-muted small mb-4">Select a patient to initiate an immediate emergency consultation. This will bypass the standard queue.</p>
                                <div className="mb-3">
                                    <label className="form-label small fw-bold">Search Patient</label>
                                    <DashboardSelect 
                                        asyncUrl="/patients/search"
                                        placeholder="Start typing patient name..."
                                        onChange={(val) => handleEmergencyTriage(val)}
                                    />
                                </div>
                            </div>
                            <div className="modal-footer border-0">
                                <button type="button" className="btn btn-light rounded-pill px-4" onClick={() => setIsEmergencyModalOpen(false)}>Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            <style>{`
                .extra-small {
                    font-size: 0.75rem;
                }
            `}</style>
        </AuthenticatedLayout>
    );
}
