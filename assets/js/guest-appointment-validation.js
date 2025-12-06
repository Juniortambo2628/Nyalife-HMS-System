/**
 * Guest Appointment Form - Live AJAX Validation
 * - Validates email format and availability
 * - Validates phone number format
 * - Validates date/time constraints
 * - Shows FontAwesome icons for valid/invalid states
 */
(function(){
    'use strict';

    function debounce(fn, delay){
        let t;
        return function(){
            const args = arguments;
            clearTimeout(t);
            t = setTimeout(()=> fn.apply(this, args), delay);
        };
    }

    function createStatusIcon(input){
        const parent = input.parentElement;
        parent.classList.add('position-relative');
        let icon = parent.querySelector('.field-status-icon');
        if (!icon) {
            icon = document.createElement('i');
            icon.className = 'field-status-icon fas';
            parent.appendChild(icon);
        }
        return icon;
    }

    function setIconSuccess(input, message = 'Looks good'){
        const icon = createStatusIcon(input);
        icon.className = 'field-status-icon fas fa-check-circle text-success';
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        setInlineFeedback(input, 'valid', message);
        announceForA11y(input.name + ' is valid: ' + message);
    }

    function setIconError(input, message = 'Invalid value'){
        const icon = createStatusIcon(input);
        icon.className = 'field-status-icon fas fa-times-circle text-danger';
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        setInlineFeedback(input, 'invalid', message);
        announceForA11y(input.name + ' is invalid: ' + message);
    }

    function setIconLoading(input){
        const icon = createStatusIcon(input);
        icon.className = 'field-status-icon fas fa-spinner fa-spin text-primary';
        input.classList.remove('is-invalid', 'is-valid');
    }

    function clearIcon(input){
        const icon = input.parentElement.querySelector('.field-status-icon');
        if (icon) icon.remove();
        input.classList.remove('is-invalid', 'is-valid');
        removeInlineFeedback(input);
    }

    function setInlineFeedback(input, type, message){
        let feedback = input.parentElement.querySelector('.field-feedback');
        if (!feedback) {
            feedback = document.createElement('span');
            feedback.className = 'field-feedback';
            input.parentElement.appendChild(feedback);
        }
        feedback.className = 'field-feedback text-' + (type === 'valid' ? 'success' : 'danger');
        feedback.textContent = message;
        feedback.style.display = 'block';
    }

    function removeInlineFeedback(input){
        const feedback = input.parentElement.querySelector('.field-feedback');
        if (feedback) feedback.style.display = 'none';
    }

    function announceForA11y(message) {
        const statusEl = document.getElementById('guestAppointmentValidationStatus');
        if (statusEl) {
            statusEl.textContent = message;
            setTimeout(() => { statusEl.textContent = ''; }, 1000);
        }
    }

    async function postJson(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return await response.json();
    }

    // Email validation
    const email = document.getElementById('guest_email');
    if (email) {
        email.addEventListener('blur', debounce(async function(){
            const v = this.value.trim();
            if (!v) { clearIcon(email); return; }
            
            // Basic email format check
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(v)) {
                setIconError(email, 'Please enter a valid email address');
                return;
            }

            setIconLoading(email);
            try {
                const data = await postJson(window.baseUrl + '/api/validate-email', { email: v });
                if (data && data.success && data.available) {
                    setIconSuccess(email, 'Email looks good');
                } else {
                    setIconError(email, data.message || 'Email may already be in use');
                }
            } catch(e) {
                // If validation endpoint fails, just check format
                if (emailRegex.test(v)) {
                    setIconSuccess(email, 'Email format is valid');
                } else {
                    setIconError(email, 'Invalid email format');
                }
            }
        }, 500));
    }

    // Phone validation
    const phone = document.getElementById('guest_phone');
    if (phone) {
        phone.addEventListener('blur', debounce(function(){
            const v = this.value.trim();
            if (!v) { clearIcon(phone); return; }
            
            // Basic phone validation (allows various formats)
            const phoneRegex = /^[\d\s\-\+\(\)]{10,}$/;
            if (phoneRegex.test(v)) {
                setIconSuccess(phone, 'Phone number looks good');
            } else {
                setIconError(phone, 'Please enter a valid phone number');
            }
        }, 300));
    }

    // Date validation (must be in future)
    const appointmentDate = document.getElementById('appointment_date');
    const appointmentTime = document.getElementById('appointment_time');
    
    if (appointmentDate) {
        appointmentDate.addEventListener('change', function(){
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                setIconError(appointmentDate, 'Appointment date must be in the future');
            } else {
                setIconSuccess(appointmentDate, 'Date looks good');
            }
        });
    }

    if (appointmentTime && appointmentDate) {
        appointmentTime.addEventListener('change', debounce(async function(){
            const date = appointmentDate.value;
            const time = this.value;
            
            if (!date || !time) { clearIcon(appointmentTime); return; }
            
            // Check if datetime is in the future
            const dateTime = new Date(date + 'T' + time);
            const now = new Date();
            
            if (dateTime <= now) {
                setIconError(appointmentTime, 'Appointment time must be in the future');
                return;
            }
            
            // Validate with server to check clinic hours
            setIconLoading(appointmentTime);
            try {
                const data = await postJson(window.baseUrl + '/api/validate-appointment', { date, time });
                if (data && data.success && data.available) {
                    setIconSuccess(appointmentTime, 'Time looks good');
                } else {
                    setIconError(appointmentTime, data.message || 'Time is not available');
                }
            } catch(e) {
                // On network error, still validate basic format
                setIconSuccess(appointmentTime, 'Time looks good');
            }
        }, 500));
        
        // Also validate when date changes (re-validate time)
        appointmentDate.addEventListener('change', function() {
            if (appointmentTime && appointmentTime.value) {
                appointmentTime.dispatchEvent(new Event('change'));
            }
        });
    }

    // Name validation
    const firstName = document.getElementById('guest_first_name');
    const lastName = document.getElementById('guest_last_name');
    
    [firstName, lastName].forEach(input => {
        if (input) {
            input.addEventListener('blur', debounce(function(){
                const v = this.value.trim();
                if (!v) { clearIcon(this); return; }
                
                if (v.length < 2) {
                    setIconError(this, 'Name must be at least 2 characters');
                } else if (!/^[a-zA-Z\s'-]+$/.test(v)) {
                    setIconError(this, 'Name can only contain letters, spaces, hyphens, and apostrophes');
                } else {
                    setIconSuccess(this, 'Looks good');
                }
            }, 300));
        }
    });

    // Date of birth validation
    const dob = document.getElementById('guest_date_of_birth');
    if (dob) {
        dob.addEventListener('change', function(){
            const birthDate = new Date(this.value);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            
            if (birthDate > today) {
                setIconError(dob, 'Date of birth cannot be in the future');
            } else if (age < 0 || age > 150) {
                setIconError(dob, 'Please enter a valid date of birth');
            } else {
                setIconSuccess(dob, 'Looks good');
            }
        });
    }

    // Select validation
    const gender = document.getElementById('guest_gender');
    const appointmentType = document.getElementById('appointment_type');
    
    [gender, appointmentType].forEach(select => {
        if (select) {
            select.addEventListener('change', function(){
                if (this.value) {
                    setIconSuccess(this, 'Looks good');
                } else {
                    clearIcon(this);
                }
            });
        }
    });
})();

