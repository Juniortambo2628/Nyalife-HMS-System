import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react'; // Import Link
import PageHeader from '@/Components/PageHeader';
import FormSection from '@/Components/FormSection';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import FormField from '@/Components/FormField';
import TextInput from '@/Components/TextInput';
import DashboardSelect from '@/Components/DashboardSelect'; // Import DashboardSelect
import { useState, useEffect } from 'react';

export default function Create({ patient_id, consultation_id, consultation, consultation_fee, auth }) {
    const { data, setData, post, processing, errors } = useForm({
        patient_id: patient_id || '',
        consultation_id: consultation_id || '',
        invoice_date: new Date().toISOString().slice(0, 10),
        due_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10), // Default 7 days due
        items: [
            { description: 'Consultation Fee', quantity: 1, unit_price: consultation_fee || 0 },
        ],
        notes: '',
    });

    // Helper to calculate total
    const calculateTotal = (items) => {
        return items.reduce((sum, item) => sum + (Number(item.quantity) * Number(item.unit_price)), 0);
    };

    const [totalAmount, setTotalAmount] = useState(calculateTotal(data.items));

    useEffect(() => {
        setTotalAmount(calculateTotal(data.items));
    }, [data.items]);

    const handleItemChange = (index, field, value) => {
        const newItems = [...data.items];
        newItems[index][field] = value;
        setData('items', newItems);
    };

    const addItem = () => {
        setData('items', [...data.items, { description: '', quantity: 1, unit_price: 0 }]);
    };

    const removeItem = (index) => {
        const newItems = data.items.filter((_, i) => i !== index);
        setData('items', newItems);
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('invoices.store'));
    };

    return (
        <AuthenticatedLayout
            header="Create Invoice"
        >
            <Head title="Create Invoice" />

            <PageHeader 
                title="Generate New Invoice"
                breadcrumbs={[
                    { label: 'Billing', url: route('invoices.index') },
                    { label: 'Create Invoice', active: true }
                ]}
            />

            <div className="container-fluid px-0 pb-5">
                <form onSubmit={submit}>
                    <div className="row g-4">
                        {/* 1. Invoice Details */}
                        <div className="col-lg-8">
                            <FormSection 
                                title="Invoice Items" 
                                icon="fas fa-file-invoice-dollar"
                                headerClassName="bg-pink-500 text-white p-3"
                            >
                                <div className="table-responsive mb-4">
                                    <table className="table table-borderless">
                                        <thead className="text-secondary small text-uppercase">
                                            <tr>
                                                <th style={{width: '40%'}}>Description</th>
                                                <th style={{width: '15%'}}>Qty</th>
                                                <th style={{width: '20%'}}>Unit Price</th>
                                                <th style={{width: '15%'}} className="text-end">Total</th>
                                                <th style={{width: '10%'}}></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {data.items.map((item, index) => (
                                                <tr key={index} className="align-middle border-bottom border-light">
                                                    <td>
                                                        <TextInput
                                                            placeholder="Item Description"
                                                            value={item.description}
                                                            onChange={e => handleItemChange(index, 'description', e.target.value)}
                                                            className={`w-full ${errors[`items.${index}.description`] ? 'is-invalid' : ''}`}
                                                        />
                                                    </td>
                                                    <td>
                                                         <TextInput
                                                            type="number"
                                                            min="1"
                                                            value={item.quantity}
                                                            onChange={e => handleItemChange(index, 'quantity', e.target.value)}
                                                            className="w-full text-center"
                                                        />
                                                    </td>
                                                    <td>
                                                        <div className="input-group">
                                                            <span className="input-group-text bg-light border-end-0">Ksh</span>
                                                            <TextInput
                                                                type="number"
                                                                min="0"
                                                                value={item.unit_price}
                                                                onChange={e => handleItemChange(index, 'unit_price', e.target.value)}
                                                                className="form-control border-start-0"
                                                                style={{minWidth: '80px'}}
                                                            />
                                                        </div>
                                                    </td>
                                                    <td className="text-end fw-bold">
                                                        Ksh {(item.quantity * item.unit_price).toLocaleString()}
                                                    </td>
                                                    <td className="text-end">
                                                        {data.items.length > 1 && (
                                                            <button 
                                                                type="button" 
                                                                onClick={() => removeItem(index)}
                                                                className="btn btn-sm btn-light text-danger rounded-circle"
                                                                title="Remove Item"
                                                            >
                                                                <i className="fas fa-times"></i>
                                                            </button>
                                                        )}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                                
                                <button 
                                    type="button" 
                                    onClick={addItem}
                                    className="btn btn-outline-pink-500 btn-sm rounded-pill fw-extrabold extra-small tracking-widest px-4 py-2"
                                >
                                    <i className="fas fa-plus me-2"></i> ADD ANOTHER ITEM
                                </button>
                            </FormSection>

                            <FormSection title="Notes / Terms" icon="fas fa-sticky-note" className="mt-4" headerClassName="bg-white border-bottom text-pink-500 p-3 fw-extrabold extra-small text-uppercase tracking-widest">
                                <textarea
                                    className="form-control bg-light border-0 rounded-3 p-3"
                                    rows="3"
                                    placeholder="Add notes, payment terms, or instructions..."
                                    value={data.notes}
                                    onChange={e => setData('notes', e.target.value)}
                                ></textarea>
                            </FormSection>
                        </div>

                        {/* 2. Sidebar: Patient & Summary */}
                        <div className="col-lg-4">
                            <div className="card border-0 shadow-sm rounded-4 mb-4">
                                <div className="card-header bg-white p-4 border-bottom-0">
                                    <h6 className="mb-0 fw-extrabold text-pink-500 extra-small text-uppercase tracking-widest">
                                        <i className="fas fa-user-circle me-2"></i>Bill To
                                    </h6>
                                </div>
                                <div className="card-body p-4">
                                    <FormField label="Select Patient" required error={errors.patient_id}>
                                         <DashboardSelect
                                            asyncUrl="/patients/search"
                                            value={data.patient_id}
                                            onChange={val => setData('patient_id', val)}
                                            initialLabel={consultation?.patient?.user ? `${consultation.patient.user.first_name} ${consultation.patient.user.last_name}` : ''}
                                            placeholder="Search Patient..."
                                            disabled={!!consultation_id} // Lock patient if created from consultation
                                        />
                                    </FormField>

                                    <div className="row g-2 mt-2">
                                        <div className="col-6">
                                            <FormField label="Invoice Date" required error={errors.invoice_date}>
                                                <TextInput 
                                                    type="date" 
                                                    value={data.invoice_date}
                                                    onChange={e => setData('invoice_date', e.target.value)}
                                                />
                                            </FormField>
                                        </div>
                                        <div className="col-6">
                                            <FormField label="Due Date" required error={errors.due_date}>
                                                <TextInput 
                                                    type="date" 
                                                    value={data.due_date}
                                                    onChange={e => setData('due_date', e.target.value)}
                                                />
                                            </FormField>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="card border-0 shadow-sm rounded-4 bg-pink-500 text-white shadow-premium">
                                <div className="card-body p-5">
                                    <h6 className="text-white-50 text-uppercase extra-small fw-extrabold tracking-widest mb-1">Total Payable</h6>
                                    <div className="display-4 fw-extrabold mb-0 tracking-tightest">
                                        <span className="small opacity-50 me-2" style={{ fontSize: '0.4em' }}>Ksh</span>
                                        {totalAmount.toLocaleString()}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <UnifiedToolbar 
                        actions={[
                            { 
                                label: 'GENERATE INVOICE', 
                                icon: 'fa-check-circle', 
                                onClick: submit,
                                color: 'success'
                            },
                            { 
                                label: 'DISCARD', 
                                icon: 'fa-times', 
                                href: route('invoices.index'),
                                color: 'gray'
                            }
                        ]}
                    />
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
