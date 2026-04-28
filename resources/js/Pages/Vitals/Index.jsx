import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Index({ appointments }) {
    return (
        <AuthenticatedLayout header="Vital Signs History">
            <Head title="Vitals & Triage" />

            <PageHeader 
                title="Daily Triage Queue"
                breadcrumbs={[{ label: 'Clinical', active: false }, { label: 'Vitals', active: true }]}
            />

            <UnifiedToolbar 
                actions={[
                    { 
                        label: 'AD HOC VITALS', 
                        icon: 'fa-plus', 
                        href: route('vitals.create') 
                    }
                ]}
            />

            <div className="card shadow-sm border-0 rounded-2xl bg-white overflow-hidden">
                <div className="card-header bg-white border-bottom-0 py-4 px-4">
                    <h5 className="fw-bold mb-0 text-gray-800">Today's Patient Queue</h5>
                    <p className="text-muted small mb-0 mt-1">Listing all scheduled and arrived appointments for {new Date().toLocaleDateString()}</p>
                </div>
                <div className="table-responsive">
                    <table className="table table-hover align-middle mb-0">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-5 py-3 extra-small fw-extrabold text-muted text-uppercase tracking-widest border-0">Timestamp</th>
                                <th className="px-5 py-3 extra-small fw-extrabold text-muted text-uppercase tracking-widest border-0">Subject Identity</th>
                                <th className="px-5 py-3 extra-small fw-extrabold text-muted text-uppercase tracking-widest border-0">Monitoring State</th>
                                <th className="px-5 py-3 text-end extra-small fw-extrabold text-muted text-uppercase tracking-widest border-0">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {appointments && appointments.length > 0 ? appointments.map((apt) => (
                                <tr key={apt.appointment_id}>
                                    <td className="px-4 py-3 fw-bold text-gray-700">{apt.appointment_time || 'Walk-in'}</td>
                                    <td className="px-4 py-3">
                                        <div className="fw-bold text-gray-900">{apt.patient?.user?.first_name} {apt.patient?.user?.last_name}</div>
                                        <div className="text-muted small text-uppercase tracking-wider">ID: PAT-{apt.patient_id}</div>
                                    </td>
                                    <td className="px-4 py-3">
                                        {/* Simplified status check - assumes if it's past arrived/scheduled, it's triaged */}
                                        {['scheduled', 'arrived'].includes(apt.status) ? (
                                            <span className="badge bg-warning text-dark px-3 py-1 rounded-pill uppercase tracking-wider text-xs">Pending Triage</span>
                                        ) : (
                                            <span className="badge bg-success px-3 py-1 rounded-pill uppercase tracking-wider text-xs border border-success">Completed</span>
                                        )}
                                    </td>
                                    <td className="px-4 py-3 text-end">
                                        {['scheduled', 'arrived'].includes(apt.status) && (
                                            <Link href={route('consultations.create', { appointment_id: apt.appointment_id, patient_id: apt.patient_id })} className="btn btn-sm btn-outline-primary rounded-pill px-3 font-bold">
                                                <i className="fas fa-stethoscope me-2"></i>Record Vitals
                                            </Link>
                                        )}
                                    </td>
                                </tr>
                            )) : (
                                <tr>
                                    <td colSpan="4" className="text-center py-5">
                                        <i className="fas fa-calendar-check text-muted mb-3" style={{ fontSize: '2rem' }}></i>
                                        <h6 className="text-muted fw-bold">No appointments found for today.</h6>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

        </AuthenticatedLayout>
    );
}
