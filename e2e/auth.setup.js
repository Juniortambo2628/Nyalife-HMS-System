import { test as setup, expect } from '@playwright/test';

const authFile = 'e2e/.auth/user.json';

setup('authenticate as staff user', async ({ page }) => {
    // These credentials must match a seeded staff user in the target environment.
    const email = process.env.E2E_STAFF_EMAIL || 'admin@nyalife.com';
    const password = process.env.E2E_STAFF_PASSWORD || 'password';

    // Override the per-test timeout so the Inertia/React hydration has time to mount.
    setup.setTimeout(180000);

    // Capture browser console and network failures for diagnostics.
    page.on('console', (msg) => {
        if (msg.type() === 'error') {
            console.log(`[browser:${msg.type()}] ${msg.text()}`);
        }
    });
    page.on('pageerror', (err) => {
        console.log(`[pageerror] ${err.message}`);
    });
    page.on('requestfailed', (req) => {
        console.log(`[requestfailed] ${req.url()} - ${req.failure()?.errorText}`);
    });
    page.on('response', (resp) => {
        if (resp.status() >= 400) {
            console.log(`[response ${resp.status()}] ${resp.url()}`);
        }
    });

    const response = await page.goto('/login/staff', { waitUntil: 'domcontentloaded' });
    console.log(`[goto] status=${response?.status()} url=${page.url()}`);

    // Dump the initial HTML for debugging if hydration fails.
    const initialHtml = await page.content();
    console.log(`[html:initial] length=${initialHtml.length}`);
    if (initialHtml.length < 500) {
        console.log(`[html:initial] ${initialHtml}`);
    } else {
        // Print first 2000 chars to see what's there.
        console.log(`[html:initial] ${initialHtml.substring(0, 2000)}`);
    }

    // Inertia renders <div id="app" data-page="..."></div> directly into <body>.
    // Wait for that element to be present (meaning Inertia responded server-side).
    await page.waitForSelector('#app', { state: 'attached', timeout: 30000 });
    console.log('[wait] #app attached');

    // Now wait for the React app to hydrate and render the login form.
    await page.waitForSelector('input#email', { state: 'visible', timeout: 90000 });

    await page.fill('input#email', email);
    await page.fill('input#password', password);
    await page.click('button[type="submit"]');

    // After login the user is usually redirected to /dashboard.
    await page.waitForURL('/dashboard', { timeout: 30000 });
    await expect(page.locator('body')).toContainText('Dashboard');

    await page.context().storageState({ path: authFile });
});
