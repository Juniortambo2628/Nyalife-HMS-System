import { Head } from '@inertiajs/react';
import { useEffect } from 'react';
import { formatCurrency } from '@/Utils/formatUtils';
import { formatPatientId } from '@/Components/PatientTableCell';

export default function Print({ invoice, clinic_settings = {} }) {
    useEffect(() => {
        window.print();
    }, []);

    const patient = invoice.patient?.user;
    const subtotal = (invoice.items || []).reduce((acc, item) => acc + Number(item.total_price || 0), 0);
    const taxRate = parseFloat(clinic_settings.tax_rate || 0);
    const taxAmount = subtotal * (taxRate / 100);
    const total = subtotal + taxAmount - Number(invoice.discount || 0);
    const paid = invoice.amount_paid ?? 0;
    const balance = invoice.balance_due ?? Math.max(0, total - paid);

    return (
        <div className="invoice-print p-4 bg-white">
            <Head title={`Invoice ${invoice.invoice_number}`} />

            <div className="header">
                <img
                    src="/assets/logo/Logo2-transparent.png"
                    alt="Nyalife"
                    style={{ maxWidth: '120px', marginBottom: '10px' }}
                />
                <div className="hospital-name">Nyalife Women&apos;s Clinic</div>
                <div className="hospital-info">{clinic_settings.contact_address}</div>
                <div className="hospital-info">
                    {clinic_settings.contact_phone} | {clinic_settings.contact_email}
                </div>
            </div>

            <div className="invoice-header">
                <div className="invoice-details">
                    <div className="invoice-number">{invoice.invoice_number}</div>
                    <div className="invoice-info">
                        Date: {invoice.invoice_date}
                        <br />
                        Due: {invoice.due_date || '—'}
                        <br />
                        Status: {invoice.status?.toUpperCase()}
                    </div>
                </div>
                <div className="patient-info">
                    <div className="patient-name">
                        {patient?.first_name} {patient?.last_name}
                    </div>
                    <div className="patient-details">
                        {formatPatientId(invoice.patient_id)}
                        <br />
                        {patient?.phone || ''}
                        <br />
                        {patient?.email || ''}
                    </div>
                </div>
            </div>

            <table className="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    {(invoice.items || []).map((item, idx) => (
                        <tr key={idx}>
                            <td>{item.description || item.item_name}</td>
                            <td>{item.quantity}</td>
                            <td>{formatCurrency(item.unit_price, { maximumFractionDigits: 2 })}</td>
                            <td>{formatCurrency(item.total_price, { maximumFractionDigits: 2 })}</td>
                        </tr>
                    ))}
                </tbody>
            </table>

            <div className="total-section">
                <div>Subtotal: {formatCurrency(subtotal)}</div>
                {taxRate > 0 && (
                    <div>
                        Tax ({taxRate}%): {formatCurrency(taxAmount)}
                    </div>
                )}
                {Number(invoice.discount) > 0 && <div>Discount: -{formatCurrency(invoice.discount)}</div>}
                <div className="total-row total-amount">Total: {formatCurrency(total)}</div>
                {paid > 0 && <div>Paid: {formatCurrency(paid)}</div>}
                {balance > 0 && <div className="fw-bold">Balance Due: {formatCurrency(balance)}</div>}
            </div>

            {invoice.notes && (
                <div className="mt-4 small text-muted">
                    <strong>Notes:</strong> {invoice.notes}
                </div>
            )}

            <div className="mt-5 text-center small text-muted">Thank you for choosing Nyalife Women&apos;s Clinic</div>

            <style>{`
                @media print { .no-print { display: none !important; } body { margin: 0; background: #fff; } }
                .invoice-print { font-family: Arial, sans-serif; color: #333; }
                .invoice-print .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 16px; margin-bottom: 24px; }
                .invoice-print .hospital-name { font-size: 22px; font-weight: bold; }
                .invoice-print .hospital-info { font-size: 13px; color: #666; }
                .invoice-print .invoice-header { display: flex; justify-content: space-between; margin-bottom: 24px; }
                .invoice-print .invoice-number { font-size: 18px; font-weight: bold; }
                .invoice-print .patient-name { font-size: 16px; font-weight: bold; }
                .invoice-print .items-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
                .invoice-print .items-table th, .invoice-print .items-table td { border: 1px solid #dee2e6; padding: 10px; text-align: left; }
                .invoice-print .items-table th { background: #f8f9fa; }
                .invoice-print .total-section { text-align: right; }
                .invoice-print .total-amount { font-size: 18px; color: #20c997; font-weight: bold; }
            `}</style>
        </div>
    );
}
