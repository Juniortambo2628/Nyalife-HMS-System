import React, { useState } from 'react';
import axios from 'axios';

export default function QuickPatientModal({ isOpen, onClose, onSuccess }) {
    const [formData, setFormData] = useState({
        first_name: '',
        last_name: '',
        phone: '',
        gender: '',
        date_of_birth: '',
        email: '',
        emergency_name: '',
        emergency_contact: '',
    });
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});

    if (!isOpen) return null;

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setErrors({});

        try {
            const response = await axios.post(route('patients.quick-store'), formData);
            if (response.data.success) {
                onSuccess({
                    value: response.data.patient_id,
                    label: response.data.full_name
                });
                onClose();
            }
        } catch (err) {
            if (err.response && err.response.data.errors) {
                setErrors(err.response.data.errors);
            } else {
                alert('An error occurred while creating the patient.');
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="modal show d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 10000 }}>
            <div className="modal-dialog modal-dialog-centered">
                <div className="modal-content border-0 shadow-lg rounded-4">
                    <div className="modal-header bg-primary text-white p-4 rounded-top-4 border-0">
                        <h5 className="modal-title fw-bold">
                            <i className="fas fa-user-plus me-2"></i>Quick Register Patient
                        </h5>
                        <button type="button" className="btn-close btn-close-white" onClick={onClose}></button>
                    </div>
                    <form onSubmit={handleSubmit}>
                        <div className="modal-body p-4">
                            <div className="row g-3">
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">First Name</label>
                                    <input 
                                        type="text" 
                                        className={`form-control ${errors.first_name ? 'is-invalid' : ''}`} 
                                        required 
                                        value={formData.first_name}
                                        onChange={e => setFormData({...formData, first_name: e.target.value})}
                                    />
                                    {errors.first_name && <div className="invalid-feedback">{errors.first_name[0]}</div>}
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">Last Name</label>
                                    <input 
                                        type="text" 
                                        className={`form-control ${errors.last_name ? 'is-invalid' : ''}`} 
                                        required 
                                        value={formData.last_name}
                                        onChange={e => setFormData({...formData, last_name: e.target.value})}
                                    />
                                    {errors.last_name && <div className="invalid-feedback">{errors.last_name[0]}</div>}
                                </div>
                                <div className="col-12">
                                    <label className="form-label small fw-bold">Phone Number</label>
                                    <input 
                                        type="text" 
                                        className={`form-control ${errors.phone ? 'is-invalid' : ''}`} 
                                        required 
                                        value={formData.phone}
                                        onChange={e => setFormData({...formData, phone: e.target.value})}
                                    />
                                    {errors.phone && <div className="invalid-feedback">{errors.phone[0]}</div>}
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">Gender</label>
                                    <select 
                                        className={`form-select ${errors.gender ? 'is-invalid' : ''}`} 
                                        required
                                        value={formData.gender}
                                        onChange={e => setFormData({...formData, gender: e.target.value})}
                                    >
                                        <option value="">Select...</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                    {errors.gender && <div className="invalid-feedback">{errors.gender[0]}</div>}
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">Date of Birth</label>
                                    <input 
                                        type="date" 
                                        className={`form-control ${errors.date_of_birth ? 'is-invalid' : ''}`} 
                                        required 
                                        value={formData.date_of_birth}
                                        onChange={e => setFormData({...formData, date_of_birth: e.target.value})}
                                    />
                                    {errors.date_of_birth && <div className="invalid-feedback">{errors.date_of_birth[0]}</div>}
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">Next of Kin Name</label>
                                    <input 
                                        type="text" 
                                        className={`form-control ${errors.emergency_name ? 'is-invalid' : ''}`} 
                                        value={formData.emergency_name}
                                        onChange={e => setFormData({...formData, emergency_name: e.target.value})}
                                        placeholder="Full Name"
                                    />
                                    {errors.emergency_name && <div className="invalid-feedback">{errors.emergency_name[0]}</div>}
                                </div>
                                <div className="col-md-6">
                                    <label className="form-label small fw-bold">Next of Kin Phone</label>
                                    <input 
                                        type="text" 
                                        className={`form-control ${errors.emergency_contact ? 'is-invalid' : ''}`} 
                                        value={formData.emergency_contact}
                                        onChange={e => setFormData({...formData, emergency_contact: e.target.value})}
                                        placeholder="Phone Number"
                                    />
                                    {errors.emergency_contact && <div className="invalid-feedback">{errors.emergency_contact[0]}</div>}
                                </div>
                            </div>
                        </div>
                        <div className="modal-footer p-3 bg-light rounded-bottom-4 border-0">
                            <button type="button" className="btn btn-light rounded-pill px-4" onClick={onClose} disabled={loading}>Cancel</button>
                            <button type="submit" className="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" disabled={loading}>
                                {loading ? (
                                    <>
                                        <span className="spinner-border spinner-border-sm me-2"></span>
                                        Saving...
                                    </>
                                ) : 'Register & Select'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
