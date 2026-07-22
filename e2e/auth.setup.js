import { test as setup, expect } from '@playwright/test';

const authFile = 'e2e/.auth/user.json';

setup('authenticate as staff user', async ({ page }) => {
    // These credentials must match a seeded staff user in the target environment.
    const email = process.env.E2E_STAFF_EMAIL || 'admin@nyalife.com';
    const password = process.env.E2E_STAFF_PASSWORD || 'password';

    await page.goto('/login/staff');

    // Wait for the login form to render.
    await page.waitForSelector('input#email');

    await page.fill('input#email', email);
    await page.fill('input#password', password);
    await page.click('button[type="submit"]');

    // After login the user is usually redirected to /dashboard.
    await page.waitForURL('/dashboard', { timeout: 10000 });
    await expect(page.locator('body')).toContainText('Dashboard');

    await page.context().storageState({ path: authFile });
});
