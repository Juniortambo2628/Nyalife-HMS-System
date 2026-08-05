import { Head } from '@inertiajs/react';
import { useEffect } from 'react';
import { formatPatientId } from '@/Components/PatientTableCell';

const safe = (v) => (v && String(v).trim() ? v : '—');

export default function Print({ consultation, clinic_settings = {} }) {
    useEffect(() => {
        window.print();
    }, []);

    const patient = consultation.patient?.user;
    const doctor = consultation.doctor?.user;
    const vitals = consultation.vital_signs || {};
    const menstrual = consultation.menstrual_history || {};

    const Section = ({ title, children }) => (
        <div className="mb-4">
            <div className="section-title">{title}</div>
            {children}
        </div>
    );

    const Field = ({ label, value }) => (
        <div className="mb-2">
            <div className="info-label">{label}</div>
            <div className="info-value">{safe(value)}</div>
        </div>
    );

    return (
        <div className="consultation-print p-4 bg-white">
            <Head title={`Consultation #${consultation.consultation_id}`} />

            <div className="header">
                <img src="/assets/logo/Logo2-transparent.png" alt="Nyalife" className="hospital-logo" />
                <h1 className="hospital-name">Nyalife Women&apos;s Clinic</h1>
                <p className="hospital-tagline">{clinic_settings.contact_address || 'Nairobi, Kenya'}</p>
                <p className="hospital-tagline">
                    {clinic_settings.contact_phone} | {clinic_settings.contact_email}
                </p>
            </div>

            <div className="d-flex justify-content-between mb-4">
                <div>
                    <h4 className="fw-bold mb-1">Clinical Consultation Record</h4>
                    <div className="text-muted small">CON-{consultation.consultation_id}</div>
                </div>
                <div className="text-end">
                    <div className="fw-bold">
                        {consultation.consultation_date?.slice?.(0, 10) || consultation.consultation_date}
                    </div>
                    <div className="text-muted small text-uppercase">
                        {consultation.consultation_status?.replace('_', ' ')}
                    </div>
                </div>
            </div>

            <div className="row mb-4">
                <div className="col-6 patient-info">
                    <Field label="Patient" value={`${patient?.first_name || ''} ${patient?.last_name || ''}`.trim()} />
                    <Field label="Patient ID" value={formatPatientId(consultation.patient_id)} />
                </div>
                <div className="col-6 doctor-info text-end">
                    <Field label="Attending Physician" value={doctor ? `Dr. ${doctor.last_name}` : '—'} />
                    <Field label="Walk-in" value={consultation.is_walk_in ? 'Yes' : 'No'} />
                </div>
            </div>

            <Section title="Chief Complaint & Present Illness">
                <Field label="Chief Complaint" value={consultation.chief_complaint} />
                <Field label="History of Present Illness" value={consultation.history_present_illness} />
            </Section>

            <Section title="Clinical Background">
                <div className="row">
                    <div className="col-6">
                        <Field label="Past Medical History" value={consultation.past_medical_history} />
                        <Field label="Surgical History" value={consultation.surgical_history} />
                    </div>
                    <div className="col-6">
                        <Field label="Family History" value={consultation.family_history} />
                        <Field label="Social History" value={consultation.social_history} />
                    </div>
                </div>
            </Section>

            {(consultation.parity ||
                consultation.current_pregnancy ||
                consultation.contraceptive_history ||
                consultation.obstetric_history ||
                consultation.past_obstetric?.length > 0) && (
                <Section title="Reproductive History">
                    <Field label="Parity" value={consultation.parity} />
                    <Field label="Current Pregnancy" value={consultation.current_pregnancy} />
                    <Field label="Contraceptive History" value={consultation.contraceptive_history} />
                    <Field label="Cervical Screening" value={consultation.cervical_screening} />
                    {consultation.obstetric_history && (
                        <Field label="Obstetric History Notes" value={consultation.obstetric_history} />
                    )}
                    {menstrual.last_period_date && (
                        <Field label="LMP" value={`${menstrual.last_period_date} (${menstrual.regularity || '—'})`} />
                    )}
                    {consultation.past_obstetric?.length > 0 && (
                        <>
                            <div className="extra-small fw-bold text-uppercase mb-1">Past Pregnancies</div>
                            <table className="table table-bordered table-sm small mb-0">
                                <thead>
                                    <tr>
                                        <th>Year</th>
                                        <th>Place</th>
                                        <th>Duration</th>
                                        <th>Mode</th>
                                        <th>Outcome</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {consultation.past_obstetric.map((rec, i) => (
                                        <tr key={i}>
                                            <td>{rec.year}</td>
                                            <td>{rec.place_of_birth}</td>
                                            <td>{rec.duration}</td>
                                            <td>{rec.mode_of_delivery}</td>
                                            <td>{rec.outcome}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </>
                    )}
                </Section>
            )}

            {Object.keys(vitals).some((k) => vitals[k]) && (
                <Section title="Vital Signs">
                    <div className="row small">
                        {vitals.blood_pressure && (
                            <div className="col-3">
                                BP: <strong>{vitals.blood_pressure}</strong>
                            </div>
                        )}
                        {vitals.temperature && (
                            <div className="col-3">
                                Temp: <strong>{vitals.temperature}°C</strong>
                            </div>
                        )}
                        {vitals.heart_rate && (
                            <div className="col-3">
                                Pulse: <strong>{vitals.heart_rate}</strong>
                            </div>
                        )}
                        {vitals.weight && (
                            <div className="col-3">
                                Weight: <strong>{vitals.weight} kg</strong>
                            </div>
                        )}
                    </div>
                </Section>
            )}

            <Section title="Examination">
                <Field label="Review of Systems" value={consultation.review_of_systems} />
                <Field label="General Examination" value={consultation.general_examination} />
                <Field label="Systems Examination" value={consultation.systems_examination} />
            </Section>

            <Section title="Assessment & Plan">
                <Field label="Diagnosis" value={consultation.diagnosis} />
                <Field label="Treatment Plan" value={consultation.treatment_plan} />
                <Field label="Follow-up Instructions" value={consultation.follow_up_instructions} />
                <Field label="Notes" value={consultation.notes} />
            </Section>

            <div className="signature-area">
                <div className="signature-line"></div>
                <div className="small text-muted">Attending Physician Signature</div>
            </div>

            <div className="footer">Computer-generated clinical record — Nyalife Women&apos;s Clinic</div>

            <style>{`
                @page { size: A4; margin: 1cm; }
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #fff; }
                .consultation-print .header { text-align: center; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid #eee; }
                .consultation-print .hospital-logo { max-width: 120px; margin-bottom: 10px; }
                .consultation-print .hospital-name { font-size: 22px; font-weight: bold; color: #2c3e50; margin: 0; }
                .consultation-print .hospital-tagline { color: #7f8c8d; margin: 3px 0 0; font-size: 13px; }
                .consultation-print .section-title { font-size: 16px; font-weight: bold; color: #2c3e50; margin: 16px 0 8px; padding-bottom: 4px; border-bottom: 1px solid #eee; }
                .consultation-print .info-label { font-weight: bold; color: #7f8c8d; font-size: 11px; text-transform: uppercase; }
                .consultation-print .info-value { margin-bottom: 6px; white-space: pre-wrap; font-size: 13px; }
                .consultation-print .signature-area { margin-top: 40px; text-align: right; }
                .consultation-print .signature-line { border-top: 1px solid #000; width: 200px; display: inline-block; margin-top: 40px; }
                .consultation-print .footer { text-align: center; margin-top: 30px; font-size: 11px; color: #7f8c8d; border-top: 1px solid #eee; padding-top: 10px; }
                @media print { nav, .sidebar, .header { display: none !important; } }
            `}</style>
        </div>
    );
}
