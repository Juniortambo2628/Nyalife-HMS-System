<?php
/**
 * Nyalife HMS - Default Layout
 */

$pageTitle = 'Dashboard - Nyalife HMS';
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
       <!-- Load FullCalendar CSS -->
       <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/nyalife-loader-unified.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/footer.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/layout-system.css">
    <link href="<?= $baseUrl ?>/assets/css/nyalife-theme.css" rel="stylesheet">
    <link href="<?= $baseUrl ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?= $baseUrl ?>/assets/css/custom.css" rel="stylesheet">
    <link href="<?= $baseUrl ?>/assets/css/notifications.css" rel="stylesheet">
    <link href="<?= $baseUrl ?>/assets/css/messages.css" rel="stylesheet">
    <link href="<?= $baseUrl ?>/assets/css/header-mobile.css" rel="stylesheet">
    <link href="<?= $baseUrl ?>/assets/css/z-index.css" rel="stylesheet">

    <!-- Additional CSS -->
    <?php if (isset($styles)): ?>
        <?php foreach ($styles as $style): ?>
            <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/<?= $style ?>">
        <?php endforeach; ?>
    <?php endif; ?>

</head>
<?php $bodyClasses = (function_exists('get_body_classes') ? get_body_classes() : '');
// Add app-page and has-sidebar on all non-landing pages to enable centralized padding
if (empty($isLanding) || !$isLanding) {
    $bodyClasses = trim($bodyClasses . ' app-page has-sidebar');
}
// Detect authentication pages (login / register / forgot password) and mark them as auth pages
$requestPath = isset($_SERVER['REQUEST_URI']) ? trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') : '';
$pathSegments = $requestPath === '' ? [] : explode('/', $requestPath);
$authKeywords = ['login', 'register', 'forgot', 'forgot-password', 'password-reset', 'password'];
$isAuthPage = false;
foreach ($pathSegments as $seg) {
    if (in_array(strtolower($seg), $authKeywords, true)) {
        $isAuthPage = true;
        break;
    }
}
if ($isAuthPage) {
    // mark auth page and ensure we DO NOT include has-sidebar on auth pages
    $bodyClasses = trim(($bodyClasses . ' auth-page'));
    $bodyClasses = preg_replace('/\bhas-sidebar\b/', '', $bodyClasses);
    $bodyClasses = trim(preg_replace('/\s+/', ' ', $bodyClasses));
}
?>
<body class="<?= htmlspecialchars($bodyClasses) ?>">  
    <!-- Nyalife Loader will be injected by nyalife-loader-unified.js -->

    <!-- Header - Using reusable component -->
    <?php
    // Define NYALIFE_INCLUDED to allow direct inclusion of the header
    define('NYALIFE_INCLUDED', true);
include_once __DIR__ . '/../../components/header.php';

// Include sidebar on all non-landing pages when user is logged in, but skip auth pages
if (isset($isLoggedIn) && $isLoggedIn && isset($currentUser) && (empty($isLanding) || !$isLanding) && empty($isAuthPage)):
    include_once __DIR__ . '/../../components/sidebar.php';
endif;
?>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Sidebar Toggle in Header (Desktop) -->
        <?php if (isset($isLoggedIn) && $isLoggedIn && (empty($isLanding) || !$isLanding)): ?>
        <button class="sidebar-toggle-header d-none d-md-block" id="sidebarToggleHeader" title="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <?php endif; ?>
        
        <!-- Flash Messages -->
        <?php if (!empty($flashMessages)): ?>
            <?php foreach ($flashMessages as $flash): ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Page Content -->
        <?= $content ?>

        <!-- Footer - placed inside main content so it aligns with the main area when sidebar is present; hide on auth pages -->
        <?php
    // Already defined NYALIFE_INCLUDED above
    if (empty($isAuthPage)) {
        include_once __DIR__ . '/../../components/footer.php';
    }
?>
    </main>
    
    <!-- Initialize Global Variables -->
    <script>
    // Set global variables for notifications and API calls
    window.baseUrl = '<?= $baseUrl ?>';
    window.isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
    </script>

    <!-- Scripts - Using unified script loader -->
    <?php
    // Initialize page specific scripts if not set
    if (!isset($pageSpecificScripts)) {
        $pageSpecificScripts = [];
    }
    
    // Add dashboard scripts if not landing and not auth
    if ((empty($isLanding) || !$isLanding) && empty($isAuthPage)) {
        $pageSpecificScripts = array_merge($pageSpecificScripts, ['components.js', 'notifications.js', 'messages.js']);
    }

    // If on auth pages, add the auth validation script
    if (!empty($isAuthPage)) {
        $additionalScripts = isset($additionalScripts) ? $additionalScripts : [];
        $additionalScripts = array_merge($additionalScripts, ['auth-validation.js','register-steps.js']);
    }
    
    // If landing page, add landing.js
    if (!empty($isLanding) && $isLanding) {
        // AssetHelper returns the full path relative to web root (e.g. assets/dist/js/landing.hash.js)
        // unified-script-loader will handle it correctly if it starts with assets/
        $pageSpecificScripts[] = AssetHelper::getJs('landing');
    }
    
    include_once __DIR__ . '/../../components/unified-script-loader.php';
    ?>
   
    <script>
    (function(){
        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/\"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function nl2br(str) {
            return String(str).replace(/\r?\n/g, '<br>');
        }

        // Show Bootstrap toast
        window.showToast = function(message, type = 'success', timeout = 4000) {
            const container = document.getElementById('globalToasts');
            if (!container) return;
            const toastId = 'toast-' + Date.now();
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-bg-' + (type === 'danger' ? 'danger' : 'success') + ' border-0';
            toast.id = toastId;
            toast.setAttribute('role','alert');
            toast.setAttribute('aria-live','assertive');
            toast.setAttribute('aria-atomic','true');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${escapeHtml(String(message))}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            container.appendChild(toast);
            const btoast = new bootstrap.Toast(toast, { delay: timeout });
            btoast.show();
            toast.addEventListener('hidden.bs.toast', function(){ toast.remove(); });
        };

        // Generic AJAX form handler for update endpoints
        document.addEventListener('submit', function(e){
            const form = e.target;
            if (!form || !form.action) return;
            // Handle update endpoints (update-field, update-vitals, update-*) or forms marked data-ajax
            const isUpdate = form.dataset.ajax === 'true' || /\/update(-|_)[a-z]+\//.test(form.action) || /\/update(?:-field|_vitals|_vital)/.test(form.action);
            if (!isUpdate) return;
            e.preventDefault();
            const formData = new FormData(form);
            fetch(form.action, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                .then(r => r.json())
                .then(json => {
                    if (json && json.success) {
                        // If updated fields returned, update elements with data-field attributes
                        if (json.updated && typeof json.updated === 'object') {
                        Object.keys(json.updated).forEach(function(k){
                                const els = document.querySelectorAll('[data-field~="'+k+'"]');
                                els.forEach(function(el){ el.innerHTML = nl2br(escapeHtml(String(json.updated[k]))); });
                            });
                        }
                        // If vitals returned under json.vitals, update elements with data-field names
                        if (json.vitals && typeof json.vitals === 'object') {
                        Object.keys(json.vitals).forEach(function(k){
                                const els = document.querySelectorAll('[data-field~="'+k+'"]');
                                els.forEach(function(el){ el.innerHTML = nl2br(escapeHtml(String(json.vitals[k]))); });
                            });
                        }
                        // If server returned a newly created vital record, insert into patient vitals table if present
                        if (json.vitals && json.vital_id) {
                            try {
                                const tbody = document.querySelector('#vitals table tbody');
                                if (tbody) {
                                    const v = json.vitals;
                                    const newRow = `
                                        <tr>
                                            <td>${v.measured_at || ''}</td>
                                            <td>${v.blood_pressure || ''}</td>
                                            <td>${v.pulse || ''}</td>
                                            <td>${v.temperature || ''}</td>
                                            <td>${v.respiratory_rate || ''}</td>
                                            <td>${v.oxygen_saturation || ''}</td>
                                            <td>${v.recorded_by || ''}</td>
                                        </tr>`;
                                    tbody.insertAdjacentHTML('afterbegin', newRow);
                                    // If DataTable present, try to redraw
                                    try {
                                        if (typeof $ !== 'undefined' && $.fn.DataTable) {
                                            const table = $(tbody).closest('table');
                                            if (table && table.length && table.dataTable) {
                                                // simple redraw
                                                table.DataTable().rows().invalidate().draw(false);
                                            }
                                        }
                                    } catch (e) {}
                                }
                            } catch (e) { console.error('Insert vitals row error', e); }
                        }
                        window.showToast(json.message || 'Updated', 'success');
                    } else {
                        // If server returned field errors, map them to form fields
                        if (json && json.errors && typeof json.errors === 'object') {
                            Object.keys(json.errors).forEach(function(field){
                                // try name selector then id
                                const inputs = document.querySelectorAll('[name="' + field + '"]');
                                if (inputs.length) {
                                    inputs.forEach(function(inp){
                                        inp.classList.add('is-invalid');
                                        const fb = inp.closest('.form-group')?.querySelector('.invalid-feedback');
                                        if (fb) fb.textContent = json.errors[field];
                                    });
                                } else {
                                    const el = document.getElementById(field);
                                    if (el) {
                                        el.classList.add('is-invalid');
                                        const fb = el.closest('.form-group')?.querySelector('.invalid-feedback');
                                        if (fb) fb.textContent = json.errors[field];
                                    }
                                }
                            });
                            window.showToast(json.message || 'Validation failed', 'danger');
                        } else {
                            const msg = (json && json.message) ? json.message : 'Update failed';
                            window.showToast(msg, 'danger');
                        }
                    }
                }).catch(err=>{ console.error(err); window.showToast('Update failed', 'danger'); });
        }, false);
    })();
    </script>
</body>
</html>