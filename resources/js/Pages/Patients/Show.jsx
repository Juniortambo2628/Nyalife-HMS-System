import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useForm } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import StatusBadge from '@/Components/StatusBadge';
import UserAvatar from '@/Components/UserAvatar';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import DashboardPanel from '@/Components/DashboardPanel';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import StatCardGrid from '@/Components/StatCardGrid';
import TableActions from '@/Components/TableActions';
import PriorityBadge from '@/Components/PriorityBadge';
import { PatientIdLabel } from '@/Components/PatientTableCell';
import { RefBadge, TableCellPrimary, TableCellStack } from '@/Components/TableCells';
import { formatDateTime, formatDateOnly } from '@/Utils/dateUtils';

const formatLabel = (value) =>
    (value || '')
        .toString()
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (c) => c.toUpperCase()) || '—';

export default function Show({ patient, clinical_summary, auth }) {
    const isReceptionist = auth.user.role === 'receptionist';
    const canViewClinical = ['doctor', 'admin', 'nurse'].includes(auth.user.role);

    const appointments = patient.appointments || [];
    const consultations = patient.consultations || [];
    const prescriptions = patient.prescriptions || [];
    const vitals = patient.vitals || [];
    const latestVital = vitals[0];

    const [voidingVital, setVoidingVital] = useState(null);
    const {
        data: voidData,
        setData: setVoidData,
        delete: destroyVital,
        processing: voidProcessing,
        reset: resetVoid,
    } = useForm({
        void_reason: '',
    });

    const age = patient.age ?? '—';
    const dob = patient.date_of_birth || patient.user?.date_of_birth;

    const statItems = [
        {
            label: 'Age',
            value: age !== '—' ? `${age} years` : '—',
            icon: 'fa-birthday-cake',
            color: 'pink',
            sub: dob ? formatDateOnly(dob) : undefined,
        },
        {
            label: 'Scheduled visits',
            value: appointments.length,
            icon: 'fa-calendar-check',
            color: 'teal',
            sub: appointments.filter((a) => ['scheduled', 'confirmed', 'arrived'].includes(a.status)).length
                ? `${appointments.filter((a) => ['scheduled', 'confirmed', 'arrived'].includes(a.status)).length} upcoming`
                : undefined,
        },
        {
            label: 'Consultations',
            value: canViewClinical ? consultations.length : '—',
            icon: 'fa-stethoscope',
            color: 'info',
        },
        {
            label: 'Vitals on file',
            value: vitals.length,
            icon: 'fa-heartbeat',
            color: 'warning',
            sub: latestVital?.blood_pressure ? `Latest BP ${latestVital.blood_pressure}` : undefined,
        },
    ];

    const consultationColumns = useMemo(
        () => [
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
                header: 'Physician',
                id: 'doctor',
                cell: ({ row }) => (
                    <TableCellPrimary>Dr. {row.original.doctor?.user?.last_name || 'Staff'}</TableCellPrimary>
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
                    <TableActions
                        actions={[
                            {
                                icon: 'fa-eye',
                                label: 'View consultation',
                                href: route('consultations.show', row.original.consultation_id),
                            },
                        ]}
                    />
                ),
            },
        ],
        [],
    );

    const appointmentColumns = useMemo(
        () => [
            {
                header: 'Visit',
                accessorKey: 'appointment_id',
                cell: ({ row }) => <RefBadge variant="pink">APT-{row.original.appointment_id}</RefBadge>,
            },
            {
                header: 'Schedule',
                id: 'schedule',
                cell: ({ row }) => (
                    <TableCellStack primary={row.original.appointment_date} secondary={row.original.appointment_time} />
                ),
            },
            {
                header: 'Provider',
                id: 'doctor',
                cell: ({ row }) => (
                    <TableCellPrimary>Dr. {row.original.doctor?.user?.last_name || 'Staff'}</TableCellPrimary>
                ),
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
                    <TableActions
                        actions={[
                            {
                                icon: 'fa-eye',
                                label: 'View appointment',
                                href: route('appointments.show', row.original.appointment_id),
                            },
                        ]}
                    />
                ),
            },
        ],
        [],
    );

    const prescriptionColumns = useMemo(
        () => [
            {
                header: 'Prescription',
                accessorKey: 'prescription_id',
                cell: ({ row }) => (
                    <RefBadge variant="info">
                        {row.original.prescription_number || `RX-${row.original.prescription_id}`}
                    </RefBadge>
                ),
            },
            {
                header: 'Date',
                accessorKey: 'prescription_date',
                cell: ({ row }) => <TableCellPrimary>{row.original.prescription_date}</TableCellPrimary>,
            },
            {
                header: 'Medications',
                accessorKey: 'items',
                cell: ({ row }) => {
                    const items = row.original.items || [];
                    return (
                        <TableCellPrimary className="text-truncate nyl-text-constrained">
                            {items.map((i) => i.medication?.medication_name || i.medicine_name || 'Item').join(' · ') ||
                                '—'}
                        </TableCellPrimary>
                    );
                },
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
                    <TableActions
                        actions={[
                            {
                                icon: 'fa-eye',
                                label: 'View prescription',
                                href: route('prescriptions.show', row.original.prescription_id),
                            },
                        ]}
                    />
                ),
            },
        ],
        [],
    );

    const vitalColumns = useMemo(
        () => [
            {
                header: 'Recorded',
                accessorKey: 'measured_at',
                cell: ({ row }) => <TableCellPrimary>{formatDateTime(row.original.measured_at)}</TableCellPrimary>,
            },
            {
                header: 'Blood pressure',
                accessorKey: 'blood_pressure',
                cell: ({ row }) => <TableCellPrimary>{row.original.blood_pressure || '—'}</TableCellPrimary>,
            },
            {
                header: 'Vitals',
                id: 'vitals',
                cell: ({ row }) => (
                    <TableCellStack
                        primary={
                            [
                                row.original.temperature && `${row.original.temperature}°C`,
                                row.original.heart_rate && `${row.original.heart_rate} bpm`,
                            ]
                                .filter(Boolean)
                                .join(' · ') || '—'
                        }
                        secondary={[
                            row.original.respiratory_rate && `RR ${row.original.respiratory_rate}`,
                            row.original.oxygen_saturation && `SpO₂ ${row.original.oxygen_saturation}%`,
                        ]
                            .filter(Boolean)
                            .join(' · ')}
                    />
                ),
            },
            {
                header: 'Priority',
                accessorKey: 'priority',
                cell: ({ row }) => <PriorityBadge priority={row.original.priority || 'normal'} />,
            },
            {
                header: 'Actions',
                id: 'actions',
                cell: ({ row }) => (
                    <TableActions
                        actions={[
                            {
                                icon: 'fa-edit',
                                label: 'Edit vitals',
                                href: route('vitals.edit', row.original.vital_id),
                            },
                            {
                                icon: 'fa-ban',
                                label: 'Void record',
                                color: 'danger',
                                onClick: () => setVoidingVital(row.original),
                            },
                        ]}
                    />
                ),
            },
        ],
        [],
    );

    const clinicalFields = clinical_summary
        ? [
              { title: 'Past medical history', value: clinical_summary.past_medical_history },
              { title: 'Surgical history', value: clinical_summary.surgical_history },
              { title: 'Family history', value: clinical_summary.family_history },
              { title: 'Social history', value: clinical_summary.social_history },
              { title: 'Gynecological history', value: clinical_summary.gynecological_history },
              { title: 'Obstetric history', value: clinical_summary.obstetric_history },
              { title: 'Parity', value: clinical_summary.parity },
              { title: 'Current pregnancy', value: clinical_summary.current_pregnancy },
          ].filter((f) => f.value)
        : [];

    return (
        <AuthenticatedLayout
            headerTitle="Patient record"
            breadcrumbs={[
                { label: 'Patients', url: route('patients.index') },
                { label: `PAT-${patient.patient_id}`, active: true },
            ]}
        >
            <Head title={`Patient - ${patient.user?.first_name || 'Profile'}`} />

            <div className="pb-5">
                <StatCardGrid items={statItems} cols={4} />

                <div className="row g-4">
                    {/* Sidebar */}
                    <div className="col-lg-4">
                        <DashboardPanel
                            title="Patient profile"
                            icon="fa-user-injured"
                            headerVariant="gradient"
                            className="mb-4 nyl-detail-panel"
                            bodyClassName="p-4"
                        >
                            <div className="text-center mb-4">
                                <UserAvatar user={patient.user} size="xl" className="mb-3 shadow-sm" />
                                <h5 className="fw-extrabold text-gray-900 mb-1">
                                    {patient.user?.first_name} {patient.user?.last_name}
                                </h5>
                                <PatientIdLabel
                                    id={patient.patient_id}
                                    variant="pat-id"
                                    className="extra-small font-bold text-muted tracking-widest uppercase mb-1 d-block"
                                />
                                {patient.patient_number && (
                                    <div className="extra-small text-muted fw-bold mb-3">
                                        REF: {patient.patient_number}
                                    </div>
                                )}
                                <div className="d-flex flex-wrap justify-content-center gap-2">
                                    {patient.gender && (
                                        <span className="badge bg-pink-50 text-pink-600 rounded-pill px-3 py-2 extra-small fw-bold uppercase">
                                            {patient.gender}
                                        </span>
                                    )}
                                    {patient.blood_group && (
                                        <span className="badge bg-danger-subtle text-danger rounded-pill px-3 py-2 extra-small fw-bold">
                                            {patient.blood_group}
                                        </span>
                                    )}
                                    {patient.marital_status && (
                                        <span className="badge bg-light text-muted rounded-pill px-3 py-2 extra-small fw-bold text-capitalize">
                                            {patient.marital_status}
                                        </span>
                                    )}
                                </div>
                            </div>

                            <div className="nyl-meta-grid mb-4">
                                <div className="nyl-meta-item">
                                    <div className="nyl-meta-item__label">Phone</div>
                                    <div className="nyl-meta-item__value">{patient.user?.phone || '—'}</div>
                                </div>
                                <div className="nyl-meta-item">
                                    <div className="nyl-meta-item__label">Email</div>
                                    <div className="nyl-meta-item__value text-truncate">
                                        {patient.user?.email || '—'}
                                    </div>
                                </div>
                                <div className="nyl-meta-item">
                                    <div className="nyl-meta-item__label">Date of birth</div>
                                    <div className="nyl-meta-item__value">{dob ? formatDateOnly(dob) : '—'}</div>
                                </div>
                                <div className="nyl-meta-item">
                                    <div className="nyl-meta-item__label">Registered</div>
                                    <div className="nyl-meta-item__value">{formatDateOnly(patient.created_at)}</div>
                                </div>
                            </div>

                            {(patient.address || patient.user?.address) && (
                                <div className="nyl-content-box nyl-content-box--muted mb-4">
                                    <div className="nyl-content-box__title mb-2">Address</div>
                                    {patient.address || patient.user?.address}
                                </div>
                            )}

                            <Link
                                href={route('patients.edit', patient.patient_id)}
                                className="btn btn-outline-primary btn-sm w-100 rounded-pill fw-bold"
                            >
                                Edit patient profile
                            </Link>
                        </DashboardPanel>

                        <DashboardPanel
                            title="Emergency contact"
                            icon="fa-user-shield"
                            headerVariant="section"
                            className="mb-4 nyl-detail-panel"
                            bodyClassName="p-4"
                        >
                            <div className="nyl-detail-meta-row">
                                <span className="extra-small fw-bold text-muted text-uppercase">Next of kin</span>
                                <span className="fw-extrabold text-gray-900 small">
                                    {patient.emergency_name || 'Not specified'}
                                </span>
                            </div>
                            <div className="nyl-detail-meta-row">
                                <span className="extra-small fw-bold text-muted text-uppercase">Contact line</span>
                                <span className="fw-bold text-gray-800 small">{patient.emergency_contact || '—'}</span>
                            </div>
                        </DashboardPanel>

                        {(patient.insurance_provider || patient.insurance_id) && (
                            <DashboardPanel
                                title="Insurance"
                                icon="fa-shield-alt"
                                headerVariant="section"
                                className="mb-4 nyl-detail-panel"
                                bodyClassName="p-4"
                            >
                                <div className="nyl-detail-meta-row">
                                    <span className="extra-small fw-bold text-muted text-uppercase">Provider</span>
                                    <span className="fw-extrabold text-gray-900 small">
                                        {patient.insurance_provider || '—'}
                                    </span>
                                </div>
                                <div className="nyl-detail-meta-row">
                                    <span className="extra-small fw-bold text-muted text-uppercase">Member ID</span>
                                    <span className="fw-bold text-gray-800 small">{patient.insurance_id || '—'}</span>
                                </div>
                            </DashboardPanel>
                        )}

                        {canViewClinical && (patient.allergies || patient.chronic_diseases) && (
                            <DashboardPanel
                                title="Clinical alerts"
                                icon="fa-exclamation-triangle"
                                headerVariant="section"
                                className="nyl-detail-panel"
                                bodyClassName="p-4"
                            >
                                {patient.allergies && (
                                    <div className="nyl-content-box nyl-content-box--highlight mb-3">
                                        <div className="nyl-content-box__title mb-2">Allergies</div>
                                        {patient.allergies}
                                    </div>
                                )}
                                {patient.chronic_diseases && (
                                    <div className="nyl-content-box nyl-content-box--muted mb-0">
                                        <div className="nyl-content-box__title mb-2">Chronic conditions</div>
                                        {patient.chronic_diseases}
                                    </div>
                                )}
                            </DashboardPanel>
                        )}
                    </div>

                    {/* Main content */}
                    <div className="col-lg-8">
                        <DashboardPanel
                            title="Demographics & biometrics"
                            icon="fa-id-card"
                            headerVariant="gradient"
                            className="mb-4 nyl-detail-panel"
                            bodyClassName="p-4"
                        >
                            <div className="row g-4 mx-0">
                                <div className="col-md-6">
                                    <div className="nyl-content-box__title mb-2">Physical profile</div>
                                    <div className="nyl-meta-grid">
                                        <div className="nyl-meta-item">
                                            <div className="nyl-meta-item__label">Height</div>
                                            <div className="nyl-meta-item__value">
                                                {patient.height ? `${patient.height} cm` : '—'}
                                            </div>
                                        </div>
                                        <div className="nyl-meta-item">
                                            <div className="nyl-meta-item__label">Weight</div>
                                            <div className="nyl-meta-item__value">
                                                {patient.weight ? `${patient.weight} kg` : '—'}
                                            </div>
                                        </div>
                                        <div className="nyl-meta-item">
                                            <div className="nyl-meta-item__label">Occupation</div>
                                            <div className="nyl-meta-item__value">{patient.occupation || '—'}</div>
                                        </div>
                                        <div className="nyl-meta-item">
                                            <div className="nyl-meta-item__label">Prescriptions</div>
                                            <div className="nyl-meta-item__value">
                                                {canViewClinical ? prescriptions.length : 'Restricted'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div className="col-md-6">
                                    <div className="nyl-content-box__title mb-2">Latest vitals snapshot</div>
                                    <div className="nyl-content-box nyl-content-box--info mb-0">
                                        {latestVital ? (
                                            <div className="nyl-meta-grid">
                                                <div className="nyl-meta-item">
                                                    <div className="nyl-meta-item__label">Blood pressure</div>
                                                    <div className="nyl-meta-item__value">
                                                        {latestVital.blood_pressure || '—'}
                                                    </div>
                                                </div>
                                                <div className="nyl-meta-item">
                                                    <div className="nyl-meta-item__label">Temperature</div>
                                                    <div className="nyl-meta-item__value">
                                                        {latestVital.temperature ? `${latestVital.temperature}°C` : '—'}
                                                    </div>
                                                </div>
                                                <div className="nyl-meta-item">
                                                    <div className="nyl-meta-item__label">Pulse</div>
                                                    <div className="nyl-meta-item__value">
                                                        {latestVital.heart_rate ? `${latestVital.heart_rate} bpm` : '—'}
                                                    </div>
                                                </div>
                                                <div className="nyl-meta-item">
                                                    <div className="nyl-meta-item__label">SpO₂</div>
                                                    <div className="nyl-meta-item__value">
                                                        {latestVital.oxygen_saturation
                                                            ? `${latestVital.oxygen_saturation}%`
                                                            : '—'}
                                                    </div>
                                                </div>
                                            </div>
                                        ) : (
                                            <span className="text-muted">No vitals recorded yet.</span>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </DashboardPanel>

                        {canViewClinical && clinicalFields.length > 0 && (
                            <DashboardPanel
                                title="Clinical background"
                                icon="fa-notes-medical"
                                headerVariant="gradient"
                                className="mb-4 nyl-detail-panel"
                                bodyClassName="p-4"
                                actions={
                                    clinical_summary?.consultation_id && (
                                        <Link
                                            href={route('consultations.show', clinical_summary.consultation_id)}
                                            className="btn btn-sm btn-outline-primary rounded-pill fw-bold extra-small"
                                        >
                                            View source record
                                        </Link>
                                    )
                                }
                            >
                                {clinical_summary?.consultation_date && (
                                    <p className="extra-small text-muted fw-bold mb-4">
                                        Sourced from consultation on{' '}
                                        {formatDateTime(clinical_summary.consultation_date)}
                                    </p>
                                )}
                                <div className="row g-3 mx-0">
                                    {clinicalFields.map((field) => (
                                        <div key={field.title} className="col-md-6">
                                            <div className="nyl-content-box__title mb-2">{field.title}</div>
                                            <div className="nyl-content-box nyl-content-box--muted mb-0 small">
                                                {typeof field.value === 'object'
                                                    ? JSON.stringify(field.value, null, 2)
                                                    : field.value}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </DashboardPanel>
                        )}

                        <RegistryTablePanel
                            title="Scheduled visits"
                            icon="fa-calendar-alt"
                            columns={appointmentColumns}
                            data={appointments}
                            emptyMessage="No appointments recorded for this patient."
                            idField="appointment_id"
                            panelClassName="mb-4"
                        />

                        {canViewClinical && (
                            <RegistryTablePanel
                                title="Consultations"
                                icon="fa-stethoscope"
                                columns={consultationColumns}
                                data={consultations}
                                emptyMessage="No consultations on file."
                                idField="consultation_id"
                                panelClassName="mb-4"
                            />
                        )}

                        {canViewClinical && vitals.length > 0 && (
                            <RegistryTablePanel
                                title="Vitals history"
                                icon="fa-heartbeat"
                                columns={vitalColumns}
                                data={vitals}
                                emptyMessage="No vitals recorded."
                                idField="vital_id"
                                panelClassName="mb-4"
                            />
                        )}

                        {canViewClinical && prescriptions.length > 0 && (
                            <RegistryTablePanel
                                title="Pharmacy orders"
                                icon="fa-pills"
                                columns={prescriptionColumns}
                                data={prescriptions}
                                emptyMessage="No prescriptions issued."
                                idField="prescription_id"
                            />
                        )}

                        {isReceptionist && (
                            <DashboardPanel
                                title="Clinical records"
                                icon="fa-lock"
                                headerVariant="section"
                                className="nyl-detail-panel"
                                bodyClassName="p-4"
                            >
                                <p className="text-muted mb-0 small">
                                    Consultations, vitals, and pharmacy history are restricted to clinical staff. You
                                    can manage appointments and profile details from the toolbar.
                                </p>
                            </DashboardPanel>
                        )}
                    </div>
                </div>
            </div>

            <UnifiedToolbar
                actions={[
                    ['doctor', 'nurse', 'admin'].includes(auth.user.role) && {
                        label: 'RECORD VITALS',
                        icon: 'fa-heartbeat',
                        href: route('vitals.create', { patient_id: patient.patient_id }),
                    },
                    ['doctor', 'nurse', 'admin', 'lab_technician'].includes(auth.user.role) && {
                        label: 'NEW CONSULTATION',
                        icon: 'fa-stethoscope',
                        href: route('consultations.create', { patient_id: patient.patient_id }),
                    },
                    ['admin', 'doctor'].includes(auth.user.role) && {
                        label: 'NEW PRESCRIPTION',
                        icon: 'fa-prescription',
                        href: route('prescriptions.create', { patient_id: patient.patient_id }),
                    },
                    {
                        label: 'SCHEDULE VISIT',
                        icon: 'fa-calendar-plus',
                        href: route('appointments.create', { patient_id: patient.patient_id }),
                        color: 'pink',
                    },
                    {
                        label: 'EDIT PROFILE',
                        icon: 'fa-user-edit',
                        href: route('patients.edit', patient.patient_id),
                        color: 'gray',
                    },
                ].filter(Boolean)}
            />

            {voidingVital && (
                <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 shadow-lg rounded-4">
                            <div className="modal-header border-bottom-0 p-4">
                                <h5 className="modal-title fw-bold text-danger">
                                    <i className="fas fa-exclamation-triangle me-2" /> Void vitals record
                                </h5>
                                <button
                                    type="button"
                                    aria-label="Close"
                                    className="btn-close"
                                    onClick={() => {
                                        setVoidingVital(null);
                                        resetVoid();
                                    }}
                                />
                            </div>
                            <div className="modal-body p-4 pt-0">
                                <p className="text-muted mb-4">
                                    Void vitals recorded on <strong>{formatDateTime(voidingVital.measured_at)}</strong>?
                                    The record stays in the audit trail.
                                </p>
                                <label className="form-label fw-bold text-muted extra-small text-uppercase">
                                    Reason
                                </label>
                                <textarea
                                    className="form-control bg-light border-0"
                                    rows="3"
                                    placeholder="e.g. Duplicate entry, incorrect values…"
                                    value={voidData.void_reason}
                                    onChange={(e) => setVoidData('void_reason', e.target.value)}
                                />
                            </div>
                            <div className="modal-footer border-top-0 p-4">
                                <button
                                    type="button"
                                    className="btn btn-light rounded-pill px-4 fw-bold"
                                    onClick={() => {
                                        setVoidingVital(null);
                                        resetVoid();
                                    }}
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-danger rounded-pill px-4 fw-bold shadow-sm"
                                    disabled={!voidData.void_reason || voidProcessing}
                                    onClick={() => {
                                        destroyVital(route('vitals.destroy', voidingVital.vital_id), {
                                            preserveScroll: true,
                                            onSuccess: () => {
                                                setVoidingVital(null);
                                                resetVoid();
                                            },
                                        });
                                    }}
                                >
                                    {voidProcessing ? 'Voiding…' : 'Void record'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
