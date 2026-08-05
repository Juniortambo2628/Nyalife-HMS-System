import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import StatusBadge from '@/Components/StatusBadge';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { formatDateOnly } from '@/Utils/dateUtils';

export default function Show({ request }) {
    const results = typeof request.results === 'string' ? JSON.parse(request.results || '{}') : request.results || {};

    const handlePrint = () => window.open(route('lab.print', request.request_id), '_blank');

    const renderQuantitative = () => {
        const rows = results.quantitative || [];
        if (!rows.length) return null;

        return (
            <div className="table-responsive">
                <table className="table table-hover align-middle">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="extra-small fw-bold text-muted text-uppercase">Parameter</th>
                            <th className="extra-small fw-bold text-muted text-uppercase">Result</th>
                            <th className="extra-small fw-bold text-muted text-uppercase">Unit</th>
                            <th className="extra-small fw-bold text-muted text-uppercase">Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        {rows.map((row, idx) => (
                            <tr key={idx}>
                                <td className="fw-bold">{row.label || row.name}</td>
                                <td className="fw-extrabold text-primary">{row.value}</td>
                                <td className="text-muted small">{row.unit || '—'}</td>
                                <td className="text-muted small font-mono">
                                    {row.normalRange || row.reference || '—'}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        );
    };

    const renderNarrative = () => {
        if (results.narrative || results.comment || results.interpretation) {
            return (
                <div className="p-4 bg-gray-50 rounded-4 border">
                    <h6 className="extra-small fw-extrabold text-muted text-uppercase mb-2">Interpretation</h6>
                    <p className="mb-0">{results.narrative || results.comment || results.interpretation}</p>
                </div>
            );
        }
        if (typeof results === 'object' && !results.quantitative?.length) {
            return (
                <pre className="p-3 bg-light rounded small mb-0 overflow-auto">{JSON.stringify(results, null, 2)}</pre>
            );
        }
        return null;
    };

    return (
        <AuthenticatedLayout
            headerTitle={request.test_type?.test_name || 'Laboratory Result'}
            breadcrumbs={[
                { label: 'Lab Results', url: route('lab.results') },
                { label: request.request_number || `LAB-${request.request_id}`, active: true },
            ]}
        >
            <Head title={`Lab Result - ${request.request_number || request.request_id}`} />

            <div className="card border-0 shadow-sm rounded-4 mb-4">
                <div className="card-body p-5">
                    <div className="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
                        <div>
                            <div className="extra-small fw-bold text-muted text-uppercase mb-1">Patient</div>
                            <h4 className="fw-extrabold mb-0">
                                {request.patient?.user?.first_name} {request.patient?.user?.last_name}
                            </h4>
                            <div className="text-muted small mt-1">
                                Requested {request.request_date}
                                {request.completed_at && ` · Completed ${formatDateOnly(request.completed_at)}`}
                            </div>
                        </div>
                        <StatusBadge status={request.status} />
                    </div>

                    {renderQuantitative() || renderNarrative() || (
                        <p className="text-muted mb-0">No structured result data recorded for this request.</p>
                    )}

                    {request.notes && (
                        <div className="mt-4 pt-4 border-top">
                            <h6 className="extra-small fw-extrabold text-muted text-uppercase">Notes</h6>
                            <p className="mb-0 text-muted">{request.notes}</p>
                        </div>
                    )}
                </div>
            </div>

            <UnifiedToolbar
                actions={[
                    { label: 'PRINT REPORT', icon: 'fa-print', onClick: handlePrint },
                    { label: 'ALL RESULTS', icon: 'fa-list', href: route('lab.results'), color: 'gray' },
                    { label: 'FULL REQUEST', icon: 'fa-flask', href: route('lab.show', request.request_id) },
                ]}
            />
        </AuthenticatedLayout>
    );
}
