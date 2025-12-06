/**
 * Nyalife HMS - Login Page JavaScript
 * 
 * Extracted from includes/views/auth/login.php
 * Handles login form submission and AJAX navigation
 */

import httpClient from '@common/http';
import { setupAjaxNavigation } from '@shared/dashboard-utils';

/**
 * Initialize login form
 */
function initLoginForm() {
    console.log('Initializing login form...');
    
    const loginForm = document.getElementById('loginForm');
    const loginSpinner = document.getElementById('loginSpinner');
    const loginAlert = document.getElementById('loginAlert');
    
    if (!loginForm) {
        console.warn('Login form not found');
        return;
    }
    
    // Attach submit handler
    loginForm.addEventListener('submit', async function(e) {
        // Only use AJAX if Components is available
        if (typeof Components === 'undefined') {
            return; // Let form submit normally
        }
        
        e.preventDefault();
        
        // Show spinner
        if (loginSpinner) {
            loginSpinner.classList.remove('d-none');
        }
        
        // Disable submit button
        const submitBtn = loginForm.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
        }
        
        // Clear previous alerts
        if (loginAlert) {
            loginAlert.style.display = 'none';
            loginAlert.innerHTML = '';
        }
        
        try {
            // Get form data
            const formData = new FormData(loginForm);
            
            // Submit via axios
            const response = await httpClient.post(loginForm.action, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
            
            const data = response.data;
            
            if (data.success) {
                // Success - redirect
                let redirectUrl = data.redirect || `${window.baseUrl || ''}/dashboard`;
                
                // Ensure URL is properly formatted
                if (!redirectUrl.match(/^https?:\/\//i) && !redirectUrl.startsWith('/')) {
                    redirectUrl = '/' + redirectUrl;
                }
                
                // Use AJAX navigation if available, otherwise normal navigation
                if (typeof Components !== 'undefined') {
                    Components.loadPage(redirectUrl);
                } else {
                    window.location.href = redirectUrl;
                }
            } else {
                // Show error
                showLoginError(data.message || 'Invalid username or password');
            }
        } catch (error) {
            console.error('Login error:', error);
            
            // Extract error message from response if available
            let errorMessage = 'An error occurred while processing your request. Please try again.';
            if (error.response && error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            }
            
            showLoginError(errorMessage);
        } finally {
            // Hide spinner
            if (loginSpinner) {
                loginSpinner.classList.add('d-none');
            }
            
            // Re-enable submit button
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        }
    });
    
    // Setup AJAX navigation for other links on the page
    setupAjaxNavigation();
}

/**
 * Show login error message
 * @param {string} message - Error message to display
 */
function showLoginError(message) {
    const loginAlert = document.getElementById('loginAlert');
    if (loginAlert) {
        loginAlert.style.display = 'block';
        loginAlert.className = 'alert alert-danger alert-dismissible fade show';
        loginAlert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
    }
}

// Initialize on DOM ready and page loaded events
document.addEventListener('DOMContentLoaded', initLoginForm);
document.addEventListener('page:loaded', initLoginForm);

// Export for potential use by other modules
export { initLoginForm };
