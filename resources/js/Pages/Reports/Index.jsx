import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import ReportsNav, { ReportDateFilter, REPORT_EXPORT_TYPES } from '@/Components/ReportsNav';
import StatCardGrid from '@/Components/StatCardGrid';
import { formatCurrency } from '@/Utils/formatUtils';
import { useMemo, useState } from 'react';

function MiniBar({ data, maxVal, color }) {
    return (
        <div className="d-flex align-items-end gap-1" style={{ height: 60 }}>
            {data.map((d, i) => (
                <div key={i} className="d-flex flex-column align-items-center flex-fill">
                    <div
                        className="rounded-top-2"
                        style={{
                            width: '100%',
                            height: maxVal > 0 ? Math.max(4, (d.value / maxVal) * 50) : 4,
                            backgroundColor: color,
                            opacity: 0.15 + (0.85 * (i / Math.max(data.length - 1, 1))),
                            transition: 'height 0.4s ease',
                        }}
                    />
                    <div className="extra-small text-muted fw-bold text-center mt-1" style={{ fontSize: '0.6rem' }}>{d.label}</div>
                </div>
            ))}
        </div>
    );
}

export default function Index({ stats, appointmentsByStatus = {}, revenueTrend = [], patientTrend = [], topDiagnoses = [], recentInvoices = [], filters }) {
    const [from, setFrom] = useState(filters?.from || '');
    const [to, setTo] = useState(filters?.to || '');
    const [exporting, setExporting] = useState(null);

    const applyFilters = () => {
        router.get(route('reports.index'), { from, to }, { preserveState: true, preserveScroll: true });
    };

    const exportCsv = (type) => {
        setExporting(type);
        window.open(route('reports.export') + `?type=${type}&from=${from}&to=${to}`, '_blank');
        setTimeout(() => setExporting(null), 2000);
    };

    const revenueMax = Math.max(...revenueTrend.map(r => r.revenue), 1);
    const patientMax = Math.max(...patientTrend.map(r => r.count), 1);

    const statusColors = {
        scheduled: '#6366f1', confirmed: '#10b981', completed: '#3b82f6',
        cancelled: '#ef4444', no_show: '#f59e0b', pending: '#8b5cf6',
    };

    const totalStatusCount = Object.values(appointmentsByStatus).reduce((a, b) => a + b, 0) || 1;

    const primaryStats = useMemo(() => [
        { label: 'Total patients', value: stats.total_patients, icon: 'fa-users', color: 'primary', sub: `+${stats.new_patients} this period` },
        { label: 'Appointments', value: stats.period_appointments, icon: 'fa-calendar-check', color: 'success', sub: `${stats.total_appointments} all time` },
        { label: 'Consultations', value: stats.period_consultations, icon: 'fa-stethoscope', color: 'info', sub: `${stats.total_consultations} all time` },
        { label: 'Period revenue', value: formatCurrency(stats.period_revenue), icon: 'fa-wallet', color: 'warning', sub: `${formatCurrency(stats.total_revenue)} all time` },
    ], [stats]);

    const secondaryStats = useMemo(() => [
        { label: 'Pending invoices', value: stats.pending_invoices, icon: 'fa-file-invoice', color: 'danger', sub: formatCurrency(stats.pending_amount) },
        { label: 'Prescriptions', value: stats.period_prescriptions, icon: 'fa-prescription-bottle-alt', color: 'purple', sub: `${stats.total_prescriptions} all time` },
        { label: 'Lab requests', value: stats.period_lab_requests, icon: 'fa-flask', color: 'teal', sub: `${stats.total_lab_requests} all time` },
        { label: 'Total staff', value: stats.total_staff, icon: 'fa-user-md', color: 'pink' },
    ], [stats]);

    return (
        <AuthenticatedLayout
            headerTitle="Management Reports"
            breadcrumbs={[
                { label: 'Dashboard', url: route('dashboard') },
                { label: 'Reports', active: true },
            ]}
        >
            <Head title="Reports & Analytics" />

            <ReportsNav active="overview" />

            <div className="py-0">
            <ReportDateFilter
                from={from}
                to={to}
                onFromChange={setFrom}
                onToChange={setTo}
                onApply={applyFilters}
                exportTypes={REPORT_EXPORT_TYPES}
                exporting={exporting}
                onExport={exportCsv}
            />

                <StatCardGrid items={primaryStats} gap={3} />
                <StatCardGrid items={secondaryStats} gap={3} />

                {/* Charts Row */}
                <div className="row g-4 mb-4">
                    {/* Revenue Trend */}
                    <div className="col-lg-6">
                        <div className="card shadow-sm border-0 rounded-3xl bg-white p-4 h-100">
                            <h6 className="fw-extrabold text-gray-900 mb-1">Revenue Trend</h6>
                            <div className="extra-small text-muted fw-bold mb-3">Last 6 months</div>
                            <MiniBar
                                data={revenueTrend.map(r => ({ label: r.month.split(' ')[0], value: r.revenue }))}
                                maxVal={revenueMax}
                                color="#6366f1"
                            />
                            <div className="d-flex justify-content-between mt-3 px-1">
                                {revenueTrend.map((r, i) => (
                                    <div key={i} className="text-center">
                                        <div className="fw-extrabold text-gray-900 extra-small">{formatCurrency(r.revenue)}</div>
                                        <div className="extra-small text-muted">{r.count} inv</div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* Patient Registrations */}
                    <div className="col-lg-6">
                        <div className="card shadow-sm border-0 rounded-3xl bg-white p-4 h-100">
                            <h6 className="fw-extrabold text-gray-900 mb-1">Patient Registrations</h6>
                            <div className="extra-small text-muted fw-bold mb-3">Last 6 months</div>
                            <MiniBar
                                data={patientTrend.map(r => ({ label: r.month.split(' ')[0], value: r.count }))}
                                maxVal={patientMax}
                                color="#10b981"
                            />
                            <div className="d-flex justify-content-between mt-3 px-1">
                                {patientTrend.map((r, i) => (
                                    <div key={i} className="text-center">
                                        <div className="fw-extrabold text-gray-900 extra-small">{r.count}</div>
                                        <div className="extra-small text-muted">patients</div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Appointments Breakdown & Top Diagnoses */}
                <div className="row g-4 mb-4">
                    {/* Appointment Status Breakdown */}
                    <div className="col-lg-5">
                        <div className="card shadow-sm border-0 rounded-3xl bg-white p-4 h-100">
                            <h6 className="fw-extrabold text-gray-900 mb-1">Appointment Status</h6>
                            <div className="extra-small text-muted fw-bold mb-3">Current period breakdown</div>
                            {Object.keys(appointmentsByStatus).length === 0 ? (
                                <div className="text-center text-muted py-4">No appointment data for this period</div>
                            ) : (
                                <div className="d-flex flex-column gap-2">
                                    {Object.entries(appointmentsByStatus).map(([status, count]) => (
                                        <div key={status}>
                                            <div className="d-flex justify-content-between mb-1">
                                                <span className="small fw-bold text-gray-700 text-capitalize">{status.replace('_', ' ')}</span>
                                                <span className="small fw-extrabold text-gray-900">{count}</span>
                                            </div>
                                            <div className="progress" style={{ height: 6, borderRadius: 99 }}>
                                                <div
                                                    className="progress-bar"
                                                    style={{
                                                        width: `${(count / totalStatusCount) * 100}%`,
                                                        backgroundColor: statusColors[status] || '#6b7280',
                                                        borderRadius: 99,
                                                        transition: 'width 0.6s ease',
                                                    }}
                                                />
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Top Diagnoses */}
                    <div className="col-lg-7">
                        <div className="card shadow-sm border-0 rounded-3xl bg-white p-4 h-100">
                            <h6 className="fw-extrabold text-gray-900 mb-1">Top Diagnoses</h6>
                            <div className="extra-small text-muted fw-bold mb-3">Most common clinical findings</div>
                            {topDiagnoses.length === 0 ? (
                                <div className="text-center text-muted py-4">No diagnosis data available</div>
                            ) : (
                                <div className="d-flex flex-column gap-2">
                                    {topDiagnoses.map((d, i) => (
                                        <div key={i} className="d-flex align-items-center gap-3">
                                            <div className="avatar-sm bg-gray-50 rounded-xl d-flex align-items-center justify-content-center flex-shrink-0 border fw-extrabold text-gray-400 extra-small" style={{ width: 32, height: 32 }}>
                                                {i + 1}
                                            </div>
                                            <div className="flex-grow-1 overflow-hidden">
                                                <div className="small fw-bold text-gray-800 text-truncate">{d.diagnosis}</div>
                                            </div>
                                            <span className="badge bg-light text-gray-700 rounded-pill px-3 fw-extrabold">{d.count}</span>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Recent Invoices */}
                <div className="card shadow-sm border-0 rounded-3xl bg-white p-4 mb-4">
                    <div className="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 className="fw-extrabold text-gray-900 mb-0">Recent Invoices</h6>
                            <div className="extra-small text-muted fw-bold">Latest 10 transactions</div>
                        </div>
                        <Link href={route('invoices.index')} className="btn btn-sm btn-light rounded-pill px-3 fw-bold border">
                            View All <i className="fas fa-arrow-right ms-1"></i>
                        </Link>
                    </div>
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead>
                                <tr className="border-bottom">
                                    <th className="extra-small fw-bold text-muted text-uppercase tracking-widest py-3">Invoice</th>
                                    <th className="extra-small fw-bold text-muted text-uppercase tracking-widest py-3">Patient</th>
                                    <th className="extra-small fw-bold text-muted text-uppercase tracking-widest py-3">Amount</th>
                                    <th className="extra-small fw-bold text-muted text-uppercase tracking-widest py-3">Status</th>
                                    <th className="extra-small fw-bold text-muted text-uppercase tracking-widest py-3">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                {recentInvoices.map(inv => (
                                    <tr key={inv.invoice_id}>
                                        <td className="fw-bold small">
                                            <Link href={route('invoices.show', inv.invoice_id)} className="text-primary text-decoration-none">
                                                {inv.invoice_number}
                                            </Link>
                                        </td>
                                        <td className="small text-gray-700">{inv.patient?.user?.first_name} {inv.patient?.user?.last_name}</td>
                                        <td className="fw-extrabold small">{formatCurrency(inv.total_amount)}</td>
                                        <td>
                                            <span className={`badge rounded-pill px-2 py-1 extra-small fw-bold ${
                                                inv.status === 'paid' ? 'bg-soft-success text-success' :
                                                inv.status === 'pending' ? 'bg-soft-warning text-warning' :
                                                'bg-soft-danger text-danger'
                                            }`}>
                                                {inv.status}
                                            </span>
                                        </td>
                                        <td className="small text-muted">{inv.invoice_date}</td>
                                    </tr>
                                ))}
                                {recentInvoices.length === 0 && (
                                    <tr><td colSpan={5} className="text-center text-muted py-4">No invoices found</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
