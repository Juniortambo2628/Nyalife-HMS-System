import { Head } from '@inertiajs/react';
import { useEffect } from 'react';
import { formatCurrency } from '@/Utils/formatUtils';

export default function Print({ payment, clinic_settings = {} }) {
    useEffect(() => {
        window.print();
    }, []);

    const inv = payment.invoice;

    return (
        <div className="print-container p-5 bg-white">
            <Head title={`Payment Receipt PAY-${payment.payment_id}`} />

            <div className="d-flex justify-content-between align-items-center mb-5 border-bottom pb-4">
                <div>
                    <img
                        src="/assets/img/logo/Logo2-transparent.png"
                        alt="Nyalife"
                        style={{ height: '70px' }}
                        className="mb-2"
                    />
                    <div className="text-muted small">{clinic_settings.contact_address}</div>
                    <div className="text-muted small">
                        {clinic_settings.contact_email} | {clinic_settings.contact_phone}
                    </div>
                </div>
                <div className="text-end">
                    <h4 className="fw-bold text-uppercase mb-1">Payment Receipt</h4>
                    <div className="text-muted">PAY-{payment.payment_id}</div>
                    <div className="text-muted small">{payment.payment_date}</div>
                </div>
            </div>

            <div className="row mb-4">
                <div className="col-6">
                    <h6 className="text-muted text-uppercase small fw-bold">Patient</h6>
                    <p className="fw-bold mb-0">
                        {inv?.patient?.user?.first_name} {inv?.patient?.user?.last_name}
                    </p>
                    <p className="text-muted small mb-0">Invoice: {inv?.invoice_number}</p>
                </div>
                <div className="col-6 text-end">
                    <h6 className="text-muted text-uppercase small fw-bold">Payment</h6>
                    <p className="mb-0">
                        <strong>Method:</strong> {payment.payment_method_label}
                    </p>
                    {payment.transaction_reference && (
                        <p className="mb-0">
                            <strong>Ref:</strong> {payment.transaction_reference}
                        </p>
                    )}
                </div>
            </div>

            <div className="text-center py-5 my-4 border-top border-bottom">
                <div className="text-muted text-uppercase small fw-bold mb-2">Amount Received</div>
                <h1 className="fw-bold text-success">{formatCurrency(payment.amount)}</h1>
            </div>

            {payment.notes && (
                <p className="text-muted small">
                    <em>{payment.notes}</em>
                </p>
            )}

            <div className="mt-5 pt-4 text-center text-muted small">
                <p>
                    Received by: {payment.received_by_user?.first_name} {payment.received_by_user?.last_name}
                </p>
                <p className="mb-0">Computer generated receipt — Nyalife Women&apos;s Clinic</p>
            </div>
        </div>
    );
}
