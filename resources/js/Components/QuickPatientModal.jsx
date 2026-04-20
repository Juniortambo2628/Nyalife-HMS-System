import React, { useState } from 'react';
import axios from 'axios';
import Modal from '@/Components/Modal';

export default function QuickPatientModal({ show, onClose, onSuccess }) {
    const [formData, setFormData] = useState({
        first_name: '',
        last_name: '',
        phone: '',
        gender: '',
        date_of_birth: '',
        email: '',
        emergency_name: '',
        emergency_contact: '',
        blood_group: '',
    });
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setErrors({});

        try {
            const response = await axios.post(route('patients.quick-store'), formData);
            if (response.data.success) {
                onSuccess({
                    value: response.data.patient_id,
                    label: response.data.full_name,
                    gender: response.data.gender
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
        <Modal show={show} onClose={onClose} maxWidth="2xl">
            {/* Modal Header */}
            <div className="bg-gradient-to-br from-pink-500 via-pink-600 to-pink-700 p-8 flex justify-between items-center text-white relative overflow-hidden">
                <div className="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                <div className="relative z-10">
                    <h5 className="text-2xl font-bold flex items-center m-0 tracking-tight">
                        <div className="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mr-4 backdrop-blur-md">
                            <i className="fas fa-user-plus text-lg"></i>
                        </div>
                        Quick Register Patient
                    </h5>
                    <p className="text-pink-100 text-xs mt-1 ml-14 opacity-80 uppercase tracking-widest font-semibold">Immediate Walk-in Registration</p>
                </div>
                <button 
                    onClick={onClose}
                    className="relative z-10 text-white/80 hover:text-white hover:bg-white/20 rounded-full p-2.5 transition-all duration-300"
                >
                    <i className="fas fa-times text-lg"></i>
                </button>
            </div>
            
            {/* Modal Body */}
            <form onSubmit={handleSubmit} className="p-8 bg-white">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    {/* First Name */}
                    <div className="group">
                        <label className="block text-[0.7rem] font-bold text-gray-500 uppercase tracking-[0.1em] mb-2 px-1 transition-colors group-focus-within:text-pink-600">First Name</label>
                        <input 
                            type="text" 
                            className={`w-full px-5 py-3.5 rounded-2xl border ${errors.first_name ? 'border-red-300 bg-red-50/30' : 'border-gray-200 bg-white'} focus:ring-4 focus:ring-pink-100 focus:border-pink-500 focus:bg-white transition-all outline-none text-gray-800 font-medium`}
                            required 
                            placeholder="e.g. Jane"
                            value={formData.first_name}
                            onChange={e => setFormData({...formData, first_name: e.target.value})}
                        />
                        {errors.first_name && <p className="text-red-500 text-[0.65rem] font-bold mt-1.5 px-1 uppercase tracking-wider">{errors.first_name[0]}</p>}
                    </div>

                    {/* Last Name */}
                    <div className="group">
                        <label className="block text-[0.7rem] font-bold text-gray-500 uppercase tracking-[0.1em] mb-2 px-1 transition-colors group-focus-within:text-pink-600">Last Name</label>
                        <input 
                            type="text" 
                            className={`w-full px-5 py-3.5 rounded-2xl border ${errors.last_name ? 'border-red-300 bg-red-50/30' : 'border-gray-200 bg-white'} focus:ring-4 focus:ring-pink-100 focus:border-pink-500 focus:bg-white transition-all outline-none text-gray-800 font-medium`}
                            required 
                            placeholder="e.g. Doe"
                            value={formData.last_name}
                            onChange={e => setFormData({...formData, last_name: e.target.value})}
                        />
                        {errors.last_name && <p className="text-red-500 text-[0.65rem] font-bold mt-1.5 px-1 uppercase tracking-wider">{errors.last_name[0]}</p>}
                    </div>

                    {/* Phone Number */}
                    <div className="group">
                        <label className="block text-[0.7rem] font-bold text-gray-500 uppercase tracking-[0.1em] mb-2 px-1 transition-colors group-focus-within:text-pink-600">Phone Number</label>
                        <input 
                            type="text" 
                            className={`w-full px-5 py-3.5 rounded-2xl border ${errors.phone ? 'border-red-300 bg-red-50/30' : 'border-gray-200 bg-white'} focus:ring-4 focus:ring-pink-100 focus:border-pink-500 focus:bg-white transition-all outline-none text-gray-800 font-medium`}
                            required 
                            placeholder="+254..."
                            value={formData.phone}
                            onChange={e => setFormData({...formData, phone: e.target.value})}
                        />
                        {errors.phone && <p className="text-red-500 text-[0.65rem] font-bold mt-1.5 px-1 uppercase tracking-wider">{errors.phone[0]}</p>}
                    </div>

                    {/* Gender */}
                    <div className="group">
                        <label className="block text-[0.7rem] font-bold text-gray-500 uppercase tracking-[0.1em] mb-2 px-1 transition-colors group-focus-within:text-pink-600">Gender</label>
                        <select 
                            className={`w-full px-5 py-3.5 rounded-2xl border ${errors.gender ? 'border-red-300 bg-red-50/30' : 'border-gray-200 bg-white'} focus:ring-4 focus:ring-pink-100 focus:border-pink-500 focus:bg-white transition-all outline-none text-gray-800 font-medium appearance-none cursor-pointer`}
                            required
                            value={formData.gender}
                            onChange={e => setFormData({...formData, gender: e.target.value})}
                        >
                            <option value="">Select Gender...</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        {errors.gender && <p className="text-red-500 text-[0.65rem] font-bold mt-1.5 px-1 uppercase tracking-wider">{errors.gender[0]}</p>}
                    </div>

                    {/* Date of Birth */}
                    <div className="group">
                        <label className="block text-[0.7rem] font-bold text-gray-500 uppercase tracking-[0.1em] mb-2 px-1 transition-colors group-focus-within:text-pink-600">Date of Birth</label>
                        <input 
                            type="date" 
                            className={`w-full px-5 py-3.5 rounded-2xl border ${errors.date_of_birth ? 'border-red-300 bg-red-50/30' : 'border-gray-200 bg-white'} focus:ring-4 focus:ring-pink-100 focus:border-pink-500 focus:bg-white transition-all outline-none text-gray-800 font-medium`}
                            required 
                            value={formData.date_of_birth}
                            onChange={e => setFormData({...formData, date_of_birth: e.target.value})}
                        />
                        {errors.date_of_birth && <p className="text-red-500 text-[0.65rem] font-bold mt-1.5 px-1 uppercase tracking-wider">{errors.date_of_birth[0]}</p>}
                    </div>

                    {/* Blood Group */}
                    <div className="group">
                        <label className="block text-[0.7rem] font-bold text-gray-500 uppercase tracking-[0.1em] mb-2 px-1 transition-colors group-focus-within:text-pink-600">Blood Group</label>
                        <select 
                            className={`w-full px-5 py-3.5 rounded-2xl border ${errors.blood_group ? 'border-red-300 bg-red-50/30' : 'border-gray-200 bg-white'} focus:ring-4 focus:ring-pink-100 focus:border-pink-500 focus:bg-white transition-all outline-none text-gray-800 font-medium appearance-none cursor-pointer`}
                            value={formData.blood_group}
                            onChange={e => setFormData({...formData, blood_group: e.target.value})}
                        >
                            <option value="">Select Blood Group...</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                        {errors.blood_group && <p className="text-red-500 text-[0.65rem] font-bold mt-1.5 px-1 uppercase tracking-wider">{errors.blood_group[0]}</p>}
                    </div>
                </div>
                
                {/* Emergency Contact Section */}
                <div className="mt-10 bg-pink-50/50 rounded-[2rem] p-8 border border-pink-100 shadow-sm relative overflow-hidden">
                    <div className="absolute top-0 right-0 w-32 h-32 bg-pink-200/20 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl"></div>
                    <h6 className="text-[0.7rem] font-black text-pink-600 uppercase tracking-[0.2em] mb-6 flex items-center gap-3">
                        <div className="w-6 h-6 bg-pink-600 text-white rounded-full flex items-center justify-center">
                            <i className="fas fa-heartbeat text-[0.6rem]"></i>
                        </div>
                        Emergency Contact info
                    </h6>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                        <div>
                            <input 
                                type="text" 
                                placeholder="Contact Person Name" 
                                className="w-full px-5 py-3 rounded-2xl border border-pink-100 bg-white focus:ring-4 focus:ring-pink-200 focus:border-pink-400 transition-all outline-none font-medium text-gray-700"
                                value={formData.emergency_name}
                                onChange={e => setFormData({...formData, emergency_name: e.target.value})}
                            />
                        </div>
                        <div>
                            <input 
                                type="tel" 
                                placeholder="Contact Phone" 
                                className="w-full px-5 py-3 rounded-2xl border border-pink-100 bg-white focus:ring-4 focus:ring-pink-200 focus:border-pink-400 transition-all outline-none font-medium text-gray-700"
                                value={formData.emergency_contact}
                                onChange={e => setFormData({...formData, emergency_contact: e.target.value})}
                            />
                        </div>
                    </div>
                </div>
                
                {/* Footer Actions */}
                <div className="mt-10 flex items-center justify-end gap-4 pt-8 border-t border-gray-100">
                    <button 
                        type="button" 
                        className="px-8 py-3.5 rounded-2xl font-bold text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-all duration-300"
                        onClick={onClose} 
                        disabled={loading}
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        className="px-10 py-3.5 rounded-2xl font-black text-white bg-pink-600 hover:bg-pink-700 shadow-xl shadow-pink-200 hover:shadow-pink-300 hover:scale-[1.02] active:scale-95 transition-all duration-300 flex items-center gap-3"
                        disabled={loading}
                    >
                        {loading ? (
                            <>
                                <svg className="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                PROCESSING...
                            </>
                        ) : (
                            <>
                                <i className="fas fa-check-circle"></i>
                                Register & Select
                            </>
                        )}
                    </button>
                </div>
            </form>
        </Modal>
    );
}
