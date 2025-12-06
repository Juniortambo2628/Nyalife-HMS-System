/**
 * NyaLife HMS Landing Page Functions
 * Handles all landing page specific functionality including hero animations,
 * service tooltips, and modal interactions.
 */

// Initialize hero animations and service tooltips
function initHeroAnimations() {
    let currentSlide = 0;
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    const totalSlides = slides.length;

    function showSlide(index) {
        // Hide all slides
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));

        // Show the selected slide
        slides[index].classList.add('active');
        dots[index].classList.add('active');

        currentSlide = index;
    }

    // Event listeners for controls
    const nextButton = document.getElementById('next-slide');
    const prevButton = document.getElementById('prev-slide');

    if (nextButton) {
        nextButton.addEventListener('click', () => {
            showSlide((currentSlide + 1) % totalSlides);
        });
    }

    if (prevButton) {
        prevButton.addEventListener('click', () => {
            showSlide((currentSlide - 1 + totalSlides) % totalSlides);
        });
    }

    // Add click events to dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
        });
    });

    // Auto-advance slides
    setInterval(() => {
        showSlide((currentSlide + 1) % totalSlides);
    }, 7000);

    // Handle tooltip content display
    const tooltipItems = document.querySelectorAll('.why-join-item');
    const tooltipContents = document.querySelectorAll('.tooltip-content');

    tooltipItems.forEach((item, index) => {
        item.addEventListener('mouseenter', () => {
            tooltipContents.forEach(content => content.style.display = 'none');
            const tooltipContent = document.getElementById(`tooltip-${index+1}`);
            if (tooltipContent) {
                tooltipContent.style.display = 'block';
            }
        });
    });

    // Initialize with the first tooltip
    tooltipContents.forEach(content => content.style.display = 'none');
    const firstTooltip = document.getElementById('tooltip-1');
    if (firstTooltip) {
        firstTooltip.style.display = 'block';
    }
}

// Handle service modal content
function initServiceModal() {
    // Copy content from tooltip to modal when service item is clicked
    document.querySelectorAll('.why-join-item').forEach(item => {
        item.addEventListener('click', function() {
            const tooltipContent = this.querySelector('.join-tooltip');
            if (tooltipContent) {
                const modalContent = document.getElementById('modalServiceContent');
                if (modalContent) {
                    modalContent.innerHTML = tooltipContent.innerHTML;
                }
            }
        });
    });
}

// Initialize modal cleanup
function initModalCleanup() {
    // Handle modal transitions
    const modals = document.querySelectorAll('.modal');

    modals.forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function() {
            // Clean up any lingering backdrops when a modal is closed
            if (document.querySelectorAll('.modal.show').length === 0) {
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.paddingRight = '';
            }
        });
    });

    // Fix for modal chaining - ensure clean state when opening a new modal
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
        button.addEventListener('click', function() {
            // Small delay to ensure proper modal transition
            setTimeout(function() {
                // If there are multiple backdrops, keep only the last one
                const backdrops = document.querySelectorAll('.modal-backdrop');
                if (backdrops.length > 1) {
                    for (let i = 0; i < backdrops.length - 1; i++) {
                        backdrops[i].remove();
                    }
                }
            }, 50);
        });
    });
}

// Run the initialization when the document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize landing page components if they exist
    if (document.querySelector('.hero-slide')) {
        initHeroAnimations();
    }

    if (document.querySelector('#serviceModal')) {
        initServiceModal();
    }

    initModalCleanup();
});