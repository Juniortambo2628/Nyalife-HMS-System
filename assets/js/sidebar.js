/**
 * Nyalife HMS - Sidebar JavaScript
 * Handles sidebar toggle, badge updates, and responsive behavior
 */

class NyalifeSidebar {
    constructor() {
        this.sidebar = document.getElementById('nyalifeSidebar');
        this.toggleBtn = document.getElementById('sidebarToggleBtn');
        this.headerToggleBtn = document.getElementById('sidebarToggleHeader');
        this.overlay = document.getElementById('sidebarOverlay');
        this.isCollapsed = this.getCollapseState();
        this.init();
    }
    
    /**
     * Initialize the sidebar
     */
    init() {
        if (!this.sidebar) return;
        
        // Set initial state
        if (this.isCollapsed) {
            this.collapse();
        } else {
            this.expand();
        }
        
        // Bind events for sidebar toggle button
        if (this.toggleBtn) {
            this.toggleBtn.addEventListener('click', () => this.toggle());
        }
        
        // Bind events for header toggle button
        if (this.headerToggleBtn) {
            this.headerToggleBtn.addEventListener('click', () => this.toggle());
        }
        
        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.collapse());
        }
        
        // Handle responsive behavior
        this.handleResponsive();
        window.addEventListener('resize', () => this.handleResponsive());
        
        // Update badges on load
        this.updateBadges();
        
        // Setup badge update interval
        setInterval(() => this.updateBadges(), 30000); // Update every 30 seconds
    }
    
    /**
     * Toggle sidebar collapse state
     */
    toggle() {
        if (this.isCollapsed) {
            this.expand();
        } else {
            this.collapse();
        }
    }
    
    /**
     * Collapse the sidebar
     */
    collapse() {
        this.sidebar.classList.add('collapsed');
        this.isCollapsed = true;
        this.saveCollapseState();
        this.updateMainContent();
    }
    
    /**
     * Expand the sidebar
     */
    expand() {
        this.sidebar.classList.remove('collapsed');
        this.isCollapsed = false;
        this.saveCollapseState();
        this.updateMainContent();
    }
    
    /**
     * Update main content wrapper
     */
    updateMainContent() {
        const wrapper = document.querySelector('.main-content-wrapper');
        if (wrapper) {
            if (this.isCollapsed) {
                wrapper.style.marginLeft = '0';
            } else {
                wrapper.style.marginLeft = '260px';
            }
        }
    }
    
    /**
     * Save collapse state to localStorage
     */
    saveCollapseState() {
        try {
            localStorage.setItem('nyalifeSidebarCollapsed', this.isCollapsed ? 'true' : 'false');
        } catch (e) {
            // eslint-disable-next-line no-console
            console.error('Error saving sidebar state:', e);
        }
    }
    
    /**
     * Get collapse state from localStorage
     */
    getCollapseState() {
        try {
            return localStorage.getItem('nyalifeSidebarCollapsed') === 'true';
        } catch (e) {
            return false; // Default to expanded
        }
    }
    
    /**
     * Handle responsive behavior
     */
    handleResponsive() {
        if (window.innerWidth <= 768) {
            // On mobile, close sidebar and show overlay when open
            if (!this.isCollapsed && this.overlay) {
                this.overlay.classList.add('active');
            }
        } else {
            // On desktop, hide overlay
            if (this.overlay) {
                this.overlay.classList.remove('active');
            }
        }
    }
    
    /**
     * Update notification badges
     */
    async updateBadges() {
        try {
            // Update appointments badge
            const appointmentsBadge = document.getElementById('badge-appointments');
            if (appointmentsBadge) {
                const count = await this.getUnreadAppointmentsCount();
                this.updateBadge(appointmentsBadge, count);
            }
            
            // Update consultations badge
            const consultationsBadge = document.getElementById('badge-consultations');
            if (consultationsBadge) {
                const count = await this.getUnreadConsultationsCount();
                this.updateBadge(consultationsBadge, count);
            }
        } catch (error) {
            // eslint-disable-next-line no-console
            console.error('Error updating badges:', error);
        }
    }
    
    /**
     * Get unread appointments count
     */
    async getUnreadAppointmentsCount() {
        try {
            const response = await fetch(`${window.baseUrl || ''}/api/appointments/pending-count`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-No-Loader': 'true'
                }
            });
            
            if (response.ok) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    return data.count || data.data?.count || 0;
                } else {
                    // If not JSON, return 0 silently
                    // eslint-disable-next-line no-console
                    console.warn('Appointments count endpoint returned non-JSON response');
                    return 0;
                }
            }
        } catch (error) {
            // Silently fail - don't spam console for missing endpoints
            if (error.message && !error.message.includes('JSON')) {
                // eslint-disable-next-line no-console
                console.error('Error fetching appointments count:', error);
            }
        }
        return 0;
    }
    
    /**
     * Get unread consultations count
     */
    async getUnreadConsultationsCount() {
        try {
            const response = await fetch(`${window.baseUrl || ''}/api/consultations/pending-count`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-No-Loader': 'true'
                }
            });
            
            if (response.ok) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    return data.count || data.data?.count || 0;
                } else {
                    // If not JSON, return 0 silently
                    // eslint-disable-next-line no-console
                    console.warn('Consultations count endpoint returned non-JSON response');
                    return 0;
                }
            }
        } catch (error) {
            // Silently fail - don't spam console for missing endpoints
            if (error.message && !error.message.includes('JSON')) {
                // eslint-disable-next-line no-console
                console.error('Error fetching consultations count:', error);
            }
        }
        return 0;
    }
    
    /**
     * Update a badge element
     */
    updateBadge(badge, count) {
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'inline-flex';
                badge.classList.add('updated');
                setTimeout(() => badge.classList.remove('updated'), 500);
            } else {
                badge.textContent = '0';
                badge.style.display = 'none';
            }
        }
    }
    
    /**
     * Show breadcrumb based on current path
     */
    static generateBreadcrumb() {
        const path = window.location.pathname;
        const segments = path.split('/').filter(Boolean);
        
        // Skip base path if present
        if (segments[0] === 'Nyalife-HMS-System') {
            segments.shift();
        }
        
        // Generate breadcrumb HTML
        let breadcrumb = '<ol class="breadcrumb"><li class="breadcrumb-item"><a href="' + (window.baseUrl || '') + '/dashboard"><i class="fas fa-home"></i> Home</a></li>';
        
        let currentPath = '';
        segments.forEach((segment, index) => {
            currentPath += '/' + segment;
            
            // Format segment name
            const segmentName = segment
                .split('-')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
            
            if (index === segments.length - 1) {
                // Last item - active
                breadcrumb += '<li class="breadcrumb-item active">' + segmentName + '</li>';
            } else {
                // Link item
                breadcrumb += '<li class="breadcrumb-item"><a href="' + (window.baseUrl || '') + currentPath + '">' + segmentName + '</a></li>';
            }
        });
        
        breadcrumb += '</ol>';
        
        // Insert breadcrumb
        const breadcrumbContainer = document.createElement('div');
        breadcrumbContainer.className = 'breadcrumb-container';
        breadcrumbContainer.innerHTML = breadcrumb;
        
        const mainContent = document.querySelector('.main-content-wrapper');
        if (mainContent && !mainContent.querySelector('.breadcrumb-container')) {
            mainContent.insertBefore(breadcrumbContainer, mainContent.firstChild);
        }
    }
}

// Initialize sidebar when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.nyalifeSidebar = new NyalifeSidebar();
    NyalifeSidebar.generateBreadcrumb();
});

// Generate breadcrumb on page navigation
window.addEventListener('load', () => {
    NyalifeSidebar.generateBreadcrumb();
});

