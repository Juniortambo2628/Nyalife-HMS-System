import React, { useState, useEffect } from 'react';
import { useForm, Link } from '@inertiajs/react';
import QuickPatientModal from '@/Components/QuickPatientModal';

export default function ConsultationForm({ consultation, patients, doctors, appointment, submitRoute, isEdit = false }) {
    const [showQuickPatient, setShowQuickPatient] = useState(false);
    const [localPatients, setLocalPatients] = useState(patients);

    const handleQuickPatientSuccess = (newPatient) => {
        const option = { value: newPatient.patient_id, label: newPatient.full_name };
        setLocalPatients([option, ...localPatients]);
        setData('patient_id', newPatient.patient_id);
    };
    const { data, setData, post, put, processing, errors } = useForm({
        patient_id: consultation?.patient_id || appointment?.patient_id || '',
        doctor_id: consultation?.doctor_id || '',
        consultation_date: consultation?.consultation_date || new Date().toISOString().slice(0, 16),
        status: consultation?.consultation_status || 'scheduled',
        is_walk_in: consultation?.is_walk_in || false,
        vital_signs: consultation?.vital_signs || {
             blood_pressure: '',
             pulse: '',
             temperature: '',
             respiratory_rate: '',
             oxygen_saturation: '',
             pain_level: '',
             height: '',
             weight: '',
             bmi: ''
        },
        chief_complaint: consultation?.chief_complaint || '',
        history_present_illness: consultation?.history_present_illness || '',
        past_medical_history: consultation?.past_medical_history || '',
        family_history: consultation?.family_history || '',
        social_history: consultation?.social_history || '',
        obstetric_history: consultation?.obstetric_history || '',
        gynecological_history: consultation?.gynecological_history || '',
        menstrual_history: consultation?.menstrual_history || '',
        contraceptive_history: consultation?.contraceptive_history || '',
        sexual_history: consultation?.sexual_history || '',
        review_of_systems: consultation?.review_of_systems || '',
        physical_examination: consultation?.physical_examination || '',
        general_examination: consultation?.general_examination || '',
        systems_examination: consultation?.systems_examination || '',
        diagnosis: consultation?.diagnosis || '',
        diagnosis_confidence: consultation?.diagnosis_confidence || '',
        differential_diagnosis: consultation?.differential_diagnosis || '',
        diagnostic_plan: consultation?.diagnostic_plan || '',
        treatment_plan: consultation?.treatment_plan || '',
        follow_up_instructions: consultation?.follow_up_instructions || '',
        notes: consultation?.notes || '',
        clinical_summary: consultation?.clinical_summary || '',
        parity: consultation?.parity || '',
        current_pregnancy: consultation?.current_pregnancy || '',
        past_obstetric: consultation?.past_obstetric || '',
        surgical_history: consultation?.surgical_history || '',
        cervical_screening: consultation?.cervical_screening || '',
    });

    const calculateBMI = () => {
        const height = parseFloat(data.vital_signs.height);
        const weight = parseFloat(data.vital_signs.weight);
        if (height > 0 && weight > 0) {
            const heightInMeters = height / 100;
            const bmi = (weight / (heightInMeters * heightInMeters)).toFixed(1);
            setData('vital_signs', { ...data.vital_signs, bmi: bmi });
        }
    };

    useEffect(() => {
        calculateBMI();
    }, [data.vital_signs.height, data.vital_signs.weight]);

    const handleSubmit = (e) => {
        e.preventDefault();
        if (isEdit) {
            put(submitRoute);
        } else {
            post(submitRoute);
        }
    };

    const handleVitalChange = (field, value) => {
        setData('vital_signs', { ...data.vital_signs, [field]: value });
    };

    // Shared input class string for consistency (matches cards, gray bg, black text)
    const inputClass = "mt-1 block w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-pink-500 focus:ring-pink-500 text-gray-900 shadow-sm py-3.5 px-4";
    const labelClass = "block font-bold text-sm text-gray-900 mb-1";
    const sectionHeaderClass = "text-lg font-bold text-gray-800 flex items-center mb-4 pb-2 border-b border-gray-100";

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            {/* Basic Information */}
            <div className="bg-white rounded-2xl shadow-sm p-6 md:p-8">
                <h3 className={sectionHeaderClass}>
                    <i className="fas fa-id-card me-2 text-pink-500"></i> Basic Information
                </h3>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                     <div className="relative">
                        <div className="flex justify-between items-center mb-1">
                            <label htmlFor="patient_id" className={labelClass + " mb-0"}>Patient</label>
                            <button 
                                type="button" 
                                onClick={() => setShowQuickPatient(true)}
                                className="text-xs font-bold text-pink-500 hover:text-pink-700 transition-colors"
                            >
                                <i className="fas fa-plus-circle me-1"></i> Add New
                            </button>
                        </div>
                        <select
                            id="patient_id"
                            className={inputClass}
                            value={data.patient_id}
                            onChange={(e) => setData('patient_id', e.target.value)}
                            required
                        >
                            <option value="">Select Patient</option>
                            {localPatients.map((p) => (
                                <option key={p.value} value={p.value}>{p.label}</option>
                            ))}
                        </select>
                        {errors.patient_id && <p className="mt-2 text-sm text-red-600">{errors.patient_id}</p>}
                    </div>

                    <div>
                        <label htmlFor="doctor_id" className={labelClass}>Doctor</label>
                         <select
                            id="doctor_id"
                            className={inputClass}
                            value={data.doctor_id}
                            onChange={(e) => setData('doctor_id', e.target.value)}
                            required
                        >
                            <option value="">Select Doctor</option>
                            {doctors.map((d) => (
                                <option key={d.value} value={d.value}>{d.label}</option>
                            ))}
                        </select>
                        {errors.doctor_id && <p className="mt-2 text-sm text-red-600">{errors.doctor_id}</p>}
                    </div>

                    <div>
                        <label htmlFor="consultation_date" className={labelClass}>Date</label>
                        <input
                            id="consultation_date"
                            type="datetime-local"
                            className={inputClass}
                            value={data.consultation_date}
                            onChange={(e) => setData('consultation_date', e.target.value)}
                            required
                        />
                         {errors.consultation_date && <p className="mt-2 text-sm text-red-600">{errors.consultation_date}</p>}
                    </div>
                    
                    <div>
                        <label htmlFor="status" className={labelClass}>Status</label>
                        <select
                             id="status"
                             className={inputClass}
                             value={data.status}
                             onChange={(e) => setData('status', e.target.value)}
                        >
                            <option value="scheduled">Scheduled</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        {errors.status && <p className="mt-2 text-sm text-red-600">{errors.status}</p>}
                    </div>
                </div>
                
                 <div className="mt-4 flex items-center">
                    <input
                        id="is_walk_in"
                        type="checkbox"
                        className="rounded border-gray-300 text-pink-500 shadow-sm focus:ring-pink-500 h-5 w-5"
                        checked={data.is_walk_in}
                        onChange={(e) => setData('is_walk_in', e.target.checked)}
                    />
                    <label htmlFor="is_walk_in" className="ml-2 block text-sm font-bold text-gray-700">
                        Walk-in Consultation
                    </label>
                </div>
            </div>

            {/* Vital Signs */}
            <div className="bg-white rounded-2xl shadow-sm p-6 md:p-8">
                <h3 className={sectionHeaderClass}>
                    <i className="fas fa-heartbeat me-2 text-pink-500"></i> Vital Signs
                </h3>
                <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                     <div>
                        <label className={labelClass}>BP (mmHg)</label>
                        <input
                            value={data.vital_signs.blood_pressure}
                            onChange={(e) => handleVitalChange('blood_pressure', e.target.value)}
                            placeholder="120/80"
                            className={`${inputClass} text-center font-bold`}
                        />
                     </div>
                     <div>
                        <label className={labelClass}>Pulse (bpm)</label>
                        <input
                            type="number"
                            value={data.vital_signs.pulse}
                            onChange={(e) => handleVitalChange('pulse', e.target.value)}
                            className={`${inputClass} text-center font-bold`}
                        />
                     </div>
                     <div>
                        <label className={labelClass}>Temp (°C)</label>
                        <input
                            type="number" step="0.1"
                            value={data.vital_signs.temperature}
                            onChange={(e) => handleVitalChange('temperature', e.target.value)}
                            className={`${inputClass} text-center font-bold`}
                        />
                     </div>
                     <div>
                        <label className={labelClass}>Resp Rate</label>
                        <input
                            type="number"
                            value={data.vital_signs.respiratory_rate}
                            onChange={(e) => handleVitalChange('respiratory_rate', e.target.value)}
                            className={`${inputClass} text-center font-bold`}
                        />
                     </div>
                     <div>
                        <label className={labelClass}>SpO2 (%)</label>
                         <input
                            type="number"
                            value={data.vital_signs.oxygen_saturation}
                            onChange={(e) => handleVitalChange('oxygen_saturation', e.target.value)}
                            className={`${inputClass} text-center font-bold`}
                        />
                     </div>
                      <div>
                        <label className={labelClass}>Pain (0-10)</label>
                         <input
                            type="number" max="10"
                            value={data.vital_signs.pain_level}
                            onChange={(e) => handleVitalChange('pain_level', e.target.value)}
                            className={`${inputClass} text-center font-bold`}
                        />
                     </div>
                     <div>
                        <label className={labelClass}>Height (cm)</label>
                         <input
                            type="number"
                            value={data.vital_signs.height}
                            onChange={(e) => handleVitalChange('height', e.target.value)}
                            className={`${inputClass} text-center`}
                        />
                     </div>
                     <div>
                        <label className={labelClass}>Weight (kg)</label>
                         <input
                            type="number" step="0.1"
                            value={data.vital_signs.weight}
                            onChange={(e) => handleVitalChange('weight', e.target.value)}
                            className={`${inputClass} text-center`}
                        />
                     </div>
                     <div>
                        <label className={labelClass}>BMI</label>
                         <input
                            value={data.vital_signs.bmi}
                            readOnly
                            className={`${inputClass} bg-gray-100 text-center font-black text-pink-500`}
                        />
                     </div>
                </div>
            </div>

            {/* Chief Complaint */}
            <div className="bg-white rounded-2xl shadow-sm p-6 md:p-8">
                <h3 className={sectionHeaderClass}>
                    <i className="fas fa-bullhorn me-2 text-pink-500"></i> Chief Complaint
                </h3>
                <label className={labelClass}>Reason for Visit *</label>
                <textarea
                    className={`${inputClass} h-32 rounded-2xl`}
                    value={data.chief_complaint}
                    onChange={(e) => setData('chief_complaint', e.target.value)}
                    required
                    placeholder="What brings the patient in today?"
                />
                {errors.chief_complaint && <p className="mt-2 text-sm text-red-600">{errors.chief_complaint}</p>}
            </div>

            {/* Clinical History */}
            <div className="bg-white rounded-2xl shadow-sm p-6 md:p-8">
                <h3 className={sectionHeaderClass}>
                    <i className="fas fa-history me-2 text-pink-500"></i> Clinical History
                </h3>
                <div className="space-y-6">
                    <div>
                        <label className={labelClass}>History of Presenting Illness</label>
                        <textarea
                            className={`${inputClass} h-32 rounded-2xl`}
                            value={data.history_present_illness}
                            onChange={(e) => setData('history_present_illness', e.target.value)}
                        />
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                         <div>
                            <label className={labelClass}>Past Medical History</label>
                            <textarea
                                className={`${inputClass} h-24 rounded-2xl`}
                                value={data.past_medical_history}
                                onChange={(e) => setData('past_medical_history', e.target.value)}
                            />
                        </div>
                         <div>
                            <label className={labelClass}>Surgical History</label>
                            <textarea
                                className={`${inputClass} h-24 rounded-2xl`}
                                value={data.surgical_history}
                                onChange={(e) => setData('surgical_history', e.target.value)}
                            />
                        </div>
                    </div>

                     <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                         <div>
                            <label className={labelClass}>Family History</label>
                            <textarea
                                className={`${inputClass} h-24 rounded-2xl`}
                                value={data.family_history}
                                onChange={(e) => setData('family_history', e.target.value)}
                            />
                        </div>
                         <div>
                            <label className={labelClass}>Social History</label>
                            <textarea
                                className={`${inputClass} h-24 rounded-2xl`}
                                value={data.social_history}
                                onChange={(e) => setData('social_history', e.target.value)}
                            />
                        </div>
                    </div>
                </div>
            </div>
            
             {/* O&G History */}
             <div className="bg-white rounded-2xl shadow-sm p-6 md:p-8">
                <h3 className={sectionHeaderClass}>
                    <i className="fas fa-female me-2 text-pink-500"></i> Gynaecological & Obstetric History
                </h3>
                <div className="space-y-6">
                     <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                         <div>
                            <label className={labelClass}>Menstrual History</label>
                            <textarea
                                className={`${inputClass} h-16 rounded-2xl`}
                                value={data.menstrual_history}
                                onChange={(e) => setData('menstrual_history', e.target.value)}
                                 placeholder="LMP, Cycle, Flow..."
                            />
                        </div>
                         <div>
                            <label className={labelClass}>Contraceptive History</label>
                            <textarea
                                className={`${inputClass} h-16 rounded-2xl`}
                                value={data.contraceptive_history}
                                onChange={(e) => setData('contraceptive_history', e.target.value)}
                            />
                        </div>
                          <div>
                            <label className={labelClass}>Sexual History</label>
                            <textarea
                                className={`${inputClass} h-16 rounded-2xl`}
                                value={data.sexual_history}
                                onChange={(e) => setData('sexual_history', e.target.value)}
                            />
                        </div>
                         <div>
                            <label className={labelClass}>Cervical Screening</label>
                            <textarea
                                className={`${inputClass} h-16 rounded-2xl`}
                                value={data.cervical_screening}
                                onChange={(e) => setData('cervical_screening', e.target.value)}
                            />
                        </div>
                    </div>
                     <div className="grid grid-cols-1 md:grid-cols-3 gap-6 border-t pt-6">
                         <div>
                            <label className={labelClass}>Parity (Pregnancies)</label>
                            <input
                                className={inputClass}
                                value={data.parity}
                                onChange={(e) => setData('parity', e.target.value)}
                                placeholder="G3P2A1"
                            />
                        </div>
                         <div className="col-span-2">
                            <label className={labelClass}>Obstetric History (Detailed)</label>
                            <textarea
                                className={`${inputClass} h-16 rounded-2xl`}
                                value={data.obstetric_history}
                                onChange={(e) => setData('obstetric_history', e.target.value)}
                            />
                        </div>
                     </div>
                </div>
            </div>

            {/* Examination */}
            <div className="bg-white rounded-2xl shadow-sm p-6 md:p-8">
                <h3 className={sectionHeaderClass}>
                    <i className="fas fa-stethoscope me-2 text-pink-500"></i> Examination
                </h3>
                <div className="space-y-6">
                    <div>
                        <label className={labelClass}>Review of Systems</label>
                        <textarea
                            className={`${inputClass} h-24 rounded-2xl`}
                            value={data.review_of_systems}
                            onChange={(e) => setData('review_of_systems', e.target.value)}
                        />
                    </div>
                     <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                         <div>
                            <label className={labelClass}>General Examination</label>
                            <textarea
                                className={`${inputClass} h-24 rounded-2xl`}
                                value={data.general_examination}
                                onChange={(e) => setData('general_examination', e.target.value)}
                            />
                        </div>
                         <div>
                            <label className={labelClass}>Systems Examination</label>
                            <textarea
                                className={`${inputClass} h-24 rounded-2xl`}
                                value={data.systems_examination}
                                onChange={(e) => setData('systems_examination', e.target.value)}
                            />
                        </div>
                    </div>
                     <div>
                        <label className={labelClass}>Detailed Physical Examination</label>
                        <textarea
                            className={`${inputClass} h-32 rounded-2xl`}
                            value={data.physical_examination}
                            onChange={(e) => setData('physical_examination', e.target.value)}
                        />
                    </div>
                </div>
            </div>

             {/* Diagnosis & Plan */}
            <div className="bg-white rounded-2xl shadow-sm p-6 md:p-8">
                <h3 className={sectionHeaderClass}>
                    <i className="fas fa-clipboard-check me-2 text-green-500"></i> Diagnosis & Plan
                </h3>
                <div className="space-y-8">
                     <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                         <div className="col-span-2">
                            <label className={labelClass}>Primary Diagnosis</label>
                             <textarea
                                className={`${inputClass} h-24 font-bold border-l-4 border-l-green-500 rounded-2xl`}
                                value={data.diagnosis}
                                onChange={(e) => setData('diagnosis', e.target.value)}
                            />
                        </div>
                         <div>
                            <label className={labelClass}>Confidence</label>
                             <select
                                 className={inputClass}
                                 value={data.diagnosis_confidence}
                                 onChange={(e) => setData('diagnosis_confidence', e.target.value)}
                            >
                                <option value="">Select</option>
                                <option value="high">High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                                <option value="provisional">Provisional</option>
                            </select>
                        </div>
                    </div>
                     
                     <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                         <div>
                            <label className={labelClass}>Differential Diagnosis</label>
                            <textarea
                                className={`${inputClass} h-32 rounded-2xl`}
                                value={data.differential_diagnosis}
                                onChange={(e) => setData('differential_diagnosis', e.target.value)}
                            />
                        </div>
                         <div>
                            <label className={labelClass}>Diagnostic Plan</label>
                            <textarea
                                className={`${inputClass} h-32 rounded-2xl`}
                                value={data.diagnostic_plan}
                                onChange={(e) => setData('diagnostic_plan', e.target.value)}
                            />
                        </div>
                    </div>

                     <div>
                        <label className={labelClass}>Treatment Plan</label>
                        <textarea
                            className={`${inputClass} h-48 border-l-4 border-l-pink-500 rounded-2xl`}
                            value={data.treatment_plan}
                            onChange={(e) => setData('treatment_plan', e.target.value)}
                        />
                    </div>
                     
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                         <div>
                            <label className={labelClass}>Follow-up Instructions</label>
                            <textarea
                                className={`${inputClass} h-24 rounded-2xl`}
                                value={data.follow_up_instructions}
                                onChange={(e) => setData('follow_up_instructions', e.target.value)}
                            />
                        </div>
                         <div>
                            <label className={labelClass}>Clinical Summary</label>
                            <textarea
                                className={`${inputClass} h-24 rounded-2xl`}
                                value={data.clinical_summary}
                                onChange={(e) => setData('clinical_summary', e.target.value)}
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div className="flex items-center justify-end gap-4 p-6 bg-white rounded-2xl shadow-sm">
                <button type="button" onClick={() => window.history.back()} className="px-6 py-2.5 rounded-full border border-gray-300 text-gray-700 font-bold hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                {isEdit && data.status === 'in_progress' && consultation && (
                    <Link href={`/lab/requests/create?consultation_id=${consultation.consultation_id}&patient_id=${consultation.patient_id}`} className="px-6 py-2.5 rounded-full border-2 border-pink-500 text-pink-600 font-bold hover:bg-pink-50 transition-colors">
                        <i className="fas fa-flask me-2"></i> Request Labs
                    </Link>
                )}
                <button type="submit" className="px-8 py-3 rounded-full bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold shadow-lg hover:shadow-xl transform transition hover:-translate-y-0.5 disabled:opacity-75 disabled:cursor-not-allowed" disabled={processing}>
                    <i className="fas fa-save me-2"></i> {isEdit ? 'Update Consultation' : 'Save Consultation Record'}
                </button>
            </div>
            <QuickPatientModal 
                show={showQuickPatient} 
                onClose={() => setShowQuickPatient(false)}
                onSuccess={handleQuickPatientSuccess}
            />
        </form>
    );
}
