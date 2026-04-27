import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import PageHeader from '@/Components/PageHeader';
import DashboardTable from '@/Components/DashboardTable';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import ViewToggle from '@/Components/ViewToggle';

export default function Index({ blogs }) {
    const [viewMode, setViewMode] = useState('grid'); // 'grid' or 'list'

    const handleDelete = (id) => {
        if (confirm('Are you sure you want to delete this blog post?')) {
            router.delete(route('blog.destroy', id));
        }
    };

    const getImageUrl = (path) => {
        if (!path) return '/assets/img/logo/Logo2-transparent.png';
        if (path.includes('/assets/img/') || path.startsWith('http')) return path;
        return `/storage/${path.replace(/^\//, '')}`;
    };

    return (
        <AuthenticatedLayout
            header="Blog Management"
        >
            <Head title="Manage Blogs" />

            <PageHeader 
                title="Hospital Blog Posts"
                breadcrumbs={[{ label: 'Admin', url: '/dashboard' }, { label: 'Blogs', active: true }]}
            />

            <div className="py-0">
                {viewMode === 'list' ? (
                    <DashboardTable 
                        data={blogs}
                        columns={[
                            {
                                header: 'Article',
                                accessorKey: 'title',
                                cell: info => (
                                    <div className="d-flex align-items-center">
                                        <div 
                                            className="rounded-lg bg-light me-3 flex-shrink-0"
                                            style={{ 
                                                width: '50px', 
                                                height: '50px', 
                                                backgroundImage: info.row.original.image_path ? `url(${getImageUrl(info.row.original.image_path)})` : 'none',
                                                backgroundSize: 'cover',
                                                backgroundPosition: 'center',
                                                display: 'flex',
                                                alignItems: 'center',
                                                justifyContent: 'center'
                                        }}>
                                            {!info.row.original.image_path && <i className="fas fa-image text-muted"></i>}
                                        </div>
                                        <div>
                                            <div className="fw-bold text-gray-900">{info.getValue()}</div>
                                            <div className="text-pink-500 extra-small font-semibold">/{info.row.original.slug}</div>
                                        </div>
                                    </div>
                                )
                            },
                            {
                                header: 'Details',
                                accessorKey: 'author',
                                cell: info => (
                                    <div>
                                        <div className="small"><i className="fas fa-user-circle me-1 text-muted"></i> {info.row.original.author?.first_name} {info.row.original.author?.last_name}</div>
                                        <div className="extra-small text-muted"><i className="fas fa-calendar-alt me-1"></i> {new Date(info.row.original.created_at).toLocaleDateString()}</div>
                                    </div>
                                )
                            },
                            {
                                header: 'Status',
                                accessorKey: 'is_published',
                                cell: info => (
                                    <span className={`badge rounded-pill px-3 py-2 ${info.getValue() ? 'bg-soft-success text-success' : 'bg-soft-secondary text-secondary'}`}>
                                        <i className={`fas fa-${info.getValue() ? 'check-circle' : 'clock'} me-1`}></i>
                                        {info.getValue() ? 'Published' : 'Draft'}
                                    </span>
                                )
                            },
                            {
                                header: 'Actions',
                                id: 'actions',
                                cell: info => (
                                    <div className="text-end">
                                        <div className="d-flex justify-content-end gap-2">
                                            <Link href={route('blog.edit', info.row.original.id)} className="btn btn-sm btn-icon btn-outline-primary hover-lift">
                                                <i className="fas fa-edit"></i>
                                            </Link>
                                            <button onClick={() => handleDelete(info.row.original.id)} className="btn btn-sm btn-icon btn-outline-danger hover-lift">
                                                <i className="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                )
                            }
                        ]}
                        emptyMessage="No blog posts found."
                    />
                ) : (
                    <div className="row g-4">
                        {blogs.length > 0 ? (
                            blogs.map((post) => (
                                <div key={post.id} className="col-md-4">
                                    <div className="card h-100 shadow-sm border-0 rounded-2xl overflow-hidden bg-white hover-lift transition-all">
                                        <div 
                                            className="card-img-top bg-light" 
                                            style={{ 
                                                height: '180px', 
                                                backgroundImage: post.image_path ? `url(${getImageUrl(post.image_path)})` : 'none',
                                                backgroundSize: 'cover',
                                                backgroundPosition: 'center',
                                                display: 'flex',
                                                alignItems: 'center',
                                                justifyContent: 'center'
                                            }}
                                        >
                                            {!post.image_path && <i className="fas fa-image fa-3x text-gray-200"></i>}
                                            <div className="position-absolute top-0 end-0 m-3">
                                                <span className={`badge rounded-pill shadow-sm px-3 py-2 ${post.is_published ? 'bg-success' : 'bg-secondary'}`}>
                                                    {post.is_published ? 'Published' : 'Draft'}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="card-body p-4 d-flex flex-column">
                                            <h5 className="fw-bold text-gray-900 mb-2 line-clamp-2">{post.title}</h5>
                                            <p className="small text-muted mb-4 line-clamp-3">
                                                {post.excerpt || (post.content?.substring(0, 100) + '...')}
                                            </p>
                                            <div className="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                                <div className="d-flex align-items-center">
                                                    <div className="avatar-xs bg-pink-100 text-pink-500 rounded-circle d-flex align-items-center justify-content-center me-2" style={{width: '24px', height: '24px', fontSize: '10px'}}>
                                                        {post.author?.first_name?.charAt(0)}
                                                    </div>
                                                    <span className="extra-small font-bold">{post.author?.first_name}</span>
                                                </div>
                                                <div className="d-flex gap-2">
                                                    <Link href={route('blog.edit', post.id)} className="btn btn-sm btn-icon btn-soft-primary rounded-circle">
                                                        <i className="fas fa-edit"></i>
                                                    </Link>
                                                    <button onClick={() => handleDelete(post.id)} className="btn btn-sm btn-icon btn-soft-danger rounded-circle">
                                                        <i className="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="col-12 text-center py-5">
                                <div className="card shadow-sm border-0 rounded-2xl p-5 bg-white">
                                    <i className="fas fa-blog fa-3x text-gray-200 mb-3"></i>
                                    <h5 className="text-muted">No blog posts yet. Start writing!</h5>
                                </div>
                            </div>
                        )}
                    </div>
                )}

                <UnifiedToolbar 
                    actions={
                        <div className="d-flex align-items-center gap-2">
                            <ViewToggle view={viewMode} setView={setViewMode} />
                            <Link href={route('blog.create')} className="btn btn-primary rounded-pill px-3 py-2 fw-bold small">
                                <i className="fas fa-plus me-1"></i> New Post
                            </Link>
                        </div>
                    }
                />
            </div>

            <style>{`
                .extra-small { font-size: 0.75rem; }
                .btn-icon { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0; }
                .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
                .bg-soft-secondary { background-color: rgba(108, 117, 125, 0.1); }
                .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
                .btn-soft-primary { border: 0; color: #0d6efd; background-color: rgba(13, 110, 253, 0.1); }
                .btn-soft-primary:hover { background-color: #0d6efd; color: white; }
                .btn-soft-danger { border: 0; color: #dc3545; background-color: rgba(220, 53, 69, 0.1); }
                .btn-soft-danger:hover { background-color: #dc3545; color: white; }
                .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
                .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
            `}</style>
        </AuthenticatedLayout>
    );
}
