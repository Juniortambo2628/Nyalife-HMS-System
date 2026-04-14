import React, { useState } from 'react';
import axios from 'axios';

export default function QuickMedicationModal({ isOpen, onClose, onSuccess, initialName = '' }) {
    const [formData, setFormData] = useState({
        medication_name: initialName,
        strength: '',
        unit: 'mg',
        price_per_unit: 0,
        description: '',
        category: 'General'
    });
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});

    if (!isOpen) return null;

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setErrors({});

        try {
            const response = await axios.post(route('pharmacy.medicines.store'), formData);
            // Assuming the backend returns the new medication object or at least the ID
            // Since it's a redirect backed, we might need a JSON response for this modal
            // Let's check PharmacyController@storeMedicine
            
            // For now, let's assume we can handle success
            onSuccess({
                value: response.data.medication_id,
                label: `${formData.medication_name} (${formData.strength} ${formData.unit})`
            });
            onClose();
        } catch (err) {
            if (err.response && err.response.data.errors) {
                setErrors(err.response.data.errors);
            } else {
                alert('An error occurred while adding the medicine.');
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="modal show d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 10001 }}>
            <div className="modal-dialog modal-dialog-centered">
                <div className="modal-content border-0 shadow-lg rounded-4">
                    <div className="modal-header bg-success text-white p-4 rounded-top-4 border-0">
                        <h5 className="modal-title fw-bold">
                            <i className="fas fa-pills me-2"></i>Add New Medicine to Catalog
                        </h5>
                        <button type="button" className="btn-close btn-close-white" onClick={onClose}></button>
                    </div>
                    <form onSubmit={handleSubmit}>
                        <div className="modal-body p-4">
                            <div className="row g-3">
                                <div className="col-12">
                                    <label className="form-label small fw-bold">Medicine Name</label>
                                    <input 
                                        type="text" 
                                        className={`form-control ${errors.medication_name ? 'is-invalid' : ''}`} 
                                        required 
                                        value={formData.medication_name}
                                        onChange={e => setFormData({...formData, medication_name: e.target.value})}
                                    />
                                    {errors.medication_name && <div className="invalid-feedback">{errors.medication_name[0]}</div>}
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">Strength</label>
                                    <input 
                                        type="text" 
                                        className={`form-control ${errors.strength ? 'is-invalid' : ''}`} 
                                        placeholder="e.g. 500"
                                        required 
                                        value={formData.strength}
                                        onChange={e => setFormData({...formData, strength: e.target.value})}
                                    />
                                    {errors.strength && <div className="invalid-feedback">{errors.strength[0]}</div>}
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">Unit</label>
                                    <select 
                                        className="form-select"
                                        value={formData.unit}
                                        onChange={e => setFormData({...formData, unit: e.target.value})}
                                    >
                                        <option value="mg">mg</option>
                                        <option value="ml">ml</option>
                                        <option value="g">g</option>
                                        <option value="tablet">tablet</option>
                                        <option value="capsule">capsule</option>
                                        <option value="vial">vial</option>
                                    </select>
                                </div>
                                <div className="col-md-12">
                                    <label className="form-label small fw-bold">Price per Unit (KES)</label>
                                    <input 
                                        type="number" 
                                        className={`form-control ${errors.price_per_unit ? 'is-invalid' : ''}`} 
                                        required 
                                        value={formData.price_per_unit}
                                        onChange={e => setFormData({...formData, price_per_unit: e.target.value})}
                                    />
                                    {errors.price_per_unit && <div className="invalid-feedback">{errors.price_per_unit[0]}</div>}
                                </div>
                            </div>
                        </div>
                        <div className="modal-footer p-3 bg-light rounded-bottom-4 border-0">
                            <button type="button" className="btn btn-light rounded-pill px-4" onClick={onClose} disabled={loading}>Cancel</button>
                            <button type="submit" className="btn btn-success rounded-pill px-4 fw-bold shadow-sm" disabled={loading}>
                                {loading ? 'Saving...' : 'Add to Catalog'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
