import { Link, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import CookieBanner from '@/Components/CookieBanner';
import { Toaster, toast } from 'react-hot-toast';
import ContextSwitcher from '@/Components/ContextSwitcher';
import UserAvatar from '@/Components/UserAvatar';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

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
        { id: 'lab-catalog', text: 'Lab Catalog', url: route('lab.tests'), icon: 'fas fa-vials' },
        { id: 'reports', text: 'Reports', url: '/reports', icon: 'fas fa-chart-bar' },
        { id: 'blog-manage', text: 'Manage Blogs', url: route('blog.manage'), icon: 'fas fa-blog' },
        { id: 'cms-manage', text: 'Landing CMS', url: route('cms.index'), icon: 'fas fa-laptop-code' },
        { id: 'insurance-manage', text: 'Accepted Insurances', url: route('insurances.index'), icon: 'fas fa-shield-alt' },
        { id: 'mail-templates', text: 'Email Templates', url: route('mail-templates.index'), icon: 'fas fa-envelope-open-text' },
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
        { id: 'lab-catalog', text: 'Test Catalog', url: route('lab.tests'), icon: 'fas fa-vial' },
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
        { id: 'insurance-manage', text: 'Health Insurances', url: route('insurances.index'), icon: 'fas fa-shield-alt' },
    ],
    patient: [
        { id: 'dashboard', text: 'Dashboard', url: '/dashboard', icon: 'fas fa-tachometer-alt' },
        { id: 'appointments', text: 'My Appointments', url: '/appointments', icon: 'fas fa-calendar-alt' },
        { id: 'consultations', text: 'My Consultations', url: '/consultations', icon: 'fas fa-stethoscope' },
        { id: 'lab-results', text: 'My Lab Results', url: '/lab/requests', icon: 'fas fa-flask' },
        { id: 'prescriptions', text: 'My Prescriptions', url: '/prescriptions', icon: 'fas fa-prescription' },
        { id: 'billing', text: 'Billing & Invoices', url: '/invoices', icon: 'fas fa-file-invoice-dollar' },
        { id: 'profile', text: 'My Profile', url: '/profile', icon: 'fas fa-user' },
    ],
};

export default function AuthenticatedLayout({ 
    header, 
    children, 
    toolbarActions, 
    toolbarFilters, 
    toolbarBulkActions, 
    selectionCount 
}) {
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
    const [autosaveStatus, setAutosaveStatus] = useState(null); // { status: 'saving' | 'saved', timestamp: number }

    useEffect(() => {
        const handleAutosave = (e) => {
            setAutosaveStatus(e.detail);
            if (e.detail.status === 'saved') {
                setTimeout(() => {
                    setAutosaveStatus(prev => prev?.status === 'saved' ? null : prev);
                }, 3000);
            }
        };
        window.addEventListener('autosave', handleAutosave);
        return () => window.removeEventListener('autosave', handleAutosave);
    }, []);

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
        if (page.props.flash?.success) {
            toast.success(page.props.flash.success);
        }
        if (page.props.flash?.error) {
            toast.error(page.props.flash.error);
        }
    }, [page.props.flash]);

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
            <Toaster position="top-right" reverseOrder={false} />
            {/* Sidebar */}
            <aside id="nyalifeSidebar" className={`nyalife-sidebar ${sidebarCollapsed ? 'collapsed' : ''} ${sidebarOpen ? 'open' : ''}`}>
                {/* Sidebar Toggle Button */}
                <button className="sidebar-toggle-btn" id="sidebarToggleBtn" title="Toggle Sidebar" onClick={toggleSidebar}>
                    <i className={`fas fa-chevron-${sidebarCollapsed ? 'right' : 'left'}`}></i>
                </button>
                
                {/* Sidebar Header with Logo */}
                <div className="sidebar-header h-auto">
                    <div className="sidebar-logo h-auto">
                        <img src="/assets/logo/Logo2-transparent.png" alt="Nyalife HMS" className="logo-img me-2 bg-white rounded-2 p-1" 
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

                            const moduleName = {
                                'appointments': 'appointments',
                                'consultations': 'consultations',
                                'lab-requests': 'lab',
                                'lab-results': 'lab',
                                'prescriptions': 'pharmacy',
                                'invoices': 'billing',
                                'billing': 'billing'
                            }[item.id];
                            
                            const badgeCount = moduleName ? (auth.module_notifications?.[moduleName] || 0) : 0;

                            return (
                                <li key={item.id} className="sidebar-menu-item h-auto">
                                    <Link 
                                        href={item.url} 
                                        className={`sidebar-link ${active ? 'active' : ''} d-flex align-items-center justify-content-between`}
                                        data-menu-id={item.id}
                                    >
                                        <div className="d-flex align-items-center">
                                            <i className={`${item.icon} sidebar-icon`}></i>
                                            <span className="sidebar-text">{item.text}</span>
                                        </div>
                                        {badgeCount > 0 && (
                                            <span className="badge rounded-pill bg-pink-500 text-white ms-auto" style={{ fontSize: '0.65rem', minWidth: '1.2rem', height: '1.2rem', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                                {badgeCount}
                                            </span>
                                        )}
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
                <div className="dashboard-header sticky-header px-4 py-2 mb-4">
                    <div className="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div className="d-none d-md-block">
                            {header && <div className="page-title mb-0 fs-5 fw-bold text-gray-800">{header}</div>}
                        </div>
                        <div className="d-flex align-items-center gap-2 gap-md-3 ms-auto">
                            {/* Autosave Status Indicator */}
                            {autosaveStatus && (
                                <div className={`autosave-indicator animate-in fade-in slide-in-from-right-4 d-flex align-items-center gap-2 px-3 py-1.5 rounded-pill small fw-bold ${autosaveStatus.status === 'saving' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success'}`}>
                                    {autosaveStatus.status === 'saving' ? (
                                        <><i className="fas fa-sync fa-spin"></i> Saving...</>
                                    ) : (
                                        <><i className="fas fa-check-circle"></i> Draft Saved</>
                                    )}
                                </div>
                            )}
                            {/* Messages Toggle */}
                            <div className="dropdown">
                                <button 
                                    className="btn btn-link position-relative p-1 text-gray-500 hover:text-pink-500 transition-colors dropdown-toggle border-0 shadow-none"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                >
                                    <i className="fas fa-comment-dots fa-lg"></i>
                                    {auth.unread_messages_count > 0 && (
                                        <span className="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" style={{ fontSize: '0.6rem' }}>
                                            {auth.unread_messages_count}
                                        </span>
                                    )}
                                </button>
                                <ul className="dropdown-menu dropdown-menu-end p-0 shadow-2xl border-0 rounded-2xl overflow-hidden mt-3 animate-in fade-in zoom-in-95 duration-200" style={{ width: '280px', maxWidth: '90vw' }}>
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
                                    className="btn btn-link position-relative p-1 text-gray-500 hover:text-pink-500 transition-colors dropdown-toggle border-0 shadow-none"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                >
                                    <i className="fas fa-bell fa-lg"></i>
                                    {auth.unread_notifications_count > 0 && (
                                        <span className="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" style={{ fontSize: '0.6rem' }}>
                                            {auth.unread_notifications_count}
                                        </span>
                                    )}
                                </button>
                                <ul className="dropdown-menu dropdown-menu-end p-0 shadow-2xl border-0 rounded-2xl overflow-hidden mt-3 animate-in fade-in zoom-in-95 duration-200" style={{ width: '280px', maxWidth: '90vw' }}>
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

                            <div className="vr mx-1 mx-md-2 bg-gray-200 d-none d-sm-block" style={{ height: '24px' }}></div>
                            {/* User Info */}
                            <div className="dropdown">
                                <button 
                                    className="btn btn-link text-decoration-none d-flex align-items-center gap-2 dropdown-toggle border-0 shadow-none p-1" 
                                    type="button" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false"
                                >
                                    <UserAvatar user={user} size="sm" showStatus={true} />
                                    <div className="d-none d-md-block text-start">
                                        <div className="fw-bold text-gray-900" style={{ fontSize: '0.9rem' }}>{displayName}</div>
                                        <div className="text-muted text-capitalize font-bold extra-small opacity-75">{userRole.replace('_', ' ')}</div>
                                    </div>
                                    <i className="fas fa-chevron-down text-gray-400 ms-1 d-none d-md-block" style={{ fontSize: '0.7rem' }}></i>
                                </button>
                                <ul className="dropdown-menu dropdown-menu-end p-2 shadow-2xl border-0 rounded-2xl mt-3 animate-in fade-in zoom-in-95 duration-200">
                                    <li>
                                        <Link className="dropdown-item py-2 rounded-xl d-flex align-items-center gap-3" href="/profile">
                                            <div className="bg-primary-subtle text-primary rounded-lg p-2 d-flex align-items-center justify-content-center" style={{width: '32px', height: '32px'}}>
                                                <i className="fas fa-user text-xs"></i>
                                            </div>
                                            <span className="fw-bold text-gray-700">My Profile</span>
                                        </Link>
                                    </li>
                                    <li><hr className="dropdown-divider opacity-10 mx-2" /></li>
                                    <li>
                                        <Link 
                                            className="dropdown-item py-2 rounded-xl d-flex align-items-center gap-3 text-danger" 
                                            href={route('logout')} 
                                            method="post" 
                                            as="button"
                                        >
                                            <div className="bg-danger-subtle text-danger rounded-lg p-2 d-flex align-items-center justify-content-center" style={{width: '32px', height: '32px'}}>
                                                <i className="fas fa-sign-out-alt text-xs"></i>
                                            </div>
                                            <span className="fw-bold">Logout System</span>
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Page Content */}
                <div className="content-container">
                    {children}
                </div>
            </main>

            {/* Shared Page Actions Toolbar */}
            {(toolbarActions || toolbarFilters || toolbarBulkActions) && (
                <UnifiedToolbar 
                    actions={toolbarActions}
                    filters={toolbarFilters}
                    bulkActions={toolbarBulkActions}
                    selectionCount={selectionCount}
                />
            )}

            <CookieBanner />
            <ContextSwitcher />
        </div>
    );
}
