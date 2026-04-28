import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Show({ invoice, auth, clinic_settings = {} }) {
    const handlePrint = () => window.print();

    const taxRate = parseFloat(clinic_settings.tax_rate || 0);
    const subtotal = invoice.items.reduce((acc, item) => acc + Number(item.total_price), 0);
    const taxAmount = subtotal * (taxRate / 100);
    const finalTotal = subtotal + taxAmount - Number(invoice.discount || 0);

    const markAsPaid = () => {
        if (confirm('Are you sure you want to mark this invoice as PAID?')) {
            router.put(route('invoices.update', invoice.invoice_id), {
                status: 'paid',
                payment_method: 'Access Code / Cash'
            });
        }
    };

    return (
        <AuthenticatedLayout header="Invoice Details">
            <Head title={`Invoice - ${invoice.invoice_number}`} />

            <PageHeader 
                title={`Financial Document`}
                breadcrumbs={[
                    { label: 'Billing', url: route('invoices.index') },
                    { label: invoice.invoice_number, active: true }
                ]}
                showBack={true}
                className="no-print"
            />

            <div className="px-0 py-0" id="invoice-content">
                <div className="row g-4">
                    <div className="col-lg-8">
                        <div className="card shadow-sm border-0 mb-5 rounded-4 overflow-hidden bg-white">
                            <div className="card-body p-5">
                                <div className="row mb-5 align-items-center">
                                    <div className="col-sm-6">
                                        <div className="d-flex align-items-center gap-3 mb-4">
                                            <div className="bg-white rounded-xl p-2 shadow-sm border border-light">
                                                <img src="/assets/img/logo/Logo2-transparent.png" alt="Nyalife" style={{ height: '70px' }} />
                                            </div>
                                            <div>
                                                <h3 className="mb-0 text-gray-900 fw-extrabold tracking-tightest fs-2">NYALIFE</h3>
                                                <div className="text-clinical-high extra-small font-bold uppercase tracking-widest">Women's Clinic</div>
                                            </div>
                                        </div>
                                        <div className="space-y-1">
                                            <div className="text-muted small"><i className="fas fa-map-marker-alt me-2 text-pink-400"></i>{clinic_settings.contact_address || 'Sabaki, Athi River, Machakos'}</div>
                                            <div className="text-muted small"><i className="fas fa-envelope me-2 text-pink-400"></i>{clinic_settings.contact_email || 'info@nyalifewomensclinic.com'}</div>
                                            <div className="text-muted small"><i className="fas fa-phone-alt me-2 text-pink-400"></i>{clinic_settings.contact_phone || '+254 746 516 514'}</div>
                                        </div>
                                    </div>
                                    <div className="col-sm-6 text-sm-end mt-4 mt-sm-0">
                                        <h1 className="mb-3 text-uppercase fw-extrabold text-gray-900 tracking-tightest opacity-10 display-4 d-none d-sm-block">RECEIPT</h1>
                                        <div className="space-y-1">
                                            <div className="mb-1 fw-bold text-gray-500 extra-small text-uppercase tracking-widest">Reference</div>
                                            <div className="h4 fw-extrabold text-clinical-high mb-3">{invoice.invoice_number}</div>
                                            <div className="d-flex flex-column align-items-sm-end">
                                                <div className="small text-muted mb-1"><span className="fw-bold text-gray-700">Date:</span> {invoice.invoice_date}</div>
                                                <div className="small text-muted mb-3"><span className="fw-bold text-gray-700">Due:</span> {invoice.due_date}</div>
                                                <StatusBadge status={invoice.status} />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div className="row g-4 mb-5">
                                    <div className="col-sm-6">
                                        <div className="p-4 rounded-4 bg-gray-50 border border-gray-100 h-100">
                                            <h6 className="mb-4 text-muted text-uppercase extra-small fw-extrabold tracking-widest opacity-50">Billed To:</h6>
                                            <h5 className="mb-1 fw-extrabold text-gray-900">{invoice.patient?.user?.first_name} {invoice.patient?.user?.last_name}</h5>
                                            <div className="text-muted extra-small fw-bold text-uppercase mb-3 font-mono opacity-75">PAT-ID: {invoice.patient_id}</div>
                                            <div className="space-y-1 opacity-75">
                                                <div className="small text-muted">{invoice.patient?.user?.address || 'Address not recorded'}</div>
                                                <div className="small text-muted">{invoice.patient?.user?.phone || 'No phone recorded'}</div>
                                            </div>
                                        </div>
                                    </div>
                                    {invoice.consultation && (
                                        <div className="col-sm-6">
                                            <div className="p-4 rounded-4 bg-white border border-gray-100 border-l-4 border-primary h-100 shadow-sm">
                                                <h6 className="mb-4 text-muted text-uppercase extra-small fw-extrabold tracking-widest opacity-50">Service Reference:</h6>
                                                <div className="small fw-extrabold text-gray-900 mb-2">DR. {invoice.consultation.doctor?.user?.last_name?.toUpperCase()}</div>
                                                <div className="small text-muted mb-2"><span className="fw-bold text-gray-600">Diagnosis:</span> {invoice.consultation.diagnosis}</div>
                                                <div className="extra-small text-muted font-bold opacity-50">VISIT DATE: {invoice.consultation.consultation_date}</div>
                                            </div>
                                        </div>
                                    )}
                                </div>

                                <div className="table-responsive rounded-3 overflow-hidden border border-gray-100 mb-4">
                                    <table className="table table-hover align-middle mb-0">
                                        <thead className="bg-pink-500 border-0">
                                            <tr>
                                                <th className="px-4 py-3 text-white extra-small fw-extrabold text-uppercase border-0">#</th>
                                                <th className="px-4 py-3 text-white extra-small fw-extrabold text-uppercase border-0">Description</th>
                                                <th className="px-4 py-3 text-white extra-small fw-extrabold text-uppercase border-0 text-end">Unit Price</th>
                                                <th className="px-4 py-3 text-white extra-small fw-extrabold text-uppercase border-0 text-center">Qty</th>
                                                <th className="px-4 py-3 text-white extra-small fw-extrabold text-uppercase border-0 text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody className="border-0">
                                            {invoice.items.map((item, index) => (
                                                <tr key={index} className="border-bottom border-gray-50">
                                                    <td className="px-4 py-3 text-muted small">{index + 1}</td>
                                                    <td className="px-4 py-3 fw-bold text-gray-800">{item.description}</td>
                                                    <td className="px-4 py-3 text-end text-muted small">Ksh {Number(item.unit_price).toLocaleString()}</td>
                                                    <td className="px-4 py-3 text-center text-gray-700 fw-bold small">{item.quantity}</td>
                                                    <td className="px-4 py-3 text-end fw-extrabold text-gray-900">Ksh {Number(item.total_price).toLocaleString()}</td>
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
                                                    <span className="fw-bold text-gray-800">Ksh {subtotal.toLocaleString()}</span>
                                                </div>
                                                {invoice.discount > 0 && (
                                                    <div className="d-flex justify-content-between align-items-center text-danger">
                                                        <span className="extra-small fw-bold">DISCOUNT</span>
                                                        <span className="fw-bold">- Ksh {Number(invoice.discount).toLocaleString()}</span>
                                                    </div>
                                                )}
                                                <div className="d-flex justify-content-between align-items-center border-bottom border-gray-200 pb-3">
                                                    <span className="text-muted extra-small fw-bold text-uppercase">Tax ({taxRate}%)</span>
                                                    <span className="fw-bold text-gray-800">Ksh {taxAmount.toLocaleString()}</span>
                                                </div>
                                                 <div className="d-flex justify-content-between align-items-center pt-2">
                                                     <span className="fw-extrabold text-gray-900">TOTAL DUE</span>
                                                     <span className="h4 fw-extrabold text-clinical-high mb-0">Ksh {finalTotal.toLocaleString()}</span>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div className="print-only mt-5 pt-5 border-top">
                            <p className="text-center text-muted small italic mb-5">Computer generated document. Valid without physical signature.</p>
                            <div className="row text-center mt-5">
                                <div className="col-6">
                                    <div className="mx-auto border-top border-gray-300 pt-2 w-50">
                                        <span className="extra-small fw-extrabold text-gray-500 uppercase tracking-widest">Authorized Signatory</span>
                                    </div>
                                </div>
                                <div className="col-6">
                                    <div className="mx-auto border-top border-gray-300 pt-2 w-50">
                                        <span className="extra-small fw-extrabold text-gray-500 uppercase tracking-widest">Patient / Guardian</span>
                                    </div>
                                </div>
                            </div>
                            <div className="text-center mt-8 opacity-25">
                                <p className="extra-small font-bold text-uppercase tracking-tightest">Printed on {new Date().toLocaleString()}</p>
                            </div>
                        </div>
                    </div>

                    <div className="col-lg-4 no-print">
                        <div className="card shadow-sm border-0 mb-4 bg-primary text-white rounded-4 shadow-hover overflow-hidden position-relative">
                            <div className="card-body p-5 position-relative z-10">
                                <h6 className="mb-4 fw-extrabold border-bottom border-white border-opacity-25 pb-3 text-uppercase tracking-widest extra-small">Payment Summary</h6>
                                <div className="space-y-4">
                                    <div className="d-flex justify-content-between align-items-center">
                                        <span className="opacity-75 extra-small fw-bold">METHOD</span>
                                        <span className="fw-extrabold small text-uppercase">{invoice.payment_method || 'PENDING'}</span>
                                    </div>
                                    <div className="d-flex justify-content-between align-items-center">
                                        <span className="opacity-75 extra-small fw-bold">CURRENT STATUS</span>
                                        <StatusBadge status={invoice.status} />
                                    </div>
                                    <div className="pt-4 border-top border-white border-opacity-10 text-center">
                                        <div className="display-5 fw-extrabold tracking-tightest">
                                            <span className="small opacity-50 me-2" style={{ fontSize: '0.4em' }}>Ksh</span>
                                            {finalTotal.toLocaleString()}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <i className="fas fa-coins position-absolute text-white opacity-10" style={{ right: '-2rem', bottom: '-2rem', fontSize: '10rem', transform: 'rotate(-15deg)' }}></i>
                        </div>
                        
                        <div className="card shadow-sm border-0 rounded-4 bg-white shadow-hover">
                            <div className="card-header bg-white py-3 border-bottom-0 pt-4 px-4">
                                <h6 className="mb-0 fw-extrabold text-gray-400 extra-small text-uppercase tracking-widest">Remarks</h6>
                            </div>
                            <div className="card-body p-4 pt-0 text-muted small leading-relaxed">
                                {invoice.notes || 'No internal notes available for this financial record.'}
                            </div>
                        </div>
                    </div>
                </div>

            <UnifiedToolbar 
                actions={[
                    invoice.status !== 'paid' && auth.user.role !== 'patient' && { 
                        label: 'FINALIZE PAYMENT', 
                        icon: 'fa-check-circle', 
                        onClick: markAsPaid,
                        color: 'success'
                    },
                    { 
                        label: 'PRINT RECEIPT', 
                        icon: 'fa-print', 
                        onClick: handlePrint 
                    },
                    { 
                        label: 'FINANCIAL REGISTRY', 
                        icon: 'fa-list', 
                        href: route('invoices.index'),
                        color: 'gray'
                    }
                ].filter(Boolean)}
            />
            </div>
        </AuthenticatedLayout>
    );
}
