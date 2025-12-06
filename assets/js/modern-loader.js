/**
 * Simple Page Loader for Nyalife HMS
 * Handles page transitions with a sleek loading animation
 */

// Run this script immediately - we don't need to wait for DOMContentLoaded
(function() {
    // Get loader element that's already in the DOM
    const loaderEl = document.querySelector('.page-loader');

    if (!loaderEl) {
        console.error('Loader element not found');
        return;
    }

    // Function to hide the loader
    function hideLoader() {
        loaderEl.classList.add('hidden');
        document.body.classList.remove('loading');

        // Optional: Remove from DOM after transition (500ms)
        setTimeout(() => {
            if (loaderEl.parentNode) {
                //loaderEl.parentNode.removeChild(loaderEl);
            }
        }, 1000);
    }

    // Function to show the loader
    function showLoader() {
        loaderEl.classList.remove('hidden');
        document.body.classList.add('loading');
    }

    // Set loading text
    function setLoaderText(text) {
        const textEl = loaderEl.querySelector('.loader-text');
        if (textEl) {
            textEl.textContent = text;
        }
    }

    // Force hide after 3 seconds to prevent infinite loading
    const forceHideTimeout = setTimeout(hideLoader, 7000);

    // Hide on page load - this is the main event we care about
    window.addEventListener('load', function() {
        clearTimeout(forceHideTimeout); // Clear the force timeout since we're doing it properly now
        hideLoader();
    });

    // Show on page unload for navigation
    window.addEventListener('beforeunload', showLoader);

    // Expose the loader API globally
    window.NyalifeLoader = {
        show: showLoader,
        hide: hideLoader,
        setText: setLoaderText
    };
})();