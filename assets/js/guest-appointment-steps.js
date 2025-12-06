/**
 * Guest Appointment Form - Multi-step navigation with validation
 */
(function(){
    'use strict';

    function announceForA11y(message) {
        const statusEl = document.getElementById('guestAppointmentValidationStatus');
        if (statusEl) {
            statusEl.textContent = message;
            setTimeout(() => { statusEl.textContent = ''; }, 1000);
        }
    }

    function showStep(container, index) {
        const steps = container.querySelectorAll('.form-step');
        steps.forEach((s, i) => {
            if (i === index) {
                s.classList.add('active');
                s.classList.remove('prev');
            } else {
                s.classList.remove('active');
                if (i < index) {
                    s.classList.add('prev');
                } else {
                    s.classList.remove('prev');
                }
            }
        });
        
        // Update progress bar
        const progressBar = document.querySelector('.register-progress-bar');
        if (progressBar) {
            const progress = ((index + 1) / steps.length) * 100;
            progressBar.style.width = progress + '%';
        }
        
        // Update focus to first input in active step
        const active = container.querySelector('.form-step.active');
        if (active) {
            const first = active.querySelector('input, select, textarea, button');
            if (first) setTimeout(() => first.focus(), 100);
        }
        
        announceForA11y(`Step ${index + 1} of ${steps.length}`);
    }

    function validStep(container, index) {
        const step = container.querySelectorAll('.form-step')[index];
        if (!step) return false;
        
        const controls = step.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;
        
        for (const c of controls) {
            if (!c.value || c.value.trim() === '') {
                c.reportValidity();
                c.classList.add('is-invalid');
                isValid = false;
            } else {
                c.classList.remove('is-invalid');
            }
        }
        
        return isValid;
    }

    function init() {
        const form = document.getElementById('guestAppointmentForm');
        if (!form) return;

        const container = form.querySelector('.registration-steps');
        if (!container) return;

        // Restore saved step from localStorage if present
        const saved = parseInt(localStorage.getItem('guestAppointmentStep') || '0', 10);
        let current = (!isNaN(saved) && saved >= 0 && saved < 2) ? saved : 0;
        
        const steps = container.querySelectorAll('.form-step');
        const dotsContainer = document.querySelector('.step-dots');

        // Create step dots
        if (dotsContainer) {
            dotsContainer.innerHTML = '';
            steps.forEach((s, i) => {
                const d = document.createElement('div');
                d.className = 'step-dot';
                d.textContent = i + 1;
                d.setAttribute('role', 'button');
                d.setAttribute('aria-label', `Step ${i + 1}`);
                d.setAttribute('tabindex', '0');
                d.addEventListener('click', () => {
                    if (validStep(container, current) || i < current) {
                        current = i;
                        localStorage.setItem('guestAppointmentStep', String(current));
                        showStep(container, current);
                        updateDots();
                    }
                });
                dotsContainer.appendChild(d);
            });
        }

        // Attach next buttons
        container.querySelectorAll('.btn-step-next').forEach(btn => {
            btn.addEventListener('click', function() {
                if (validStep(container, current)) {
                    current = Math.min(current + 1, steps.length - 1);
                    localStorage.setItem('guestAppointmentStep', String(current));
                    showStep(container, current);
                    updateDots();
                }
            });
        });

        // Attach prev buttons
        container.querySelectorAll('.btn-step-prev').forEach(btn => {
            btn.addEventListener('click', function() {
                current = Math.max(current - 1, 0);
                localStorage.setItem('guestAppointmentStep', String(current));
                showStep(container, current);
                updateDots();
            });
        });

        function updateDots() {
            if (!dotsContainer) return;
            const dots = dotsContainer.querySelectorAll('.step-dot');
            dots.forEach((d, i) => {
                d.classList.remove('active', 'completed');
                if (i === current) {
                    d.classList.add('active');
                } else if (i < current) {
                    d.classList.add('completed');
                }
            });
        }

        // Ensure initial state
        showStep(container, current);
        updateDots();

        // Clear saved step on successful submission
        form.addEventListener('submit', function(e) {
            const formData = new FormData(form);
            const isAjax = form.dataset.ajax === 'true';
            
            if (isAjax) {
                e.preventDefault();
                
                // Animate progress bar
                const progressBar = document.querySelector('.register-progress-bar');
                if (progressBar) {
                    progressBar.style.width = '100%';
                }
                
                // Show spinner
                const spinner = document.getElementById('guestAppointmentSpinner');
                if (spinner) spinner.classList.remove('d-none');
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (spinner) spinner.classList.add('d-none');
                    if (progressBar) progressBar.style.width = '0%';
                    
                    if (data.success) {
                        localStorage.removeItem('guestAppointmentStep');
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.href = (window.baseUrl || '') + '/guest-appointments/confirmation';
                        }
                    } else {
                        const alert = document.getElementById('guestAppointmentAlert');
                        if (alert) {
                            alert.textContent = data.message || 'Failed to submit appointment request';
                            alert.className = 'alert alert-danger';
                            alert.style.display = 'block';
                        }
                    }
                })
                .catch(error => {
                    if (spinner) spinner.classList.add('d-none');
                    if (progressBar) progressBar.style.width = '0%';
                    const alert = document.getElementById('guestAppointmentAlert');
                    if (alert) {
                        alert.textContent = 'An error occurred. Please try again.';
                        alert.className = 'alert alert-danger';
                        alert.style.display = 'block';
                    }
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', init);
    document.addEventListener('page:loaded', init);
})();

