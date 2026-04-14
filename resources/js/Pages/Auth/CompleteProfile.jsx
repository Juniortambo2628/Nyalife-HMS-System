import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';

export default function CompleteProfile({ google_user }) {
    const { data, setData, post, processing, errors } = useForm({
        first_name: google_user.first_name || '',
        last_name: google_user.last_name || '',
        gender: '',
        date_of_birth: '',
        phone: '',
        terms: false,
    });

    const onHandleChange = (event) => {
        const { name, value, type, checked } = event.target;
        setData(name, type === 'checkbox' ? checked : value);
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('auth.google.store-profile'));
    };

    return (
        <GuestLayout>
            <Head title="Complete Profile" />

            <div className="col-12 col-md-10 col-lg-8">
                <div className="card shadow border-0">
                    <div className="card-header bg-primary text-white p-4">
                        <h4 className="mb-0">Almost there, {google_user.first_name}!</h4>
                        <p className="mb-0 small opacity-75">Please provide a few more details to complete your registration.</p>
                    </div>
                    <div className="card-body p-4">
                        <div className="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
                            <i className="fas fa-info-circle me-3 fa-lg"></i>
                            <div>
                                We've successfully connected your Google account (<strong>{google_user.email}</strong>).
                            </div>
                        </div>

                        <form onSubmit={submit}>
                            <div className="row mb-3">
                                <div className="col-md-6">
                                    <label htmlFor="first_name" className="form-label">First Name</label>
                                    <input 
                                        type="text" 
                                        className={`form-control ${errors.first_name ? 'is-invalid' : ''}`} 
                                        id="first_name" 
                                        name="first_name" 
                                        value={data.first_name} 
                                        onChange={onHandleChange} 
                                        required 
                                    />
                                    {errors.first_name && <div className="invalid-feedback">{errors.first_name}</div>}
                                </div>
                                <div className="col-md-6">
                                    <label htmlFor="last_name" className="form-label">Last Name</label>
                                    <input 
                                        type="text" 
                                        className={`form-control ${errors.last_name ? 'is-invalid' : ''}`} 
                                        id="last_name" 
                                        name="last_name" 
                                        value={data.last_name} 
                                        onChange={onHandleChange} 
                                        required 
                                    />
                                    {errors.last_name && <div className="invalid-feedback">{errors.last_name}</div>}
                                </div>
                            </div>

                            <div className="row mb-3">
                                <div className="col-md-6">
                                    <label htmlFor="phone" className="form-label">Phone Number</label>
                                    <input 
                                        type="tel" 
                                        className={`form-control ${errors.phone ? 'is-invalid' : ''}`} 
                                        id="phone" 
                                        name="phone" 
                                        value={data.phone} 
                                        onChange={onHandleChange} 
                                        required 
                                        placeholder="e.g. +254 700 000000"
                                    />
                                    {errors.phone && <div className="invalid-feedback">{errors.phone}</div>}
                                </div>
                                <div className="col-md-6">
                                    <label htmlFor="gender" className="form-label">Gender</label>
                                    <select 
                                        className={`form-select ${errors.gender ? 'is-invalid' : ''}`} 
                                        id="gender" 
                                        name="gender" 
                                        value={data.gender} 
                                        onChange={onHandleChange} 
                                        required
                                    >
                                        <option value="">Select Gender</option>
                                        <option value="female">Female</option>
                                        <option value="male">Male</option>
                                        <option value="other">Other</option>
                                    </select>
                                    {errors.gender && <div className="invalid-feedback">{errors.gender}</div>}
                                </div>
                            </div>

                            <div className="mb-3">
                                <label htmlFor="date_of_birth" className="form-label">Date of Birth</label>
                                <input 
                                    type="date" 
                                    className={`form-control ${errors.date_of_birth ? 'is-invalid' : ''}`} 
                                    id="date_of_birth" 
                                    name="date_of_birth" 
                                    value={data.date_of_birth} 
                                    onChange={onHandleChange} 
                                    required
                                />
                                {errors.date_of_birth && <div className="invalid-feedback">{errors.date_of_birth}</div>}
                            </div>

                            <div className="mb-4 form-check">
                                <input 
                                    type="checkbox" 
                                    className={`form-check-input ${errors.terms ? 'is-invalid' : ''}`} 
                                    id="terms" 
                                    name="terms" 
                                    checked={data.terms} 
                                    onChange={onHandleChange} 
                                    required
                                />
                                <label className="form-check-label" htmlFor="terms">
                                    I agree to the <a href={route('terms-of-service')} target="_blank" className="text-decoration-none">Terms of Service</a> and <a href={route('privacy-policy')} target="_blank" className="text-decoration-none">Privacy Policy</a>
                                </label>
                                {errors.terms && <div className="invalid-feedback">{errors.terms}</div>}
                            </div>

                            <div className="d-grid">
                                <button type="submit" className="btn btn-primary btn-lg shadow-sm" disabled={processing}>
                                    {processing ? (
                                        <>
                                            <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Completing Registration...
                                        </>
                                    ) : (
                                        'Complete Registration'
                                    )}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <style>{`
                .card-header {
                    background: linear-gradient(135deg, #198754 0%, #20c997 100%);
                }
            `}</style>
        </GuestLayout>
    );
}
