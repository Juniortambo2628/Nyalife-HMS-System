/* Auth form live validation
 * - Debounced AJAX checks for email and username availability
 * - Client-side password strength and match checks
 * - Shows FontAwesome icons for valid/invalid states
 */
(function(){
    function debounce(fn, delay){
        let t;
        return function(){
            const args = arguments;
            clearTimeout(t);
            t = setTimeout(()=> fn.apply(this, args), delay);
        };
    }

    function createStatusIcon(input){
        // ensure wrapper is position:relative
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

    function setIconSuccess(input){
        const icon = createStatusIcon(input);
        icon.className = 'field-status-icon fas fa-check-circle text-success';
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        // update inline helper
        setInlineFeedback(input, 'valid', 'Looks good');
        // update aria-live
        announceForA11y(input.name + ' is valid');
    }

    function setIconError(input){
        const icon = createStatusIcon(input);
        icon.className = 'field-status-icon fas fa-times-circle text-danger';
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        // update inline helper
        setInlineFeedback(input, 'invalid', 'Invalid value');
        // update aria-live
        announceForA11y(input.name + ' is invalid');
    }

    function setIconLoading(input){
        const icon = createStatusIcon(input);
        icon.className = 'field-status-icon fas fa-spinner fa-spin text-muted';
        input.classList.remove('is-valid','is-invalid');
    }

    function clearIcon(input){
        const parent = input.parentElement;
        const icon = parent.querySelector('.field-status-icon');
        if (icon) icon.remove();
        input.classList.remove('is-valid','is-invalid');
        removeInlineFeedback(input);
    }

    function setInlineFeedback(input, type, message){
        // create or update feedback element next to input
        let fb = input.parentElement.querySelector('.field-feedback');
        if (!fb){
            fb = document.createElement('div');
            fb.className = 'field-feedback small mt-1';
            input.parentElement.appendChild(fb);
        }
        fb.textContent = message || '';
        if (type === 'valid'){
            fb.classList.remove('text-danger');
            fb.classList.add('text-success');
        } else {
            fb.classList.remove('text-success');
            fb.classList.add('text-danger');
        }
    }

    function removeInlineFeedback(input){
        const fb = input.parentElement.querySelector('.field-feedback');
        if (fb) fb.remove();
    }

    function announceForA11y(message){
        const status = document.getElementById('authValidationStatus');
        if (status){
            status.textContent = '';
            setTimeout(()=> status.textContent = message, 50);
        }
    }

    async function postJson(url, payload){
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        });
        return await res.json();
    }

    function init(){
        // Skip validation on login page - user requested no validation messages on login
        const isLoginPage = window.location.pathname.includes('/login') || 
                           document.querySelector('form#loginForm') !== null;
        if (isLoginPage) return;
        
        if (!document.body.classList.contains('auth-page')) return;

        const email = document.getElementById('email');
        const username = document.getElementById('username');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');

        if (email){
            const checkEmail = debounce(async function(){
                const v = email.value.trim();
                if (!v) { clearIcon(email); return; }
                // basic format check
                const re = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
                if (!re.test(v)) { setIconError(email); return; }
                setIconLoading(email);
                try{
                    const data = await postJson(window.baseUrl + '/api/validate-email', { email: v });
                    if (data && data.success && data.available) {
                        setIconSuccess(email);
                        setInlineFeedback(email, 'valid', data.message || 'Email available');
                        announceForA11y(data.message || 'Email available');
                    } else {
                        setIconError(email);
                        setInlineFeedback(email, 'invalid', data.message || 'Email already registered');
                        announceForA11y(data.message || 'Email already registered');
                    }
                }catch(e){ setIconError(email); }
            }, 400);
            email.addEventListener('input', checkEmail);
        }

        if (username){
            const checkUsername = debounce(async function(){
                const v = username.value.trim();
                if (!v) { clearIcon(username); return; }
                setIconLoading(username);
                try{
                    const data = await postJson(window.baseUrl + '/api/validate-username', { username: v });
                    if (data && data.success && data.available) {
                        setIconSuccess(username);
                        setInlineFeedback(username, 'valid', data.message || 'Username available');
                        announceForA11y(data.message || 'Username available');
                    } else {
                        setIconError(username);
                        setInlineFeedback(username, 'invalid', data.message || 'Username already taken');
                        announceForA11y(data.message || 'Username already taken');
                    }
                }catch(e){ setIconError(username); }
            }, 400);
            username.addEventListener('input', checkUsername);
        }

        // Password strength
        if (password){
            password.addEventListener('input', function(){
                const v = password.value;
                const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
                if (!v) { clearIcon(password); return; }
                if (regex.test(v)) setIconSuccess(password); else setIconError(password);
                // also re-check confirm
                if (confirmPassword && confirmPassword.value) {
                    if (confirmPassword.value === v) setIconSuccess(confirmPassword); else setIconError(confirmPassword);
                }
            });
        }

        if (confirmPassword){
            confirmPassword.addEventListener('input', function(){
                if (!confirmPassword.value) { clearIcon(confirmPassword); return; }
                if (password && confirmPassword.value === password.value) setIconSuccess(confirmPassword); else setIconError(confirmPassword);
            });
        }

        // On form submit, prevent if any invalid fields
        const regForm = document.getElementById('registrationForm');
        if (regForm){
            regForm.addEventListener('submit', function(e){
                // if any inputs have is-invalid class, prevent submission
                const invalid = regForm.querySelectorAll('.is-invalid');
                if (invalid && invalid.length > 0) {
                    // let browser show validation UI
                    e.preventDefault();
                    invalid[0].focus();
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', init);
    document.addEventListener('page:loaded', init);
})();


