<?php
/**
 * Nyalife HMS - Landing Page Layout
 */

$pageTitle = 'Home - Nyalife HMS';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= $baseUrl ?>">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?>Nyalife HMS</title>
    
    <?php if (isset($headExtras)) {
        echo $headExtras;
    } ?>
      <!--favicon-->
      <link rel="icon" href="<?= $baseUrl ?>/assets/img/logo/Logo2-transparent.png" type="image/x-icon">
    
     <!-- Google Fonts -->
     <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@400;500;600;700&family=Raleway:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Vendor CSS Files -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/z-index.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/nyalife-loader-unified.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/footer.css">
    <link href="<?= $baseUrl ?>/assets/css/nyalife-theme.css" rel="stylesheet">
    <link href="<?= $baseUrl ?>/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/modal-unified.css">
    
    
    <!-- Additional CSS -->
    <?php if (isset($styles)): ?>
        <?php foreach ($styles as $style): ?>
            <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/<?= $style ?>">
        <?php endforeach; ?>
    <?php endif; ?>

</head>
<body class="<?= function_exists('get_body_classes') ? get_body_classes() : '' ?>">  

    <!-- Nyalife Loader will be injected by nyalife-loader-unified.js -->

    <!-- Header - Using reusable component -->
    <?php
    // Define NYALIFE_INCLUDED to allow direct inclusion of the header
    define('NYALIFE_INCLUDED', true);
include_once __DIR__ . '/../../components/header.php';
?>
    
    <!-- Display any session messages -->
    <?php if (!empty($message)): ?>
    <div class="container position-relative z-index-1">
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <?= $content ?>
    
    <!-- Footer - Using reusable component -->
    <?php
// Already defined NYALIFE_INCLUDED above
include_once __DIR__ . '/../../components/footer.php';
?>
    
    <!-- Scripts - Using unified script loader -->
    <?php
// Set page-specific scripts for landing page
$pageSpecificScripts = ['guest-appointment.js', 'landing.js'];
include_once __DIR__ . '/../../components/unified-script-loader.php';
?>
</body>
</html>