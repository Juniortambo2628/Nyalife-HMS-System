import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import FormSection from '@/Components/FormSection';
import FormField from '@/Components/FormField';

/**
 * Register Patient Page - Standardized Clinical Design
 * Implements the premium, structured form architecture for consistent patient onboarding.
 */
export default function Create({ auth }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        date_of_birth: '',
        gender: 'male',
        address: '',
        blood_group: '',
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
            <Head title="Register New Patient" />

            <PageHeader 
                title="Onboard New Patient"
                breadcrumbs={[
                    { label: 'Patients Catalog', url: route('patients.index') },
                    { label: 'New Registration', active: true }
                ]}
            />

            <div className="container-fluid px-0 pb-5 mt-4">
                <div className="row justify-content-center">
                    <div className="col-lg-10 col-xl-9">
                        <form onSubmit={submit}>
                            {/* Section 1: Core Bio-data */}
                            <FormSection 
                                title="Personal Profile & Bio-data" 
                                icon="fas fa-user-plus"
                                headerClassName="bg-gradient-primary-to-secondary text-white p-4"
                            >
                                <div className="row g-4">
                                    <FormField label="Legal First Name" required error={errors.first_name}>
                                        <input 
                                            type="text"
                                            className="form-control form-control-lg bg-light border-0 fw-bold px-4"
                                            placeholder="e.g. Jane"
                                            value={data.first_name}
                                            onChange={e => setData('first_name', e.target.value)}
                                            required
                                        />
                                    </FormField>

                                    <FormField label="Legal Last Name" required error={errors.last_name}>
                                        <input 
                                            type="text"
                                            className="form-control form-control-lg bg-light border-0 fw-bold px-4"
                                            placeholder="e.g. Smith"
                                            value={data.last_name}
                                            onChange={e => setData('last_name', e.target.value)}
                                            required
                                        />
                                    </FormField>

                                    <FormField label="Date of Birth" required error={errors.date_of_birth} className="col-md-4">
                                        <input 
                                            type="date"
                                            className="form-control form-control-lg bg-light border-0 fw-bold px-4"
                                            value={data.date_of_birth}
                                            onChange={e => setData('date_of_birth', e.target.value)}
                                            required
                                        />
                                    </FormField>

                                    <FormField label="Biological Gender" required error={errors.gender} className="col-md-4">
                                        <div className="d-flex gap-2 mt-1">
                                            {['male', 'female', 'other'].map(g => (
                                                <button 
                                                    key={g}
                                                    type="button" 
                                                    className={`btn rounded-pill px-4 py-2.5 flex-fill fw-extrabold extra-small text-uppercase tracking-widest transition-all ${data.gender === g ? 'btn-primary shadow-sm' : 'btn-light border text-muted'}`}
                                                    onClick={() => setData('gender', g)}
                                                >
                                                    {g}
                                                </button>
                                            ))}
                                        </div>
                                    </FormField>

                                    <FormField label="Blood Group" error={errors.blood_group} className="col-md-4">
                                        <select 
                                            className="form-select form-select-lg bg-light border-0 fw-bold px-4"
                                            value={data.blood_group}
                                            onChange={e => setData('blood_group', e.target.value)}
                                        >
                                            <option value="">Unknown</option>
                                            {['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'].map(bg => (
                                                <option key={bg} value={bg}>{bg}</option>
                                            ))}
                                        </select>
                                    </FormField>
                                </div>
                            </FormSection>

                            {/* Section 2: Contact Matrix */}
                            <FormSection 
                                title="Communication & Contact Matrix" 
                                icon="fas fa-address-book"
                                headerClassName="bg-white border-bottom text-primary p-4"
                            >
                                <div className="row g-4">
                                    <FormField label="Direct Email" required error={errors.email}>
                                        <div className="input-group">
                                            <span className="input-group-text bg-light border-0 px-4 text-muted"><i className="fas fa-envelope"></i></span>
                                            <input 
                                                type="email"
                                                className="form-control form-control-lg bg-light border-0 fw-bold px-4"
                                                placeholder="patient@example.com"
                                                value={data.email}
                                                onChange={e => setData('email', e.target.value)}
                                                required
                                            />
                                        </div>
                                    </FormField>

                                    <FormField label="Primary Phone" required error={errors.phone}>
                                        <div className="input-group">
                                            <span className="input-group-text bg-light border-0 px-4 text-muted"><i className="fas fa-phone-alt"></i></span>
                                            <input 
                                                type="text"
                                                className="form-control form-control-lg bg-light border-0 fw-bold px-4"
                                                placeholder="+254 700 000 000"
                                                value={data.phone}
                                                onChange={e => setData('phone', e.target.value)}
                                                required
                                            />
                                        </div>
                                    </FormField>

                                    <FormField label="Residential Address" error={errors.address} className="col-12">
                                        <textarea 
                                            className="form-control bg-light border-0 rounded-2xl p-4 fw-medium"
                                            placeholder="Physical location details..."
                                            value={data.address}
                                            onChange={e => setData('address', e.target.value)}
                                            rows="3"
                                        />
                                    </FormField>
                                </div>
                            </FormSection>

                            {/* Section 3: Next of Kin */}
                            <FormSection 
                                title="Next of Kin (Emergency Support)" 
                                icon="fas fa-user-shield"
                                headerClassName="bg-light text-dark p-4 border-bottom"
                            >
                                <div className="row g-4">
                                    <FormField label="Full Name of Guardian/Kin" error={errors.emergency_name}>
                                        <input 
                                            type="text"
                                            className="form-control form-control-lg bg-light border-0 fw-bold px-4"
                                            placeholder="Contact Name"
                                            value={data.emergency_name}
                                            onChange={e => setData('emergency_name', e.target.value)}
                                        />
                                    </FormField>
                                    <FormField label="Emergency Phone Line" error={errors.emergency_contact}>
                                        <input 
                                            type="text"
                                            className="form-control form-control-lg bg-light border-0 fw-bold px-4"
                                            placeholder="Contact Phone"
                                            value={data.emergency_contact}
                                            onChange={e => setData('emergency_contact', e.target.value)}
                                        />
                                    </FormField>
                                </div>
                            </FormSection>

                            {/* Info Card */}
                            <div className="alert alert-info border-0 shadow-sm rounded-2xl p-4 d-flex align-items-center mb-5 bg-opacity-10 bg-info">
                                <div className="bg-info bg-opacity-20 text-info rounded-circle p-3 me-4 d-flex align-items-center justify-content-center" style={{ width: '60px', height: '60px' }}>
                                    <i className="fas fa-user-lock fs-4"></i>
                                </div>
                                <div>
                                    <h6 className="fw-extrabold mb-1">Automatic Secure Access</h6>
                                    <p className="mb-0 small text-muted font-medium">A patient portal account will be initialized with the temporary password <code>password123</code>. The patient will be prompted to reset this credential upon first successful authentication.</p>
                                </div>
                            </div>

                            {/* Actions */}
                            <div className="d-flex justify-content-between align-items-center">
                                <button type="button" onClick={() => router.visit(route('patients.index'))} className="btn btn-link text-muted fw-bold text-decoration-none">
                                    <i className="fas fa-times me-2"></i> Abandon Registration
                                </button>
                                <div className="d-flex gap-3">
                                    <button type="button" onClick={() => reset()} className="btn btn-light rounded-pill px-4 fw-bold border">Reset</button>
                                    <button type="submit" disabled={processing} className="btn btn-primary rounded-pill px-5 py-3 fw-extrabold shadow-lg transition-all hover-lift">
                                        {processing ? (
                                            <><span className="spinner-border spinner-border-sm me-2"></span> Finalizing...</>
                                        ) : (
                                            <><i className="fas fa-check-circle me-2"></i> COMPLETE REGISTRATION</>
                                        )}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <style>{`
                .extra-small { font-size: 0.7rem; }
                .fw-extrabold { font-weight: 800; }
                .tracking-widest { letter-spacing: 0.1em; }
                .bg-gradient-primary-to-secondary {
                    background: linear-gradient(135deg, #e91e63 0%, #d81b60 100%);
                }
                .hover-lift:hover { transform: translateY(-3px); box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important; }
            `}</style>
        </AuthenticatedLayout>
    );
}
