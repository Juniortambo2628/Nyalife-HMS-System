import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Create({ auth }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        date_of_birth: '',
        gender: 'male',
        address: '',
        blood_group: 'Unknown',
        emergency_name: '',
        emergency_contact: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('patients.store'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header="Register Patient"
        >
            <Head title="Register Patient" />

            <div className="container-fluid patients-page px-0">
                <div className="row mb-4">
                    <div className="col-12 d-flex justify-content-between align-items-center">
                        <h2 className="mb-0">New Patient Registration</h2>
                        <Link href={route('patients.index')} className="btn btn-outline-secondary">
                            <i className="fas fa-arrow-left me-2"></i>Back to Registry
                        </Link>
                    </div>
                </div>

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
                                            <label className="form-label fw-bold">Email Address <span className="text-danger">*</span></label>
                                            <input 
                                                type="email"
                                                className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                                                value={data.email}
                                                onChange={e => setData('email', e.target.value)}
                                                required
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

                                    <div className="alert alert-light border border-primary-subtle d-flex align-items-center" role="alert">
                                        <i className="fas fa-info-circle text-primary me-3 fs-3"></i>
                                        <div>
                                            An account will be automatically created for this patient with the password <code>password123</code>. 
                                            They can change it upon their first login.
                                        </div>
                                    </div>

                                    <div className="mt-4 d-flex justify-content-end gap-2">
                                        <button type="button" onClick={() => reset()} className="btn btn-light px-4 border">Reset Form</button>
                                        <button type="submit" disabled={processing} className="btn btn-primary px-5 shadow-sm">
                                            {processing ? 'Registering...' : 'Complete Registration'}
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
