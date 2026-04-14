import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link } from '@inertiajs/react';

export default function CookiePolicy() {
    return (
        <GuestLayout>
            <Head title="Cookie Policy" />

            <div className="col-12 col-lg-10">
                <div className="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div className="card-header bg-info text-white p-5 text-center">
                        <h1 className="fw-bold mb-0">Cookie Policy</h1>
                        <p className="opacity-75 mt-2">How we use cookies to improve your experience</p>
                    </div>
                    <div className="card-body p-4 p-md-5">
                        <section className="mb-5">
                            <h2 className="h4 fw-bold text-info mb-3">1. What Are Cookies?</h2>
                            <p>Cookies are small text files that are placed on your computer or mobile device when you browse websites. They are widely used to make websites work, or work more efficiently, as well as to provide information to the owners of the site.</p>
                        </section>

                        <section className="mb-5">
                            <h2 className="h4 fw-bold text-info mb-3">2. How We Use Cookies</h2>
                            <p>Our website uses cookies to:</p>
                            <ul>
                                <li><strong>Essential Cookies:</strong> These are necessary for the website to function. They include, for example, cookies that enable you to log into secure areas of our website.</li>
                                <li><strong>Analytical/Performance Cookies:</strong> They allow us to recognize and count the number of visitors and to see how visitors move around our website when they are using it.</li>
                                <li><strong>Functionality Cookies:</strong> These are used to recognize you when you return to our website. This enables us to personalize our content for you.</li>
                            </ul>
                        </section>

                        <section className="mb-5">
                            <h2 className="h4 fw-bold text-info mb-3">3. Managing Cookies</h2>
                            <p>Most browsers allow you to refuse to accept cookies and to delete cookies. The methods for doing so vary from browser to browser, and from version to version. You can however obtain up-to-date information about blocking and deleting cookies via these links:</p>
                            <ul>
                                <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener">Chrome</a></li>
                                <li><a href="https://support.mozilla.org/en-US/kb/enable-and-disable-cookies-website-preferences" target="_blank" rel="noopener">Firefox</a></li>
                                <li><a href="https://support.microsoft.com/en-gb/help/17442/windows-internet-explorer-delete-manage-cookies" target="_blank" rel="noopener">Internet Explorer</a></li>
                            </ul>
                        </section>

                        <div className="text-center mt-5">
                            <Link href="/" className="btn btn-outline-info px-4 rounded-pill">
                                <i className="fas fa-home me-2"></i>Back to Home
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
            <style>{`
                .card-header {
                    background: linear-gradient(135deg, #0dcaf0 0%, #0d6efd 100%) !important;
                }
            `}</style>
        </GuestLayout>
    );
}
