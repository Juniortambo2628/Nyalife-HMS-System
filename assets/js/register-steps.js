/* Register form multi-step navigation
 * - Shows step 1 (personal) then step 2 (account)
 * - Validates current step's required controls before advancing
 * - Smooth CSS transitions
 */
(function(){
    function showStep(container, index){
        const steps = container.querySelectorAll('.form-step');
        steps.forEach((s, i) => {
            if (i === index) s.classList.add('active'); else s.classList.remove('active');
        });
        // update focus to first input in active
        const active = container.querySelector('.form-step.active');
        if (active) {
            const first = active.querySelector('input, select, textarea, button');
            if (first) first.focus();
        }
    }

    function validStep(container, index){
        const step = container.querySelectorAll('.form-step')[index];
        if (!step) return false;
        const controls = step.querySelectorAll('input, select, textarea');
        for (const c of controls){
            if (c.hasAttribute('required')){
                if (!c.value || c.value.trim() === ''){
                    // trigger browser validation UI
                    c.reportValidity();
                    return false;
                }
            }
        }
        return true;
    }

    function init(){
        const form = document.getElementById('registrationForm');
        if (!form) return;

        const container = form.querySelector('.registration-steps');
        if (!container) return;

        // restore saved step from localStorage if present
        const saved = parseInt(localStorage.getItem('registrationStep') || '0', 10);
        let current = (!isNaN(saved) && saved >=0) ? saved : 0;
        const steps = container.querySelectorAll('.form-step');
        const dotsContainer = form.parentElement.querySelector('.step-dots');

        // create dots
        if (dotsContainer){
            dotsContainer.innerHTML = '';
            steps.forEach((s, i) => {
                const d = document.createElement('span');
                d.className = 'step-dot mx-1';
                d.dataset.index = i;
                // show human-friendly step number (1-based)
                d.textContent = String(i + 1);
                d.setAttribute('aria-label', 'Step ' + String(i + 1));
                dotsContainer.appendChild(d);
            });
        }

        // attach next buttons
        container.querySelectorAll('.btn-step-next').forEach(btn => {
            btn.addEventListener('click', function(){
                if (validStep(container, current)){
                    current = Math.min(current + 1, steps.length -1);
                    localStorage.setItem('registrationStep', String(current));
                    showStep(container, current);
                    updateDots();
                }
            });
        });

        // attach prev buttons
        container.querySelectorAll('.btn-step-prev').forEach(btn => {
            btn.addEventListener('click', function(){
                current = Math.max(current - 1, 0);
                localStorage.setItem('registrationStep', String(current));
                showStep(container, current);
                updateDots();
            });
        });

        // ensure initial state
        showStep(container, current);
        updateDots();

        function updateDots(){
            if (!dotsContainer) return;
            const dots = dotsContainer.querySelectorAll('.step-dot');
            dots.forEach((d,i)=> d.classList.toggle('active', i === current));
        }

        // clear saved step on successful submission (listen for form submit success via event)
        form.addEventListener('registration:success', function(){ localStorage.removeItem('registrationStep'); });
    }

    document.addEventListener('DOMContentLoaded', init);
    document.addEventListener('page:loaded', init);
})();


