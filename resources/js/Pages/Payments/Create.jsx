import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import FormSection from '@/Components/FormSection';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import FormField from '@/Components/FormField';
import DashboardSelect from '@/Components/DashboardSelect';
import { useEffect, useState } from 'react';
import { formatCurrency } from '@/Utils/formatUtils';

export default function Create({ invoice, pendingInvoices, paymentMethods, preselected_invoice_id }) {
    const defaultAmount = invoice?.balance_due ?? invoice?.total_amount ?? '';

    const { data, setData, post, processing, errors } = useForm({
        invoice_id: preselected_invoice_id || invoice?.invoice_id || '',
        amount: defaultAmount ? String(defaultAmount) : '',
        payment_method: 'cash',
        payment_date: new Date().toISOString().slice(0, 16),
        transaction_reference: '',
        payment_status: 'completed',
        notes: '',
    });

    const [selectedInvoice, setSelectedInvoice] = useState(invoice);

    useEffect(() => {
        if (invoice) {
            setSelectedInvoice(invoice);
            setData('invoice_id', invoice.invoice_id);
            if (invoice.balance_due != null) {
                setData('amount', String(invoice.balance_due));
            }
        }
    }, [invoice]);

    const invoiceOptions = (pendingInvoices?.data || pendingInvoices || []).map((inv) => ({
        label: `${inv.invoice_number} — ${inv.patient?.user?.first_name || ''} ${inv.patient?.user?.last_name || ''} (${formatCurrency(inv.total_amount)})`,
        value: String(inv.invoice_id),
    }));

    const methodOptions = Object.entries(paymentMethods || {}).map(([value, label]) => ({ label, value }));

    const handleInvoiceChange = (invoiceId) => {
        setData('invoice_id', invoiceId);
        const list = pendingInvoices?.data || pendingInvoices || [];
        const found = list.find((inv) => String(inv.invoice_id) === String(invoiceId));
        if (found) {
            setSelectedInvoice(found);
            setData('amount', String(found.balance_due ?? found.total_amount ?? ''));
        }
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('payments.store'));
    };

    return (
        <AuthenticatedLayout
            headerTitle="Record Payment"
            breadcrumbs={[
                { label: 'Payments', url: route('payments.index') },
                { label: 'Record Payment', active: true },
            ]}
        >
            <Head title="Record Payment" />

            <form onSubmit={submit}>
                <div className="row g-4">
                    <div className="col-lg-8">
                        <FormSection title="Payment Details" icon="fas fa-money-bill-wave" headerClassName="bg-success text-white p-3">
                            <div className="row g-3">
                                <FormField label="Invoice *" className="col-12" error={errors.invoice_id}>
                                    <DashboardSelect
                                        options={invoiceOptions}
                                        value={String(data.invoice_id)}
                                        onChange={handleInvoiceChange}
                                        placeholder="Select invoice..."
                                    />
                                </FormField>

                                <FormField label="Amount (Ksh) *" className="col-md-6" error={errors.amount}>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        className="form-control"
                                        value={data.amount}
                                        onChange={(e) => setData('amount', e.target.value)}
                                        required
                                    />
                                </FormField>

                                <FormField label="Payment Method *" className="col-md-6" error={errors.payment_method}>
                                    <DashboardSelect
                                        options={methodOptions}
                                        value={data.payment_method}
                                        onChange={(val) => setData('payment_method', val)}
                                    />
                                </FormField>

                                <FormField label="Payment Date *" className="col-md-6" error={errors.payment_date}>
                                    <input
                                        type="datetime-local"
                                        className="form-control"
                                        value={data.payment_date}
                                        onChange={(e) => setData('payment_date', e.target.value)}
                                        required
                                    />
                                </FormField>

                                <FormField label="Transaction Reference" className="col-md-6" error={errors.transaction_reference}>
                                    <input
                                        type="text"
                                        className="form-control"
                                        placeholder="M-Pesa code, cheque #, etc."
                                        value={data.transaction_reference}
                                        onChange={(e) => setData('transaction_reference', e.target.value)}
                                    />
                                </FormField>

                                <FormField label="Status" className="col-md-6" error={errors.payment_status}>
                                    <DashboardSelect
                                        options={[
                                            { label: 'Completed', value: 'completed' },
                                            { label: 'Pending', value: 'pending' },
                                        ]}
                                        value={data.payment_status}
                                        onChange={(val) => setData('payment_status', val)}
                                    />
                                </FormField>

                                <FormField label="Notes" className="col-12" error={errors.notes}>
                                    <textarea
                                        className="form-control"
                                        rows="3"
                                        value={data.notes}
                                        onChange={(e) => setData('notes', e.target.value)}
                                        placeholder="Optional internal notes..."
                                    />
                                </FormField>
                            </div>
                        </FormSection>
                    </div>

                    <div className="col-lg-4">
                        {selectedInvoice && (
                            <div className="card border-0 shadow-sm rounded-4 mb-4">
                                <div className="card-body p-4">
                                    <h6 className="fw-extrabold text-muted extra-small text-uppercase tracking-widest mb-3">Invoice Summary</h6>
                                    <div className="small text-muted mb-1">Reference</div>
                                    <div className="fw-bold mb-3">{selectedInvoice.invoice_number}</div>
                                    <div className="small text-muted mb-1">Total</div>
                                    <div className="fw-extrabold mb-3">{formatCurrency(selectedInvoice.total_amount)}</div>
                                    {selectedInvoice.amount_paid != null && (
                                        <>
                                            <div className="small text-muted mb-1">Already Paid</div>
                                            <div className="fw-bold text-success mb-3">{formatCurrency(selectedInvoice.amount_paid)}</div>
                                            <div className="small text-muted mb-1">Balance Due</div>
                                            <div className="h4 fw-extrabold text-clinical-high">{formatCurrency(selectedInvoice.balance_due ?? 0)}</div>
                                        </>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                <UnifiedToolbar
                    actions={[
                        { label: 'SAVE PAYMENT', icon: 'fa-check', onClick: () => post(route('payments.store')), color: 'success', disabled: processing },
                        { label: 'CANCEL', icon: 'fa-times', href: route('payments.index'), color: 'gray' },
                    ]}
                />
            </form>
        </AuthenticatedLayout>
    );
}
