const { test, expect } = require('@playwright/test');

test('Login page loads', async ({ page }) => {
  await page.goto('http://localhost/autodesk/Auto-Desk-Project/signin.php');

  const emailInput = await page.locator('input[name="email"]');
  await expect(emailInput).toBeVisible();

  await emailInput.fill('test@example.com');
  await page.locator('input[name="password"]').fill('password123');
  
  await page.locator('button[type="submit"]').click();

  await expect(page).toHaveURL(/home|dashboard|project/i);
});
