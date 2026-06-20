import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import DashboardSelect from '@/Components/DashboardSelect';
import DashboardPanel from '@/Components/DashboardPanel';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Create() {
    const handlePatientSelect = (val) => {
        if (val) {
            router.get(route('consultations.create'), {
                patient_id: val,
                priority: 'emergency',
                is_walk_in: 1,
            });
        }
    };

    return (
        <AuthenticatedLayout
            headerTitle="Emergency Triage"
            breadcrumbs={[
                { label: 'Dashboard', url: route('dashboard') },
                { label: 'Emergency Triage', active: true },
            ]}
        >
            <Head title="Emergency Triage" />

            <div className="row justify-content-center pb-5">
                <div className="col-lg-8 col-xl-6">
                    <DashboardPanel
                        title="Emergency Triage"
                        icon="fa-notes-medical"
                        className="mb-4"
                        headerVariant="danger"
                        bodyClassName="p-5"
                    >
                        <div className="text-center mb-5">
                            <div className="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style={{ width: 80, height: 80 }}>
                                <i className="fas fa-exclamation-triangle text-danger fa-2x"></i>
                            </div>
                            <h4 className="fw-extrabold text-gray-900 mb-2">Initiate Emergency Consultation</h4>
                            <p className="text-muted small mb-0">Select a patient below to start an immediate emergency assessment. This will bypass the standard queue.</p>
                        </div>

                        <div className="mb-4">
                            <label className="form-label extra-small fw-bold text-muted text-uppercase tracking-widest mb-2">Search patient registry</label>
                            <DashboardSelect
                                asyncUrl="/patients/search"
                                placeholder="Start typing patient name or ID..."
                                onChange={handlePatientSelect}
                            />
                        </div>

                        <div className="text-center mt-5 pt-3 border-top">
                            <button
                                type="button"
                                className="btn btn-light rounded-pill px-5 py-2 fw-bold border text-muted"
                                onClick={() => router.visit(route('dashboard'))}
                            >
                                <i className="fas fa-arrow-left me-2"></i>
                                Back to Dashboard
                            </button>
                        </div>
                    </DashboardPanel>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
