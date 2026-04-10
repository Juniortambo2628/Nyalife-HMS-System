import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import ConsultationForm from './Form';

export default function Edit({ auth, consultation, patients, doctors }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Consultation #{consultation.consultation_id}</h2>}
        >
            <Head title="Edit Consultation" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                     <ConsultationForm 
                        consultation={consultation}
                        patients={patients}
                        doctors={doctors}
                        submitRoute={route('consultations.update', consultation.consultation_id)}
                        isEdit={true}
                     />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
