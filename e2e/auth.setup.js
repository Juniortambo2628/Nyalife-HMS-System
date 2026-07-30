import { test as setup, expect } from '@playwright/test';

const authFile = 'e2e/.auth/user.json';

setup('authenticate as staff user', async ({ page }) => {
    // These credentials must match a seeded staff user in the target environment.
    const email = process.env.E2E_STAFF_EMAIL || 'admin@nyalife.com';
    const password = process.env.E2E_STAFF_PASSWORD || 'password';

    // Override the per-test timeout so the Inertia/React hydration has time to mount.
    setup.setTimeout(180000);

    await page.goto('/login/staff', { waitUntil: 'domcontentloaded' });

    // Inertia renders <div id="app" data-page="..."></div> directly into <body>.
    // Wait for that element (Inertia SSR response) and then for the hydrated form.
    await page.waitForSelector('#app', { state: 'attached', timeout: 30000 });
    await page.waitForSelector('input#email', { state: 'visible', timeout: 90000 });

    await page.fill('input#email', email);
    await page.fill('input#password', password);
    await page.click('button[type="submit"]');

    // After login the user is usually redirected to /dashboard.
    await page.waitForURL('/dashboard', { timeout: 30000 });
    await expect(page.locator('body')).toContainText('Dashboard');

    await page.context().storageState({ path: authFile });
});
