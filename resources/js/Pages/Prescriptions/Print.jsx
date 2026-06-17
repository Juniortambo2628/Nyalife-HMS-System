import { Head } from '@inertiajs/react';
import { useEffect } from 'react';
import { PatientIdLabel } from '@/Components/PatientTableCell';

export default function Print({ prescription, clinic_settings = {} }) {
    useEffect(() => {
        window.print();
    }, []);

    const patient = prescription.patient?.user;
    const doctor = prescription.doctor;

    return (
        <div className="print-container p-5 bg-white">
            <Head title={`Prescription RX-${prescription.prescription_id}`} />

            <div className="d-flex justify-content-between align-items-center mb-5 border-bottom pb-4">
                <div>
                    <img src="/assets/logo/Logo2-transparent.png" alt="Nyalife" style={{ height: '70px' }} className="mb-2" />
                    <div className="text-muted small">{clinic_settings.contact_address}</div>
                    <div className="text-muted small">{clinic_settings.contact_email} | {clinic_settings.contact_phone}</div>
                </div>
                <div className="text-end">
                    <h4 className="fw-bold text-uppercase mb-1">Prescription</h4>
                    <div className="text-muted">RX-{String(prescription.prescription_id).padStart(5, '0')}</div>
                    <div className="text-muted small">{prescription.prescription_date}</div>
                </div>
            </div>

            <div className="row mb-4">
                <div className="col-6">
                    <h6 className="text-muted text-uppercase small fw-bold">Patient</h6>
                    <p className="fw-bold mb-0">{patient?.first_name} {patient?.last_name}</p>
                    <PatientIdLabel id={prescription.patient_id} variant="short" as="p" className="text-muted small mb-0" />
                </div>
                <div className="col-6 text-end">
                    <h6 className="text-muted text-uppercase small fw-bold">Prescriber</h6>
                    <p className="fw-bold mb-0">Dr. {doctor?.last_name || doctor?.user?.last_name || 'Staff'}</p>
                    <p className="text-muted small mb-0">Status: {prescription.status}</p>
                </div>
            </div>

            <table className="table table-bordered">
                <thead className="bg-light">
                    <tr>
                        <th>Medicine</th>
                        <th>Dosage</th>
                        <th>Frequency</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    {(prescription.items || []).map((item, idx) => (
                        <tr key={idx}>
                            <td className="fw-bold">{item.medication?.medication_name || item.medicine_name}</td>
                            <td>{item.dosage || '—'}</td>
                            <td>{item.frequency || '—'}</td>
                            <td>{item.duration || '—'}</td>
                        </tr>
                    ))}
                </tbody>
            </table>

            {prescription.notes && (
                <div className="p-3 border rounded bg-light mt-3">
                    <h6 className="small fw-bold text-muted text-uppercase">Instructions</h6>
                    <p className="mb-0">{prescription.notes}</p>
                </div>
            )}

            <div className="mt-5 pt-5 d-flex justify-content-between">
                <div className="text-center" style={{ width: '200px' }}>
                    <div className="border-bottom mb-2"></div>
                    <div className="small text-muted">Pharmacist Signature</div>
                </div>
                <div className="text-center" style={{ width: '200px' }}>
                    <div className="border-bottom mb-2"></div>
                    <div className="small text-muted">Prescriber Signature</div>
                </div>
            </div>

            <div className="mt-4 text-center text-muted small">
                Computer-generated prescription — Nyalife Women&apos;s Clinic
            </div>

            <style>{`@media print { nav, .sidebar { display: none !important; } body { background: white !important; } }`}</style>
        </div>
    );
}
