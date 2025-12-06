<?php
/**
 * Nyalife HMS - Loader Component
 * 
 * This file includes the loader component that can be included in pages
 * to show loading states during AJAX operations.
 * 
 * The loader HTML elements are created dynamically via JavaScript.
 */
?>

<!-- Include loader styles and scripts -->
<link rel="stylesheet" href="<?php echo getBaseUrl(); ?>/assets/css/loader.css">
<script src="<?php echo getBaseUrl(); ?>/assets/js/loader.js"></script>

<script>
  // Ensure the loader is properly initialized on page load
  document.addEventListener('DOMContentLoaded', function() {
    if (window.NyalifeLoader) {
      NyalifeLoader.init();
      
      // Force hide any loader after a short delay to ensure it doesn't block interaction
      setTimeout(function() {
        NyalifeLoader.forceHide();
      }, 500);
    }
  });
</script> 