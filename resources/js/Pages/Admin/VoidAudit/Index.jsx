import ReportsNav from '@/Components/ReportsNav';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import DashboardSearch from '@/Components/DashboardSearch';
import PatientTableCell from '@/Components/PatientTableCell';
import { RefBadge, TableCellPrimary, TableCellStack } from '@/Components/TableCells';
import { formatDateTime } from '@/Utils/dateUtils';

const TABS = [
    { id: 'prescriptions', label: 'Prescriptions', icon: 'fa-prescription' },
    { id: 'vitals', label: 'Vitals', icon: 'fa-heartbeat' },
    { id: 'invoices', label: 'Invoices', icon: 'fa-file-invoice' },
];

export default function Index({ type, prescriptions, vitals, invoices, filters, counts }) {
    const [search, setSearch] = useState(filters.search || '');

    const applyFilters = (nextType = type, nextSearch = search) => {
        router.get(route('admin.void-audit.index'), { type: nextType, search: nextSearch || undefined }, {
            preserveState: true,
            replace: true,
        });
    };

    const prescriptionColumns = useMemo(() => [
        {
            header: 'Prescription',
            accessorKey: 'prescription_id',
            cell: ({ row }) => (
                <RefBadge variant="info">
                    {row.original.prescription_number || `RX-${row.original.prescription_id}`}
                </RefBadge>
            ),
        },
        {
            header: 'Patient',
            id: 'patient',
            cell: ({ row }) => (
                <PatientTableCell patient={row.original.patient} patientId={row.original.patient_id} />
            ),
        },
        {
            header: 'Void reason',
            accessorKey: 'void_reason',
            cell: ({ row }) => (
                <TableCellPrimary className="text-truncate nyl-text-constrained">
                    {row.original.void_reason || '—'}
                </TableCellPrimary>
            ),
        },
        {
            header: 'Voided by',
            id: 'voided_by',
            cell: ({ row }) => {
                const user = row.original.voided_by_user;
                return (
                    <TableCellPrimary>
                        {user ? `${user.first_name} ${user.last_name}` : '—'}
                    </TableCellPrimary>
                );
            },
        },
        {
            header: 'Voided at',
            accessorKey: 'voided_at',
            cell: ({ row }) => (
                <TableCellPrimary>{formatDateTime(row.original.voided_at)}</TableCellPrimary>
            ),
        },
    ], []);

    const vitalColumns = useMemo(() => [
        {
            header: 'Record',
            accessorKey: 'vital_id',
            cell: ({ row }) => <RefBadge variant="info">VIT-{row.original.vital_id}</RefBadge>,
        },
        {
            header: 'Patient',
            id: 'patient',
            cell: ({ row }) => (
                <PatientTableCell patient={row.original.patient} patientId={row.original.patient_id} />
            ),
        },
        {
            header: 'Reading',
            id: 'reading',
            cell: ({ row }) => (
                <TableCellStack
                    primary={[row.original.blood_pressure, row.original.heart_rate ? `${row.original.heart_rate} bpm` : null].filter(Boolean).join(' · ') || 'Vitals recorded'}
                    secondary={row.original.temperature ? `${row.original.temperature}°C` : null}
                />
            ),
        },
        {
            header: 'Void reason',
            accessorKey: 'void_reason',
            cell: ({ row }) => <TableCellPrimary>{row.original.void_reason || '—'}</TableCellPrimary>,
        },
        {
            header: 'Voided at',
            accessorKey: 'voided_at',
            cell: ({ row }) => <TableCellPrimary>{formatDateTime(row.original.voided_at)}</TableCellPrimary>,
        },
    ], []);

    const invoiceColumns = useMemo(() => [
        {
            header: 'Invoice',
            accessorKey: 'invoice_number',
            cell: ({ row }) => <RefBadge variant="info">{row.original.invoice_number}</RefBadge>,
        },
        {
            header: 'Patient',
            id: 'patient',
            cell: ({ row }) => (
                <PatientTableCell patient={row.original.patient} patientId={row.original.patient_id} />
            ),
        },
        {
            header: 'Amount',
            accessorKey: 'total_amount',
            cell: ({ row }) => <TableCellPrimary>{row.original.total_amount}</TableCellPrimary>,
        },
        {
            header: 'Void reason',
            accessorKey: 'void_reason',
            cell: ({ row }) => <TableCellPrimary>{row.original.void_reason || '—'}</TableCellPrimary>,
        },
        {
            header: 'Voided at',
            accessorKey: 'voided_at',
            cell: ({ row }) => <TableCellPrimary>{formatDateTime(row.original.voided_at)}</TableCellPrimary>,
        },
    ], []);

    const activeData = type === 'prescriptions' ? prescriptions : type === 'vitals' ? vitals : invoices;
    const activeColumns = type === 'prescriptions' ? prescriptionColumns : type === 'vitals' ? vitalColumns : invoiceColumns;
    const idField = type === 'prescriptions' ? 'prescription_id' : type === 'vitals' ? 'vital_id' : 'invoice_id';

    return (
        <AuthenticatedLayout
            headerTitle="Void audit trail"
            breadcrumbs={[
                { label: 'Reports', url: route('reports.index') },
                { label: 'Void audit', active: true },
            ]}
        >
            <Head title="Void Audit Trail" />

            <ReportsNav active="void-audit" />

            <div className="mb-4">
                <ul className="nav nav-pills gap-2 flex-wrap">
                    {TABS.map((tab) => (
                        <li key={tab.id} className="nav-item">
                            <button
                                type="button"
                                className={`nav-link rounded-pill px-4 fw-bold extra-small text-uppercase ${type === tab.id ? 'active' : 'text-muted bg-light'}`}
                                onClick={() => applyFilters(tab.id, search)}
                            >
                                <i className={`fas ${tab.icon} me-2`} />
                                {tab.label}
                                <span className="badge bg-white text-dark ms-2">{counts[tab.id] ?? 0}</span>
                            </button>
                        </li>
                    ))}
                </ul>
            </div>

            <div className="mb-4">
                <DashboardSearch
                    value={search}
                    onChange={setSearch}
                    onSubmit={() => applyFilters(type, search)}
                    placeholder={`Search voided ${type}…`}
                />
            </div>

            <RegistryTablePanel
                title={`Voided ${type}`}
                icon={TABS.find((t) => t.id === type)?.icon || 'fa-ban'}
                columns={activeColumns}
                data={activeData?.data || []}
                pagination={activeData?.meta ? { ...activeData.meta, links: activeData.links } : null}
                emptyMessage={`No voided ${type} on record.`}
                idField={idField}
            />
        </AuthenticatedLayout>
    );
}
