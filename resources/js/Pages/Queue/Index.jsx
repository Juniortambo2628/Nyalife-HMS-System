import DashboardSelect from '@/Components/DashboardSelect';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';

const nextStatus = { waiting: 'triage', triage: 'with_doctor', with_doctor: 'completed' };

export default function QueueIndex({ queue }) {
    const { data, setData, post, processing, errors, reset } = useForm({ patient_id: '' });

    const submit = (e) => {
        e.preventDefault();
        post(route('queue.store'), {
            preserveScroll: true,
            onSuccess: () => reset('patient_id'),
        });
    };

    return (
        <AuthenticatedLayout headerTitle="Patient Queue" breadcrumbs={[{ label: 'Patient Queue', active: true }]}>
            <Head title="Patient Queue" />
            <div className="card border-0 shadow-sm rounded-4">
                <div className="card-body p-4">
                    <p className="text-muted">
                        Today's queue resets at 8:00 AM. New and revisit patients are numbered in arrival order.
                    </p>

                    <form onSubmit={submit} className="row g-2 align-items-end mb-4">
                        <div className="col-md-8 col-lg-6">
                            <label className="form-label fw-bold">Add walk-in or unscheduled patient</label>
                            <DashboardSelect
                                asyncUrl="/patients/search"
                                value={data.patient_id}
                                onChange={(val) => setData('patient_id', val || '')}
                                placeholder="Search Patients..."
                                className={errors.patient_id ? 'is-invalid' : ''}
                            />
                            {errors.patient_id && <div className="text-danger small mt-1">{errors.patient_id}</div>}
                        </div>
                        <div className="col-md-auto">
                            <button
                                type="submit"
                                disabled={processing || !data.patient_id}
                                className="btn btn-primary px-4"
                            >
                                {processing ? 'Adding...' : 'Add to Queue'}
                            </button>
                        </div>
                    </form>

                    <div className="table-responsive">
                        <table className="table align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Patient</th>
                                    <th>Visit</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {queue.map((entry) => (
                                    <tr key={entry.id}>
                                        <td className="fw-bold">{entry.queue_number}</td>
                                        <td>
                                            {entry.patient?.user?.first_name} {entry.patient?.user?.last_name}
                                        </td>
                                        <td className="text-capitalize">{entry.visit_type}</td>
                                        <td className="text-capitalize">{entry.status.replace('_', ' ')}</td>
                                        <td>
                                            {nextStatus[entry.status] && (
                                                <button
                                                    className="btn btn-sm btn-primary"
                                                    onClick={() =>
                                                        router.post(route('queue.status', entry.id), {
                                                            status: nextStatus[entry.status],
                                                        })
                                                    }
                                                >
                                                    Mark {nextStatus[entry.status].replace('_', ' ')}
                                                </button>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                                {!queue.length && (
                                    <tr>
                                        <td colSpan="5" className="text-center text-muted py-4">
                                            No patients are waiting.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
