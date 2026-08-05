import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import DashboardPanel from '@/Components/DashboardPanel';
import StatCardGrid from '@/Components/StatCardGrid';
import StatusBadge from '@/Components/StatusBadge';
import PriorityBadge from '@/Components/PriorityBadge';
import UserAvatar from '@/Components/UserAvatar';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { PatientIdLabel } from '@/Components/PatientTableCell';
import { RefBadge } from '@/Components/TableCells';
import { formatDateTime, formatDateOnly } from '@/Utils/dateUtils';
import { useEffect, useState, useMemo } from 'react';

import { FilePond, registerPlugin } from 'react-filepond';
import 'filepond/dist/filepond.min.css';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';

registerPlugin(FilePondPluginImagePreview);

const formatLabel = (value) =>
    (value || '')
        .toString()
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (c) => c.toUpperCase()) || '—';

const userName = (user) => {
    if (!user) return '—';
    const name = `${user.first_name || ''} ${user.last_name || ''}`.trim();
    return name || '—';
};

export default function Show({ request, auth }) {
    const [files, setFiles] = useState([]);
    const [viewingAttachment, setViewingAttachment] = useState(null);

    const hasTemplate =
        request.test_type?.template &&
        Array.isArray(request.test_type.template) &&
        request.test_type.template.length > 0;
    const patientUser = request.patient?.user;
    const requestRef = request.request_number || `LAB-${request.request_id}`;

    const initialResults = {
        lab_results: request.results?.lab_results || (hasTemplate ? {} : ''),
        observations: request.results?.observations || '',
        conclusions: request.results?.conclusions || '',
        attachments: request.results?.attachments || [],
    };

    if (typeof request.results === 'string' || (request.results && request.results.general)) {
        initialResults.observations = request.results.general || request.results;
    }

    if (hasTemplate && typeof initialResults.lab_results !== 'object') {
        initialResults.lab_results = {};
        request.test_type.template.forEach((item) => {
            initialResults.lab_results[item.label] = '';
        });
    }

    const { data, setData, processing } = useForm({
        status: auth.user.role === 'lab_technician' ? 'pending_verification' : 'completed',
        results: initialResults,
    });

    const isLabTech = auth.user.role === 'lab_technician' || auth.user.role === 'admin';
    const isDoctorOrAdmin = ['doctor', 'admin'].includes(auth.user.role);
    const storageKey = `lab_draft_${request.request_id}`;

    useEffect(() => {
        const savedDraft = localStorage.getItem(storageKey);
        if (savedDraft && request.status !== 'completed') {
            try {
                const parsed = JSON.parse(savedDraft);
                setData('results', {
                    ...data.results,
                    ...parsed.results,
                });
            } catch (e) {
                console.error('Failed to recover draft', e);
            }
        }
    }, []);

    useEffect(() => {
        if (request.status !== 'completed') {
            window.dispatchEvent(new CustomEvent('autosave', { detail: { status: 'saving' } }));

            const timeoutId = setTimeout(() => {
                try {
                    localStorage.setItem(
                        storageKey,
                        JSON.stringify({
                            results: data.results,
                            timestamp: new Date().getTime(),
                        }),
                    );
                    window.dispatchEvent(new CustomEvent('autosave', { detail: { status: 'saved' } }));
                } catch (e) {
                    console.warn('Autosave failed: Storage access blocked', e);
                }
            }, 1000);
            return () => clearTimeout(timeoutId);
        }
    }, [data.results]);

    const compressImage = (file) => {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (event) => {
                const img = new Image();
                img.src = event.target.result;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const MAX_WIDTH = 1200;
                    const MAX_HEIGHT = 1200;
                    let width = img.width;
                    let height = img.height;

                    if (width > height) {
                        if (width > MAX_WIDTH) {
                            height *= MAX_WIDTH / width;
                            width = MAX_WIDTH;
                        }
                    } else if (height > MAX_HEIGHT) {
                        width *= MAX_HEIGHT / height;
                        height = MAX_HEIGHT;
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    resolve(canvas.toDataURL('image/jpeg', 0.7));
                };
            };
        });
    };

    const submit = async (e, statusOverride = null) => {
        if (e && e.preventDefault) e.preventDefault();
        const filePromises = files.map(async (fileItem) => {
            const file = fileItem.file;
            let fileData = null;
            if (file.type.startsWith('image/')) {
                fileData = await compressImage(file);
            } else {
                fileData = await new Promise((resolve) => {
                    const reader = new FileReader();
                    reader.onloadend = () => resolve(reader.result);
                    reader.readAsDataURL(file);
                });
            }
            return { name: file.name, type: file.type, size: file.size, data: fileData };
        });
        const attachments = await Promise.all(filePromises);
        const finalResults = { ...data.results, attachments };

        const targetStatus = statusOverride || data.status;

        router.post(
            route('lab.update-status', request.request_id),
            {
                ...data,
                status: targetStatus,
                results: finalResults,
            },
            {
                onSuccess: () => localStorage.removeItem(storageKey),
            },
        );
    };

    const handlePrint = () => window.open(route('lab.print', request.request_id), '_blank');

    const handleLabResultChange = (key, value) => {
        if (hasTemplate) {
            setData('results', {
                ...data.results,
                lab_results: { ...data.results.lab_results, [key]: value },
            });
        } else {
            setData('results', { ...data.results, lab_results: value });
        }
    };

    const handleFieldChange = (field, value) => setData('results', { ...data.results, [field]: value });

    const statItems = useMemo(
        () => [
            {
                label: 'Status',
                value: formatLabel(request.status),
                icon: 'fa-flask',
                color: ['verified', 'completed'].includes(request.status) ? 'success' : 'info',
            },
            {
                label: 'Priority',
                value: formatLabel(request.priority || 'normal'),
                icon: 'fa-bolt',
                color: request.priority === 'urgent' ? 'danger' : 'primary',
            },
            {
                label: 'Requested',
                value: formatDateOnly(request.request_date || request.created_at),
                icon: 'fa-calendar-day',
                color: 'pink',
                sub: request.created_at ? formatDateTime(request.created_at) : undefined,
            },
            {
                label: 'Category',
                value: request.test_type?.category || 'Laboratory',
                icon: 'fa-vials',
                color: 'teal',
            },
        ],
        [request],
    );

    const parameterStats = useMemo(
        () => [
            {
                label: 'Ref. range',
                value: request.test_type?.normal_range || '—',
                icon: 'fa-ruler-horizontal',
                color: 'info',
            },
            {
                label: 'Units',
                value: request.test_type?.units || '—',
                icon: 'fa-balance-scale',
                color: 'secondary',
            },
            {
                label: 'Parameters',
                value: hasTemplate ? request.test_type.template.length : '1',
                icon: 'fa-list-ol',
                color: 'teal',
            },
        ],
        [request, hasTemplate],
    );

    const toolbarActions = [
        ['admin', 'lab_technician', 'nurse'].includes(auth.user.role) &&
            ['pending', 'processing'].includes(request.status) && {
                label: 'Register sample',
                icon: 'fa-vial',
                href: route('lab.samples.register', { lab_request_id: request.request_id }),
                color: 'success',
            },
        ['verified', 'completed'].includes(request.status) && {
            label: 'Print report',
            icon: 'fa-print',
            onClick: handlePrint,
        },
        !processing &&
            isDoctorOrAdmin &&
            request.status === 'pending_verification' && {
                label: 'Verify & release results',
                icon: 'fa-check-double',
                onClick: (e) => submit(e, 'verified'),
                color: 'success',
            },
        !processing &&
            isLabTech &&
            !['verified', 'completed', 'pending_verification'].includes(request.status) && {
                label: 'Release results',
                icon: 'fa-paper-plane',
                onClick: (e) => submit(e, 'pending_verification'),
                color: 'warning',
            },
        {
            label: 'Back to registry',
            icon: 'fa-arrow-left',
            href: route('lab.index'),
            color: 'gray',
        },
    ].filter(Boolean);

    return (
        <AuthenticatedLayout
            headerTitle={`Lab request — ${requestRef}`}
            breadcrumbs={[
                { label: 'Laboratory', url: route('lab.index') },
                { label: 'Requests', url: route('lab.index') },
                { label: requestRef, active: true },
            ]}
        >
            <Head title={`Lab Request ${requestRef}`} />

            <div className="pb-5">
                <StatCardGrid items={statItems} cols={4} />

                <div className="row g-4">
                    <div className="col-lg-4">
                        <DashboardPanel
                            title="Patient"
                            icon="fa-user-injured"
                            headerVariant="gradient"
                            className="mb-4 nyl-detail-panel"
                            bodyClassName="p-4"
                        >
                            {patientUser ? (
                                <>
                                    <div className="text-center mb-4">
                                        <UserAvatar user={patientUser} size="xl" className="mb-3 shadow-sm" />
                                        <h5 className="fw-extrabold text-gray-900 mb-1">
                                            {patientUser.first_name} {patientUser.last_name}
                                        </h5>
                                        <PatientIdLabel
                                            id={request.patient_id}
                                            variant="pat-id"
                                            className="extra-small font-bold text-muted tracking-widest uppercase mb-3 d-block"
                                        />
                                        <div className="d-flex flex-wrap justify-content-center gap-2">
                                            {(request.patient?.gender || patientUser.gender) && (
                                                <span className="badge bg-pink-50 text-pink-600 rounded-pill px-3 py-2 extra-small fw-bold uppercase">
                                                    {request.patient?.gender || patientUser.gender}
                                                </span>
                                            )}
                                            {request.patient?.blood_group && (
                                                <span className="badge bg-danger-subtle text-danger rounded-pill px-3 py-2 extra-small fw-bold">
                                                    {request.patient.blood_group}
                                                </span>
                                            )}
                                            {request.patient?.age != null && (
                                                <span className="badge bg-info-subtle text-info rounded-pill px-3 py-2 extra-small fw-bold">
                                                    {request.patient.age} years
                                                </span>
                                            )}
                                        </div>
                                    </div>

                                    <div className="nyl-meta-grid mb-4">
                                        <div className="nyl-meta-item">
                                            <div className="nyl-meta-item__label">Phone</div>
                                            <div className="nyl-meta-item__value">{patientUser.phone || '—'}</div>
                                        </div>
                                        <div className="nyl-meta-item">
                                            <div className="nyl-meta-item__label">Email</div>
                                            <div className="nyl-meta-item__value text-truncate">
                                                {patientUser.email || '—'}
                                            </div>
                                        </div>
                                    </div>

                                    <Link
                                        href={route('patients.show', request.patient_id)}
                                        className="btn btn-outline-primary btn-sm w-100 rounded-pill fw-bold"
                                    >
                                        View patient record
                                    </Link>
                                </>
                            ) : (
                                <p className="text-muted mb-0">Patient record unavailable.</p>
                            )}
                        </DashboardPanel>

                        <DashboardPanel
                            title="Request workflow"
                            icon="fa-clipboard-list"
                            headerVariant="section"
                            className="mb-4 nyl-detail-panel"
                            bodyClassName="p-4"
                        >
                            <div className="d-flex flex-wrap align-items-center gap-2 mb-4">
                                <RefBadge variant="info">{requestRef}</RefBadge>
                                <StatusBadge status={request.status} />
                                <PriorityBadge priority={request.priority || 'normal'} />
                            </div>

                            <div className="nyl-meta-grid">
                                <div className="nyl-meta-item">
                                    <div className="nyl-meta-item__label">Ordering physician</div>
                                    <div className="nyl-meta-item__value">
                                        Dr. {request.doctor?.user?.last_name || 'Staff'}
                                    </div>
                                </div>
                                <div className="nyl-meta-item">
                                    <div className="nyl-meta-item__label">Requested by</div>
                                    <div className="nyl-meta-item__value">{userName(request.requestedBy)}</div>
                                </div>
                                <div className="nyl-meta-item">
                                    <div className="nyl-meta-item__label">Assigned to</div>
                                    <div className="nyl-meta-item__value">{userName(request.assignedToUser)}</div>
                                </div>
                                <div className="nyl-meta-item">
                                    <div className="nyl-meta-item__label">Request date</div>
                                    <div className="nyl-meta-item__value">
                                        {formatDateOnly(request.request_date || request.created_at)}
                                    </div>
                                </div>
                                {request.completed_at && (
                                    <div className="nyl-meta-item">
                                        <div className="nyl-meta-item__label">Completed</div>
                                        <div className="nyl-meta-item__value">
                                            {formatDateTime(request.completed_at)}
                                        </div>
                                    </div>
                                )}
                                {request.verified_at && (
                                    <div className="nyl-meta-item">
                                        <div className="nyl-meta-item__label">Verified</div>
                                        <div className="nyl-meta-item__value">
                                            {formatDateTime(request.verified_at)}
                                            {request.verifiedByUser && (
                                                <span className="d-block extra-small text-muted mt-1">
                                                    by Dr.{' '}
                                                    {request.verifiedByUser.last_name ||
                                                        userName(request.verifiedByUser)}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>

                            {request.notes && (
                                <div className="nyl-content-box nyl-content-box--muted mt-4">
                                    <div className="nyl-content-box__title mb-2">Clinical notes</div>
                                    {request.notes}
                                </div>
                            )}
                        </DashboardPanel>

                        {(request.consultation_id || request.appointment_id) && (
                            <DashboardPanel
                                title="Related records"
                                icon="fa-link"
                                headerVariant="section"
                                className="mb-4 nyl-detail-panel"
                                bodyClassName="p-4"
                            >
                                <div className="d-grid gap-2">
                                    {request.appointment_id && (
                                        <Link
                                            href={route('appointments.show', request.appointment_id)}
                                            className="btn btn-outline-primary btn-sm rounded-pill fw-bold"
                                        >
                                            <i className="fas fa-calendar-check me-2" />
                                            Appointment APT-{request.appointment_id}
                                        </Link>
                                    )}
                                    {request.consultation_id && (
                                        <Link
                                            href={route('consultations.show', request.consultation_id)}
                                            className="btn btn-outline-primary btn-sm rounded-pill fw-bold"
                                        >
                                            <i className="fas fa-stethoscope me-2" />
                                            Consultation CON-{request.consultation_id}
                                        </Link>
                                    )}
                                </div>
                            </DashboardPanel>
                        )}
                    </div>

                    <div className="col-lg-8">
                        <DashboardPanel
                            title="Investigation parameters"
                            icon="fa-vials"
                            headerVariant="gradient"
                            className="mb-4 nyl-detail-panel"
                            bodyClassName="p-4"
                            actions={
                                request.test_type?.category && (
                                    <span className="badge bg-info-subtle text-info rounded-pill px-3 py-2 border border-info-subtle extra-small fw-bold">
                                        {request.test_type.category}
                                    </span>
                                )
                            }
                        >
                            <div className="mb-4">
                                <h3 className="fw-extrabold text-gray-900 mb-2 tracking-tighter">
                                    {request.test_type?.test_name || 'Laboratory test'}
                                </h3>
                                <p className="text-muted small fw-medium mb-0">
                                    {request.test_type?.description || 'Standard diagnostic investigation protocol.'}
                                </p>
                            </div>
                            <StatCardGrid items={parameterStats} cols={3} className="mb-0" />
                        </DashboardPanel>

                        <DashboardPanel
                            title="Investigation findings"
                            icon="fa-microscope"
                            headerVariant="pink"
                            className="nyl-detail-panel"
                            bodyClassName="p-4"
                        >
                            {['verified', 'completed', 'pending_verification'].includes(request.status) ? (
                                <div className="animate-in fade-in slide-in-from-bottom-4 duration-500">
                                    <div className="text-center mb-5">
                                        {request.status === 'pending_verification' ? (
                                            <>
                                                <div className="bg-warning-subtle text-warning p-4 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm border border-warning-subtle nyl-icon-circle-lg">
                                                    <i className="fas fa-history fa-2x" />
                                                </div>
                                                <h4 className="fw-extrabold text-warning tracking-tighter">
                                                    Awaiting verification
                                                </h4>
                                                <p className="extra-small fw-bold text-muted text-uppercase tracking-widest opacity-50">
                                                    Results compiled, awaiting physician verification
                                                </p>
                                            </>
                                        ) : (
                                            <>
                                                <div className="bg-success-subtle text-success p-4 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm border border-success-subtle nyl-icon-circle-lg">
                                                    <i className="fas fa-check-double fa-2x" />
                                                </div>
                                                <h4 className="fw-extrabold text-success tracking-tighter">
                                                    Results certified
                                                </h4>
                                                <p className="extra-small fw-bold text-muted text-uppercase tracking-widest opacity-50">
                                                    Analysis verified by{' '}
                                                    {request.verified_by
                                                        ? `Dr. ${request.verifiedByUser?.last_name || 'attending physician'}`
                                                        : 'laboratory department'}
                                                </p>
                                            </>
                                        )}
                                    </div>

                                    <div className="space-y-8">
                                        <div>
                                            <h6 className="extra-small fw-extrabold text-uppercase text-muted tracking-widest mb-4 border-bottom border-gray-100 pb-2">
                                                Quantitative analysis
                                            </h6>
                                            {hasTemplate ? (
                                                <div className="table-responsive rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
                                                    <table className="table table-hover align-middle mb-0">
                                                        <thead className="bg-gray-50">
                                                            <tr>
                                                                <th className="px-4 py-3 extra-small fw-extrabold text-muted border-0">
                                                                    Parameter
                                                                </th>
                                                                <th className="px-4 py-3 extra-small fw-extrabold text-muted border-0">
                                                                    Result
                                                                </th>
                                                                <th className="px-4 py-3 extra-small fw-extrabold text-muted border-0 text-center">
                                                                    Ref. range
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody className="border-0">
                                                            {request.test_type.template.map((item, idx) => (
                                                                <tr key={idx} className="border-bottom border-gray-50">
                                                                    <td className="px-4 py-3 fw-bold text-gray-800">
                                                                        {item.label}
                                                                    </td>
                                                                    <td className="px-4 py-3 fw-extrabold text-primary">
                                                                        {data.results.lab_results[item.label] || '—'}
                                                                        <small className="text-muted fw-normal ms-1">
                                                                            {item.unit}
                                                                        </small>
                                                                    </td>
                                                                    <td className="px-4 py-3 text-center small text-muted font-mono">
                                                                        {item.normalRange || '—'}
                                                                    </td>
                                                                </tr>
                                                            ))}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            ) : (
                                                <div className="p-6 rounded-2xl bg-light-blue border border-blue-50 text-gray-800 fw-bold shadow-inner">
                                                    {data.results.lab_results || 'No quantitative results recorded.'}
                                                </div>
                                            )}
                                        </div>

                                        <div className="row g-4">
                                            <div className="col-md-6">
                                                <h6 className="extra-small fw-extrabold text-uppercase text-muted tracking-widest mb-4 border-bottom border-gray-100 pb-2">
                                                    Observations
                                                </h6>
                                                <div className="p-5 rounded-2xl bg-gray-50 border border-gray-100 text-gray-700 italic fw-medium shadow-inner">
                                                    {data.results.observations || 'No specific observations recorded.'}
                                                </div>
                                            </div>
                                            <div className="col-md-6">
                                                <h6 className="extra-small fw-extrabold text-uppercase text-muted tracking-widest mb-4 border-bottom border-gray-100 pb-2">
                                                    Clinical conclusion
                                                </h6>
                                                <div className="p-5 rounded-2xl bg-success-subtle border border-success-subtle text-success-emphasis fw-extrabold shadow-inner">
                                                    {data.results.conclusions || 'No final conclusion recorded.'}
                                                </div>
                                            </div>
                                        </div>

                                        {data.results.attachments?.length > 0 && (
                                            <div>
                                                <h6 className="extra-small fw-extrabold text-uppercase text-muted tracking-widest mb-4 border-bottom border-gray-100 pb-2">
                                                    Supporting evidence
                                                </h6>
                                                <div className="row g-3">
                                                    {data.results.attachments.map((file, idx) => (
                                                        <div key={idx} className="col-md-4">
                                                            <div
                                                                className="card h-100 border-0 shadow-sm rounded-xl overflow-hidden bg-gray-50 hover-lift cursor-pointer shadow-hover"
                                                                onClick={() => setViewingAttachment(file)}
                                                            >
                                                                <div className="position-relative nyl-attachment-preview">
                                                                    {file.type?.startsWith('image/') ? (
                                                                        <img
                                                                            src={file.data}
                                                                            alt={file.name}
                                                                            className="w-100 h-100 object-fit-cover"
                                                                        />
                                                                    ) : (
                                                                        <div className="w-100 h-100 d-flex align-items-center justify-content-center bg-gray-200">
                                                                            <i
                                                                                className={`fas ${file.type?.includes('pdf') ? 'fa-file-pdf text-danger' : 'fa-file-medical text-primary'} fa-4x opacity-20`}
                                                                            />
                                                                        </div>
                                                                    )}
                                                                    <div className="position-absolute bottom-0 start-0 w-100 p-3 bg-dark bg-opacity-70 text-white extra-small fw-bold text-truncate backdrop-blur">
                                                                        {file.name}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}
                                    </div>

                                    <div className="text-center mt-8">
                                        <button
                                            type="button"
                                            onClick={handlePrint}
                                            className="btn btn-primary rounded-pill px-5 py-3.5 fw-extrabold shadow-lg transition-all hover-translate-up tracking-widest"
                                        >
                                            <i className="fas fa-print me-2" />
                                            Generate official report
                                        </button>
                                    </div>
                                </div>
                            ) : isLabTech ? (
                                <form
                                    onSubmit={submit}
                                    className="animate-in fade-in slide-in-from-bottom-4 duration-500"
                                >
                                    <div className="space-y-6">
                                        <div>
                                            <h6 className="extra-small fw-extrabold text-uppercase text-pink-500 tracking-widest mb-4 d-flex align-items-center gap-3">
                                                <span className="avatar-sm bg-pink-500 text-white rounded-lg d-flex align-items-center justify-content-center fw-bold">
                                                    1
                                                </span>
                                                Data entry
                                            </h6>

                                            {hasTemplate ? (
                                                <div className="row g-4">
                                                    {request.test_type.template.map((item, idx) => (
                                                        <div className="col-md-6" key={idx}>
                                                            <label className="extra-small fw-extrabold text-gray-500 text-uppercase tracking-widest mb-2 d-block">
                                                                {item.label}
                                                                <span className="text-muted fw-bold ms-2 opacity-50 italic">
                                                                    ({item.normalRange || 'REF'}: {item.unit || 'U'})
                                                                </span>
                                                            </label>
                                                            <div className="input-group">
                                                                <input
                                                                    type="text"
                                                                    className="form-control form-control-lg bg-light border-0 rounded-xl fw-bold"
                                                                    value={data.results.lab_results[item.label] || ''}
                                                                    onChange={(e) =>
                                                                        handleLabResultChange(
                                                                            item.label,
                                                                            e.target.value,
                                                                        )
                                                                    }
                                                                    placeholder="Enter value"
                                                                />
                                                                {item.unit && (
                                                                    <span className="input-group-text bg-gray-100 border-0 text-muted extra-small fw-bold">
                                                                        {item.unit}
                                                                    </span>
                                                                )}
                                                            </div>
                                                        </div>
                                                    ))}
                                                </div>
                                            ) : (
                                                <textarea
                                                    className="form-control form-control-lg bg-light border-0 rounded-2xl fw-bold"
                                                    rows="3"
                                                    value={data.results.lab_results}
                                                    onChange={(e) => handleLabResultChange(null, e.target.value)}
                                                    placeholder="Enter quantitative data or detailed results..."
                                                />
                                            )}
                                        </div>

                                        <div className="row g-4">
                                            <div className="col-md-6">
                                                <h6 className="extra-small fw-extrabold text-uppercase text-pink-500 tracking-widest mb-4 d-flex align-items-center gap-3">
                                                    <span className="avatar-sm bg-pink-500 text-white rounded-lg d-flex align-items-center justify-content-center fw-bold">
                                                        2
                                                    </span>
                                                    Observations
                                                </h6>
                                                <textarea
                                                    className="form-control bg-light border-0 rounded-2xl fw-medium"
                                                    rows="4"
                                                    value={data.results.observations}
                                                    onChange={(e) => handleFieldChange('observations', e.target.value)}
                                                    placeholder="Record clinical observations..."
                                                />
                                            </div>
                                            <div className="col-md-6">
                                                <h6 className="extra-small fw-extrabold text-uppercase text-pink-500 tracking-widest mb-4 d-flex align-items-center gap-3">
                                                    <span className="avatar-sm bg-pink-500 text-white rounded-lg d-flex align-items-center justify-content-center fw-bold">
                                                        3
                                                    </span>
                                                    Conclusion
                                                </h6>
                                                <textarea
                                                    className="form-control bg-success-subtle border-0 rounded-2xl text-success-emphasis fw-extrabold"
                                                    rows="4"
                                                    value={data.results.conclusions}
                                                    onChange={(e) => handleFieldChange('conclusions', e.target.value)}
                                                    placeholder="Enter professional conclusion..."
                                                />
                                            </div>
                                        </div>

                                        <div>
                                            <h6 className="extra-small fw-extrabold text-uppercase text-pink-500 tracking-widest mb-4 d-flex align-items-center gap-3">
                                                <span className="avatar-sm bg-pink-500 text-white rounded-lg d-flex align-items-center justify-content-center fw-bold">
                                                    4
                                                </span>
                                                Evidence upload
                                            </h6>
                                            <div className="border-2 border-dashed border-gray-200 rounded-3xl p-4 bg-gray-50 shadow-inner">
                                                <FilePond
                                                    files={files}
                                                    onupdatefiles={setFiles}
                                                    allowMultiple
                                                    maxFiles={5}
                                                    name="files"
                                                    labelIdle='Drag & Drop images or <span class="filepond--label-action">Browse</span>'
                                                    imagePreviewHeight={170}
                                                />
                                            </div>
                                        </div>

                                        <div className="d-grid mt-6">
                                            <button
                                                type="submit"
                                                disabled={processing}
                                                className="btn btn-success btn-lg rounded-pill shadow-lg py-3.5 fw-extrabold transition-all hover-translate-up tracking-widest"
                                            >
                                                {processing ? (
                                                    <span className="spinner-border spinner-border-sm me-2" />
                                                ) : (
                                                    <i className="fas fa-check-circle me-2" />
                                                )}
                                                Release results
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            ) : (
                                <div className="opacity-50 py-16 text-center">
                                    <div className="bg-gray-100 p-5 rounded-circle d-inline-flex mb-4 shadow-inner border border-gray-50">
                                        <i className="fas fa-microscope text-gray-400 fa-4x opacity-20" />
                                    </div>
                                    <h5 className="fw-extrabold text-gray-600 tracking-tighter">
                                        Investigation in progress
                                    </h5>
                                    <p className="text-muted extra-small fw-bold text-uppercase tracking-widest px-5 mx-auto nyl-text-constrained">
                                        Results are currently being processed by the laboratory team.
                                    </p>
                                </div>
                            )}
                        </DashboardPanel>
                    </div>
                </div>
            </div>

            <UnifiedToolbar actions={toolbarActions} />

            {viewingAttachment && (
                <div className="fixed inset-0 z-[2000] bg-black bg-opacity-95 d-flex flex-column animate-in fade-in backdrop-blur-sm">
                    <div className="d-flex justify-content-between align-items-center p-5 text-white">
                        <h4 className="mb-0 fw-extrabold tracking-tighter">{viewingAttachment.name}</h4>
                        <div className="d-flex gap-3">
                            <a
                                href={viewingAttachment.data}
                                download={viewingAttachment.name}
                                className="btn btn-outline-light rounded-pill px-4 btn-sm fw-bold"
                            >
                                <i className="fas fa-download me-2" />
                                Download
                            </a>
                            <button
                                onClick={() => setViewingAttachment(null)}
                                type="button"
                                className="btn btn-white rounded-circle d-flex align-items-center justify-content-center nyl-modal-close-btn"
                            >
                                <i className="fas fa-times fa-lg" />
                            </button>
                        </div>
                    </div>
                    <div className="flex-grow-1 d-flex align-items-center justify-content-center p-6 overflow-auto">
                        {viewingAttachment.type?.startsWith('image/') ? (
                            <img
                                src={viewingAttachment.data}
                                alt={viewingAttachment.name}
                                className="img-fluid rounded-2xl shadow-2xl nyl-modal-media-img"
                            />
                        ) : viewingAttachment.type?.includes('pdf') ? (
                            <iframe
                                src={viewingAttachment.data}
                                className="w-100 h-100 rounded-2xl shadow-2xl bg-white nyl-modal-media-frame"
                                title={viewingAttachment.name}
                            />
                        ) : (
                            <div className="text-center text-white p-10 bg-white bg-opacity-10 rounded-3xl border border-white border-opacity-10">
                                <i className="fas fa-file-medical fa-5x mb-4 opacity-20" />
                                <h4 className="fw-bold">Preview unavailable</h4>
                                <a
                                    href={viewingAttachment.data}
                                    download={viewingAttachment.name}
                                    className="btn btn-primary mt-4 rounded-pill px-5 py-3 fw-bold tracking-widest"
                                >
                                    Download to view
                                </a>
                            </div>
                        )}
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
