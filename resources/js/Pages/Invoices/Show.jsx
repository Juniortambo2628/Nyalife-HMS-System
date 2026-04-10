import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { useRef } from 'react';

export default function Show({ invoice, auth }) {
    const handlePrint = () => {
        window.print();
    };

    const markAsPaid = () => {
        if (confirm('Are you sure you want to mark this invoice as PAID?')) {
            router.put(route('invoices.update', invoice.invoice_id), {
                status: 'paid',
                payment_method: 'Access Code / Cash' // Default for now, can be a modal later
            });
        }
    };

    return (
        <AuthenticatedLayout
            header="Invoice Details"
        >
            <Head title={`Invoice - ${invoice.invoice_number}`} />

            <style>{`
                @media print {
                    body * {
                        visibility: hidden;
                    }
                    #invoice-content, #invoice-content * {
                        visibility: visible;
                    }
                    #invoice-content {
                        position: absolute;
                        left: 0;
                        top: 0;
                        width: 100%;
                    }
                    .no-print {
                        display: none !important;
                    }
                }
            `}</style>

            <PageHeader 
                title={`Invoice ${invoice.invoice_number}`}
                breadcrumbs={[
                    { label: 'Billing', url: route('invoices.index') },
                    { label: 'Details', active: true }
                ]}
                actions={
                    <button 
                        onClick={handlePrint}
                        className="btn btn-outline-primary rounded-pill px-4 font-bold shadow-sm"
                    >
                        <i className="fas fa-print me-2"></i>Print Invoice
                    </button>
                }
                className="no-print"
            />

            <div className="px-0 py-0" id="invoice-content">

                <div className="row">
                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 mb-4 rounded-4">
                            <div className="card-body p-5">
                                <div className="row mb-5">
                                    <div className="col-sm-6">
                                        <h5 className="mb-3 text-primary fw-bold">Nyalife HMS</h5>
                                        <div>123 Medical Dr, Nairobi</div>
                                        <div>Email: info@nyalife.com</div>
                                        <div>Phone: +254 712 345 678</div>
                                    </div>
                                    <div className="col-sm-6 text-sm-end mt-4 mt-sm-0">
                                        <h5 className="mb-3 text-uppercase fw-bold text-secondary">Invoice</h5>
                                        <div className="mb-1"><strong>Invoice #:</strong> {invoice.invoice_number}</div>
                                        <div className="mb-1"><strong>Date:</strong> {invoice.invoice_date}</div>
                                        <div><strong>Status:</strong> <span className={`badge ${invoice.status === 'paid' ? 'bg-success' : 'bg-warning text-dark'}`}>{invoice.status.toUpperCase()}</span></div>
                                    </div>
                                </div>

                                <div className="row mb-5">
                                    <div className="col-sm-6">
                                        <h6 className="mb-3 text-muted text-uppercase small fw-bold">Billed To:</h6>
                                        <h5 className="mb-1 text-dark">{invoice.patient?.user?.first_name || 'Unknown'} {invoice.patient?.user?.last_name || 'Patient'}</h5>
                                        <div className="text-muted">{invoice.patient?.user?.address || 'Address not recorded'}</div>
                                        <div className="text-muted">Email: {invoice.patient?.user?.email || 'N/A'}</div>
                                        <div className="text-muted">Phone: {invoice.patient?.user?.phone || 'N/A'}</div>
                                    </div>
                                </div>

                                <div className="table-responsive-sm">
                                    <table className="table table-striped align-middle">
                                        <thead className="bg-light table-light">
                                            <tr>
                                                <th className="center">#</th>
                                                <th>Description</th>
                                                <th className="text-end">Unit Cost</th>
                                                <th className="text-center">Qty</th>
                                                <th className="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {invoice.items.map((item, index) => (
                                                <tr key={index}>
                                                    <td className="center">{index + 1}</td>
                                                    <td className="left fw-bold">{item.description}</td>
                                                    <td className="text-end">Ksh {Number(item.unit_price).toLocaleString()}</td>
                                                    <td className="text-center">{item.quantity}</td>
                                                    <td className="text-end fw-bold">Ksh {Number(item.total_price).toLocaleString()}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                <div className="row mt-4">
                                    <div className="col-lg-5 col-sm-6 ms-auto">
                                        <table className="table table-clear">
                                            <tbody>
                                                <tr>
                                                    <td className="left"><strong>Subtotal</strong></td>
                                                    <td className="text-end">Ksh {Number(invoice.total_amount).toLocaleString()}</td>
                                                </tr>
                                                {invoice.discount > 0 && (
                                                    <tr>
                                                        <td className="left"><strong>Discount</strong></td>
                                                        <td className="text-end">Ksh {Number(invoice.discount).toLocaleString()}</td>
                                                    </tr>
                                                )}
                                                <tr>
                                                    <td className="left"><strong>Tax (0%)</strong></td>
                                                    <td className="text-end">Ksh 0.00</td>
                                                </tr>
                                                <tr className="bg-light">
                                                    <td className="left h5 mb-0 fw-bold">Total</td>
                                                    <td className="text-end h5 mb-0 fw-bold text-primary">Ksh {Number(invoice.total_amount).toLocaleString()}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="col-lg-4 no-print">
                        <div className="card shadow-sm border-0 mb-4 bg-primary text-white rounded-4">
                            <div className="card-body p-4">
                                <h6 className="mb-3 fw-bold border-bottom border-white pb-2 op-50">Payment Information</h6>
                                <div className="mb-2 d-flex justify-content-between">
                                    <span>Method:</span>
                                    <span className="fw-bold">{invoice.payment_method || 'N/A'}</span>
                                </div>
                                <div className="mb-4 d-flex justify-content-between">
                                    <span>Status:</span>
                                    <span className="fw-bold text-uppercase">{invoice.status}</span>
                                </div>
                                
                                {invoice.status !== 'paid' && (
                                    <button 
                                        onClick={markAsPaid}
                                        className="btn btn-light w-100 fw-bold text-primary py-3 rounded-pill shadow-sm"
                                    >
                                        <i className="fas fa-check-circle me-2"></i>Mark as Paid
                                    </button>
                                )}
                            </div>
                        </div>
                        
                        <div className="card shadow-sm border-0 rounded-4">
                            <div className="card-header bg-white py-3 border-bottom">
                                <h6 className="mb-0 fw-bold text-secondary">Notes</h6>
                            </div>
                            <div className="card-body p-4 text-muted">
                                {invoice.notes || 'No notes available for this invoice.'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
