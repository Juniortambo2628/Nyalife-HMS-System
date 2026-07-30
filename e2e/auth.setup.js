import { test as setup, expect } from '@playwright/test';

const authFile = 'e2e/.auth/user.json';

setup('authenticate as staff user', async ({ page }) => {
    // These credentials must match a seeded staff user in the target environment.
    const email = process.env.E2E_STAFF_EMAIL || 'admin@nyalife.com';
    const password = process.env.E2E_STAFF_PASSWORD || 'password';

    // Override the per-test timeout so the Inertia/React hydration has time to mount.
    test.setTimeout(180000);

    await page.goto('/login/staff', { waitUntil: 'domcontentloaded' });

    // Wait for the Inertia/React app to hydrate before looking for the input.
    // Inertia renders into #app and the login form is mounted by React.
    await page.waitForSelector('#app [data-page], #app form, input#email', {
        state: 'attached',
        timeout: 90000,
    });

    // Now wait for the email input specifically.
    await page.waitForSelector('input#email', { state: 'visible', timeout: 60000 });

    await page.fill('input#email', email);
    await page.fill('input#password', password);
    await page.click('button[type="submit"]');

    // After login the user is usually redirected to /dashboard.
    await page.waitForURL('/dashboard', { timeout: 30000 });
    await expect(page.locator('body')).toContainText('Dashboard');

    await page.context().storageState({ path: authFile });
});
