import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Index() {
    return (
        <AuthenticatedLayout
            header="Lab Results"
        >
            <Head title="Lab Results" />

            <div className="py-4">
                <div className="card shadow-sm border-0 rounded-xl bg-white overflow-hidden">
                    <div className="card-body p-5 text-center">
                        <div className="mb-4">
                            <i className="fas fa-file-medical-alt text-gray-200" style={{ fontSize: '5rem' }}></i>
                        </div>
                        <h4 className="fw-bold text-gray-800">Patient Laboratory Results</h4>
                        <p className="text-muted mb-0">Select a patient from the list or enter a result number to view and print reports.</p>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
