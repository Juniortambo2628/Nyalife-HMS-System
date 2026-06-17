import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { formatDateTime } from '@/Utils/dateUtils';
export default function Show({ message, auth }) {
    const deleteMessage = () => {
        if (confirm('Are you sure you want to delete this message?')) {
            router.delete(route('admin.messages.destroy', message.contact_message_id));
        }
    };

    const { data, setData, post, processing, errors, reset } = useForm({
        reply_message: '',
    });

    const submitReply = (e) => {
        e.preventDefault();
        post(route('admin.messages.reply', message.contact_message_id), {
            onSuccess: () => reset('reply_message'),
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            headerTitle="Message Details"
            breadcrumbs={[
                { label: 'Dashboard', url: route('dashboard') },
                { label: 'Messages', url: route('admin.messages.index') },
                { label: 'View', active: true },
            ]}
        >
            <Head title="View Message" />

            <div className="row justify-content-center">
                <div className="col-lg-8">
                    <div className="card shadow-sm border-0 rounded-xl overflow-hidden bg-white">
                        <div className="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                            <div className="d-flex align-items-center gap-3">
                                <div className="avatar-md bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center">
                                    <i className="fas fa-user fa-lg"></i>
                                </div>
                                <div>
                                    <h5 className="fw-bold mb-0 text-gray-900">{message.name}</h5>
                                    <div className="text-muted small">{message.email}</div>
                                </div>
                            </div>
                            <div className="text-end">
                                <span className={`badge rounded-pill px-3 py-2 mb-1 ${
                                    message.status === 'pending' 
                                        ? 'bg-warning text-dark' 
                                        : message.status === 'replied'
                                        ? 'bg-success text-white'
                                        : 'bg-light text-muted'
                                }`}>
                                    {message.status === 'pending' 
                                        ? 'New Message' 
                                        : message.status === 'replied'
                                        ? 'Replied'
                                        : 'Read'}
                                </span>
                                <div className="extra-small text-muted">
                                    {formatDateTime(message.created_at)}
                                </div>
                            </div>
                        </div>
                        <div className="card-body p-4">
                            <div className="bg-light rounded-3 p-4 mb-4" style={{ minHeight: '200px' }}>
                                <div className="text-gray-700 whitespace-pre-wrap lead font-sans" style={{ whiteSpace: 'pre-wrap' }}>
                                    {message.message}
                                </div>
                            </div>

                            {/* Existing Reply Section */}
                            {message.reply ? (
                                <div className="border border-success-subtle rounded-3 p-4 mb-4 bg-success-subtle bg-opacity-10">
                                    <div className="d-flex justify-content-between align-items-center mb-3">
                                        <div className="d-flex align-items-center gap-2">
                                            <div className="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style={{ width: '28px', height: '28px', fontSize: '0.8rem' }}>
                                                <i className="fas fa-check"></i>
                                            </div>
                                            <div>
                                                <span className="fw-bold text-success-emphasis me-2">Reply Sent</span>
                                                <span className="text-muted small">
                                                    by {message.replier ? `${message.replier.first_name} ${message.replier.last_name}` : 'Administrator'}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="text-muted small">
                                            {formatDateTime(message.replied_at)}
                                        </div>
                                    </div>
                                    <div className="text-gray-800 whitespace-pre-wrap font-sans" style={{ whiteSpace: 'pre-wrap' }}>
                                        {message.reply}
                                    </div>
                                </div>
                            ) : (
                                <form onSubmit={submitReply} className="mb-4 p-4 border border-light-subtle rounded-3 bg-light bg-opacity-25">
                                    <h6 className="fw-bold mb-3 text-gray-900 d-flex align-items-center gap-2">
                                        <i className="fas fa-reply text-primary"></i> Write a Reply
                                    </h6>
                                    <div className="mb-3">
                                        <textarea
                                            value={data.reply_message}
                                            onChange={e => setData('reply_message', e.target.value)}
                                            rows="5"
                                            className={`form-control rounded-3 border-gray-300 ${errors.reply_message ? 'is-invalid' : ''}`}
                                            placeholder="Type your response here... (An email will be sent automatically to the inquirer if configured)"
                                            required
                                        ></textarea>
                                        {errors.reply_message && (
                                            <div className="invalid-feedback">{errors.reply_message}</div>
                                        )}
                                    </div>
                                    <div className="d-flex justify-content-end">
                                        <button
                                            type="submit"
                                            className="btn btn-primary rounded-pill px-4"
                                            disabled={processing}
                                        >
                                            {processing ? (
                                                <>
                                                    <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                    Sending...
                                                </>
                                            ) : (
                                                <>
                                                    <i className="fas fa-paper-plane me-2"></i> Send Reply
                                                </>
                                            )}
                                        </button>
                                    </div>
                                </form>
                            )}

                            <div className="d-flex justify-content-between align-items-center pt-2">
                                <Link 
                                    href={route('admin.messages.index')}
                                    className="btn btn-light rounded-pill px-4"
                                >
                                    <i className="fas fa-arrow-left me-2"></i> Back to List
                                </Link>
                                <div className="d-flex gap-2">
                                    {!message.reply && (
                                        <a 
                                            href={`mailto:${message.email}?subject=Re: Inquiry from Nyalife Website`}
                                            className="btn btn-outline-primary rounded-pill px-4"
                                        >
                                            <i className="fas fa-reply me-2"></i> Reply by Email Client
                                        </a>
                                    )}
                                    <button 
                                        onClick={deleteMessage}
                                        className="btn btn-outline-danger rounded-pill px-4"
                                    >
                                        <i className="fas fa-trash me-2"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
