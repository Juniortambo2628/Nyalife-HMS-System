/**
 * Nyalife HMS - Registration JavaScript
 */

// Initialize registration form when the page is loaded or reloaded via AJAX
document.addEventListener('DOMContentLoaded', initRegistrationForm);
document.addEventListener('page:loaded', initRegistrationForm);

function initRegistrationForm() {
    const registrationForm = document.getElementById('registrationForm');
    
    // Initialize Select2 for dropdowns if available
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#gender').select2({
            minimumResultsForSearch: -1, // Hide search box
            width: '100%'
        });
        
        $('#blood_group').select2({
            minimumResultsForSearch: -1, // Hide search box
            width: '100%'
        });
    }
    
    // Client-side validation
    if (registrationForm) {
        // Password validation
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        if (passwordInput && confirmPasswordInput) {
            passwordInput.addEventListener('input', validatePassword);
            confirmPasswordInput.addEventListener('input', validatePasswordMatch);
        }
        
        function validatePassword() {
            const password = passwordInput.value;
            const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
            
            if (!regex.test(password)) {
                passwordInput.setCustomValidity('Password must be at least 8 characters and include uppercase, lowercase, and numbers.');
            } else {
                passwordInput.setCustomValidity('');
            }
        }
        
        function validatePasswordMatch() {
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('Passwords do not match');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        }
        
        // Form submission with AJAX
        registrationForm.addEventListener('submit', function(e) {
            // Only use AJAX if Components is available or we want to enforce it
            // For now, we'll use it if the form has the class or data attribute, or always
            e.preventDefault();
            
            // Disable submit button
            const submitBtn = registrationForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Registering...';

            // progress bar handling (animated simulated progress)
            const progressBar = document.querySelector('.register-progress-bar');
            let progressInterval = null;
            function startProgress(){
                if (!progressBar) return;
                progressBar.style.width = '10%';
                let val = 10;
                progressInterval = setInterval(()=>{
                    val = Math.min(90, val + Math.random()*10);
                    progressBar.style.width = val + '%';
                }, 400);
            }
            function finishProgress(){
                if (!progressBar) return;
                if (progressInterval) clearInterval(progressInterval);
                progressBar.style.width = '100%';
                setTimeout(()=>{ progressBar.style.width = '0%'; }, 600);
            }

            startProgress();

            // Get form data
            const formData = new FormData(registrationForm);

            // Submit the form via AJAX
            fetch(registrationForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Finish progress
                finishProgress();
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Register';

                if (data.success) {
                    // Dispatch registration success event to allow clearing stored step
                    registrationForm.dispatchEvent(new Event('registration:success'));
                    // Show success message
                    const successAlert = document.createElement('div');
                    successAlert.className = 'alert alert-success alert-dismissible fade show';
                    successAlert.innerHTML = `
                        <strong>Success!</strong> ${data.message || 'Registration successful! You can now log in.'}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    
                    // Insert alert before the form
                    if (registrationForm.parentNode) {
                        registrationForm.parentNode.insertBefore(successAlert, registrationForm);
                    }
                    
                    // Reset the form
                    registrationForm.reset();
                    
                    // Redirect to login page after a delay
                    setTimeout(() => {
                        const loginUrl = window.baseUrl ? window.baseUrl + '/login' : '/login';
                        if (typeof Components !== 'undefined') {
                            Components.loadPage(loginUrl);
                        } else {
                            window.location.href = loginUrl;
                        }
                    }, 3000);
                } else {
                    // Show error messages
                    if (data.errors) {
                        // Handle field-specific errors
                        Object.keys(data.errors).forEach(field => {
                            const input = document.getElementById(field);
                            if (input) {
                                input.classList.add('is-invalid');
                                
                                // Create or update feedback div
                                let feedback = input.nextElementSibling;
                                if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                                    feedback = document.createElement('div');
                                    feedback.className = 'invalid-feedback';
                                    input.parentNode.insertBefore(feedback, input.nextSibling);
                                }
                                feedback.textContent = data.errors[field];
                                
                                // also add small inline helper message consistent with live validation
                                let fbInline = input.parentNode.querySelector('.field-feedback');
                                if (!fbInline) {
                                    fbInline = document.createElement('div');
                                    fbInline.className = 'field-feedback small mt-1 text-danger';
                                    input.parentNode.appendChild(fbInline);
                                }
                                fbInline.textContent = data.errors[field];
                            }
                        });
                        
                        // Show general error if any
                        if (data.errors.general) {
                            const generalAlert = document.createElement('div');
                            generalAlert.className = 'alert alert-danger alert-dismissible fade show';
                            generalAlert.innerHTML = `
                                ${data.errors.general}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            `;
                            
                            // Insert alert before the form
                            if (registrationForm.parentNode) {
                                registrationForm.parentNode.insertBefore(generalAlert, registrationForm);
                            }
                        }
                    } else {
                        // Show general error
                        const generalAlert = document.createElement('div');
                        generalAlert.className = 'alert alert-danger alert-dismissible fade show';
                        generalAlert.innerHTML = `
                            <strong>Error!</strong> ${data.message || 'An error occurred during registration. Please try again.'}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;
                        
                        // Insert alert before the form
                        if (registrationForm.parentNode) {
                            registrationForm.parentNode.insertBefore(generalAlert, registrationForm);
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                finishProgress();
                
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Register';
                
                // Show error message
                const generalAlert = document.createElement('div');
                generalAlert.className = 'alert alert-danger alert-dismissible fade show';
                generalAlert.innerHTML = `
                    <strong>Error!</strong> An unexpected error occurred. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                // Insert alert before the form
                if (registrationForm.parentNode) {
                    registrationForm.parentNode.insertBefore(generalAlert, registrationForm);
                }
            });
        });
    }
    
    // Add AJAX navigation to links if Components is available
    if (typeof Components !== 'undefined') {
        const baseUrl = window.baseUrl || '';
        const links = document.querySelectorAll('#main-content a[href^="' + baseUrl + '"]');
        links.forEach(link => {
            if (!link.hasAttribute('data-no-ajax')) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    Components.loadPage(this.href);
                });
            }
        });
    }
}