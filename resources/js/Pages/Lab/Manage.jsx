import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Manage() {
    return (
        <AuthenticatedLayout
            header="Lab Management"
        >
            <Head title="Lab Management" />

            <div className="py-4">
                <div className="card shadow-sm border-0 rounded-xl p-5 text-center bg-white">
                    <div className="avatar-xl bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-4">
                        <i className="fas fa-vial text-pink-500 fs-1"></i>
                    </div>
                    <h3 className="fw-bold text-gray-900">Laboratory Configuration</h3>
                    <p className="text-muted max-w-lg mx-auto mb-4">
                        Manage lab technicians, equipment maintenance logs, and test reference ranges from this centralized dashboard.
                    </p>
                    <div className="d-flex justify-content-center gap-3">
                        <button className="btn btn-primary rounded-pill px-4 py-2 fw-bold">Add Equipment</button>
                        <button className="btn btn-outline-secondary rounded-pill px-4 py-2 fw-bold">View Logs</button>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
