import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import StatusBadge from '@/Components/StatusBadge';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { PatientIdLabel } from '@/Components/PatientTableCell';
import { formatCurrency } from '@/Utils/formatUtils';
import { formatDateTime } from '@/Utils/dateUtils';
import { useState } from 'react';

export default function Show({ invoice, auth, clinic_settings = {}, paymentMethods = {} }) {
    const handlePrint = () => window.open(route('invoices.print', invoice.invoice_id), '_blank');
    const handleDownloadPdf = () => window.open(route('invoices.pdf', invoice.invoice_id), '_blank');

    const taxRate = parseFloat(clinic_settings.tax_rate || 0);
    const subtotal = invoice.items.reduce((acc, item) => acc + Number(item.total_price), 0);
    const taxAmount = subtotal * (taxRate / 100);
    const finalTotal = subtotal + taxAmount - Number(invoice.discount || 0);

    const [isVoidModalOpen, setIsVoidModalOpen] = useState(false);
    const [isPaymentModalOpen, setIsPaymentModalOpen] = useState(false);

    const {
        data: voidData,
        setData: setVoidData,
        delete: destroyInvoice,
        processing: voidProcessing,
        reset: resetVoid,
    } = useForm({
        void_reason: '',
    });

    const {
        data: paymentData,
        setData: setPaymentData,
        post: postPayment,
        processing: paymentProcessing,
        reset: resetPayment,
        errors: paymentErrors,
    } = useForm({
        invoice_id: invoice.invoice_id,
        amount: String(invoice.balance_due ?? finalTotal),
        payment_method: 'cash',
        payment_date: new Date().toISOString().slice(0, 16),
        transaction_reference: '',
        payment_status: 'completed',
        notes: '',
    });

    const methodOptions = Object.entries(paymentMethods).map(([value, label]) => ({ label, value }));

    const openPaymentModal = () => {
        setPaymentData({
            invoice_id: invoice.invoice_id,
            amount: String(invoice.balance_due ?? finalTotal),
            payment_method: 'cash',
            payment_date: new Date().toISOString().slice(0, 16),
            transaction_reference: '',
            payment_status: 'completed',
            notes: '',
        });
        setIsPaymentModalOpen(true);
    };

    const submitPayment = (e) => {
        e.preventDefault();
        postPayment(route('payments.store'), {
            onSuccess: () => {
                setIsPaymentModalOpen(false);
                resetPayment();
            },
        });
    };

    const amountPaid = Number(invoice.amount_paid ?? 0);
    const balanceDue = Number(invoice.balance_due ?? Math.max(0, finalTotal - amountPaid));
    const payments = invoice.payments || [];

    const handleVoid = (e) => {
        e.preventDefault();
        destroyInvoice(route('invoices.destroy', invoice.invoice_id), {
            onSuccess: () => {
                setIsVoidModalOpen(false);
                resetVoid();
            },
        });
    };

    return (
        <AuthenticatedLayout
            headerTitle="Financial Document"
            breadcrumbs={[
                { label: 'Billing', url: route('invoices.index') },
                { label: invoice.invoice_number, active: true },
            ]}
        >
            <Head title={`Invoice - ${invoice.invoice_number}`} />

            <div className="px-0 py-0" id="invoice-content">
                <div className="row g-4">
                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 mb-5 rounded-4 overflow-hidden bg-white">
                            <div className="card-body p-5">
                                <div className="row mb-5 align-items-center">
                                    <div className="col-sm-6">
                                        <div className="d-flex align-items-center gap-3 mb-4">
                                            <div className="bg-white rounded-xl p-2 shadow-sm border border-light">
                                                <img
                                                    src="/assets/img/logo/Logo2-transparent.png"
                                                    alt="Nyalife"
                                                    style={{ height: '70px' }}
                                                />
                                            </div>
                                            <div>
                                                <h3 className="mb-0 text-gray-900 fw-extrabold tracking-tightest fs-2">
                                                    NYALIFE
                                                </h3>
                                                <div className="text-clinical-high extra-small font-bold uppercase tracking-widest">
                                                    Women's Clinic
                                                </div>
                                            </div>
                                        </div>
                                        <div className="space-y-1">
                                            <div className="text-muted small">
                                                <i className="fas fa-map-marker-alt me-2 text-pink-400"></i>
                                                {clinic_settings.contact_address || 'Sabaki, Athi River, Machakos'}
                                            </div>
                                            <div className="text-muted small">
                                                <i className="fas fa-envelope me-2 text-pink-400"></i>
                                                {clinic_settings.contact_email || 'info@nyalifewomensclinic.com'}
                                            </div>
                                            <div className="text-muted small">
                                                <i className="fas fa-phone-alt me-2 text-pink-400"></i>
                                                {clinic_settings.contact_phone || '+254 746 516 514'}
                                            </div>
                                        </div>
                                    </div>
                                    <div className="col-sm-6 text-sm-end mt-4 mt-sm-0">
                                        <h1 className="mb-3 text-uppercase fw-extrabold text-gray-900 tracking-tightest opacity-10 display-4 d-none d-sm-block">
                                            RECEIPT
                                        </h1>
                                        <div className="space-y-1">
                                            <div className="mb-1 fw-bold text-gray-500 extra-small text-uppercase tracking-widest">
                                                Reference
                                            </div>
                                            <div className="h4 fw-extrabold text-clinical-high mb-3">
                                                {invoice.invoice_number}
                                            </div>
                                            <div className="d-flex flex-column align-items-sm-end">
                                                <div className="small text-muted mb-1">
                                                    <span className="fw-bold text-gray-700">Date:</span>{' '}
                                                    {invoice.invoice_date}
                                                </div>
                                                <div className="small text-muted mb-3">
                                                    <span className="fw-bold text-gray-700">Due:</span>{' '}
                                                    {invoice.due_date}
                                                </div>
                                                <StatusBadge status={invoice.status} />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="row g-4 mb-5">
                                    <div className="col-sm-6">
                                        <div className="p-4 rounded-4 bg-gray-50 border border-gray-100 h-100">
                                            <h6 className="mb-4 text-muted text-uppercase extra-small fw-extrabold tracking-widest opacity-50">
                                                Billed To:
                                            </h6>
                                            <h5 className="mb-1 fw-extrabold text-gray-900">
                                                {invoice.patient?.user?.first_name} {invoice.patient?.user?.last_name}
                                            </h5>
                                            <PatientIdLabel
                                                id={invoice.patient_id}
                                                variant="pat-id"
                                                className="text-muted extra-small fw-bold text-uppercase mb-3 font-mono opacity-75"
                                            />
                                            <div className="space-y-1 opacity-75">
                                                <div className="small text-muted">
                                                    {invoice.patient?.user?.address || 'Address not recorded'}
                                                </div>
                                                <div className="small text-muted">
                                                    {invoice.patient?.user?.phone || 'No phone recorded'}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {invoice.consultation && (
                                        <div className="col-sm-6">
                                            <div className="p-4 rounded-4 bg-white border border-gray-100 border-l-4 border-primary h-100 shadow-sm">
                                                <h6 className="mb-4 text-muted text-uppercase extra-small fw-extrabold tracking-widest opacity-50">
                                                    Service Reference:
                                                </h6>
                                                <div className="small fw-extrabold text-gray-900 mb-2">
                                                    DR. {invoice.consultation.doctor?.user?.last_name?.toUpperCase()}
                                                </div>
                                                <div className="small text-muted mb-2">
                                                    <span className="fw-bold text-gray-600">Diagnosis:</span>{' '}
                                                    {invoice.consultation.diagnosis}
                                                </div>
                                                <div className="extra-small text-muted font-bold opacity-50">
                                                    VISIT DATE: {invoice.consultation.consultation_date}
                                                </div>
                                            </div>
                                        </div>
                                    )}
                                </div>

                                <div className="table-responsive rounded-3 overflow-hidden border border-gray-100 mb-4">
                                    <table className="table table-hover align-middle mb-0">
                                        <thead className="bg-pink-500 border-0">
                                            <tr>
                                                <th className="px-4 py-3 text-white extra-small fw-extrabold text-uppercase border-0">
                                                    #
                                                </th>
                                                <th className="px-4 py-3 text-white extra-small fw-extrabold text-uppercase border-0">
                                                    Description
                                                </th>
                                                <th className="px-4 py-3 text-white extra-small fw-extrabold text-uppercase border-0 text-end">
                                                    Unit Price
                                                </th>
                                                <th className="px-4 py-3 text-white extra-small fw-extrabold text-uppercase border-0 text-center">
                                                    Qty
                                                </th>
                                                <th className="px-4 py-3 text-white extra-small fw-extrabold text-uppercase border-0 text-end">
                                                    Total
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="border-0">
                                            {invoice.items.map((item, index) => (
                                                <tr key={index} className="border-bottom border-gray-50">
                                                    <td className="px-4 py-3 text-muted small">{index + 1}</td>
                                                    <td className="px-4 py-3 fw-bold text-gray-800">
                                                        {item.description}
                                                    </td>
                                                    <td className="px-4 py-3 text-end text-muted small">
                                                        {formatCurrency(item.unit_price)}
                                                    </td>
                                                    <td className="px-4 py-3 text-center text-gray-700 fw-bold small">
                                                        {item.quantity}
                                                    </td>
                                                    <td className="px-4 py-3 text-end fw-extrabold text-gray-900">
                                                        {formatCurrency(item.total_price)}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                <div className="row mt-4">
                                    <div className="col-lg-5 col-sm-6 ms-auto">
                                        <div className="p-4 rounded-4 bg-gray-50 border border-gray-100">
                                            <div className="space-y-3">
                                                <div className="d-flex justify-content-between align-items-center">
                                                    <span className="text-muted extra-small fw-bold">SUBTOTAL</span>
                                                    <span className="fw-bold text-gray-800">
                                                        {formatCurrency(subtotal)}
                                                    </span>
                                                </div>
                                                {invoice.discount > 0 && (
                                                    <div className="d-flex justify-content-between align-items-center text-danger">
                                                        <span className="extra-small fw-bold">DISCOUNT</span>
                                                        <span className="fw-bold">
                                                            - {formatCurrency(invoice.discount)}
                                                        </span>
                                                    </div>
                                                )}
                                                <div className="d-flex justify-content-between align-items-center border-bottom border-gray-200 pb-3">
                                                    <span className="text-muted extra-small fw-bold text-uppercase">
                                                        Tax ({taxRate}%)
                                                    </span>
                                                    <span className="fw-bold text-gray-800">
                                                        {formatCurrency(taxAmount)}
                                                    </span>
                                                </div>
                                                <div className="d-flex justify-content-between align-items-center pt-2">
                                                    <span className="fw-extrabold text-gray-900">TOTAL DUE</span>
                                                    <span className="h4 fw-extrabold text-clinical-high mb-0">
                                                        {formatCurrency(finalTotal)}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="print-only mt-5 pt-5 border-top">
                            <p className="text-center text-muted small italic mb-5">
                                Computer generated document. Valid without physical signature.
                            </p>
                            <div className="row text-center mt-5">
                                <div className="col-6">
                                    <div className="mx-auto border-top border-gray-300 pt-2 w-50">
                                        <span className="extra-small fw-extrabold text-gray-500 uppercase tracking-widest">
                                            Authorized Signatory
                                        </span>
                                    </div>
                                </div>
                                <div className="col-6">
                                    <div className="mx-auto border-top border-gray-300 pt-2 w-50">
                                        <span className="extra-small fw-extrabold text-gray-500 uppercase tracking-widest">
                                            Patient / Guardian
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div className="text-center mt-8 opacity-25">
                                <p className="extra-small font-bold text-uppercase tracking-tightest">
                                    Printed on {formatDateTime(new Date())}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="col-lg-4 no-print">
                        <div className="card shadow-sm border-0 mb-4 bg-primary text-white rounded-4 shadow-hover overflow-hidden position-relative">
                            <div className="card-body p-5 position-relative z-10">
                                <h6 className="mb-4 fw-extrabold border-bottom border-white border-opacity-25 pb-3 text-uppercase tracking-widest extra-small">
                                    Payment Summary
                                </h6>
                                <div className="space-y-4">
                                    <div className="d-flex justify-content-between align-items-center">
                                        <span className="opacity-75 extra-small fw-bold">AMOUNT PAID</span>
                                        <span className="fw-extrabold small">{formatCurrency(amountPaid)}</span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center">
                                        <span className="opacity-75 extra-small fw-bold">BALANCE DUE</span>
                                        <span className="fw-extrabold small">{formatCurrency(balanceDue)}</span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center">
                                        <span className="opacity-75 extra-small fw-bold">METHOD</span>
                                        <span className="fw-extrabold small text-uppercase">
                                            {invoice.payment_method || 'PENDING'}
                                        </span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center">
                                        <span className="opacity-75 extra-small fw-bold">CURRENT STATUS</span>
                                        <StatusBadge status={invoice.status} />
                                    </div>
                                    <div className="pt-4 border-top border-white border-opacity-10 text-center">
                                        <div className="display-5 fw-extrabold tracking-tightest">
                                            {formatCurrency(finalTotal)}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <i
                                className="fas fa-coins position-absolute text-white opacity-10"
                                style={{
                                    right: '-2rem',
                                    bottom: '-2rem',
                                    fontSize: '10rem',
                                    transform: 'rotate(-15deg)',
                                }}
                            ></i>
                        </div>

                        {payments.length > 0 && (
                            <div className="card shadow-sm border-0 mb-4 rounded-4 bg-white">
                                <div className="card-header bg-white py-3 border-bottom-0 pt-4 px-4">
                                    <h6 className="mb-0 fw-extrabold text-gray-400 extra-small text-uppercase tracking-widest">
                                        Payment History
                                    </h6>
                                </div>
                                <div className="card-body p-4 pt-0">
                                    <ul className="list-group list-group-flush">
                                        {payments.map((p) => (
                                            <li
                                                key={p.payment_id}
                                                className="list-group-item px-0 d-flex justify-content-between align-items-center"
                                            >
                                                <div>
                                                    <Link
                                                        href={route('payments.show', p.payment_id)}
                                                        className="fw-bold text-success text-decoration-none"
                                                    >
                                                        {formatCurrency(p.amount)}
                                                    </Link>
                                                    <div className="extra-small text-muted">
                                                        {p.payment_method_label} · {p.payment_date}
                                                    </div>
                                                </div>
                                                <StatusBadge status={p.payment_status} />
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            </div>
                        )}

                        <div className="card shadow-sm border-0 rounded-4 bg-white shadow-hover">
                            <div className="card-header bg-white py-3 border-bottom-0 pt-4 px-4">
                                <h6 className="mb-0 fw-extrabold text-gray-400 extra-small text-uppercase tracking-widest">
                                    Remarks
                                </h6>
                            </div>
                            <div className="card-body p-4 pt-0 text-muted small leading-relaxed">
                                {invoice.notes || 'No internal notes available for this financial record.'}
                            </div>
                        </div>
                    </div>
                </div>

                <UnifiedToolbar
                    actions={[
                        !['paid', 'cancelled'].includes(invoice.status) &&
                            balanceDue > 0 &&
                            auth.user.role !== 'patient' && {
                                label: 'RECORD PAYMENT',
                                icon: 'fa-money-bill-wave',
                                onClick: openPaymentModal,
                                color: 'success',
                            },
                        invoice.status !== 'paid' &&
                            auth.user.role !== 'patient' && {
                                label: 'VOID INVOICE',
                                icon: 'fa-ban',
                                onClick: () => setIsVoidModalOpen(true),
                                color: 'danger',
                            },
                        {
                            label: 'PRINT INVOICE',
                            icon: 'fa-print',
                            onClick: handlePrint,
                        },
                        {
                            label: 'DOWNLOAD PDF',
                            icon: 'fa-file-pdf',
                            onClick: handleDownloadPdf,
                            color: 'danger',
                        },
                        {
                            label: 'FINANCIAL REGISTRY',
                            icon: 'fa-list',
                            href: route('invoices.index'),
                            color: 'gray',
                        },
                    ].filter(Boolean)}
                />
            </div>

            {/* Void Invoice Modal */}
            {isVoidModalOpen && (
                <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1050 }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 shadow-lg rounded-4">
                            <div className="modal-header border-bottom-0 p-4">
                                <h5 className="modal-title fw-bold text-danger">
                                    <i className="fas fa-exclamation-triangle me-2"></i> Void Invoice
                                </h5>
                                <button
                                    type="button"
                                    aria-label="Close"
                                    className="btn-close"
                                    onClick={() => {
                                        setIsVoidModalOpen(false);
                                        resetVoid();
                                    }}
                                ></button>
                            </div>
                            <div className="modal-body p-4 pt-0">
                                <p className="text-muted mb-4">
                                    Are you sure you want to void this invoice <strong>{invoice.invoice_number}</strong>
                                    ? This action will mark it as voided but keep it in the system for auditing
                                    purposes.
                                </p>

                                <div className="mb-3">
                                    <label className="form-label fw-bold text-muted extra-small text-uppercase tracking-widest">
                                        Reason for Voiding
                                    </label>
                                    <textarea
                                        className="form-control bg-light border-0"
                                        rows="3"
                                        placeholder="e.g., Incorrect billing amount, client request, entry error..."
                                        value={voidData.void_reason}
                                        onChange={(e) => setVoidData('void_reason', e.target.value)}
                                        required
                                    ></textarea>
                                </div>
                            </div>
                            <div className="modal-footer border-top-0 p-4">
                                <button
                                    type="button"
                                    className="btn btn-light rounded-pill px-4 fw-bold"
                                    onClick={() => {
                                        setIsVoidModalOpen(false);
                                        resetVoid();
                                    }}
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-danger rounded-pill px-4 fw-bold shadow-sm"
                                    disabled={!voidData.void_reason || voidProcessing}
                                    onClick={handleVoid}
                                >
                                    {voidProcessing ? 'Voiding...' : 'Void Invoice'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Record Payment Modal */}
            {isPaymentModalOpen && (
                <div className="modal fade show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)', zIndex: 1050 }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content border-0 shadow-lg rounded-4">
                            <form onSubmit={submitPayment}>
                                <div className="modal-header border-bottom-0 p-4">
                                    <h5 className="modal-title fw-bold text-success">
                                        <i className="fas fa-money-bill-wave me-2"></i> Record Payment
                                    </h5>
                                    <button
                                        type="button"
                                        aria-label="Close"
                                        className="btn-close"
                                        onClick={() => {
                                            setIsPaymentModalOpen(false);
                                            resetPayment();
                                        }}
                                    ></button>
                                </div>
                                <div className="modal-body p-4 pt-0">
                                    <p className="text-muted small mb-4">
                                        Invoice <strong>{invoice.invoice_number}</strong> — balance due:{' '}
                                        <strong>{formatCurrency(balanceDue)}</strong>
                                    </p>
                                    <div className="mb-3">
                                        <label className="form-label fw-bold text-muted extra-small text-uppercase">
                                            Amount (Ksh)
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            className={`form-control ${paymentErrors.amount ? 'is-invalid' : ''}`}
                                            value={paymentData.amount}
                                            onChange={(e) => setPaymentData('amount', e.target.value)}
                                            required
                                        />
                                        {paymentErrors.amount && (
                                            <div className="invalid-feedback d-block">{paymentErrors.amount}</div>
                                        )}
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label fw-bold text-muted extra-small text-uppercase">
                                            Payment Method
                                        </label>
                                        <select
                                            className="form-select"
                                            value={paymentData.payment_method}
                                            onChange={(e) => setPaymentData('payment_method', e.target.value)}
                                        >
                                            {methodOptions.map((opt) => (
                                                <option key={opt.value} value={opt.value}>
                                                    {opt.label}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label fw-bold text-muted extra-small text-uppercase">
                                            Date
                                        </label>
                                        <input
                                            type="datetime-local"
                                            className="form-control"
                                            value={paymentData.payment_date}
                                            onChange={(e) => setPaymentData('payment_date', e.target.value)}
                                            required
                                        />
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label fw-bold text-muted extra-small text-uppercase">
                                            Reference (optional)
                                        </label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            placeholder="M-Pesa code, etc."
                                            value={paymentData.transaction_reference}
                                            onChange={(e) => setPaymentData('transaction_reference', e.target.value)}
                                        />
                                    </div>
                                </div>
                                <div className="modal-footer border-top-0 p-4">
                                    <button
                                        type="button"
                                        className="btn btn-light rounded-pill px-4 fw-bold"
                                        onClick={() => {
                                            setIsPaymentModalOpen(false);
                                            resetPayment();
                                        }}
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        className="btn btn-success rounded-pill px-4 fw-bold shadow-sm"
                                        disabled={paymentProcessing}
                                    >
                                        {paymentProcessing ? 'Saving...' : 'Record Payment'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
