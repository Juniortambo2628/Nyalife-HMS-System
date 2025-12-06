/**
 * Nyalife HMS - Shared Dashboard Utilities
 * 
 * Common functionality used across all dashboard types
 * Eliminates ~400 lines of duplicate code
 */

import { formatDistanceToNow } from 'date-fns';
import httpClient from '@common/http';
import { NyalifeUtils } from '@common/utils';
import DOMPurify from 'dompurify';
import '../../css/dashboard.css';

/**
 * Initialize dashboard messages card
 * @param {string} containerId - ID of the messages container element
 * @param {string} apiEndpoint - API endpoint for fetching messages
 * @param {number} limit - Number of messages to fetch
 */
export async function loadDashboardMessages(containerId = 'dashboard-messages-container', apiEndpoint = 'api/messages/inbox', limit = 5) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    try {
        const response = await httpClient.get(`${apiEndpoint}?limit=${limit}&unread_only=false`, {
            headers: { 'X-No-Loader': 'true' } // Disable loader for dashboard
        });
        
        // Handle different API response formats
        let messages = [];
        const data = response.data;
        
        if (data.success && data.data && data.data.messages) {
            messages = data.data.messages;
        } else if (data.messages) {
            messages = data.messages;
        }
        
        renderDashboardMessages(messages, container);
        
    } catch (error) {
        console.error('Error loading dashboard messages:', error);
        showDashboardMessageError(container);
    }
}

/**
 * Render messages in dashboard card
 * @param {Array} messages - Array of message objects
 * @param {HTMLElement} container - Container element
 */
export function renderDashboardMessages(messages, container) {
    if (!container) return;
    
    if (!messages || messages.length === 0) {
        container.innerHTML = `
            <div class="text-center p-4">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No messages yet</p>
                <a href="${window.baseUrl || ''}/messages/compose" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Send your first message
                </a>
            </div>
        `;
        return;
    }
    
    const messageHtml = messages.map(message => createMessageItem(message)).join('');
    container.innerHTML = messageHtml;
}

/**
 * Create individual message item HTML
 * @param {Object} message - Message object
 * @returns {string} HTML string
 */
export function createMessageItem(message) {
    const isUnread = !message.is_read;
    const timeAgo = formatDistanceToNow(new Date(message.created_at), { addSuffix: true });
    const senderName = `${message.sender_first_name} ${message.sender_last_name}`;
    // Sanitize and truncate message
    const cleanMessage = DOMPurify.sanitize(message.message, { ALLOWED_TAGS: [] }); // Strip all tags for preview
    const messagePreview = NyalifeUtils.truncate(cleanMessage, 80);
    const initials = senderName.split(' ').map(n => n.charAt(0)).join('').toUpperCase();
    
    return `
        <div class="message-item ${isUnread ? 'unread' : 'read'} p-3 border-bottom" style="cursor: pointer;" onclick="window.location.href='${window.baseUrl || ''}/messages/${message.message_id}'">
            <div class="d-flex align-items-center">
                <div class="message-avatar me-3">
                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; font-size: 14px;">
                        ${initials}
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div class="message-sender ${isUnread ? 'fw-bold text-dark' : 'text-muted'} small">
                            ${senderName}
                        </div>
                        <div class="message-time small text-muted">${timeAgo}</div>
                    </div>
                    <div class="message-subject ${isUnread ? 'fw-semibold text-dark' : 'text-muted'} small mb-1" style="line-height: 1.2;">
                        ${message.subject}
                    </div>
                    <div class="message-preview small text-muted" style="line-height: 1.2;">
                        ${messagePreview}
                    </div>
                </div>
                ${isUnread ? '<div class="message-indicator bg-primary rounded-circle" style="width: 8px; height: 8px;"></div>' : ''}
            </div>
        </div>
    `;
}

/**
 * Show error message in dashboard messages container
 * @param {HTMLElement} container - Container element
 */
export function showDashboardMessageError(container) {
    if (!container) return;
    
    container.innerHTML = `
        <div class="text-center p-4 text-danger">
            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
            <p>Unable to load messages</p>
            <button class="btn btn-sm btn-outline-primary" onclick="loadDashboardMessages('${container.id}')">
                <i class="fas fa-redo me-1"></i>Retry
            </button>
        </div>
    `;
}

/**
 * Setup AJAX navigation for dashboard links
 * @param {string} containerSelector - Selector for the container with links
 */
export function setupAjaxNavigation(containerSelector = '#main-content') {
    if (typeof Components === 'undefined') return;
    
    const container = document.querySelector(containerSelector);
    if (!container) return;
    
    const baseUrl = window.baseUrl || '';
    const links = container.querySelectorAll(`a[href^="${baseUrl}"]`);
    
    links.forEach(link => {
        // Skip links that should not use AJAX
        if (link.hasAttribute('data-no-ajax') ||
            link.getAttribute('href').includes('#') ||
            link.getAttribute('href').endsWith('.pdf') ||
            link.getAttribute('href').endsWith('.doc') ||
            link.getAttribute('href').endsWith('.docx')) {
            return;
        }
        
        link.addEventListener('click', function(e) {
            e.preventDefault();
            Components.loadPage(this.href);
        });
    });
}

// Make functions available globally for backward compatibility
if (typeof window !== 'undefined') {
    window.loadDashboardMessages = loadDashboardMessages;
    window.renderDashboardMessages = renderDashboardMessages;
    window.showDashboardMessageError = showDashboardMessageError;
}

// Global image error handler
document.addEventListener('error', function(e) {
    if (e.target.tagName === 'IMG' && e.target.classList.contains('img-error-handler')) {
        e.target.style.display = 'none';
        const iconClass = e.target.getAttribute('data-error-icon') || 'fas fa-image';
        
        const placeholder = document.createElement('div');
        placeholder.className = 'text-center mb-3 error-placeholder-icon';
        placeholder.innerHTML = `<i class="${iconClass} fa-4x text-muted"></i>`;
        
        e.target.parentNode.insertBefore(placeholder, e.target);
    }
}, true);
