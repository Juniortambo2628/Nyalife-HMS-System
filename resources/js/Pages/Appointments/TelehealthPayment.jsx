import { Head, Link, useForm } from '@inertiajs/react';

export default function TelehealthPayment({ appointment, payment_token }) {
    const { data, setData, post, processing, errors } = useForm({
        transaction_reference: '',
        receipt: null,
    });

    const submit = (event) => {
        event.preventDefault();
        post(route('telehealth.payment.submit', payment_token), { forceFormData: true });
    };

    return (
        <main className="landing-wrapper min-vh-100 d-flex align-items-center py-5 bg-light">
            <Head title="Telehealth Payment" />
            <div className="container"><div className="row justify-content-center"><div className="col-lg-6">
                <div className="card border-0 shadow-lg rounded-4"><div className="card-body p-5">
                    <h1 className="h3 fw-bold">Confirm your telehealth payment</h1>
                    <p className="text-muted">Pay <strong>KES 4,000</strong> by M-Pesa, then provide the transaction code. Your appointment is held for 15 minutes pending clinic approval.</p>
                    <div className="bg-light rounded-3 p-3 mb-4"><strong>Appointment #{appointment.appointment_id}</strong><br />{appointment.appointment_date} at {appointment.appointment_time}</div>
                    <form onSubmit={submit} encType="multipart/form-data">
                        <label className="form-label fw-bold">M-Pesa transaction code</label>
                        <input className={`form-control ${errors.transaction_reference ? 'is-invalid' : ''}`} value={data.transaction_reference} onChange={(e) => setData('transaction_reference', e.target.value)} required />
                        {errors.transaction_reference && <div className="invalid-feedback">{errors.transaction_reference}</div>}
                        <label className="form-label fw-bold mt-3">Receipt image (optional)</label>
                        <input type="file" accept="image/jpeg,image/png" className={`form-control ${errors.receipt ? 'is-invalid' : ''}`} onChange={(e) => setData('receipt', e.target.files[0])} />
                        {errors.receipt && <div className="invalid-feedback">{errors.receipt}</div>}
                        <button className="btn btn-primary w-100 rounded-pill mt-4" disabled={processing}>{processing ? 'Submitting…' : 'Submit payment proof'}</button>
                    </form>
                    <Link href="/" className="d-block text-center mt-3 text-muted">Return home</Link>
                </div></div>
            </div></div></div>
        </main>
    );
}
