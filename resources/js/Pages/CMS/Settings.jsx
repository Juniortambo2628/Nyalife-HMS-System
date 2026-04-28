import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import TextInput from '@/Components/TextInput';
import { toast } from 'react-hot-toast';

// FilePond imports
import { FilePond, registerPlugin } from 'react-filepond';
import 'filepond/dist/filepond.min.css';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';

registerPlugin(FilePondPluginImagePreview, FilePondPluginFileValidateType);

import SectionOrder from './Partials/SectionOrder';
import ServiceTabsManager from './Partials/ServiceTabsManager';

export default function Settings({ settings, serviceTabs }) {
    const { data, setData, post, processing, errors } = useForm({
        settings: Object.values(settings).flat().reduce((acc, s) => {
            acc[s.key] = s.value;
            return acc;
        }, {})
    });

    const handleChange = (key, value) => {
        setData('settings', {
            ...data.settings,
            [key]: value
        });
    };

    const handleFileChange = (key, fileItems) => {
        if (fileItems.length > 0) {
            handleChange(key, fileItems[0].file);
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('cms.update'), {
            onSuccess: () => toast.success('CMS Configuration updated successfully!'),
            onError: () => toast.error('Failed to update CMS Configuration. Please check for errors.')
        });
    };

    return (
        <AuthenticatedLayout
            headerTitle="Landing Page Configuration"
            breadcrumbs={[{ label: 'Admin', url: '/dashboard' }, { label: 'CMS', active: true }]}
            toolbarActions={
                <button 
                    onClick={handleSubmit}
                    className="btn btn-primary rounded-pill px-4 py-2 fw-bold small" 
                    disabled={processing}
                >
                    <i className="fas fa-save me-1"></i> Save CMS Configuration
                </button>
            }
        >
            <Head title="CMS Settings" />



            <div className="py-0">
                {/* Section Order Tool - Special Case */}
                {data.settings.landing_page_order && (
                    <div className="mb-5">
                        <SectionOrder 
                            value={data.settings.landing_page_order} 
                            onChange={(val) => handleChange('landing_page_order', val)}
                        />
                    </div>
                )}

                {/* Service Tabs Manager */}
                <ServiceTabsManager tabs={serviceTabs} />

                <form onSubmit={handleSubmit}>
                    {Object.entries(settings).map(([group, items]) => (
                        <div key={`group-${group}`} className="card shadow-sm border-0 rounded-2xl overflow-hidden bg-white p-5 mb-5">
                            <div className="d-flex align-items-center mb-4 border-bottom border-pink-100 pb-3">
                                <div className="avatar-sm bg-pink-100 text-pink-500 rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <i className={`fas ${
                                        group === 'hero' ? 'fa-star' : 
                                        group === 'hero_cards' ? 'fa-th-large' :
                                        group === 'about' ? 'fa-info-circle' : 
                                        group === 'contact' ? 'fa-phone' : 'fa-cog'
                                    }`}></i>
                                </div>
                                <h5 className="fw-bold text-gray-900 mb-0 text-capitalize">
                                    {group.replace(/_/g, ' ')} Configuration
                                </h5>
                            </div>
                            <div className="row g-4 h-auto">
                                {items.map((item, itemIdx) => {
                                    // Skip landing_page_order as we handle it above
                                    if (item.key === 'landing_page_order') return null;

                                    return (
                                        <div key={`setting-${item.id || item.key || itemIdx}`} className="col-lg-6 text-dark h-auto">
                                            <div className="form-group mb-2">
                                                <InputLabel htmlFor={item.key} value={item.label || item.key.replace(/_/g, ' ').toUpperCase()} className="fw-bold text-gray-700 mb-2" />
                                                
                                                {item.type === 'textarea' ? (
                                                    <textarea
                                                        id={item.key}
                                                        className="form-control border-gray-200 focus:border-pink-500 focus:ring-pink-500 rounded-xl shadow-sm transition-all"
                                                        rows="4"
                                                        value={data.settings[item.key] || ''}
                                                        onChange={(e) => handleChange(item.key, e.target.value)}
                                                        placeholder={`Enter ${(item.label || 'value').toLowerCase()}...`}
                                                    ></textarea>
                                                ) : item.type === 'image' ? (
                                                    <div className="cms-image-upload-container">
                                                        <div className="mb-3">
                                                            <FilePond
                                                                onupdatefiles={(fileItems) => handleFileChange(item.key, fileItems)}
                                                                allowMultiple={false}
                                                                maxFiles={1}
                                                                labelIdle='Drag & Drop your image or <span class="filepond--label-action">Browse</span>'
                                                                acceptedFileTypes={['image/*']}
                                                            />
                                                        </div>
                                                        {data.settings[item.key] && typeof data.settings[item.key] === 'string' && (
                                                            <div className="mt-2 p-3 border rounded-2xl bg-light d-flex align-items-center gap-3 shadow-sm">
                                                                <div className="flex-shrink-0">
                                                                    <img 
                                                                        src={data.settings[item.key].startsWith('cms/') || !data.settings[item.key].startsWith('/') ? `/storage/${data.settings[item.key].replace(/^\/storage\//, '')}` : data.settings[item.key]} 
                                                                        className="rounded-xl shadow-sm border-2 border-white" 
                                                                        style={{ height: '80px', width: '80px', objectFit: 'cover' }} 
                                                                        alt="Current" 
                                                                    />
                                                                </div>
                                                                <div className="overflow-hidden">
                                                                    <small className="text-muted d-block fw-bold">Live Preview:</small>
                                                                    <code className="extra-small text-pink-500 text-truncate d-block">{data.settings[item.key]}</code>
                                                                </div>
                                                            </div>
                                                        )}
                                                    </div>
                                                ) : (
                                                    <TextInput
                                                        id={item.key}
                                                        className="w-100"
                                                        value={data.settings[item.key] || ''}
                                                        onChange={(e) => handleChange(item.key, e.target.value)}
                                                        placeholder={`Enter ${(item.label || 'value').toLowerCase()}...`}
                                                    />
                                                )}
                                                <InputError message={errors[`settings.${item.key}`]} className="mt-2" />
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    ))}

                </form>
                
            </div>
        </AuthenticatedLayout>
    );
}

