<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= $baseUrl ?>">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?>Nyalife HMS</title>
    
    <?php if (isset($headExtras)) echo $headExtras; ?>
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
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/modern-loader.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/footer.css">
    <link href="<?= $baseUrl ?>/assets/css/nyalife-theme.css" rel="stylesheet">
    <link href="<?= $baseUrl ?>/assets/css/style.css" rel="stylesheet">

    <!-- Additional CSS -->
    <?php if (isset($styles)): ?>
        <?php foreach ($styles as $style): ?>
            <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/<?= $style ?>">
        <?php endforeach; ?>
    <?php endif; ?>

</head>
<body>  
     <!--Modern Loader-->
     <?php include_once __DIR__ . '/modern-loader.php'; ?>

    <!-- Header -->
    <header class="header">
        <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--primary-color);">
            <div class="container">
                <a class="navbar-brand" href="<?= $baseUrl ?>">
                    <img src="<?= $baseUrl ?>/assets/img/logo/Logo2-transparent.png" alt="Nyalife HMS" height="40">
                    Nyalife HMS
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <?php if ($isLoggedIn): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= rtrim($baseUrl, '/') ?>/dashboard">Dashboard</a>
                            </li>
                            
                            <?php if (isset($currentUser['role']) && in_array($currentUser['role'], ['admin', 'nurse', 'doctor'])): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= rtrim($baseUrl, '/') ?>/patients">Patients</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if (isset($currentUser['role']) && in_array($currentUser['role'], ['admin', 'nurse', 'doctor', 'patient'])): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= rtrim($baseUrl, '/') ?>/appointments">Appointments</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if (isset($currentUser['role']) && in_array($currentUser['role'], ['admin', 'doctor', 'nurse'])): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= rtrim($baseUrl, '/') ?>/consultations">Consultations</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if (isset($currentUser['role']) && in_array($currentUser['role'], ['admin', 'lab_technician', 'doctor'])): ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="labDropdown" role="button" data-bs-toggle="dropdown">
                                        Laboratory
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="<?= rtrim($baseUrl, '/') ?>/lab/requests">Lab Requests</a></li>
                                        <?php if (in_array($currentUser['role'], ['admin', 'lab_technician'])): ?>
                                            <li><a class="dropdown-item" href="<?= rtrim($baseUrl, '/') ?>/lab/tests">Lab Tests</a></li>
                                        <?php endif; ?>
                                    </ul>
                                </li>
                            <?php endif; ?>
                            
                            <?php if (isset($currentUser['role']) && in_array($currentUser['role'], ['admin', 'pharmacist', 'doctor'])): ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="pharmacyDropdown" role="button" data-bs-toggle="dropdown">
                                        Pharmacy
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="<?= rtrim($baseUrl, '/') ?>/pharmacy/medicines">Medicines</a></li>
                                        <li><a class="dropdown-item" href="<?= rtrim($baseUrl, '/') ?>/pharmacy/inventory">Inventory</a></li>
                                        <li><a class="dropdown-item" href="<?= rtrim($baseUrl, '/') ?>/pharmacy/orders">Orders</a></li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                            
                            <?php if (isset($currentUser['role']) && in_array($currentUser['role'], ['admin', 'doctor'])): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= rtrim($baseUrl, '/') ?>/prescriptions">Prescriptions</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if (isset($currentUser['role']) && $currentUser['role'] === 'admin'): ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                        Administration
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="<?= rtrim($baseUrl, '/') ?>/users">Users</a></li>
                                        <li><a class="dropdown-item" href="<?= rtrim($baseUrl, '/') ?>/settings">System Settings</a></li>
                                        <li><a class="dropdown-item" href="<?= rtrim($baseUrl, '/') ?>/reports">Reports</a></li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                    
                    <ul class="navbar-nav">
                        <?php if ($isLoggedIn): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                    <?= $currentUser['firstName'] ?> <?= $currentUser['lastName'] ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="<?= rtrim($baseUrl, '/') ?>/profile"><i class="fas fa-user me-2"></i>My Profile</a></li>
                                    <li><a class="dropdown-item" href="<?= rtrim($baseUrl, '/') ?>/profile/edit"><i class="fas fa-user-edit me-2"></i>Edit Profile</a></li>
                                    <li><a class="dropdown-item" href="<?= rtrim($baseUrl, '/') ?>/profile/change-password"><i class="fas fa-key me-2"></i>Change Password</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="<?= rtrim($baseUrl, '/') ?>/logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= rtrim($baseUrl, '/') ?>/login">Login</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Main Content -->
    <main class="container py-5 px-5">
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
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-top">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="footer-about">
                            <a href="<?= $baseUrl ?>" class="footer-logo">
                                <img src="<?= $baseUrl ?>/assets/img/logo/Logo2-transparent.png" alt="Nyalife HMS" height="50">
                            </a>
                            <p>Nyalife Women's Clinic is dedicated to providing exceptional healthcare services with compassion and expertise.</p>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <div class="footer-links">
                            <h4>Quick Links</h4>
                            <ul>
                                <li><a href="<?= $baseUrl ?>"><i class="fas fa-chevron-right"></i> Home</a></li>
                                <li><a href="<?= $baseUrl ?>/about"><i class="fas fa-chevron-right"></i> About Us</a></li>
                                <li><a href="<?= $baseUrl ?>/services"><i class="fas fa-chevron-right"></i> Services</a></li>
                                <li><a href="<?= $baseUrl ?>/doctors"><i class="fas fa-chevron-right"></i> Doctors</a></li>
                                <li><a href="<?= $baseUrl ?>/contact"><i class="fas fa-chevron-right"></i> Contact</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="footer-contact">
                            <h4>Contact Us</h4>
                            <ul>
                                <li><i class="fas fa-map-marker-alt"></i> JemPark Complex, Suite A5, Sabaki, Kenya</li>
                                <li><i class="fas fa-phone-alt"></i> +254 746 516 514</li>
                                <li><i class="fas fa-envelope"></i> info@nyalifewomensclinic.com</li>
                                <li><i class="fas fa-clock"></i> Mon - Sat: 8:00 AM - 8:00 PM</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="footer-newsletter">
                            <h4>Newsletter</h4>
                            <p>Subscribe to our newsletter for health tips and updates.</p>
                            <form class="newsletter-form">
                                <input type="email" placeholder="Your Email Address">
                                <button type="submit"><i class="fas fa-paper-plane"></i></button>
                            </form>
                            <div class="social-links mt-3">
                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                                <a href="#"><i class="fab fa-instagram"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-12 text-center">
                        <p class="mb-0">&copy; <?= date('Y') ?> Nyalife Women's Clinic. All Rights Reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Load jQuery FIRST -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- AOS Animation Library -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Core modules -->
    <script src="<?= $baseUrl ?>/assets/js/core/forms.js"></script>
    
    <!-- Utils -->
    <script src="<?= $baseUrl ?>/assets/js/common/utils.js"></script>
    <script src="<?= $baseUrl ?>/assets/js/common/auth-utils.js"></script>
    <script src="<?= $baseUrl ?>/assets/js/common/validation.js"></script>
    <script src="<?= $baseUrl ?>/assets/js/common/date-utils.js"></script>
    
    <!-- Modern Loader -->
    <script src="<?= $baseUrl ?>/assets/js/modern-loader.js"></script>
    
    <!-- Main Application JS -->
    <script src="<?= $baseUrl ?>/assets/js/nyalife.js"></script>
    <script src="<?= $baseUrl ?>/assets/js/alerts.js"></script>
    
    <!-- Additional Scripts -->
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= $baseUrl ?>/assets/js/<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <script>
        // Initialize AOS
        AOS.init();
    </script>
</body>
</html>