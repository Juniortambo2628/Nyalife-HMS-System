/**
 * jQuery Compatibility Layer for Nyalife HMS
 * This ensures jQuery is accessible via both jQuery and $ globally
 */

(function() {
    // Check if jQuery is loaded
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded! Many features will not work correctly.');
        return;
    }

    // Ensure $ is defined globally even if jQuery.noConflict() was used
    if (typeof $ === 'undefined') {
        window.$ = jQuery;
        console.info('jQuery assigned to global $ variable for compatibility');
    }

    // Check jQuery version
    const jQueryVersion = jQuery.fn.jquery;
    const minVersion = '3.0.0';
    
    if (jQueryVersion && compareVersions(jQueryVersion, minVersion) < 0) {
        console.warn(`jQuery version ${jQueryVersion} detected. Nyalife HMS recommends jQuery ${minVersion} or newer.`);
    }

    // Version comparison utility
    function compareVersions(a, b) {
        const partsA = a.split('.');
        const partsB = b.split('.');
        
        for (let i = 0; i < Math.max(partsA.length, partsB.length); i++) {
            const numA = parseInt(partsA[i] || 0);
            const numB = parseInt(partsB[i] || 0);
            
            if (numA > numB) return 1;
            if (numA < numB) return -1;
        }
        
        return 0;
    }
})(); 