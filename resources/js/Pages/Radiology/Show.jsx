import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { PatientIdLabel } from '@/Components/PatientTableCell';
import { useEffect, useState } from 'react';
import { formatDateTime } from '@/Utils/dateUtils';

// FilePond
import { FilePond, registerPlugin } from 'react-filepond';
import 'filepond/dist/filepond.min.css';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';

registerPlugin(FilePondPluginImagePreview);

export default function Show({ request, auth }) {
    const [files, setFiles] = useState([]);
    const [viewingAttachment, setViewingAttachment] = useState(null);
    const [activeTemplate, setActiveTemplate] = useState('');
    const [isPrinting, setIsPrinting] = useState(false);

    const isReceptionist = auth.user.role === 'receptionist';
    const isPatient = auth.user.role === 'patient';
    const isClinical = ['doctor', 'nurse', 'lab_technician', 'admin'].includes(auth.user.role);
    const isAttendingOrAdmin = ['doctor', 'admin'].includes(auth.user.role);

    // Check if the clinical fields are null (masked)
    const isMasked = request.findings === null && request.impression === null && request.clinical_indication === null;

    const { data, setData, post, processing, errors } = useForm({
        status: request.status || 'pending',
        findings: request.findings || '',
        impression: request.impression || '',
        scan_details: request.scan_details || '',
    });

    const storageKey = `radiology_draft_${request.request_id}`;

    // Recover draft if available
    useEffect(() => {
        const savedDraft = localStorage.getItem(storageKey);
        if (savedDraft && !['verified', 'completed'].includes(request.status)) {
            try {
                const parsed = JSON.parse(savedDraft);
                setData((d) => ({
                    ...d,
                    ...parsed,
                }));
            } catch (e) {
                console.error('Failed to recover radiology draft', e);
            }
        }
    }, []);

    // Autosave local draft
    useEffect(() => {
        if (!['verified', 'completed'].includes(request.status)) {
            const timeoutId = setTimeout(() => {
                try {
                    localStorage.setItem(
                        storageKey,
                        JSON.stringify({
                            findings: data.findings,
                            impression: data.impression,
                            scan_details: data.scan_details,
                            timestamp: new Date().getTime(),
                        }),
                    );
                } catch (e) {
                    console.warn('Draft save failed', e);
                }
            }, 1000);
            return () => clearTimeout(timeoutId);
        }
    }, [data.findings, data.impression, data.scan_details]);

    const templates = {
        pelvic_tas: {
            name: 'Pelvic Ultrasound (TAS)',
            findings: `UTERUS: Anteverted, measures 7.8 x 4.2 x 3.6 cm. Myometrial echotexture is homogeneous without focal lesions.\nENDOMETRIUM: Regular, thickness 6.5 mm. No intracavitary fluid or mass.\nRIGHT OVARY: Measures 3.2 x 2.1 cm. Normal follicular distribution. No cystic or solid mass.\nLEFT OVARY: Measures 2.9 x 1.8 cm. Normal architecture and stroma.\nDOUGLAS POUCH: No free fluid visualized in the rectouterine pouch.`,
            impression: `Normal transabdominal pelvic ultrasound scan.`,
        },
        pelvic_tvs: {
            name: 'Pelvic Ultrasound (TVS)',
            findings: `UTERUS: Midpositioned, normal contours, measuring 7.5 x 4.0 x 3.2 cm.\nENDOMETRIUM: Distinct trilaminar appearance, thickness 8.0 mm.\nRIGHT OVARY: Measures 3.0 x 2.0 cm. Normal stromal echogenicity.\nLEFT OVARY: Measures 3.1 x 1.9 cm. Contains a dominant follicle measuring 14 mm.\nCUL-DE-SAC: A trace amount of physiological free fluid noted.`,
            impression: `Physiological pelvic scan with a dominant left ovarian follicle.`,
        },
        ob_dating: {
            name: 'Obstetric (Dating/Early)',
            findings: `GESTATIONAL SAC: Regular, intra-uterine, MSD measures 2.4 cm.\nFETAL POLE: Clearly visualized, CRL measures 1.2 cm, corresponding to 7 weeks 3 days gestation.\nCARDIAC ACTIVITY: Fetal heart motion is present and regular at 145 bpm.\nYOLK SAC: Round, normal size, measures 4.1 mm.\nADNEXA: No corpus luteum cyst issues. No subchorionic hematoma seen.`,
            impression: `Single active intrauterine pregnancy at 7+3 weeks.`,
        },
        ob_growth: {
            name: 'Obstetric (Growth & Doppler)',
            findings: `PRESENTATION: Longitudinal, cephalic.\nFETAL MOVEMENT & TONE: Normal.\nPLACENTA: Anterior, clear of internal os, Grade I maturity.\nAMNIOTIC FLUID: Normal volume, AFI is 12.4 cm.\nBIOMETRY:\n- BPD: 8.2 cm (33w 0d)\n- HC: 30.5 cm (32w 6d)\n- AC: 28.9 cm (32w 5d)\n- FL: 6.4 cm (33w 1d)\n- EFW: 2150g, placing growth on the 48th percentile.\nDOPPLER: Umbilical artery PI is 0.95 (normal).`,
            impression: `Single active cephalic fetus showing reassuring growth parameters.`,
        },
        mammography: {
            name: 'Mammography / Breast Scan',
            findings: `SKIN & NIPPLE: Normal skin thickness and nipple profile. No retraction.\nPARENCHYMA: Fibroglandular tissue is symmetric. No architectural distortion or microcalcifications.\nRIGHT BREAST: No dominant solid or cystic mass. Normal retromammary space.\nLEFT BREAST: No focal masses or suspicious calcifications.\nAXILLARY REGIONS: Normal morphology lymph nodes.`,
            impression: `BI-RADS Category 1 - Negative (Normal breast scan).`,
        },
        chest_xray: {
            name: 'Chest X-Ray',
            findings: `LUNGS: Clear, fully expanded. No focal consolidation, pleural effusion, or pneumothorax.\nHEART: Cardiothoracic ratio is normal (<50%). Mediastinal contours are normal.\nBONES: Thoracic cage is intact. No osteolytic lesions.\nDIAPHRAGM: Smooth domes, costophrenic angles are sharp.`,
            impression: `Clear lung fields and normal cardiac silhouette.`,
        },
    };

    const loadTemplate = (key) => {
        const t = templates[key];
        if (t) {
            setData((d) => ({
                ...d,
                findings: t.findings,
                impression: t.impression,
            }));
            setActiveTemplate(key);
        }
    };

    const submit = (statusOverride) => {
        const targetStatus = statusOverride || data.status;
        post(
            route('radiology.update-status', request.request_id),
            {
                ...data,
                status: targetStatus,
                _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
            {
                onSuccess: () => {
                    localStorage.removeItem(storageKey);
                },
            },
        );
    };

    const handlePrint = () => {
        setIsPrinting(true);
        setTimeout(() => {
            window.print();
            setIsPrinting(false);
        }, 500);
    };

    return (
        <AuthenticatedLayout
            headerTitle="Radiology & Imaging Order Details"
            breadcrumbs={[
                { label: 'Radiology Registry', url: route('radiology.index') },
                { label: request.request_number, active: true },
            ]}
        >
            <Head title={`Radiology scan ${request.request_number}`} />

            {/* Print Header styling */}
            {isPrinting && (
                <div className="d-none d-print-block mb-5 text-center">
                    <h2 className="fw-extrabold text-pink-600">NYALIFE WOMEN'S HEALTH CLINIC</h2>
                    <p className="extra-small fw-bold text-muted text-uppercase tracking-widest">
                        Diagnostic Imaging & Radiology Report
                    </p>
                    <hr className="my-4" />
                </div>
            )}

            <div className="py-0">
                <div className="row g-4">
                    {/* Patient Information Panel */}
                    <div className="col-lg-4 d-print-block col-print-4">
                        <div className="card shadow-sm border-0 rounded-2xl overflow-hidden mb-4 bg-white shadow-hover">
                            <div className="card-header bg-white py-4 border-bottom-0 d-print-none">
                                <h6 className="mb-0 fw-extrabold text-uppercase tracking-widest extra-small text-pink-500">
                                    Patient Registry
                                </h6>
                            </div>
                            <div className="card-body p-4 pt-0 pt-print-4">
                                <div className="d-flex align-items-center mb-4 p-3 bg-light rounded-2xl border border-light shadow-inner">
                                    <div
                                        className="avatar-md me-3 flex-shrink-0 rounded-lg d-flex align-items-center justify-content-center fw-extrabold text-white"
                                        style={{
                                            background: 'linear-gradient(135deg, #e91e63, #c2185b)',
                                            fontSize: '1.25rem',
                                            width: '50px',
                                            height: '50px',
                                        }}
                                    >
                                        {request.patient?.user?.first_name?.charAt(0) || 'P'}
                                    </div>
                                    <div>
                                        <div
                                            className="fw-extrabold text-gray-900 tracking-tighter"
                                            style={{ fontSize: '1.1rem' }}
                                        >
                                            {request.patient?.user?.first_name} {request.patient?.user?.last_name}
                                        </div>
                                        <PatientIdLabel id={request.patient_id} variant="pat-id" />
                                    </div>
                                </div>
                                <div className="space-y-3">
                                    <div className="d-flex justify-content-between align-items-center p-3 rounded-xl bg-gray-50 border border-gray-100">
                                        <span className="text-muted extra-small fw-extrabold text-uppercase tracking-widest">
                                            Gender
                                        </span>
                                        <span className="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-extrabold extra-small text-uppercase">
                                            {request.patient?.gender || request.patient?.user?.gender || 'FEMALE'}
                                        </span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center p-3 rounded-xl bg-gray-50 border border-gray-100">
                                        <span className="text-muted extra-small fw-extrabold text-uppercase tracking-widest">
                                            Age
                                        </span>
                                        <span className="badge bg-info-subtle text-info rounded-pill px-3 py-2 fw-extrabold extra-small">
                                            {request.patient?.age ? `${request.patient?.age} YEARS` : 'N/A'}
                                        </span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center p-3 rounded-xl bg-gray-50 border border-gray-100">
                                        <span className="text-muted extra-small fw-extrabold text-uppercase tracking-widest">
                                            Blood Type
                                        </span>
                                        <span className="badge bg-danger-subtle text-danger rounded-pill px-3 py-2 fw-extrabold extra-small">
                                            {request.patient?.blood_group || 'O+'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Order Metadata */}
                        <div className="card shadow-sm border-0 rounded-2xl overflow-hidden bg-white mb-4 shadow-hover">
                            <div className="card-header bg-white py-4 border-bottom-0 d-print-none">
                                <h6 className="mb-0 fw-extrabold text-uppercase tracking-widest extra-small text-pink-500">
                                    Order Information
                                </h6>
                            </div>
                            <div className="card-body p-4 pt-0 pt-print-4">
                                <div className="space-y-4">
                                    <div className="d-flex justify-content-between align-items-center border-bottom border-gray-50 pb-2">
                                        <span className="text-muted extra-small fw-bold text-uppercase">
                                            Ref Number
                                        </span>
                                        <span className="small fw-extrabold text-gray-900">
                                            {request.request_number}
                                        </span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center border-bottom border-gray-50 pb-2">
                                        <span className="text-muted extra-small fw-bold text-uppercase">Scan Type</span>
                                        <span className="small fw-extrabold text-pink-600">{request.scan_type}</span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center border-bottom border-gray-50 pb-2">
                                        <span className="text-muted extra-small fw-bold text-uppercase">
                                            Attending Physician
                                        </span>
                                        <span className="small fw-extrabold text-gray-900">
                                            Dr. {request.requestedBy?.last_name || 'Staff'}
                                        </span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center border-bottom border-gray-50 pb-2">
                                        <span className="text-muted extra-small fw-bold text-uppercase">Priority</span>
                                        <span
                                            className={`badge rounded-pill px-3 py-1 fw-extrabold extra-small text-uppercase ${
                                                request.priority === 'emergency'
                                                    ? 'bg-danger text-white'
                                                    : request.priority === 'urgent'
                                                      ? 'bg-warning text-dark'
                                                      : 'bg-info text-white'
                                            }`}
                                        >
                                            {request.priority}
                                        </span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center">
                                        <span className="text-muted extra-small fw-bold text-uppercase">
                                            Current Status
                                        </span>
                                        <StatusBadge status={request.status} />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {request.consultation_id && (
                            <Link
                                href={route('consultations.show', request.consultation_id)}
                                className="btn btn-outline-primary w-100 rounded-pill py-3 fw-extrabold extra-small tracking-widest shadow-sm shadow-hover d-print-none"
                            >
                                <i className="fas fa-external-link-alt me-2"></i> VIEW CONSULTATION
                            </Link>
                        )}
                    </div>

                    {/* Scan Request Indications & Findings Form */}
                    <div className="col-lg-8 d-print-block col-print-8">
                        {/* Indicative Details */}
                        <div className="card shadow-sm border-0 rounded-2xl overflow-hidden mb-4 bg-white shadow-hover">
                            <div className="card-header bg-white py-4 border-bottom-0 d-flex justify-content-between align-items-center">
                                <h6 className="mb-0 fw-extrabold text-uppercase tracking-widest extra-small text-pink-500">
                                    Order Clinical Indication
                                </h6>
                            </div>
                            <div className="card-body p-4 pt-0">
                                {isReceptionist ? (
                                    <div className="p-4 rounded-2xl bg-light-warning border border-warning-subtle text-muted text-center shadow-inner d-print-none">
                                        <i className="fas fa-shield-alt text-warning me-2 mb-2 d-block fs-3"></i>
                                        <span className="fw-semibold small">
                                            Clinical indications and details are restricted from administrative roles
                                            under strict HIPAA compliance rules.
                                        </span>
                                    </div>
                                ) : isPatient && !['verified', 'completed'].includes(request.status) ? (
                                    <div className="p-4 rounded-2xl bg-light-info border border-info-subtle text-muted text-center shadow-inner">
                                        <i className="fas fa-user-lock text-info me-2 mb-2 d-block fs-3"></i>
                                        <span className="fw-semibold small">
                                            Your clinical request details are under secure review and will be visible
                                            once certified by the doctor.
                                        </span>
                                    </div>
                                ) : (
                                    <div className="row g-3">
                                        <div className="col-md-6">
                                            <div className="p-4 rounded-2xl bg-gray-50 border border-gray-100 shadow-inner h-100">
                                                <div className="text-muted extra-small fw-extrabold text-uppercase tracking-widest mb-2">
                                                    Clinical Indication
                                                </div>
                                                <div
                                                    className="fw-bold text-gray-800"
                                                    style={{ whiteSpace: 'pre-line' }}
                                                >
                                                    {request.clinical_indication || 'No indications specified.'}
                                                </div>
                                            </div>
                                        </div>
                                        <div className="col-md-6">
                                            <div className="p-4 rounded-2xl bg-gray-50 border border-gray-100 shadow-inner h-100">
                                                <div className="text-muted extra-small fw-extrabold text-uppercase tracking-widest mb-2">
                                                    Instructions / Details
                                                </div>
                                                <div
                                                    className="fw-medium text-gray-700"
                                                    style={{ whiteSpace: 'pre-line' }}
                                                >
                                                    {request.scan_details || 'No instructions provided.'}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Imaging Scan Results Section */}
                        <div className="card shadow-sm border-0 rounded-2xl overflow-hidden bg-white shadow-hover">
                            <div className="card-header bg-gradient-primary-to-secondary text-white py-4 border-0 d-print-none">
                                <h6 className="mb-0 fw-extrabold text-uppercase tracking-widest extra-small">
                                    Diagnostic Findings & Reporting
                                </h6>
                            </div>
                            <div className="card-body p-4 p-md-5">
                                {isReceptionist ? (
                                    <div className="text-center py-5 d-print-none">
                                        <i className="fas fa-user-shield text-pink-500 fa-3x mb-3 opacity-30"></i>
                                        <h5 className="fw-extrabold text-gray-900 tracking-tighter">
                                            Administrative Privacy Restricted
                                        </h5>
                                        <p className="text-muted small max-w-md mx-auto">
                                            This report contains HIPAA-protected clinical information, including
                                            findings and diagnostic impressions. Access is limited to authorized
                                            clinical practitioners only.
                                        </p>
                                    </div>
                                ) : isPatient && !['verified', 'completed'].includes(request.status) ? (
                                    <div className="text-center py-5">
                                        <i className="fas fa-spinner fa-spin text-pink-500 fa-3x mb-3"></i>
                                        <h5 className="fw-extrabold text-gray-900 tracking-tighter">
                                            Scan Awaiting Verification
                                        </h5>
                                        <p className="text-muted small max-w-md mx-auto">
                                            Your scan report has been sent to our chief medical practitioner for
                                            verification. We will notify you immediately once results are ready and
                                            signed off.
                                        </p>
                                    </div>
                                ) : (
                                    <div>
                                        {/* Status & Stamps */}
                                        <div className="text-center mb-5 d-print-none">
                                            {request.status === 'pending' && (
                                                <>
                                                    <div
                                                        className="bg-warning-subtle text-warning p-4 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 border border-warning-subtle"
                                                        style={{ width: '80px', height: '80px' }}
                                                    >
                                                        <i className="fas fa-clock fa-2x"></i>
                                                    </div>
                                                    <h4 className="fw-extrabold text-warning tracking-tighter">
                                                        ORDER IS PENDING
                                                    </h4>
                                                    <p className="extra-small fw-bold text-muted text-uppercase tracking-widest opacity-75">
                                                        Click 'Start Scanning' below to process this patient scan
                                                    </p>
                                                </>
                                            )}
                                            {request.status === 'processing' && (
                                                <>
                                                    <div
                                                        className="bg-info-subtle text-info p-4 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 border border-info-subtle"
                                                        style={{ width: '80px', height: '80px' }}
                                                    >
                                                        <i className="fas fa-spinner fa-spin fa-2x"></i>
                                                    </div>
                                                    <h4 className="fw-extrabold text-info tracking-tighter">
                                                        SCAN IN PROGRESS
                                                    </h4>
                                                    <p className="extra-small fw-bold text-muted text-uppercase tracking-widest opacity-75">
                                                        Technician is currently compiling clinical scan findings
                                                    </p>
                                                </>
                                            )}
                                            {request.status === 'pending_verification' && (
                                                <>
                                                    <div
                                                        className="bg-warning-subtle text-warning p-4 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 border border-warning-subtle animate-pulse-custom"
                                                        style={{ width: '80px', height: '80px' }}
                                                    >
                                                        <i className="fas fa-history fa-2x"></i>
                                                    </div>
                                                    <h4 className="fw-extrabold text-warning-emphasis tracking-tighter">
                                                        AWAITING CLINICAL SIGN-OFF
                                                    </h4>
                                                    <p className="extra-small fw-bold text-muted text-uppercase tracking-widest opacity-75">
                                                        Results compiled, awaiting attending physician verification
                                                    </p>
                                                </>
                                            )}
                                            {['verified', 'completed'].includes(request.status) && (
                                                <>
                                                    <div
                                                        className="bg-success-subtle text-success p-4 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 border border-success-subtle"
                                                        style={{ width: '80px', height: '80px' }}
                                                    >
                                                        <i className="fas fa-check-double fa-2x"></i>
                                                    </div>
                                                    <h4 className="fw-extrabold text-success tracking-tighter">
                                                        REPORT VERIFIED & SIGNED OFF
                                                    </h4>
                                                    <p className="extra-small fw-bold text-muted text-uppercase tracking-widest opacity-75">
                                                        Clinical record verified by{' '}
                                                        {request.verifiedByUser
                                                            ? `Dr. ${request.verifiedByUser.first_name} ${request.verifiedByUser.last_name}`
                                                            : 'Attending Physician'}
                                                    </p>
                                                </>
                                            )}
                                        </div>

                                        {/* Print Signature Block / Stamp */}
                                        {['verified', 'completed'].includes(request.status) && (
                                            <div className="d-flex align-items-center justify-content-between p-4 rounded-2xl mb-4 bg-success-subtle border border-success text-success shadow-sm">
                                                <div>
                                                    <div className="fw-extrabold tracking-tighter uppercase d-flex align-items-center gap-2">
                                                        <i className="fas fa-shield-check"></i> Verified Digital
                                                        Clinical Record
                                                    </div>
                                                    <div className="extra-small fw-bold text-success-emphasis opacity-75 mt-1">
                                                        VERIFIED BY: DR.{' '}
                                                        {request.verifiedByUser?.first_name?.toUpperCase()}{' '}
                                                        {request.verifiedByUser?.last_name?.toUpperCase()} (
                                                        {request.verifiedByUser?.role?.toUpperCase()})
                                                    </div>
                                                    <div className="extra-small fw-bold text-success-emphasis opacity-75">
                                                        VERIFIED AT:{' '}
                                                        {request.verified_at
                                                            ? formatDateTime(request.verified_at)
                                                            : 'N/A'}
                                                    </div>
                                                </div>
                                                <button
                                                    onClick={handlePrint}
                                                    className="btn btn-sm btn-success rounded-pill px-4 py-2 fw-bold d-print-none shadow-sm"
                                                >
                                                    <i className="fas fa-print me-1"></i> Print Report
                                                </button>
                                            </div>
                                        )}

                                        {/* Clinical Text Fields - Show or Edit */}
                                        {['verified', 'completed'].includes(request.status) ? (
                                            <div className="space-y-4 animate-in fade-in duration-300">
                                                <div className="p-4 rounded-2xl bg-gray-50 border border-gray-100 shadow-inner">
                                                    <h6 className="extra-small fw-extrabold text-uppercase text-pink-500 tracking-widest mb-3">
                                                        Diagnostic Findings
                                                    </h6>
                                                    <div
                                                        className="fw-bold text-gray-800"
                                                        style={{
                                                            whiteSpace: 'pre-line',
                                                            fontSize: '1rem',
                                                            lineHeight: '1.6',
                                                        }}
                                                    >
                                                        {request.findings}
                                                    </div>
                                                </div>

                                                <div className="p-4 rounded-2xl bg-gray-100 border border-gray-200 shadow-inner">
                                                    <h6 className="extra-small fw-extrabold text-uppercase text-pink-500 tracking-widest mb-3">
                                                        Diagnostic Impression
                                                    </h6>
                                                    <div
                                                        className="fw-extrabold text-pink-600"
                                                        style={{
                                                            whiteSpace: 'pre-line',
                                                            fontSize: '1.05rem',
                                                            lineHeight: '1.5',
                                                        }}
                                                    >
                                                        {request.impression}
                                                    </div>
                                                </div>
                                            </div>
                                        ) : (
                                            // Write / Verify Mode
                                            <form
                                                onSubmit={(e) => {
                                                    e.preventDefault();
                                                }}
                                            >
                                                {/* Preselected templates for scanning technicians */}
                                                {!['verified', 'completed', 'pending_verification'].includes(
                                                    request.status,
                                                ) && (
                                                    <div className="mb-4">
                                                        <label className="extra-small fw-extrabold text-pink-500 text-uppercase tracking-widest mb-3 d-block">
                                                            Quick Diagnostic Templates
                                                        </label>
                                                        <div className="d-flex flex-wrap gap-2">
                                                            {Object.keys(templates).map((k) => (
                                                                <button
                                                                    key={k}
                                                                    type="button"
                                                                    onClick={() => loadTemplate(k)}
                                                                    className={`btn btn-sm px-3 py-2 rounded-pill fw-bold border transition-all ${
                                                                        activeTemplate === k
                                                                            ? 'btn-pink text-white border-pink-500 shadow-sm'
                                                                            : 'btn-outline-light text-muted border-gray-200 hover-bg-light shadow-sm'
                                                                    }`}
                                                                >
                                                                    {templates[k].name}
                                                                </button>
                                                            ))}
                                                        </div>
                                                    </div>
                                                )}

                                                <div className="row g-4">
                                                    {/* If technician hasn't started scanning yet, show Start Scanning trigger */}
                                                    {request.status === 'pending' && (
                                                        <div className="col-md-12 text-center py-4 bg-light rounded-3xl border border-dashed">
                                                            <p className="fw-bold text-muted mb-3">
                                                                This radiology scan has not been started yet.
                                                            </p>
                                                            <button
                                                                type="button"
                                                                onClick={() => submit('processing')}
                                                                className="btn btn-pink rounded-pill px-5 py-3 fw-bold shadow shadow-hover"
                                                                disabled={processing}
                                                            >
                                                                <i className="fas fa-play me-2"></i> Start Scanning &
                                                                Processing
                                                            </button>
                                                        </div>
                                                    )}

                                                    {/* In progress or pending verification (Physician editing is allowed before verifying) */}
                                                    {['processing', 'pending_verification'].includes(
                                                        request.status,
                                                    ) && (
                                                        <>
                                                            <div className="col-md-12">
                                                                <label className="extra-small fw-extrabold text-pink-500 text-uppercase tracking-widest mb-2 d-block">
                                                                    Diagnostic Findings{' '}
                                                                    <span className="text-danger">*</span>
                                                                </label>
                                                                <textarea
                                                                    className="form-control bg-light border-0 rounded-2xl p-4 fw-bold"
                                                                    rows="8"
                                                                    value={data.findings}
                                                                    onChange={(e) =>
                                                                        setData('findings', e.target.value)
                                                                    }
                                                                    placeholder="Describe systematic ultrasound or x-ray findings (Uterus, ovaries, gestational sac, lungs, etc.)..."
                                                                    required
                                                                />
                                                                {errors.findings && (
                                                                    <div className="text-danger small mt-1">
                                                                        {errors.findings}
                                                                    </div>
                                                                )}
                                                            </div>

                                                            <div className="col-md-12">
                                                                <label className="extra-small fw-extrabold text-pink-500 text-uppercase tracking-widest mb-2 d-block">
                                                                    Diagnostic Impression{' '}
                                                                    <span className="text-danger">*</span>
                                                                </label>
                                                                <textarea
                                                                    className="form-control bg-light border-0 rounded-2xl p-4 fw-extrabold text-pink-600"
                                                                    rows="3"
                                                                    value={data.impression}
                                                                    onChange={(e) =>
                                                                        setData('impression', e.target.value)
                                                                    }
                                                                    placeholder="Summary conclusion / clinical diagnosis impression..."
                                                                    required
                                                                />
                                                                {errors.impression && (
                                                                    <div className="text-danger small mt-1">
                                                                        {errors.impression}
                                                                    </div>
                                                                )}
                                                            </div>

                                                            <div className="col-md-12 mt-4 d-flex flex-wrap gap-3">
                                                                {/* Technician submission */}
                                                                {request.status === 'processing' && (
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => submit('pending_verification')}
                                                                        className="btn btn-pink rounded-pill px-4 py-3 fw-extrabold extra-small tracking-widest"
                                                                        disabled={
                                                                            processing ||
                                                                            !data.findings ||
                                                                            !data.impression
                                                                        }
                                                                    >
                                                                        <i className="fas fa-check-circle me-1"></i>{' '}
                                                                        Submit for Verification
                                                                    </button>
                                                                )}

                                                                {/* Doctor / Admin Verification */}
                                                                {request.status === 'pending_verification' &&
                                                                    isAttendingOrAdmin && (
                                                                        <button
                                                                            type="button"
                                                                            onClick={() => submit('verified')}
                                                                            className="btn btn-success rounded-pill px-5 py-3 fw-extrabold extra-small tracking-widest shadow shadow-hover"
                                                                            disabled={
                                                                                processing ||
                                                                                !data.findings ||
                                                                                !data.impression
                                                                            }
                                                                        >
                                                                            <i className="fas fa-check-double me-2"></i>{' '}
                                                                            Verify & Sign Off Report
                                                                        </button>
                                                                    )}

                                                                {/* Save draft */}
                                                                <button
                                                                    type="button"
                                                                    onClick={() => submit(request.status)}
                                                                    className="btn btn-outline-secondary rounded-pill px-4 py-3 fw-extrabold extra-small tracking-widest"
                                                                    disabled={processing}
                                                                >
                                                                    <i className="fas fa-save me-1"></i> Save Draft
                                                                </button>
                                                            </div>
                                                        </>
                                                    )}
                                                </div>
                                            </form>
                                        )}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div className="d-print-none">
                <UnifiedToolbar
                    actions={[
                        isAttendingOrAdmin &&
                            request.status === 'pending' && {
                                label: 'EDIT ORDER',
                                icon: 'fa-edit',
                                href: route('radiology.edit', request.request_id),
                                color: 'primary',
                            },
                        isAttendingOrAdmin &&
                            request.status === 'pending' && {
                                label: 'DELETE ORDER',
                                icon: 'fa-trash-alt',
                                onClick: () => {
                                    if (confirm('Are you sure you want to delete this pending radiology request?')) {
                                        router.delete(route('radiology.destroy', request.request_id));
                                    }
                                },
                                color: 'danger',
                            },
                        ['verified', 'completed'].includes(request.status) && {
                            label: 'PRINT REPORT',
                            icon: 'fa-print',
                            onClick: handlePrint,
                            color: 'success',
                        },
                        {
                            label: 'BACK TO REGISTRY',
                            icon: 'fa-layer-group',
                            href: route('radiology.index'),
                            color: 'gray',
                        },
                    ].filter(Boolean)}
                />
            </div>
        </AuthenticatedLayout>
    );
}
