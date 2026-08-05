import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { toast } from 'react-hot-toast';
import { formatDateOnly, formatDateTime } from '@/Utils/dateUtils';

export default function ApiTokens({ tokens }) {
    const { flash } = usePage().props;
    const [revealedToken, setRevealedToken] = useState(flash?.new_api_token || null);

    const { data, setData, post, processing, reset, errors } = useForm({
        name: '',
    });

    useEffect(() => {
        if (flash?.new_api_token) {
            setRevealedToken(flash.new_api_token);
        }
    }, [flash?.new_api_token]);

    const createToken = (e) => {
        e.preventDefault();
        post(route('admin.api-tokens.store'), {
            onSuccess: () => reset(),
        });
    };

    const revokeToken = (id) => {
        if (!confirm('Revoke this API token? Applications using it will lose access.')) return;
        router.delete(route('admin.api-tokens.destroy', id));
    };

    const copyToken = () => {
        if (revealedToken) {
            navigator.clipboard.writeText(revealedToken);
            toast.success('Token copied to clipboard');
        }
    };

    return (
        <AuthenticatedLayout
            headerTitle="API Access Tokens"
            breadcrumbs={[
                { label: 'Admin', url: '/dashboard' },
                { label: 'Settings', url: route('admin.settings.index') },
                { label: 'API Tokens', active: true },
            ]}
        >
            <Head title="API Tokens" />

            <div className="py-0 row g-4">
                <div className="col-lg-5">
                    <div className="card border-0 shadow-sm rounded-3xl">
                        <div className="card-body p-4">
                            <h5 className="fw-bold mb-3">Create Token</h5>
                            <p className="text-muted small">
                                Tokens inherit your current permissions. Use as{' '}
                                <code>Authorization: Bearer {'{token}'}</code> on <code>/api/v1/*</code> requests.
                            </p>
                            <form onSubmit={createToken} className="d-flex flex-column gap-3">
                                <input
                                    type="text"
                                    className="form-control rounded-pill"
                                    placeholder="Token name (e.g. Mobile App)"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    required
                                />
                                {errors.name && <div className="text-danger small">{errors.name}</div>}
                                <button type="submit" disabled={processing} className="btn btn-primary rounded-pill">
                                    {processing ? 'Creating...' : 'Generate Token'}
                                </button>
                            </form>

                            {revealedToken && (
                                <div className="alert alert-warning mt-4 mb-0 rounded-3xl">
                                    <div className="fw-bold mb-2">Copy your new token now</div>
                                    <code className="d-block text-break small mb-2">{revealedToken}</code>
                                    <button
                                        type="button"
                                        onClick={copyToken}
                                        className="btn btn-sm btn-dark rounded-pill"
                                    >
                                        <i className="fas fa-copy me-1"></i> Copy
                                    </button>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                <div className="col-lg-7">
                    <div className="card border-0 shadow-sm rounded-3xl">
                        <div className="card-body p-4">
                            <h5 className="fw-bold mb-3">Active Tokens</h5>
                            {tokens.length === 0 ? (
                                <p className="text-muted mb-0">No API tokens yet.</p>
                            ) : (
                                <div className="table-responsive">
                                    <table className="table table-sm align-middle">
                                        <thead>
                                            <tr className="text-muted extra-small">
                                                <th>Name</th>
                                                <th>Created</th>
                                                <th>Last Used</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {tokens.map((token) => (
                                                <tr key={token.id}>
                                                    <td className="fw-medium">{token.name}</td>
                                                    <td className="small text-muted">
                                                        {token.created_at ? formatDateOnly(token.created_at) : '—'}
                                                    </td>
                                                    <td className="small text-muted">
                                                        {token.last_used_at
                                                            ? formatDateTime(token.last_used_at)
                                                            : 'Never'}
                                                    </td>
                                                    <td className="text-end">
                                                        <button
                                                            type="button"
                                                            onClick={() => revokeToken(token.id)}
                                                            className="btn btn-sm btn-outline-danger rounded-pill"
                                                        >
                                                            Revoke
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
