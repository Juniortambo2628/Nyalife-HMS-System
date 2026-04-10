import { Link, Head, router } from '@inertiajs/react';
import React, { useState, useEffect } from 'react';

export default function PublicIndex({ blogs = { data: [], links: [] }, filters = {}, allTags = [] }) {
    const [search, setSearch] = useState(filters?.search || '');
    const [tag, setTag] = useState(filters?.tag || '');
    const [sort, setSort] = useState(filters?.sort || 'latest');

    // Handle case where blogs might still be undefined
    const blogData = blogs?.data || [];
    const blogLinks = blogs?.links || [];

    const handleImageError = (e) => {
        e.target.src = '/assets/img/logo/Logo2-transparent.png';
        e.target.className = 'w-100 h-100 object-fit-contain p-4 transition-all opacity-50';
    };

    const getImageUrl = (path) => {
        if (!path) return '/assets/img/logo/Logo2-transparent.png';
        if (path.includes('/assets/img/') || path.startsWith('http')) return path;
        return `/storage/${path.replace(/^\//, '')}`;
    };

    const handleSearch = (e) => {
        e.preventDefault();
        applyFilters();
    };

    const applyFilters = () => {
        router.get(route('blogs.public.index'), {
            search,
            tag,
            sort
        }, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setSearch('');
        setTag('');
        setSort('latest');
        router.get(route('blogs.public.index'));
    };

    return (
        <div className="bg-gray-50 min-h-screen">
            <Head title="Health & Wellness Blog" />
            
            {/* Simple Navbar */}
            <nav className="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
                <div className="container h-auto">
                    <Link className="navbar-brand d-flex align-items-center h-auto" href="/">
                        <img src="/assets/img/logo/Logo2-transparent.png" alt="Nyalife HMS" height="40" className="me-2 bg-white rounded-2 p-1" />
                        <span className="fw-bold">Nyalife HMS</span>
                    </Link>
                    <div className="ms-auto">
                        <Link href="/" className="btn btn-outline-light btn-sm rounded-pill px-3">
                            <i className="fas fa-arrow-left me-2"></i> Back to Home
                        </Link>
                    </div>
                </div>
            </nav>

            <header className="bg-white border-bottom py-10 mb-8">
                <div className="container px-4 mx-auto text-center">
                    <h1 className="display-4 fw-bold text-gray-900 mb-4">Health & Wellness Blog</h1>
                    <p className="lead text-gray-600 max-w-2xl mx-auto">Expert insights, health tips, and clinic updates from the Nyalife team.</p>
                </div>
            </header>

            <div className="container px-4 mx-auto pb-20">
                <div className="row g-5">
                    {/* Sidebar */}
                    <div className="col-lg-3">
                        <div className="sticky-top" style={{ top: '90px', zIndex: 10 }}>
                            {/* Search */}
                            <div className="card border-0 shadow-sm rounded-2xl p-4 mb-4">
                                <h5 className="fw-bold mb-3 d-flex align-items-center">
                                    <i className="fas fa-search text-primary me-2"></i> Search
                                </h5>
                                <form onSubmit={handleSearch}>
                                    <div className="input-group">
                                        <input 
                                            type="text" 
                                            className="form-control border-gray-200 rounded-start-pill ps-3"
                                            placeholder="Keyword..."
                                            value={search}
                                            onChange={(e) => setSearch(e.target.value)}
                                        />
                                        <button className="btn btn-primary rounded-end-pill px-3" type="submit">
                                            <i className="fas fa-search"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {/* Sort */}
                            <div className="card border-0 shadow-sm rounded-2xl p-4 mb-4">
                                <h5 className="fw-bold mb-3 d-flex align-items-center">
                                    <i className="fas fa-sort-amount-down text-primary me-2"></i> Sort By
                                </h5>
                                <select 
                                    className="form-select border-gray-200 rounded-pill"
                                    value={sort}
                                    onChange={(e) => {
                                        setSort(e.target.value);
                                        router.get(route('blogs.public.index'), { ...filters, sort: e.target.value });
                                    }}
                                >
                                    <option value="latest">Latest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="alphabetical">Alphabetical</option>
                                </select>
                            </div>

                            {/* Tags */}
                            <div className="card border-0 shadow-sm rounded-2xl p-4 mb-4">
                                <h5 className="fw-bold mb-3 d-flex align-items-center">
                                    <i className="fas fa-tags text-primary me-2"></i> Popular Tags
                                </h5>
                                <div className="d-flex flex-wrap gap-2">
                                    <button 
                                        onClick={() => { setTag(''); applyFilters(); }}
                                        className={`btn btn-sm rounded-pill px-3 ${tag === '' ? 'btn-primary' : 'btn-outline-secondary'}`}
                                    >
                                        All
                                    </button>
                                    {allTags.map(t => (
                                        <button 
                                            key={t}
                                            onClick={() => {
                                                const newTag = tag === t ? '' : t;
                                                setTag(newTag);
                                                router.get(route('blogs.public.index'), { ...filters, tag: newTag });
                                            }}
                                            className={`btn btn-sm rounded-pill px-3 ${tag === t ? 'btn-primary' : 'btn-outline-secondary'}`}
                                        >
                                            #{t}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {/* Clear */}
                            {(search || tag || sort !== 'latest') && (
                                <button onClick={clearFilters} className="btn btn-light w-100 rounded-pill shadow-sm py-2">
                                    <i className="fas fa-undo me-2"></i> Reset Filters
                                </button>
                            )}
                        </div>
                    </div>

                    {/* Blog Grid */}
                    <div className="col-lg-9">
                        <div className="row g-4">
                            {blogData.length > 0 ? blogData.map((blog) => (
                                <div key={blog.id} className="col-md-6">
                                    <div className="card h-100 shadow-sm border-0 rounded-2xl overflow-hidden bg-white hover-lift transition-all">
                                        <div className="position-relative overflow-hidden bg-light h-auto" style={{ height: '240px' }}>
                                            <img 
                                                src={getImageUrl(blog.image_path)} 
                                                alt={blog.title}
                                                className={`w-100 h-100 ${blog.image_path ? 'object-fit-cover' : 'object-fit-contain p-4'} transition-all`}
                                                onError={handleImageError}
                                            />
                                            {blog.tags?.[0] && (
                                                <div className="position-absolute top-0 start-0 m-3">
                                                    <span className="badge bg-primary rounded-pill px-3 py-2 shadow-sm font-bold">
                                                        {blog.tags[0]}
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                        <div className="card-body p-4 d-flex flex-column">
                                            <div className="d-flex align-items-center text-muted extra-small mb-3">
                                                <div className="avatar-xs bg-pink-100 text-pink-500 rounded-circle d-flex align-items-center justify-content-center me-2" style={{width: '24px', height: '24px'}}>
                                                    <i className="fas fa-user" style={{fontSize: '10px'}}></i>
                                                </div>
                                                <span className="me-3">{blog.author?.first_name} {blog.author?.last_name}</span>
                                                <span className="ms-auto"><i className="far fa-calendar-alt me-1"></i> {new Date(blog.published_at || blog.created_at).toLocaleDateString()}</span>
                                            </div>
                                            <h3 className="h4 card-title fw-bold mb-3 text-gray-900 leading-tight">{blog.title}</h3>
                                            <p className="card-text text-gray-600 mb-4 flex-grow-1 lead-sm">
                                                {blog.excerpt || blog.content.substring(0, 120) + '...'}
                                            </p>
                                            <Link 
                                                href={route('blogs.public.show', blog.slug)}
                                                className="btn btn-outline-primary rounded-pill py-2 font-bold hover-lift"
                                            >
                                                Read Article <i className="fas fa-arrow-right ms-2"></i>
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            )) : (
                                <div className="col-12 py-20 text-center">
                                    <div className="card border-0 shadow-sm rounded-2xl p-5 bg-white max-w-md mx-auto">
                                        <i className="fas fa-search fa-4x text-gray-200 mb-4"></i>
                                        <h3 className="fw-bold text-gray-900">No articles found</h3>
                                        <p className="text-gray-600 mb-4">We couldn't find any articles matching your search criteria. Try different keywords or filters.</p>
                                        <button onClick={clearFilters} className="btn btn-primary rounded-pill px-4">Browse All Articles</button>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Pagination */}
                        {blogLinks.length > 3 && (
                            <div className="mt-12 d-flex justify-content-center">
                                <nav aria-label="Blog pagination">
                                    <ul className="pagination gap-2 border-0">
                                        {blogLinks.map((link, i) => (
                                            <li key={i} className={`page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`}>
                                                <Link 
                                                    className={`page-link rounded-circle d-flex align-items-center justify-content-center ${link.active ? 'bg-primary border-primary' : 'bg-white border-gray-100 text-gray-600'}`}
                                                    style={{ width: '45px', height: '45px' }}
                                                    href={link.url || '#'}
                                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                                />
                                            </li>
                                        ))}
                                    </ul>
                                </nav>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            <style>{`
                .extra-small { font-size: 0.75rem; }
                .lead-sm { font-size: 0.95rem; line-height: 1.6; }
                .hover-lift:hover { transform: translateY(-5px); transition: transform 0.3s ease; }
            `}</style>
        </div>
    );
}
