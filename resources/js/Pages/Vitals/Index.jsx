import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';

export default function Index() {
    return (
        <AuthenticatedLayout
            header="Vital Signs History"
        >
            <Head title="Vitals" />

            <PageHeader 
                title="Vitals Registry"
                breadcrumbs={[{ label: 'Clinical', active: true }, { label: 'Vitals', active: true }]}
                actions={
                    <Link href={route('vitals.create')} className="btn btn-primary rounded-pill px-4 font-bold shadow-sm">
                        <i className="fas fa-plus me-2"></i>Record New Vitals
                    </Link>
                }
            />

            <div className="py-0">
                <div className="card shadow-sm border-0 rounded-2xl bg-white p-5 text-center">
                    <div className="mb-4">
                        <i className="fas fa-heartbeat text-pink-500" style={{ fontSize: '4rem' }}></i>
                    </div>
                    <h4 className="fw-bold text-gray-800">No Vitals Recorded</h4>
                    <p className="text-muted mb-4">Start monitoring patient health by recording their latest vital signs.</p>
                    <Link href={route('vitals.create')} className="btn btn-primary rounded-pill px-5 py-2 font-bold shadow-lg">
                        <i className="fas fa-plus me-2"></i>Record New Vitals
                    </Link>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
