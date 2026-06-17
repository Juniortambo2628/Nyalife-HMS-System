import { Link } from '@inertiajs/react';

const reportLinks = [
    { key: 'overview', label: 'Overview', route: 'reports.index', icon: 'fa-chart-pie' },
    { key: 'financial', label: 'Financial', route: 'reports.financial', icon: 'fa-file-invoice-dollar' },
    { key: 'appointments', label: 'Appointments', route: 'reports.appointments', icon: 'fa-calendar-check' },
    { key: 'patients', label: 'Patients', route: 'reports.patients', icon: 'fa-users' },
    { key: 'laboratory', label: 'Laboratory', route: 'reports.laboratory', icon: 'fa-flask' },
    { key: 'pharmacy', label: 'Pharmacy', route: 'reports.pharmacy', icon: 'fa-prescription-bottle-alt' },
    { key: 'void-audit', label: 'Void audit', route: 'admin.void-audit.index', icon: 'fa-ban' },
];

export const REPORT_EXPORT_TYPES = [
    { type: 'financial', label: 'Financial report', icon: 'fa-file-invoice-dollar' },
    { type: 'patients', label: 'Patients report', icon: 'fa-users' },
    { type: 'appointments', label: 'Appointments report', icon: 'fa-calendar' },
    { type: 'consultations', label: 'Consultations report', icon: 'fa-stethoscope' },
    { type: 'laboratory', label: 'Laboratory report', icon: 'fa-flask' },
    { type: 'pharmacy', label: 'Pharmacy report', icon: 'fa-prescription-bottle-alt' },
];

export default function ReportsNav({ active }) {
    return (
        <div className="d-flex flex-wrap gap-2 mb-4">
            {reportLinks.map((item) => (
                <Link
                    key={item.key}
                    href={route(item.route)}
                    className={`btn btn-sm rounded-pill px-3 py-2 fw-bold extra-small ${
                        active === item.key
                            ? 'btn-primary shadow-sm'
                            : 'btn-light border text-muted'
                    }`}
                >
                    <i className={`fas ${item.icon} me-1 opacity-75`}></i>
                    {item.label}
                </Link>
            ))}
        </div>
    );
}

export function ReportDateFilter({
    from,
    to,
    onFromChange,
    onToChange,
    onApply,
    exportType,
    exportTypes,
    exporting,
    onExport,
}) {
    const exportCsv = (type) => {
        const target = type || exportType;
        if (!target) return;

        if (onExport) {
            onExport(target);
            return;
        }

        window.open(route('reports.export') + `?type=${target}&from=${from}&to=${to}`, '_blank');
    };

    const multiExport = exportTypes && exportTypes.length > 0;

    return (
        <div className="card shadow-sm border-0 rounded-3xl bg-white p-4 mb-4">
            <div className="d-flex flex-wrap align-items-end gap-3">
                <div>
                    <label className="extra-small fw-bold text-muted d-block mb-1">From</label>
                    <input type="date" className="form-control form-control-sm rounded-pill px-3" value={from} onChange={(e) => onFromChange(e.target.value)} />
                </div>
                <div>
                    <label className="extra-small fw-bold text-muted d-block mb-1">To</label>
                    <input type="date" className="form-control form-control-sm rounded-pill px-3" value={to} onChange={(e) => onToChange(e.target.value)} />
                </div>
                <button onClick={onApply} className="btn btn-primary rounded-pill px-4 py-2 fw-bold small shadow-sm">
                    <i className="fas fa-filter me-1"></i> Apply
                </button>
                {(exportType || multiExport) && (
                    multiExport ? (
                        <div className="ms-auto d-flex gap-2">
                            <div className="dropdown">
                                <button className="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold small dropdown-toggle" data-bs-toggle="dropdown">
                                    <i className="fas fa-download me-1"></i> Export CSV
                                </button>
                                <ul className="dropdown-menu shadow-lg border-0 rounded-3">
                                    {exportTypes.map(({ type, label, icon }) => (
                                        <li key={type}>
                                            <button className="dropdown-item py-2" onClick={() => exportCsv(type)}>
                                                <i className={`fas ${icon} me-2 opacity-50`}></i>
                                                {label}
                                                {exporting === type && <i className="fas fa-spinner fa-spin ms-2"></i>}
                                            </button>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </div>
                    ) : (
                        <button onClick={() => exportCsv()} className="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold small ms-auto">
                            <i className="fas fa-download me-1"></i> Export CSV
                        </button>
                    )
                )}
            </div>
        </div>
    );
}
