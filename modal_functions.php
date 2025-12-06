<?php
/**
 * Nyalife HMS - Modal Functions
 * 
 * This file contains PHP functions for handling modals across the application.
 */

/**
 * Cleanup Modal Backdrops
 * 
 * This function removes any lingering modal backdrops and resets body classes
 * to ensure clean navigation between pages, especially after modal-based login.
 * It's also compatible with the NyalifeLoader implementation.
 * 
 * @return string JavaScript to execute for cleanup
 */
function cleanupModalBackdrops() {
    ob_start();
    ?>
    <script>
    // Function to cleanup modal backdrops
    function cleanupModals() {
        // Remove any lingering modal backdrops
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        
        // Remove modal-open class and inline styles from body
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
        // Reset any modal instances
        if (typeof bootstrap !== 'undefined') {
            document.querySelectorAll('.modal').forEach(modalEl => {
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.dispose();
                }
            });
        }
        
        // Also force hide loader if NyalifeLoader is available
        if (typeof NyalifeLoader !== 'undefined') {
            NyalifeLoader.forceHide();
        }
        
        console.log('Modal cleanup complete');
    }
    
    // Execute cleanup
    document.addEventListener('DOMContentLoaded', cleanupModals);
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        cleanupModals();
    }
    </script>
    <?php
    return ob_get_clean();
}

/**
 * Force Hide Loader
 * 
 * This function force-hides the loader container that might be blocking
 * dashboard content after login. It uses the NyalifeLoader JavaScript object
 * to properly clean up and hide any loaders on the page.
 * 
 * @return string JavaScript to execute for hiding the loader
 */
function forceHideLoader() {
    ob_start();
    ?>
    <script>
    // Function to force-hide the loader container and ensure it doesn't block content
    function forceHideLoaderContainer() {
        // Use the NyalifeLoader if it exists
        if (typeof NyalifeLoader !== 'undefined') {
            console.log('Using NyalifeLoader to clean up loaders');
            NyalifeLoader.forceHide();
            NyalifeLoader.cleanup();
            return;
        }
        
        // Fallback cleanup for older pages
        // Get all possible loader elements
        const loaders = [
            document.getElementById('nyalifaLoader'),
            document.getElementById('globalLoader'),
            document.querySelector('.nyalife-loader-container'),
            document.querySelector('.loader-container')
        ];
        
        // Apply force-hide to all found loader elements
        loaders.forEach(loader => {
            if (loader) {
                console.log('Force-hiding loader:', loader.id || 'loader-container');
                
                // Force hide by setting z-index to below content
                loader.style.zIndex = "-1";
                loader.style.display = "none";
                loader.style.visibility = "hidden";
                loader.style.opacity = "0";
                
                // Add a class to mark it as force-hidden
                loader.classList.add('force-hide');
                
                // Also remove any class that might be showing it
                loader.classList.remove('active');
                loader.classList.add('d-none');
            }
        });
        
        // Clean up body classes that might be related to loader
        document.body.classList.remove('overflow-hidden');
        document.body.classList.remove('loader-active');
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
        console.log('Loader cleanup complete');
    }
    
    // Execute immediately
    forceHideLoaderContainer();
    
    // Also execute when DOM is ready to ensure it runs
    document.addEventListener('DOMContentLoaded', forceHideLoaderContainer);
    
    // As a fallback, set a timeout to ensure it runs even if the DOMContentLoaded doesn't trigger
    setTimeout(forceHideLoaderContainer, 500);
    </script>
    <?php
    return ob_get_clean();
}
?> 