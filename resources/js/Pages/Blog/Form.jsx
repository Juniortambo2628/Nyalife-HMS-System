import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import TextInput from '@/Components/TextInput';
import PrimaryButton from '@/Components/PrimaryButton';

export default function Form({ blog = null }) {
    const isEditing = !!blog;

    const { data, setData, post, processing, errors } = useForm({
        title: blog?.title || '',
        excerpt: blog?.excerpt || '',
        content: blog?.content || '',
        tags: blog?.tags || [],
        image: null,
        _method: isEditing ? 'POST' : 'POST', // Use POST for both, handled by route name
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (isEditing) {
            // Laravel requires POST with _method spoofing for file uploads on PUT/PATCH, 
            // but we named the route blog.update with POST in web.php for simplicity
            post(route('blog.update', blog.id));
        } else {
            post(route('blog.store'));
        }
    };

    return (
        <AuthenticatedLayout
            header={isEditing ? 'Edit Blog Post' : 'Create Blog Post'}
        >
            <Head title={isEditing ? 'Edit Post' : 'New Post'} />

            <PageHeader 
                title={isEditing ? 'Update Article' : 'Write New Article'}
                breadcrumbs={[
                    { label: 'Admin', url: '/dashboard' }, 
                    { label: 'Blogs', url: route('blog.manage') },
                    { label: isEditing ? 'Edit' : 'Create', active: true }
                ]}
            />

            <div className="py-0">
                <div className="card shadow-sm border-0 rounded-2xl overflow-hidden bg-white p-5">
                    <form onSubmit={handleSubmit} className="row g-4">
                        <div className="col-12">
                            <InputLabel htmlFor="title" value="Post Title" />
                            <TextInput
                                id="title"
                                className="mt-1 block w-100"
                                value={data.title}
                                onChange={(e) => setData('title', e.target.value)}
                                required
                                isFocused
                                placeholder="Enter a compelling title..."
                            />
                            <InputError message={errors.title} className="mt-2" />
                        </div>

                        <div className="col-12">
                            <InputLabel htmlFor="excerpt" value="Short Excerpt (Optional)" />
                            <textarea
                                id="excerpt"
                                className="form-control mt-1 border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-xl shadow-sm"
                                rows="2"
                                value={data.excerpt}
                                onChange={(e) => setData('excerpt', e.target.value)}
                                placeholder="A brief summary for the listing page..."
                            ></textarea>
                            <InputError message={errors.excerpt} className="mt-2" />
                        </div>

                        <div className="col-12">
                            <InputLabel htmlFor="content" value="Article Content" />
                            <textarea
                                id="content"
                                className="form-control mt-1 border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-xl shadow-sm"
                                rows="10"
                                value={data.content}
                                onChange={(e) => setData('content', e.target.value)}
                                required
                                placeholder="Write your health insights here..."
                            ></textarea>
                            <InputError message={errors.content} className="mt-2" />
                        </div>

                        <div className="col-md-6">
                            <InputLabel htmlFor="image" value="Featured Image" />
                            <input
                                id="image"
                                type="file"
                                className="form-control mt-1 border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-xl shadow-sm"
                                onChange={(e) => setData('image', e.target.files[0])}
                            />
                            {blog?.image_path && (
                                <div className="mt-2 p-2 border rounded-xl bg-light">
                                    <small className="text-muted d-block mb-1">Current Image:</small>
                                    <img src={`/storage/${blog.image_path}`} className="rounded shadow-sm" style={{ maxHeight: '100px' }} />
                                </div>
                            )}
                            <InputError message={errors.image} className="mt-2" />
                        </div>

                        <div className="col-12 d-flex justify-content-end gap-3 mt-5">
                            <Link href={route('blog.manage')} className="btn btn-light rounded-pill px-4 py-2 fw-bold">
                                Cancel
                            </Link>
                            <PrimaryButton className="rounded-pill px-5 py-2 font-bold shadow" disabled={processing}>
                                {isEditing ? 'Update Article' : 'Publish Article'}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
