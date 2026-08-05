import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import StatusBadge from '@/Components/StatusBadge';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { formatCurrency } from '@/Utils/formatUtils';

export default function Show({ payment, clinic_settings = {}, auth }) {
    const handlePrint = () => window.open(route('payments.print', payment.payment_id), '_blank');

    const completePayment = () => {
        if (confirm('Mark this payment as completed?')) {
            router.post(route('payments.complete', payment.payment_id));
        }
    };

    const inv = payment.invoice;

    return (
        <AuthenticatedLayout
            headerTitle="Payment Receipt"
            breadcrumbs={[
                { label: 'Payments', url: route('payments.index') },
                { label: `PAY-${payment.payment_id}`, active: true },
            ]}
        >
            <Head title={`Payment PAY-${payment.payment_id}`} />

            <div className="row g-4">
                <div className="col-lg-8">
                    <div className="card shadow-sm border-0 rounded-4 bg-white overflow-hidden">
                        <div className="card-body p-5">
                            <div className="d-flex justify-content-between align-items-start mb-5">
                                <div>
                                    <img
                                        src="/assets/img/logo/Logo2-transparent.png"
                                        alt="Nyalife"
                                        style={{ height: '60px' }}
                                        className="mb-3"
                                    />
                                    <div className="text-muted small">
                                        {clinic_settings.contact_address || 'Sabaki, Athi River, Machakos'}
                                    </div>
                                    <div className="text-muted small">
                                        {clinic_settings.contact_phone || '+254 746 516 514'}
                                    </div>
                                </div>
                                <div className="text-end">
                                    <h4 className="fw-extrabold text-uppercase tracking-widest text-gray-400 mb-2">
                                        Payment Receipt
                                    </h4>
                                    <div className="h5 fw-extrabold text-success">PAY-{payment.payment_id}</div>
                                    <StatusBadge status={payment.payment_status} />
                                </div>
                            </div>

                            <div className="row g-4 mb-4">
                                <div className="col-md-6">
                                    <div className="p-4 rounded-4 bg-gray-50 border">
                                        <h6 className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-3">
                                            Received From
                                        </h6>
                                        <div className="fw-bold">
                                            {inv?.patient?.user?.first_name} {inv?.patient?.user?.last_name}
                                        </div>
                                        <div className="text-muted small">Invoice: {inv?.invoice_number}</div>
                                    </div>
                                </div>
                                <div className="col-md-6">
                                    <div className="p-4 rounded-4 bg-gray-50 border">
                                        <h6 className="extra-small fw-extrabold text-muted text-uppercase tracking-widest mb-3">
                                            Payment Details
                                        </h6>
                                        <div className="d-flex justify-content-between small mb-2">
                                            <span className="text-muted">Method</span>
                                            <span className="fw-bold">{payment.payment_method_label}</span>
                                        </div>
                                        <div className="d-flex justify-content-between small mb-2">
                                            <span className="text-muted">Date</span>
                                            <span className="fw-bold">{payment.payment_date}</span>
                                        </div>
                                        {payment.transaction_reference && (
                                            <div className="d-flex justify-content-between small">
                                                <span className="text-muted">Reference</span>
                                                <span className="fw-bold font-mono">
                                                    {payment.transaction_reference}
                                                </span>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>

                            <div className="text-center py-4 border-top border-bottom my-4">
                                <div className="text-muted extra-small fw-bold text-uppercase tracking-widest mb-2">
                                    Amount Received
                                </div>
                                <div className="display-4 fw-extrabold text-success">
                                    {formatCurrency(payment.amount)}
                                </div>
                            </div>

                            {payment.notes && (
                                <div className="text-muted small">
                                    <strong>Notes:</strong> {payment.notes}
                                </div>
                            )}

                            <div className="mt-4 pt-3 border-top text-muted extra-small">
                                Received by: {payment.received_by_user?.first_name}{' '}
                                {payment.received_by_user?.last_name}
                            </div>
                        </div>
                    </div>
                </div>

                <div className="col-lg-4 no-print">
                    <div className="card border-0 shadow-sm rounded-4">
                        <div className="card-body p-4">
                            <h6 className="fw-extrabold text-muted extra-small text-uppercase tracking-widest mb-3">
                                Linked Invoice
                            </h6>
                            <Link
                                href={route('invoices.show', payment.invoice_id)}
                                className="btn btn-outline-primary w-100 rounded-pill fw-bold"
                            >
                                View {inv?.invoice_number}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <UnifiedToolbar
                actions={[
                    payment.payment_status === 'pending' && {
                        label: 'MARK COMPLETED',
                        icon: 'fa-check-circle',
                        onClick: completePayment,
                        color: 'success',
                    },
                    { label: 'PRINT RECEIPT', icon: 'fa-print', onClick: handlePrint },
                    { label: 'ALL PAYMENTS', icon: 'fa-list', href: route('payments.index'), color: 'gray' },
                ].filter(Boolean)}
            />
        </AuthenticatedLayout>
    );
}
