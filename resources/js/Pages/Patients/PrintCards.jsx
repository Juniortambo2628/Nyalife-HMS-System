import React, { useEffect } from 'react';
import { Head } from '@inertiajs/react';
import { formatPatientId } from '@/Components/PatientTableCell';

export default function PrintCards({ patients }) {
    useEffect(() => {
        // Automatically trigger print dialog when component mounts and styles load
        setTimeout(() => {
            window.print();
        }, 800);
    }, []);

    return (
        <div className="bg-white min-h-screen">
            <Head title="Print Patient Cards" />
            
            <div className="print-container p-5">
                <style dangerouslySetInnerHTML={{__html: `
                    @media print {
                        body { background: white !important; margin: 0; padding: 0; }
                        * {
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                            color-adjust: exact !important;
                        }
                        .print-card { 
                            page-break-inside: avoid; 
                            break-inside: avoid; 
                            box-shadow: none !important;
                            border: 2px solid #e5e7eb !important;
                        }
                        @page { margin: 0.5in; }
                        /* Hide any inertia progress bars or toast notifications */
                        #nprogress, .Toastify { display: none !important; }
                    }
                `}} />

                <div className="d-flex flex-wrap gap-5 justify-content-center align-items-start pb-5">
                    {patients.map(patient => (
                        <div key={patient.patient_id} className="d-flex flex-row gap-4 align-items-center print-card-wrapper" style={{ pageBreakInside: 'avoid' }}>
                            {/* FRONT OF CARD */}
                            <div className="print-card rounded-4 overflow-hidden d-flex flex-column shadow-sm" style={{ width: '3.75in', height: '2.25in', backgroundColor: '#ffffff', border: '2px solid #e5e7eb', position: 'relative' }}>
                                {/* Card Header */}
                                <div className="p-3 d-flex justify-content-between align-items-center" style={{ backgroundColor: '#ec4899', color: '#ffffff' }}>
                                    <div>
                                        <h6 className="mb-0 fw-extrabold tracking-tight text-white" style={{ fontSize: '14px', margin: 0 }}>Nyalife HMS</h6>
                                        <div className="opacity-75 font-mono fw-bold text-white" style={{ fontSize: '10px' }}>{formatPatientId(patient.patient_id)}</div>
                                    </div>
                                    <i className="fas fa-hospital-symbol text-white opacity-50" style={{ fontSize: '24px' }}></i>
                                </div>
                                
                                {/* Card Body */}
                                <div className="px-3 py-2 text-start flex-grow-1 bg-white d-flex flex-column justify-content-center">
                                    <h6 className="fw-extrabold text-gray-900 mb-1 text-truncate" style={{ fontSize: '16px' }}>{patient.user?.first_name} {patient.user?.last_name}</h6>
                                    <div className="text-muted fw-bold mb-2 text-uppercase tracking-widest" style={{ fontSize: '10px' }}>
                                        {(patient.gender || 'U').charAt(0)} / {patient.blood_group || 'N/A'}
                                    </div>
                                    <div className="d-flex flex-column gap-1 font-mono text-gray-700 fw-bold" style={{ fontSize: '11px' }}>
                                        <div><i className="fas fa-phone-alt me-2" style={{ color: '#ec4899', width: '12px' }}></i>{patient.user?.phone || 'N/A'}</div>
                                        <div><i className="fas fa-calendar-alt me-2" style={{ color: '#ec4899', width: '12px' }}></i>DOB: {patient.date_of_birth ? patient.date_of_birth.split('T')[0] : 'N/A'}</div>
                                    </div>
                                </div>
                                
                                {/* Card Footer */}
                                <div className="w-100 border-top text-center py-1 text-muted fw-bold tracking-widest text-uppercase" style={{ backgroundColor: '#f9fafb', fontSize: '9px' }}>
                                    Patient Identification Card
                                </div>
                            </div>

                            {/* BACK OF CARD */}
                            <div className="print-card rounded-4 overflow-hidden d-flex flex-column shadow-sm" style={{ width: '3.75in', height: '2.25in', backgroundColor: '#ffffff', border: '2px solid #e5e7eb', position: 'relative' }}>
                                <div className="w-100 py-3" style={{ backgroundColor: '#f3f4f6' }}>
                                    <div className="w-100" style={{ height: '30px', backgroundColor: '#374151' }}></div>
                                </div>
                                
                                <div className="px-3 py-2 text-start flex-grow-1 bg-white d-flex flex-column">
                                    <div className="mb-2">
                                        <div className="text-uppercase tracking-widest fw-bold" style={{ fontSize: '8px', color: '#9ca3af' }}>Emergency Contact</div>
                                        <div className="fw-extrabold text-gray-800 text-truncate" style={{ fontSize: '11px' }}>{patient.emergency_name || 'Not Provided'}</div>
                                        <div className="font-mono text-gray-600 fw-bold" style={{ fontSize: '10px' }}>{patient.emergency_contact || 'N/A'}</div>
                                    </div>
                                    
                                    <div className="mb-2">
                                        <div className="text-uppercase tracking-widest fw-bold" style={{ fontSize: '8px', color: '#9ca3af' }}>Contact & Address</div>
                                        <div className="font-mono text-gray-800 fw-bold text-truncate" style={{ fontSize: '10px' }}>{patient.user?.email || 'No email on file'}</div>
                                        <div className="text-gray-600 text-truncate" style={{ fontSize: '9px' }}>{patient.address || 'Address not provided'}</div>
                                    </div>

                                    <div className="mt-auto border-top pt-1 text-center">
                                        <div className="text-uppercase tracking-widest fw-bold" style={{ fontSize: '7px', color: '#ec4899' }}>If found, please return to:</div>
                                        <div className="fw-bold text-gray-800" style={{ fontSize: '8px' }}>Nyalife Hospital & Medical Center</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
                
                {patients.length === 0 && (
                    <div className="text-center py-5">
                        <h4>No patients selected for printing.</h4>
                        <p className="text-muted">Please close this window and select patients to print.</p>
                    </div>
                )}
            </div>
        </div>
    );
}
