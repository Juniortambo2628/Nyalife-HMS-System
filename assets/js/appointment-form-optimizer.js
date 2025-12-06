/**
 * Optimized Appointment Form Handler
 * Handles form submission with better performance, validation, and UX
 */

class AppointmentFormOptimizer {
    constructor(formId, options = {}) {
        this.form = document.getElementById(formId);
        // Resolve base URL from meta tag to support subdirectory deployments
        const metaBase = document.querySelector('meta[name="base-url"]');
        this.baseUrl = metaBase ? metaBase.getAttribute('content') : '';
        this.options = {
            timeout: 30000, // 30 seconds timeout
            retryAttempts: 2,
            progressSteps: [
                'Validating information...',
                'Creating patient record...',
                'Scheduling appointment...',
                'Finalizing booking...'
            ],
            ...options
        };
        
        this.currentStep = 0;
        this.retryCount = 0;
        this.abortController = null;
        
        this.init();
    }
    
    init() {
        if (!this.form) {
            console.error('Form not found');
            return;
        }
        
        this.setupElements();
        this.setupEventListeners();
        this.setupValidation();
    }
    
    setupElements() {
        this.submitBtn = this.form.querySelector('[type="submit"]');
        this.spinner = this.form.querySelector('.spinner-border') || this.createSpinner();
        this.alertDiv = document.getElementById('guestAppointmentAlert') || this.createAlert();
        this.progressDiv = this.createProgressIndicator();
        
        // Insert progress indicator after submit button
        if (this.submitBtn.parentNode) {
            this.submitBtn.parentNode.insertBefore(this.progressDiv, this.submitBtn.nextSibling);
        }
    }
    
    createSpinner() {
        const spinner = document.createElement('div');
        spinner.className = 'spinner-border text-primary d-none mt-2 mx-auto';
        spinner.setAttribute('role', 'status');
        spinner.innerHTML = '<span class="visually-hidden">Loading...</span>';
        return spinner;
    }
    
    createAlert() {
        const alert = document.createElement('div');
        alert.id = 'guestAppointmentAlert';
        alert.className = 'alert';
        alert.setAttribute('role', 'alert');
        alert.style.display = 'none';
        this.form.insertBefore(alert, this.form.firstChild);
        return alert;
    }
    
    createProgressIndicator() {
        const progressDiv = document.createElement('div');
        progressDiv.className = 'appointment-progress d-none mt-3';
        progressDiv.innerHTML = `
            <div class="progress mb-2" style="height: 8px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                     role="progressbar" style="width: 0%"></div>
            </div>
            <small class="text-muted progress-text">Preparing...</small>
        `;
        return progressDiv;
    }
    
    setupEventListeners() {
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Add real-time validation
        const inputs = this.form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.clearFieldError(input));
        });

        // Live time validation against business rules & conflicts
        const dateEl = this.form.querySelector('#appointment_date');
        const timeEl = this.form.querySelector('#appointment_time');
        const doctorEl = this.form.querySelector('#preferred_doctor, #doctor_id');
        const debounced = this.debounce(() => this.validateAppointmentTime(false), 250);
        if (dateEl) {
            dateEl.addEventListener('change', () => { debounced(); this.fetchAvailableDoctors(); });
            dateEl.addEventListener('input', () => { debounced(); this.fetchAvailableDoctors(); });
        }
        if (timeEl) {
            timeEl.addEventListener('change', () => { debounced(); this.fetchAvailableDoctors(); });
            timeEl.addEventListener('input', () => { debounced(); this.fetchAvailableDoctors(); });
        }
        if (doctorEl) {
            doctorEl.addEventListener('change', debounced);
            doctorEl.addEventListener('input', debounced);
        }
    }
    
    setupValidation() {
        // Set minimum date to today
        const dateInput = this.form.querySelector('#appointment_date');
        if (dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('min', today);
        }
        
        // Email validation
        const emailInput = this.form.querySelector('#guest_email');
        if (emailInput) {
            emailInput.addEventListener('input', this.debounce(() => {
                this.validateEmail(emailInput.value);
            }, 500));
        }
    }

    async fetchAvailableDoctors() {
        const dateEl = this.form.querySelector('#appointment_date');
        const timeEl = this.form.querySelector('#appointment_time');
        const doctorSelect = this.form.querySelector('#preferred_doctor, #doctor_id');
        if (!dateEl || !timeEl || !doctorSelect) return;
        const date = dateEl.value;
        const time = timeEl.value;
        if (!date || !time) return;
        try {
            const res = await fetch(`${this.baseUrl}/api/available-doctors?date=${encodeURIComponent(date)}&time=${encodeURIComponent(time)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const out = await res.json();
            if (!out.success) return;
            // Preserve current selection if still available
            const current = doctorSelect.value;
            // Clear and repopulate options
            while (doctorSelect.firstChild) doctorSelect.removeChild(doctorSelect.firstChild);
            const anyOpt = document.createElement('option');
            anyOpt.value = '';
            anyOpt.textContent = 'Any Available Doctor';
            doctorSelect.appendChild(anyOpt);
            (out.doctors || []).forEach(d => {
                const opt = document.createElement('option');
                opt.value = String(d.user_id);
                opt.textContent = `Dr. ${d.name}` + (d.specialization ? ` (${d.specialization})` : '');
                doctorSelect.appendChild(opt);
            });
            // Restore selection if present
            if (current && [...doctorSelect.options].some(o => o.value === current)) {
                doctorSelect.value = current;
            }
        } catch(e) {
            // non-blocking
        }
    }

    // Validate selected appointment time against server business hours
    // showAlert: whether to show global alert, otherwise mark field invalid inline
    async validateAppointmentTime(showAlert = true) {
        const dateEl = this.form.querySelector('#appointment_date');
        const timeEl = this.form.querySelector('#appointment_time');
        const doctorEl = this.form.querySelector('#preferred_doctor, #doctor_id');
        const date = dateEl ? dateEl.value : '';
        const time = timeEl ? timeEl.value : '';
        const doctor_id = doctorEl && doctorEl.value ? doctorEl.value : undefined;
        if (!date || !time) return true;
        try {
            const res = await fetch(`${this.baseUrl}/api/validate-appointment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ date, time, doctor_id })
            });
            const out = await res.json();
            if (!out.success || out.available === false) {
                const message = out.message || 'Selected time is not available';
                if (showAlert) {
                    this.handleError(message);
                } else if (timeEl) {
                    this.showFieldError(timeEl, message);
                }
                return false;
            }
        } catch (e) {
            // ignore network errors; server will re-validate on submit
        }
        return true;
    }
    
    async handleSubmit(e) {
        e.preventDefault();
        
        // Prevent double submission
        if (this.submitBtn.disabled) {
            return;
        }
        
        try {
            // Client-side validation
            if (!this.validateForm()) {
                return;
            }
            
            // Validate appointment time with server-side rules
            const okTime = await this.validateAppointmentTime(true);
            if (!okTime) { return; }

            // Start submission process
            this.startSubmission();
            
            // Submit with timeout and retry logic
            const result = await this.submitWithRetry();
            
            if (result.success) {
                this.handleSuccess(result);
            } else {
                this.handleError(result.message || 'Submission failed');
            }
            
        } catch (error) {
            console.error('Submission error:', error);
            this.handleError(error.message || 'An unexpected error occurred');
        } finally {
            this.endSubmission();
        }
    }
    
    validateForm() {
        let isValid = true;
        const requiredFields = this.form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        // Custom validations
        const appointmentDate = this.form.querySelector('#appointment_date');
        const appointmentTime = this.form.querySelector('#appointment_time');
        
        if (appointmentDate && appointmentTime) {
            const appointmentDateTime = new Date(`${appointmentDate.value}T${appointmentTime.value}`);
            const now = new Date();
            
            if (appointmentDateTime <= now) {
                this.showFieldError(appointmentDate, 'Appointment must be in the future');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    validateField(field) {
        this.clearFieldError(field);
        
        if (field.hasAttribute('required') && !field.value.trim()) {
            this.showFieldError(field, 'This field is required');
            return false;
        }
        
        if (field.type === 'email' && field.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                this.showFieldError(field, 'Please enter a valid email address');
                return false;
            }
        }
        
        if (field.type === 'tel' && field.value) {
            const phoneRegex = /^[\+]?[\d\s\-\(\)]{10,}$/;
            if (!phoneRegex.test(field.value)) {
                this.showFieldError(field, 'Please enter a valid phone number');
                return false;
            }
        }
        
        return true;
    }
    
    showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        let feedback = field.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentNode.appendChild(feedback);
        }
        feedback.textContent = message;
    }
    
    clearFieldError(field) {
        field.classList.remove('is-invalid');
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    }
    
    async validateEmail(email) {
        if (!email || !email.includes('@')) return;
        
        try {
            const response = await fetch(`${this.baseUrl}/api/validate-email`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ email })
            });
            
            const result = await response.json();
            const emailInput = this.form.querySelector('#guest_email');
            
            if (!result.available) {
                this.showFieldError(emailInput, 'Email already registered. Please login or use different email.');
            }
        } catch (error) {
            // Silently fail - don't block form submission
            console.warn('Email validation failed:', error);
        }
    }
    
    startSubmission() {
        this.submitBtn.disabled = true;
        this.submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        this.progressDiv.classList.remove('d-none');
        this.currentStep = 0;
        this.updateProgress();
        
        // Create and show viewport-centered loading overlay
        this.showLoadingOverlay();
    }
    
    endSubmission() {
        this.submitBtn.disabled = false;
        this.submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Appointment Request';
        this.progressDiv.classList.add('d-none');
        
        // Hide viewport-centered loading overlay
        this.hideLoadingOverlay();
        
        if (this.abortController) {
            this.abortController.abort();
        }
    }
    
    updateProgress() {
        if (this.currentStep < this.options.progressSteps.length) {
            const progress = ((this.currentStep + 1) / this.options.progressSteps.length) * 100;
            
            // Update form progress bar
            const progressBar = this.progressDiv.querySelector('.progress-bar');
            const progressText = this.progressDiv.querySelector('.progress-text');
            
            if (progressBar) progressBar.style.width = `${progress}%`;
            if (progressText) progressText.textContent = this.options.progressSteps[this.currentStep];
            
            // Update overlay progress bar
            this.updateOverlayProgress();
            
            this.currentStep++;
        }
    }
    
    async submitWithRetry() {
        for (let attempt = 0; attempt <= this.options.retryAttempts; attempt++) {
            try {
                this.retryCount = attempt;
                if (attempt > 0) {
                    this.showAlert('info', `Retrying submission... (Attempt ${attempt + 1})`);
                    await this.delay(1000 * attempt); // Progressive delay
                }
                
                return await this.performSubmission();
                
            } catch (error) {
                if (attempt === this.options.retryAttempts) {
                    throw error;
                }
                console.warn(`Submission attempt ${attempt + 1} failed:`, error);
            }
        }
    }
    
    async performSubmission() {
        this.abortController = new AbortController();
        
        const formData = new FormData(this.form);
        
        // Add progress updates
        const progressInterval = setInterval(() => {
            this.updateProgress();
        }, 2000);
        
        try {
            const response = await fetch(this.form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: this.abortController.signal,
                timeout: this.options.timeout
            });
            
            clearInterval(progressInterval);
            
            if (!response.ok) {
                let errorMessage = `HTTP ${response.status}: ${response.statusText}`;
                try {
                    const errorBody = await response.json();
                    if (errorBody && (errorBody.message || errorBody.error || errorBody.details)) {
                        const msg = errorBody.message || errorBody.error || errorBody.details;
                        errorMessage = `HTTP ${response.status}: ${msg}`;
                    }
                } catch (e) { /* ignore parse errors */ }
                throw new Error(errorMessage);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Invalid response format');
            }
            
            return await response.json();
            
        } catch (error) {
            clearInterval(progressInterval);
            
            if (error.name === 'AbortError') {
                throw new Error('Request was cancelled');
            } else if (error.name === 'TimeoutError') {
                throw new Error('Request timed out. Please check your connection and try again.');
            }
            
            throw error;
        }
    }
    
    handleSuccess(result) {
        // Keep overlay visible for a moment to show success
        const overlayProgressText = document.getElementById('overlay-progress-text');
        const overlayProgressBar = document.getElementById('overlay-progress-bar');
        
        if (overlayProgressText) overlayProgressText.textContent = 'Success! Redirecting...';
        if (overlayProgressBar) overlayProgressBar.style.width = '100%';
        
        this.showAlert('success', result.message);
        this.form.reset();
        
        // Hide overlay and redirect after delay
        setTimeout(() => {
            this.hideLoadingOverlay();
            if (result.redirect_url) {
                window.location.href = result.redirect_url;
            }
        }, 2000);
    }
    
    handleError(message) {
        this.showAlert('danger', message);
        // Ensure overlay is hidden on error
        this.hideLoadingOverlay();
    }
    
    showAlert(type, message) {
        this.alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        this.alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        this.alertDiv.style.display = 'block';
        
        // Auto-hide success messages
        if (type === 'success') {
            setTimeout(() => {
                this.alertDiv.style.display = 'none';
            }, 5000);
        }
    }
    
    // Utility functions
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    /**
     * Show viewport-centered loading overlay
     */
    showLoadingOverlay() {
        // Remove existing overlay if present
        this.hideLoadingOverlay();
        
        // Also hide any global loaders that might be active
        if (typeof NyalifeLoader !== 'undefined') {
            NyalifeLoader.forceHide();
        }
        
        // Create overlay container
        this.loadingOverlay = document.createElement('div');
        this.loadingOverlay.id = 'appointment-loading-overlay';
        this.loadingOverlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(2px);
        `;
        
        // Create loading content
        const loadingContent = document.createElement('div');
        loadingContent.style.cssText = `
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            min-width: 300px;
        `;
        
        loadingContent.innerHTML = `
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h5 class="mb-2 text-dark">Processing Your Request</h5>
            <div class="progress mb-3" style="height: 8px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                     role="progressbar" style="width: 0%" id="overlay-progress-bar"></div>
            </div>
            <p class="text-muted mb-0" id="overlay-progress-text">Preparing...</p>
        `;
        
        this.loadingOverlay.appendChild(loadingContent);
        document.body.appendChild(this.loadingOverlay);
        
        // Add processing class to body for better control
        document.body.classList.add('appointment-processing');
    }
    
    /**
     * Hide viewport-centered loading overlay
     */
    hideLoadingOverlay() {
        if (this.loadingOverlay) {
            this.loadingOverlay.remove();
            this.loadingOverlay = null;
        }
        
        // Remove processing class from body
        document.body.classList.remove('appointment-processing');
    }
    
    /**
     * Update the overlay progress (in addition to the form progress)
     */
    updateOverlayProgress() {
        if (!this.loadingOverlay) return;
        
        const overlayProgressBar = document.getElementById('overlay-progress-bar');
        const overlayProgressText = document.getElementById('overlay-progress-text');
        
        if (this.currentStep < this.options.progressSteps.length) {
            const progress = ((this.currentStep + 1) / this.options.progressSteps.length) * 100;
            
            if (overlayProgressBar) {
                overlayProgressBar.style.width = `${progress}%`;
            }
            
            if (overlayProgressText) {
                overlayProgressText.textContent = this.options.progressSteps[this.currentStep];
            }
        }
    }
}

// Auto-initialize for guest appointment forms
document.addEventListener('DOMContentLoaded', function() {
    const guestForm = document.getElementById('guestAppointmentForm');
    if (guestForm) {
        new AppointmentFormOptimizer('guestAppointmentForm');
    }
});
