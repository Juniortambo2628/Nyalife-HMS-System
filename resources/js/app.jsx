import '../css/bootstrap-local.min.css';
import '../css/fontawesome-local.min.css';
import '../css/z-index.css';
import '../css/nyalife-loader-unified.css';
import '../css/footer.css';
import '../css/modal-unified.css';
import '../css/nyalife-theme.css';
import '../css/style.css';
import '../css/custom.css';
import '../css/nyalife-sidebar.css';
import '../css/layout-system.css';
import '../css/dashboard-fresh.css';
import '../css/nyalife-components.css';
import './bootstrap';
import './echo';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});
