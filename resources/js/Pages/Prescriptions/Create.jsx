import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
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
        <AuthenticatedLayout header="New Prescription">
            <Head title="Create Prescription" />

            <PageHeader 
                title="Prescription Form"
                breadcrumbs={[
                    { label: 'Pharmacy', url: route('prescriptions.index') },
                    { label: 'New Prescription', active: true }
                ]}
            />

            <div className="container-fluid px-0">
                <form onSubmit={submit} className="row g-4">
                    <div className="col-lg-12">
                        <div className="card shadow-sm border-0 rounded-3xl overflow-hidden mb-4 bg-white shadow-hover">
                            <div className="card-header bg-white py-4 px-4 border-bottom-0">
                                <h6 className="mb-0 fw-extrabold text-primary extra-small text-uppercase tracking-widest">
                                    <i className="fas fa-prescription-bottle-alt me-2"></i>Regimen Details
                                </h6>
                            </div>
                            <div className="card-body p-4 pt-0">
                                <div className="row g-4 mb-5">
                                    <div className="col-md-6">
                                        <label className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-2 d-block">Patient Target</label>
                                        <DashboardSelect 
                                            asyncUrl="/patients/search"
                                            value={data.patient_id}
                                            onChange={val => setData('patient_id', val)}
                                            initialLabel={preselected_patient_label}
                                            placeholder="Search Patients..."
                                            className={errors.patient_id ? 'is-invalid' : ''}
                                            disabled={!!preselected_patient_id}
                                        />
                                        {errors.patient_id && <div className="text-danger extra-small fw-bold mt-1">{errors.patient_id}</div>}
                                    </div>
                                    <div className="col-md-6">
                                        <label className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-2 d-block">Prescription Date</label>
                                        <input 
                                            type="date" 
                                            className="form-control form-control-lg bg-light border-0 rounded-xl fw-bold"
                                            value={data.prescription_date}
                                            onChange={e => setData('prescription_date', e.target.value)}
                                            required
                                        />
                                    </div>
                                </div>

                                <h6 className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-4 border-bottom border-gray-50 pb-2">Medication Schedule</h6>
                                {data.items.map((item, index) => (
                                    <div key={index} className="p-4 rounded-2xl bg-gray-50 border border-gray-100 mb-4 position-relative animate-in fade-in slide-in-from-bottom-2 duration-300">
                                        {data.items.length > 1 && (
                                            <button type="button" onClick={() => removeItem(index)} className="btn btn-sm btn-light text-danger rounded-circle position-absolute top-0 end-0 mt-3 me-3 avatar-xs d-flex align-items-center justify-content-center shadow-sm">
                                                <i className="fas fa-times extra-small"></i>
                                            </button>
                                        )}
                                        <div className="row g-3">
                                            <div className="col-md-4">
                                                <label className="extra-small fw-bold text-muted text-uppercase mb-1">Medicine</label>
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
                                                    placeholder="Search..."
                                                    onAddNew={() => {
                                                        setActiveItemIndex(index);
                                                        setIsQuickMedModalOpen(true);
                                                    }}
                                                    addNewLabel="Medicine not found? Add to Catalog"
                                                />
                                            </div>
                                            <div className="col-md-3">
                                                <label className="extra-small fw-bold text-muted text-uppercase mb-1">Dosage</label>
                                                <input 
                                                    type="text" 
                                                    className="form-control bg-white border-0 rounded-xl small fw-bold"
                                                    placeholder="e.g. 500mg"
                                                    value={item.dosage}
                                                    onChange={e => handleItemChange(index, 'dosage', e.target.value)}
                                                    required
                                                />
                                            </div>
                                            <div className="col-md-3">
                                                <label className="extra-small fw-bold text-muted text-uppercase mb-1">Frequency</label>
                                                <input 
                                                    type="text" 
                                                    className="form-control bg-white border-0 rounded-xl small fw-bold"
                                                    placeholder="e.g. 3 times daily"
                                                    value={item.frequency}
                                                    onChange={e => handleItemChange(index, 'frequency', e.target.value)}
                                                    required
                                                />
                                            </div>
                                            <div className="col-md-2">
                                                <label className="extra-small fw-bold text-muted text-uppercase mb-1">Duration</label>
                                                <input 
                                                    type="text" 
                                                    className="form-control bg-white border-0 rounded-xl small fw-bold"
                                                    placeholder="e.g. 7 days"
                                                    value={item.duration}
                                                    onChange={e => handleItemChange(index, 'duration', e.target.value)}
                                                    required
                                                />
                                            </div>
                                        </div>
                                    </div>
                                ))}

                                <button type="button" onClick={addItem} className="btn btn-outline-primary btn-sm rounded-pill px-4 fw-extrabold extra-small tracking-widest py-2.5">
                                    <i className="fas fa-plus me-2"></i>ADD ANOTHER MEDICINE
                                </button>

                                <div className="mt-5 pt-4 border-top border-gray-50">
                                    <label className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-3 d-block">Pharmacist Instructions / Notes</label>
                                    <textarea 
                                        className="form-control bg-light border-0 rounded-2xl p-4 small fw-medium" 
                                        rows="3" 
                                        value={data.notes}
                                        onChange={e => setData('notes', e.target.value)}
                                        placeholder="Add any specific instructions for the pharmacist or patient..."
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <UnifiedToolbar 
                        actions={[
                            { 
                                label: 'CREATE PRESCRIPTION', 
                                icon: 'fa-check-circle', 
                                onClick: submit,
                                color: 'success'
                            },
                            { 
                                label: 'DISCARD', 
                                icon: 'fa-times', 
                                href: route('prescriptions.index'),
                                color: 'gray'
                            }
                        ]}
                    />
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
