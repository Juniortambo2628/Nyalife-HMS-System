import { Link } from '@inertiajs/react';
import { useEffect } from 'react';

export default function GuestLayout({ children }) {
    useEffect(() => {
        document.body.classList.add('auth-page');
        return () => document.body.classList.remove('auth-page');
    }, []);

    return (
        <div className="auth-page">
            <div className="main-content">
                <div className="container-fluid">
                    <div className="row justify-content-center">
                        {children}
                    </div>
                </div>
            </div>
        </div>
    );
}
