/**
 * Nyalife HMS - Notifications JavaScript
 * 
 * JavaScript for handling notification alerts and badges in the static header dropdown
 */

class NotificationManager {
    constructor() {
        this.baseUrl = window.baseUrl || '';
        this.pollingInterval = 30000; // Poll every 30 seconds
        this.pollingTimer = null;
        this.initialized = false;
        
        this.init();
    }
    
    /**
     * Initialize notification system
     */
    init() {
        if (this.initialized) return;
        
        this.bindEvents();
        this.updateUnreadCount();
        this.startPolling();
        
        this.initialized = true;
    }
    
    /**
     * Bind event handlers
     */
    bindEvents() {
        // No dropdown events needed - direct navigation to notifications page
    }
    
    /**
     * Load recent notifications from server
     */
    async loadNotifications() {
        try {
            const response = await fetch(`${this.baseUrl}/api/notifications?limit=5&unread_only=false`, {
                headers: {
                    'X-No-Loader': 'true',  // Disable loader for dropdown
                    'X-Requested-With': 'dropdown',  // Additional dropdown context
                    'Content-Type': 'application/json'
                },
                noLoader: true  // Additional bypass flag
            });
            const data = await response.json();
            
            // Handle both old and new API response formats
            let notifications = [];
            if (data.success && data.data) {
                notifications = data.data || [];
            } else if (data.notifications) {
                notifications = data.notifications;
            }
            
            this.renderNotifications(notifications);
            
        } catch (error) {
            // eslint-disable-next-line no-console
            console.error('Error loading notifications:', error);
            this.showNotificationError();
        }
    }
    
    /**
     * Render notifications in dropdown
     */
    renderNotifications(notifications) {
        const container = document.getElementById('notifications-list');
        if (!container) return;
        
        if (!notifications || notifications.length === 0) {
            container.innerHTML = `
                <div class="text-center p-3 text-muted">
                    <i class="fas fa-bell-slash mb-2"></i>
                    <div>No notifications</div>
                </div>
            `;
            return;
        }
        
        const notificationHtml = notifications.map(notification => {
            const isUnread = !notification.is_read;
            const timeAgo = this.formatTimeAgo(notification.created_at);
            const title = notification.title || 'Notification';
            const message = this.truncateText(notification.message, 60);
            
            return `
                <div class="notification-item ${isUnread ? 'unread' : 'read'}" style="cursor: pointer;">
                    <div class="d-flex align-items-start">
                        <div class="notification-avatar me-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; font-size: 14px;">
                                <i class="fas fa-bell"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div class="notification-title ${isUnread ? 'text-dark fw-semibold' : 'text-muted'}">
                                    ${title}
                                </div>
                                <div class="notification-time small text-muted">${timeAgo}</div>
                            </div>
                            <div class="notification-message small ${isUnread ? 'text-secondary' : 'text-muted'}">
                                ${message}
                            </div>
                        </div>
                        ${isUnread ? '<div class="notification-indicator bg-danger rounded-circle ms-2" style="width: 6px; height: 6px; margin-top: 8px;"></div>' : ''}
                    </div>
                </div>
            `;
        }).join('');
        
        container.innerHTML = notificationHtml;
    }
    
    /**
     * Update unread notification count
     */
    async updateUnreadCount() {
        try {
            const response = await fetch(`${this.baseUrl}/api/notifications/count`, {
                headers: {
                    'X-No-Loader': 'true',  // Disable loader for count updates
                    'X-Requested-With': 'dropdown',  // Additional dropdown context
                    'Content-Type': 'application/json'
                },
                noLoader: true  // Additional bypass flag
            });
            const data = await response.json();
            
            let unreadCount = 0;
            if (data.success && data.data) {
                unreadCount = data.data.count || 0;
            } else if (data.count !== undefined) {
                unreadCount = data.count;
            }
            
            this.updateBadge(unreadCount);
        } catch (error) {
            // eslint-disable-next-line no-console
            console.error('Error updating notification count:', error);
        }
    }
    
    /**
     * Update notification badge count
     */
    updateBadge(count) {
        const badge = document.getElementById('notification-count');
        const toggle = document.getElementById('notificationsToggle');
        if (!badge || !toggle) return;
        
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'block';
            toggle.classList.add('has-notifications');
        } else {
            badge.style.display = 'none';
            toggle.classList.remove('has-notifications');
        }
    }
    
    /**
     * Mark all notifications as read
     */
    async markAllAsRead() {
        try {
            await fetch(`${this.baseUrl}/api/notifications/mark-all-read`, {
                method: 'PUT',
                headers: {
                    'X-No-Loader': 'true',  // Disable loader for bulk actions
                    'X-Requested-With': 'dropdown',  // Additional dropdown context
                    'Content-Type': 'application/json'
                },
                noLoader: true  // Additional bypass flag
            });
            
            this.loadNotifications();
            this.updateUnreadCount();
        } catch (error) {
            // eslint-disable-next-line no-console
            console.error('Error marking all as read:', error);
        }
    }
    
    /**
     * Start polling for new notifications
     */
    startPolling() {
        this.pollingTimer = setInterval(() => {
            this.updateUnreadCount();
        }, this.pollingInterval);
    }
    
    /**
     * Stop polling
     */
    stopPolling() {
        if (this.pollingTimer) {
            clearInterval(this.pollingTimer);
            this.pollingTimer = null;
        }
    }
    
    /**
     * Show notification error
     */
    showNotificationError() {
        const container = document.getElementById('notifications-list');
        if (container) {
            container.innerHTML = `
                <div class="text-center p-3 text-danger">
                    <i class="fas fa-exclamation-circle mb-2"></i>
                    <div>Error loading notifications</div>
                </div>
            `;
        }
    }
    
    /**
     * Truncate text to specified length
     */
    truncateText(text, maxLength) {
        if (!text) return '';
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }
    
    /**
     * Format time ago
     */
    formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);
        
        const intervals = [
            { label: 'year', seconds: 31536000 },
            { label: 'month', seconds: 2592000 },
            { label: 'week', seconds: 604800 },
            { label: 'day', seconds: 86400 },
            { label: 'hour', seconds: 3600 },
            { label: 'minute', seconds: 60 }
        ];
        
        for (const interval of intervals) {
            const count = Math.floor(seconds / interval.seconds);
            if (count >= 1) {
                return `${count}${interval.label.charAt(0)}`;
            }
        }
        
        return 'now';
    }
    
    /**
     * Destroy notification manager
     */
    destroy() {
        this.stopPolling();
        this.initialized = false;
    }
}

// Initialize notification manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Only initialize if user is logged in and notifications container exists
    // AND notification manager hasn't been initialized yet
    if (window.isLoggedIn !== false && 
        document.getElementById('notifications-container') && 
        !window.notificationManager) {
        window.notificationManager = new NotificationManager();
    }
});

// Clean up when page is unloaded
window.addEventListener('beforeunload', () => {
    if (window.notificationManager) {
        window.notificationManager.destroy();
    }
});
