import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import ConsultationForm from './Form';

export default function Create({ auth, patients, doctors, appointment }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
        >
            <Head title="New Consultation" />

            <PageHeader 
                title="New Consultation"
                breadcrumbs={[
                    { label: 'Consultations', url: route('consultations.index') },
                    { label: 'New Consultation', active: true }
                ]}
            />

            <div className="py-0">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                     <ConsultationForm 
                        patients={patients}
                        doctors={doctors}
                        appointment={appointment}
                        submitRoute={route('consultations.store')}
                     />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
