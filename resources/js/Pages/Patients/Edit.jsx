import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
export default function Edit({ patient, auth }) {
    const { data, setData, put, processing, errors } = useForm({
        first_name: patient.user?.first_name || '',
        last_name: patient.user?.last_name || '',
        email: patient.user?.email || '',
        phone: patient.user?.phone || '',
        date_of_birth: patient.date_of_birth || '',
        gender: patient.gender || 'male',
        address: patient.address || '',
        blood_group: patient.blood_group || 'Unknown',
        height: patient.height ?? '',
        weight: patient.weight ?? '',
        allergies: patient.allergies || '',
        chronic_diseases: patient.chronic_diseases || '',
        marital_status: patient.marital_status || '',
        occupation: patient.occupation || '',
        insurance_provider: patient.insurance_provider || '',
        insurance_id: patient.insurance_id || '',
        emergency_name: patient.emergency_name || '',
        emergency_contact: patient.emergency_contact || '',
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('patients.update', patient.patient_id));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            headerTitle="Edit Patient Profile"
            breadcrumbs={[
                { label: 'Patients', url: route('patients.index') },
                { label: patient.user?.first_name, url: route('patients.show', patient.patient_id) },
                { label: 'Edit', active: true },
            ]}
        >
            <Head title={`Edit Patient - ${patient.user?.first_name}`} />

            <div className="container-fluid patients-page px-0">
                <div className="row justify-content-center">
                    <div className="col-lg-10">
                        <div className="card shadow-sm border-0">
                            <div className="card-body p-4 p-md-5">
                                <form onSubmit={submit}>
                                    <h5 className="mb-4 border-bottom pb-2 text-primary">Personal Information</h5>
                                    <div className="row g-3 mb-4">
                                        <div className="col-md-6">
                                            <label className="form-label fw-bold">First Name <span className="text-danger">*</span></label>
                                            <input 
                                                type="text"
                                                className={`form-control ${errors.first_name ? 'is-invalid' : ''}`}
                                                value={data.first_name}
                                                onChange={e => setData('first_name', e.target.value)}
                                                required
                                            />
                                            {errors.first_name && <div className="invalid-feedback">{errors.first_name}</div>}
                                        </div>

                                        <div className="col-md-6">
                                            <label className="form-label fw-bold">Last Name <span className="text-danger">*</span></label>
                                            <input 
                                                type="text"
                                                className={`form-control ${errors.last_name ? 'is-invalid' : ''}`}
                                                value={data.last_name}
                                                onChange={e => setData('last_name', e.target.value)}
                                                required
                                            />
                                            {errors.last_name && <div className="invalid-feedback">{errors.last_name}</div>}
                                        </div>

                                        <div className="col-md-4">
                                            <label className="form-label fw-bold">Date of Birth <span className="text-danger">*</span></label>
                                            <input 
                                                type="date"
                                                className={`form-control ${errors.date_of_birth ? 'is-invalid' : ''}`}
                                                value={data.date_of_birth}
                                                onChange={e => setData('date_of_birth', e.target.value)}
                                                required
                                            />
                                            {errors.date_of_birth && <div className="invalid-feedback">{errors.date_of_birth}</div>}
                                        </div>

                                        <div className="col-md-4">
                                            <label className="form-label fw-bold">Gender <span className="text-danger">*</span></label>
                                            <div className="d-flex gap-3 mt-2">
                                                <div className="form-check">
                                                    <input className="form-check-input" type="radio" name="gender" id="male" value="male" checked={data.gender === 'male'} onChange={e => setData('gender', e.target.value)} />
                                                    <label className="form-check-label" htmlFor="male">Male</label>
                                                </div>
                                                <div className="form-check">
                                                    <input className="form-check-input" type="radio" name="gender" id="female" value="female" checked={data.gender === 'female'} onChange={e => setData('gender', e.target.value)} />
                                                    <label className="form-check-label" htmlFor="female">Female</label>
                                                </div>
                                                <div className="form-check">
                                                    <input className="form-check-input" type="radio" name="gender" id="other" value="other" checked={data.gender === 'other'} onChange={e => setData('gender', e.target.value)} />
                                                    <label className="form-check-label" htmlFor="other">Other</label>
                                                </div>
                                            </div>
                                            {errors.gender && <div className="text-danger small mt-1">{errors.gender}</div>}
                                        </div>

                                        <div className="col-md-4">
                                            <label className="form-label fw-bold">Blood Group</label>
                                            <select 
                                                className={`form-select ${errors.blood_group ? 'is-invalid' : ''}`}
                                                value={data.blood_group}
                                                onChange={e => setData('blood_group', e.target.value)}
                                            >
                                                <option value="Unknown">Unknown</option>
                                                <option value="A+">A+</option>
                                                <option value="A-">A-</option>
                                                <option value="B+">B+</option>
                                                <option value="B-">B-</option>
                                                <option value="AB+">AB+</option>
                                                <option value="AB-">AB-</option>
                                                <option value="O+">O+</option>
                                                <option value="O-">O-</option>
                                            </select>
                                            {errors.blood_group && <div className="invalid-feedback">{errors.blood_group}</div>}
                                        </div>
                                    </div>

                                    <h5 className="mb-4 border-bottom pb-2 text-primary">Contact Details</h5>
                                    <div className="row g-3 mb-4">
                                        <div className="col-md-6">
                                            <label className="form-label fw-bold">Email Address</label>
                                            <input 
                                                type="email"
                                                className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                                                value={data.email}
                                                onChange={e => setData('email', e.target.value)}
                                            />
                                            {errors.email && <div className="invalid-feedback">{errors.email}</div>}
                                        </div>

                                        <div className="col-md-6">
                                            <label className="form-label fw-bold">Phone Number <span className="text-danger">*</span></label>
                                            <input 
                                                type="text"
                                                className={`form-control ${errors.phone ? 'is-invalid' : ''}`}
                                                value={data.phone}
                                                onChange={e => setData('phone', e.target.value)}
                                                required
                                            />
                                            {errors.phone && <div className="invalid-feedback">{errors.phone}</div>}
                                        </div>

                                        <div className="col-12">
                                            <label className="form-label fw-bold">Residential Address</label>
                                            <textarea 
                                                className={`form-control ${errors.address ? 'is-invalid' : ''}`}
                                                value={data.address}
                                                onChange={e => setData('address', e.target.value)}
                                                rows="2"
                                            />
                                            {errors.address && <div className="invalid-feedback">{errors.address}</div>}
                                        </div>
                                    </div>

                                    <h5 className="mb-4 border-bottom pb-2 text-primary">Clinical Profile & Insurance</h5>
                                    <div className="row g-3 mb-4">
                                        <div className="col-md-3">
                                            <label className="form-label fw-bold">Height (cm)</label>
                                            <input type="number" step="0.1" className={`form-control ${errors.height ? 'is-invalid' : ''}`} value={data.height} onChange={e => setData('height', e.target.value)} />
                                            {errors.height && <div className="invalid-feedback">{errors.height}</div>}
                                        </div>
                                        <div className="col-md-3">
                                            <label className="form-label fw-bold">Weight (kg)</label>
                                            <input type="number" step="0.1" className={`form-control ${errors.weight ? 'is-invalid' : ''}`} value={data.weight} onChange={e => setData('weight', e.target.value)} />
                                            {errors.weight && <div className="invalid-feedback">{errors.weight}</div>}
                                        </div>
                                        <div className="col-md-3">
                                            <label className="form-label fw-bold">Marital status</label>
                                            <select className={`form-select ${errors.marital_status ? 'is-invalid' : ''}`} value={data.marital_status} onChange={e => setData('marital_status', e.target.value)}>
                                                <option value="">Not specified</option>
                                                {['single', 'married', 'divorced', 'widowed', 'other'].map(s => (
                                                    <option key={s} value={s}>{s.charAt(0).toUpperCase() + s.slice(1)}</option>
                                                ))}
                                            </select>
                                        </div>
                                        <div className="col-md-3">
                                            <label className="form-label fw-bold">Occupation</label>
                                            <input type="text" className={`form-control ${errors.occupation ? 'is-invalid' : ''}`} value={data.occupation} onChange={e => setData('occupation', e.target.value)} />
                                        </div>
                                        <div className="col-md-6">
                                            <label className="form-label fw-bold">Known allergies</label>
                                            <textarea className={`form-control ${errors.allergies ? 'is-invalid' : ''}`} rows="2" value={data.allergies} onChange={e => setData('allergies', e.target.value)} />
                                        </div>
                                        <div className="col-md-6">
                                            <label className="form-label fw-bold">Chronic conditions</label>
                                            <textarea className={`form-control ${errors.chronic_diseases ? 'is-invalid' : ''}`} rows="2" value={data.chronic_diseases} onChange={e => setData('chronic_diseases', e.target.value)} />
                                        </div>
                                        <div className="col-md-6">
                                            <label className="form-label fw-bold">Insurance provider</label>
                                            <input type="text" className={`form-control ${errors.insurance_provider ? 'is-invalid' : ''}`} value={data.insurance_provider} onChange={e => setData('insurance_provider', e.target.value)} />
                                        </div>
                                        <div className="col-md-6">
                                            <label className="form-label fw-bold">Insurance member ID</label>
                                            <input type="text" className={`form-control ${errors.insurance_id ? 'is-invalid' : ''}`} value={data.insurance_id} onChange={e => setData('insurance_id', e.target.value)} />
                                        </div>
                                    </div>

                                    <h5 className="mb-4 border-bottom pb-2 text-primary">Next of Kin (NOK)</h5>
                                    <div className="row g-3 mb-4">
                                        <div className="col-md-6">
                                            <label className="form-label fw-bold">NOK Full Name</label>
                                            <input 
                                                type="text"
                                                className={`form-control ${errors.emergency_name ? 'is-invalid' : ''}`}
                                                value={data.emergency_name}
                                                onChange={e => setData('emergency_name', e.target.value)}
                                                placeholder="e.g. John Doe"
                                            />
                                            {errors.emergency_name && <div className="invalid-feedback">{errors.emergency_name}</div>}
                                        </div>
                                        <div className="col-md-6">
                                            <label className="form-label fw-bold">NOK Phone Number</label>
                                            <input 
                                                type="text"
                                                className={`form-control ${errors.emergency_contact ? 'is-invalid' : ''}`}
                                                value={data.emergency_contact}
                                                onChange={e => setData('emergency_contact', e.target.value)}
                                                placeholder="e.g. 0712345678"
                                            />
                                            {errors.emergency_contact && <div className="invalid-feedback">{errors.emergency_contact}</div>}
                                        </div>
                                    </div>

                                    <div className="mt-4 d-flex justify-content-end gap-2">
                                        <Link href={route('patients.show', patient.patient_id)} className="btn btn-light px-4 border">Cancel</Link>
                                        <button type="submit" disabled={processing} className="btn btn-primary px-5 shadow-sm">
                                            {processing ? 'Saving Changes...' : 'Update Patient Profile'}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
