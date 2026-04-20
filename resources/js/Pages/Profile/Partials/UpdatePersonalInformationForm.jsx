import React from 'react';
import { useForm, usePage } from '@inertiajs/react';
import { Transition } from '@headlessui/react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { User, Mail, Phone, MapPin, Calendar, CheckCircle } from 'lucide-react';

export default function UpdatePersonalInformationForm({ mustVerifyEmail, status, className = '' }) {
    const user = usePage().props.auth.user;

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
        first_name: user.first_name || '',
        last_name: user.last_name || '',
        email: user.email || '',
        phone: user.phone || '',
        gender: user.gender || '',
        date_of_birth: user.date_of_birth || '',
        address: user.address || '',
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
                    <User size={24} />
                </div>
                <div>
                    <h2 className="text-2xl font-bold text-gray-900 dark:text-white">Personal Details</h2>
                    <p className="text-sm text-gray-500 dark:text-gray-400">Keep your personal information up to date for accurate clinical records.</p>
                </div>
            </header>

            <form onSubmit={submit} className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <InputLabel htmlFor="first_name" value="First Name" className="text-gray-700 font-bold mb-2 ml-1" />
                        <div className="relative group">
                            <TextInput
                                id="first_name"
                                className="block w-full pl-12 h-14 bg-white border-gray-200 focus:bg-white transition-all text-black"
                                value={data.first_name}
                                onChange={(e) => setData('first_name', e.target.value)}
                                required
                                placeholder="Enter first name"
                            />
                            <User className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-pink-500 transition-colors" size={20} />
                        </div>
                        <InputError className="mt-2" message={errors.first_name} />
                    </div>

                    <div>
                        <InputLabel htmlFor="last_name" value="Last Name" className="text-gray-700 font-bold mb-2 ml-1" />
                        <div className="relative group">
                            <TextInput
                                id="last_name"
                                className="block w-full pl-12 h-14 bg-white border-gray-200 focus:bg-white transition-all text-black"
                                value={data.last_name}
                                onChange={(e) => setData('last_name', e.target.value)}
                                required
                                placeholder="Enter last name"
                            />
                            <User className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-pink-500 transition-colors" size={20} />
                        </div>
                        <InputError className="mt-2" message={errors.last_name} />
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <InputLabel htmlFor="email" value="Email Address" className="text-gray-700 font-bold mb-2 ml-1" />
                        <div className="relative group">
                            <TextInput
                                id="email"
                                type="email"
                                className="block w-full pl-12 h-14 bg-white border-gray-200 focus:bg-white transition-all text-black"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                required
                                placeholder="name@example.com"
                            />
                            <Mail className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-pink-500 transition-colors" size={20} />
                        </div>
                        <InputError className="mt-2" message={errors.email} />
                    </div>

                    <div>
                        <InputLabel htmlFor="phone" value="Phone Number" className="text-gray-700 font-bold mb-2 ml-1" />
                        <div className="relative group">
                            <TextInput
                                id="phone"
                                className="block w-full pl-12 h-14 bg-white border-gray-200 focus:bg-white transition-all text-black"
                                value={data.phone}
                                onChange={(e) => setData('phone', e.target.value)}
                                placeholder="+1 (555) 000-0000"
                            />
                            <Phone className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-pink-500 transition-colors" size={20} />
                        </div>
                        <InputError className="mt-2" message={errors.phone} />
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <InputLabel htmlFor="gender" value="Gender" className="text-gray-700 font-bold mb-2 ml-1" />
                        <select
                            id="gender"
                            className="mt-1 block w-full h-14 pl-4 bg-white border-gray-200 dark:border-gray-700 dark:bg-white text-black dark:text-black focus:border-pink-500 dark:focus:border-pink-600 focus:ring-4 focus:ring-pink-500/20 focus:bg-white transition-all rounded-xl shadow-sm"
                            value={data.gender}
                            onChange={(e) => setData('gender', e.target.value)}
                        >
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        <InputError className="mt-2" message={errors.gender} />
                    </div>

                    <div>
                        <InputLabel htmlFor="date_of_birth" value="Date of Birth" className="text-gray-700 font-bold mb-2 ml-1" />
                        <div className="relative group">
                            <TextInput
                                id="date_of_birth"
                                type="date"
                                className="block w-full pl-12 h-14 bg-white border-gray-200 focus:bg-white transition-all text-black"
                                value={data.date_of_birth}
                                onChange={(e) => setData('date_of_birth', e.target.value)}
                            />
                            <Calendar className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-pink-500 transition-colors" size={20} />
                        </div>
                        <InputError className="mt-2" message={errors.date_of_birth} />
                    </div>
                </div>

                <div>
                    <InputLabel htmlFor="address" value="Home Address" className="text-gray-700 font-bold mb-2 ml-1" />
                    <div className="relative group">
                        <textarea
                            id="address"
                            className="block w-full pl-12 pt-4 border-gray-200 dark:border-gray-700 dark:bg-white bg-white text-black dark:text-black focus:border-pink-500 dark:focus:border-pink-600 focus:ring-4 focus:ring-pink-500/20 focus:bg-white transition-all rounded-xl shadow-sm min-h-[120px]"
                            value={data.address}
                            onChange={(e) => setData('address', e.target.value)}
                            placeholder="Enter your street address, city, and state"
                        ></textarea>
                        <MapPin className="absolute left-4 top-4 text-gray-400 group-focus-within:text-pink-500 transition-colors" size={20} />
                    </div>
                    <InputError className="mt-2" message={errors.address} />
                </div>

                <div className="flex items-center gap-6 pt-6 border-t border-gray-50 dark:border-gray-700">
                    <PrimaryButton disabled={processing} className="relative !py-4 !px-10 !rounded-xl text-lg font-bold">
                        <span className={processing ? 'opacity-0' : 'opacity-100'}>Save Changes</span>
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
                            <span>Saved successfully!</span>
                        </div>
                    </Transition>
                </div>
            </form>
        </section>
    );
}

