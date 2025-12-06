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
            pauseAutoAdvance();
        });
    }

    if (prevButton) {
        prevButton.addEventListener('click', () => {
            showSlide((currentSlide - 1 + totalSlides) % totalSlides);
            pauseAutoAdvance();
        });
    }

    // Add click events to dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
            pauseAutoAdvance();
        });
    });

    // Auto-advance slides with better performance
    let slideInterval = setInterval(() => {
        showSlide((currentSlide + 1) % totalSlides);
    }, 7000);

    // Pause auto-advance when user interacts with controls
    const pauseAutoAdvance = () => {
        clearInterval(slideInterval);
        slideInterval = setInterval(() => {
            showSlide((currentSlide + 1) % totalSlides);
        }, 7000);
    };

    // Handle tooltip content display with better performance
    const tooltipItems = document.querySelectorAll('.why-join-item');
    const tooltipContents = document.querySelectorAll('.tooltip-content');
    let activeTooltip = 0;

    // Use requestAnimationFrame for smooth transitions
    const showTooltip = (index) => {
        requestAnimationFrame(() => {
            tooltipContents.forEach((content, i) => {
                content.style.display = i === index ? 'block' : 'none';
            });
            activeTooltip = index;
        });
    };

    tooltipItems.forEach((item, index) => {
        item.addEventListener('mouseenter', () => {
            if (activeTooltip !== index) {
                showTooltip(index);
            }
        });
    });

    // Initialize with the first tooltip
    showTooltip(0);
}

// Handle service modal content
function initServiceModal() {
    // Get the service modal element
    const serviceModal = document.getElementById('serviceModal');
    if (!serviceModal) return;
    
    // Get the modal content container
    const modalServiceContent = document.getElementById('modalServiceContent');
    if (!modalServiceContent) return;
    
    // Add click event listeners to service items
    document.querySelectorAll('.why-join-item').forEach((item, index) => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Find the tooltip content inside this item
            const tooltipContent = this.querySelector('.join-tooltip');
            
            if (tooltipContent) {
                // Clear existing content and copy new content
                modalServiceContent.innerHTML = tooltipContent.innerHTML;
                
                // Show the modal using Bootstrap's modal method
                const bsModal = new bootstrap.Modal(serviceModal);
                bsModal.show();
            }
        });
    });
    
    // Fix modal buttons to ensure they're clickable
    serviceModal.addEventListener('shown.bs.modal', function() {
        // Ensure buttons are clickable by setting proper z-index
        const buttons = serviceModal.querySelectorAll('button');
        buttons.forEach(button => {
            button.style.position = 'relative';
            button.style.zIndex = '1000';
        });
        
        // Fix backdrop
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
            backdrop.style.zIndex = '200';
        });
        
        // Fix modal z-index
        serviceModal.style.zIndex = '300';
    });
    
    // Fix for modal closing
    const closeButton = serviceModal.querySelector('.btn-close');
    if (closeButton) {
        closeButton.addEventListener('click', function(e) {
            e.preventDefault();
            const bsModal = bootstrap.Modal.getInstance(serviceModal);
            if (bsModal) {
                bsModal.hide();
            }
            
            // Remove backdrops
            setTimeout(() => {
                document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                    backdrop.remove();
                });
            }, 300);
        });
    }
    
    // Fix for buttons inside the modal footer
    const modalFooterButtons = serviceModal.querySelectorAll('.modal-footer button');
    modalFooterButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Get the target modal ID
            const targetModalId = this.getAttribute('data-bs-target');
            
            // Close the current modal
            const currentModal = bootstrap.Modal.getInstance(serviceModal);
            if (currentModal) {
                currentModal.hide();
            }
            
            // Remove any lingering backdrops
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                backdrop.remove();
            });
            
            // Show the target modal after a short delay
            if (targetModalId) {
                setTimeout(() => {
                    const targetModal = document.querySelector(targetModalId);
                    if (targetModal) {
                        const bsTargetModal = new bootstrap.Modal(targetModal);
                        bsTargetModal.show();
                    }
                }, 300);
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
                document.body.style.overflow = '';
            }
        });
    });

    // Fix for modal chaining - ensure clean state when opening a new modal
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
        button.addEventListener('click', function(e) {
            // Get the target modal
            const targetModalId = this.getAttribute('data-bs-target');
            if (!targetModalId) return;
            
            // Small delay to ensure proper modal transition
            setTimeout(function() {
                // If there are multiple backdrops, keep only the last one
                const backdrops = document.querySelectorAll('.modal-backdrop');
                if (backdrops.length > 1) {
                    for (let i = 0; i < backdrops.length - 1; i++) {
                        backdrops[i].remove();
                    }
                }
                
                // Ensure the backdrop has the correct z-index
                if (backdrops.length > 0) {
                    backdrops[backdrops.length - 1].style.zIndex = '200';
                }
                
                // Ensure the modal has the correct z-index
                const targetModal = document.querySelector(targetModalId);
                if (targetModal) {
                    targetModal.style.zIndex = '300';
                }
            }, 50);
        });
    });
}

// Run the initialization when the document is ready
// Debounce function for performance optimization
function debounce(func, wait) {
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

// Optimize scroll performance
function initScrollOptimization() {
    let ticking = false;
    
    const updateScrollPosition = () => {
        // Add any scroll-based animations here if needed
        ticking = false;
    };
    
    const onScroll = () => {
        if (!ticking) {
            requestAnimationFrame(updateScrollPosition);
            ticking = true;
        }
    };
    
    // Use passive event listener for better performance
    window.addEventListener('scroll', onScroll, { passive: true });
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize landing page components if they exist
    if (document.querySelector('.hero-slide')) {
        initHeroAnimations();
    }

    if (document.querySelector('#serviceModal')) {
        initServiceModal();
    }

    initModalCleanup();
    initScrollOptimization();
});

