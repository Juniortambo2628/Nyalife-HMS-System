import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import { useState } from 'react';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import TableActions from '@/Components/TableActions';

export default function Index({ blockOuts, filters, auth }) {
    const [showForm, setShowForm] = useState(false);
    const { data, setData, post, processing, reset } = useForm({
        doctor_id: '',
        block_date: '',
        start_time: '',
        end_time: '',
        reason: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('doctor-block-outs.store'), {
            onSuccess: () => {
                reset();
                setShowForm(false);
            },
        });
    };

    const handleDelete = (id) => {
        if (!confirm('Remove this block-out date?')) return;
        router.delete(route('doctor-block-outs.destroy', id));
    };

    return (
        <AuthenticatedLayout
            headerTitle="Doctor Availability"
            breadcrumbs={[{ label: 'Block-Out Dates', active: true }]}
        >
            <Head title="Doctor Block-Out Dates" />

            <UnifiedToolbar
                actions={[
                    {
                        label: showForm ? 'CANCEL' : 'ADD BLOCK-OUT',
                        icon: showForm ? 'fa-times' : 'fa-plus',
                        onClick: () => setShowForm(!showForm),
                        color: showForm ? 'gray' : 'primary',
                    },
                ]}
            />

            <div className="py-6">
                {/* Add Form */}
                {showForm && (
                    <div className="bg-white rounded-2xl shadow-sm p-6 mb-6">
                        <h3 className="text-lg font-bold mb-4">Add Block-Out Date</h3>
                        <form onSubmit={handleSubmit} className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Doctor *</label>
                                <select
                                    value={data.doctor_id}
                                    onChange={(e) => setData('doctor_id', e.target.value)}
                                    className="w-full border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500"
                                    required
                                >
                                    <option value="">Select Doctor</option>
                                    {/* Doctors would be passed as props in production */}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                                <input
                                    type="date"
                                    value={data.block_date}
                                    onChange={(e) => setData('block_date', e.target.value)}
                                    min={new Date().toISOString().split('T')[0]}
                                    className="w-full border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Start Time (optional - full day if blank)
                                </label>
                                <input
                                    type="time"
                                    value={data.start_time}
                                    onChange={(e) => setData('start_time', e.target.value)}
                                    className="w-full border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    End Time (optional)
                                </label>
                                <input
                                    type="time"
                                    value={data.end_time}
                                    onChange={(e) => setData('end_time', e.target.value)}
                                    className="w-full border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500"
                                />
                            </div>
                            <div className="md:col-span-2 lg:col-span-3">
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Reason (optional)
                                </label>
                                <input
                                    type="text"
                                    value={data.reason}
                                    onChange={(e) => setData('reason', e.target.value)}
                                    placeholder="e.g., Leave, Conference, Personal"
                                    className="w-full border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500"
                                />
                            </div>
                            <div className="flex items-end">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700 disabled:opacity-50"
                                >
                                    {processing ? 'Saving...' : 'Save Block-Out'}
                                </button>
                            </div>
                        </form>
                    </div>
                )}

                {/* Block-Out List */}
                <div className="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div className="p-4 border-b border-gray-100">
                        <h3 className="font-bold text-gray-900">Block-Out Dates</h3>
                    </div>
                    {blockOuts.data.length === 0 ? (
                        <div className="p-8 text-center text-gray-500">
                            <i className="fas fa-calendar-check text-4xl mb-3 text-gray-300"></i>
                            <p>No block-out dates configured.</p>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">
                                            Doctor
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">
                                            Date
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">
                                            Time
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">
                                            Reason
                                        </th>
                                        <th className="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {blockOuts.data.map((block) => (
                                        <tr key={block.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4 text-sm font-medium text-gray-900">
                                                Dr. {block.doctor?.user?.first_name} {block.doctor?.user?.last_name}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-600">
                                                {new Date(block.block_date).toLocaleDateString('en-US', {
                                                    weekday: 'short',
                                                    year: 'numeric',
                                                    month: 'short',
                                                    day: 'numeric',
                                                })}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-600">
                                                {block.start_time && block.end_time
                                                    ? `${block.start_time} - ${block.end_time}`
                                                    : 'Full Day'}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-600">{block.reason || '-'}</td>
                                            <td className="px-6 py-4 text-right">
                                                <button
                                                    onClick={() => handleDelete(block.id)}
                                                    className="text-red-500 hover:text-red-700 text-sm"
                                                >
                                                    <i className="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                    {blockOuts.last_page > 1 && (
                        <div className="p-4 border-t border-gray-100">{/* Pagination would go here */}</div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
