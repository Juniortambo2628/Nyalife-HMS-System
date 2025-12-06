/**
 * Nyalife HMS - Shared JavaScript Entry Point
 * Aggregates common utilities and core modules
 */

// Common Utilities
import './common/date-utils';
import './common/http';
import './common/utils';
import './common/validation';

// Import Bootstrap (includes Popper) and expose globally
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Expose jQuery globally for inline scripts
import $ from 'jquery';
window.$ = window.jQuery = $;

// Import and expose Chart.js globally for inline scripts
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);
window.Chart = Chart;

// Shared Dashboard Utilities
import './shared/dashboard-utils';
import './shared/datatable-utils';

// Core Unified Modules
import './nyalife-loader-unified';
import './core/unified-notifications';
import './core/unified-api';
import './core/unified-utils';
import './core/unified-forms';

// UI Fixes and Components
import './dropdown-fix';
import './sidebar';

// Export for external use if needed (though mostly these modules attach to window)
export const Shared = {
    version: '1.0.0'
};
