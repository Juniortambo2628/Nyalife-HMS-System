import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import DashboardSelect from '@/Components/DashboardSelect';
import QuickMedicationModal from '@/Components/QuickMedicationModal';
import { useState } from 'react';

export default function Create({ preselected_patient_id, preselected_patient_label, consultation_id, auth }) {
    const { data, setData, post, processing, errors } = useForm({
        patient_id: preselected_patient_id || '',
        consultation_id: consultation_id || '',
        prescription_date: new Date().toISOString().split('T')[0],
        items: [{ medicine_name: '', medication_id: '', dosage: '', frequency: '', duration: '' }],
        notes: '',
    });

    const [isQuickMedModalOpen, setIsQuickMedModalOpen] = useState(false);
    const [activeItemIndex, setActiveItemIndex] = useState(null);
    const [searchTerm, setSearchTerm] = useState('');

    const addItem = () => {
        setData('items', [...data.items, { medicine_name: '', dosage: '', frequency: '', duration: '' }]);
    };

    const removeItem = (index) => {
        const newItems = [...data.items];
        newItems.splice(index, 1);
        setData('items', newItems);
    };

    const handleItemChange = (index, field, value) => {
        const newItems = [...data.items];
        newItems[index][field] = value;
        setData('items', newItems);
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('prescriptions.store'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header="Create Prescription"
        >
            <Head title="New Prescription" />

            <div className="container-fluid pharmacy-page px-0">
                <div className="row mb-4">
                    <div className="col-12 d-flex justify-content-between align-items-center">
                        <h2 className="mb-0">New Prescription</h2>
                        <Link href={route('prescriptions.index')} className="btn btn-outline-secondary">
                            <i className="fas fa-arrow-left me-2"></i>Back to List
                        </Link>
                    </div>
                </div>

                <form onSubmit={submit} className="row">
                    <div className="col-lg-12">
                        <div className="card shadow-sm border-0 mb-4">
                            <div className="card-body p-4">
                                <div className="row g-3 mb-4">
                                    <div className="col-md-4">
                                        <label className="form-label fw-bold">Patient <span className="text-danger">*</span></label>
                                        <DashboardSelect 
                                            asyncUrl="/patients/search"
                                            value={data.patient_id}
                                            onChange={val => setData('patient_id', val)}
                                            initialLabel={preselected_patient_label}
                                            placeholder="Search Patients..."
                                            className={errors.patient_id ? 'is-invalid' : ''}
                                            disabled={!!preselected_patient_id}
                                        />
                                        {errors.patient_id && <div className="text-danger small mt-1">{errors.patient_id}</div>}
                                    </div>
                                    <div className="col-md-4">
                                        <label className="form-label fw-bold">Date <span className="text-danger">*</span></label>
                                        <input 
                                            type="date" 
                                            className="form-control"
                                            value={data.prescription_date}
                                            onChange={e => setData('prescription_date', e.target.value)}
                                            required
                                        />
                                    </div>
                                </div>

                                <h6 className="fw-bold mb-3">Medications</h6>
                                {data.items.map((item, index) => (
                                    <div key={index} className="row g-2 mb-3 align-items-end bg-light p-3 rounded position-relative">
                                        {data.items.length > 1 && (
                                            <button type="button" onClick={() => removeItem(index)} className="btn btn-sm btn-link text-danger position-absolute top-0 end-0 mt-1 me-1">
                                                <i className="fas fa-times"></i>
                                            </button>
                                        )}
                                        <div className="col-md-4">
                                            <label className="form-label small">Medicine Name</label>
                                            <DashboardSelect 
                                                asyncUrl="/medications/search"
                                                value={item.medication_id}
                                                onChange={(val, opt) => {
                                                    const newItems = [...data.items];
                                                    newItems[index].medication_id = val;
                                                    newItems[index].medicine_name = opt.label;
                                                    setData('items', newItems);
                                                }}
                                                initialLabel={item.medicine_name}
                                                placeholder="Search Medicine..."
                                                onAddNew={() => {
                                                    setActiveItemIndex(index);
                                                    setIsQuickMedModalOpen(true);
                                                }}
                                                addNewLabel="Medicine not found? Add to Catalog"
                                            />
                                        </div>
                                        <div className="col-md-3">
                                            <label className="form-label small">Dosage</label>
                                            <input 
                                                type="text" 
                                                className="form-control"
                                                placeholder="e.g. 500mg"
                                                value={item.dosage}
                                                onChange={e => handleItemChange(index, 'dosage', e.target.value)}
                                                required
                                            />
                                        </div>
                                        <div className="col-md-3">
                                            <label className="form-label small">Frequency</label>
                                            <input 
                                                type="text" 
                                                className="form-control"
                                                placeholder="e.g. 3 times daily"
                                                value={item.frequency}
                                                onChange={e => handleItemChange(index, 'frequency', e.target.value)}
                                                required
                                            />
                                        </div>
                                        <div className="col-md-2">
                                            <label className="form-label small">Duration</label>
                                            <input 
                                                type="text" 
                                                className="form-control"
                                                placeholder="e.g. 7 days"
                                                value={item.duration}
                                                onChange={e => handleItemChange(index, 'duration', e.target.value)}
                                                required
                                            />
                                        </div>
                                    </div>
                                ))}

                                <button type="button" onClick={addItem} className="btn btn-outline-primary btn-sm mt-2">
                                    <i className="fas fa-plus me-2"></i>Add Another Medicine
                                </button>

                                <div className="mt-4">
                                    <label className="form-label fw-bold">Pharmacist Instructions / Notes</label>
                                    <textarea 
                                        className="form-control" 
                                        rows="3" 
                                        value={data.notes}
                                        onChange={e => setData('notes', e.target.value)}
                                        placeholder="Add any specific instructions for the pharmacist or patient..."
                                    />
                                </div>

                                <div className="mt-4 d-flex justify-content-end gap-2">
                                    <button type="submit" disabled={processing} className="btn btn-primary px-5 shadow-sm">
                                        {processing ? 'Saving...' : 'Create Prescription'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <QuickMedicationModal 
                isOpen={isQuickMedModalOpen}
                onClose={() => setIsQuickMedModalOpen(false)}
                onSuccess={(newMed) => {
                    const newItems = [...data.items];
                    newItems[activeItemIndex].medication_id = newMed.value;
                    newItems[activeItemIndex].medicine_name = newMed.label;
                    setData('items', newItems);
                }}
            />
        </AuthenticatedLayout>
    );
}
