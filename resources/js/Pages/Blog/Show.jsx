import { Link, Head } from '@inertiajs/react';
import React from 'react';

export default function Show({ blog }) {
    return (
        <div className="bg-gray-50 min-h-screen">
            <Head title={`${blog.title} - Nyalife Blog`} />
            
            {/* Simple Navbar */}
            <nav className="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
                <div className="container">
                    <Link className="navbar-brand d-flex align-items-center h-auto" href="/">
                        <img src="/assets/img/logo/Logo2-transparent.png" alt="Nyalife HMS" height="40" className="me-2 bg-white rounded-2 p-1" />
                        <span className="fw-bold">Nyalife HMS</span>
                    </Link>
                    <div className="ms-auto d-flex gap-2">
                        <Link href={route('blogs.public.index')} className="btn btn-outline-light btn-sm rounded-pill px-3">
                            <i className="fas fa-th me-2"></i> All Articles
                        </Link>
                    </div>
                </div>
            </nav>

            <article className="pb-20">
                <div className="bg-white border-bottom mb-10 overflow-hidden" style={{ maxHeight: '500px' }}>
                    <div className="container px-0 mx-auto">
                        <img 
                            src={blog.image_path ? (blog.image_path.includes('/assets/img/') || blog.image_path.startsWith('http') ? blog.image_path : `/storage/${blog.image_path.replace(/^\//, '')}`) : '/assets/img/logo/Logo2-transparent.png'} 
                            alt={blog.title}
                            className={`w-100 h-100 ${blog.image_path ? 'object-fit-cover' : 'object-fit-contain p-20 bg-light'}`}
                            style={{ minHeight: '400px' }}
                            onError={(e) => { e.target.src = '/assets/img/logo/Logo2-transparent.png'; e.target.className = 'w-100 h-100 object-fit-contain p-20 bg-light opacity-50'; }}
                        />
                    </div>
                </div>

                <div className="container px-4 mx-auto">
                    <div className="max-w-4xl mx-auto">
                        <div className="card border-0 shadow-lg rounded-3xl mt-n10 position-relative bg-white p-6 p-md-10">
                            <div className="mb-4 d-flex flex-wrap gap-2">
                                {blog.tags?.map(tag => (
                                    <span key={tag} className="badge bg-pink-100 text-pink-600 px-3 py-2 rounded-pill font-bold">
                                        #{tag}
                                    </span>
                                ))}
                            </div>

                            <h1 className="display-4 fw-bold text-gray-900 mb-6 leading-tight">{blog.title}</h1>
                            
                            <div className="d-flex align-items-center border-bottom pb-6 mb-8">
                                <div className="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style={{width: '48px', height: '48px'}}>
                                    <i className="fas fa-user-md fa-lg"></i>
                                </div>
                                <div>
                                    <div className="fw-bold text-gray-900">{blog.author?.first_name} {blog.author?.last_name}</div>
                                    <div className="text-muted small">
                                        <i className="far fa-calendar-alt me-1"></i> {new Date(blog.published_at || blog.created_at).toLocaleDateString()}
                                    </div>
                                </div>
                            </div>

                            <div className="blog-content prose prose-pink max-w-none text-gray-700 lead" style={{ fontSize: '1.15rem', lineHeight: '1.8' }}>
                                {blog.content.split('\n').map((para, i) => (
                                    para.trim() && <p key={i} className="mb-4">{para}</p>
                                ))}
                            </div>

                            <div className="mt-12 pt-8 border-top d-flex justify-content-between align-items-center">
                                <Link href={route('blogs.public.index')} className="btn btn-light rounded-pill px-4">
                                    <i className="fas fa-chevron-left me-2"></i> Back to Blog
                                </Link>
                                <div className="d-flex gap-3">
                                    <span className="small text-muted me-2">Share:</span>
                                    <a href="#" className="text-primary"><i className="fab fa-facebook fa-lg"></i></a>
                                    <a href="#" className="text-info"><i className="fab fa-twitter fa-lg"></i></a>
                                    <a href="#" className="text-danger"><i className="fas fa-envelope fa-lg"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            {/* CTA */}
            <section className="bg-primary py-16 text-white text-center">
                <div className="container px-4">
                    <h2 className="display-6 fw-bold mb-4">Ready to Prioritize Your Health?</h2>
                    <p className="lead mb-8 opacity-90 max-w-2xl mx-auto">Book an appointment with our specialists today and receive the dedicated care you deserve.</p>
                    <Link href="/#guest-appointment" className="btn btn-light btn-lg rounded-pill px-8 font-bold shadow-lg">
                        Schedule Your Visit
                    </Link>
                </div>
            </section>
        </div>
    );
}
