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
                                    <div class="why-join-item blur-bg" id="join-item-1" data-bs-toggle="modal" data-bs-target="#serviceModal">
                                        <i class="fas fa-user-md"></i>
                                        <p>Obstetrics Care</p>
                                        <div class="join-tooltip">
                                            <h4>Obstetrics Care</h4>
                                            <p>Comprehensive prenatal, delivery, and postnatal care for expectant mothers. Our team of experienced obstetricians provides personalized care throughout your pregnancy journey.</p>
                                            <p>Services include regular check-ups, ultrasound screenings, genetic testing, and specialized care for high-risk pregnancies.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6 col-6 mb-3">
                                    <div class="why-join-item blur-bg" id="join-item-2" data-bs-toggle="modal" data-bs-target="#serviceModal">
                                        <i class="fas fa-heartbeat"></i>
                                        <p>Gynecology Services</p>
                                        <div class="join-tooltip">
                                            <h4>Gynecology Services</h4>
                                            <p>Expert care for women's reproductive health and wellness. Our gynecology services cover all aspects of women's health from routine examinations to specialized treatments.</p>
                                            <p>We offer pap smears, HPV testing, family planning, and treatments for conditions like endometriosis, PCOS, and menopause symptoms.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6 col-6 mb-3">
                                    <div class="why-join-item blur-bg" id="join-item-3" data-bs-toggle="modal" data-bs-target="#serviceModal">
                                        <i class="fas fa-notes-medical"></i>
                                        <p>Lab Services</p>
                                        <div class="join-tooltip">
                                            <h4>Laboratory Services</h4>
                                            <p>State-of-the-art diagnostic and testing facilities that provide quick and accurate results. Our advanced laboratory is equipped with the latest technology.</p>
                                            <p>Services include blood tests, urinalysis, hormone level testing, genetic testing, amniotic fluid analysis, and various screenings for better health management.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6 col-6 mb-3">
                                    <div class="why-join-item blur-bg" id="join-item-4" data-bs-toggle="modal" data-bs-target="#serviceModal">
                                        <i class="fas fa-pills"></i>
                                        <p>Pharmacy</p>
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
                                    <button class="btn btn-secondary btn-hero me-2 mb-2" data-bs-toggle="modal" data-bs-target="#registerPatientModal">Register as Patient</button>
                                    <button class="btn btn-light btn-hero mb-2" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
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
                            <p>Our state-of-the-art laboratory provides quick and accurate diagnostic testing for a wide range of conditions. Services include blood tests, urinalysis, hormone level testing, genetic testing, amniotic fluid analysis, and more.</p>
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

    <!-- Services section -->
    <section class="pt-5 pb-5 justify-content-center align-items-center"  id="services">
        <div class="container justify-content-center align-items-center">
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
                            <p>We provide a full spectrum of gynecological care, including routine examinations, cervical cancer screening, family planning, and management of conditions like endometriosis, PCOS, and menopausal symptoms. Our approach is holistic, focusing on both physical and emotional well-being.</p>
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

    <!-- Contact section -->
    <section class="py-5 mt-5 mb-5" id="contact">
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
                            <form id="contactForm">
                                <div class="mb-4">
                                    <label for="contactName" class="form-label">Your Name</label>
                                    <input type="text" class="form-control" id="contactName" name="contactName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contactEmail" class="form-label">Your Email</label>
                                    <input type="email" class="form-control" id="contactEmail" name="contactEmail" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contactSubject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="contactSubject" name="contactSubject">
                                </div>
                                <div class="mb-3">
                                    <label for="contactMessage" class="form-label">Your Message</label>
                                    <textarea class="form-control" id="contactMessage" name="contactMessage" rows="5" required></textarea>
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

     <!-- Guest Appointment Booking Section -->
     <section class="py-5 px-5" id="guest-appointment">
        <div class="container text-center">
            <h2 class="mb-4 section-title border-bottom">New Patient? Book an Appointment!</h2>
            <p class="lead mb-4">You don't need an account to book your first visit. Fill out a simple form and we'll get you scheduled.</p>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#guestAppointmentModal">
                <i class="fas fa-calendar-alt me-2"></i> Book Guest Appointment
            </button>
            <p class="mt-3 text-muted">Already a patient? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login here</a> to manage your appointments.</p>
        </div>
    </section>

    <!-- Patient Registration Modal -->
    <div class="modal fade" id="registerPatientModal" tabindex="-1" aria-labelledby="registerPatientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="registerPatientModalLabel">Patient Registration</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="registerPatientAlert" class="alert" role="alert" style="display: none;"></div>
                    <p class="text-muted mb-4">Please fill out this form to register as a new patient.</p>
                    <form id="registerPatientForm" action="<?= $baseUrl ?>/register" method="post" data-nyalife-form="true" data-validate-blur="true" data-ajax="true">
                        <input type="hidden" name="role" value="patient">
                        <!-- Registration form fields -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="female">Female</option>
                                    <option value="male">Male</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                                <div class="form-text">This will be used for login</div>
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
                                <div class="form-text">Minimum 8 characters with letters and numbers</div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                                <div class="form-text">Re-enter password to confirm</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms of Service</a> and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>
                                </label>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Register</button>
                            <div class="spinner-border text-primary d-none mt-2 mx-auto" id="registerPatientSpinner" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Modal -->
    <div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="serviceModalLabel">Service Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());"></button>
                </div>
                <div class="service-modal-bg">
                    <div id="modalServiceContent" class="service-modal-content p-4"></div>
                </div>
                <div class="modal-footer justify-content-start">
                    <div class="d-flex gap-3">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Log in and Book Appointment</button>
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#registerPatientModal" data-bs-dismiss="modal">Sign up as New Client</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="loginModalLabel">Login</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="loginAlert" class="alert" role="alert" style="display: none;"></div>
                    <form id="loginForm" action="<?= $baseUrl ?>/includes/controllers/ajax/auth_handler.php" method="post" data-nyalife-form="true" data-validate-blur="true" data-ajax="true">
                        <div class="mb-3">
                            <label for="login_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="login_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="login_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="login_password" name="password" required autocomplete="current-password" pb-role="password">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                            <label class="form-check-label" for="remember_me">Remember me</label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                            <div class="spinner-border text-primary d-none mt-2 mx-auto" id="loginSpinner" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <p class="mb-0">Don't have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#registerPatientModal" data-bs-dismiss="modal">Register here</a></p>
                            <p class="mt-2"><a href="#" id="forgotPasswordLink">Forgot Password?</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Guest Appointment Modal -->
    <div class="modal fade" id="guestAppointmentModal" tabindex="-1" aria-labelledby="guestAppointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="guestAppointmentModalLabel">Book a Guest Appointment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="guestAppointmentAlert" class="alert" role="alert" style="display: none;"></div>
                    <p class="text-muted mb-4">Please provide your details and desired appointment time. We will contact you to confirm.</p>
                    <form id="guestAppointmentForm" action="<?= $baseUrl ?>/includes/controllers/ajax/book_guest_appointment.php" method="post" data-nyalife-form="true" data-ajax="true">
                        <!-- Patient Details (for guest) -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="guest_first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="guest_first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="guest_last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="guest_last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="guest_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="guest_email" name="email" required>
                                <div class="form-text">We will send appointment confirmation here.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="guest_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="guest_phone" name="phone" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="guest_date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="guest_date_of_birth" name="date_of_birth" required>
                            </div>
                            <div class="col-md-6">
                                <label for="guest_gender" class="form-label">Gender</label>
                                <select class="form-select" id="guest_gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="female">Female</option>
                                    <option value="male">Male</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Appointment Details -->
                        <h5 class="mb-3">Appointment Preferences</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="appointment_date" class="form-label">Preferred Date</label>
                                <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                            </div>
                            <div class="col-md-6">
                                <label for="appointment_time" class="form-label">Preferred Time</label>
                                <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="appointment_type" class="form-label">Service Needed</label>
                                <select class="form-select" id="appointment_type" name="appointment_type" required>
                                    <option value="">Select Service</option>
                                    <option value="new_visit">New Patient Consultation</option>
                                    <option value="follow_up">Follow-up</option>
                                    <option value="routine_checkup">Routine Check-up</option>
                                    <option value="consultation">General Consultation</option>
                                    <!-- These can be dynamically populated from your 'services' table -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="preferred_doctor" class="form-label">Preferred Doctor (Optional)</label>
                                <select class="form-select" id="preferred_doctor" name="doctor_id">
                                    <option value="">Any Available Doctor</option>
                                    <option value="1">Dr. Jane Smith (Gynecologist)</option>
                                    <option value="2">Dr. Emily White (Obstetrician)</option>
                                    <!-- Dynamically populate from your 'staff' table where role_id is doctor -->
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="appointment_reason" class="form-label">Reason for Appointment</label>
                            <textarea class="form-control" id="appointment_reason" name="reason" rows="3" required></textarea>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary submit-guest-appointment-btn">
                                <i class="fas fa-paper-plane me-2"></i> Submit Appointment Request
                            </button>
                            <div class="spinner-border text-primary d-none mt-2 mx-auto" id="guestAppointmentSpinner" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
