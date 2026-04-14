import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link } from '@inertiajs/react';

export default function TermsOfService() {
    return (
        <GuestLayout>
            <Head title="Terms of Service" />

            <div className="col-12 col-lg-10">
                <div className="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div className="card-header bg-dark text-white p-5 text-center">
                        <h1 className="fw-bold mb-0">Terms of Service</h1>
                        <p className="opacity-75 mt-2">Our terms and conditions of use</p>
                    </div>
                    <div className="card-body p-4 p-md-5">
                        <section className="mb-5">
                            <h2 className="h4 fw-bold text-dark mb-3">1. Acceptance of Terms</h2>
                            <p>By accessing and using Nyalife HMS, you accept and agree to be bound by the terms and provision of this agreement. In addition, when using this website's particular services, you shall be subject to any posted guidelines or rules applicable to such services.</p>
                        </section>

                        <section className="mb-5">
                            <h2 className="h4 fw-bold text-dark mb-3">2. Description of Service</h2>
                            <p>Nyalife HMS provides users with hospital management services, including appointment booking, patient records, and lab result tracking. Any new features that augment or enhance the current Service shall be subject to the Terms of Service.</p>
                        </section>

                        <section className="mb-5">
                            <h2 className="h4 fw-bold text-dark mb-3">3. User Conduct</h2>
                            <p>You understand that all information, data, text, software, music, sound, photographs, graphics, video, messages or other materials, whether publicly posted or privately transmitted, are the sole responsibility of the person from which such content originated.</p>
                            <ul>
                                <li>You agree not to use the Service to upload or otherwise transmit any content that is unlawful or harmful.</li>
                                <li>You agree not to impersonate any person or entity.</li>
                                <li>You agree not to forge headers or otherwise manipulate identifiers.</li>
                            </ul>
                        </section>

                        <section className="mb-5">
                            <h2 className="h4 fw-bold text-dark mb-3">4. Termination</h2>
                            <p>You agree that Nyalife HMS may, under certain circumstances and without prior notice, immediately terminate your account and access to the Service.</p>
                        </section>

                        <div className="text-center mt-5">
                            <Link href="/" className="btn btn-outline-dark px-4 rounded-pill">
                                <i className="fas fa-home me-2"></i>Back to Home
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
            <style>{`
                .card-header {
                    background: linear-gradient(135deg, #212529 0%, #495057 100%) !important;
                }
            `}</style>
        </GuestLayout>
    );
}
