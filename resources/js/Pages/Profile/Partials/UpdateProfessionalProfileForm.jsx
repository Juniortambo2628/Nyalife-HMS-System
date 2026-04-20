import React from 'react';
import { useForm, usePage } from '@inertiajs/react';
import { Transition } from '@headlessui/react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Briefcase, Award, Building, CheckCircle, ShieldCheck } from 'lucide-react';

export default function UpdateProfessionalProfileForm({ className = '' }) {
    const { auth, staff } = usePage().props;
    const user = auth.user;

    // Only render if staff data exists
    if (!staff) return null;

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
        specialization: staff.specialization || '',
        department: staff.department || '',
        license_number: staff.license_number || '',
    });

    const submit = (e) => {
        e.preventDefault();
        patch(route('profile.update'), {
            preserveScroll: true,
        });
    };

    return (
        <section className={`${className} bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700`}>
            <header className="flex items-center gap-4 mb-10">
                <div className="p-3 bg-pink-50 dark:bg-pink-900/20 rounded-xl text-pink-600 dark:text-pink-400 shadow-sm border border-pink-100/50">
                    <Briefcase size={24} />
                </div>
                <div>
                    <h2 className="text-2xl font-bold text-gray-900 dark:text-white">Professional Profile</h2>
                    <p className="text-sm text-gray-500 dark:text-gray-400">Update your clinical credentials and department assignment.</p>
                </div>
            </header>

            <form onSubmit={submit} className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <InputLabel htmlFor="specialization" value="Specialization" className="text-gray-700 font-bold mb-2 ml-1" />
                        <div className="relative group">
                            <TextInput
                                id="specialization"
                                className="block w-full pl-12 h-14 bg-white border-gray-200 focus:bg-white transition-all text-black"
                                value={data.specialization}
                                onChange={(e) => setData('specialization', e.target.value)}
                                placeholder="e.g. General Practice, Cardiology"
                            />
                            <Award className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-pink-500 transition-colors" size={20} />
                        </div>
                        <InputError className="mt-2" message={errors.specialization} />
                    </div>

                    <div>
                        <InputLabel htmlFor="department" value="Department" className="text-gray-700 font-bold mb-2 ml-1" />
                        <div className="relative group">
                            <TextInput
                                id="department"
                                className="block w-full pl-12 h-14 bg-white border-gray-200 focus:bg-white transition-all text-black"
                                value={data.department}
                                onChange={(e) => setData('department', e.target.value)}
                                placeholder="e.g. Outpatient, Surgery"
                            />
                            <Building className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-pink-500 transition-colors" size={20} />
                        </div>
                        <InputError className="mt-2" message={errors.department} />
                    </div>
                </div>

                <div>
                    <InputLabel htmlFor="license_number" value="Medical License Number" className="text-gray-700 font-bold mb-2 ml-1" />
                    <div className="relative group md:w-1/2">
                        <TextInput
                            id="license_number"
                            className="block w-full pl-12 h-14 bg-white border-gray-200 focus:bg-white transition-all text-black"
                            value={data.license_number}
                            onChange={(e) => setData('license_number', e.target.value)}
                            placeholder="MED-123456"
                        />
                        <ShieldCheck className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-pink-500 transition-colors" size={20} />
                    </div>
                    <InputError className="mt-2" message={errors.license_number} />
                </div>

                <div className="flex items-center gap-6 pt-6 border-t border-gray-50 dark:border-gray-700">
                    <PrimaryButton disabled={processing} className="relative !py-4 !px-10 !rounded-xl text-lg font-bold">
                        <span className={processing ? 'opacity-0' : 'opacity-100'}>Update Credentials</span>
                        {processing && (
                            <div className="absolute inset-0 flex items-center justify-center">
                                <div className="w-6 h-6 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                            </div>
                        )}
                    </PrimaryButton>

                    <Transition
                        show={recentlySuccessful}
                        enter="transition ease-in-out duration-300"
                        enterFrom="opacity-0 translate-x-4"
                        enterTo="opacity-100 translate-x-0"
                        leave="transition ease-in-out duration-300"
                        leaveTo="opacity-0 translate-x-4"
                    >
                        <div className="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-medium">
                            <CheckCircle size={20} />
                            <span>Staff profile updated!</span>
                        </div>
                    </Transition>
                </div>
            </form>
        </section>
    );
}

