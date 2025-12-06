<?php
/**
 * Nyalife HMS - Home Page
 */

$pageTitle = 'Landing Page - Nyalife HMS';
?>
<div class="container-fluid p-0 m-0">
    <!-- Hero section -->
    <section class="hero hero-image">
        <!-- Hero background slides -->
        <div class="hero-slide slide-1 active"></div>
        <div class="hero-slide slide-2"></div>
        <div class="hero-slide slide-3"></div>
        <div class="hero-slide slide-4"></div>
        
        <!-- Hero overlay - single overlay for all slides -->
        <div class="hero-overlay overlay-slide-1"></div>
        
        <div class="container position-relative h-100">
            <div class="row h-100 align-items-center">
                <div class="col-lg-7">
                    <div class="hero-content w-100">
                        <h1 class="hero-title">Nyalife Hospital Management System</h1>
                        <h2 class="hero-subtitle">Specialized Obstetrics & Gynecology Care</h2>
                        <!-- Service boxes section -->
                        <div class="why-join mt-2">
                            <h3>Our Services</h3>
                            <div class="row g-3">
                                <div class="col-lg-3 col-md-3 col-sm-6 col-6 mb-3">
                                    <div class="why-join-item blur-bg" id="join-item-1">
                                        <a href="<?= $baseUrl ?>/services/obstetrics" class="text-decoration-none text-white">
                                            <i class="fas fa-user-md"></i>
                                            <p>Obstetrics Care</p>
                                        </a>
                                        <div class="join-tooltip">
                                            <h4>Obstetrics Care</h4>
                                            <p>Comprehensive prenatal, delivery, and postnatal care for expectant mothers. Our team of experienced obstetricians provides personalized care throughout your pregnancy journey.</p>
                                            <p>Services include regular check-ups, ultrasound screenings, genetic testing, and specialized care for high-risk pregnancies.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6 col-6 mb-3">
                                    <div class="why-join-item blur-bg" id="join-item-2">
                                        <a href="<?= $baseUrl ?>/services/gynecology" class="text-decoration-none text-white">
                                            <i class="fas fa-heartbeat"></i>
                                            <p>Gynecology Services</p>
                                        </a>
                                        <div class="join-tooltip">
                                            <h4>Gynecology Services</h4>
                                            <p>Expert care for women's reproductive health and wellness. Our gynecology services cover all aspects of women's health from routine examinations to specialized treatments.</p>
                                            <p>We offer pap smears, HPV testing, family planning, and treatments for conditions like endometriosis, PCOS, and menopause symptoms.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6 col-6 mb-3">
                                    <div class="why-join-item blur-bg" id="join-item-3">
                                        <a href="<?= $baseUrl ?>/services/laboratory" class="text-decoration-none text-white">
                                            <i class="fas fa-notes-medical"></i>
                                            <p>Lab Services</p>
                                        </a>
                                        <div class="join-tooltip">
                                            <h4>Laboratory Services</h4>
                                            <p>State-of-the-art diagnostic and testing facilities that provide quick and accurate results. Our advanced laboratory is equipped with the latest technology.</p>
                                            <p>Services include blood tests, urinalysis, hormone level testing, genetic testing, amniotic fluid analysis, and various screenings for better health management.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6 col-6 mb-3">
                                    <div class="why-join-item blur-bg" id="join-item-4">
                                        <a href="<?= $baseUrl ?>/services/pharmacy" class="text-decoration-none text-white">
                                            <i class="fas fa-pills"></i>
                                            <p>Pharmacy</p>
                                        </a>
                                        <div class="join-tooltip">
                                            <h4>Pharmacy Services</h4>
                                            <p>Full-service pharmacy with prescription and over-the-counter medications. Our in-house pharmacy ensures you receive your prescribed medications conveniently and promptly.</p>
                                            <p>Our pharmacists work closely with your healthcare providers to provide comprehensive medication management, including counseling on proper medication use.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 d-flex flex-wrap">
                                <?php if (!$isLoggedIn): ?>
                                    <a href="<?= $baseUrl ?>/register/patient" class="btn btn-secondary btn-hero me-2 mb-2">
                                        <i class="fas fa-user-plus me-1"></i> Register as Patient
                                    </a>
                                    <a href="<?= $baseUrl ?>/login" class="btn btn-light btn-hero mb-2">
                                        <i class="fas fa-sign-in-alt me-1"></i> Login
                                    </a>
                                <?php else: ?>
                                    <a href="<?= $baseUrl ?>/dashboard" class="btn btn-secondary btn-hero me-2 mb-2">
                                        <i class="fas fa-tachometer-alt me-1"></i> Go to My Dashboard
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 d-flex align-items-center mobile-hidden tablet-hidden">
                    <div class="tooltip-display-area blur-bg w-100">
                        <!-- Content for Obstetrics Care -->
                        <div class="tooltip-content" id="tooltip-1">
                            <h4>Obstetrics Care</h4>
                            <p>Our obstetrics care encompasses comprehensive prenatal, labor, delivery, and postnatal services. We provide personalized care throughout your pregnancy journey, including regular check-ups, ultrasound screenings, genetic testing, and specialized care for high-risk pregnancies.</p>
                            <p class="mt-2">Our experienced obstetricians and midwives work together to ensure you and your baby receive the best care possible in a warm, supportive environment.</p>
                        </div>
                        <!-- Content for Gynecology Services -->
                        <div class="tooltip-content" id="tooltip-2">
                            <h4>Gynecology Services</h4>
                            <p>Our gynecology services cover all aspects of women's reproductive health. We offer routine examinations, pap smears, HPV testing, contraception management, and treatment for conditions such as endometriosis, PCOS, fibroids, and menopause symptoms.</p>
                            <p class="mt-2">Our specialists provide both surgical and non-surgical treatments, using the latest technologies and minimally invasive procedures when possible.</p>
                        </div>
                        <!-- Content for Lab Services -->
                        <div class="tooltip-content" id="tooltip-3">
                            <h4>Laboratory Services</h4>
                            <p>Our state-of-the-art laboratory provides quick and accurate diagnostic testing for a wide range of conditions. Services include blood tests, urinalysis, hormone level tests, genetic testing, amniotic fluid analysis, and more.</p>
                            <p class="mt-2">Results are quickly integrated into our system, allowing your healthcare provider to promptly review and discuss findings with you for timely treatment decisions.</p>
                        </div>
                        <!-- Content for Pharmacy -->
                        <div class="tooltip-content" id="tooltip-4">
                            <h4>Pharmacy Services</h4>
                            <p>Our in-house pharmacy ensures you receive your prescribed medications conveniently and promptly. Our pharmacists work closely with your healthcare providers to provide comprehensive medication management, including prenatal vitamins, hormonal treatments, and other specialized medications.</p>
                            <p class="mt-2">We offer counseling on proper medication use, potential side effects, and drug interactions to keep you informed and safe.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hero navigation controls -->
        <div class="hero-controls">
            <div class="hero-arrow prev" id="prev-slide">
                <i class="fas fa-chevron-left"></i>
            </div>
            <div class="hero-dots">
                <div class="hero-dot active"></div>
                <div class="hero-dot"></div>
                <div class="hero-dot"></div>
                <div class="hero-dot"></div>
            </div>
            <div class="hero-arrow next" id="next-slide">
                <i class="fas fa-chevron-right"></i>
            </div>
        </div>
    </section>

    <!-- Hero animations and tooltip handling is now in landing.js -->

    <!-- Guest Appointment Booking Section - Moved higher up for better visibility -->
    <section class="py-5" id="guest-appointment">
        <div class="container text-center">
            <h2 class="mb-4 section-title border-bottom">New Patient? Book an Appointment!</h2>
            <p class="lead mb-4">You don't need an account to book your first visit. Fill out a simple form and we'll get you scheduled.</p>
            <a href="<?= $baseUrl ?>/guest-appointments" class="btn btn-primary btn-sm">
                <i class="fas fa-calendar-alt me-2"></i> Book Guest Appointment
            </a>
            <p class="mt-3 text-muted">Already a patient? <a href="<?= $baseUrl ?>/login">Login here</a> to manage your appointments.</p>
        </div>
    </section>

    <!-- Services section -->
    <section class="py-5"  id="services">
        <div class="container">
        <h2 class="text-center mt-5 mb-5 section-title">Our Services</h2>
            <ul class="nav nav-pills justify-content-center align-items-center g-3" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-obstetrics-tab" data-bs-toggle="pill" data-bs-target="#pills-obstetrics" type="button" role="tab" aria-controls="pills-obstetrics" aria-selected="true">
                        <i class="fas fa-xl fa-baby me-2"></i>Obstetrics Care
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-gynecology-tab" data-bs-toggle="pill" data-bs-target="#pills-gynecology" type="button" role="tab" aria-controls="pills-gynecology" aria-selected="false">
                        <i class="fas fa-xl fa-venus me-2"></i>Gynecology Services
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-lab-tab" data-bs-toggle="pill" data-bs-target="#pills-lab" type="button" role="tab" aria-controls="pills-lab" aria-selected="false">
                        <i class="fas fa-xl fa-microscope me-2"></i>Lab Services
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-pharmacy-tab" data-bs-toggle="pill" data-bs-target="#pills-pharmacy" type="button" role="tab" aria-controls="pills-pharmacy" aria-selected="false">
                        <i class="fas fa-xl fa-pills me-2"></i>Pharmacy
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="pills-tabContent">
                <!-- Obstetrics Tab Content -->
                <div class="tab-pane fade show active" id="pills-obstetrics" role="tabpanel" aria-labelledby="pills-obstetrics-tab">
                <div class="row align-items-center flex-md-row-reverse g-4 py-5 px-5">
                <div class="col-md-6">
                            <div class="service-tab-img-wrapper">   
                            <img src="<?= $baseUrl ?>/assets/img/service-tabs/doctor-1.jpg" class="service-tab-img" alt="Obstetrics Care">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="service-tabs-wrapper">
                            <h3>Comprehensive Pregnancy Care</h3>
                            <p class="lead">Nyalife HMS offers complete care for expectant mothers, ensuring a healthy and joyful journey from conception to postpartum.</p>
                            <p>Our obstetrics services include regular prenatal check-ups, advanced ultrasound screenings, genetic counseling, and childbirth education classes. We focus on personalized care plans for every mother, including specialized support for high-risk pregnancies.</p>
                            <ul class="list-unstyled service-features">
                                <li class="border-bottom py-2"><i class="fas fa-xl fa-check-circle text-primary me-2"></i> Personalised Prenatal Care</li>
                                <li class="border-bottom py-2"><i class="fas fa-xl fa-check-circle text-primary me-2"></i> Advanced Ultrasound & Diagnostics</li>
                                <li class="border-bottom py-2"><i class="fas fa-xl fa-check-circle text-primary me-2"></i> High-Risk Pregnancy Management</li>
                                <li class="py-2"><i class="fas fa-xl fa-check-circle text-primary me-2"></i> Postnatal Support & Counseling</li>
                            </ul>
                        </div>
                    </div>
                </div>  
            </div>

                <!-- Gynecology Tab Content -->
                <div class="tab-pane fade" id="pills-gynecology" role="tabpanel" aria-labelledby="pills-gynecology-tab">
                <div class="row align-items-center flex-md-row-reverse g-4 py-5 px-5">
                <div class="col-md-6">
                        <div class="service-tab-img-wrapper">
                            <img src="<?= $baseUrl ?>/assets/img/service-tabs/hospital-machine.jpg" class="service-tab-img" />
                        </div>
                        </div>
                        <div class="col-md-6">
                            <div class="service-tabs-wrapper">
                            <h3>Dedicated Women's Health Services</h3>
                            <p class="lead">From routine check-ups to complex procedures, our gynecology services are designed to support women at every stage of life.</p>
                            <p>We provide a full spectrum of gynecological care, including routine examinations, pap smears, HPV testing, contraception management, and management of conditions like endometriosis, PCOS, and menopausal symptoms. Our approach is holistic, focusing on both physical and emotional well-being.</p>
                            <ul class="list-unstyled service-features">
                                <li class="border-bottom py-2"><i class="fas fa-xl fa-check-circle text-primary me-2"></i> Routine GYN Exams & Screenings</li>
                                <li class="border-bottom py-2"><i class="fas fa-xl fa-check-circle text-primary me-2"></i> Contraception & Family Planning</li>
                                <li class="border-bottom py-2"><i class="fas fa-xl fa-check-circle text-primary me-2"></i> Menopause Management</li>
                                <li class="py-2"><i class="fas fa-xl fa-check-circle text-primary me-2"></i> Treatment for Gynecological Conditions</li>
                            </ul>
                        </div>
                    </div>
                </div>  
                </div>
                <!-- Lab Services Tab Content -->
                <div class="tab-pane fade" id="pills-lab" role="tabpanel" aria-labelledby="pills-lab-tab">
                <div class="row align-items-center flex-md-row-reverse g-4 py-5 px-5">
                <div class="col-md-6">
                        <div class="service-tab-img-wrapper">   
                            <img src="<?= $baseUrl ?>/assets/img/service-tabs/Laboratory-services.jpg" class="service-tab-img" alt="Laboratory Services">
                        </div>
                        </div>
                        <div class="col-md-6">
                            <div class="service-tabs-wrapper">
                            <h3>Accurate & Timely Diagnostics</h3>
                            <p class="lead">Our advanced laboratory provides precise and rapid diagnostic testing, crucial for effective treatment planning and patient management.</p>
                            <p>Equipped with cutting-edge technology, our lab conducts a wide range of tests including blood work, urinalysis, hormone level tests, and specialized genetic screenings. We ensure quick turnaround times for results, enabling prompt medical decisions.</p>
                            <ul class="list-unstyled service-features">
                                <li class="border-bottom py-2"><i class="fas fa-xl fa-check-circle text-primary me-2"></i> Extensive Blood & Urine Testing</li>
                                <li class="border-bottom py-2"><i class="fas fa-xl fa-check-circle text-primary me-2"></i> Hormone Level Assessments</li>
                                <li class="border-bottom py-2"><i class="fas fa-xl fa-check-circle text-primary me-2"></i> Genetic & Prenatal Screenings</li>
                                <li class="py-2"><i class="fas fa-xl fa-check-circle text-primary me-2"></i> Fast & Reliable Results</li>
                            </ul>
                        </div>
                    </div>
                </div>
                </div>


                <!-- Pharmacy Tab Content -->
                <div class="tab-pane fade" id="pills-pharmacy" role="tabpanel" aria-labelledby="pills-pharmacy-tab">
                    <div class="row align-items-center flex-md-row-reverse g-4 py-5 px-5">
                        <div class="col-md-6">
                            <div class="service-tab-img-wrapper">   
                            <img src="<?= $baseUrl ?>/assets/img/service-tabs/treatment.jpg" class="service-tab-img" alt="Pharmacy Services">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="service-tabs-wrapper">
                            <h3>Convenient & Expert Pharmacy</h3>
                            <p class="lead">Our in-house pharmacy provides easy access to medications and expert pharmaceutical advice, supporting your health journey.</p>
                            <p>We stock a comprehensive range of prescribed and over-the-counter medications relevant to women's health. Our pharmacists offer personalized counseling on medication use, potential side effects, and drug interactions, ensuring optimal therapeutic outcomes.</p>
                            <ul class="list-unstyled service-features">
                                <li class="border-bottom py-2"><i class="fas fa-xl fa-check-circle text-primary me-2"></i> Wide Range of Medications</li>
                                <li class="border-bottom py-2"><i class="fas fa-xl fa-check-circle text-primary me-2"></i> Expert Medication Counseling</li>
                                <li class="border-bottom py-2"><i class="fas fa-xl fa-check-circle text-primary me-2"></i> Prescription Fulfillment</li>
                                <li class="py-2"><i class="fas fa-xl fa-check-circle text-primary me-2"></i> Convenient On-Site Access</li>
                            </ul>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About section -->
    <section class="py-5" id="about">
        <div class="container">
            <h2 class="text-center mb-5 section-title">About Nyalife Women's Clinic</h2>
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <div class="about-content">
                        <h3 class="mb-4 text-secondary">Comprehensive Women's Healthcare</h3>
                        <p class="lead mb-4">Nyalife Women's Clinic is a specialized healthcare facility dedicated to providing comprehensive obstetrics and gynecology services to women at every stage of life.</p>
                        <p class="mb-4">Founded with a mission to deliver exceptional, personalized care, our clinic combines medical expertise with a compassionate approach to women's health. We understand that every woman's health journey is unique, and we're committed to providing the highest standard of care in a comfortable, supportive environment.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="about-feature">
                                    <i class="fas fa-user-md text-primary mb-2"></i>
                                    <h5>Expert Medical Team</h5>
                                    <p class="small">Experienced obstetricians and gynecologists</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="about-feature">
                                    <i class="fas fa-heart text-primary mb-2"></i>
                                    <h5>Patient-Centered Care</h5>
                                    <p class="small">Personalized treatment plans</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="about-feature">
                                    <i class="fas fa-microscope text-primary mb-2"></i>
                                    <h5>Advanced Technology</h5>
                                    <p class="small">State-of-the-art diagnostic equipment</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="about-feature">
                                    <i class="fas fa-clock text-primary mb-2"></i>
                                    <h5>Convenient Hours</h5>
                                    <p class="small">Flexible scheduling options</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-image-wrapper">
                        <img src="<?= $baseUrl ?>/assets/img/service-tabs/nyalife-1.JPG" class="img-fluid rounded shadow" alt="Nyalife Women's Clinic">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact section -->
    <section class="py-5" id="contact">
        <div class="container">
            <h2 class="text-center mb-5 section-title">Connect With Us</h2>
            <div class="row g-5">
                <div class="col-lg-6 service-tabs-wrapper">
                    <div class="contact-info-card">
                        <div class="card-body">
                            <h4 class="card-title mb-4 text-secondary">Get in Touch</h4>
                            <p class="contact-item mb-1"><i class="fas fa-sm fa-map-marker-alt contact-icon"></i> JemPark Complex building suite A5 in Sabaki, About 500meters from Mlolongo in Athi River, Machakos</p>
                            <p class="contact-item mb-1"><i class="fas fa-sm fa-phone contact-icon"></i> +254746516514</p>
                            <p class="contact-item mb-1"><i class="fas fa-sm fa-envelope contact-icon"></i> info@nyalifewomensclinic.com</p>
                            
                            <h5 class="mt-4 mb-3 text-secondary"><i class="fas fa-sm fa-calendar-alt me-2"></i>Opening Hours</h5>
                            <ul class="list-unstyled contact-hours">
                                <li><b class="work-hours-heading">Weekday Opening Hours:</b></li>
                                <li class="contact-item mb-4">Monday to Friday: 08:00 AM to 01:00 PM; 02:00 PM to 05:00 PM</li>
                                <li><b class="work-hours-heading">Weekend Opening Hours:</b></li>
                                <li class="contact-item mb-4">Saturdays: 08:00 AM to 01:00 PM</li>
                                <li><b class="work-hours-heading">Holiday Opening Hours:</b></li>
                                <li class="contact-item mb-4">Usually open: 08:00 AM to 01:00 PM</li>
                            </ul>

                            <h5 class="mt-5 mb-3 text-secondary">Follow Us</h5>
                            <div class="social-links-contact">
                                <a href="#" class="social-icon"><i class="fab fa-facebook-f fa-sm"></i></a>
                                <a href="#" class="social-icon"><i class="fab fa-twitter fa-sm"></i></a>
                                <a href="#" class="social-icon"><i class="fab fa-instagram fa-sm"></i></a>
                                <a href="#" class="social-icon"><i class="fab fa-linkedin-in fa-sm"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card p-4">
                        <div class="card-body">
                            <h4 class="card-title mb-5 text-secondary"><b>Send Us a Message</b></h4>
                            <form id="contactForm" action="<?= $baseUrl ?>/contact" method="post">
                                <div class="mb-4">
                                    <label for="contactName" class="form-label">Your Name</label>
                                    <input type="text" class="form-control" id="contactName" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contactEmail" class="form-label">Your Email</label>
                                    <input type="email" class="form-control" id="contactEmail" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contactSubject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="contactSubject" name="subject">
                                </div>
                                <div class="mb-3">
                                    <label for="contactMessage" class="form-label">Your Message</label>
                                    <textarea class="form-control" id="contactMessage" name="message" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary contact-submit-btn"><i class="fas fa-sm fa-paper-plane me-2"></i>Send Message</button>
                            </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>