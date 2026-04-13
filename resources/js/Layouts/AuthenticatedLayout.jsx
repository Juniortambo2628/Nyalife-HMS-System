import { Link, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';

// Role-based sidebar menu items matching legacy system
const sidebarMenus = {
    admin: [
        { id: 'dashboard', text: 'Dashboard', url: '/dashboard', icon: 'fas fa-tachometer-alt' },
        { id: 'appointments', text: 'Appointments', url: '/appointments', icon: 'fas fa-calendar-alt' },
        { id: 'patients', text: 'Patients', url: '/patients', icon: 'fas fa-users' },
        { id: 'consultations', text: 'Consultations', url: '/consultations', icon: 'fas fa-stethoscope' },
        { id: 'lab-requests', text: 'Lab Requests', url: route('lab.index'), icon: 'fas fa-flask' },
        { id: 'prescriptions', text: 'Prescriptions', url: '/prescriptions', icon: 'fas fa-prescription' },
        { id: 'invoices', text: 'Invoices', url: '/invoices', icon: 'fas fa-file-invoice' },
        { id: 'users', text: 'Users', url: '/users', icon: 'fas fa-user-cog' },
        { id: 'lab-tests-manage', text: 'Lab Test Management', url: route('lab-tests.index'), icon: 'fas fa-vials' },
        { id: 'reports', text: 'Reports', url: '/reports', icon: 'fas fa-chart-bar' },
        { id: 'blog-manage', text: 'Manage Blogs', url: route('blog.manage'), icon: 'fas fa-blog' },
        { id: 'cms-manage', text: 'Landing CMS', url: route('cms.index'), icon: 'fas fa-laptop-code' },
        { id: 'contact-messages', text: 'Website Messages', url: route('admin.messages.index'), icon: 'fas fa-envelope-open-text' },
    ],
    doctor: [
        { id: 'dashboard', text: 'Dashboard', url: '/dashboard', icon: 'fas fa-tachometer-alt' },
        { id: 'appointments', text: 'My Appointments', url: '/appointments', icon: 'fas fa-calendar-alt' },
        { id: 'consultations', text: 'Consultations', url: '/consultations', icon: 'fas fa-stethoscope' },
        { id: 'patients', text: 'View Patients', url: '/patients', icon: 'fas fa-users' },
        { id: 'lab-requests', text: 'Lab Requests', url: route('lab.index'), icon: 'fas fa-flask' },
        { id: 'prescriptions', text: 'Prescriptions', url: '/prescriptions', icon: 'fas fa-prescription' },
        { id: 'schedule', text: 'My Schedule', url: '/appointments/calendar', icon: 'fas fa-clock' },
    ],
    nurse: [
        { id: 'dashboard', text: 'Dashboard', url: '/dashboard', icon: 'fas fa-tachometer-alt' },
        { id: 'appointments', text: 'Appointments', url: '/appointments', icon: 'fas fa-calendar-alt' },
        { id: 'consultations', text: 'Consultations', url: '/consultations', icon: 'fas fa-stethoscope' },
        { id: 'patients', text: 'View Patients', url: '/patients', icon: 'fas fa-users' },
        { id: 'vitals', text: 'Record Vitals', url: '/vitals', icon: 'fas fa-heartbeat' },
    ],
    lab_technician: [
        { id: 'dashboard', text: 'Dashboard', url: '/dashboard', icon: 'fas fa-tachometer-alt' },
        { id: 'lab-requests', text: 'Lab Requests', url: route('lab.index'), icon: 'fas fa-flask' },
        { id: 'lab-tests', text: 'Lab Tests', url: '/lab-tests/manage', icon: 'fas fa-vial' },
        { id: 'samples', text: 'Samples', url: '/lab/tests', icon: 'fas fa-test-tube' },
    ],
    pharmacist: [
        { id: 'dashboard', text: 'Dashboard', url: '/dashboard', icon: 'fas fa-tachometer-alt' },
        { id: 'prescriptions', text: 'Prescriptions', url: '/prescriptions', icon: 'fas fa-prescription' },
        { id: 'inventory', text: 'Inventory', url: '/pharmacy/inventory', icon: 'fas fa-boxes' },
        { id: 'medicines', text: 'Medicines', url: '/pharmacy/medicines', icon: 'fas fa-pills' },
    ],
    receptionist: [
        { id: 'dashboard', text: 'Dashboard', url: '/dashboard', icon: 'fas fa-tachometer-alt' },
        { id: 'appointments', text: 'Appointments', url: '/appointments', icon: 'fas fa-calendar-alt' },
        { id: 'patients', text: 'Patients', url: '/patients', icon: 'fas fa-users' },
        { id: 'invoices', text: 'Invoices', url: '/invoices', icon: 'fas fa-file-invoice' },
    ],
    patient: [
        { id: 'dashboard', text: 'Dashboard', url: '/dashboard', icon: 'fas fa-tachometer-alt' },
        { id: 'appointments', text: 'My Appointments', url: '/appointments', icon: 'fas fa-calendar-alt' },
        { id: 'lab-results', text: 'Lab Results', url: '/lab-results', icon: 'fas fa-flask' },
        { id: 'prescriptions', text: 'My Prescriptions', url: '/prescriptions', icon: 'fas fa-prescription' },
        { id: 'profile', text: 'My Profile', url: '/profile', icon: 'fas fa-user' },
    ],
};

export default function AuthenticatedLayout({ header, children }) {
    const page = usePage();
    const { auth } = page.props;
    const currentUrl = page.url;
    const user = auth?.user || {};
    const userRole = user.role || 'patient';
    
    // Get menu items for current user role
    const menuItems = sidebarMenus[userRole] || sidebarMenus.patient;
    
    const [sidebarCollapsed, setSidebarCollapsed] = useState(() => {
        const saved = localStorage.getItem('sidebarCollapsed');
        return saved === 'true';
    });
    const [sidebarOpen, setSidebarOpen] = useState(false); // For mobile

    const toggleSidebar = () => {
        setSidebarCollapsed(prev => {
            const newState = !prev;
            localStorage.setItem('sidebarCollapsed', newState);
            return newState;
        });
    };

    const toggleMobileSidebar = () => {
        setSidebarOpen(!sidebarOpen);
    };

    // Apply sidebar classes to body for global CSS targeting
    useEffect(() => {
        const saved = localStorage.getItem('sidebarCollapsed');
        if (saved === 'true') document.body.classList.add('sidebar-collapsed');
        document.body.classList.add('has-sidebar');

        return () => {
            document.body.classList.remove('has-sidebar');
            document.body.classList.remove('sidebar-collapsed');
        };
    }, []);

    useEffect(() => {
        if (sidebarCollapsed) {
            document.body.classList.add('sidebar-collapsed');
        } else {
            document.body.classList.remove('sidebar-collapsed');
        }
        
        // Ensure dropdowns are initialized when the component mounts or updates
        const initDropdowns = () => {
             const dropdownElementList = document.querySelectorAll('[data-bs-toggle="dropdown"]');
             if (window.bootstrap) {
                 dropdownElementList.forEach(el => {
                     new window.bootstrap.Dropdown(el);
                 });
             }
        };
        
        initDropdowns();
    }, [sidebarCollapsed]);

    // Get display name (safely)
    const displayName = (user && user.first_name && user.last_name)
        ? `${user.first_name} ${user.last_name}` 
        : (user?.name || user?.username || 'User');

    return (
        <div className={`has-sidebar ${sidebarCollapsed ? 'sidebar-collapsed' : ''}`}>
            {/* Sidebar */}
            <aside id="nyalifeSidebar" className={`nyalife-sidebar ${sidebarCollapsed ? 'collapsed' : ''} ${sidebarOpen ? 'open' : ''}`}>
                {/* Sidebar Toggle Button */}
                <button className="sidebar-toggle-btn" id="sidebarToggleBtn" title="Toggle Sidebar" onClick={toggleSidebar}>
                    <i className={`fas fa-chevron-${sidebarCollapsed ? 'right' : 'left'}`}></i>
                </button>
                
                {/* Sidebar Header with Logo */}
                <div className="sidebar-header h-auto">
                    <div className="sidebar-logo h-auto">
                        <img src="/assets/img/logo/Logo2-transparent.png" alt="Nyalife HMS" className="logo-img rounded-2" 
                             onError={(e) => { 
                                 if (e.target) e.target.style.display = 'none'; 
                                 if (e.target?.nextElementSibling) e.target.nextElementSibling.style.display = 'flex'; 
                             }} />
                    </div>
                </div>
                
                {/* Sidebar Menu */}
                <nav className="sidebar-nav h-auto">
                    <ul className="sidebar-menu h-auto">
                        {menuItems.map((item) => {
                            // Normalize item.url to just the path for reliable comparison
                            const itemPath = item.url.startsWith('http') 
                                ? new URL(item.url).pathname 
                                : item.url;
                                
                            const active = currentUrl === itemPath || 
                                         currentUrl.startsWith(itemPath + '/') || 
                                         currentUrl.startsWith(itemPath + '?') ||
                                         (itemPath !== '/' && currentUrl.startsWith(itemPath));

                            return (
                                <li key={item.id} className="sidebar-menu-item h-auto">
                                    <Link 
                                        href={item.url} 
                                        className={`sidebar-link ${active ? 'active' : ''}`}
                                        data-menu-id={item.id}
                                    >
                                        <i className={`${item.icon} sidebar-icon`}></i>
                                        <span className="sidebar-text">{item.text}</span>
                                    </Link>
                                </li>
                            );
                        })}
                    </ul>
                </nav>
            </aside>

            {/* Sidebar Overlay (for mobile) */}
            <div className={`sidebar-overlay ${sidebarOpen ? 'active' : ''}`} id="sidebarOverlay" onClick={toggleMobileSidebar}></div>

            {/* Main Content */}
            <main className="main-content">
                {/* Mobile Sidebar Toggle */}
                <button className="sidebar-toggle-header d-md-none" onClick={toggleMobileSidebar} style={{ left: '15px', top: '15px' }}>
                    <i className="fas fa-bars"></i>
                </button>

                {/* Top Header Bar */}
                <div className="dashboard-header mb-4">
                    <div className="d-flex justify-content-between align-items-center">
                        <div>
                            {header && <div className="page-title mb-0">{header}</div>}
                        </div>
                        <div className="d-flex align-items-center gap-3">
                            {/* Messages Toggle */}
                            <div className="dropdown">
                                <button 
                                    className="btn btn-link position-relative p-1 text-gray-500 hover:text-pink-500 transition-colors dropdown-toggle"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                >
                                    <i className="fas fa-comment-dots fa-lg"></i>
                                    {auth.unread_messages_count > 0 && (
                                        <span className="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style={{ fontSize: '0.6rem' }}>
                                            {auth.unread_messages_count}
                                        </span>
                                    )}
                                </button>
                                <ul className="dropdown-menu dropdown-menu-end p-0 shadow-lg border-0 rounded-2xl overflow-hidden mt-3" style={{ width: '320px' }}>
                                    <li className="px-4 py-3 bg-white border-b border-gray-100 d-flex justify-content-between align-items-center">
                                        <span className="fw-bold text-gray-900">Recent Messages</span>
                                        <Link href="/messages" className="text-xs text-pink-500 hover:underline">View All</Link>
                                    </li>
                                    <li className="p-4 text-center text-muted small">
                                        <i className="fas fa-comments mb-2 d-block fa-2x opacity-20"></i>
                                        {auth.unread_messages_count > 0 ? `You have ${auth.unread_messages_count} unread messages.` : 'No new messages.'}
                                    </li>
                                </ul>
                            </div>

                            {/* Notifications Toggle */}
                            <div className="dropdown">
                                <button 
                                    className="btn btn-link position-relative p-1 text-gray-500 hover:text-pink-500 transition-colors dropdown-toggle"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                >
                                    <i className="fas fa-bell fa-lg"></i>
                                    {auth.unread_notifications_count > 0 && (
                                        <span className="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style={{ fontSize: '0.6rem' }}>
                                            {auth.unread_notifications_count}
                                        </span>
                                    )}
                                </button>
                                <ul className="dropdown-menu dropdown-menu-end p-0 shadow-lg border-0 rounded-2xl overflow-hidden mt-3" style={{ width: '320px' }}>
                                    <li className="px-4 py-3 bg-white border-b border-gray-100 d-flex justify-content-between align-items-center">
                                        <span className="fw-bold text-gray-900">Notifications</span>
                                        <Link href="/notifications" className="text-xs text-pink-500 hover:underline">View All</Link>
                                    </li>
                                    <li className="p-4 text-center text-muted small">
                                        <i className="fas fa-bell mb-2 d-block fa-2x opacity-20"></i>
                                        {auth.unread_notifications_count > 0 ? `You have ${auth.unread_notifications_count} unread notifications.` : 'No new notifications.'}
                                    </li>
                                </ul>
                            </div>

                            <div className="vr mx-2 bg-gray-200" style={{ height: '24px' }}></div>
                            {/* User Info */}
                            <div className="dropdown">
                                <button 
                                    className="btn btn-link text-decoration-none d-flex align-items-center gap-2 dropdown-toggle" 
                                    type="button" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false"
                                >
                                    <div className="avatar-circle" style={{
                                        width: '40px', 
                                        height: '40px', 
                                        borderRadius: '50%', 
                                        background: 'linear-gradient(135deg, #e91e63, #c2185b)',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        color: 'white',
                                        fontWeight: 'bold'
                                    }}>
                                        {displayName.charAt(0).toUpperCase()}
                                    </div>
                                    <div className="d-none d-md-block text-start">
                                        <div className="fw-semibold text-dark">{displayName}</div>
                                        <small className="text-muted text-capitalize">{userRole.replace('_', ' ')}</small>
                                    </div>
                                </button>
                                <ul className="dropdown-menu dropdown-menu-end">
                                    <li><Link className="dropdown-item" href="/profile"><i className="fas fa-user me-2"></i>Profile</Link></li>
                                    <li><hr className="dropdown-divider" /></li>
                                    <li>
                                        <Link 
                                            className="dropdown-item text-danger" 
                                            href={route('logout')} 
                                            method="post" 
                                            as="button"
                                        >
                                            <i className="fas fa-sign-out-alt me-2"></i>Logout
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Page Content */}
                {children}
            </main>
        </div>
    );
}
