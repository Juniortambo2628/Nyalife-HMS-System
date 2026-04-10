import { Link } from '@inertiajs/react';

export default function PageHeader({ title, breadcrumbs = [], actions, showBack = true }) {
    const handleBack = () => {
        window.history.back();
    };

    return (
        <div className="page-header-hero mb-4">
            <div className="card border-0 shadow-sm rounded-2xl bg-white overflow-hidden">
                <div className="card-body p-4 p-md-5 position-relative">
                    {/* Abstract background elements */}
                    <div className="position-absolute top-0 end-0 p-5 opacity-10 d-none d-lg-block">
                        <i className="fas fa-layer-group fa-10x text-pink-500"></i>
                    </div>

                    <div className="row align-items-center position-relative">
                        <div className="col-lg-8">
                            {/* Breadcrumbs */}
                            <nav aria-label="breadcrumb" className="mb-2">
                                <ol className="breadcrumb small font-bold text-uppercase tracking-wider mb-0">
                                    <li className="breadcrumb-item">
                                        <Link href="/dashboard" className="text-pink-500 text-decoration-none">Home</Link>
                                    </li>
                                    {breadcrumbs.map((crumb, index) => (
                                        <li 
                                            key={index} 
                                            className={`breadcrumb-item ${crumb.active ? 'active text-gray-400' : ''}`}
                                            aria-current={crumb.active ? 'page' : undefined}
                                        >
                                            {crumb.active ? (
                                                crumb.label
                                            ) : (
                                                <Link href={crumb.url} className="text-pink-500 text-decoration-none">{crumb.label}</Link>
                                            )}
                                        </li>
                                    ))}
                                </ol>
                            </nav>

                            <div className="d-flex align-items-center gap-3">
                                {showBack && (
                                    <button 
                                        onClick={handleBack}
                                        className="btn btn-sm btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                                        style={{ width: '40px', height: '40px' }}
                                        title="Go Back"
                                    >
                                        <i className="fas fa-chevron-left text-pink-500"></i>
                                    </button>
                                )}
                                <h2 className="display-6 fw-bold text-gray-900 mb-0">{title}</h2>
                            </div>
                        </div>

                        <div className="col-lg-4 mt-4 mt-lg-0 text-lg-end">
                            <div className="d-flex flex-wrap gap-2 justify-content-lg-end">
                                {actions}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
