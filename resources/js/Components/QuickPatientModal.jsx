import React from 'react';
import Modal from '@/Components/Modal';
import { useForm } from '@inertiajs/react';
import axios from 'axios';

export default function QuickPatientModal({ show, onClose, onSuccess }) {
    const { data, setData, processing, errors, reset, setError } = useForm({
        first_name: '',
        last_name: '',
        phone: '',
        date_of_birth: '',
        gender: 'female',
        email: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        
        axios.post(route('patients.quick-store'), data)
            .then(response => {
                if (response.data.success) {
                    onSuccess(response.data);
                    reset();
                    onClose();
                }
            })
            .catch(error => {
                if (error.response?.data?.errors) {
                    Object.keys(error.response.data.errors).forEach(key => {
                        setError(key, error.response.data.errors[key][0]);
                    });
                }
            });
    };

    const inputClass = "mt-1 block w-full rounded-2xl border-gray-200 bg-gray-50 focus:border-pink-500 focus:ring-pink-500 text-gray-900 shadow-sm py-3 px-4";
    const labelClass = "block font-bold text-sm text-gray-900 mb-1";

    return (
        <Modal show={show} onClose={onClose} maxWidth="lg">
            <div className="p-6">
                <div className="flex justify-between items-center mb-6">
                    <h3 className="text-xl font-bold text-gray-900">Quick-Create Patient</h3>
                    <button onClick={onClose} className="text-gray-400 hover:text-gray-600 transition-colors">
                        <i className="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className={labelClass}>First Name</label>
                            <input
                                value={data.first_name}
                                onChange={(e) => setData('first_name', e.target.value)}
                                className={inputClass}
                                required
                            />
                            {errors.first_name && <p className="text-red-500 text-xs mt-1">{errors.first_name}</p>}
                        </div>
                        <div>
                            <label className={labelClass}>Last Name</label>
                            <input
                                value={data.last_name}
                                onChange={(e) => setData('last_name', e.target.value)}
                                className={inputClass}
                                required
                            />
                            {errors.last_name && <p className="text-red-500 text-xs mt-1">{errors.last_name}</p>}
                        </div>
                    </div>

                    <div>
                        <label className={labelClass}>Phone Number</label>
                        <input
                            value={data.phone}
                            onChange={(e) => setData('phone', e.target.value)}
                            className={inputClass}
                            required
                        />
                        {errors.phone && <p className="text-red-500 text-xs mt-1">{errors.phone}</p>}
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className={labelClass}>Date of Birth</label>
                            <input
                                type="date"
                                value={data.date_of_birth}
                                onChange={(e) => setData('date_of_birth', e.target.value)}
                                className={inputClass}
                                required
                            />
                            {errors.date_of_birth && <p className="text-red-500 text-xs mt-1">{errors.date_of_birth}</p>}
                        </div>
                        <div>
                            <label className={labelClass}>Gender</label>
                            <select
                                value={data.gender}
                                onChange={(e) => setData('gender', e.target.value)}
                                className={inputClass}
                                required
                            >
                                <option value="female">Female</option>
                                <option value="male">Male</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label className={labelClass}>Email (Optional)</label>
                        <input
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            className={inputClass}
                        />
                        {errors.email && <p className="text-red-500 text-xs mt-1">{errors.email}</p>}
                    </div>

                    <div className="flex justify-end gap-3 mt-8">
                        <button
                            type="button"
                            onClick={onClose}
                            className="px-6 py-2.5 rounded-full border border-gray-300 text-gray-700 font-bold hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="px-8 py-2.5 rounded-full bg-pink-500 text-white font-bold shadow-lg hover:bg-pink-600 transition-all disabled:opacity-50"
                        >
                            {processing ? 'Creating...' : 'Create Patient'}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    );
}
