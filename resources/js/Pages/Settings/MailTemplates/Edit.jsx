import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import DashboardHero from '@/Components/DashboardHero';

export default function Edit({ template }) {
    const { data, setData, post, processing, errors } = useForm({
        subject: template.subject || '',
        html_template: template.html_template || '',
        text_template: template.text_template || '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('mail-templates.update', template.id));
    };

    return (
        <AuthenticatedLayout
            header={`Edit Template: ${template.mailable}`}
        >
            <Head title={`Edit ${template.mailable}`} />

            <DashboardHero 
                title="Edit Template"
                subtitle={`Customizing ${template.mailable}`}
                icon="fas fa-edit"
            />

            <div className="container-fluid mt-4 px-0">
                <div className="row">
                    <div className="col-lg-8">
                        <div className="card border-0 shadow-sm rounded-4">
                            <div className="card-body p-4">
                                <form onSubmit={submit}>
                                    <div className="mb-4">
                                        <label className="form-label fw-bold">Email Subject</label>
                                        <input 
                                            type="text" 
                                            className={`form-control rounded-3 ${errors.subject ? 'is-invalid' : ''}`}
                                            value={data.subject}
                                            onChange={e => setData('subject', e.target.value)}
                                            placeholder="Enter email subject"
                                        />
                                        {errors.subject && <div className="invalid-feedback">{errors.subject}</div>}
                                    </div>

                                    <div className="mb-4">
                                        <label className="form-label fw-bold">HTML Template</label>
                                        <textarea 
                                            className={`form-control rounded-3 font-monospace ${errors.html_template ? 'is-invalid' : ''}`}
                                            rows="15"
                                            value={data.html_template}
                                            onChange={e => setData('html_template', e.target.value)}
                                            placeholder="<p>Hello {{ name }},</p>..."
                                        ></textarea>
                                        {errors.html_template && <div className="invalid-feedback">{errors.html_template}</div>}
                                        <div className="form-text mt-2">
                                            <i className="fas fa-info-circle me-1"></i>
                                            Use double curly braces for variables, e.g., <code>{`{{ name }}`}</code>.
                                        </div>
                                    </div>

                                    <div className="mb-4">
                                        <label className="form-label fw-bold">Text Template (Optional)</label>
                                        <textarea 
                                            className="form-control rounded-3 font-monospace"
                                            rows="8"
                                            value={data.text_template}
                                            onChange={e => setData('text_template', e.target.value)}
                                            placeholder="Hello {{ name }},..."
                                        ></textarea>
                                    </div>

                                    <div className="d-flex justify-content-end gap-2">
                                        <button 
                                            type="button" 
                                            className="btn btn-light rounded-pill px-4"
                                            onClick={() => window.history.back()}
                                        >
                                            Cancel
                                        </button>
                                        <button 
                                            type="submit" 
                                            className="btn btn-primary rounded-pill px-4"
                                            disabled={processing}
                                        >
                                            {processing ? (
                                                <><span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Saving...</>
                                            ) : (
                                                <><i className="fas fa-save me-2"></i> Save Template</>
                                            )}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div className="col-lg-4">
                        <div className="card border-0 shadow-sm rounded-4 bg-light">
                            <div className="card-body p-4">
                                <h6 className="fw-bold mb-3"><i className="fas fa-magic me-2 text-primary"></i> Template Tips</h6>
                                <ul className="list-unstyled mb-0 small text-muted">
                                    <li className="mb-3">
                                        <strong>Placeholders:</strong> You can use placeholders like <code>{`{{ first_name }}`}</code>, <code>{`{{ last_name }}`}</code>, etc., depending on the data sent to the notification.
                                    </li>
                                    <li className="mb-3">
                                        <strong>Styling:</strong> Use inline CSS for maximum compatibility across email clients.
                                    </li>
                                    <li>
                                        <strong>Fallback:</strong> If no template is found, the system uses the default Laravel notification layout.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
