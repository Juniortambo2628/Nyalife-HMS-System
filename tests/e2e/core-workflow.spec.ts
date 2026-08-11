import { test, expect } from '@playwright/test';

test.describe('Core Clinical Workflow', () => {
    test('Nurse creates draft and Doctor concludes', async ({ page }) => {
        // We assume we have a seed for a nurse and doctor
        
        // 1. Login as Nurse
        await page.goto('/login');
        await page.fill('input[name="email"]', 'nurse@example.com');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        
        // Wait for dashboard
        await expect(page).toHaveURL('/dashboard');
        
        // 2. Go to Consultations -> Create
        await page.goto('/consultations/create');
        
        // 3. Fill Vitals
        await page.fill('input[name="vital_signs.blood_pressure"]', '120/80');
        await page.fill('input[name="vital_signs.temperature"]', '37.0');
        await page.fill('input[name="vital_signs.heart_rate"]', '72');
        
        // Save Vitals
        await page.click('button:has-text("SAVE VITALS")');
        
        // Should redirect to Edit
        await expect(page).toHaveURL(/\/consultations\/\d+\/edit/);
        
        // Logout Nurse
        await page.goto('/logout');
        
        // 4. Login as Doctor
        await page.goto('/login');
        await page.fill('input[name="email"]', 'doctor@example.com');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        
        // 5. Doctor opens the draft
        await page.goto('/consultations');
        // Find the "In Progress" consultation and click Edit
        await page.click('a:has-text("Edit")');
        
        // Conclude
        await page.fill('textarea[name="diagnosis"]', 'Healthy');
        await page.click('button:has-text("CONCLUDE & CLOSE")');
        
        // Should return to dashboard
        await expect(page).toHaveURL('/dashboard');
    });
});
