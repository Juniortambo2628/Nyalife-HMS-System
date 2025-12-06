/**
 * Nyalife HMS - Messages JavaScript
 * 
 * JavaScript for handling message alerts and dropdown in the navbar
 */

class MessageManager {
    constructor() {
        this.baseUrl = window.baseUrl || '';
        this.pollingInterval = 30000; // Poll every 30 seconds for real-time updates
        this.pollingTimer = null;
        this.initialized = false;
        this.lastUnreadCount = 0;
        
        this.init();
    }
    
    /**
     * Initialize message system
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
        // No dropdown events needed - direct navigation to messages page
    }
    
    /**
     * Load recent messages from server
     */
    async loadMessages() {
        try {
            const response = await fetch(`${this.baseUrl}/api/messages/inbox?limit=5&unread_only=false`, {
                headers: {
                    'X-No-Loader': 'true',  // Disable loader for dropdown
                    'X-Requested-With': 'dropdown',  // Additional dropdown context
                    'Content-Type': 'application/json'
                },
                noLoader: true  // Additional bypass flag
            });
            const data = await response.json();
            
            // Handle both old and new API response formats
            let messages = [];
            if (data.success && data.data && data.data.messages) {
                // New format from ApiController
                messages = data.data.messages;
            } else if (data.messages) {
                // Old format
                messages = data.messages;
            }
            
            this.renderMessages(messages);
            
        } catch (error) {
            console.error('Error loading messages:', error);
            this.showMessageError();
        }
    }
    
    /**
     * Update unread message count
     */
    async updateUnreadCount() {
        try {
            const response = await fetch(`${this.baseUrl}/api/messages/inbox?limit=1&unread_only=true`, {
                headers: {
                    'X-No-Loader': 'true',  // Disable loader for count updates
                    'X-Requested-With': 'dropdown',  // Additional dropdown context
                    'Content-Type': 'application/json'
                },
                noLoader: true  // Additional bypass flag
            });
            const data = await response.json();
            
            let unreadCount = 0;
            let stats = null;
            
            // Handle both old and new API response formats
            if (data.success && data.data) {
                stats = data.data.stats;
                // Also count from messages if stats not available
                if (!stats && data.data.messages) {
                    unreadCount = data.data.messages.filter(msg => !msg.is_read).length;
                }
            } else if (data.stats) {
                stats = data.stats;
            } else if (data.messages) {
                // Fallback: count unread messages from the response
                unreadCount = data.messages.filter(msg => !msg.is_read).length;
            }
            
            if (stats) {
                unreadCount = stats.unread || 0;
            }
            
            // Check for new messages and show notification
            if (unreadCount > this.lastUnreadCount && this.lastUnreadCount > 0) {
                this.showNewMessageToast();
            }
            
            this.lastUnreadCount = unreadCount;
            this.updateBadge(unreadCount);
        } catch (error) {
            console.error('Error updating message count:', error);
        }
    }
    
    /**
     * Render messages in dropdown
     */
    renderMessages(messages) {
        const container = document.getElementById('messages-list');
        if (!container) return;
        
        if (!messages || messages.length === 0) {
            container.innerHTML = `
                <div class="text-center p-3 text-muted">
                    <i class="fas fa-inbox mb-2"></i>
                    <div>No messages</div>
                </div>
            `;
            return;
        }
        
        const messageHtml = messages.map(message => {
            const isUnread = !message.is_read;
            const timeAgo = this.formatTimeAgo(message.created_at);
            const senderName = `${message.sender_first_name} ${message.sender_last_name}`;
            const messagePreview = this.truncateText(message.message, 50);
            
            return `
                <div class="message-item ${isUnread ? 'unread' : 'read'}" data-id="${message.message_id}" style="cursor: pointer;">
                    <div class="d-flex p-3 border-bottom">
                        <div class="message-avatar me-3">
                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; font-size: 14px;">
                                ${senderName.split(' ').map(n => n.charAt(0)).join('').toUpperCase()}
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="message-sender fw-bold ${isUnread ? 'text-dark' : 'text-muted'} small">
                                    ${senderName}
                                </div>
                                <div class="message-time small text-muted">${timeAgo}</div>
                            </div>
                            <div class="message-subject ${isUnread ? 'text-dark fw-semibold' : 'text-muted'} small" style="line-height: 1.2;">
                                ${this.truncateText(message.subject, 30)}
                            </div>
                            <div class="message-preview small ${isUnread ? 'text-secondary' : 'text-muted'}" style="line-height: 1.2;">
                                ${messagePreview}
                            </div>
                        </div>
                        ${isUnread ? '<div class="message-indicator bg-info rounded-circle" style="width: 6px; height: 6px; margin-top: 8px;"></div>' : ''}
                    </div>
                </div>
            `;
        }).join('');
        
        container.innerHTML = messageHtml;
    }
    
    /**
     * Update message badge count
     */
    updateBadge(count) {
        const badge = document.getElementById('messages-count');
        const toggle = document.getElementById('messagesToggle');
        if (!badge || !toggle) return;
        
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'block';
            toggle.classList.add('has-messages');
        } else {
            badge.style.display = 'none';
            toggle.classList.remove('has-messages');
        }
    }
    
    /**
     * Start polling for new messages
     */
    startPolling() {
        this.pollingTimer = setInterval(() => {
            this.updateUnreadCount();
            this.updateDashboardMessages();
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
     * Show message error
     */
    showMessageError() {
        const container = document.getElementById('messages-list');
        if (container) {
            container.innerHTML = `
                <div class="text-center p-3 text-danger">
                    <i class="fas fa-exclamation-circle mb-2"></i>
                    <div>Error loading messages</div>
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
     * Update dashboard messages if present
     */
    updateDashboardMessages() {
        // Check if we're on a dashboard page and the function exists
        if (typeof loadDashboardMessages === 'function') {
            loadDashboardMessages();
        }
    }
    
    /**
     * Show toast notification for new messages
     */
    showNewMessageToast() {
        if (typeof window.showToast === 'function') {
            window.showToast('New message received!', 'info', 3000);
        } else {
            // Fallback: simple browser notification if available
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('Nyalife HMS', {
                    body: 'You have received a new message',
                    icon: `${this.baseUrl}/assets/img/favicon.png`,
                    tag: 'new-message'
                });
            }
        }
    }
    
    /**
     * Refresh both dropdown and dashboard
     */
    refresh() {
        this.loadMessages();
        this.updateUnreadCount();
        this.updateDashboardMessages();
    }
    
    /**
     * Destroy message manager
     */
    destroy() {
        this.stopPolling();
        this.initialized = false;
    }
}

// Initialize message manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Only initialize if user is logged in and messages container exists
    if (window.isLoggedIn !== false && document.getElementById('messages-container')) {
        window.messageManager = new MessageManager();
    }
});

// Clean up when page is unloaded
window.addEventListener('beforeunload', () => {
    if (window.messageManager) {
        window.messageManager.destroy();
    }
});
