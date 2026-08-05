import React from 'react';
import { Link } from '@inertiajs/react';

export default function FooterSection({ cms = {}, serviceTabs = [], newsletterForm, onNewsletterSubmit }) {
    const year = new Date().getFullYear();
    const serviceLinks =
        serviceTabs.length > 0
            ? serviceTabs.slice(0, 5).map((tab) => ({ label: tab.title, href: '#services' }))
            : [
                  { label: 'Obstetrics Care', href: '#services' },
                  { label: 'Gynecology', href: '#services' },
                  { label: 'Laboratory', href: '#services' },
                  { label: 'Pharmacy', href: '#services' },
              ];

    const portalLinks = [
        { label: 'Patient portal', href: route('login.patient'), inertia: true },
        { label: 'Staff portal', href: route('login.staff'), inertia: true },
        { label: 'Privacy policy', href: route('privacy-policy'), inertia: true },
        { label: 'Terms of service', href: route('terms-of-service'), inertia: true },
    ];

    const exploreLinks = [
        { label: 'About us', href: '#about' },
        { label: 'Our services', href: '#services' },
        { label: 'Health journal', href: '#blog' },
        { label: 'Contact', href: '#contact' },
        { label: 'Book appointment', href: '#appointment' },
    ];

    return (
        <footer className="landing-footer">
            <div className="landing-footer-wave" aria-hidden="true"></div>

            <div className="container position-relative">
                <div className="row g-5 g-lg-4 py-5 py-lg-6">
                    <div className="col-lg-4">
                        <div className="d-flex align-items-center gap-3 mb-4">
                            <div className="landing-footer-logo-wrap">
                                <img src="/assets/img/logo/Logo2-transparent.png" alt="Nyalife" height="44" />
                            </div>
                            <div>
                                <div className="fw-extrabold fs-5 text-white tracking-tight">NYALIFE</div>
                                <div className="extra-small text-white opacity-75 text-uppercase tracking-widest">
                                    Women's Clinic
                                </div>
                            </div>
                        </div>
                        <p className="text-white opacity-80 leading-relaxed mb-4 pe-lg-4">
                            Specialized obstetrics and gynecology care with clinical excellence and a compassionate,
                            patient-first approach.
                        </p>
                        <div className="d-flex gap-2">
                            <a
                                href={cms.instagram_url || 'https://www.instagram.com/nyalife_womenshealth'}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="landing-footer-social"
                                aria-label="Instagram"
                            >
                                <i className="fab fa-instagram"></i>
                            </a>
                            <a
                                href={cms.linkedin_url || 'https://www.linkedin.com/company/nyalife-women-s-health/'}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="landing-footer-social"
                                aria-label="LinkedIn"
                            >
                                <i className="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>

                    <div className="col-6 col-lg-2">
                        <h6 className="landing-footer-heading">Explore</h6>
                        <ul className="list-unstyled landing-footer-links">
                            {exploreLinks.map((link) => (
                                <li key={link.label}>
                                    <a href={link.href}>{link.label}</a>
                                </li>
                            ))}
                        </ul>
                    </div>

                    <div className="col-6 col-lg-2">
                        <h6 className="landing-footer-heading">Services</h6>
                        <ul className="list-unstyled landing-footer-links">
                            {serviceLinks.map((link) => (
                                <li key={link.label}>
                                    <a href={link.href}>{link.label}</a>
                                </li>
                            ))}
                        </ul>
                    </div>

                    <div className="col-lg-4">
                        <h6 className="landing-footer-heading">Stay connected</h6>
                        <div className="card border-0 rounded-3xl landing-footer-newsletter mb-4">
                            <div className="card-body p-4">
                                <p className="small text-white opacity-80 mb-3">
                                    Health tips, clinic updates, and wellness programs — straight to your inbox.
                                </p>
                                <form onSubmit={onNewsletterSubmit} className="d-flex flex-column gap-2">
                                    <input
                                        type="text"
                                        placeholder="Your name (optional)"
                                        value={newsletterForm.data.name}
                                        onChange={(e) => newsletterForm.setData('name', e.target.value)}
                                        className="form-control form-control-sm rounded-pill border-0 bg-white bg-opacity-10 text-white placeholder-white placeholder-opacity-50"
                                    />
                                    <input
                                        type="email"
                                        required
                                        placeholder="Email address"
                                        value={newsletterForm.data.email}
                                        onChange={(e) => newsletterForm.setData('email', e.target.value)}
                                        className="form-control form-control-sm rounded-pill border-0 bg-white bg-opacity-10 text-white placeholder-white placeholder-opacity-50"
                                    />
                                    {newsletterForm.errors.email && (
                                        <div className="text-warning extra-small">{newsletterForm.errors.email}</div>
                                    )}
                                    <button
                                        type="submit"
                                        disabled={newsletterForm.processing}
                                        className="btn btn-light btn-sm rounded-pill fw-bold mt-1"
                                    >
                                        {newsletterForm.processing ? 'Subscribing...' : 'Subscribe'}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div className="landing-footer-contact-grid">
                            <div>
                                <div className="landing-footer-contact-label">Phone</div>
                                <a
                                    href={`tel:${(cms.contact_phone || '+254746516514').replace(/\s/g, '')}`}
                                    className="landing-footer-contact-value"
                                >
                                    {cms.contact_phone || '+254 746 516 514'}
                                </a>
                            </div>
                            <div>
                                <div className="landing-footer-contact-label">Email</div>
                                <a
                                    href={`mailto:${cms.contact_email || 'info@nyalifewomensclinic.com'}`}
                                    className="landing-footer-contact-value"
                                >
                                    {cms.contact_email || 'info@nyalifewomensclinic.com'}
                                </a>
                            </div>
                        </div>
                        <p className="small text-white opacity-70 mt-3 mb-0 leading-relaxed">
                            {cms.contact_address || 'JemPark Complex, Suite A5, Sabaki, Athi River, Machakos, Kenya'}
                        </p>
                    </div>
                </div>

                <div className="landing-footer-bottom py-4">
                    <div className="row align-items-center g-3">
                        <div className="col-md-6">
                            <p className="extra-small fw-bold text-uppercase tracking-widest text-white opacity-50 mb-0">
                                © {year} Nyalife Women&apos;s Clinic. All rights reserved.
                            </p>
                        </div>
                        <div className="col-md-6">
                            <div className="d-flex flex-wrap align-items-center justify-content-md-end gap-3">
                                {portalLinks.slice(2).map((link) => (
                                    <Link key={link.label} href={link.href} className="landing-footer-legal-link">
                                        {link.label}
                                    </Link>
                                ))}
                                <a
                                    href="https://www.okjtech.co.ke"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="d-flex align-items-center gap-2 text-white text-decoration-none opacity-50 landing-footer-engineered"
                                >
                                    <span className="extra-small fw-bold text-uppercase tracking-widest">
                                        Engineered by
                                    </span>
                                    <img
                                        src="/assets/img/OKJTechLogo-White_Transparent.png"
                                        alt="OKJTech"
                                        className="footer-logo-fixed"
                                    />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    );
}
