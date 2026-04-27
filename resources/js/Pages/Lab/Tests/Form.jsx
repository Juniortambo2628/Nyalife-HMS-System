import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import DangerButton from '@/Components/DangerButton';

export default function Form({ test = null }) {
    const isEditing = !!test;

    const { data, setData, post, put, processing, errors } = useForm({
        test_name: test?.test_name || '',
        description: test?.description || '',
        category: test?.category || 'General',
        price: test?.price || 0,
        normal_range: test?.normal_range || '',
        units: test?.units || '',
        is_active: test?.is_active ?? true,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (isEditing) {
            put(route('lab-tests.update', test.test_type_id));
        } else {
            post(route('lab-tests.store'));
        }
    };

    return (
        <AuthenticatedLayout
            header={isEditing ? `Edit Test: ${test.test_name}` : 'New Lab Test Type'}
        >
            <Head title={isEditing ? 'Edit Lab Test' : 'New Lab Test'} />

            <PageHeader 
                title={isEditing ? 'Update Test Protocol' : 'Create New Investigation'}
                breadcrumbs={[
                    { label: 'Lab', url: route('lab.index') }, 
                    { label: 'Manage Tests', url: route('lab-tests.index') },
                    { label: isEditing ? 'Edit' : 'New', active: true }
                ]}
            />

            <div className="py-0">
                <div className="card shadow-sm border-0 rounded-2xl overflow-hidden p-5 bg-white">
                    <form onSubmit={handleSubmit} className="row g-4">
                        <div className="col-md-8">
                            <InputLabel htmlFor="test_name" value="Test Name" />
                            <TextInput
                                id="test_name"
                                className="mt-1 block w-100"
                                value={data.test_name}
                                onChange={(e) => setData('test_name', e.target.value)}
                                required
                            />
                            <InputError message={errors.test_name} className="mt-2" />
                        </div>

                        <div className="col-md-4">
                            <InputLabel htmlFor="category" value="Category" />
                            <select
                                id="category"
                                className="form-select mt-1 block w-100 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                value={data.category}
                                onChange={(e) => setData('category', e.target.value)}
                                required
                            >
                                <option value="">Select Category</option>
                                {[
                                    'Hematology', 'Chemistry', 'Reproductive', 'Serology', 
                                    'Microbiology', 'Pathology', 'Parasitology', 'Biochemistry', 'Toxicology', 'General'
                                ].map(cat => (
                                    <option key={cat} value={cat}>{cat}</option>
                                ))}
                            </select>
                            <InputError message={errors.category} className="mt-2" />
                        </div>

                        <div className="col-12">
                            <InputLabel htmlFor="description" value="Description" />
                            <textarea
                                id="description"
                                className="form-control mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                rows="3"
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                            ></textarea>
                            <InputError message={errors.description} className="mt-2" />
                        </div>

                        <div className="col-md-4">
                            <InputLabel htmlFor="price" value="Price (KES)" />
                            <TextInput
                                id="price"
                                type="number"
                                className="mt-1 block w-100"
                                value={data.price}
                                onChange={(e) => setData('price', e.target.value)}
                                required
                            />
                            <InputError message={errors.price} className="mt-2" />
                        </div>

                        <div className="col-md-4">
                            <InputLabel htmlFor="normal_range" value="Normal Range" />
                            <TextInput
                                id="normal_range"
                                className="mt-1 block w-100"
                                value={data.normal_range}
                                onChange={(e) => setData('normal_range', e.target.value)}
                            />
                            <InputError message={errors.normal_range} className="mt-2" />
                        </div>

                        <div className="col-md-4">
                            <InputLabel htmlFor="units" value="Units" />
                            <TextInput
                                id="units"
                                className="mt-1 block w-100"
                                value={data.units}
                                onChange={(e) => setData('units', e.target.value)}
                            />
                            <InputError message={errors.units} className="mt-2" />
                        </div>

                        <div className="col-12 d-flex justify-content-between align-items-center mt-5">
                            <Link href={route('lab-tests.index')} className="btn btn-outline-secondary rounded-pill px-4">
                                Cancel
                            </Link>
                            <button className="btn btn-primary rounded-pill px-5 py-2 font-bold shadow" disabled={processing}>
                                {isEditing ? 'Save Changes' : 'Create Test Type'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
