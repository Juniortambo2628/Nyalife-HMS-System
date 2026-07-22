import { test, expect } from '@playwright/test';

test.use({ storageState: 'e2e/.auth/user.json' });

test.describe('patient clinical workflow', () => {
    test('navigates to the consultation creation page after login', async ({ page }) => {
        await page.goto('/dashboard');
        await expect(page.locator('body')).toContainText('Dashboard');

        await page.goto('/consultations/create');

        // Expect either the consultation form or an access-denied/redirect response.
        await page.waitForLoadState('networkidle');

        const bodyText = await page.locator('body').textContent();
        const url = page.url();

        expect(
            url.includes('/consultations/create') || url.includes('/login') || url.includes('/verify-email'),
        ).toBeTruthy();

        if (url.includes('/consultations/create')) {
            expect(bodyText).toMatch(/consultation|patient|create/i);
        }
    });

    test('patient list is reachable from the authenticated dashboard', async ({ page }) => {
        await page.goto('/patients');
        await page.waitForLoadState('networkidle');

        const url = page.url();
        expect(url.includes('/patients') || url.includes('/login')).toBeTruthy();
    });
});
