import React from 'react';
import { Link } from '@inertiajs/react';

export default function BlogSection({ blogs }) {
    return (
        <section className="py-20 bg-white" id="blog">
            <div className="container">
                <div className="text-center mb-16">
                    <span className="badge bg-pink-100 text-pink-600 px-3 py-2 rounded-pill mb-3 font-bold text-uppercase tracking-wider">Journal</span>
                    <h2 className="display-5 fw-bold text-gray-900 mb-4 section-title-main">Latest Health Insights</h2>
                    <p className="lead text-gray-600 max-w-2xl mx-auto">Staying informed is the first step towards a healthier life.</p>
                </div>

                <div className="row g-4 mb-10 h-auto">
                    {blogs.length > 0 ? blogs.map(blog => (
                        <div key={blog.id} className="col-lg-4 col-md-6 h-auto">
                            <div className="card h-100 border-0 shadow-sm rounded-2xl overflow-hidden transition-all hover-lift">
                                <div className="position-relative bg-light" style={{ height: '220px' }}>
                                    <img 
                                        src={blog.image_path ? (blog.image_path.includes('/assets/img/') || blog.image_path.startsWith('http') ? blog.image_path : `/storage/${blog.image_path.replace(/^\//, '')}`) : '/assets/img/logo/Logo2-transparent.png'} 
                                        className={`w-100 h-100 ${blog.image_path ? 'object-fit-cover' : 'object-fit-contain p-5'}`}
                                        alt={blog.title}
                                        onError={(e) => { e.target.src = '/assets/img/logo/Logo2-transparent.png'; e.target.className = 'w-100 h-100 object-fit-contain p-5 opacity-50'; }}
                                    />
                                    <div className="position-absolute top-0 end-0 m-3">
                                        <span className="badge bg-white text-primary rounded-pill shadow-sm py-2 px-3">
                                            {blog.tags?.[0] || 'Article'}
                                        </span>
                                    </div>
                                </div>
                                <div className="card-body p-5">
                                    <h4 className="fw-bold text-gray-900 mb-3 h5 line-clamp-2">{blog.title}</h4>
                                    <p className="text-gray-600 small mb-6 line-clamp-3">
                                        {blog.excerpt || blog.content.substring(0, 100) + '...'}
                                    </p>
                                    <Link 
                                        href={route('blogs.public.show', blog.slug)}
                                        className="btn btn-outline-primary btn-sm rounded-pill px-4 font-bold"
                                    >
                                        Learn More <i className="fas fa-arrow-right ms-2"></i>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    )) : (
                        <div className="col-12 py-10 text-center">
                            <div className="p-8 border-2 border-dashed border-gray-200 rounded-3xl">
                                <i className="fas fa-blog fa-3x text-gray-200 mb-4"></i>
                                <h5 className="text-gray-400">No recent articles posted.</h5>
                            </div>
                        </div>
                    )}
                </div>

                <div className="text-center">
                    <Link href={route('blogs.public.index')} className="btn btn-primary rounded-pill px-8 py-3 font-bold shadow-lg">
                        View All Articles <i className="fas fa-external-link-alt ms-2"></i>
                    </Link>
                </div>
            </div>
        </section>
    );
}
