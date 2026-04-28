import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import DashboardSelect from '@/Components/DashboardSelect';
import Modal from '@/Components/Modal';
import QuickPatientModal from '@/Components/QuickPatientModal';
import axios from 'axios';
import { useState } from 'react';

export default function Create({ preselected_patient_id, preselected_patient_label, preselected_doctor_id, preselected_doctor_label, auth }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        patient_id: preselected_patient_id || '',
        doctor_id: preselected_doctor_id || '',
        appointment_date: new Date().toISOString().split('T')[0],
        appointment_time: '08:00',
        appointment_type: 'consultation',
        status: 'scheduled',
        reason: '',
        notes: '',
    });

    const [showQuickAdd, setShowQuickAdd] = useState(false);
    const [quickPatientLabel, setQuickPatientLabel] = useState(preselected_patient_label || "");

    const submit = (e) => {
        e.preventDefault();
        post(route('appointments.store'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            headerTitle="New Appointment"
            breadcrumbs={[
                { label: 'Dashboard', url: route('dashboard') },
                { label: 'Appointments', url: route('appointments.index') },
                { label: 'Create', active: true }
            ]}
        >
            <Head title="Create Appointment" />

            <div className="container-fluid appointments-page px-0">

                <div className="row justify-content-center">
                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0">
                            <div className="card-body p-4">
                                <form onSubmit={submit}>
                                    <div className="row g-3">
                                        <div className="col-md-6">
                                            <div className="d-flex justify-content-between align-items-end mb-2">
                                                <label className="form-label fw-bold mb-0">Patient <span className="text-danger">*</span></label>
                                                <button 
                                                    type="button" 
                                                    onClick={() => setShowQuickAdd(true)}
                                                    className="btn btn-link btn-sm p-0 text-decoration-none fw-bold"
                                                >
                                                    <i className="fas fa-plus-circle me-1"></i>New Patient
                                                </button>
                                            </div>
                                            <DashboardSelect 
                                                asyncUrl="/patients/search"
                                                value={data.patient_id}
                                                onChange={val => setData('patient_id', val)}
                                                initialLabel={quickPatientLabel || (data.patient_id ? `PAT-${data.patient_id}` : '')}
                                                placeholder="Search Patients..."
                                                className={errors.patient_id ? 'is-invalid' : ''}
                                            />
                                            {errors.patient_id && <div className="text-danger small mt-1">{errors.patient_id}</div>}
                                        </div>

                                        <div className="col-md-6">
                                            <label className="form-label fw-bold">Doctor <span className="text-danger">*</span></label>
                                            <DashboardSelect 
                                                asyncUrl="/doctors/search"
                                                value={data.doctor_id}
                                                onChange={val => setData('doctor_id', val)}
                                                initialLabel={preselected_doctor_label}
                                                placeholder="Search Doctors..."
                                                className={errors.doctor_id ? 'is-invalid' : ''}
                                            />
                                            {errors.doctor_id && <div className="text-danger small mt-1">{errors.doctor_id}</div>}
                                        </div>

                                        <div className="col-md-6">
                                            <label className="form-label fw-bold">Date <span className="text-danger">*</span></label>
                                            <input 
                                                type="date"
                                                className={`form-control ${errors.appointment_date ? 'is-invalid' : ''}`}
                                                value={data.appointment_date}
                                                onChange={e => setData('appointment_date', e.target.value)}
                                                required
                                            />
                                            {errors.appointment_date && <div className="invalid-feedback">{errors.appointment_date}</div>}
                                        </div>

                                        <div className="col-md-6">
                                            <label className="form-label fw-bold">Time <span className="text-danger">*</span></label>
                                            <input 
                                                type="time"
                                                className={`form-control ${errors.appointment_time ? 'is-invalid' : ''}`}
                                                value={data.appointment_time}
                                                onChange={e => setData('appointment_time', e.target.value)}
                                                required
                                            />
                                            {errors.appointment_time && <div className="invalid-feedback">{errors.appointment_time}</div>}
                                        </div>

                                        <div className="col-md-6">
                                            <label className="form-label fw-bold">Type <span className="text-danger">*</span></label>
                                            <select 
                                                className={`form-select ${errors.appointment_type ? 'is-invalid' : ''}`}
                                                value={data.appointment_type}
                                                onChange={e => setData('appointment_type', e.target.value)}
                                                required
                                            >
                                                <option value="consultation">Consultation</option>
                                                <option value="follow_up">Follow Up</option>
                                                <option value="emergency">Emergency</option>
                                                <option value="routine_checkup">Routine Checkup</option>
                                                <option value="vaccination">Vaccination</option>
                                                <option value="lab_test">Lab Test</option>
                                            </select>
                                            {errors.appointment_type && <div className="invalid-feedback">{errors.appointment_type}</div>}
                                        </div>

                                        <div className="col-md-6">
                                            <label className="form-label fw-bold">Status <span className="text-danger">*</span></label>
                                            <select 
                                                className={`form-select ${errors.status ? 'is-invalid' : ''}`}
                                                value={data.status}
                                                onChange={e => setData('status', e.target.value)}
                                                required
                                            >
                                                <option value="scheduled">Scheduled</option>
                                                <option value="confirmed">Confirmed</option>
                                                <option value="completed">Completed</option>
                                                <option value="cancelled">Cancelled</option>
                                                <option value="no_show">No Show</option>
                                            </select>
                                            {errors.status && <div className="invalid-feedback">{errors.status}</div>}
                                        </div>

                                        <div className="col-12">
                                            <label className="form-label fw-bold">Reason for Visit <span className="text-danger">*</span></label>
                                            <textarea 
                                                className={`form-control ${errors.reason ? 'is-invalid' : ''}`}
                                                value={data.reason}
                                                onChange={e => setData('reason', e.target.value)}
                                                rows="3"
                                                required
                                            />
                                            {errors.reason && <div className="invalid-feedback">{errors.reason}</div>}
                                        </div>

                                        <div className="col-12">
                                            <label className="form-label fw-bold">Additional Notes</label>
                                            <textarea 
                                                className={`form-control ${errors.notes ? 'is-invalid' : ''}`}
                                                value={data.notes}
                                                onChange={e => setData('notes', e.target.value)}
                                                rows="3"
                                            />
                                            {errors.notes && <div className="invalid-feedback">{errors.notes}</div>}
                                        </div>

                                        <div className="col-12 mt-4 d-flex justify-content-end gap-2">
                                            <button type="button" onClick={() => reset()} className="btn btn-outline-secondary px-4">Clear</button>
                                            <button type="submit" disabled={processing} className="btn btn-primary px-4">
                                                {processing ? 'Saving...' : 'Create Appointment'}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <QuickPatientModal 
                show={showQuickAdd} 
                onClose={() => setShowQuickAdd(false)}
                onSuccess={(patient) => {
                    setData('patient_id', patient.value);
                    setQuickPatientLabel(patient.label);
                    setShowQuickAdd(false);
                }}
            />
        </AuthenticatedLayout>
    );
}
