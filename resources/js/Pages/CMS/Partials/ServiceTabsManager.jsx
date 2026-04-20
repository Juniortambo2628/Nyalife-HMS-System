import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import { toast } from 'react-hot-toast';

export default function ServiceTabsManager({ tabs: initialTabs }) {
    const [tabs, setTabs] = useState(initialTabs || []); // Manage local state for tabs
    const [activeIndex, setActiveIndex] = useState(null);
    const { setData, post, processing } = useForm({ tabs });

    const toggleAccordion = (index) => {
        setActiveIndex(activeIndex === index ? null : index);
    };

    const addTab = () => {
        const newTab = { title: 'New Service', icon: 'fa-star', content_title: '', content_lead: '', content_body: '' };
        const newTabs = [...tabs, newTab];
        setTabs(newTabs);
        setData('tabs', newTabs);
        setActiveIndex(tabs.length); // Open the new tab
    };

    const removeTab = (index) => {
        const newTabs = tabs.filter((_, i) => i !== index);
        setTabs(newTabs);
        setData('tabs', newTabs);
        if (activeIndex === index) setActiveIndex(null);
    };

    const updateTab = (index, field, value) => {
        const newTabs = [...tabs];
        newTabs[index][field] = value;
        setTabs(newTabs);
        setData('tabs', newTabs);
    };

    const saveTabs = () => {
        post(route('cms.service-tabs.update'), {
            preserveScroll: true,
            onSuccess: () => toast.success('Service tabs updated successfully!'),
            onError: () => toast.error('Failed to update service tabs.')
        });
    };

    return (
        <div className="card shadow-sm border-0 rounded-2xl bg-white p-5 mb-5">
            <div className="d-flex align-items-center justify-content-between mb-4 border-bottom border-pink-100 pb-3">
                <div className="d-flex align-items-center">
                    <div className="avatar-sm bg-pink-100 text-pink-500 rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i className="fas fa-layer-group"></i>
                    </div>
                    <h5 className="fw-bold text-gray-900 mb-0">Services Tabs Management</h5>
                </div>
                <button onClick={addTab} type="button" className="btn btn-sm btn-outline-pink rounded-pill">
                    <i className="fas fa-plus me-1"></i> Add Tab
                </button>
            </div>

            <div className="space-y-4">
                {tabs.map((tab, index) => (
                    <div className="border border-gray-200 rounded-2xl overflow-hidden shadow-sm transition-all hover:shadow-md" key={index}>
                        <div
                            className={`p-4 flex items-center justify-between cursor-pointer transition-colors ${activeIndex === index ? 'bg-pink-50' : 'bg-gray-50 hover:bg-gray-100'}`}
                            onClick={() => toggleAccordion(index)}
                        >
                            <div className="flex items-center gap-3">
                                <i className={`fas ${tab.icon || 'fa-star'} text-pink-500`}></i>
                                <span className={`font-bold ${activeIndex === index ? 'text-pink-600' : 'text-gray-700'}`}>
                                    {tab.title || `Tab ${index + 1}`}
                                </span>
                            </div>
                            <i className={`fas fa-chevron-down transition-transform duration-300 ${activeIndex === index ? 'rotate-180 text-pink-500' : 'text-gray-400'}`}></i>
                        </div>

                        {activeIndex === index && (
                            <div className="p-5 bg-white border-t border-gray-100">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel value="Tab Title" />
                                        <TextInput
                                            value={tab.title}
                                            onChange={(e) => updateTab(index, 'title', e.target.value)}
                                            className="w-full mt-1"
                                        />
                                    </div>
                                    <div>
                                        <InputLabel value="FontAwesome Icon (e.g. fa-baby)" />
                                        <TextInput
                                            value={tab.icon}
                                            onChange={(e) => updateTab(index, 'icon', e.target.value)}
                                            className="w-full mt-1"
                                        />
                                    </div>
                                    <div className="md:col-span-2">
                                        <InputLabel value="Content Title" />
                                        <TextInput
                                            value={tab.content_title}
                                            onChange={(e) => updateTab(index, 'content_title', e.target.value)}
                                            className="w-full mt-1"
                                        />
                                    </div>
                                    <div className="md:col-span-2">
                                        <InputLabel value="Content Lead (Bold sub-text)" />
                                        <TextInput
                                            value={tab.content_lead}
                                            onChange={(e) => updateTab(index, 'content_lead', e.target.value)}
                                            className="w-full mt-1"
                                        />
                                    </div>
                                    <div className="md:col-span-2">
                                        <InputLabel value="Body Text" />
                                        <textarea
                                            className="form-control mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500"
                                            rows="4"
                                            value={tab.content_body}
                                            onChange={(e) => updateTab(index, 'content_body', e.target.value)}
                                        ></textarea>
                                    </div>
                                    <div className="md:col-span-2 text-end">
                                        <button onClick={() => removeTab(index)} type="button" className="btn btn-sm btn-outline-danger rounded-pill">
                                            <i className="fas fa-trash me-1"></i> Remove This Tab
                                        </button>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                ))}
            </div>

            {tabs.length > 0 && (
                <div className="text-end mt-4">
                    <button onClick={saveTabs} disabled={processing} type="button" className="btn btn-secondary rounded-pill px-5">
                        {processing ? 'Saving...' : 'Save All Tabs'}
                    </button>
                </div>
            )}

            <style>{`
                .btn-outline-pink { color: #ec4899; border-color: #ec4899; }
                .btn-outline-pink:hover { background-color: #ec4899; color: white; }
            `}</style>
        </div>
    );
}
