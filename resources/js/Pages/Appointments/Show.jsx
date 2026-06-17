import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useMemo } from 'react';
import UserAvatar from '@/Components/UserAvatar';
import StatusBadge from '@/Components/StatusBadge';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import DashboardPanel from '@/Components/DashboardPanel';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import StatCardGrid from '@/Components/StatCardGrid';
import TableActions from '@/Components/TableActions';
import PriorityBadge from '@/Components/PriorityBadge';
import { PatientIdLabel } from '@/Components/PatientTableCell';
import { RefBadge, TableCellPrimary, TableCellStack } from '@/Components/TableCells';
import { formatDateTime } from '@/Utils/dateUtils';

const formatLabel = (value) =>
    (value || '')
        .toString()
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (c) => c.toUpperCase()) || '—';

const formatTime = (value) => {
    if (!value) return '—';
    const str = value.toString();
    return str.length >= 5 ? str.slice(0, 5) : str;
};

export default function Show({ appointment, auth }) {
    const isReceptionist = auth.user.role === 'receptionist';
    const canViewClinical = !isReceptionist;

    const patient = appointment.patient;
    const doctor = appointment.doctor;
    const consultations = appointment.consultations || [];
    const labRequests = appointment.lab_test_requests || [];
    const prescriptions = appointment.prescriptions || [];

    const prescriptionRows = useMemo(() =>
        prescriptions.flatMap((rx) =>
            (rx.items || []).map((item) => ({
                ...item,
                prescription_id: rx.prescription_id,
                prescription_status: rx.status,
                prescription_date: rx.prescription_date,
            }))
        ),
    [prescriptions]);

    const updateStatus = (status) => {
        if (confirm(`Change visit status to "${formatLabel(status)}"?`)) {
            router.patch(route('appointments.update', appointment.appointment_id), { status }, {
                preserveScroll: true,
            });
        }
    };

    const checkIn = () => {
        if (confirm('Confirm patient arrival for this visit?')) {
            router.post(route('appointments.check-in', appointment.appointment_id), {}, {
                preserveScroll: true,
            });
        }
    };

    const statItems = [
        {
            label: 'Visit date',
            value: appointment.appointment_date || '—',
            icon: 'fa-calendar-day',
            color: 'pink',
        },
        {
            label: 'Scheduled time',
            value: appointment.end_time
                ? `${formatTime(appointment.appointment_time)} – ${formatTime(appointment.end_time)}`
                : formatTime(appointment.appointment_time),
            icon: 'fa-clock',
            color: 'teal',
        },
        {
            label: 'Visit type',
            value: formatLabel(appointment.appointment_type),
            icon: 'fa-tag',
            color: 'info',
        },
        {
            label: 'Visit status',
            value: formatLabel(appointment.status),
            icon: 'fa-info-circle',
            color: appointment.status === 'completed' ? 'success' : appointment.status === 'cancelled' ? 'danger' : 'warning',
            sub: `${consultations.length} consultation${consultations.length === 1 ? '' : 's'}`,
        },
    ];

    const consultationColumns = useMemo(() => [
        {
            header: 'Record',
            accessorKey: 'consultation_id',
            cell: ({ row }) => <RefBadge variant="info">CON-{row.original.consultation_id}</RefBadge>,
        },
        {
            header: 'Date',
            accessorKey: 'consultation_date',
            cell: ({ row }) => (
                <TableCellPrimary>{formatDateTime(row.original.consultation_date)}</TableCellPrimary>
            ),
        },
        {
            header: 'Diagnosis',
            accessorKey: 'diagnosis',
            cell: ({ row }) => (
                <TableCellStack
                    primary={row.original.diagnosis || 'General assessment'}
                    secondary={row.original.chief_complaint}
                />
            ),
        },
        {
            header: 'Status',
            accessorKey: 'consultation_status',
            cell: ({ row }) => <StatusBadge status={row.original.consultation_status || 'in_progress'} />,
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => (
                <TableActions actions={[
                    { icon: 'fa-eye', label: 'View consultation', href: route('consultations.show', row.original.consultation_id) },
                    { icon: 'fa-edit', label: 'Edit record', href: route('consultations.edit', row.original.consultation_id) },
                ]} />
            ),
        },
    ], []);

    const labColumns = useMemo(() => [
        {
            header: 'Request',
            accessorKey: 'request_id',
            cell: ({ row }) => <RefBadge variant="info">LAB-{row.original.request_id}</RefBadge>,
        },
        {
            header: 'Test',
            id: 'test',
            cell: ({ row }) => (
                <TableCellStack
                    primary={row.original.test_type?.test_name || 'Lab test'}
                    secondary={row.original.test_type?.category}
                />
            ),
        },
        {
            header: 'Priority',
            accessorKey: 'priority',
            cell: ({ row }) => <PriorityBadge priority={row.original.priority || 'normal'} />,
        },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: ({ row }) => <StatusBadge status={row.original.status} />,
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => (
                <TableActions actions={[
                    { icon: 'fa-eye', label: 'View request', href: route('lab.show', row.original.request_id) },
                ]} />
            ),
        },
    ], []);

    const prescriptionColumns = useMemo(() => [
        {
            header: 'Prescription',
            accessorKey: 'prescription_id',
            cell: ({ row }) => <RefBadge variant="info">RX-{row.original.prescription_id}</RefBadge>,
        },
        {
            header: 'Medication',
            id: 'medication',
            cell: ({ row }) => (
                <TableCellStack
                    primary={row.original.medication?.medication_name || row.original.medicine_name || 'Medication'}
                    secondary={[row.original.medication?.strength, row.original.dosage].filter(Boolean).join(' · ')}
                />
            ),
        },
        {
            header: 'Regimen',
            id: 'regimen',
            cell: ({ row }) => (
                <TableCellPrimary className="text-muted small">
                    {[row.original.frequency, row.original.duration].filter(Boolean).join(' · ') || '—'}
                </TableCellPrimary>
            ),
        },
        {
            header: 'Status',
            accessorKey: 'prescription_status',
            cell: ({ row }) => <StatusBadge status={row.original.prescription_status || 'pending'} />,
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => (
                <TableActions actions={[
                    { icon: 'fa-eye', label: 'View prescription', onClick: () => router.visit(route('prescriptions.show', row.original.prescription_id)) },
                ]} />
            ),
        },
    ], []);

    return (
        <AuthenticatedLayout
            headerTitle="Visit record"
            breadcrumbs={[
                { label: 'Appointments', url: route('appointments.index') },
                { label: `APT-${appointment.appointment_id}`, active: true },
            ]}
        >
            <Head title={`Appointment APT-${appointment.appointment_id}`} />

            <div className="pb-5">
                <StatCardGrid items={statItems} cols={4} />

                <div className="row g-4">
                    {/* Patient & provider sidebar */}
                    <div className="col-lg-4">
                        <DashboardPanel
                            title="Patient"
                            icon="fa-user-injured"
                            headerVariant="gradient"
                            className="mb-4 nyl-detail-panel"
                            bodyClassName="p-4"
                        >
                            <div className="text-center mb-4">
                                <UserAvatar user={patient?.user} size="xl" className="mb-3 shadow-sm" />
                                <h5 className="fw-extrabold text-gray-900 mb-1">
                                    {patient?.user?.first_name} {patient?.user?.last_name}
                                </h5>
                                <PatientIdLabel
                                    id={appointment.patient_id}
                                    variant="pat-id"
                                    className="extra-small font-bold text-muted tracking-widest uppercase mb-3 d-block"
                                />
                                <StatusBadge status={appointment.status} />
                            </div>

                            <div className="nyl-meta-grid mb-4">
                                <div className="nyl-meta-item">
                                    <div className="nyl-meta-item__label">Phone</div>
                                    <div className="nyl-meta-item__value">{patient?.user?.phone || '—'}</div>
                                </div>
                                <div className="nyl-meta-item">
                                    <div className="nyl-meta-item__label">Email</div>
                                    <div className="nyl-meta-item__value text-truncate">{patient?.user?.email || '—'}</div>
                                </div>
                            </div>

                            {(patient?.blood_group || patient?.gender) && (
                                <div className="d-flex flex-wrap gap-2 mb-4">
                                    {patient?.gender && (
                                        <span className="badge bg-pink-50 text-pink-600 rounded-pill px-3 py-2 extra-small fw-bold uppercase">
                                            {patient.gender}
                                        </span>
                                    )}
                                    {patient?.blood_group && (
                                        <span className="badge bg-danger-subtle text-danger rounded-pill px-3 py-2 extra-small fw-bold">
                                            {patient.blood_group}
                                        </span>
                                    )}
                                </div>
                            )}

                            <Link
                                href={route('patients.show', appointment.patient_id)}
                                className="btn btn-outline-primary btn-sm w-100 rounded-pill fw-bold"
                            >
                                View patient profile
                            </Link>
                        </DashboardPanel>

                        <DashboardPanel
                            title="Assigned provider"
                            icon="fa-user-md"
                            headerVariant="section"
                            className="nyl-detail-panel"
                            bodyClassName="p-4"
                        >
                            <div className="nyl-detail-meta-row">
                                <span className="extra-small fw-bold text-muted text-uppercase">Physician</span>
                                <span className="fw-extrabold text-gray-900 small">
                                    Dr. {doctor?.user?.first_name} {doctor?.user?.last_name}
                                </span>
                            </div>
                            <div className="nyl-detail-meta-row">
                                <span className="extra-small fw-bold text-muted text-uppercase">Specialization</span>
                                <span className="fw-bold text-gray-800 small">{doctor?.specialization || 'General practice'}</span>
                            </div>
                            <div className="nyl-detail-meta-row">
                                <span className="extra-small fw-bold text-muted text-uppercase">Department</span>
                                <span className="fw-bold text-gray-800 small">
                                    {doctor?.department_name || doctor?.department || '—'}
                                </span>
                            </div>
                        </DashboardPanel>
                    </div>

                    {/* Main content */}
                    <div className="col-lg-8">
                        <DashboardPanel
                            title="Visit summary"
                            icon="fa-clipboard-list"
                            headerVariant="gradient"
                            className="mb-4 nyl-detail-panel"
                            bodyClassName="p-4"
                        >
                            <div className="row g-4 mx-0">
                                <div className="col-md-6">
                                    <div className="nyl-content-box__title mb-2">Reason for visit</div>
                                    <div className="nyl-content-box nyl-content-box--highlight mb-0">
                                        {appointment.reason || 'Routine visit — no specific reason recorded.'}
                                    </div>
                                </div>
                                <div className="col-md-6">
                                    <div className="nyl-content-box__title mb-2">Triage & internal notes</div>
                                    <div className="nyl-content-box nyl-content-box--muted mb-0">
                                        {appointment.notes || 'No triage or internal notes recorded.'}
                                    </div>
                                </div>
                            </div>

                            <div className="nyl-meta-grid mt-4">
                                <div className="nyl-meta-item">
                                    <div className="nyl-meta-item__label">Booked on</div>
                                    <div className="nyl-meta-item__value">{formatDateTime(appointment.created_at)}</div>
                                </div>
                                <div className="nyl-meta-item">
                                    <div className="nyl-meta-item__label">Last updated</div>
                                    <div className="nyl-meta-item__value">{formatDateTime(appointment.updated_at)}</div>
                                </div>
                                <div className="nyl-meta-item">
                                    <div className="nyl-meta-item__label">Lab requests</div>
                                    <div className="nyl-meta-item__value">{labRequests.length}</div>
                                </div>
                                <div className="nyl-meta-item">
                                    <div className="nyl-meta-item__label">Prescriptions</div>
                                    <div className="nyl-meta-item__value">{prescriptions.length}</div>
                                </div>
                            </div>
                        </DashboardPanel>

                        {canViewClinical && (
                            <RegistryTablePanel
                                title="Clinical consultations"
                                icon="fa-stethoscope"
                                columns={consultationColumns}
                                data={consultations}
                                emptyMessage="No consultations linked to this visit yet."
                                idField="consultation_id"
                                panelClassName="mb-4"
                            />
                        )}

                        {canViewClinical && (
                            <RegistryTablePanel
                                title="Laboratory requests"
                                icon="fa-flask"
                                columns={labColumns}
                                data={labRequests}
                                emptyMessage="No lab tests requested for this visit."
                                idField="request_id"
                                panelClassName="mb-4"
                            />
                        )}

                        {canViewClinical && (
                            <RegistryTablePanel
                                title="Pharmacy orders"
                                icon="fa-pills"
                                columns={prescriptionColumns}
                                data={prescriptionRows}
                                emptyMessage="No prescriptions issued for this visit."
                                idField="item_id"
                            />
                        )}

                        {isReceptionist && (
                            <DashboardPanel title="Clinical records" icon="fa-lock" headerVariant="section">
                                <p className="text-muted mb-0 small">
                                    Clinical consultations, lab results, and pharmacy orders are restricted to clinical staff.
                                    Use check-in and status actions from the toolbar below.
                                </p>
                            </DashboardPanel>
                        )}
                    </div>
                </div>
            </div>

            <UnifiedToolbar
                actions={[
                    auth.user.role === 'nurse' && {
                        label: 'RECORD VITALS',
                        icon: 'fa-heartbeat',
                        href: route('consultations.create', {
                            patient_id: appointment.patient_id,
                            appointment_id: appointment.appointment_id,
                        }),
                    },
                    auth.user.role === 'doctor' && {
                        label: 'START CONSULTATION',
                        icon: 'fa-stethoscope',
                        href: route('consultations.create', {
                            patient_id: appointment.patient_id,
                            appointment_id: appointment.appointment_id,
                        }),
                    },
                    ['admin', 'doctor'].includes(auth.user.role) && {
                        label: 'NEW PRESCRIPTION',
                        icon: 'fa-prescription',
                        href: route('prescriptions.create', {
                            patient_id: appointment.patient_id,
                            appointment_id: appointment.appointment_id,
                        }),
                    },
                    ['admin', 'doctor', 'receptionist'].includes(auth.user.role) &&
                        ['scheduled', 'confirmed'].includes(appointment.status) && {
                        label: 'CONFIRM ARRIVAL',
                        icon: 'fa-check-circle',
                        onClick: checkIn,
                        color: 'success',
                    },
                    ['admin', 'doctor', 'receptionist'].includes(auth.user.role) &&
                        ['arrived', 'confirmed'].includes(appointment.status) && {
                        label: 'MARK COMPLETE',
                        icon: 'fa-flag-checkered',
                        onClick: () => updateStatus('completed'),
                        color: 'success',
                    },
                    ['admin', 'doctor', 'receptionist'].includes(auth.user.role) &&
                        !['completed', 'cancelled', 'no_show'].includes(appointment.status) && {
                        label: 'MARK NO-SHOW',
                        icon: 'fa-user-slash',
                        onClick: () => updateStatus('no_show'),
                        color: 'gray',
                    },
                    ['admin', 'doctor', 'receptionist'].includes(auth.user.role) &&
                        !['completed', 'cancelled'].includes(appointment.status) && {
                        label: 'CANCEL VISIT',
                        icon: 'fa-times-circle',
                        onClick: () => updateStatus('cancelled'),
                        color: 'danger',
                    },
                    {
                        label: 'BACK TO REGISTRY',
                        icon: 'fa-arrow-left',
                        href: route('appointments.index'),
                        color: 'gray',
                    },
                ].filter(Boolean)}
            />
        </AuthenticatedLayout>
    );
}
