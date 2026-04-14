import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link } from '@inertiajs/react';

export default function PrivacyPolicy() {
    return (
        <GuestLayout>
            <Head title="Privacy Policy" />

            <div className="col-12 col-lg-10">
                <div className="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div className="card-header bg-primary text-white p-5 text-center">
                        <h1 className="fw-bold mb-0">Privacy Policy</h1>
                        <p className="opacity-75 mt-2">Last Updated: April 14, 2026</p>
                    </div>
                    <div className="card-body p-4 p-md-5">
                        <section className="mb-5">
                            <h2 className="h4 fw-bold text-primary mb-3">1. Introduction</h2>
                            <p>Welcome to Nyalife HMS. We respect your privacy and are committed to protecting your personal data. This privacy policy will inform you as to how we look after your personal data when you visit our website and tell you about your privacy rights and how the law protects you.</p>
                        </section>

                        <section className="mb-5">
                            <h2 className="h4 fw-bold text-primary mb-3">2. Data We Collect</h2>
                            <p>We may collect, use, store and transfer different kinds of personal data about you which we have grouped together as follows:</p>
                            <ul>
                                <li><strong>Identity Data:</strong> includes first name, last name, username, and title.</li>
                                <li><strong>Contact Data:</strong> includes billing address, email address, and telephone numbers.</li>
                                <li><strong>Medical Data:</strong> includes consultations, prescriptions, and lab reports.</li>
                                <li><strong>Technical Data:</strong> includes internet protocol (IP) address, your login data, browser type and version.</li>
                            </ul>
                        </section>

                        <section className="mb-5">
                            <h2 className="h4 fw-bold text-primary mb-3">3. How We Use Your Data</h2>
                            <p>We will only use your personal data when the law allows us to. Most commonly, we will use your personal data in the following circumstances:</p>
                            <ul>
                                <li>Where we need to perform the contract we are about to enter into or have entered into with you.</li>
                                <li>Where it is necessary for our legitimate interests and your interests and fundamental rights do not override those interests.</li>
                                <li>Where we need to comply with a legal or regulatory obligation.</li>
                            </ul>
                        </section>

                        <section className="mb-5">
                            <h2 className="h4 fw-bold text-primary mb-3">4. Security</h2>
                            <p>We have put in place appropriate security measures to prevent your personal data from being accidentally lost, used or accessed in an unauthorized way, altered or disclosed. In addition, we limit access to your personal data to those employees, agents, contractors and other third parties who have a business need to know.</p>
                        </section>

                        <div className="text-center mt-5">
                            <Link href="/" className="btn btn-outline-primary px-4 rounded-pill">
                                <i className="fas fa-home me-2"></i>Back to Home
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
            <style>{`
                .card-header {
                    background: linear-gradient(135deg, #198754 0%, #20c997 100%) !important;
                }
            `}</style>
        </GuestLayout>
    );
}
