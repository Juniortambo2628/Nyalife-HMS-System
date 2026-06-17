/**
 * Standard patient name + ID display for dashboard tables.
 */

export function formatPatientId(id, prefix = 'PAT') {
    if (id == null || id === '') return '';
    return `${prefix}-${id}`;
}

/** Search/select dropdown label: "Jane Doe (PAT-123)" */
export function formatPatientSelectLabel(name, id, prefix = 'PAT') {
    const formattedId = formatPatientId(id, prefix);
    const trimmed = (name || '').trim();

    if (!formattedId) return trimmed;
    if (trimmed && trimmed.includes(formattedId)) return trimmed;
    if (!trimmed) return formattedId;

    return `${trimmed} (${formattedId})`;
}

/** Hero/detail line: "ID: PAT-123 | REF: PAT-20260101-0001" */
export function PatientReferenceLabel({
    patientId,
    referenceNumber,
    className = 'extra-small font-bold text-white opacity-50 tracking-widest uppercase mb-4',
    as: Component = 'div',
}) {
    const formattedId = formatPatientId(patientId);
    if (!formattedId && !referenceNumber) return null;

    return (
        <Component className={className}>
            {formattedId && <>ID: {formattedId}</>}
            {formattedId && referenceNumber && ' | '}
            {referenceNumber && <>REF: {referenceNumber}</>}
        </Component>
    );
}

export function PatientIdLabel({
    id,
    prefix = 'PAT',
    variant = 'default',
    className = '',
    as: Component = 'div',
}) {
    if (id == null || id === '') return null;

    const formatted = formatPatientId(id, prefix);

    const variants = {
        default: {
            className: 'extra-small text-muted fw-medium opacity-75',
            children: <>ID: {formatted}</>,
        },
        short: {
            className: 'extra-small text-muted',
            children: formatted,
        },
        'pat-id': {
            className: 'extra-small font-bold text-muted opacity-50',
            children: <>PAT-ID: {formatted}</>,
        },
        patid: {
            className: 'extra-small text-muted fw-bold text-uppercase opacity-75',
            children: <>PATID: {formatted}</>,
        },
        print: {
            className: 'text-muted small mb-1',
            children: <>ID: {formatted}</>,
        },
    };

    const config = variants[variant] ?? variants.default;

    return (
        <Component className={className || config.className}>
            {config.children}
        </Component>
    );
}

export default function PatientTableCell({ patient, patientId, showId = true, idVariant = 'default' }) {
    const user = patient?.user;
    const id = patientId ?? patient?.patient_id;
    const name = user
        ? `${user.first_name ?? ''} ${user.last_name ?? ''}`.trim()
        : 'Unknown patient';

    return (
        <div>
            <div className="fw-bold text-gray-900">{name || 'Unknown patient'}</div>
            {showId && <PatientIdLabel id={id} variant={idVariant} />}
        </div>
    );
}
